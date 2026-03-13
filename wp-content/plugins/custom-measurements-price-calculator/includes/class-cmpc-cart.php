<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cart integration.
 */
class CMPC_Cart {

	/**
	 * Init hooks.
	 */
	public static function init() {
		add_filter( 'woocommerce_add_cart_item_data', array( __CLASS__, 'add_cart_item_data' ), 10, 3 );
		add_action( 'woocommerce_before_calculate_totals', array( __CLASS__, 'before_calculate_totals' ), 20 );
		add_filter( 'woocommerce_get_item_data', array( __CLASS__, 'display_cart_item_data' ), 10, 2 );
	}

	/**
	 * Add measurement data to cart item.
	 *
	 * @param array $cart_item_data Existing cart item data.
	 * @param int   $product_id Product ID.
	 * @param int   $variation_id Variation ID.
	 * @return array
	 */
	public static function add_cart_item_data( $cart_item_data, $product_id, $variation_id ) {
		$product = wc_get_product( $variation_id ? $variation_id : $product_id );

		if ( ! CMPC_Helpers::is_custom_measure_product( $product ) ) {
			return $cart_item_data;
		}

		$width  = isset( $_POST['cmpc_width'] ) ? (float) wc_clean( wp_unslash( $_POST['cmpc_width'] ) ) : 0;
		$height = isset( $_POST['cmpc_height'] ) ? (float) wc_clean( wp_unslash( $_POST['cmpc_height'] ) ) : 0;

		// Backstop validation (frontend + validate_add_to_cart zaten yaptı).
		if ( $width <= 0 || $height <= 0 ) {
			return $cart_item_data;
		}

		$pricing = CMPC_Helpers::calculate_pricing( $width, $height, $product );

		$cart_item_data['cmpc_measurement'] = array(
			'width'      => $width,
			'height'     => $height,
			'area'       => $pricing['area'],
			'base_price' => $pricing['base'],
			'total'      => $pricing['total'],
		);

		// Unique key so duplicated custom measurements create separate cart items.
		$cart_item_data['cmpc_key'] = md5( wp_json_encode( $cart_item_data['cmpc_measurement'] ) . microtime() );

		return $cart_item_data;
	}

	/**
	 * Override product price in cart with calculated total (per unit).
	 *
	 * @param WC_Cart $cart Cart object.
	 */
	public static function before_calculate_totals( $cart ) {
		if ( is_admin() && ! wp_doing_ajax() ) {
			return;
		}

		if ( ! $cart instanceof WC_Cart ) {
			return;
		}

		foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {
			if ( empty( $cart_item['cmpc_measurement'] ) || ! is_array( $cart_item['cmpc_measurement'] ) ) {
				continue;
			}

			/** @var WC_Product $product */
			$product = isset( $cart_item['data'] ) && $cart_item['data'] instanceof WC_Product ? $cart_item['data'] : null;

			if ( ! $product instanceof WC_Product || ! CMPC_Helpers::is_custom_measure_product( $product ) ) {
				continue;
			}

			$measure = $cart_item['cmpc_measurement'];

			$width  = isset( $measure['width'] ) ? (float) $measure['width'] : 0;
			$height = isset( $measure['height'] ) ? (float) $measure['height'] : 0;

			// Recalculate for safety.
			if ( $width <= 0 || $height <= 0 ) {
				continue;
			}

			$pricing = CMPC_Helpers::calculate_pricing( $width, $height, $product );

			// Update cart item data so order meta has consistent numbers.
			$cart->cart_contents[ $cart_item_key ]['cmpc_measurement']['area']       = $pricing['area'];
			$cart->cart_contents[ $cart_item_key ]['cmpc_measurement']['base_price'] = $pricing['base'];
			$cart->cart_contents[ $cart_item_key ]['cmpc_measurement']['total']      = $pricing['total'];

			// Set per-unit price; WooCommerce will multiply by qty.
			$product->set_price( $pricing['total'] );
		}
	}

	/**
	 * Show measurement data in cart and checkout.
	 *
	 * @param array $item_data Display data.
	 * @param array $cart_item Cart item.
	 * @return array
	 */
	public static function display_cart_item_data( $item_data, $cart_item ) {
		if ( empty( $cart_item['cmpc_measurement'] ) || ! is_array( $cart_item['cmpc_measurement'] ) ) {
			return $item_data;
		}

		$measure = $cart_item['cmpc_measurement'];

		$item_data[] = array(
			'key'   => __( 'En', 'custom-measurements-price-calculator' ),
			'value' => wc_format_decimal( (float) $measure['width'], 2 ) . ' m',
		);

		$item_data[] = array(
			'key'   => __( 'Boy', 'custom-measurements-price-calculator' ),
			'value' => wc_format_decimal( (float) $measure['height'], 2 ) . ' m',
		);

		$item_data[] = array(
			'key'   => __( 'Alan', 'custom-measurements-price-calculator' ),
			'value' => wc_format_decimal( (float) $measure['area'], 2 ) . ' m²',
		);

		return $item_data;
	}
}

