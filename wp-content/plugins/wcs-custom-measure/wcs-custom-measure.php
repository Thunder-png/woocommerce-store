<?php
/**
 * Plugin Name: WCS Custom Measure Products
 * Description: Özel ölçü (m²) ürün tipi ve hesaplayıcı entegrasyonu.
 * Version: 1.0.0
 * Author: Custom
 * Text Domain: wcs-custom-measure
 *
 * @package WCS_Custom_Measure
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'WCS_CM_PLUGIN_FILE' ) ) {
	define( 'WCS_CM_PLUGIN_FILE', __FILE__ );
}

if ( ! defined( 'WCS_CM_PLUGIN_DIR' ) ) {
	define( 'WCS_CM_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'WCS_CM_PLUGIN_URL' ) ) {
	define( 'WCS_CM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

/**
 * Initialize plugin.
 */
function wcs_cm_init() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}

	require_once WCS_CM_PLUGIN_DIR . 'includes/class-wc-product-custom-measure.php';
	require_once WCS_CM_PLUGIN_DIR . 'includes/wcs-custom-measure-helpers.php';
	require_once WCS_CM_PLUGIN_DIR . 'includes/wcs-custom-measure-frontend.php';
	require_once WCS_CM_PLUGIN_DIR . 'includes/wcs-custom-measure-cart.php';
}
add_action( 'plugins_loaded', 'wcs_cm_init' );

