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
 * @param string $classname   Resolved class name.
 * @param string $product_type Product type slug.
 * @param string $post_type   Post type (usually 'product').
 * @return string
 */
function wcs_cm_product_class( $classname, $product_type, $post_type ) {
	if ( 'wcs_custom_measure' === $product_type ) {
		return WC_Product_Custom_Measure::class;
	}

	return $classname;
}
add_filter( 'woocommerce_product_class', 'wcs_cm_product_class', 10, 3 );

/**
 * Ensure pricing fields are visible for custom measure type in admin.
 */
function wcs_cm_admin_product_type_script() {
	if ( ! function_exists( 'get_current_screen' ) ) {
		return;
	}

	$screen = get_current_screen();

	if ( ! $screen || 'product' !== $screen->id ) {
		return;
	}
	?>
	<script>
	jQuery( function( $ ) {
		function wcsShowPricingForCustomMeasure() {
			if ( $( '#product-type' ).val() === 'wcs_custom_measure' ) {
				$( '#general_product_data' ).show();
				$( '#general_product_data .pricing' ).show();
				$( '#general_product_data .show_if_simple' ).show();
			}
		}

		$( 'body' ).on( 'woocommerce-product-type-change', function( event, val ) {
			if ( val === 'wcs_custom_measure' ) {
				wcsShowPricingForCustomMeasure();
			}
		} );

		wcsShowPricingForCustomMeasure();
	} );
	</script>
	<?php
}
add_action( 'admin_footer', 'wcs_cm_admin_product_type_script' );

