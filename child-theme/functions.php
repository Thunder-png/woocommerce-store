<?php
/**
 * Theme functions for WooCommerce Store Child.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Return cache-busting version safely for child-theme assets.
 *
 * @param string $relative_path Relative path from child theme root.
 * @param string $fallback      Fallback version if file does not exist.
 * @return string
 */
function wcs_asset_version( $relative_path, $fallback = '1.0.0' ) {
    $full_path = trailingslashit( get_stylesheet_directory() ) . ltrim( $relative_path, '/' );

    if ( file_exists( $full_path ) ) {
        return (string) filemtime( $full_path );
    }

    return $fallback;
}

/**
 * Enqueue child theme assets.
 */
function wcs_child_enqueue_assets() {
    $parent_theme = wp_get_theme( get_template() );
    $child_theme  = wp_get_theme();
    $is_shop_front = function_exists( 'wc_get_page_id' ) && is_front_page() && (int) get_queried_object_id() === (int) wc_get_page_id( 'shop' );

    wp_enqueue_style(
        'wcs-parent-style',
        get_template_directory_uri() . '/style.css',
        array(),
        $parent_theme->get( 'Version' )
    );

    wp_enqueue_style(
        'wcs-child-style',
        get_stylesheet_uri(),
        array( 'wcs-parent-style' ),
        $child_theme->get( 'Version' )
    );

    wp_enqueue_style(
        'wcs-custom-style',
        get_stylesheet_directory_uri() . '/assets/css/style.css',
        array( 'wcs-child-style' ),
        wcs_asset_version( 'assets/css/style.css', $child_theme->get( 'Version' ) )
    );

    if ( function_exists( 'is_shop' ) && ( is_shop() || is_product_taxonomy() || $is_shop_front ) ) {
        $branding_base_url = trailingslashit( get_stylesheet_directory_uri() ) . 'assets/branding/';

        wp_enqueue_style(
            'wcs-brand-colors',
            $branding_base_url . 'colors/brand-colors.css',
            array( 'wcs-custom-style' ),
            wcs_asset_version( 'assets/branding/colors/brand-colors.css', $child_theme->get( 'Version' ) )
        );

        wp_enqueue_style(
            'wcs-brand-typography',
            $branding_base_url . 'typography/typo-kit.css',
            array( 'wcs-brand-colors' ),
            wcs_asset_version( 'assets/branding/typography/typo-kit.css', $child_theme->get( 'Version' ) )
        );

        wp_enqueue_style(
            'wcs-hero-style',
            get_stylesheet_directory_uri() . '/template-parts/components/hero/hero.css',
            array( 'wcs-brand-typography' ),
            wcs_asset_version( 'template-parts/components/hero/hero.css', $child_theme->get( 'Version' ) )
        );

        wp_enqueue_style(
            'wcs-shop-header-style',
            get_stylesheet_directory_uri() . '/template-parts/header/shop-header.css',
            array( 'wcs-brand-typography' ),
            wcs_asset_version( 'template-parts/header/shop-header.css', $child_theme->get( 'Version' ) )
        );

        wp_enqueue_script(
            'lottie-web',
            'https://cdnjs.cloudflare.com/ajax/libs/bodymovin/5.12.2/lottie.min.js',
            array(),
            '5.12.2',
            true
        );

        wp_enqueue_script(
            'wcs-hero-script',
            get_stylesheet_directory_uri() . '/template-parts/components/hero/hero.js',
            array( 'lottie-web' ),
            wcs_asset_version( 'template-parts/components/hero/hero.js', $child_theme->get( 'Version' ) ),
            true
        );
    }

    if ( function_exists( 'is_product' ) && is_product() ) {
        global $product;

        $price_per_m2 = $product instanceof WC_Product ? (float) wc_get_price_to_display( $product ) : 0;

        wp_enqueue_script(
            'wcs-m2-calculator',
            get_stylesheet_directory_uri() . '/assets/js/m2-calculator.js',
            array(),
            wcs_asset_version( 'assets/js/m2-calculator.js', $child_theme->get( 'Version' ) ),
            true
        );

        wp_localize_script(
            'wcs-m2-calculator',
            'wcsCalculator',
            array(
                'pricePerM2' => $price_per_m2,
                'vatRate'    => 0.20,
                'currency'   => get_woocommerce_currency_symbol(),
            )
        );
    }
}
add_action( 'wp_enqueue_scripts', 'wcs_child_enqueue_assets' );

/**
 * Hide Astra native header on WooCommerce archive pages
 * when custom shop header component is used.
 */
function wcs_hide_astra_header_on_shop() {
    $is_shop_front = function_exists( 'wc_get_page_id' ) && is_front_page() && (int) get_queried_object_id() === (int) wc_get_page_id( 'shop' );

    if ( ! function_exists( 'is_shop' ) || ( ! is_shop() && ! is_product_taxonomy() && ! $is_shop_front ) ) {
        return;
    }
    ?>
    <style id="wcs-hide-astra-header">
        #masthead,
        .site-header,
        .main-header-bar-wrap,
        .ast-mobile-header-wrap {
            display: none !important;
        }
    </style>
    <?php
}
add_action( 'wp_head', 'wcs_hide_astra_header_on_shop', 99 );

