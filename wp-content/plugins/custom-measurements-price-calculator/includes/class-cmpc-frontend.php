<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Frontend rendering and scripts.
 */
class CMPC_Frontend {

	/**
	 * Init hooks.
	 */
	public static function init() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
		add_action( 'woocommerce_before_add_to_cart_button', array( __CLASS__, 'render_measurement_block' ), 15 );
		add_filter( 'woocommerce_add_to_cart_validation', array( __CLASS__, 'validate_add_to_cart' ), 10, 3 );
	}

	/**
	 * Enqueue JS/CSS only on relevant product pages.
	 */
	public static function enqueue_assets() {
		if ( ! function_exists( 'is_product' ) || ! is_product() ) {
			return;
		}

		global $product;

		if ( ! ( $product instanceof WC_Product ) || ! CMPC_Helpers::is_custom_measure_product( $product ) ) {
			return;
		}

		wp_enqueue_style(
			'cmpc-frontend',
			CMPC_PLUGIN_URL . 'assets/css/cmpc-frontend.css',
			array(),
			'1.0.0'
		);

		wp_enqueue_script(
			'cmpc-frontend',
			CMPC_PLUGIN_URL . 'assets/js/cmpc-frontend.js',
			array(),
			'1.0.0',
			true
		);

		$base_price = CMPC_Helpers::get_base_m2_price( $product );

		wp_localize_script(
			'cmpc-frontend',
			'CMPC_Settings',
			array(
				'baseM2Price' => (float) wc_format_decimal( $base_price ),
				'currency'    => get_woocommerce_currency_symbol(),
				'minWidth'    => CMPC_Helpers::get_float_meta( $product, CMPC_Helpers::META_MIN_WIDTH, null ),
				'maxWidth'    => CMPC_Helpers::get_float_meta( $product, CMPC_Helpers::META_MAX_WIDTH, null ),
				'minHeight'   => CMPC_Helpers::get_float_meta( $product, CMPC_Helpers::META_MIN_HEIGHT, null ),
				'maxHeight'   => CMPC_Helpers::get_float_meta( $product, CMPC_Helpers::META_MAX_HEIGHT, null ),
			)
		);
	}

	/**
	 * Render measurement calculator block on single product page.
	 */
	public static function render_measurement_block() {
		global $product;

		if ( ! ( $product instanceof WC_Product ) || ! CMPC_Helpers::is_custom_measure_product( $product ) ) {
			return;
		}

		$base_price = CMPC_Helpers::get_base_m2_price( $product );
		if ( $base_price <= 0 ) {
			// Admin must configure base price.
			return;
		}
		?>
		<div class="cmpc-box" aria-label="<?php esc_attr_e( 'Özel Ölçü Hesaplama', 'custom-measurements-price-calculator' ); ?>">
			<h3 class="cmpc-title"><?php esc_html_e( 'Özel Ölçü Hesaplama', 'custom-measurements-price-calculator' ); ?></h3>

			<div class="cmpc-grid">
				<div class="cmpc-field">
					<label for="cmpc-width"><?php esc_html_e( 'En (metre)', 'custom-measurements-price-calculator' ); ?></label>
					<input
						id="cmpc-width"
						name="cmpc_width"
						type="number"
						min="0"
						step="0.01"
						inputmode="decimal"
						required
					/>
				</div>

				<div class="cmpc-field">
					<label for="cmpc-height"><?php esc_html_e( 'Boy (metre)', 'custom-measurements-price-calculator' ); ?></label>
					<input
						id="cmpc-height"
						name="cmpc_height"
						type="number"
						min="0"
						step="0.01"
						inputmode="decimal"
						required
					/>
				</div>
			</div>

			<div class="cmpc-summary">
				<div class="cmpc-summary-row">
					<span class="cmpc-label"><?php esc_html_e( 'Alan', 'custom-measurements-price-calculator' ); ?></span>
					<span class="cmpc-value" id="cmpc-area-display">0.00 m²</span>
				</div>
				<div class="cmpc-summary-row">
					<span class="cmpc-label"><?php esc_html_e( 'm² Fiyatı', 'custom-measurements-price-calculator' ); ?></span>
					<span class="cmpc-value" id="cmpc-base-price-display">
						<?php echo wp_kses_post( wc_price( $base_price ) ); ?>
					</span>
				</div>
				<div class="cmpc-summary-row cmpc-summary-total">
					<span class="cmpc-label"><?php esc_html_e( 'Toplam Fiyat', 'custom-measurements-price-calculator' ); ?></span>
					<span class="cmpc-value" id="cmpc-total-display">0.00</span>
				</div>
			</div>

			<input type="hidden" name="cmpc_area" id="cmpc-area-hidden" value="" />
		</div>
		<?php
	}

	/**
	 * Backend add-to-cart validation.
	 *
	 * @param bool $passed Passed.
	 * @param int  $product_id Product ID.
	 * @param int  $quantity Quantity.
	 * @return bool
	 */
	public static function validate_add_to_cart( $passed, $product_id, $quantity ) {
		if ( ! $passed ) {
			return false;
		}

		$product = wc_get_product( $product_id );
		if ( ! CMPC_Helpers::is_custom_measure_product( $product ) ) {
			return $passed;
		}

		$width  = isset( $_POST['cmpc_width'] ) ? (float) wc_clean( wp_unslash( $_POST['cmpc_width'] ) ) : 0;
		$height = isset( $_POST['cmpc_height'] ) ? (float) wc_clean( wp_unslash( $_POST['cmpc_height'] ) ) : 0;

		$valid = CMPC_Helpers::validate_measurements( $width, $height, $product, true );

		return $valid;
	}
}

