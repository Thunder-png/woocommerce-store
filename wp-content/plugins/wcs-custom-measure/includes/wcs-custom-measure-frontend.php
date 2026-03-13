<?php
/**
 * Frontend rendering and assets for custom measure calculator.
 *
 * @package WCS_Custom_Measure
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue scripts and styles on single product pages when needed.
 */
function wcs_cm_enqueue_assets() {
	if ( ! function_exists( 'is_product' ) || ! is_product() ) {
		return;
	}

	global $product;

	if ( ! ( $product instanceof WC_Product ) ) {
		return;
	}

	$product_id = $product->get_id();

	if ( ! $product_id ) {
		return;
	}

	$m2_enabled = wcs_is_custom_measure_product( $product );

	$child_theme = wp_get_theme( get_stylesheet() );

	if ( $m2_enabled ) {
		wp_enqueue_script(
			'wcs-m2-calculator',
			get_stylesheet_directory_uri() . '/assets/js/m2-calculator.js',
			array(),
			function_exists( 'wcs_asset_version' ) ? wcs_asset_version( 'assets/js/m2-calculator.js', $child_theme->get( 'Version' ) ) : $child_theme->get( 'Version' ),
			true
		);

		wp_enqueue_script(
			'wcs-calculator-toggle',
			get_stylesheet_directory_uri() . '/assets/js/wcs-calculator-toggle.js',
			array(),
			function_exists( 'wcs_asset_version' ) ? wcs_asset_version( 'assets/js/wcs-calculator-toggle.js', $child_theme->get( 'Version' ) ) : $child_theme->get( 'Version' ),
			true
		);

		$price_per_m2 = $product instanceof WC_Product ? (float) wc_get_price_to_display( $product ) : 0;

		wp_localize_script(
			'wcs-m2-calculator',
			'wcsCalculator',
			array(
				'pricePerM2' => $price_per_m2,
				'vatRate'    => 0.20,
				'currency'   => get_woocommerce_currency_symbol(),
			)
		);
	}
}
add_action( 'wp_enqueue_scripts', 'wcs_cm_enqueue_assets' );

/**
 * Render calculator fields for single product pages.
 */
function wcs_render_price_calculator() {
	if ( ! function_exists( 'is_product' ) || ! is_product() ) {
		return;
	}

	global $product;

	if ( ! ( $product instanceof WC_Product ) ) {
		return;
	}

	$product_id = $product->get_id();

	if ( ! $product_id ) {
		return;
	}

	if ( ! wcs_is_custom_measure_product( $product ) ) {
		return;
	}
	?>
	<section class="wcs-calculator" aria-label="Price calculator">
		<h3><?php esc_html_e( 'm² Price Calculator', 'woocommerce-store-child' ); ?></h3>
		<div class="wcs-calculator__grid">
			<label for="wcs-width"><?php esc_html_e( 'Width (m)', 'woocommerce-store-child' ); ?></label>
			<input id="wcs-width" name="wcs_width" type="number" min="0" step="0.01" inputmode="decimal" />

			<label for="wcs-height"><?php esc_html_e( 'Height (m)', 'woocommerce-store-child' ); ?></label>
			<input id="wcs-height" name="wcs_height" type="number" min="0" step="0.01" inputmode="decimal" />

			<label for="wcs-thickness"><?php esc_html_e( 'İp Kalınlığı (mm)', 'woocommerce-store-child' ); ?></label>
			<select id="wcs-thickness" name="wcs_thickness">
				<option value=""><?php esc_html_e( 'Seçiniz', 'woocommerce-store-child' ); ?></option>
				<option value="1.5">1.5 mm</option>
				<option value="2">2 mm</option>
				<option value="2.5">2.5 mm</option>
				<option value="3">3 mm</option>
				<option value="4">4 mm</option>
				<option value="6">6 mm</option>
			</select>

			<label for="wcs-mesh"><?php esc_html_e( 'Göz Boyutu', 'woocommerce-store-child' ); ?></label>
			<select id="wcs-mesh" name="wcs_mesh">
				<option value=""><?php esc_html_e( 'Seçiniz', 'woocommerce-store-child' ); ?></option>
				<option value="2x2">2x2</option>
				<option value="4x4">4x4</option>
				<option value="5x5">5x5</option>
				<option value="10x10">10x10</option>
				<option value="12x12">12x12</option>
				<option value="13x13">13x13</option>
			</select>

			<label for="wcs-color"><?php esc_html_e( 'Renk Grubu', 'woocommerce-store-child' ); ?></label>
			<select id="wcs-color" name="wcs_color">
				<option value="standard"><?php esc_html_e( 'Standart (Beyaz)', 'woocommerce-store-child' ); ?></option>
				<option value="colored"><?php esc_html_e( 'Renkli', 'woocommerce-store-child' ); ?></option>
				<option value="black"><?php esc_html_e( 'Siyah / Gri', 'woocommerce-store-child' ); ?></option>
			</select>
		</div>

		<div class="wcs-calculator__results" aria-live="polite">
			<p><?php esc_html_e( 'Area:', 'woocommerce-store-child' ); ?> <strong id="wcs-area">0.00 m²</strong></p>
			<p><?php esc_html_e( 'Base Price:', 'woocommerce-store-child' ); ?> <strong id="wcs-price">0.00</strong></p>
			<p><?php esc_html_e( 'VAT (20%):', 'woocommerce-store-child' ); ?> <strong id="wcs-vat">0.00</strong></p>
			<p><?php esc_html_e( 'Total:', 'woocommerce-store-child' ); ?> <strong id="wcs-total">0.00</strong></p>
		</div>
	</section>
	<?php
}
add_action( 'woocommerce_before_add_to_cart_form', 'wcs_render_price_calculator', 15 );

/**
 * Render hidden calculator payload fields inside add-to-cart form.
 */
function wcs_render_calculator_hidden_inputs() {
	if ( ! function_exists( 'is_product' ) || ! is_product() ) {
		return;
	}

	global $product;

	if ( ! ( $product instanceof WC_Product ) || ! wcs_is_custom_measure_product( $product ) ) {
		return;
	}
	?>
	<input type="hidden" name="wcs_area" id="wcs-area-hidden" value="" />
	<input type="hidden" name="wcs_unit_price_m2" id="wcs-unit-price-hidden" value="" />
	<input type="hidden" name="wcs_calculated_total" id="wcs-total-hidden" value="" />
	<?php
}
add_action( 'woocommerce_before_add_to_cart_button', 'wcs_render_calculator_hidden_inputs', 15 );

