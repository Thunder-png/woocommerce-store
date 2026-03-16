<?php
/**
 * Plugin Name: DHL eCommerce MNG WooCommerce
 * Plugin URI: https://example.com
 * Description: Admin-side WooCommerce shipment integration scaffold for DHL eCommerce Turkey / MNG Kargo (sandbox-first).
 * Version: 1.0.0
 * Author: Custom
 * Text Domain: dhl-ecommerce-mng-woocommerce
 * Requires Plugins: woocommerce
 *
 * @package DEMW
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'DEMW_PLUGIN_FILE', __FILE__ );
define( 'DEMW_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'DEMW_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'DEMW_VERSION', '1.0.0' );
define( 'DEMW_OPTION_KEY', 'demw_settings' );

require_once DEMW_PLUGIN_DIR . 'includes/class-demw-helpers.php';
require_once DEMW_PLUGIN_DIR . 'includes/class-demw-logger.php';
require_once DEMW_PLUGIN_DIR . 'includes/class-demw-settings.php';
require_once DEMW_PLUGIN_DIR . 'includes/class-demw-auth.php';
require_once DEMW_PLUGIN_DIR . 'includes/class-demw-api-client.php';
require_once DEMW_PLUGIN_DIR . 'includes/class-demw-order-mapper.php';
require_once DEMW_PLUGIN_DIR . 'includes/class-demw-order-actions.php';
require_once DEMW_PLUGIN_DIR . 'includes/class-demw-admin-metabox.php';

/**
 * Main plugin bootstrap.
 */
final class DEMW_Plugin {
	/**
	 * Singleton instance.
	 *
	 * @var DEMW_Plugin|null
	 */
	private static $instance = null;

	/**
	 * Plugin services.
	 *
	 * @var array<string,mixed>
	 */
	private $services = array();

	/**
	 * Get instance.
	 *
	 * @return DEMW_Plugin
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		add_action( 'plugins_loaded', array( $this, 'init' ) );
	}

	/**
	 * Initialize plugin services.
	 *
	 * @return void
	 */
	public function init() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			add_action( 'admin_notices', array( $this, 'woocommerce_missing_notice' ) );
			return;
		}

		$this->register_shipping_method_hooks();

		$logger       = new DEMW_Logger();
		$settings     = new DEMW_Settings( $logger );
		$auth         = new DEMW_Auth( $settings );
		$api_client   = new DEMW_API_Client( $settings, $auth, $logger );
		$order_mapper = new DEMW_Order_Mapper();
		$order_action = new DEMW_Order_Actions( $settings, $api_client, $order_mapper, $logger );
		$metabox      = new DEMW_Admin_Metabox( $settings, $order_action );
		$settings->set_api_client( $api_client );

		$this->services = array(
			'logger'       => $logger,
			'settings'     => $settings,
			'auth'         => $auth,
			'api_client'   => $api_client,
			'order_mapper' => $order_mapper,
			'order_action' => $order_action,
			'metabox'      => $metabox,
		);

		$settings->hooks();
		$order_action->hooks();
		$metabox->hooks();
	}

	/**
	 * Register WooCommerce shipping method integration hooks.
	 *
	 * @return void
	 */
	private function register_shipping_method_hooks() {
		add_action(
			'woocommerce_shipping_init',
			function() {
				require_once DEMW_PLUGIN_DIR . 'includes/class-demw-shipping-method.php';
			}
		);

		add_filter(
			'woocommerce_shipping_methods',
			function( $methods ) {
				$methods['demw_dhl_mng'] = 'DEMW_Shipping_Method';
				return $methods;
			}
		);
	}

	/**
	 * Admin notice when WooCommerce is unavailable.
	 *
	 * @return void
	 */
	public function woocommerce_missing_notice() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}
		?>
		<div class="notice notice-error">
			<p><?php echo esc_html__( 'DHL eCommerce MNG WooCommerce requires WooCommerce to be active.', 'dhl-ecommerce-mng-woocommerce' ); ?></p>
		</div>
		<?php
	}
}

DEMW_Plugin::instance();
