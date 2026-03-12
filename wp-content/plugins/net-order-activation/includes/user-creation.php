<?php
/**
 * User creation and order linking.
 *
 * @package Net_Order_Activation
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Verify the order token generated during activation verification.
 *
 * @param int    $order_id Order ID.
 * @param string $activation_code Activation code.
 * @param string $phone_number Phone number used.
 * @param string $token Provided token.
 * @return bool
 */
function noa_verify_order_token( $order_id, $activation_code, $phone_number, $token ) {
	$normalized_phone = noa_normalize_phone( $phone_number );
	$expected_action  = 'noa_order_' . $order_id . '|' . $activation_code . '|' . $normalized_phone;

	return wp_verify_nonce( $token, $expected_action );
}

/**
 * Create user and link to order.
 */
function noa_ajax_create_user_and_link_order() {
	check_ajax_referer( 'noa_activation_nonce', 'nonce' );

	if ( ! noa_rate_limited( 'create_user_and_link_order', 3, 900 ) ) {
		wp_send_json_error(
			array(
				'message' => __( 'Çok fazla deneme yaptınız. Lütfen biraz sonra tekrar deneyin.', 'net-order-activation' ),
			),
			429
		);
	}

	$name            = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
	$email           = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	$password        = isset( $_POST['password'] ) ? (string) $_POST['password'] : '';
	$privacy         = isset( $_POST['privacy'] ) ? (int) $_POST['privacy'] : 0;
	$activation_code = isset( $_POST['activation_code'] ) ? sanitize_text_field( wp_unslash( $_POST['activation_code'] ) ) : '';
	$phone_number    = isset( $_POST['phone_number'] ) ? sanitize_text_field( wp_unslash( $_POST['phone_number'] ) ) : '';
	$order_id        = isset( $_POST['order_id'] ) ? (int) $_POST['order_id'] : 0;
	$order_token     = isset( $_POST['order_token'] ) ? sanitize_text_field( wp_unslash( $_POST['order_token'] ) ) : '';

	if ( ! $name || ! $email || ! $password || ! $order_id || ! $activation_code || ! $phone_number || ! $order_token ) {
		wp_send_json_error(
			array(
				'message' => __( 'Lütfen tüm zorunlu alanları doldurun.', 'net-order-activation' ),
			)
		);
	}

	if ( ! is_email( $email ) ) {
		wp_send_json_error(
			array(
				'message' => __( 'Lütfen geçerli bir e-posta adresi girin.', 'net-order-activation' ),
			)
		);
	}

	if ( strlen( $password ) < 6 ) {
		wp_send_json_error(
			array(
				'message' => __( 'Lütfen en az 6 karakterden oluşan daha güçlü bir şifre seçin.', 'net-order-activation' ),
			)
		);
	}

	if ( ! $privacy ) {
		wp_send_json_error(
			array(
				'message' => __( 'Devam etmek için gizlilik politikasını kabul etmelisiniz.', 'net-order-activation' ),
			)
		);
	}

	if ( ! noa_validate_phone( $phone_number ) ) {
		wp_send_json_error(
			array(
				'message' => __( 'Lütfen geçerli bir telefon numarası girin.', 'net-order-activation' ),
			)
		);
	}

	$order = wc_get_order( $order_id );

	if ( ! $order ) {
		wp_send_json_error(
			array(
				'message' => __( 'Sipariş bulunamadı.', 'net-order-activation' ),
			)
		);
	}

	// Prevent reuse / tampering by checking token and order data again.
	if ( ! noa_verify_order_token( $order_id, $activation_code, $phone_number, $order_token ) ) {
		wp_send_json_error(
			array(
				'message' => __( 'Sipariş doğrulaması başarısız oldu. Lütfen aktivasyon sürecine baştan başlayın.', 'net-order-activation' ),
			)
		);
	}

	$stored_activation_code = $order->get_meta( 'activation_code' );
	if ( $stored_activation_code !== $activation_code ) {
		wp_send_json_error(
			array(
				'message' => __( 'Aktivasyon kodu eşleşmiyor.', 'net-order-activation' ),
			)
		);
	}

	$activation_status = (bool) $order->get_meta( 'activation_status' );
	if ( $activation_status ) {
		wp_send_json_error(
			array(
				'message' => __( 'Bu sipariş daha önce aktive edilmiş.', 'net-order-activation' ),
			)
		);
	}

	if ( ! noa_validate_phone_match( $order, $phone_number ) ) {
		wp_send_json_error(
			array(
				'message' => __( 'Telefon numarası kayıtlarımızla eşleşmiyor.', 'net-order-activation' ),
			)
		);
	}

	// Placeholder for future reCAPTCHA verification.

	// Create or retrieve user.
	$user_id = email_exists( $email );
	if ( ! $user_id ) {
		$username = sanitize_user( strtolower( str_replace( ' ', '.', $name ) ), true );

		if ( empty( $username ) ) {
			$username = sanitize_user( current( explode( '@', $email ) ), true );
		}

		// Ensure username uniqueness.
		$base_username = $username;
		$counter       = 1;
		while ( username_exists( $username ) ) {
			$counter++;
			$username = $base_username . $counter;
		}

		$user_id = wp_create_user( $username, $password, $email );

		if ( is_wp_error( $user_id ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Kullanıcı hesabı oluşturulamadı. Lütfen destek ile iletişime geçin.', 'net-order-activation' ),
				)
			);
		}

		// Set display name and role.
		wp_update_user(
			array(
				'ID'           => $user_id,
				'display_name' => $name,
				'first_name'   => $name,
			)
		);

		$user = new WP_User( $user_id );
		$user->set_role( 'customer' );
	}

	// Automatically sign the customer in so that "Sign In" sonucu gerçek bir oturum açma / kayıt deneyimi olur.
	if ( function_exists( 'wc_set_customer_auth_cookie' ) ) {
		wc_set_customer_auth_cookie( $user_id );
	} else {
		wp_set_current_user( $user_id );
		wp_set_auth_cookie( $user_id, true );
	}

	// Link order to user.
	$order->update_meta_data( 'linked_user_id', $user_id );
	$order->update_meta_data( 'activation_status', true );

	// Also set WooCommerce customer ID for this order.
	$order->set_customer_id( $user_id );
	$order->save();

	wp_send_json_success(
		array(
			'message'    => __( 'Hesabınız oluşturuldu ve siparişiniz hesabınıza başarıyla bağlandı.', 'net-order-activation' ),
			'redirectTo' => apply_filters( 'noa_activation_success_redirect', wc_get_page_permalink( 'myaccount' ) ),
		)
	);
}
add_action( 'wp_ajax_noa_create_user_and_link_order', 'noa_ajax_create_user_and_link_order' );
add_action( 'wp_ajax_nopriv_noa_create_user_and_link_order', 'noa_ajax_create_user_and_link_order' );

