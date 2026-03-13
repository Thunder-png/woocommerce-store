<?php
/**
 * Custom measure (m²) product type.
 *
 * @package WCS_Custom_Measure
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( class_exists( 'WC_Product_Custom_Measure' ) ) {
	return;
}

/**
 * Custom measure product class.
 */
class WC_Product_Custom_Measure extends WC_Product_Simple {

	/**
	 * Constructor.
	 *
	 * @param int|WC_Product|null $product Product.
	 */
	public function __construct( $product = 0 ) {
		$this->product_type = 'wcs_custom_measure';
		parent::__construct( $product );
	}
}

/**
 * Register custom product type.
 *
 * @param array<string,string> $types Existing types.
 * @return array<string,string>
 */
function wcs_cm_register_product_type( $types ) {
	$types['wcs_custom_measure'] = __( 'Özel ölçü (m²)', 'wcs-custom-measure' );

	return $types;
}
add_filter( 'product_type_selector', 'wcs_cm_register_product_type' );

/**
 * Map product type to class.
 *
 * @param array<string,string> $types Existing classes.
 * @return array<string,string>
 */
function wcs_cm_product_class( $types ) {
	$types['wcs_custom_measure'] = WC_Product_Custom_Measure::class;

	return $types;
}
add_filter( 'woocommerce_product_class', 'wcs_cm_product_class', 10, 1 );

