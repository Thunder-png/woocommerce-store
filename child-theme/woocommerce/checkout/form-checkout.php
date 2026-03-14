<?php
/**
 * Checkout Form — Karaca File Child Theme.
 *
 * Adım göstergesi + güven bandı + 2 kolonlu grid:
 * Sol: Fatura & Teslimat bilgileri | Sağ: Sipariş özeti + Ödeme
 *
 * @package WooCommerce_Store_Child
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_checkout_form', $checkout );

if ( ! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in() ) {
	echo esc_html( apply_filters( 'woocommerce_checkout_must_be_logged_in_message',
		__( 'Ödeme yapabilmek için giriş yapmalısınız.', 'woocommerce-store-child' ) ) );
	return;
}
?>

<div class="wcs-co-wrap">

	<!-- ── BAŞLIK & ADIM GÖSTERGESİ ─────────────────────── -->
	<header class="wcs-co-header">
		<div class="wcs-co-header__left">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="wcs-co-header__logo-link" aria-label="<?php esc_attr_e( 'Ana Sayfa', 'woocommerce-store-child' ); ?>">
				<span class="wcs-co-header__logo-text">Karaca File</span>
			</a>
			<nav class="wcs-co-breadcrumb" aria-label="<?php esc_attr_e( 'Sipariş adımları', 'woocommerce-store-child' ); ?>">
				<a href="<?php echo esc_url( wc_get_cart_url() ); ?>" class="wcs-co-breadcrumb__step">
					<i class="bi bi-cart3"></i>
					<?php esc_html_e( 'Sepet', 'woocommerce-store-child' ); ?>
				</a>
				<i class="bi bi-chevron-right wcs-co-breadcrumb__sep"></i>
				<span class="wcs-co-breadcrumb__step wcs-co-breadcrumb__step--active">
					<i class="bi bi-credit-card"></i>
					<?php esc_html_e( 'Ödeme', 'woocommerce-store-child' ); ?>
				</span>
				<i class="bi bi-chevron-right wcs-co-breadcrumb__sep"></i>
				<span class="wcs-co-breadcrumb__step wcs-co-breadcrumb__step--pending">
					<i class="bi bi-check-circle"></i>
					<?php esc_html_e( 'Onay', 'woocommerce-store-child' ); ?>
				</span>
			</nav>
		</div>

		<!-- Güven rozetleri -->
		<div class="wcs-co-header__trust">
			<span class="wcs-co-trust-item">
				<i class="bi bi-lock-fill"></i>
				<?php esc_html_e( 'SSL Güvenli', 'woocommerce-store-child' ); ?>
			</span>
			<span class="wcs-co-trust-item">
				<i class="bi bi-shield-fill-check"></i>
				<?php esc_html_e( 'Güvenli Ödeme', 'woocommerce-store-child' ); ?>
			</span>
			<span class="wcs-co-trust-item">
				<i class="bi bi-arrow-return-left"></i>
				<?php esc_html_e( '30 Gün İade', 'woocommerce-store-child' ); ?>
			</span>
		</div>
	</header>

	<!-- ── ANA FORM ──────────────────────────────────────── -->
	<form name="checkout" method="post"
		  class="checkout woocommerce-checkout wcs-co-form"
		  action="<?php echo esc_url( wc_get_checkout_url() ); ?>"
		  enctype="multipart/form-data">

		<!-- SOL: Fatura & Teslimat -->
		<div class="wcs-co-form__left">

			<!-- Giriş yapılmamışsa login hatırlatıcı -->
			<?php if ( ! is_user_logged_in() && $checkout->is_registration_enabled() ) : ?>
				<div class="wcs-co-login-notice">
					<i class="bi bi-person-circle"></i>
					<span>
						<?php esc_html_e( 'Üye misiniz?', 'woocommerce-store-child' ); ?>
						<a href="#" class="showlogin"><?php esc_html_e( 'Giriş yapın', 'woocommerce-store-child' ); ?></a>
					</span>
				</div>
			<?php endif; ?>

			<?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>

			<div id="customer_details">
				<!-- Fatura bölümü -->
				<div class="wcs-co-section">
					<h2 class="wcs-co-section__title">
						<span class="wcs-co-section__num">1</span>
						<i class="bi bi-person-lines-fill"></i>
						<?php esc_html_e( 'Fatura Bilgileri', 'woocommerce-store-child' ); ?>
					</h2>
					<?php do_action( 'woocommerce_checkout_billing' ); ?>
				</div>

				<!-- Teslimat bölümü -->
				<div class="wcs-co-section">
					<h2 class="wcs-co-section__title">
						<span class="wcs-co-section__num">2</span>
						<i class="bi bi-geo-alt-fill"></i>
						<?php esc_html_e( 'Teslimat Adresi', 'woocommerce-store-child' ); ?>
					</h2>
					<?php do_action( 'woocommerce_checkout_shipping' ); ?>
				</div>

				<!-- Ek notlar -->
				<div class="wcs-co-section wcs-co-section--notes">
					<h2 class="wcs-co-section__title">
						<span class="wcs-co-section__num">3</span>
						<i class="bi bi-chat-left-text"></i>
						<?php esc_html_e( 'Sipariş Notları', 'woocommerce-store-child' ); ?>
						<span class="wcs-co-section__optional"><?php esc_html_e( '(İsteğe bağlı)', 'woocommerce-store-child' ); ?></span>
					</h2>
					<?php
					// Sadece order_comments alanını buraya çek
					foreach ( $checkout->get_checkout_fields( 'order' ) as $key => $field ) {
						woocommerce_form_field( $key, $field, $checkout->get_value( $key ) );
					}
					?>
				</div>
			</div>

			<?php do_action( 'woocommerce_checkout_after_customer_details' ); ?>

		</div><!-- /.wcs-co-form__left -->

		<!-- SAĞ: Sipariş özeti + Ödeme -->
		<aside class="wcs-co-form__right">

			<!-- Sipariş özeti sticky kartı -->
			<div class="wcs-co-summary" id="order_review">
				<h2 class="wcs-co-summary__title">
					<i class="bi bi-bag-check-fill"></i>
					<?php esc_html_e( 'Sipariş Özeti', 'woocommerce-store-child' ); ?>
					<span class="wcs-co-summary__item-count">
						<?php
						$count = WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
						printf( esc_html__( '%d ürün', 'woocommerce-store-child' ), absint( $count ) );
						?>
					</span>
				</h2>

				<?php do_action( 'woocommerce_checkout_before_order_review' ); ?>

				<!-- WooCommerce sipariş tablosu + ödeme yöntemleri -->
				<div class="wcs-co-summary__body">
					<?php do_action( 'woocommerce_checkout_order_review' ); ?>
				</div>

				<?php do_action( 'woocommerce_checkout_after_order_review' ); ?>

				<!-- Güven notu -->
				<div class="wcs-co-summary__security">
					<i class="bi bi-lock-fill"></i>
					<span><?php esc_html_e( 'Bilgileriniz 256-bit SSL ile şifrelenerek iletilmektedir.', 'woocommerce-store-child' ); ?></span>
				</div>

				<!-- Ödeme yöntemleri ikonları -->
				<div class="wcs-co-summary__payment-icons">
					<?php foreach ( array( 'Visa', 'Mastercard', 'Havale', 'Kapıda Ödeme' ) as $pm ) : ?>
						<span class="wcs-co-payment-pill"><?php echo esc_html( $pm ); ?></span>
					<?php endforeach; ?>
				</div>
			</div>

		</aside><!-- /.wcs-co-form__right -->

	</form>

</div><!-- /.wcs-co-wrap -->

<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>
