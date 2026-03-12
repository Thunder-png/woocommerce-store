<?php
/**
 * AJAX handlers and core verification helpers.
 *
 * @package Net_Order_Activation
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Normalize phone numbers by stripping non-digits except leading plus.
 *
 * @param string $phone Raw phone.
 * @return string
 */
function noa_normalize_phone( $phone ) {
	$phone = trim( (string) $phone );

	// Keep leading + if present, then digits only.
	if ( '' === $phone ) {
		return '';
	}

	$has_plus = ( strpos( $phone, '+' ) === 0 );
	$digits   = preg_replace( '/\D+/', '', $phone );

	if ( $has_plus ) {
		return '+' . $digits;
	}

	return $digits;
}

/**
 * Basic phone validation.
 *
 * @param string $phone Phone string.
 * @return bool
 */
function noa_validate_phone( $phone ) {
	$normalized = noa_normalize_phone( $phone );

	if ( '' === $normalized ) {
		return false;
	}

	// Very simple length guard.
	return ( strlen( $normalized ) >= 7 && strlen( $normalized ) <= 20 );
}

/**
 * Simple rate limiting using transients by IP and action.
 *
 * @param string $action_key Unique key for action.
 * @param int    $max_attempts Maximum allowed attempts.
 * @param int    $window_in_seconds Window for rate limiting.
 * @return bool True if allowed, false if limit exceeded.
 */
function noa_rate_limited( $action_key, $max_attempts = 5, $window_in_seconds = 600 ) {
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : 'unknown';

	$key      = 'noa_rate_' . md5( $action_key . '|' . $ip );
	$attempts = get_transient( $key );

	if ( ! is_array( $attempts ) ) {
		$attempts = array();
	}

	$now       = time();
	$attempts  = array_filter(
		$attempts,
		function ( $ts ) use ( $now, $window_in_seconds ) {
			return ( $ts >= ( $now - $window_in_seconds ) );
		}
	);
	$attempts[] = $now;

	set_transient( $key, $attempts, $window_in_seconds );

	return ( count( $attempts ) <= $max_attempts );
}

/**
 * Find order by activation code.
 *
 * @param string $activation_code Activation code.
 * @return WC_Order|false
 */
function noa_find_order_by_activation_code( $activation_code ) {
	if ( empty( $activation_code ) ) {
		return false;
	}

	if ( ! class_exists( 'WC_Order_Query' ) ) {
		return false;
	}

	$query = new WC_Order_Query(
		array(
			'limit'        => 1,
			'status'       => array_keys( wc_get_order_statuses() ),
			'meta_key'     => 'activation_code',
			'meta_value'   => $activation_code,
			'meta_compare' => '=',
		)
	);

	$orders = $query->get_orders();

	if ( empty( $orders ) ) {
		return false;
	}

	return reset( $orders );
}

/**
 * Validate phone match with order.
 *
 * @param WC_Order $order Order object.
 * @param string   $phone Phone to compare.
 * @return bool
 */
function noa_validate_phone_match( $order, $phone ) {
	if ( ! $order instanceof WC_Order ) {
		return false;
	}

	$order_phone = $order->get_billing_phone();

	if ( '' === $order_phone ) {
		return false;
	}

	return noa_normalize_phone( $order_phone ) === noa_normalize_phone( $phone );
}

/**
 * AJAX handler: verify activation code and phone.
 */
function noa_ajax_verify_activation() {
	check_ajax_referer( 'noa_activation_nonce', 'nonce' );

	if ( ! noa_rate_limited( 'verify_activation' ) ) {
		wp_send_json_error(
			array(
				'message' => __( 'Too many attempts. Please try again later.', 'net-order-activation' ),
			),
			429
		);
	}

	$activation_code = isset( $_POST['activation_code'] ) ? sanitize_text_field( wp_unslash( $_POST['activation_code'] ) ) : '';
	$phone_number    = isset( $_POST['phone_number'] ) ? sanitize_text_field( wp_unslash( $_POST['phone_number'] ) ) : '';

	if ( '' === $activation_code || '' === $phone_number ) {
		wp_send_json_error(
			array(
				'message' => __( 'Please provide both activation code and phone number.', 'net-order-activation' ),
			)
		);
	}

	if ( ! noa_validate_phone( $phone_number ) ) {
		wp_send_json_error(
			array(
				'message' => __( 'Please provide a valid phone number.', 'net-order-activation' ),
			)
		);
	}

	// Placeholder for future reCAPTCHA verification.

	$order = noa_find_order_by_activation_code( $activation_code );

	if ( ! $order ) {
		wp_send_json_error(
			array(
				'message' => __( 'Order not found. Please check your activation code.', 'net-order-activation' ),
			)
		);
	}

	$activation_status = $order->get_meta( 'activation_status' );

	if ( (bool) $activation_status ) {
		wp_send_json_error(
			array(
				'message' => __( 'This order has already been activated.', 'net-order-activation' ),
			)
		);
	}

	if ( ! noa_validate_phone_match( $order, $phone_number ) ) {
		wp_send_json_error(
			array(
				'message' => __( 'Phone number does not match our records.', 'net-order-activation' ),
			)
		);
	}

	$items        = $order->get_items();
	$first_item   = reset( $items );
	$product_name = $first_item ? $first_item->get_name() : '';

	// Example dimensions coming from order item meta, adapt as needed.
	$width  = $first_item ? $first_item->get_meta( 'width' ) : '';
	$height = $first_item ? $first_item->get_meta( 'height' ) : '';

	$dimensions = '';
	if ( $width || $height ) {
		$dimensions = trim( $width . ' x ' . $height );
	}

	$order_token = wp_create_nonce( 'noa_order_' . $order->get_id() . '|' . $activation_code . '|' . noa_normalize_phone( $phone_number ) );

	wp_send_json_success(
		array(
			'order_id'     => $order->get_id(),
			'order_date'   => wc_format_datetime( $order->get_date_created() ),
			'product_name' => $product_name,
			'dimensions'   => $dimensions,
			'order_token'  => $order_token,
		)
	);
}
add_action( 'wp_ajax_noa_verify_activation', 'noa_ajax_verify_activation' );
add_action( 'wp_ajax_nopriv_noa_verify_activation', 'noa_ajax_verify_activation' );

