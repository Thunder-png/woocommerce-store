<?php
/**
 * Cart and order integration for custom measure products.
 *
 * @package WCS_Custom_Measure
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Resolve calculator unit price from server-side attributes.
 *
 * @param string $thickness Thickness in mm.
 * @param string $mesh      Mesh size.
 * @param string $color     Color value or group.
 * @return float
 */
function wcs_resolve_unit_price_m2( $thickness, $mesh, $color ) {
	$thickness_f = (float) $thickness;
	$mesh        = strtolower( trim( (string) $mesh ) );
	$color_raw   = strtolower( trim( (string) $color ) );
	$color_group = 'standard';

	if ( false !== strpos( $color_raw, 'siyah' ) || 'black' === $color_raw ) {
		$color_group = 'black';
	} elseif (
		false !== strpos( $color_raw, 'gri' ) ||
		false !== strpos( $color_raw, 'mavi' ) ||
		false !== strpos( $color_raw, 'sari' ) ||
		false !== strpos( $color_raw, 'sarı' ) ||
		false !== strpos( $color_raw, 'turuncu' ) ||
		false !== strpos( $color_raw, 'yesil' ) ||
		false !== strpos( $color_raw, 'yeşil' ) ||
		'colored' === $color_raw
	) {
		$color_group = 'colored';
	}

	$key   = $thickness_f . '|' . $mesh;
	$price = null;

	switch ( $key ) {
		case '1.5|2x2':
			$price = 45;
			break;
		case '2|4x4':
			$price = 50;
			break;
		case '2.5|4x4':
			$price = 55;
			break;
		case '3|4x4':
			$price = 60;
			break;
		case '4|4x4':
			$price = 70;
			break;
		case '6|10x10':
			$price = 70;
			break;
		case '2|13x13':
			$price = 52;
			break;
		case '4|12x12':
			$price = 60;
			break;
		case '4|5x5':
			$price = 100;
			break;
	}

	if ( 4.0 === $thickness_f && '5x5' === $mesh && 'colored' === $color_group ) {
		$price = 105;
	}

	if ( 1.5 === $thickness_f && '2x2' === $mesh && 'colored' === $color_group ) {
		$price = 50;
	}

	if ( 2.0 === $thickness_f && '4x4' === $mesh && 'black' === $color_group ) {
		$price = 70;
	}

	if ( 3.0 === $thickness_f && '4x4' === $mesh && 'black' === $color_group ) {
		$price = 75;
	}

	if ( 6.0 === $thickness_f && '10x10' === $mesh && 'black' === $color_group ) {
		$price = 95;
	}

	return null === $price ? 0.0 : (float) $price;
}

/**
 * Calculate and sanitize posted custom measure payload.
 *
 * @param WC_Product $product Product.
 * @return array<string,float|string>|null
 */
function wcs_build_measure_payload_from_post( $product ) {
	if ( ! $product instanceof WC_Product || ! wcs_is_custom_measure_product( $product ) ) {
		return null;
	}

	$width   = isset( $_POST['wcs_width'] ) ? (float) wc_clean( wp_unslash( $_POST['wcs_width'] ) ) : 0;
	$height  = isset( $_POST['wcs_height'] ) ? (float) wc_clean( wp_unslash( $_POST['wcs_height'] ) ) : 0;
	$mesh    = isset( $_POST['wcs_mesh'] ) ? wc_clean( wp_unslash( $_POST['wcs_mesh'] ) ) : '';
	$thick   = isset( $_POST['wcs_thickness'] ) ? wc_clean( wp_unslash( $_POST['wcs_thickness'] ) ) : '';
	$color   = isset( $_POST['wcs_color'] ) ? wc_clean( wp_unslash( $_POST['wcs_color'] ) ) : '';
	$attr_ob = isset( $_POST['attribute_pa_en-boy-orani'] ) ? wc_clean( wp_unslash( $_POST['attribute_pa_en-boy-orani'] ) ) : '';

	if ( $width <= 0 || $height <= 0 || '' === $mesh || '' === $thick ) {
		return null;
	}

	$unit_price = wcs_resolve_unit_price_m2( $thick, $mesh, $color );

	if ( $unit_price <= 0 ) {
		return null;
	}

	$area             = round( $width * $height, 4 );
	$calculated_total = round( $area * $unit_price, wc_get_price_decimals() );

	if ( $area <= 0 || $calculated_total <= 0 ) {
		return null;
	}

	return array(
		'width'            => $width,
		'height'           => $height,
		'area'             => $area,
		'unit_price_m2'    => $unit_price,
		'calculated_total' => $calculated_total,
		'thickness'        => $thick,
		'mesh'             => $mesh,
		'color'            => $color,
		'attribute'        => $attr_ob,
	);
}

/**
 * Persist calculator data onto cart item.
 */
