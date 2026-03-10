<?php
/**
 * My Account dashboard
 *
 * @package WooCommerceStoreChild
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_account_dashboard' );
?>

<section class="wcs-account-dashboard">
	<header class="wcs-account-dashboard__header">
		<h1 class="wcs-account-dashboard__title">
			<?php
			printf(
				/* translators: 1: user display name 2: logout url */
				esc_html__( 'Hoş geldiniz, %1$s', 'woocommerce-store-child' ),
				esc_html( wp_get_current_user()->display_name )
			);
			?>
		</h1>
		<p class="wcs-account-dashboard__subtitle">
			<?php esc_html_e( 'Siparişlerinizi, adreslerinizi ve hesap ayarlarınızı buradan yönetebilirsiniz.', 'woocommerce-store-child' ); ?>
		</p>
	</header>

	<div class="wcs-account-dashboard__grid">
		<div class="wcs-account-card">
			<h2 class="wcs-account-card__title"><?php esc_html_e( 'Son Siparişler', 'woocommerce-store-child' ); ?></h2>
			<?php do_action( 'woocommerce_before_account_orders', get_current_user_id() ); ?>
			<?php wc_get_template( 'myaccount/orders.php' ); ?>
		</div>

		<div class="wcs-account-card">
			<h2 class="wcs-account-card__title"><?php esc_html_e( 'Adresler', 'woocommerce-store-child' ); ?></h2>
			<?php wc_get_template( 'myaccount/my-address.php' ); ?>
		</div>
	</div>
</section>

