<?php
/**
 * My Account navigation
 *
 * @package WooCommerceStoreChild
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_account_navigation' );
?>

<nav class="wcs-account-nav" aria-label="<?php esc_attr_e( 'Hesap menüsü', 'woocommerce-store-child' ); ?>">
	<ul class="wcs-account-nav__list">
		<?php foreach ( wc_get_account_menu_items() as $endpoint => $label ) : ?>
			<li class="wcs-account-nav__item <?php echo wc_get_account_menu_item_classes( $endpoint ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>">
				<a href="<?php echo esc_url( wc_get_account_endpoint_url( $endpoint ) ); ?>" class="wcs-account-nav__link">
					<span class="wcs-account-nav__label"><?php echo esc_html( $label ); ?></span>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
</nav>

<?php do_action( 'woocommerce_after_account_navigation' ); ?>

