<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Order integration.
 */
class CMPC_Order {

	/**
	 * Init hooks.
	 */
	public static function init() {
		add_action( 'woocommerce_checkout_create_order_line_item', array( __CLASS__, 'add_order_item_meta' ), 10, 3 );
	}

	/**
	 * Store measurement data in order item meta.
	 *
	 * @param WC_Order_Item_Product $item Order item.
	 * @param string                $cart_item_key Cart item key.
	 * @param array                 $values Cart item values.
	 */
	public static function add_order_item_meta( $item, $cart_item_key, $values ) {
		if ( empty( $values['cmpc_measurement'] ) || ! is_array( $values['cmpc_measurement'] ) ) {
			return;
		}

		$measure = $values['cmpc_measurement'];

		$width      = isset( $measure['width'] ) ? (float) $measure['width'] : 0;
		$height     = isset( $measure['height'] ) ? (float) $measure['height'] : 0;
		$area       = isset( $measure['area'] ) ? (float) $measure['area'] : 0;
		$base_price = isset( $measure['base_price'] ) ? (float) $measure['base_price'] : 0;
		$total      = isset( $measure['total'] ) ? (float) $measure['total'] : 0;

		$item->add_meta_data(
			__( 'En', 'custom-measurements-price-calculator' ),
			wc_format_decimal( $width, 2 ) . ' m',
			true
		);

		$item->add_meta_data(
			__( 'Boy', 'custom-measurements-price-calculator' ),
			wc_format_decimal( $height, 2 ) . ' m',
			true
		);

		$item->add_meta_data(
			__( 'Alan', 'custom-measurements-price-calculator' ),
			wc_format_decimal( $area, 2 ) . ' m²',
			true
		);

		$item->add_meta_data(
			__( 'm² Fiyatı', 'custom-measurements-price-calculator' ),
			wc_format_decimal( $base_price, 2 ),
			true
		);

		$item->add_meta_data(
			__( 'Hesaplanan Toplam', 'custom-measurements-price-calculator' ),
			wc_format_decimal( $total, 2 ),
			true
		);
	}
}

