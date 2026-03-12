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

	global $post;

	if ( ! $post instanceof WP_Post ) {
		return;
	}

	// Load assets if we're on the original /activate page,
	// or any page that uses the activation shortcode/template.
	$page_uses_activation_shortcode = has_shortcode( (string) $post->post_content, 'net_order_activation' );
	$page_template                  = get_page_template_slug( $post );
	$page_uses_activation_template  = ( 'warranty-activation.php' === $page_template );

	if ( 'activate' !== $post->post_name && ! $page_uses_activation_shortcode && ! $page_uses_activation_template ) {
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

