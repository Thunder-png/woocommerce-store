<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Frontend rendering and scripts.
 */
class CMPC_Frontend {

	/**
	 * Resolve the current product safely for single product context.
	 *
	 * @return WC_Product|null
	 */
	private static function get_current_product() {
		global $product;

		if ( $product instanceof WC_Product ) {
			return $product;
		}

		if ( function_exists( 'get_queried_object_id' ) ) {
			$product_id = (int) get_queried_object_id();
			if ( $product_id > 0 ) {
				$queried_product = wc_get_product( $product_id );
				if ( $queried_product instanceof WC_Product ) {
					return $queried_product;
				}
			}
		}

		return null;
	}

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

		$product = self::get_current_product();

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
		$product = self::get_current_product();

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

			<div class="cmpc-header">
				<div class="cmpc-header-icon" aria-hidden="true">
					<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
						<path d="M3 3h18v4H3zM3 10h4v11H3zM17 10h4v11h-4z"/>
						<path d="M7 21h10" stroke-dasharray="2 2"/>
					</svg>
				</div>
				<div>
					<h3 class="cmpc-title"><?php esc_html_e( 'Özel Ölçü Hesaplama', 'custom-measurements-price-calculator' ); ?></h3>
					<p class="cmpc-subtitle"><?php esc_html_e( 'En ve boy değerlerini girerek fiyatı hesaplayın', 'custom-measurements-price-calculator' ); ?></p>
				</div>
			</div>

			<div class="cmpc-grid">
				<div class="cmpc-field">
					<label for="cmpc-width"><?php esc_html_e( 'En', 'custom-measurements-price-calculator' ); ?></label>
					<div class="cmpc-input-wrap">
						<input
							id="cmpc-width"
							name="cmpc_width"
							type="number"
							min="0"
							step="0.01"
							inputmode="decimal"
							placeholder="0.00"
							required
						/>
						<span class="cmpc-unit-badge">m</span>
					</div>
					<div class="cmpc-field-hint" id="cmpc-width-hint"></div>
				</div>

				<div class="cmpc-field">
					<label for="cmpc-height"><?php esc_html_e( 'Boy', 'custom-measurements-price-calculator' ); ?></label>
					<div class="cmpc-input-wrap">
						<input
							id="cmpc-height"
							name="cmpc_height"
							type="number"
							min="0"
							step="0.01"
							inputmode="decimal"
							placeholder="0.00"
							required
						/>
						<span class="cmpc-unit-badge">m</span>
					</div>
					<div class="cmpc-field-hint" id="cmpc-height-hint"></div>
				</div>
			</div>

			<div class="cmpc-area-visual" id="cmpc-area-visual" aria-live="polite">
				<div class="cmpc-area-rect-wrap">
					<svg class="cmpc-area-rect-svg" id="cmpc-area-svg" width="54" height="54" viewBox="0 0 54 54" xmlns="http://www.w3.org/2000/svg">
						<rect id="cmpc-area-rect" x="1" y="1" width="52" height="52" rx="3"
							fill="rgba(37,99,235,0.08)" stroke="#2563eb" stroke-width="1.5"/>
					</svg>
				</div>
				<div class="cmpc-area-info" id="cmpc-area-info-text"></div>
			</div>

			<div class="cmpc-summary">
				<div class="cmpc-summary-row">
					<span class="cmpc-label"><?php esc_html_e( 'Alan', 'custom-measurements-price-calculator' ); ?></span>
					<span class="cmpc-value" id="cmpc-area-display">—</span>
				</div>
				<div class="cmpc-summary-row">
					<span class="cmpc-label"><?php esc_html_e( 'm² Birim Fiyatı', 'custom-measurements-price-calculator' ); ?></span>
					<span class="cmpc-value" id="cmpc-base-price-display">
						<?php echo wc_price( $base_price ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					</span>
				</div>
				<div class="cmpc-summary-row cmpc-summary-total">
					<span class="cmpc-label"><?php esc_html_e( 'Toplam Fiyat', 'custom-measurements-price-calculator' ); ?></span>
					<div class="cmpc-calc-formula" id="cmpc-formula"></div>
					<span class="cmpc-value" id="cmpc-total-display">—</span>
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
