<?php
/**
 * Authentication resolver.
 *
 * @package DEMW
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Build request headers for selected auth mode.
 */
class DEMW_Auth {
	/**
	 * Settings.
	 *
	 * @var DEMW_Settings
	 */
	private $settings;

	/**
	 * Constructor.
	 *
	 * @param DEMW_Settings $settings Settings service.
	 */
	public function __construct( DEMW_Settings $settings ) {
		$this->settings = $settings;
	}

	/**
	 * Build auth headers for selected mode.
	 *
	 * @return array<string,string>|WP_Error
	 */
	public function get_headers() {
		$auth_type = (string) $this->settings->get( 'auth_type', 'api_key_secret' );
		$headers   = array();

		// Optional identifiers that some tenant setups require.
		$customer_code = (string) $this->settings->get( 'customer_code', '' );
		$branch_code   = (string) $this->settings->get( 'branch_code', '' );
		if ( '' !== $customer_code ) {
			$headers['X-Customer-Code'] = $customer_code;
		}
		if ( '' !== $branch_code ) {
			$headers['X-Branch-Code'] = $branch_code;
		}

		switch ( $auth_type ) {
			case 'api_key_secret':
				$api_key    = (string) $this->settings->get( 'api_key', '' );
				$api_secret = (string) $this->settings->get( 'api_secret', '' );
				if ( '' === $api_key || '' === $api_secret ) {
					return new WP_Error( 'demw_missing_api_key_secret', __( 'API key and secret are required for API Key / Secret auth.', 'dhl-ecommerce-mng-woocommerce' ) );
				}
				$headers['X-IBM-Client-Id']     = $api_key;
				$headers['X-IBM-Client-Secret'] = $api_secret;
				break;

			case 'username_password':
				$username = (string) $this->settings->get( 'username', '' );
				$password = (string) $this->settings->get( 'password', '' );
				if ( '' === $username || '' === $password ) {
					return new WP_Error( 'demw_missing_username_password', __( 'Username and password are required for Username / Password auth.', 'dhl-ecommerce-mng-woocommerce' ) );
				}
				// Keep auth mode swappable: send optional api key/secret if present, token exchange is handled by API client.
				$api_key    = (string) $this->settings->get( 'api_key', '' );
				$api_secret = (string) $this->settings->get( 'api_secret', '' );
				if ( '' !== $api_key ) {
					$headers['X-IBM-Client-Id'] = $api_key;
				}
				if ( '' !== $api_secret ) {
					$headers['X-IBM-Client-Secret'] = $api_secret;
				}
				break;

			case 'bearer_token':
				$bearer_token = (string) $this->settings->get( 'bearer_token', '' );
				if ( '' === $bearer_token ) {
					return new WP_Error( 'demw_missing_bearer_token', __( 'Bearer token is required for Bearer auth.', 'dhl-ecommerce-mng-woocommerce' ) );
				}
				$headers['Authorization'] = 'Bearer ' . $bearer_token;
				break;

			case 'custom_header':
				$header_name  = (string) $this->settings->get( 'custom_header_name', '' );
				$header_value = (string) $this->settings->get( 'custom_header_value', '' );
				if ( '' === $header_name || '' === $header_value ) {
					return new WP_Error( 'demw_missing_custom_header', __( 'Custom header name and value are required for custom header auth.', 'dhl-ecommerce-mng-woocommerce' ) );
				}
				$headers[ $header_name ] = $header_value;
				break;

			default:
				return new WP_Error( 'demw_invalid_auth_type', __( 'Unsupported auth type selected.', 'dhl-ecommerce-mng-woocommerce' ) );
		}

		return $headers;
	}
}
