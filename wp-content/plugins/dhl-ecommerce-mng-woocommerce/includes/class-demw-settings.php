<?php
/**
 * Admin settings.
 *
 * @package DEMW
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings service.
 */
class DEMW_Settings {
	/**
	 * Option key.
	 *
	 * @var string
	 */
	const OPTION_KEY = DEMW_OPTION_KEY;

	/**
	 * Settings page slug.
	 *
	 * @var string
	 */
	const PAGE_SLUG = 'demw-settings';

	/**
	 * Logger.
	 *
	 * @var DEMW_Logger
	 */
	private $logger;

	/**
	 * API client reference for test-connection action.
	 *
	 * @var DEMW_API_Client|null
	 */
	private $api_client = null;

	/**
	 * Constructor.
	 *
	 * @param DEMW_Logger $logger Logger.
	 */
	public function __construct( DEMW_Logger $logger ) {
		$this->logger = $logger;
	}

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function hooks() {
		add_action( 'admin_menu', array( $this, 'register_admin_page' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'admin_post_demw_save_settings', array( $this, 'handle_save' ) );
		add_action( 'admin_post_demw_test_connection', array( $this, 'handle_test_connection' ) );
	}

	/**
	 * Attach API client for settings actions.
	 *
	 * @param DEMW_API_Client $api_client Client.
	 * @return void
	 */
	public function set_api_client( DEMW_API_Client $api_client ) {
		$this->api_client = $api_client;
	}

	/**
	 * Register settings submenu.
	 *
	 * @return void
	 */
	public function register_admin_page() {
		add_submenu_page(
			'woocommerce',
			__( 'DHL eCommerce MNG', 'dhl-ecommerce-mng-woocommerce' ),
			__( 'DHL eCommerce MNG', 'dhl-ecommerce-mng-woocommerce' ),
			'manage_woocommerce',
			self::PAGE_SLUG,
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Enqueue admin assets.
	 *
	 * @param string $hook_suffix Hook suffix.
	 * @return void
	 */
	public function enqueue_assets( $hook_suffix ) {
		if ( false === strpos( (string) $hook_suffix, self::PAGE_SLUG ) && 'post.php' !== $hook_suffix ) {
			return;
		}

		wp_enqueue_style(
			'demw-admin',
			DEMW_PLUGIN_URL . 'assets/css/admin.css',
			array(),
			DEMW_VERSION
		);

		wp_enqueue_script(
			'demw-admin',
			DEMW_PLUGIN_URL . 'assets/js/admin.js',
			array(),
			DEMW_VERSION,
			true
		);
	}

	/**
	 * Render settings page.
	 *
	 * @return void
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'dhl-ecommerce-mng-woocommerce' ) );
		}

		$settings = $this->get_settings();
		$notices  = $this->get_flash_notices();

		require DEMW_PLUGIN_DIR . 'admin/views/settings-page.php';
	}

	/**
	 * Handle settings save action.
	 *
	 * @return void
	 */
	public function handle_save() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'Insufficient permissions.', 'dhl-ecommerce-mng-woocommerce' ) );
		}

		check_admin_referer( 'demw_save_settings' );

		$raw      = isset( $_POST['demw_settings'] ) ? wp_unslash( $_POST['demw_settings'] ) : array(); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$settings = $this->sanitize_settings( is_array( $raw ) ? $raw : array() );
		update_option( self::OPTION_KEY, $settings );

