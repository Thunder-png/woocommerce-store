<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Helper utilities for Custom Measurements Price Calculator.
 */
class CMPC_Helpers {

	const META_ENABLE     = '_enable_custom_measurement_pricing';
	const META_BASE_PRICE = '_base_m2_price';
	const META_MIN_WIDTH  = '_min_width';
	const META_MIN_HEIGHT = '_min_height';
	const META_MAX_WIDTH  = '_max_width';
	const META_MAX_HEIGHT = '_max_height';

	/**
	 * Init shared hooks if needed.
	 */
	public static function init() {
		// Reserved for future shared hooks.
	}

	/**
	 * Check if product is a custom measurement product (simple + enabled).
	 *
	 * @param WC_Product|int|null $product Product or ID.
	 * @return bool
	 */
	public static function is_custom_measure_product( $product ) {
		if ( ! $product instanceof WC_Product ) {
			$product = wc_get_product( $product );
		}

		if ( ! ( $product instanceof WC_Product ) ) {
			return false;
		}

		if ( ! $product->is_type( 'simple' ) ) {
			return false;
		}

		$enabled = $product->get_meta( self::META_ENABLE, true );

		return ( 'yes' === $enabled || 1 === $enabled || true === $enabled );
	}

	/**
	 * Get base m2 price from product.
	 *
	 * @param WC_Product $product Product.
	 * @return float
	 */
	public static function get_base_m2_price( WC_Product $product ) {
		$value = $product->get_meta( self::META_BASE_PRICE, true );
		$value = '' === $value ? 0 : $value;

		return (float) wc_format_decimal( $value );
	}

	/**
	 * Get float meta with optional default.
	 *
	 * @param WC_Product $product Product.
	 * @param string     $meta_key Meta key.
	 * @param float|null $default Default.
	 * @return float|null
	 */
	public static function get_float_meta( WC_Product $product, $meta_key, $default = null ) {
		$value = $product->get_meta( $meta_key, true );

		if ( '' === $value || null === $value ) {
			return $default;
		}

		return (float) wc_format_decimal( $value );
	}

	/**
	 * Validate width/height values against min/max constraints and >0 rules.
	 *
	 * @param float      $width Width in meters.
	 * @param float      $height Height in meters.
	 * @param WC_Product $product Product.
	 * @param bool       $add_notices Whether to add WC notices.
	 * @return bool
	 */
	public static function validate_measurements( $width, $height, WC_Product $product, $add_notices = true ) {
		$base_price = self::get_base_m2_price( $product );

		$min_w = self::get_float_meta( $product, self::META_MIN_WIDTH, null );
		$max_w = self::get_float_meta( $product, self::META_MAX_WIDTH, null );
		$min_h = self::get_float_meta( $product, self::META_MIN_HEIGHT, null );
		$max_h = self::get_float_meta( $product, self::META_MAX_HEIGHT, null );

		$ok = true;

		if ( $base_price <= 0 ) {
			$ok = false;
			if ( $add_notices ) {
				wc_add_notice( __( 'Bu ürün için geçerli bir m² fiyatı tanımlanmamış.', 'custom-measurements-price-calculator' ), 'error' );
			}
		}

		if ( $width <= 0 ) {
			$ok = false;
			if ( $add_notices ) {
				wc_add_notice( __( 'En değeri 0\'dan büyük olmalıdır.', 'custom-measurements-price-calculator' ), 'error' );
			}
		}

		if ( $height <= 0 ) {
			$ok = false;
			if ( $add_notices ) {
				wc_add_notice( __( 'Boy değeri 0\'dan büyük olmalıdır.', 'custom-measurements-price-calculator' ), 'error' );
			}
		}

		if ( null !== $min_w && $width < $min_w ) {
			$ok = false;
			if ( $add_notices ) {
				wc_add_notice(
					sprintf(
						/* translators: %s: minimum width */
						__( 'En değeri en az %s m olmalıdır.', 'custom-measurements-price-calculator' ),
						wc_format_decimal( $min_w, 2 )
					),
					'error'
				);
			}
		}

		if ( null !== $max_w && $width > $max_w ) {
			$ok = false;
			if ( $add_notices ) {
				wc_add_notice(
					sprintf(
						/* translators: %s: maximum width */
						__( 'En değeri en fazla %s m olabilir.', 'custom-measurements-price-calculator' ),
						wc_format_decimal( $max_w, 2 )
					),
					'error'
				);
			}
		}

		if ( null !== $min_h && $height < $min_h ) {
			$ok = false;
			if ( $add_notices ) {
				wc_add_notice(
					sprintf(
						/* translators: %s: minimum height */
						__( 'Boy değeri en az %s m olmalıdır.', 'custom-measurements-price-calculator' ),
						wc_format_decimal( $min_h, 2 )
					),
					'error'
				);
			}
		}

		if ( null !== $max_h && $height > $max_h ) {
			$ok = false;
			if ( $add_notices ) {
				wc_add_notice(
					sprintf(
						/* translators: %s: maximum height */
						__( 'Boy değeri en fazla %s m olabilir.', 'custom-measurements-price-calculator' ),
						wc_format_decimal( $max_h, 2 )
					),
					'error'
				);
			}
		}

		return $ok;
	}

	/**
	 * Calculate area and total price.
	 *
	 * @param float      $width Width in meters.
	 * @param float      $height Height in meters.
	 * @param WC_Product $product Product.
	 * @return array{area:float,total:float,base:float}
	 */
	public static function calculate_pricing( $width, $height, WC_Product $product ) {
		$base_price = self::get_base_m2_price( $product );
		$area       = (float) ( $width * $height );
		$area       = (float) wc_format_decimal( $area, 4 );
		$total      = (float) wc_format_decimal( $area * $base_price, wc_get_price_decimals() );

		return array(
			'area'  => $area,
			'total' => $total,
			'base'  => $base_price,
		);
	}
}

