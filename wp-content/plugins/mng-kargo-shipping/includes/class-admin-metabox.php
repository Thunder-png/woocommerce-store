<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Sipariş düzenleme ekranında MNG Kargo kutusu: barkod, takip, butonlar.
 */
class MNG_Kargo_Admin_Metabox {

    protected static $instance = null;

    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        add_action( 'add_meta_boxes', array( $this, 'add_metabox' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        add_action( 'wp_ajax_mng_kargo_create_shipment', array( $this, 'ajax_create_shipment' ) );
        add_action( 'wp_ajax_mng_kargo_refresh_tracking', array( $this, 'ajax_refresh_tracking' ) );
        add_action( 'wp_ajax_mng_kargo_cancel_shipment', array( $this, 'ajax_cancel_shipment' ) );
    }

    public function enqueue_assets( $hook ) {
        if ( 'post.php' !== $hook && 'post-new.php' !== $hook ) {
            return;
        }
        global $post;
        if ( ! $post || 'shop_order' !== $post->post_type ) {
            return;
        }

        wp_enqueue_style(
            'mng-kargo-admin',
            MNG_KARGO_SHIPPING_URL . 'assets/css/mng-admin.css',
            array(),
            MNG_KARGO_SHIPPING_VERSION
        );

        wp_enqueue_script(
            'mng-kargo-admin',
            MNG_KARGO_SHIPPING_URL . 'assets/js/mng-admin.js',
            array( 'jquery' ),
            MNG_KARGO_SHIPPING_VERSION,
            true
        );
        wp_localize_script( 'mng-kargo-admin', 'mngKargoAdmin', array(
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'mng_kargo_admin' ),
        ) );
    }

    public function add_metabox() {
        add_meta_box(
            'mng_kargo_shipment',
            __( 'MNG Kargo', 'mng-kargo-shipping' ),
            array( $this, 'render_metabox' ),
            'shop_order',
            'side'
        );
    }

    public function render_metabox( $post ) {
        $order = wc_get_order( $post->ID );
        if ( ! $order ) {
            echo '<p>' . esc_html__( 'Sipariş yüklenemedi.', 'mng-kargo-shipping' ) . '</p>';
            return;
        }

        $barcode   = $order->get_meta( '_mng_barcode' );
        $invoice_id = $order->get_meta( '_mng_invoice_id' );
        $status   = $order->get_meta( '_mng_tracking_status' );

        $is_mng = false;
        foreach ( $order->get_shipping_methods() as $sm ) {
            if ( isset( $sm['method_id'] ) && 'mng_kargo' === $sm['method_id'] ) {
                $is_mng = true;
                break;
            }
        }

        if ( ! $is_mng ) {
            echo '<p class="mng-info">' . esc_html__( 'Bu sipariş MNG Kargo ile gönderilmiyor.', 'mng-kargo-shipping' ) . '</p>';
            return;
        }

        if ( $barcode ) {
            echo '<p class="mng-barcode"><strong>' . esc_html__( 'Barkod:', 'mng-kargo-shipping' ) . '</strong> ' . esc_html( $barcode ) . '</p>';
            if ( $status ) {
                echo '<p class="mng-status">' . esc_html( $status ) . '</p>';
            }
            echo '<p><a href="https://www.mngkargo.com.tr/gonderi-takip?barcode=' . esc_attr( $barcode ) . '" target="_blank" rel="noopener" class="button button-small">' . esc_html__( 'Takip et', 'mng-kargo-shipping' ) . '</a></p>';
            echo '<p><button type="button" class="button button-small mng-refresh-tracking" data-order-id="' . esc_attr( $post->ID ) . '">' . esc_html__( 'Durumu yenile', 'mng-kargo-shipping' ) . '</button></p>';
            echo '<p><button type="button" class="button button-small button-link-delete mng-cancel-shipment" data-order-id="' . esc_attr( $post->ID ) . '">' . esc_html__( 'Kargo iptal', 'mng-kargo-shipping' ) . '</button></p>';
        } else {
            echo '<p class="mng-info">' . esc_html__( 'Henüz gönderi oluşturulmadı.', 'mng-kargo-shipping' ) . '</p>';
            echo '<p><button type="button" class="button button-primary mng-create-shipment" data-order-id="' . esc_attr( $post->ID ) . '">' . esc_html__( 'Kargo gönder', 'mng-kargo-shipping' ) . '</button></p>';
        }

        echo '<p class="mng-ajax-message" style="display:none;"></p>';
    }