		$this->set_flash_notice( 'success', __( 'Settings saved.', 'dhl-ecommerce-mng-woocommerce' ) );
		wp_safe_redirect( $this->admin_url() );
		exit;
	}

	/**
	 * Handle settings test connection action.
	 *
	 * @return void
	 */
	public function handle_test_connection() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'Insufficient permissions.', 'dhl-ecommerce-mng-woocommerce' ) );
		}

		check_admin_referer( 'demw_test_connection' );

		if ( ! $this->api_client ) {
			$this->set_flash_notice( 'error', __( 'API client is not available.', 'dhl-ecommerce-mng-woocommerce' ) );
			wp_safe_redirect( $this->admin_url() );
			exit;
		}

		$result = $this->api_client->test_connection();
		if ( is_wp_error( $result ) ) {
			$this->logger->error(
				'Connection test failed',
				array(
					'code'    => $result->get_error_code(),
					'message' => $result->get_error_message(),
				)
			);
			$this->set_flash_notice( 'error', sprintf( __( 'Connection failed: %s', 'dhl-ecommerce-mng-woocommerce' ), $result->get_error_message() ) );
		} else {
			$this->set_flash_notice( 'success', __( 'Connection test succeeded.', 'dhl-ecommerce-mng-woocommerce' ) );
		}

		wp_safe_redirect( $this->admin_url() );
		exit;
	}

	/**
	 * Get all settings merged with defaults.
	 *
	 * @return array<string,mixed>
	 */
	public function get_settings() {
		$current = get_option( self::OPTION_KEY, array() );
		$current = is_array( $current ) ? $current : array();
		return wp_parse_args( $current, $this->defaults() );
	}

	/**
	 * Get setting value.
	 *
	 * @param string $key Key.
	 * @param mixed  $fallback Fallback.
	 * @return mixed
	 */
	public function get( $key, $fallback = null ) {
		$settings = $this->get_settings();
		return isset( $settings[ $key ] ) ? $settings[ $key ] : $fallback;
	}

	/**
	 * Whether debug logging is enabled.
	 *
	 * @return bool
	 */
	public function is_debug_enabled() {
		return DEMW_Helpers::as_bool( $this->get( 'debug_enabled', 0 ) );
	}

	/**
	 * Whether request body logging is enabled.
	 *
	 * @return bool
	 */
	public function should_log_request_body() {
		return DEMW_Helpers::as_bool( $this->get( 'log_request_bodies', 0 ) );
	}

	/**
	 * Whether response body logging is enabled.
	 *
	 * @return bool
	 */
	public function should_log_response_body() {
		return DEMW_Helpers::as_bool( $this->get( 'log_response_bodies', 0 ) );
	}

	/**
	 * Settings defaults.
	 *
	 * @return array<string,mixed>
	 */
	public function defaults() {
		return array(
			'environment'          => 'development',
			'development_base_url' => 'https://testapi.mngkargo.com.tr/mngapi/api',
			'production_base_url'  => '',
			'timeout'              => 20,
			'api_version'          => '',
			'shipment_command_api' => 'plus_command',
			'auth_type'            => 'api_key_secret',
			'api_key'              => '',
			'api_secret'           => '',
			'username'             => '',
			'password'             => '',
			'bearer_token'         => '',
			'customer_code'        => '',
			'branch_code'          => '',
			'custom_header_name'   => '',
			'custom_header_value'  => '',
			'debug_enabled'        => 0,
			'log_request_bodies'   => 0,
			'log_response_bodies'  => 0,
		);
	}

	/**
	 * Sanitize incoming settings payload.
	 *
	 * @param array<string,mixed> $input Input.
	 * @return array<string,mixed>
	 */
	private function sanitize_settings( $input ) {
		$defaults = $this->defaults();
		$clean    = array();

		$raw_environment = (string) ( $input['environment'] ?? '' );
		if ( 'sandbox' === $raw_environment ) {
			$raw_environment = 'development';
		}
		$clean['environment'] = in_array( $raw_environment, array( 'development', 'production' ), true ) ? $raw_environment : $defaults['environment'];

		$development_base_url = $input['development_base_url'] ?? '';
		if ( '' === (string) $development_base_url && isset( $input['sandbox_base_url'] ) ) {
			$development_base_url = $input['sandbox_base_url'];
		}
		$clean['development_base_url'] = DEMW_Helpers::sanitize_base_url( $development_base_url );
		$clean['production_base_url'] = DEMW_Helpers::sanitize_base_url( $input['production_base_url'] ?? '' );
		$clean['timeout'] = max( 3, min( 120, absint( $input['timeout'] ?? $defaults['timeout'] ) ) );
		$clean['api_version'] = sanitize_text_field( (string) ( $input['api_version'] ?? '' ) );
		$command_apis     = array( 'plus_command', 'barcode_command' );
		$clean['shipment_command_api'] = in_array( (string) ( $input['shipment_command_api'] ?? '' ), $command_apis, true ) ? (string) $input['shipment_command_api'] : $defaults['shipment_command_api'];

		$auth_types = array( 'api_key_secret', 'username_password', 'bearer_token', 'custom_header' );
		$clean['auth_type'] = in_array( (string) ( $input['auth_type'] ?? '' ), $auth_types, true ) ? (string) $input['auth_type'] : $defaults['auth_type'];

		$text_fields = array(
			'api_key',
			'api_secret',
			'username',
			'password',
			'bearer_token',
			'customer_code',
			'branch_code',
			'custom_header_name',
			'custom_header_value',
		);

		foreach ( $text_fields as $field ) {
			$clean[ $field ] = sanitize_text_field( (string) ( $input[ $field ] ?? '' ) );
		}

		$clean['debug_enabled']       = DEMW_Helpers::as_bool( $input['debug_enabled'] ?? 0 ) ? 1 : 0;
		$clean['log_request_bodies']  = DEMW_Helpers::as_bool( $input['log_request_bodies'] ?? 0 ) ? 1 : 0;
		$clean['log_response_bodies'] = DEMW_Helpers::as_bool( $input['log_response_bodies'] ?? 0 ) ? 1 : 0;

		return wp_parse_args( $clean, $defaults );
	}

	/**
	 * Build admin page URL.
	 *
	 * @return string
	 */
	public function admin_url() {
		return admin_url( 'admin.php?page=' . self::PAGE_SLUG );
	}

	/**
	 * Set temporary notice in transient.
	 *
	 * @param string $type Notice type.
	 * @param string $message Message.
	 * @return void
	 */
	private function set_flash_notice( $type, $message ) {
		set_transient(
			'demw_flash_notice_' . get_current_user_id(),
			array(
				'type'    => sanitize_key( $type ),
				'message' => sanitize_text_field( $message ),
			),
			60
		);
	}

	/**
	 * Retrieve and delete flash notices.
	 *
	 * @return array<string,string>|null
	 */
	private function get_flash_notices() {
		$key    = 'demw_flash_notice_' . get_current_user_id();
		$notice = get_transient( $key );
		delete_transient( $key );
		return is_array( $notice ) ? $notice : null;
	}
}
