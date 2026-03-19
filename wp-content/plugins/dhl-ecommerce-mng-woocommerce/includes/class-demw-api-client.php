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
	const API_BASE_PATH           = '/mngapi/api';
	const PATH_IDENTITY_TOKEN     = '/token';
	const PATH_TEST_CONNECTION    = '/cbsinfoapi/getcities';
	const PATH_CREATE_SHIPMENT    = '/pluscmdapi/createDetailedOrder';
	const PATH_CREATE_RECIPIENT   = '/pluscmdapi/createRecipient';
	const PATH_CREATE_ORDER       = '/standardcmdapi/createOrder';
	const PATH_CREATE_BARCODE     = '/barcodecmdapi/createbarcode';
	const PATH_UPDATE_SHIPMENT    = '/barcodecmdapi/updateshipment';
	const PATH_CANCEL_SHIPMENT    = '/barcodecmdapi/cancelshipment';
	const PATH_STATUS_BY_BARCODE  = '/plusqueryapi/getShipmentByBarcode/%s';
	const PATH_STATUS_BY_SHIPMENT = '/plusqueryapi/GetShipmentInfoByShipmentId/%s';
	const PATH_STD_GET_ORDER      = '/standardqueryapi/getorder/%s';
	const PATH_STD_GET_SHIPMENT   = '/standardqueryapi/getshipment/%s';
	const PATH_STD_GET_STATUS     = '/standardqueryapi/getshipmentstatus/%s';
	const PATH_STD_TRACK          = '/standardqueryapi/trackshipment/%s';
	const PATH_STD_CALCULATE      = '/standardqueryapi/calculate';
	const PATH_CBS_GET_CITIES     = '/cbsinfoapi/getcities';
	const PATH_CBS_GET_DISTRICTS  = '/cbsinfoapi/getdistricts/%s';
	const PATH_CBS_GET_NEIGHBORHOODS = '/cbsinfoapi/getneighborhoods/%s/%s';
	const PATH_CBS_GET_OUT_OF_SERVICE_AREAS = '/cbsinfoapi/getoutofserviceareas/%s/%s';
	const PATH_CBS_GET_MOBILE_AREAS = '/cbsinfoapi/getmobileareas/%s/%s';
	const PATH_LABEL_BY_BARCODE   = '/plusqueryapi/getShipmentByBarcode/%s';
	const PATH_LABEL_BY_SHIPMENT  = '/plusqueryapi/GetShipmentInfoByShipmentId/%s';

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

		/*
		 * For MNG docs-compliant test flow:
		 * 1) If username/password are present, test token generation on /token first.
		 * 2) Then test a lightweight protected endpoint with Bearer.
		 */
		$username = (string) $this->settings->get( 'username', '' );
		$password = (string) $this->settings->get( 'password', '' );
		if ( '' !== $username && '' !== $password ) {
			$token = $this->generate_token();
			if ( is_wp_error( $token ) ) {
				return $token;
			}

			$result = $this->request( 'GET', self::PATH_TEST_CONNECTION );
			if ( is_wp_error( $result ) ) {
				return $result;
			}

			return array(
				'success' => true,
				'message' => __( 'Token generated and protected endpoint responded successfully.', 'dhl-ecommerce-mng-woocommerce' ),
				'status'  => $result['status'],
			);
		}

		/*
		 * If no username/password exist, only bearer-token mode can hit protected endpoints.
		 * For API key/secret mode, return a clear guidance error instead of generic authorization failure.
		 */
		if ( in_array( $auth_type, array( 'api_key_secret', 'username_password' ), true ) ) {
			return new WP_Error(
				'demw_missing_token_credentials',
				__( 'MNG token test requires Username/Password (customerNumber/password) in addition to API Key/Secret. Please fill those fields or switch to Bearer Token auth.', 'dhl-ecommerce-mng-woocommerce' )
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
	 * Create recipient in Plus Command API.
	 *
	 * @param array<string,mixed> $payload Recipient payload.
	 * @return array<string,mixed>|WP_Error
	 */
	public function create_recipient( $payload ) {
		return $this->request( 'POST', self::PATH_CREATE_RECIPIENT, $payload );
	}

	/**
	 * Create order in Standard Command API.
	 *
	 * @param array<string,mixed> $payload Order payload.
	 * @return array<string,mixed>|WP_Error
	 */
	public function create_order( $payload ) {
		return $this->request( 'POST', self::PATH_CREATE_ORDER, $payload );
	}

	/**
	 * Create barcode shipment (Barcode Command API).
	 *
	 * @param array<string,mixed> $payload Barcode payload.
	 * @return array<string,mixed>|WP_Error
	 */
	public function create_barcode( $payload ) {
		return $this->request( 'POST', self::PATH_CREATE_BARCODE, $payload );
	}

	/**
	 * Update shipment (Barcode Command API).
	 *
	 * @param array<string,mixed> $payload Update payload.
	 * @return array<string,mixed>|WP_Error
	 */
	public function update_shipment( $payload ) {
		return $this->request( 'PUT', self::PATH_UPDATE_SHIPMENT, $payload );
	}

	/**
	 * Cancel shipment (Barcode Command API).
	 *
	 * @param array<string,mixed> $payload Cancel payload.
	 * @return array<string,mixed>|WP_Error
	 */
	public function cancel_shipment( $payload ) {
		return $this->request( 'PUT', self::PATH_CANCEL_SHIPMENT, $payload );
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

		$path = sprintf( self::PATH_STATUS_BY_SHIPMENT, rawurlencode( $shipment_id ) );
		return $this->request( 'GET', $path );
	}

	/**
	 * Query shipment status from Standard Query by reference id.
	 *
	 * @param string $reference_id Reference id.
	 * @return array<string,mixed>|WP_Error
	 */
	public function query_shipment_status_by_reference( $reference_id ) {
		$reference_id = trim( (string) $reference_id );
		if ( '' === $reference_id ) {
			return new WP_Error( 'demw_missing_reference_id', __( 'Reference ID is required.', 'dhl-ecommerce-mng-woocommerce' ) );
		}

		$path = sprintf( self::PATH_STD_GET_STATUS, rawurlencode( $reference_id ) );
		return $this->request( 'GET', $path );
	}

	/**
	 * Get order detail from Standard Query by reference id.
	 *
	 * @param string $reference_id Reference id.
	 * @return array<string,mixed>|WP_Error
	 */
	public function get_order_by_reference( $reference_id ) {
		$reference_id = trim( (string) $reference_id );
		if ( '' === $reference_id ) {
			return new WP_Error( 'demw_missing_reference_id', __( 'Reference ID is required.', 'dhl-ecommerce-mng-woocommerce' ) );
		}

		$path = sprintf( self::PATH_STD_GET_ORDER, rawurlencode( $reference_id ) );
		return $this->request( 'GET', $path );
	}

	/**
	 * Get shipment detail from Standard Query by reference id.
	 *
	 * @param string $reference_id Reference id.
	 * @return array<string,mixed>|WP_Error
	 */
	public function get_shipment_by_reference( $reference_id ) {
		$reference_id = trim( (string) $reference_id );
		if ( '' === $reference_id ) {
			return new WP_Error( 'demw_missing_reference_id', __( 'Reference ID is required.', 'dhl-ecommerce-mng-woocommerce' ) );
		}

		$path = sprintf( self::PATH_STD_GET_SHIPMENT, rawurlencode( $reference_id ) );
		return $this->request( 'GET', $path );
	}

	/**
	 * Calculate transport price using Standard Query API.
	 *
	 * @param array<string,mixed> $payload Calculate request payload.
	 * @return array<string,mixed>|WP_Error
	 */
	public function calculate_transport_cost( $payload ) {
		return $this->request( 'POST', self::PATH_STD_CALCULATE, $payload );
	}

	/**
	 * Fetch available city list from CBS API.
	 *
	 * @return array<int,array<string,mixed>>|WP_Error
	 */
	public function get_cities() {
		$result = $this->request( 'GET', self::PATH_CBS_GET_CITIES );
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		$data = $result['data'];
		return is_array( $data ) ? $data : array();
	}

	/**
	 * Fetch district list by city code from CBS API.
	 *
	 * @param string $city_code City code.
	 * @return array<int,array<string,mixed>>|WP_Error
	 */
	public function get_districts( $city_code ) {
		$city_code = trim( (string) $city_code );
		if ( '' === $city_code ) {
			return new WP_Error( 'demw_missing_city_code', __( 'City code is required to fetch districts.', 'dhl-ecommerce-mng-woocommerce' ) );
		}

		$path   = sprintf( self::PATH_CBS_GET_DISTRICTS, rawurlencode( $city_code ) );
		$result = $this->request( 'GET', $path );
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		$data = $result['data'];
		return is_array( $data ) ? $data : array();
	}

	/**
	 * Fetch neighborhoods by city and district codes.
	 *
	 * @param string $city_code City code.
	 * @param string $district_code District code.
	 * @return array<int,array<string,mixed>>|WP_Error
	 */
	public function get_neighborhoods( $city_code, $district_code ) {
		$city_code     = trim( (string) $city_code );
		$district_code = trim( (string) $district_code );
		if ( '' === $city_code || '' === $district_code ) {
			return new WP_Error( 'demw_missing_location_codes', __( 'City and district codes are required to fetch neighborhoods.', 'dhl-ecommerce-mng-woocommerce' ) );
		}

		$path = sprintf( self::PATH_CBS_GET_NEIGHBORHOODS, rawurlencode( $city_code ), rawurlencode( $district_code ) );
		$result = $this->request( 'GET', $path );
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		$data = $result['data'];
		return is_array( $data ) ? $data : array();
	}

	/**
	 * Fetch out-of-service neighborhoods by city and district codes.
	 *
	 * @param string $city_code City code.
	 * @param string $district_code District code.
	 * @return array<int,array<string,mixed>>|WP_Error
	 */
	public function get_out_of_service_areas( $city_code, $district_code ) {
		$city_code     = trim( (string) $city_code );
		$district_code = trim( (string) $district_code );
		if ( '' === $city_code || '' === $district_code ) {
			return new WP_Error( 'demw_missing_location_codes', __( 'City and district codes are required to fetch out of service areas.', 'dhl-ecommerce-mng-woocommerce' ) );
		}

		$path = sprintf( self::PATH_CBS_GET_OUT_OF_SERVICE_AREAS, rawurlencode( $city_code ), rawurlencode( $district_code ) );
		$result = $this->request( 'GET', $path );
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		$data = $result['data'];
		return is_array( $data ) ? $data : array();
	}

	/**
	 * Fetch mobile neighborhoods by city and district codes.
	 *
	 * @param string $city_code City code.
	 * @param string $district_code District code.
	 * @return array<int,array<string,mixed>>|WP_Error
	 */
	public function get_mobile_areas( $city_code, $district_code ) {
		$city_code     = trim( (string) $city_code );
		$district_code = trim( (string) $district_code );
		if ( '' === $city_code || '' === $district_code ) {
			return new WP_Error( 'demw_missing_location_codes', __( 'City and district codes are required to fetch mobile areas.', 'dhl-ecommerce-mng-woocommerce' ) );
		}

		$path = sprintf( self::PATH_CBS_GET_MOBILE_AREAS, rawurlencode( $city_code ), rawurlencode( $district_code ) );
		$result = $this->request( 'GET', $path );
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		$data = $result['data'];
		return is_array( $data ) ? $data : array();
	}

	/**
	 * Retrieve shipment label/follow data from official Plus Query endpoints.
	 *
	 * DHL eCommerce MNG docs do not expose a dedicated "get label" endpoint.
	 * The closest official source is shipment query payload that can include
	 * follow URL and cargo barcode values.
	 *
	 * @param string $shipment_id Shipment id.
	 * @param string $tracking_number Tracking number.
	 * @param string $reference_id Reference id.
	 * @return array<string,mixed>|WP_Error
	 */
	public function get_label( $shipment_id, $tracking_number, $reference_id = '' ) {
		$tracking_number = trim( (string) $tracking_number );
		$shipment_id     = trim( (string) $shipment_id );
		$reference_id    = trim( (string) $reference_id );

		// Preferred flow for new scenario: query by reference id.
		if ( '' !== $reference_id ) {
			$path = sprintf( self::PATH_STD_GET_SHIPMENT, rawurlencode( $reference_id ) );
			$result = $this->request( 'GET', $path );
			if ( ! is_wp_error( $result ) ) {
				$normalized = $this->normalize_label_response( $result['data'] );
				$this->last_exchange['response_body'] = $normalized;
				$result['data'] = $normalized;
				return $result;
			}
		}

		if ( '' === $tracking_number && '' === $shipment_id ) {
			return new WP_Error(
				'demw_missing_shipment_reference',
				__( 'Shipment ID or tracking number is required.', 'dhl-ecommerce-mng-woocommerce' )
			);
		}

		$path = '';
		if ( '' !== $tracking_number ) {
			$path = sprintf( self::PATH_LABEL_BY_BARCODE, rawurlencode( $tracking_number ) );
		} else {
			$path = sprintf( self::PATH_LABEL_BY_SHIPMENT, rawurlencode( $shipment_id ) );
		}

		$result = $this->request( 'GET', $path );
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		$normalized = $this->normalize_label_response( $result['data'] );
		$this->last_exchange['response_body'] = $normalized;
		$result['data'] = $normalized;
		return $result;
	}

	/**
	 * Normalize Plus Query response to label-like structure.
	 *
	 * @param mixed $data Raw response data.
	 * @return array<string,mixed>
	 */
	private function normalize_label_response( $data ) {
		$source = is_array( $data ) ? $data : array();

		// Some endpoints return an array with one object.
		$root = $source;
		if ( isset( $source[0] ) && is_array( $source[0] ) ) {
			$root = $source[0];
		}

		$label_url = '';
		if ( isset( $root['shipmentFollowUrl'] ) && is_string( $root['shipmentFollowUrl'] ) ) {
			$label_url = (string) $root['shipmentFollowUrl'];
		}
		if ( '' === $label_url && isset( $root['shipment']['shipmentFollowUrl'] ) && is_string( $root['shipment']['shipmentFollowUrl'] ) ) {
			$label_url = (string) $root['shipment']['shipmentFollowUrl'];
		}

		$cargo_barcode = '';
		if ( isset( $root['cargoBarcode'] ) && is_string( $root['cargoBarcode'] ) ) {
			$cargo_barcode = (string) $root['cargoBarcode'];
		}
		if ( '' === $cargo_barcode && isset( $root['shipment']['cargoBarcode'] ) && is_string( $root['shipment']['cargoBarcode'] ) ) {
			$cargo_barcode = (string) $root['shipment']['cargoBarcode'];
		}

		$shipment_id = '';
		if ( isset( $root['shipmentId'] ) && ( is_string( $root['shipmentId'] ) || is_numeric( $root['shipmentId'] ) ) ) {
			$shipment_id = (string) $root['shipmentId'];
		}
		if ( '' === $shipment_id && isset( $root['shipment']['shipmentId'] ) && ( is_string( $root['shipment']['shipmentId'] ) || is_numeric( $root['shipment']['shipmentId'] ) ) ) {
			$shipment_id = (string) $root['shipment']['shipmentId'];
		}

		$tracking_number = '';
		if ( isset( $root['barcode'] ) && is_string( $root['barcode'] ) ) {
			$tracking_number = (string) $root['barcode'];
		}
		if ( '' === $tracking_number && isset( $root['shipmentPieceList'][0]['barcode'] ) && is_string( $root['shipmentPieceList'][0]['barcode'] ) ) {
			$tracking_number = (string) $root['shipmentPieceList'][0]['barcode'];
		}

		return array(
			'labelUrl'        => $label_url,
			'trackingUrl'     => $label_url,
			'cargoBarcode'    => $cargo_barcode,
			'shipmentId'      => $shipment_id,
			'trackingNumber'  => $tracking_number,
			'raw'             => $source,
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
		$api_version = (string) $this->settings->get( 'api_version', '' );
		if ( '' !== $api_version ) {
			$headers['x-api-version'] = $api_version;
		}

		$auth_type = (string) $this->settings->get( 'auth_type', 'api_key_secret' );
		if ( 'username_password' === $auth_type && self::PATH_IDENTITY_TOKEN !== $path ) {
			$token = $this->generate_token();
			if ( is_wp_error( $token ) ) {
				return $token;
			}
			$headers['Authorization'] = 'Bearer ' . $token;
		}

		if ( self::PATH_IDENTITY_TOKEN !== $path && $this->endpoint_requires_bearer( $path ) && ! isset( $headers['Authorization'] ) ) {
			$token = $this->maybe_get_bearer_token_for_mng();
			if ( is_wp_error( $token ) ) {
				return $token;
			}
			if ( '' !== $token ) {
				$headers['Authorization'] = 'Bearer ' . $token;
			}
		}

		if ( self::PATH_IDENTITY_TOKEN !== $path && $this->endpoint_requires_bearer( $path ) && empty( $headers['Authorization'] ) ) {
			return new WP_Error(
				'demw_missing_authorization',
				__( 'Authorization token is required for this endpoint. Configure Bearer Token or Username/Password + API Key/Secret.', 'dhl-ecommerce-mng-woocommerce' )
			);
		}

		$args = array(
			'method'  => $method,
			'timeout' => (int) $this->settings->get( 'timeout', 45 ),
			'headers' => $headers,
		);

		if ( in_array( $method, array( 'POST', 'PUT', 'PATCH', 'DELETE' ), true ) && ! empty( $body ) ) {
			$args['body'] = wp_json_encode( $body );
		}

		if ( $this->settings->is_debug_enabled() ) {
			$this->logger->request( $url, $headers, $body, $this->settings->should_log_request_body() );
		}

		$response = $this->perform_request_with_optional_retry( $url, $args, $method, $path, $body );
		if ( is_wp_error( $response ) ) {
			$error_message = $response->get_error_message();
			$user_message  = sprintf( __( 'Request failed: %s', 'dhl-ecommerce-mng-woocommerce' ), $error_message );
			if ( false !== stripos( $error_message, 'timed out' ) ) {
				$user_message = sprintf(
					__( 'Request timed out. Increase timeout setting (recommended 60-90 seconds) or check network/firewall access to carrier API. Raw error: %s', 'dhl-ecommerce-mng-woocommerce' ),
					$error_message
				);
			}

			$this->last_exchange = array(
				'request_url'  => $url,
				'request_body' => $body,
				'error'        => $error_message,
			);
			return new WP_Error( 'demw_http_error', $user_message );
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
			$parsed = $this->build_api_error(
				$status,
				is_array( $decoded_body ) ? $decoded_body : array(),
				$raw_body,
				$path
			);
			return new WP_Error(
				$parsed['code'],
				$parsed['message'],
				array(
					'status' => $status,
					'body'   => $decoded_body ? $decoded_body : $raw_body,
				)
			);
		}

		return array(
			'status' => $status,
			'body'   => $raw_body,
			'data'   => is_array( $decoded_body ) ? $decoded_body : $raw_body,
		);
	}

	/**
	 * Perform HTTP request and retry once for timeout on safe endpoints.
	 *
	 * @param string              $url Request url.
	 * @param array<string,mixed> $args Request args.
	 * @param string              $method HTTP method.
	 * @param string              $path Endpoint path.
	 * @param array<string,mixed> $body Request body for logging.
	 * @return array<string,mixed>|WP_Error
	 */
	private function perform_request_with_optional_retry( $url, $args, $method, $path, $body ) {
		$response = wp_remote_request( $url, $args );
		if ( ! is_wp_error( $response ) ) {
			return $response;
		}

		$error_message = (string) $response->get_error_message();
		if ( ! $this->should_retry_timeout_request( $method, $path, $error_message ) ) {
			return $response;
		}

		$retry_args            = $args;
		$retry_args['timeout'] = min( 180, (int) $args['timeout'] + 10 );

		if ( $this->settings->is_debug_enabled() ) {
			$this->logger->error(
				'Retrying timeout request once',
				array(
					'url'            => $url,
					'path'           => $path,
					'method'         => $method,
					'initial_timeout'=> (int) $args['timeout'],
					'retry_timeout'  => (int) $retry_args['timeout'],
					'error'          => $error_message,
					'request_body'   => $body,
				)
			);
		}

		return wp_remote_request( $url, $retry_args );
	}

	/**
	 * Determine if request should be retried after timeout.
	 *
	 * Retry is enabled only for read-only calls and calculate endpoint.
	 * Shipment creation calls are excluded to avoid duplicate records.
	 *
	 * @param string $method HTTP method.
	 * @param string $path Endpoint path.
	 * @param string $error_message Error message.
	 * @return bool
	 */
	private function should_retry_timeout_request( $method, $path, $error_message ) {
		if ( false === stripos( $error_message, 'timed out' ) ) {
			return false;
		}

		$method = strtoupper( (string) $method );
		if ( 'POST' === $method && self::PATH_STD_CALCULATE === $path ) {
			return true;
		}

		if ( 'GET' !== $method ) {
			return false;
		}

		$retryable_get_paths = array(
			self::PATH_CBS_GET_CITIES,
			'/cbsinfoapi/getdistricts/',
		);

		foreach ( $retryable_get_paths as $prefix ) {
			if ( 0 === strpos( (string) $path, $prefix ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Resolve active environment base URL.
	 *
	 * @return string|WP_Error
	 */
	private function get_active_base_url() {
		$environment = (string) $this->settings->get( 'environment', 'development' );
		if ( 'sandbox' === $environment ) {
			$environment = 'development';
		}

		$key = 'development' === $environment ? 'development_base_url' : 'production_base_url';
		$base_url = DEMW_Helpers::sanitize_base_url( (string) $this->settings->get( $key, '' ) );

		// Backward compatibility for older saved key name.
		if ( '' === $base_url && 'development' === $environment ) {
			$base_url = DEMW_Helpers::sanitize_base_url( (string) $this->settings->get( 'sandbox_base_url', '' ) );
		}

		if ( '' === $base_url ) {
			return new WP_Error( 'demw_missing_base_url', __( 'Base URL for selected environment is not configured.', 'dhl-ecommerce-mng-woocommerce' ) );
		}

		return $this->normalize_base_url( $base_url );
	}

	/**
	 * Normalize configured base URL so both forms work:
	 * - https://testapi.mngkargo.com.tr
	 * - https://testapi.mngkargo.com.tr/mngapi/api
	 *
	 * @param string $base_url Configured URL.
	 * @return string
	 */
	private function normalize_base_url( $base_url ) {
		$parts = wp_parse_url( $base_url );
		$path  = isset( $parts['path'] ) ? untrailingslashit( (string) $parts['path'] ) : '';
		$api_path = self::API_BASE_PATH;

		if ( '' === $path ) {
			return untrailingslashit( $base_url ) . $api_path;
		}

		if ( 0 === strpos( $path, $api_path ) || false !== strpos( $path, $api_path ) ) {
			return untrailingslashit( $base_url );
		}

		return untrailingslashit( $base_url ) . $api_path;
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
		if ( ! ctype_digit( $username ) ) {
			return new WP_Error(
				'demw_invalid_customer_number',
				__( 'customerNumber must be numeric (Int64) for MNG Identity API token request. Please enter your numeric customer number.', 'dhl-ecommerce-mng-woocommerce' )
			);
		}

		$auth_headers = $this->auth->get_headers();
		if ( is_wp_error( $auth_headers ) ) {
			return $auth_headers;
		}

		$response = $this->request(
			'POST',
			self::PATH_IDENTITY_TOKEN,
			array(
				'customerNumber' => (int) $username,
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

	/**
	 * Determine if endpoint requires Bearer token according to MNG docs.
	 *
	 * @param string $path Endpoint path.
	 * @return bool
	 */
	private function endpoint_requires_bearer( $path ) {
		return true;
	}

	/**
	 * Try to obtain a bearer token for documented MNG endpoints.
	 *
	 * @return string|WP_Error
	 */
	private function maybe_get_bearer_token_for_mng() {
		$auth_type = (string) $this->settings->get( 'auth_type', 'api_key_secret' );
		$bearer    = (string) $this->settings->get( 'bearer_token', '' );

		if ( '' !== $bearer ) {
			return $bearer;
		}

		$username = (string) $this->settings->get( 'username', '' );
		$password = (string) $this->settings->get( 'password', '' );
		if ( in_array( $auth_type, array( 'username_password', 'api_key_secret' ), true ) && '' !== $username && '' !== $password ) {
			return $this->generate_token();
		}

		return '';
	}

	/**
	 * Build human-friendly API error details from response body.
	 *
	 * @param int                  $status HTTP status code.
	 * @param array<string,mixed>  $decoded_body Decoded body.
	 * @param string               $raw_body Raw body.
	 * @param string               $path Endpoint path.
	 * @return array{code:string,message:string}
	 */
	private function build_api_error( $status, $decoded_body, $raw_body, $path ) {
		$http_code         = isset( $decoded_body['httpCode'] ) ? (string) $decoded_body['httpCode'] : '';
		$http_message      = isset( $decoded_body['httpMessage'] ) ? (string) $decoded_body['httpMessage'] : '';
		$more_information  = isset( $decoded_body['moreInformation'] ) ? (string) $decoded_body['moreInformation'] : '';
		$problem_detail    = isset( $decoded_body['detail'] ) ? (string) $decoded_body['detail'] : '';
		$problem_title     = isset( $decoded_body['title'] ) ? (string) $decoded_body['title'] : '';
		$nested_error      = isset( $decoded_body['error'] ) && is_array( $decoded_body['error'] ) ? $decoded_body['error'] : array();
		$nested_code       = isset( $nested_error['code'] ) ? (string) $nested_error['code'] : ( isset( $nested_error['Code'] ) ? (string) $nested_error['Code'] : '' );
		$nested_message    = isset( $nested_error['message'] ) ? (string) $nested_error['message'] : ( isset( $nested_error['Message'] ) ? (string) $nested_error['Message'] : '' );
		$nested_desc       = isset( $nested_error['description'] ) ? (string) $nested_error['description'] : ( isset( $nested_error['Description'] ) ? (string) $nested_error['Description'] : '' );

		// IBM API Connect / MNG style unauthorized body.
		if ( 401 === (int) $status || '401' === $http_code ) {
			$combined = trim( $http_message . ' ' . $more_information );
			$combined = '' !== $combined ? $combined : trim( $problem_title . ' ' . $problem_detail );

			if ( false !== stripos( $more_information, 'valid subscription' ) ) {
				return array(
					'code'    => 'demw_subscription_not_found',
					'message' => __(
						'Unauthorized (Subscription): API credentials are valid format but no active API subscription/plan was found for this endpoint. Verify that your app is subscribed to identity-api / related product in the same catalog/environment as the provided client credentials.',
						'dhl-ecommerce-mng-woocommerce'
					),
				);
			}

			if ( self::PATH_IDENTITY_TOKEN === $path ) {
				return array(
					'code'    => 'demw_token_unauthorized',
					'message' => sprintf(
						/* translators: %s: response detail */
						__( 'Token request unauthorized. Check API Key/Secret subscription and customerNumber/password values. Gateway says: %s', 'dhl-ecommerce-mng-woocommerce' ),
						'' !== $combined ? $combined : __( 'Unauthorized', 'dhl-ecommerce-mng-woocommerce' )
					),
				);
			}

			return array(
				'code'    => 'demw_endpoint_unauthorized',
				'message' => sprintf(
					/* translators: %s: response detail */
					__( 'Endpoint unauthorized. Ensure JWT token generation succeeds and Authorization header is accepted for this API. Gateway says: %s', 'dhl-ecommerce-mng-woocommerce' ),
					'' !== $combined ? $combined : __( 'Unauthorized', 'dhl-ecommerce-mng-woocommerce' )
				),
			);
		}

		if ( 400 === (int) $status ) {
			$detail = '' !== $problem_detail ? $problem_detail : ( '' !== $problem_title ? $problem_title : $raw_body );
			return array(
				'code'    => 'demw_bad_request',
				'message' => sprintf(
					/* translators: %s: server detail */
					__( 'Bad request (400). Payload or required fields may be invalid. Server detail: %s', 'dhl-ecommerce-mng-woocommerce' ),
					'' !== $detail ? $detail : __( 'No detail provided', 'dhl-ecommerce-mng-woocommerce' )
				),
			);
		}

		if ( 404 === (int) $status ) {
			return array(
				'code'    => 'demw_not_found',
				'message' => __( 'Resource not found (404). Shipment ID / barcode may be incorrect or not yet available in carrier system.', 'dhl-ecommerce-mng-woocommerce' ),
			);
		}

		if ( 500 <= (int) $status ) {
			if ( self::PATH_IDENTITY_TOKEN === $path && '' !== $nested_code ) {
				return array(
					'code'    => 'demw_token_server_error',
					'message' => sprintf(
						/* translators: 1: error code, 2: message */
						__( 'Token endpoint returned server error (%1$s). This commonly indicates invalid customerNumber/password for the subscribed app, or account not enabled for token issuance. API message: %2$s', 'dhl-ecommerce-mng-woocommerce' ),
						$nested_code,
						'' !== $nested_desc ? $nested_desc : ( '' !== $nested_message ? $nested_message : __( 'No detail provided', 'dhl-ecommerce-mng-woocommerce' ) )
					),
				);
			}

			if ( '20011' === $nested_code || false !== stripos( $nested_desc, 'tek barkod yetkiniz yoktur' ) ) {
				return array(
					'code'    => 'demw_single_barcode_not_authorized',
					'message' => __(
						'Barcode generation is blocked by carrier authorization: your public IP is not authorized for single barcode operations (Code: 20011). Ask MNG/DHL eCommerce to whitelist this IP for barcode-command createbarcode.',
						'dhl-ecommerce-mng-woocommerce'
					),
				);
			}

			if (
				'20001' === $nested_code
				|| false !== stripos( $nested_desc, 'VARIŞ ŞUBESİ BULUNAMADI' )
				|| false !== stripos( $nested_desc, 'VARIS SUBESI BULUNAMADI' )
				|| false !== stripos( $nested_desc, 'SUBENIN ILI BULUNAMADI' )
			) {
				return array(
					'code'    => 'demw_destination_branch_not_found',
					'message' => __(
						'Destination branch is not resolved yet for this order (Code: 20001). Wait a short time and retry barcode generation. If it repeats, verify recipient address and district fields (il/ilce) in the order. X-Branch-Code may be empty.',
						'dhl-ecommerce-mng-woocommerce'
					),
				);
			}

			if ( '26060' === $nested_code || false !== stripos( $nested_desc, 'CustomerId' ) ) {
				return array(
					'code'    => 'demw_recipient_customerid_fullname_conflict',
					'message' => __(
						'Recipient validation failed (26060): when recipient customerId is provided, fullName must be empty. Keep customerId empty for retail orders and send fullName + phone + address fields.',
						'dhl-ecommerce-mng-woocommerce'
					),
				);
			}

			if ( '' !== $nested_code ) {
				$hint = __(
					'Check barcode payload values (referenceId, billOfLandingId, packagingType, orderPieceList) and uniqueness constraints.',
					'dhl-ecommerce-mng-woocommerce'
				);
				if ( self::PATH_CREATE_RECIPIENT === $path ) {
					$hint = __(
						'Check createRecipient payload shape and fields. Payload must be wrapped as {"recipient": {...}} and recipient phone/city/district values should be valid.',
						'dhl-ecommerce-mng-woocommerce'
					);
				} elseif ( self::PATH_CREATE_ORDER === $path ) {
					$hint = __(
						'Check createOrder payload fields, especially order.referenceId/barcode uppercase consistency, recipient fields, and marketplace parameters for TRND/N11 orders.',
						'dhl-ecommerce-mng-woocommerce'
					);
				}

				return array(
					'code'    => 'demw_carrier_business_error',
					'message' => sprintf(
						/* translators: 1: error code, 2: detail */
						__(
							'Carrier API business validation error (%1$s). The request reached MNG but was rejected by domain rules. %3$s API message: %2$s',
							'dhl-ecommerce-mng-woocommerce'
						),
						$nested_code,
						'' !== $nested_desc ? $nested_desc : ( '' !== $nested_message ? $nested_message : __( 'No detail provided', 'dhl-ecommerce-mng-woocommerce' ) ),
						$hint
					),
				);
			}

			return array(
				'code'    => 'demw_server_error',
				'message' => __( 'Carrier API server error. Please retry later and review debug logs for technical details.', 'dhl-ecommerce-mng-woocommerce' ),
			);
		}

		$fallback = __( 'API returned an error response.', 'dhl-ecommerce-mng-woocommerce' );
		if ( is_array( $decoded_body ) ) {
			$fallback = (string) ( $decoded_body['detail'] ?? $decoded_body['title'] ?? $fallback );
		}

		return array(
			'code'    => 'demw_api_error',
			'message' => $fallback,
		);
	}
}
