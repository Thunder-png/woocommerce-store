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
            <a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"><?php esc_html_e( 'Mağaza', 'woocommerce-store-child' ); ?></a>
            <a href="<?php echo esc_url( wc_get_page_permalink( 'cart' ) ); ?>"><?php esc_html_e( 'Sepet', 'woocommerce-store-child' ); ?></a>
            <a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>"><?php esc_html_e( 'Hesabım', 'woocommerce-store-child' ); ?></a>
            <a href="<?php echo esc_url( home_url( '/iletisim/' ) ); ?>"><?php esc_html_e( 'İletişim', 'woocommerce-store-child' ); ?></a>
        </nav>

        <div class="wcs-shop-header__right">
            <?php if ( function_exists( 'wc_get_page_permalink' ) ) : ?>
                <form role="search" method="get" class="wcs-shop-header__search" action="<?php echo esc_url( home_url( '/' ) ); ?>">
                    <input type="search" class="wcs-shop-header__search-input" placeholder="<?php esc_attr_e( 'Ürün ara…', 'woocommerce-store-child' ); ?>" value="<?php echo get_search_query(); ?>" name="s" />
                    <input type="hidden" name="post_type" value="product" />
                    <button type="submit" class="wcs-shop-header__search-btn" aria-label="<?php esc_attr_e( 'Ara', 'woocommerce-store-child' ); ?>">
                        <i class="bi bi-search" aria-hidden="true"></i>
                    </button>
                </form>
            <?php endif; ?>

            <a class="wcs-shop-header__icon-link" href="<?php echo esc_url( wc_get_page_permalink( 'cart' ) ); ?>" aria-label="<?php esc_attr_e( 'Sepeti görüntüle', 'woocommerce-store-child' ); ?>">
                <i class="bi bi-cart3" aria-hidden="true"></i>
            </a>
        </div>
    </div>
</header>
