<?php
/**
 * WooCommerce admin order meta box for activation data.
 *
 * @package Net_Order_Activation
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add custom meta box to WooCommerce orders.
 */
function noa_add_order_meta_box() {
	add_meta_box(
		'noa_order_activation',
		__( 'Order Activation', 'net-order-activation' ),
		'noa_render_order_meta_box',
		'shop_order',
		'side',
		'default'
	);
}
add_action( 'add_meta_boxes', 'noa_add_order_meta_box' );

/**
 * Render the order activation meta box.
 *
 * @param WP_Post $post Post object.
 */
function noa_render_order_meta_box( $post ) {
	$order = wc_get_order( $post->ID );
	if ( ! $order ) {
		return;
	}

	wp_nonce_field( 'noa_save_order_meta', 'noa_order_meta_nonce' );

	$activation_code   = $order->get_meta( 'activation_code' );
	$activation_status = (bool) $order->get_meta( 'activation_status' );
	$linked_user_id    = $order->get_meta( 'linked_user_id' );

	?>
	<p>
		<label for="noa_activation_code"><strong><?php esc_html_e( 'Activation Code', 'net-order-activation' ); ?></strong></label><br />
		<input type="text" name="noa_activation_code" id="noa_activation_code" value="<?php echo esc_attr( $activation_code ); ?>" style="width:100%;" />
	</p>
	<p>
		<strong><?php esc_html_e( 'Activation Status', 'net-order-activation' ); ?></strong><br />
		<?php
		if ( $activation_status ) {
			echo '<span class="status-activated">' . esc_html__( 'Activated', 'net-order-activation' ) . '</span>';
		} else {
			echo '<span class="status-not-activated">' . esc_html__( 'Not activated', 'net-order-activation' ) . '</span>';
		}
		?>
	</p>
	<?php if ( $linked_user_id ) : ?>
		<p>
			<strong><?php esc_html_e( 'Linked User', 'net-order-activation' ); ?></strong><br />
			<?php
			$user = get_userdata( $linked_user_id );
			if ( $user ) {
				echo esc_html( $user->display_name ) . ' (' . esc_html( $user->user_email ) . ')';
			} else {
				echo esc_html( $linked_user_id );
			}
			?>
		</p>
	<?php endif; ?>
	<?php
}

/**
 * Save order activation meta when order is saved.
 *
 * @param int $post_id Post ID.
 */
function noa_save_order_meta_box( $post_id ) {
	if ( ! isset( $_POST['noa_order_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['noa_order_meta_nonce'] ) ), 'noa_save_order_meta' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( isset( $_POST['post_type'] ) && 'shop_order' !== $_POST['post_type'] ) {
		return;
	}

	if ( ! current_user_can( 'edit_shop_order', $post_id ) ) {
		return;
	}

	$order = wc_get_order( $post_id );
	if ( ! $order ) {
		return;
	}

	$activation_code = isset( $_POST['noa_activation_code'] ) ? sanitize_text_field( wp_unslash( $_POST['noa_activation_code'] ) ) : '';

	$order->update_meta_data( 'activation_code', $activation_code );
	$order->save();
}
add_action( 'save_post_shop_order', 'noa_save_order_meta_box', 10, 1 );

