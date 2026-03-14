<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class MNG_Kargo_API_Client {

    const OPTION_KEY = 'mng_kargo_shipping_settings';
    const TOKEN_TRANSIENT_KEY = 'mng_kargo_api_token';

    /**
     * @var MNG_Kargo_API_Client|null
     */
    protected static $instance = null;

    /**
     * @return MNG_Kargo_API_Client
     */
    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Temel ayarları döndürür.
     *
     * @return array
     */
    protected function get_settings() {
        $defaults = array(
            'customer_number'       => '',
            'password'             => '',
            'ibm_client_id'        => '',
            'ibm_client_secret'    => '',
            'api_env'              => 'test',
            'shipment_service_type' => 1,
            'payment_type'         => 1,
        );

        $stored = get_option( self::OPTION_KEY, array() );

        if ( ! is_array( $stored ) ) {
            $stored = array();
        }

        return wp_parse_args( $stored, $defaults );
    }

    /**
     * API base URL.
     *
     * @return string
     */
    protected function get_base_url() {
        $settings = $this->get_settings();

        if ( 'prod' === $settings['api_env'] ) {
            // Gerçek prod host bilinmiyorsa test ile devam edilir.
            return 'https://api.mngkargo.com.tr/mngapi/api';
        }

        return 'https://testapi.mngkargo.com.tr/mngapi/api';
    }

    /**
     * JWT token'ı döndürür, yoksa alır ve transient'a kaydeder.
     *
     * @return string|WP_Error
     */
    public function get_token() {
        $cached = get_transient( self::TOKEN_TRANSIENT_KEY );
        if ( ! empty( $cached ) && is_string( $cached ) ) {
            return $cached;
        }

        $settings = $this->get_settings();

        if ( empty( $settings['customer_number'] ) || empty( $settings['password'] ) ) {
            return new WP_Error( 'mng_missing_credentials', __( 'MNG Kargo API kimlik bilgileri eksik.', 'mng-kargo-shipping' ) );
        }

        $body = array(
            'customerNumber' => (string) $settings['customer_number'],
            'password'       => (string) $settings['password'],
            'identityType'   => 1,
        );

        $response = $this->request(
            'POST',
            '/token',
            array(
                'headers' => $this->build_headers( false ),
                'body'    => wp_json_encode( $body ),
            ),
            false
        );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $data = json_decode( wp_remote_retrieve_body( $response ), true );
        if ( ! is_array( $data ) || empty( $data['jwt'] ) ) {
            return new WP_Error( 'mng_invalid_token_response', __( 'MNG Kargo token cevabı geçersiz.', 'mng-kargo-shipping' ) );
        }

        // Expire bilgisini parse etmek yerine güvenli bir tampon süre kullan.
        set_transient( self::TOKEN_TRANSIENT_KEY, $data['jwt'], HOUR_IN_SECONDS );

        return $data['jwt'];
    }

    /**
     * Ortak header'ları oluşturur.
     *
     * @param bool       $with_token Token eklensin mi?
     * @param string|bool $token     Zorunlu değil, false ise get_token() çağrılır.
     *
     * @return array|WP_Error
     */
    protected function build_headers( $with_token = true, $token = false ) {
        $settings = $this->get_settings();

        $headers = array(
            'Content-Type'        => 'application/json',
            'Accept'              => 'application/json',
            'X-IBM-Client-Id'     => $settings['ibm_client_id'],
            'X-IBM-Client-Secret' => $settings['ibm_client_secret'],
        );

        if ( $with_token ) {
            if ( false === $token ) {
                $token = $this->get_token();
            }

            if ( is_wp_error( $token ) ) {
                return $token;
            }

            $headers['Authorization'] = 'Bearer ' . $token;
        }

        return $headers;
    }

    /**
     * Düşük seviye HTTP isteği.
     *
     * @param string $method
     * @param string $path
     * @param array  $args
     * @param bool   $with_token
     *
     * @return array|WP_Error
     */
    protected function request( $method, $path, $args = array(), $with_token = true ) {
        $url = rtrim( $this->get_base_url(), '/' ) . $path;

        if ( ! isset( $args['headers'] ) ) {
            $headers = $this->build_headers( $with_token );
            if ( is_wp_error( $headers ) ) {
                return $headers;
            }
            $args['headers'] = $headers;
        }

        $args['method']  = $method;
        $args['timeout'] = isset( $args['timeout'] ) ? (int) $args['timeout'] : 20;

        $response = wp_remote_request( $url, $args );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $code = wp_remote_retrieve_response_code( $response );
        if ( $code < 200 || $code >= 300 ) {
            return new WP_Error(
                'mng_api_error',
                sprintf(
                    /* translators: 1: HTTP status code */
                    __( 'MNG Kargo API hata döndürdü (HTTP %d).', 'mng-kargo-shipping' ),
                    $code
                ),
                array(
                    'response' => $response,
                )
            );
        }

        return $response;
    }

    /**
     * Sipariş oluşturur (createDetailedOrder).
     *
     * @param array $payload
     *
     * @return array|WP_Error
     */
    public function create_order( array $payload ) {
        $response = $this->request(
            'POST',
            '/pluscmdapi/createDetailedOrder',
            array(
                'body' => wp_json_encode( $payload ),
            ),
            true
        );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $data = json_decode( wp_remote_retrieve_body( $response ), true );
        if ( ! is_array( $data ) ) {
            return new WP_Error( 'mng_invalid_order_response', __( 'MNG Kargo sipariş cevabı geçersiz.', 'mng-kargo-shipping' ) );
        }

        return $data;
    }

    /**
     * Barkod yaratır (createbarcode).
     *
     * @param array $payload
     *
     * @return array|WP_Error
     */
    public function create_barcode( array $payload ) {
        $response = $this->request(
            'POST',
            '/barcodecmdapi/createbarcode',
            array(
                'body' => wp_json_encode( $payload ),
            ),
            true
        );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $data = json_decode( wp_remote_retrieve_body( $response ), true );
        if ( ! is_array( $data ) ) {
            return new WP_Error( 'mng_invalid_barcode_response', __( 'MNG Kargo barkod cevabı geçersiz.', 'mng-kargo-shipping' ) );
        }

        return $data;
    }

    /**
     * Tek bir barkod için gönderi bilgisini getirir.
     *
     * @param string $barcode
     *
     * @return array|WP_Error
     */
    public function track_shipment( $barcode ) {
        $barcode  = sanitize_text_field( $barcode );
        $response = $this->request(
            'GET',
            '/plusqueryapi/getShipmentByBarcode/' . rawurlencode( $barcode ),
            array(),
            true
        );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $data = json_decode( wp_remote_retrieve_body( $response ), true );
        if ( ! is_array( $data ) ) {
            return new WP_Error( 'mng_invalid_track_response', __( 'MNG Kargo takip cevabı geçersiz.', 'mng-kargo-shipping' ) );
        }

        return $data;
    }

    /**
     * Şehir listesini getirir (CBS Info API).
     *
     * @return array|WP_Error
     */
    public function get_cities() {
        $response = $this->request(
            'GET',
            '/cbsinfoapi/getcities',
            array(),
            false // bu endpoint token gerektirmiyor
        );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $data = json_decode( wp_remote_retrieve_body( $response ), true );
        if ( ! is_array( $data ) ) {
            return new WP_Error( 'mng_invalid_cities_response', __( 'MNG Kargo şehir cevabı geçersiz.', 'mng-kargo-shipping' ) );
        }

        return $data;
    }

}