    public function ajax_create_shipment() {
        check_ajax_referer( 'mng_kargo_admin', 'nonce' );
        if ( ! current_user_can( 'edit_shop_orders' ) ) {
            wp_send_json_error( array( 'message' => __( 'Yetkiniz yok.', 'mng-kargo-shipping' ) ) );
        }
        $order_id = isset( $_POST['order_id'] ) ? absint( $_POST['order_id'] ) : 0;
        if ( ! $order_id ) {
            wp_send_json_error( array( 'message' => __( 'Geçersiz sipariş.', 'mng-kargo-shipping' ) ) );
        }
        $handler = MNG_Kargo_Order_Handler::instance();
        $result = $handler->maybe_create_shipment( $order_id );
        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array( 'message' => $result->get_error_message() ) );
        }
        if ( false === $result ) {
            wp_send_json_error( array( 'message' => __( 'Bu sipariş için gönderi oluşturulamadı (MNG Kargo seçili değil veya zaten oluşturulmuş).', 'mng-kargo-shipping' ) ) );
        }
        wp_send_json_success( array( 'message' => __( 'Gönderi oluşturuldu.', 'mng-kargo-shipping' ) ) );
    }

    public function ajax_refresh_tracking() {
        check_ajax_referer( 'mng_kargo_admin', 'nonce' );
        if ( ! current_user_can( 'edit_shop_orders' ) ) {
            wp_send_json_error( array( 'message' => __( 'Yetkiniz yok.', 'mng-kargo-shipping' ) ) );
        }
        $order_id = isset( $_POST['order_id'] ) ? absint( $_POST['order_id'] ) : 0;
        $order = wc_get_order( $order_id );
        if ( ! $order ) {
            wp_send_json_error( array( 'message' => __( 'Sipariş bulunamadı.', 'mng-kargo-shipping' ) ) );
        }
        $barcode = $order->get_meta( '_mng_barcode' );
        if ( ! $barcode ) {
            wp_send_json_error( array( 'message' => __( 'Barkod yok.', 'mng-kargo-shipping' ) ) );
        }
        $client = MNG_Kargo_API_Client::instance();
        $result = $client->track_shipment( $barcode );
        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array( 'message' => $result->get_error_message() ) );
        }
        $status_text = isset( $result['shipmentStatusName'] ) ? $result['shipmentStatusName'] : ( isset( $result['status'] ) ? (string) $result['status'] : '' );
        $order->update_meta_data( '_mng_tracking_status', $status_text );
        $order->save();
        wp_send_json_success( array( 'message' => $status_text ?: __( 'Durum güncellendi.', 'mng-kargo-shipping' ) ) );
    }

    public function ajax_cancel_shipment() {
        check_ajax_referer( 'mng_kargo_admin', 'nonce' );
        if ( ! current_user_can( 'edit_shop_orders' ) ) {
            wp_send_json_error( array( 'message' => __( 'Yetkiniz yok.', 'mng-kargo-shipping' ) ) );
        }
        $order_id = isset( $_POST['order_id'] ) ? absint( $_POST['order_id'] ) : 0;
        $order = wc_get_order( $order_id );
        if ( ! $order ) {
            wp_send_json_error( array( 'message' => __( 'Sipariş bulunamadı.', 'mng-kargo-shipping' ) ) );
        }
        $invoice_id = $order->get_meta( '_mng_invoice_id' );
        if ( ! $invoice_id ) {
            wp_send_json_error( array( 'message' => __( 'Gönderi bulunamadı.', 'mng-kargo-shipping' ) ) );
        }
        $client = MNG_Kargo_API_Client::instance();
        if ( ! method_exists( $client, 'cancel_shipment' ) ) {
            $order->delete_meta_data( '_mng_invoice_id' );
            $order->delete_meta_data( '_mng_shipment_id' );
            $order->delete_meta_data( '_mng_barcode' );
            $order->delete_meta_data( '_mng_tracking_status' );
            $order->save();
            wp_send_json_success( array( 'message' => __( 'Yerel kayıtlar temizlendi. API iptal için MNG panelinden yapın.', 'mng-kargo-shipping' ) ) );
        }
        $result = $client->cancel_shipment( $invoice_id );
        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array( 'message' => $result->get_error_message() ) );
        }
        $order->delete_meta_data( '_mng_invoice_id' );
        $order->delete_meta_data( '_mng_shipment_id' );
        $order->delete_meta_data( '_mng_barcode' );
        $order->delete_meta_data( '_mng_tracking_status' );
        $order->save();
        wp_send_json_success( array( 'message' => __( 'Gönderi iptal edildi.', 'mng-kargo-shipping' ) ) );
    }
}
