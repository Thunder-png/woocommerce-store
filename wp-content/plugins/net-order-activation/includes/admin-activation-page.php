<?php
/**
 * Standalone admin page for managing order activation data.
 *
 * This page works with HPOS and the new wc-orders UI by providing
 * a separate management screen under WooCommerce.
 *
 * @package Net_Order_Activation
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the "Order Activation" submenu under WooCommerce.
 */
function noa_register_admin_activation_page() {
	add_submenu_page(
		'woocommerce',
		__( 'Order Activation', 'net-order-activation' ),
		__( 'Order Activation', 'net-order-activation' ),
		'manage_woocommerce',
		'noa-order-activation',
		'noa_render_admin_activation_page'
	);
}
add_action( 'admin_menu', 'noa_register_admin_activation_page' );

/**
 * Handle form submissions for updating activation codes.
 */
function noa_handle_admin_activation_post() {
	if ( ! isset( $_POST['noa_activation_admin_action'] ) || 'update_activation' !== $_POST['noa_activation_admin_action'] ) {
		return;
	}

	if ( ! isset( $_POST['noa_activation_admin_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['noa_activation_admin_nonce'] ) ), 'noa_activation_admin' ) ) {
		return;
	}

	if ( ! current_user_can( 'manage_woocommerce' ) ) {
		return;
	}

	$order_id        = isset( $_POST['noa_order_id'] ) ? absint( $_POST['noa_order_id'] ) : 0;
	$activation_code = isset( $_POST['noa_activation_code'] ) ? sanitize_text_field( wp_unslash( $_POST['noa_activation_code'] ) ) : '';

	if ( ! $order_id ) {
		return;
	}

	$order = wc_get_order( $order_id );
	if ( ! $order ) {
		return;
	}

	$order->update_meta_data( 'activation_code', $activation_code );
	$order->save();

	// Redirect to avoid resubmission.
	wp_safe_redirect(
		add_query_arg(
			array(
				'page'          => 'noa-order-activation',
				'order_id'      => $order_id,
				'updated'       => 1,
			),
			admin_url( 'admin.php' )
		)
	);
	exit;
}
add_action( 'admin_init', 'noa_handle_admin_activation_post' );

/**
 * Generate a simple activation code if needed.
 *
 * @return string
 */
function noa_generate_activation_code() {
	$prefix = 'KF-';
	$rand   = wp_rand( 1000, 9999 );
	return $prefix . $rand;
}

/**
 * Render the standalone admin activation management page.
 */
function noa_render_admin_activation_page() {
	if ( ! current_user_can( 'manage_woocommerce' ) ) {
		wp_die( esc_html__( 'You do not have permission to access this page.', 'net-order-activation' ) );
	}

	$search_order_id = isset( $_GET['order_id'] ) ? absint( $_GET['order_id'] ) : 0;
	$search_phone    = isset( $_GET['phone'] ) ? sanitize_text_field( wp_unslash( $_GET['phone'] ) ) : '';
	$search_code     = isset( $_GET['activation_code'] ) ? sanitize_text_field( wp_unslash( $_GET['activation_code'] ) ) : '';

	$updated = isset( $_GET['updated'] ) ? absint( $_GET['updated'] ) : 0;

	$args = array(
		'limit'  => 20,
		'status' => array_keys( wc_get_order_statuses() ),
		'orderby'=> 'date',
		'order'  => 'DESC',
	);

	if ( $search_order_id ) {
		$args['include'] = array( $search_order_id );
	}

	$meta_query = array();

	if ( $search_phone ) {
		$meta_query[] = array(
			'key'     => '_billing_phone',
			'value'   => $search_phone,
			'compare' => 'LIKE',
		);
	}

	if ( $search_code ) {
		$meta_query[] = array(
			'key'     => 'activation_code',
			'value'   => $search_code,
			'compare' => 'LIKE',
		);
	}

	if ( ! empty( $meta_query ) ) {
		$args['meta_query'] = $meta_query;
	}

	$orders = wc_get_orders( $args );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Order Activation Management', 'net-order-activation' ); ?></h1>

		<?php if ( $updated ) : ?>
			<div id="message" class="updated notice is-dismissible">
				<p><?php esc_html_e( 'Activation code updated successfully.', 'net-order-activation' ); ?></p>
			</div>
		<?php endif; ?>

		<form method="get" style="margin-bottom: 20px;">
			<input type="hidden" name="page" value="noa-order-activation" />
			<table class="form-table">
				<tr>
					<th scope="row"><label for="noa_search_order_id"><?php esc_html_e( 'Order ID', 'net-order-activation' ); ?></label></th>
					<td><input type="number" name="order_id" id="noa_search_order_id" value="<?php echo $search_order_id ? esc_attr( $search_order_id ) : ''; ?>" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="noa_search_phone"><?php esc_html_e( 'Customer Phone', 'net-order-activation' ); ?></label></th>
					<td><input type="text" name="phone" id="noa_search_phone" value="<?php echo $search_phone ? esc_attr( $search_phone ) : ''; ?>" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="noa_search_activation_code"><?php esc_html_e( 'Activation Code', 'net-order-activation' ); ?></label></th>
					<td><input type="text" name="activation_code" id="noa_search_activation_code" value="<?php echo $search_code ? esc_attr( $search_code ) : ''; ?>" /></td>
				</tr>
			</table>
			<?php submit_button( __( 'Search Orders', 'net-order-activation' ), 'primary', '', false ); ?>
		</form>

		<?php if ( ! empty( $orders ) ) : ?>
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Order ID', 'net-order-activation' ); ?></th>
						<th><?php esc_html_e( 'Customer', 'net-order-activation' ); ?></th>
						<th><?php esc_html_e( 'Phone', 'net-order-activation' ); ?></th>
						<th><?php esc_html_e( 'Activation Code', 'net-order-activation' ); ?></th>
						<th><?php esc_html_e( 'Activation URL', 'net-order-activation' ); ?></th>
						<th><?php esc_html_e( 'Activation Status', 'net-order-activation' ); ?></th>
						<th><?php esc_html_e( 'Linked User', 'net-order-activation' ); ?></th>
						<th><?php esc_html_e( 'Actions', 'net-order-activation' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $orders as $order ) : ?>
						<?php
						/** @var WC_Order $order */
						$order_id         = $order->get_id();
						$customer_name    = $order->get_formatted_billing_full_name();
						$phone            = $order->get_billing_phone();
						$activation_code  = $order->get_meta( 'activation_code' );
						$activation_status = (bool) $order->get_meta( 'activation_status' );
						$linked_user_id   = $order->get_meta( 'linked_user_id' );
						?>
						<tr>
							<td>
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=wc-orders&action=edit&id=' . $order_id ) ); ?>">
									<?php echo esc_html( '#' . $order_id ); ?>
								</a>
							</td>
							<td><?php echo $customer_name ? esc_html( $customer_name ) : esc_html__( '(no name)', 'net-order-activation' ); ?></td>
							<td><?php echo $phone ? esc_html( $phone ) : '&mdash;'; ?></td>
							<td>
								<form method="post" style="display:flex; gap:4px; align-items:center;">
									<input type="hidden" name="noa_activation_admin_action" value="update_activation" />
									<?php wp_nonce_field( 'noa_activation_admin', 'noa_activation_admin_nonce' ); ?>
									<input type="hidden" name="noa_order_id" value="<?php echo esc_attr( $order_id ); ?>" />
									<input type="text" name="noa_activation_code" value="<?php echo esc_attr( $activation_code ); ?>" style="width: 110px;" />
									<button type="submit" class="button button-small"><?php esc_html_e( 'Save', 'net-order-activation' ); ?></button>
									<?php if ( ! $activation_code ) : ?>
										<button type="button" class="button button-small noa-generate-code" data-order-id="<?php echo esc_attr( $order_id ); ?>">
											<?php esc_html_e( 'Generate', 'net-order-activation' ); ?>
										</button>
									<?php endif; ?>
								</form>
							</td>
							<td>
								<?php
								if ( $activation_code && ! $activation_status ) {
									$page      = get_page_by_path( 'order-activate' );
									$base_url  = $page ? get_permalink( $page ) : home_url( '/order-activate/' );
									$act_url   = add_query_arg(
										'code',
										rawurlencode( $activation_code ),
										$base_url
									);
									?>
									<input
										type="text"
										class="noa-activation-url"
										readonly
										value="<?php echo esc_attr( $act_url ); ?>"
										style="width: 180px;"
									/>
									<button type="button" class="button button-small noa-copy-activation-url">
										<?php esc_html_e( 'Copy', 'net-order-activation' ); ?>
									</button>
									<?php
								} else {
									echo '&mdash;';
								}
								?>
							</td>
							<td>
								<?php
								if ( $activation_status ) {
									echo '<span class="status-activated">' . esc_html__( 'Activated', 'net-order-activation' ) . '</span>';
								} else {
									echo '<span class="status-not-activated">' . esc_html__( 'Not activated', 'net-order-activation' ) . '</span>';
								}
								?>
							</td>
							<td>
								<?php
								if ( $linked_user_id ) {
									$user = get_userdata( $linked_user_id );
									if ( $user ) {
										echo esc_html( $user->display_name ) . ' (' . esc_html( $user->user_email ) . ')';
									} else {
										echo esc_html( $linked_user_id );
									}
								} else {
									echo '&mdash;';
								}
								?>
							</td>
							<td>
								<a class="button button-small" href="<?php echo esc_url( admin_url( 'admin.php?page=wc-orders&action=edit&id=' . $order_id ) ); ?>">
									<?php esc_html_e( 'View Order', 'net-order-activation' ); ?>
								</a>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php else : ?>
			<p><?php esc_html_e( 'No orders found for the given criteria.', 'net-order-activation' ); ?></p>
		<?php endif; ?>
	</div>
	<script>
		// Simple in-place activation code generator using the same pattern as noa_generate_activation_code().
		(function() {
			function generateCode() {
				var rand = Math.floor(Math.random() * (9999 - 1000 + 1)) + 1000;
				return 'KF-' + rand;
			}
			document.querySelectorAll('.noa-generate-code').forEach(function(btn) {
				btn.addEventListener('click', function() {
					var form = btn.closest('form');
					var input = form.querySelector('input[name="noa_activation_code"]');
					if (input) {
						input.value = generateCode();
					}
				});
			});

			// Copy activation URL to clipboard.
			document.querySelectorAll('.noa-copy-activation-url').forEach(function(btn) {
				btn.addEventListener('click', function() {
					var container = btn.closest('td');
					if (!container) {
						return;
					}
					var input = container.querySelector('.noa-activation-url');
					if (!input) {
						return;
					}

					input.focus();
					input.select();

					try {
						document.execCommand('copy');
					} catch (e) {
						// Fallback: selection is still made, user can press Ctrl+C.
					}
				});
			});
		})();
	</script>
	<?php
}

/**
 * Add quick action in wc-orders list to jump to activation page.
 *
 * This filter covers the classic orders list (edit.php?post_type=shop_order).
 *
 * @param array   $actions Existing actions.
 * @param WC_Order $order  Order object.
 * @return array
 */
function noa_add_order_row_action( $actions, $order ) {
	if ( ! current_user_can( 'manage_woocommerce' ) ) {
		return $actions;
	}

	$order_id = $order->get_id();

	$url = add_query_arg(
		array(
			'page'     => 'noa-order-activation',
			'order_id' => $order_id,
		),
		admin_url( 'admin.php' )
	);

	$actions['noa_activation'] = sprintf(
		'<a href="%s">%s</a>',
		esc_url( $url ),
		esc_html__( 'Set Activation Code', 'net-order-activation' )
	);

	return $actions;
}
add_filter( 'woocommerce_admin_order_actions', 'noa_add_order_row_action', 20, 2 );

