<?php
/**
 * Checkout Form
 *
 * Custom wrapper + grid layout, Woo default structure preserved.
 *
 * @package WooCommerceStoreChild
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_checkout_form', $checkout );

if ( ! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in() ) {
	echo esc_html( apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'Ödeme yapabilmek için giriş yapmalısınız.', 'woocommerce-store-child' ) ) );
	return;
}
?>

<section class="wcs-checkout-shell">
	<header class="wcs-checkout-shell__header">
		<h1 class="wcs-checkout-shell__title"><?php esc_html_e( 'Ödeme', 'woocommerce-store-child' ); ?></h1>
		<p class="wcs-checkout-shell__subtitle"><?php esc_html_e( 'Teslimat ve fatura bilgilerinizi doldurun, ardından siparişinizi onaylayın.', 'woocommerce-store-child' ); ?></p>
	</header>

	<form name="checkout" method="post" class="checkout woocommerce-checkout wcs-checkout-shell__grid" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data">
		<div class="wcs-checkout-shell__col wcs-checkout-shell__col--details">
			<?php if ( $checkout->get_checkout_fields() ) : ?>
				<?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>

				<div class="wcs-checkout-section" id="customer_details">
					<div class="wcs-checkout-section__inner">
						<?php do_action( 'woocommerce_checkout_billing' ); ?>
						<?php do_action( 'woocommerce_checkout_shipping' ); ?>
					</div>
				</div>

				<?php do_action( 'woocommerce_checkout_after_customer_details' ); ?>
			<?php endif; ?>
		</div>

		<aside class="wcs-checkout-shell__col wcs-checkout-shell__col--summary">
			<div class="wcs-checkout-summary">
				<?php do_action( 'woocommerce_checkout_before_order_review_heading' ); ?>

				<h2 id="order_review_heading" class="wcs-checkout-summary__title">
					<?php esc_html_e( 'Sipariş Özeti', 'woocommerce-store-child' ); ?>
				</h2>

				<div id="order_review" class="woocommerce-checkout-review-order wcs-checkout-summary__body">
					<?php do_action( 'woocommerce_checkout_before_order_review' ); ?>

					<?php do_action( 'woocommerce_checkout_order_review' ); ?>

					<?php do_action( 'woocommerce_checkout_after_order_review' ); ?>
				</div>
			</div>
		</aside>
	</form>
</section>

<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>

