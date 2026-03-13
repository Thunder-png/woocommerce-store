<?php
/**
 * Helper functions for custom measure products.
 *
 * @package WCS_Custom_Measure
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Check whether a product should use the custom measure calculator.
 *
 * This replaces the child-theme attribute-based check.
 *
 * @param WC_Product|int|null $product Product or ID.
 * @return bool
 */
function wcs_is_custom_measure_product( $product ) {
	if ( ! $product instanceof WC_Product ) {
		$product = wc_get_product( $product );
	}

	if ( ! ( $product instanceof WC_Product ) ) {
		return false;
	}

	// Primary: product type is our custom type.
	if ( $product->is_type( 'wcs_custom_measure' ) ) {
		return true;
	}

	// Secondary: explicit product meta flag for other types.
	$enabled = $product->get_meta( '_wcs_custom_measure_enabled', true );

	return (bool) $enabled;
}

/**
 * Optional: add a simple checkbox to the product data panel
 * so that non-custom-type products can still opt into the calculator.
 */
function wcs_cm_product_data_fields() {
	global $post;

	if ( ! $post ) {
		return;
	}

	echo '<div class="options_group show_if_simple show_if_variable show_if_wcs_custom_measure">';

	woocommerce_wp_checkbox(
		array(
			'id'          => '_wcs_custom_measure_enabled',
			'label'       => __( 'Özel ölçü hesaplayıcıyı kullan', 'wcs-custom-measure' ),
			'description' => __( 'Bu ürün için m² hesaplayıcıyı etkinleştir.', 'wcs-custom-measure' ),
		)
	);

	echo '</div>';
}
add_action( 'woocommerce_product_options_general_product_data', 'wcs_cm_product_data_fields' );

/**
 * Save custom measure checkbox.
 *
 * @param int $post_id Product ID.
 */
function wcs_cm_save_product_meta( $post_id ) {
	$is_enabled = isset( $_POST['_wcs_custom_measure_enabled'] );

	if ( ! $is_enabled ) {
		delete_post_meta( $post_id, '_wcs_custom_measure_enabled' );
		return;
	}

	update_post_meta( $post_id, '_wcs_custom_measure_enabled', 'yes' );

	// Eğer admin arayüzünde ürün tipi özel ölçü olarak seçilmişse, meta ile senkron tut.
	if ( isset( $_POST['product-type'] ) && 'wcs_custom_measure' === $_POST['product-type'] ) {
		update_post_meta( $post_id, '_product_type', 'wcs_custom_measure' );
	}
}
add_action( 'woocommerce_process_product_meta', 'wcs_cm_save_product_meta', 10, 1 );

