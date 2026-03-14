<?php
/**
 * API client service.
 *
 * @package DEMW
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * API request layer with auth and environment routing.
 */
class DEMW_API_Client {
	/**
	 * Placeholder-safe endpoint paths based on sandbox docs.
	 */
	const PATH_IDENTITY_TOKEN     = '/mngapi/api/token';
	const PATH_TEST_CONNECTION    = '/mngapi/api/cbsinfoapi/getcities';
	const PATH_CREATE_SHIPMENT    = '/mngapi/api/pluscmdapi/createDetailedOrder';
	const PATH_STATUS_BY_BARCODE  = '/mngapi/api/plusqueryapi/getShipmentByBarcode/%s';
	const PATH_STATUS_BY_SHIPMENT = ''; // TODO: set after official status-by-shipment-id endpoint is confirmed.
	const PATH_LABEL_ENDPOINT     = ''; // TODO: set after official label endpoint is confirmed.

	/**
	 * Settings.
	 *
	 * @var DEMW_Settings
	 */
	private $settings;

	/**
	 * Auth resolver.
	 *
	 * @var DEMW_Auth
	 */
	private $auth;

	/**
	 * Logger service.
	 *
	 * @var DEMW_Logger
	 */
	private $logger;

	/**
	 * Cached token for current request cycle.
	 *
	 * @var string
	 */
	private $token_cache = '';

	/**
	 * Last request/response exchange.
	 *
	 * @var array<string,mixed>
	 */
	private $last_exchange = array();

	/**
	 * Constructor.
	 *
	 * @param DEMW_Settings $settings Settings.
	 * @param DEMW_Auth     $auth Auth resolver.
	 * @param DEMW_Logger   $logger Logger.
	 */
	public function __construct( DEMW_Settings $settings, DEMW_Auth $auth, DEMW_Logger $logger ) {
		$this->settings = $settings;
		$this->auth     = $auth;
		$this->logger   = $logger;
	}

	/**
	 * Expose last request/response for order meta diagnostics.
	 *
	 * @return array<string,mixed>
	 */
	public function get_last_exchange() {
		return $this->last_exchange;
	}

	/**
	 * Test API connectivity with selected auth model.
	 *
	 * @return array<string,mixed>|WP_Error
	 */
	public function test_connection() {
		$auth_type = (string) $this->settings->get( 'auth_type', 'api_key_secret' );

		if ( 'username_password' === $auth_type ) {
			$token = $this->generate_token();
			if ( is_wp_error( $token ) ) {
				return $token;
			}

			return array(
				'success' => true,
				'message' => __( 'Token generated successfully.', 'dhl-ecommerce-mng-woocommerce' ),
			);
		}

		$result = $this->request( 'GET', self::PATH_TEST_CONNECTION );
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return array(
			'success' => true,
			'message' => __( 'API endpoint responded successfully.', 'dhl-ecommerce-mng-woocommerce' ),
			'status'  => $result['status'],
		);
	}

	/**
	 * Create shipment in active environment.
	 *
	 * @param array<string,mixed> $payload Shipment payload.
	 * @return array<string,mixed>|WP_Error
	 */
	public function create_shipment( $payload ) {
		return $this->request( 'POST', self::PATH_CREATE_SHIPMENT, $payload );
	}

	/**
	 * Query shipment status using tracking number or shipment id.
	 *
	 * @param string $shipment_id Shipment id.
	 * @param string $tracking_number Tracking number.
	 * @return array<string,mixed>|WP_Error
	 */
	public function query_shipment_status( $shipment_id, $tracking_number ) {
		$tracking_number = trim( (string) $tracking_number );
		$shipment_id     = trim( (string) $shipment_id );

		if ( '' !== $tracking_number ) {
			$path = sprintf( self::PATH_STATUS_BY_BARCODE, rawurlencode( $tracking_number ) );
			return $this->request( 'GET', $path );
		}

		if ( '' === $shipment_id ) {
			return new WP_Error( 'demw_missing_shipment_reference', __( 'Shipment ID or tracking number is required.', 'dhl-ecommerce-mng-woocommerce' ) );
		}

		if ( '' === self::PATH_STATUS_BY_SHIPMENT ) {
			return new WP_Error( 'demw_status_endpoint_not_configured', __( 'Status-by-shipment-id endpoint is not configured yet. Please use tracking number or update endpoint mapping.', 'dhl-ecommerce-mng-woocommerce' ) );
		}

		return $this->request( 'POST', self::PATH_STATUS_BY_SHIPMENT, array( 'shipmentId' => $shipment_id ) );
	}

	/**
	 * Label retrieval placeholder.
	 *
	 * @param string $shipment_id Shipment id.
	 * @param string $tracking_number Tracking number.
	 * @return array<string,mixed>|WP_Error
	 */
	public function get_label( $shipment_id, $tracking_number ) {
		if ( '' === self::PATH_LABEL_ENDPOINT ) {
			return new WP_Error(
				'demw_label_endpoint_not_configured',
				__( 'Label endpoint is not configured yet. Add official endpoint and response mapping after API confirmation.', 'dhl-ecommerce-mng-woocommerce' )
			);
		}

		return $this->request(
			'POST',
			self::PATH_LABEL_ENDPOINT,
			array(
				'shipmentId' => (string) $shipment_id,
				'barcode'    => (string) $tracking_number,
			)
		);
	}

