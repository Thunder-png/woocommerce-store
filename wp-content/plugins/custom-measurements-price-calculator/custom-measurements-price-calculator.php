<?php
/**
 * Plugin Name: Custom Measurements Price Calculator
 * Description: Custom width/height based pricing for simple products (m² pricing).
 * Version: 1.0.0
 * Author: CMPC
 * Text Domain: custom-measurements-price-calculator
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'CMPC_PLUGIN_FILE' ) ) {
	define( 'CMPC_PLUGIN_FILE', __FILE__ );
}

if ( ! defined( 'CMPC_PLUGIN_DIR' ) ) {
	define( 'CMPC_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'CMPC_PLUGIN_URL' ) ) {
	define( 'CMPC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

/**
 * Main plugin bootstrap.
 */
final class CMPC_Plugin {

	/**
	 * Singleton instance.
	 *
	 * @var CMPC_Plugin|null
	 */
	private static $instance = null;

	/**
	 * Get singleton instance.
	 *
	 * @return CMPC_Plugin
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * CMPC_Plugin constructor.
	 */
	private function __construct() {
		$this->includes();
		$this->init_hooks();
	}

	/**
	 * Include required files.
	 */
	private function includes() {
		require_once CMPC_PLUGIN_DIR . 'includes/class-cmpc-helpers.php';
		require_once CMPC_PLUGIN_DIR . 'includes/class-cmpc-admin.php';
		require_once CMPC_PLUGIN_DIR . 'includes/class-cmpc-frontend.php';
		require_once CMPC_PLUGIN_DIR . 'includes/class-cmpc-cart.php';
		require_once CMPC_PLUGIN_DIR . 'includes/class-cmpc-order.php';
	}

	/**
	 * Initialize hooks.
	 */
	private function init_hooks() {
		add_action( 'plugins_loaded', array( $this, 'on_plugins_loaded' ) );
	}

	/**
	 * On plugins loaded.
	 */
	public function on_plugins_loaded() {
		// Ensure WooCommerce is active.
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}

		CMPC_Helpers::init();
		CMPC_Admin::init();
		CMPC_Frontend::init();
		CMPC_Cart::init();
		CMPC_Order::init();
	}
}

/**
 * Initialize the plugin.
 */
function cmpc_init_plugin() {
	CMPC_Plugin::instance();
}
cmpc_init_plugin();