function wcs_add_measure_data_to_cart_item( $cart_item_data, $product_id, $variation_id ) {
	$product = wc_get_product( $variation_id ? $variation_id : $product_id );
	$payload = wcs_build_measure_payload_from_post( $product );

	if ( null === $payload ) {
		return $cart_item_data;
	}

	$cart_item_data['wcs_measure']        = $payload;
	$cart_item_data['wcs_measure_unique'] = md5( wp_json_encode( $payload ) . microtime() );

	return $cart_item_data;
}
add_filter( 'woocommerce_add_cart_item_data', 'wcs_add_measure_data_to_cart_item', 10, 3 );

/**
 * Show custom measure data on cart/checkout item rows.
 */
function wcs_render_measure_item_data( $item_data, $cart_item ) {
	if ( empty( $cart_item['wcs_measure'] ) || ! is_array( $cart_item['wcs_measure'] ) ) {
		return $item_data;
	}

	$measure = $cart_item['wcs_measure'];

	$item_data[] = array(
		'key'   => __( 'En', 'woocommerce-store-child' ),
		'value' => wc_format_decimal( (float) $measure['width'], 2 ) . ' m',
	);
	$item_data[] = array(
		'key'   => __( 'Boy', 'woocommerce-store-child' ),
		'value' => wc_format_decimal( (float) $measure['height'], 2 ) . ' m',
	);
	$item_data[] = array(
		'key'   => __( 'm²', 'woocommerce-store-child' ),
		'value' => wc_format_decimal( (float) $measure['area'], 2 ) . ' m²',
	);
	$item_data[] = array(
		'key'   => __( 'Birim Fiyat', 'woocommerce-store-child' ),
		'value' => wc_price( (float) $measure['unit_price_m2'] ) . '/m²',
	);

	return $item_data;
}
add_filter( 'woocommerce_get_item_data', 'wcs_render_measure_item_data', 10, 2 );

/**
 * Apply recalculated custom-measure line prices.
 */
function wcs_apply_measure_price_to_cart( $cart ) {
	if ( is_admin() && ! wp_doing_ajax() ) {
		return;
	}

	if ( ! $cart instanceof WC_Cart ) {
		return;
	}

	foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {
		if ( empty( $cart_item['wcs_measure'] ) || ! is_array( $cart_item['wcs_measure'] ) ) {
			continue;
		}

		$product = isset( $cart_item['data'] ) && $cart_item['data'] instanceof WC_Product ? $cart_item['data'] : null;

		if ( ! $product instanceof WC_Product || ! wcs_is_custom_measure_product( $product ) ) {
			continue;
		}

		$measure = $cart_item['wcs_measure'];
		$unit    = wcs_resolve_unit_price_m2( $measure['thickness'] ?? '', $measure['mesh'] ?? '', $measure['color'] ?? '' );
		$area    = isset( $measure['width'], $measure['height'] ) ? ( (float) $measure['width'] * (float) $measure['height'] ) : 0;

		if ( $unit <= 0 || $area <= 0 ) {
			continue;
		}

		$line_price = round( $unit * $area, wc_get_price_decimals() );
		$cart->cart_contents[ $cart_item_key ]['wcs_measure']['unit_price_m2']    = $unit;
		$cart->cart_contents[ $cart_item_key ]['wcs_measure']['area']             = round( $area, 4 );
		$cart->cart_contents[ $cart_item_key ]['wcs_measure']['calculated_total'] = $line_price;

		$product->set_price( $line_price );
	}
}
add_action( 'woocommerce_before_calculate_totals', 'wcs_apply_measure_price_to_cart', 20 );

/**
 * Save custom measure metadata to order line items.
 */
function wcs_add_measure_meta_to_order_item( $item, $cart_item_key, $values ) {
	if ( empty( $values['wcs_measure'] ) || ! is_array( $values['wcs_measure'] ) ) {
		return;
	}

	$measure = $values['wcs_measure'];

	$item->add_meta_data( __( 'En', 'woocommerce-store-child' ), wc_format_decimal( (float) $measure['width'], 2 ) . ' m', true );
	$item->add_meta_data( __( 'Boy', 'woocommerce-store-child' ), wc_format_decimal( (float) $measure['height'], 2 ) . ' m', true );
	$item->add_meta_data( __( 'm²', 'woocommerce-store-child' ), wc_format_decimal( (float) $measure['area'], 2 ) . ' m²', true );
	$item->add_meta_data( __( 'Birim Fiyat', 'woocommerce-store-child' ), wc_format_decimal( (float) $measure['unit_price_m2'], 2 ), true );
	$item->add_meta_data( __( 'Hesaplanan Toplam', 'woocommerce-store-child' ), wc_format_decimal( (float) $measure['calculated_total'], 2 ), true );
}
add_action( 'woocommerce_checkout_create_order_line_item', 'wcs_add_measure_meta_to_order_item', 10, 3 );

