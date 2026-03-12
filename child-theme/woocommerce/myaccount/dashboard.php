<?php
/**
 * My Account dashboard
 *
 * @package WooCommerceStoreChild
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_account_dashboard' );

$warranty_details = wcs_get_warranty_details( get_current_user_id() );
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
			<h2 class="wcs-account-card__title"><?php esc_html_e( 'Garanti Bilgilerim', 'woocommerce-store-child' ); ?></h2>
			<?php if ( ! empty( $warranty_details ) ) : ?>
				<ul class="wcs-account-warranty-list">
					<li><?php echo esc_html( sprintf( __( 'Garanti başlangıç tarihi: %s', 'woocommerce-store-child' ), $warranty_details['start_date'] ) ); ?></li>
					<li><?php echo esc_html( sprintf( __( 'Ürün garantisi (5 yıl) bitiş: %s', 'woocommerce-store-child' ), $warranty_details['product_expires'] ) ); ?></li>
					<li><?php echo esc_html( sprintf( __( 'Montaj garantisi (2 yıl) bitiş: %s', 'woocommerce-store-child' ), $warranty_details['installation_expires'] ) ); ?></li>
				</ul>
			<?php else : ?>
				<p>
					<?php esc_html_e( 'Garanti henüz başlatılmadı. Garanti aktivasyon sayfasından üyelik/giriş işlemini tamamlayarak başlatabilirsiniz.', 'woocommerce-store-child' ); ?>
				</p>
				<p>
					<a href="<?php echo esc_url( wcs_get_warranty_page_url() ); ?>">
						<?php esc_html_e( 'Garanti aktivasyon sayfasına git', 'woocommerce-store-child' ); ?>
					</a>
				</p>
			<?php endif; ?>
		</div>

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