/**
 * Force full-width CSS layout on WooCommerce archives.
 */
function wcs_force_shop_fullwidth_css() {
    $is_shop_front = function_exists( 'wc_get_page_id' ) && is_front_page() && (int) get_queried_object_id() === (int) wc_get_page_id( 'shop' );

    if ( ! function_exists( 'is_shop' ) || ( ! is_shop() && ! is_product_taxonomy() && ! $is_shop_front ) ) {
        return;
    }
    ?>
    <style id="wcs-shop-fullwidth-layout">
        .woocommerce-page .site-content .ast-container,
        .woocommerce .site-content .ast-container {
            max-width: 100% !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
            display: block !important;
        }

        .woocommerce-page #primary,
        .woocommerce #primary,
        .woocommerce-page .content-area,
        .woocommerce .content-area {
            width: 100% !important;
            float: none !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        .woocommerce-page #secondary,
        .woocommerce #secondary,
        .woocommerce-page .widget-area,
        .woocommerce .widget-area {
            display: none !important;
        }
    </style>
    <?php
}
add_action( 'wp_head', 'wcs_force_shop_fullwidth_css', 100 );

/**
 * Force full-width no-sidebar layout on WooCommerce archives.
 *
 * @param string $layout Astra layout slug.
 * @return string
 */
function wcs_shop_no_sidebar_layout( $layout ) {
    $is_shop_front = function_exists( 'wc_get_page_id' ) && is_front_page() && (int) get_queried_object_id() === (int) wc_get_page_id( 'shop' );

    if ( function_exists( 'is_shop' ) && ( is_shop() || is_product_taxonomy() || $is_shop_front ) ) {
        return 'no-sidebar';
    }

    return $layout;
}
add_filter( 'astra_page_layout', 'wcs_shop_no_sidebar_layout', 99 );
add_filter( 'astra_woo_shop_sidebar_init', '__return_false' );

/**
 * Render calculator fields for single product pages.
 */
function wcs_render_price_calculator() {
    if ( ! function_exists( 'is_product' ) || ! is_product() ) {
        return;
    }
    ?>
    <section class="wcs-calculator" aria-label="Price calculator">
        <h3><?php esc_html_e( 'm² Price Calculator', 'woocommerce-store-child' ); ?></h3>
        <div class="wcs-calculator__grid">
            <label for="wcs-width"><?php esc_html_e( 'Width (m)', 'woocommerce-store-child' ); ?></label>
            <input id="wcs-width" name="wcs_width" type="number" min="0" step="0.01" inputmode="decimal" />

            <label for="wcs-height"><?php esc_html_e( 'Height (m)', 'woocommerce-store-child' ); ?></label>
            <input id="wcs-height" name="wcs_height" type="number" min="0" step="0.01" inputmode="decimal" />
        </div>

        <div class="wcs-calculator__results" aria-live="polite">
            <p><?php esc_html_e( 'Area:', 'woocommerce-store-child' ); ?> <strong id="wcs-area">0.00 m²</strong></p>
            <p><?php esc_html_e( 'Base Price:', 'woocommerce-store-child' ); ?> <strong id="wcs-price">0.00</strong></p>
            <p><?php esc_html_e( 'VAT (20%):', 'woocommerce-store-child' ); ?> <strong id="wcs-vat">0.00</strong></p>
            <p><?php esc_html_e( 'Total:', 'woocommerce-store-child' ); ?> <strong id="wcs-total">0.00</strong></p>
        </div>
    </section>
    <?php
}
add_action( 'woocommerce_before_add_to_cart_form', 'wcs_render_price_calculator', 15 );

/**
 * Auto-assign dedicated templates for policy pages based on common slugs.
 *
 * @param string $template Current resolved template path.
 * @return string
 */
function wcs_policy_page_templates( $template ) {
    if ( ! is_page() ) {
        return $template;
    }

    $slug = get_post_field( 'post_name', get_queried_object_id() );

    if ( in_array( $slug, array( 'privacy-policy', 'gizlilik-politikasi' ), true ) ) {
        $privacy_template = get_stylesheet_directory() . '/page-templates/privacy-policy.php';
        if ( file_exists( $privacy_template ) ) {
            return $privacy_template;
        }
    }

    if ( in_array( $slug, array( 'refund-policy', 'iade-ve-iptal-politikasi' ), true ) ) {
        $refund_template = get_stylesheet_directory() . '/page-templates/refund-policy.php';
        if ( file_exists( $refund_template ) ) {
            return $refund_template;
        }
    }

    return $template;
}
add_filter( 'template_include', 'wcs_policy_page_templates' );
