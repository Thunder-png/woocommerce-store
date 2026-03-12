<?php
/**
 * Plugin Name: Net Order Activation
 * Plugin URI: https://example.com
 * Description: QR-based WooCommerce order activation flow for offline orders (phone / WhatsApp) that links orders to newly created customer accounts.
 * Version: 1.0.0
 * Author: Custom
 * Text Domain: net-order-activation
 *
 * @package Net_Order_Activation
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define core plugin constants.
if ( ! defined( 'NOA_PLUGIN_FILE' ) ) {
	define( 'NOA_PLUGIN_FILE', __FILE__ );
}

if ( ! defined( 'NOA_PLUGIN_DIR' ) ) {
	define( 'NOA_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'NOA_PLUGIN_URL' ) ) {
	define( 'NOA_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

/**
 * Initialize plugin.
 */
function noa_init() {
	// Load required files.
	require_once NOA_PLUGIN_DIR . 'includes/activation-endpoint.php';
	require_once NOA_PLUGIN_DIR . 'includes/ajax-handlers.php';
	require_once NOA_PLUGIN_DIR . 'includes/user-creation.php';
	require_once NOA_PLUGIN_DIR . 'includes/admin-meta-box.php';
	require_once NOA_PLUGIN_DIR . 'includes/admin-activation-page.php';

	// Register hooks.
	add_action( 'wp_enqueue_scripts', 'noa_enqueue_assets' );
}
add_action( 'plugins_loaded', 'noa_init' );

/**
 * Enqueue frontend assets only on activation page.
 */
function noa_enqueue_assets() {
	if ( ! is_page() ) {
		return;
	}

	// Check for activate endpoint/page by slug.
	global $post;

	if ( ! $post instanceof WP_Post ) {
		return;
	}

	if ( 'activate' !== $post->post_name ) {
		return;
	}

	wp_enqueue_style(
		'noa-activation',
		NOA_PLUGIN_URL . 'assets/activation.css',
		array(),
		'1.0.0'
	);

	wp_enqueue_script(
		'noa-activation',
		NOA_PLUGIN_URL . 'assets/activation.js',
		array( 'jquery' ),
		'1.0.0',
		true
	);

	$ajax_nonce = wp_create_nonce( 'noa_activation_nonce' );

	wp_localize_script(
		'noa-activation',
		'NOA_Settings',
		array(
			'ajax_url'   => admin_url( 'admin-ajax.php' ),
			'nonce'      => $ajax_nonce,
			// Placeholder for future reCAPTCHA site key.
			'recaptcha'  => '',
			'i18n'       => array(
				'unknown_error' => __( 'An unknown error occurred. Please try again.', 'net-order-activation' ),
			),
		)
	);
}