	/**
	 * Build and send API request.
	 *
	 * @param string              $method HTTP method.
	 * @param string              $path Relative endpoint path.
	 * @param array<string,mixed> $body Request body.
	 * @param array<string,string> $extra_headers Extra headers.
	 * @return array<string,mixed>|WP_Error
	 */
	public function request( $method, $path, $body = array(), $extra_headers = array() ) {
		$base_url = $this->get_active_base_url();
		if ( is_wp_error( $base_url ) ) {
			return $base_url;
		}

		$auth_headers = $this->auth->get_headers();
		if ( is_wp_error( $auth_headers ) ) {
			return $auth_headers;
		}

		$method = strtoupper( (string) $method );
		$url    = untrailingslashit( $base_url ) . '/' . ltrim( (string) $path, '/' );
		$headers = array_merge(
			array(
				'Accept'       => 'application/json',
				'Content-Type' => 'application/json',
			),
			$auth_headers,
			$extra_headers
		);

		$auth_type = (string) $this->settings->get( 'auth_type', 'api_key_secret' );
		if ( 'username_password' === $auth_type && self::PATH_IDENTITY_TOKEN !== $path ) {
			$token = $this->generate_token();
			if ( is_wp_error( $token ) ) {
				return $token;
			}
			$headers['Authorization'] = 'Bearer ' . $token;
		}

		$args = array(
			'method'  => $method,
			'timeout' => (int) $this->settings->get( 'timeout', 20 ),
			'headers' => $headers,
		);

		if ( in_array( $method, array( 'POST', 'PUT', 'PATCH', 'DELETE' ), true ) && ! empty( $body ) ) {
			$args['body'] = wp_json_encode( $body );
		}

		if ( $this->settings->is_debug_enabled() ) {
			$this->logger->request( $url, $headers, $body, $this->settings->should_log_request_body() );
		}

		$response = wp_remote_request( $url, $args );
		if ( is_wp_error( $response ) ) {
			$this->last_exchange = array(
				'request_url'  => $url,
				'request_body' => $body,
				'error'        => $response->get_error_message(),
			);
			return new WP_Error( 'demw_http_error', sprintf( __( 'Request failed: %s', 'dhl-ecommerce-mng-woocommerce' ), $response->get_error_message() ) );
		}

		$status       = (int) wp_remote_retrieve_response_code( $response );
		$raw_body     = (string) wp_remote_retrieve_body( $response );
		$decoded_body = null;

		if ( '' !== $raw_body ) {
			$decoded_body = json_decode( $raw_body, true );
			if ( JSON_ERROR_NONE !== json_last_error() ) {
				$decoded_body = null;
			}
		}

		$this->last_exchange = array(
			'request_url'    => $url,
			'request_body'   => $body,
			'response_code'  => $status,
			'response_body'  => $decoded_body ? $decoded_body : $raw_body,
		);

		if ( $this->settings->is_debug_enabled() ) {
			$this->logger->response( $url, $status, $decoded_body ? $decoded_body : $raw_body, $this->settings->should_log_response_body() );
		}

		if ( $status < 200 || $status >= 300 ) {
			$message = __( 'API returned an error response.', 'dhl-ecommerce-mng-woocommerce' );
			if ( is_array( $decoded_body ) ) {
				$message = (string) ( $decoded_body['detail'] ?? $decoded_body['title'] ?? $message );
			}
			return new WP_Error( 'demw_api_error', $message, array( 'status' => $status, 'body' => $decoded_body ? $decoded_body : $raw_body ) );
		}

		return array(
			'status' => $status,
			'body'   => $raw_body,
			'data'   => is_array( $decoded_body ) ? $decoded_body : $raw_body,
		);
	}

	/**
	 * Resolve active environment base URL.
	 *
	 * @return string|WP_Error
	 */
	private function get_active_base_url() {
		$environment = (string) $this->settings->get( 'environment', 'sandbox' );
		$key         = 'sandbox' === $environment ? 'sandbox_base_url' : 'production_base_url';
		$base_url    = DEMW_Helpers::sanitize_base_url( (string) $this->settings->get( $key, '' ) );

		if ( '' === $base_url ) {
			return new WP_Error( 'demw_missing_base_url', __( 'Base URL for selected environment is not configured.', 'dhl-ecommerce-mng-woocommerce' ) );
		}

		return $base_url;
	}

	/**
	 * Generate and cache JWT token.
	 *
	 * @return string|WP_Error
	 */
	private function generate_token() {
		if ( '' !== $this->token_cache ) {
			return $this->token_cache;
		}

		$username = (string) $this->settings->get( 'username', '' );
		$password = (string) $this->settings->get( 'password', '' );
		if ( '' === $username || '' === $password ) {
			return new WP_Error( 'demw_missing_username_password', __( 'Username and password are required to generate token.', 'dhl-ecommerce-mng-woocommerce' ) );
		}

		$auth_headers = $this->auth->get_headers();
		if ( is_wp_error( $auth_headers ) ) {
			return $auth_headers;
		}

		$response = $this->request(
			'POST',
			self::PATH_IDENTITY_TOKEN,
			array(
				'customerNumber' => $username,
				'password'       => $password,
				'identityType'   => 1,
			),
			$auth_headers
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$data = is_array( $response['data'] ) ? $response['data'] : array();
		$jwt  = isset( $data['jwt'] ) ? (string) $data['jwt'] : '';
		if ( '' === $jwt ) {
			return new WP_Error( 'demw_missing_jwt', __( 'Token endpoint succeeded but JWT was missing in response.', 'dhl-ecommerce-mng-woocommerce' ) );
		}

		$this->token_cache = $jwt;
		return $jwt;
	}
}
