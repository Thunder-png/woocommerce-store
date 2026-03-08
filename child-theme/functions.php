<?php
/**
 * Theme functions for WooCommerce Store Child.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Enqueue child theme assets.
 */
function wcs_child_enqueue_assets() {
    $parent_theme = wp_get_theme( get_template() );
    $child_theme  = wp_get_theme();

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
        filemtime( get_stylesheet_directory() . '/assets/css/style.css' )
    );

    if ( function_exists( 'is_shop' ) && ( is_shop() || is_product_taxonomy() ) ) {
        $branding_base_url  = trailingslashit( home_url() ) . 'branding/';
        $branding_base_path = trailingslashit( dirname( get_stylesheet_directory() ) ) . 'branding/';

        wp_enqueue_style(
            'wcs-brand-colors',
            $branding_base_url . 'colors/brand-colors.css',
            array( 'wcs-custom-style' ),
            file_exists( $branding_base_path . 'colors/brand-colors.css' ) ? filemtime( $branding_base_path . 'colors/brand-colors.css' ) : null
        );

        wp_enqueue_style(
            'wcs-brand-typography',
            $branding_base_url . 'typography/typo-kit.css',
            array( 'wcs-brand-colors' ),
            file_exists( $branding_base_path . 'typography/typo-kit.css' ) ? filemtime( $branding_base_path . 'typography/typo-kit.css' ) : null
        );

        wp_enqueue_style(
            'wcs-hero-style',
            get_stylesheet_directory_uri() . '/template-parts/components/hero/hero.css',
            array( 'wcs-brand-typography' ),
            filemtime( get_stylesheet_directory() . '/template-parts/components/hero/hero.css' )
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
            filemtime( get_stylesheet_directory() . '/template-parts/components/hero/hero.js' ),
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
            filemtime( get_stylesheet_directory() . '/assets/js/m2-calculator.js' ),
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
