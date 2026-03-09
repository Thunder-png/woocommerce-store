<?php
/**
 * Shop header component.
 */

defined( 'ABSPATH' ) || exit;

$lottie_url = get_stylesheet_directory_uri() . '/assets/branding/logo/logo-lottie-version.json';
?>

<header class="wcs-shop-header" aria-label="<?php esc_attr_e( 'Shop Header', 'woocommerce-store-child' ); ?>">
    <div class="wcs-shop-header__inner">
        <a class="wcs-shop-header__brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php esc_attr_e( 'Ana sayfaya dön', 'woocommerce-store-child' ); ?>">
            <span class="wcs-shop-header__logo" data-wcs-lottie data-lottie-src="<?php echo esc_url( $lottie_url ); ?>" aria-hidden="true"></span>
            <span class="wcs-shop-header__wordmark">SafetyNET</span>
        </a>

        <nav class="wcs-shop-header__nav" aria-label="<?php esc_attr_e( 'Ana menü', 'woocommerce-store-child' ); ?>">
            <a href="#products-grid"><?php esc_html_e( 'Ürünler', 'woocommerce-store-child' ); ?></a>
            <a href="#products-grid"><?php esc_html_e( 'Teknik Bilgi', 'woocommerce-store-child' ); ?></a>
            <a href="#products-grid"><?php esc_html_e( 'Projeler', 'woocommerce-store-child' ); ?></a>
            <a href="#products-grid"><?php esc_html_e( 'Hakkımızda', 'woocommerce-store-child' ); ?></a>
        </nav>

        <div class="wcs-shop-header__right">
            <span class="wcs-shop-header__tel"><i class="bi bi-telephone" aria-hidden="true"></i> +90 232 000 00 00</span>
            <a class="wcs-shop-header__cta" href="#products-grid"><i class="bi bi-chat-dots" aria-hidden="true"></i> <?php esc_html_e( 'Teklif Al', 'woocommerce-store-child' ); ?></a>
        </div>
    </div>
</header>
