<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Sipariş durumu değişince MNG Kargo'ya gönderi oluşturur.
 */
class MNG_Kargo_Order_Handler {

    const META_INVOICE_ID = '_mng_invoice_id';
    const META_SHIPMENT_ID = '_mng_shipment_id';
    const META_BARCODE = '_mng_barcode';
    const META_TRACKING_STATUS = '_mng_tracking_status';

    protected static $instance = null;

    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        add_action( 'woocommerce_order_status_processing', array( $this, 'maybe_create_shipment' ), 20, 2 );
        add_action( 'woocommerce_order_status_completed', array( $this, 'maybe_create_shipment' ), 20, 2 );
    }

    /**
     * Sadece MNG Kargo ile verilen ve henüz gönderi oluşturulmamış siparişlerde API çağrısı yapar.
     *
     * @param int       $order_id
     * @param WC_Order  $order
     * @return true|false|WP_Error true = gönderi + barkod oluşturuldu, false = işlem yapılmadı (no-op), WP_Error = API hatası
     */
    public function maybe_create_shipment( $order_id, $order = null ) {
        if ( ! $order instanceof WC_Order ) {
            $order = wc_get_order( $order_id );
        }
        if ( ! $order ) {
            return false;
        }

        $shipping_methods = $order->get_shipping_methods();
        $is_mng = false;
        foreach ( $shipping_methods as $sm ) {
            if ( isset( $sm['method_id'] ) && 'mng_kargo' === $sm['method_id'] ) {
                $is_mng = true;
                break;
            }
        }
        if ( ! $is_mng ) {
            return false;
        }

        if ( $order->get_meta( self::META_INVOICE_ID ) ) {
            return false;
        }

        $client = MNG_Kargo_API_Client::instance();
        $settings = get_option( MNG_Kargo_API_Client::OPTION_KEY, array() );
        $shipment_service_type = isset( $settings['shipment_service_type'] ) ? (int) $settings['shipment_service_type'] : 1;
        $payment_type = isset( $settings['payment_type'] ) ? (int) $settings['payment_type'] : 1;

        $reference_id = strtoupper( (string) $order->get_order_number() );
        $content = $this->get_order_content_string( $order );
        $city_code = $this->resolve_city_code( $order, $client );

        $recipient = array(
            'customerName'     => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
            'address'          => $order->get_billing_address_1() . ( $order->get_billing_address_2() ? ' ' . $order->get_billing_address_2() : '' ),
            'districtName'      => $order->get_billing_state() ?: $order->get_billing_city(),
            'cityName'         => $order->get_billing_city(),
            'cityCode'         => $city_code,
            'phone'            => $order->get_billing_phone(),
            'email'            => $order->get_billing_email(),
        );

        $order_payload = array(
            'referenceId'          => $reference_id,
            'shipmentServiceType'  => $shipment_service_type,
            'packagingType'        => 4, // KOLİ
            'content'              => mb_substr( $content, 0, 100 ),
            'smsPreference1'       => 1,
            'smsPreference2'       => 0,
            'smsPreference3'       => 0,
            'paymentType'          => $payment_type,
            'deliveryType'         => 1,
            'recipient'            => $recipient,
        );

        $create_result = $client->create_order( $order_payload );
        if ( is_wp_error( $create_result ) ) {
            $order->add_order_note(
                __( 'MNG Kargo gönderi oluşturulamadı: ', 'mng-kargo-shipping' ) . $create_result->get_error_message()
            );
            return $create_result;
        }

        $order_invoice_id = isset( $create_result['orderInvoiceId'] ) ? $create_result['orderInvoiceId'] : '';
        $order->add_order_note(
            __( 'MNG Kargo siparişi oluşturuldu. Fatura ID: ', 'mng-kargo-shipping' ) . $order_invoice_id
        );

        $is_cod = ( 2 === (int) $payment_type ) ? 1 : 0; // 2 = Alıcı öder
        $barcode_payload = array(
            'referenceId'   => $reference_id,
            'isCOD'         => $is_cod,
            'packagingType' => 4,
        );

        $barcode_result = $client->create_barcode( $barcode_payload );
        if ( is_wp_error( $barcode_result ) ) {
            $order->update_meta_data( self::META_INVOICE_ID, $order_invoice_id );
            $order->save();
            $order->add_order_note(
                __( 'MNG Kargo barkod alınamadı: ', 'mng-kargo-shipping' ) . $barcode_result->get_error_message()
            );
            return $barcode_result;
        }

        $barcode_value = '';
        if ( ! empty( $barcode_result['barcodes'] ) && is_array( $barcode_result['barcodes'] ) ) {
            $first = reset( $barcode_result['barcodes'] );
            $barcode_value = isset( $first['value'] ) ? $first['value'] : '';
        }

        $order->update_meta_data( self::META_INVOICE_ID, $order_invoice_id );
        $order->update_meta_data( self::META_SHIPMENT_ID, isset( $barcode_result['shipmentId'] ) ? $barcode_result['shipmentId'] : '' );
        $order->update_meta_data( self::META_BARCODE, $barcode_value );
        $order->update_meta_data( self::META_TRACKING_STATUS, '' );
        $order->save();

        $order->add_order_note(
            __( 'MNG Kargo barkod oluşturuldu: ', 'mng-kargo-shipping' ) . $barcode_value
        );

        return true;
    }

    protected function get_order_content_string( WC_Order $order ) {
        $names = array();
        foreach ( $order->get_items() as $item ) {
            if ( $item->get_name() ) {
                $names[] = $item->get_name();
            }
        }
        return implode( ', ', $names );
    }

    /**
     * Sipariş şehir adına göre MNG cityCode döndürür. CBS API ile eşleştirme.
     */
    protected function resolve_city_code( WC_Order $order, MNG_Kargo_API_Client $client ) {
        $city_name = $order->get_billing_city();
        if ( ! $city_name ) {
            return '';
        }

        $cache_key = 'mng_city_code_' . md5( $city_name );
        $cached = get_transient( $cache_key );
        if ( false !== $cached ) {
            return $cached;
        }

        $cities = $client->get_cities();
        if ( is_wp_error( $cities ) || ! is_array( $cities ) ) {
            return '';
        }

        $normalize = function ( $s ) {
            return trim( mb_strtolower( $s ) );
        };
        $needle = $normalize( $city_name );

        foreach ( $cities as $city ) {
            $name = isset( $city['name'] ) ? $normalize( $city['name'] ) : '';
            if ( $name === $needle && ! empty( $city['code'] ) ) {
                set_transient( $cache_key, $city['code'], DAY_IN_SECONDS );
                return $city['code'];
            }
        }

        return '';
    }
}
