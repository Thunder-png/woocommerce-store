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

    // Lottie her sayfada yüklenmeli — header/footer logosu için gerekli.
    wp_enqueue_script(
        'lottie-web',
        'https://cdnjs.cloudflare.com/ajax/libs/bodymovin/5.12.2/lottie.min.js',
        array(),
        '5.12.2',
        true
    );

	wp_enqueue_script(
		'wcs-crypto-polyfill',
		get_stylesheet_directory_uri() . '/assets/js/wcs-crypto-polyfill.js',
		array(),
		wcs_asset_version( 'assets/js/wcs-crypto-polyfill.js', $child_theme->get( 'Version' ) ),
		false
	);

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
        'wcs-bootstrap-icons',
        'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css',
        array(),
        '1.11.3'
    );
    $is_shop_context = function_exists( 'is_shop' ) && ( is_shop() || is_product_taxonomy() );

    if ( $is_shop_context ) {
        wp_enqueue_style(
            'wcs-product-card',
            get_stylesheet_directory_uri() . '/assets/css/product-card.css',
            array( 'wcs-custom-style', 'wcs-bootstrap-icons' ),
            wcs_asset_version( 'assets/css/product-card.css', $child_theme->get( 'Version' ) )
        );
    }

    $branding_base_url = trailingslashit( get_stylesheet_directory_uri() ) . 'assets/branding/';

    wp_enqueue_style(
        'wcs-brand-colors',
        $branding_base_url . 'colors/brand-colors.css',
        array( 'wcs-child-style' ),
        wcs_asset_version( 'assets/branding/colors/brand-colors.css', $child_theme->get( 'Version' ) )
    );

    wp_enqueue_style(
        'wcs-brand-typography',
        $branding_base_url . 'typography/typo-kit.css',
        array( 'wcs-brand-colors' ),
        wcs_asset_version( 'assets/branding/typography/typo-kit.css', $child_theme->get( 'Version' ) )
    );

    wp_enqueue_style(
        'wcs-custom-style',
        get_stylesheet_directory_uri() . '/assets/css/style.css',
        array( 'wcs-brand-typography' ),
        wcs_asset_version( 'assets/css/style.css', $child_theme->get( 'Version' ) )
    );

    // Blog styles & scripts (archives, single posts, search).
    if ( is_home() || is_singular( 'post' ) || is_category() || is_tag() || is_search() ) {
        wp_enqueue_style(
            'wcs-blog-style',
            get_stylesheet_directory_uri() . '/assets/css/blog.css',
            array( 'wcs-custom-style' ),
            wcs_asset_version( 'assets/css/blog.css', $child_theme->get( 'Version' ) )
        );

        wp_enqueue_script(
            'wcs-blog-script',
            get_stylesheet_directory_uri() . '/assets/js/blog.js',
            array(),
            wcs_asset_version( 'assets/js/blog.js', $child_theme->get( 'Version' ) ),
            true
        );
    }

    wp_enqueue_style(
        'wcs-shop-header-style',
        get_stylesheet_directory_uri() . '/template-parts/header/shop-header.css',
        array( 'wcs-custom-style' ),
        wcs_asset_version( 'template-parts/header/shop-header.css', $child_theme->get( 'Version' ) )
    );

    // Footer stili — her sayfada.
    wp_enqueue_style(
        'wcs-site-footer',
        get_stylesheet_directory_uri() . '/template-parts/footer/site-footer.css',
        array( 'wcs-custom-style', 'wcs-bootstrap-icons' ),
        wcs_asset_version( 'template-parts/footer/site-footer.css', $child_theme->get( 'Version' ) )
    );

    // Sepet sidebar JS — header sepet ikonundan her sayfada açılabilmesi için global.
    wp_enqueue_script(
        'wcs-cart-sidebar',
        get_stylesheet_directory_uri() . '/assets/js/wcs-cart-sidebar.js',
        array( 'jquery', 'wc-cart-fragments' ),
        wcs_asset_version( 'assets/js/wcs-cart-sidebar.js', $child_theme->get( 'Version' ) ),
        true
    );

    wp_enqueue_script(
        'wcs-toast',
        get_stylesheet_directory_uri() . '/assets/js/wcs-toast.js',
        array( 'jquery' ),
        wcs_asset_version( 'assets/js/wcs-toast.js', $child_theme->get( 'Version' ) ),
        true
    );

    wp_enqueue_script(
        'wcs-live-search',
        get_stylesheet_directory_uri() . '/assets/js/wcs-live-search.js',
        array(),
        wcs_asset_version( 'assets/js/wcs-live-search.js', $child_theme->get( 'Version' ) ),
        true
    );

    wp_localize_script(
        'wcs-live-search',
        'wcsLiveSearch',
        array(
            'ajaxUrl'        => admin_url( 'admin-ajax.php' ),
            'nonce'          => wp_create_nonce( 'wcs-live-search' ),
            'minChars'       => 2,
            'noResultsText'  => __( 'Sonuc bulunamadi', 'woocommerce-store-child' ),
            'searchMoreText' => __( 'Daha fazla sonuc icin Enter', 'woocommerce-store-child' ),
        )
    );

    wp_enqueue_script(
        'wcs-cart-coupon',
        get_stylesheet_directory_uri() . '/assets/js/wcs-cart-coupon.js',
        array( 'jquery', 'wc-cart-fragments' ),
        wcs_asset_version( 'assets/js/wcs-cart-coupon.js', $child_theme->get( 'Version' ) ),
        true
    );

    wp_localize_script(
        'wcs-cart-coupon',
        'wcsCartCoupon',
        array(
            'ajaxUrl'      => function_exists( 'WC_AJAX' ) ? WC_AJAX::get_endpoint( 'apply_coupon' ) : home_url( '/?wc-ajax=apply_coupon' ),
            'nonce'        => wp_create_nonce( 'apply-coupon' ),
            'successText'  => __( 'Kupon uygulandi.', 'woocommerce-store-child' ),
            'errorText'    => __( 'Kupon uygulanamadi.', 'woocommerce-store-child' ),
            'missingText'  => __( 'Lutfen kupon kodu girin.', 'woocommerce-store-child' ),
        )
    );

    if ( is_front_page() ) {
        wp_enqueue_style(
            'wcs-home-category-grid',
            get_stylesheet_directory_uri() . '/assets/css/home-category-grid.css',
            array( 'wcs-custom-style' ),
            wcs_asset_version( 'assets/css/home-category-grid.css', $child_theme->get( 'Version' ) )
        );

        // Front page hero + section styles.
        wp_enqueue_style(
            'wcs-front-page',
            get_stylesheet_directory_uri() . '/assets/css/front-page.css',
            array( 'wcs-custom-style', 'wcs-bootstrap-icons' ),
            wcs_asset_version( 'assets/css/front-page.css', $child_theme->get( 'Version' ) )
        );

        // Hero script + styles for front page.
        wp_enqueue_style(
            'wcs-hero-style',
            get_stylesheet_directory_uri() . '/template-parts/components/hero/hero.css',
            array( 'wcs-custom-style' ),
            wcs_asset_version( 'template-parts/components/hero/hero.css', $child_theme->get( 'Version' ) )
        );

        wp_enqueue_script(
            'wcs-hero-script',
            get_stylesheet_directory_uri() . '/template-parts/components/hero/hero.js',
            array( 'lottie-web' ),
            wcs_asset_version( 'template-parts/components/hero/hero.js', $child_theme->get( 'Version' ) ),
            true
        );
    }

    if ( $is_shop_context ) {
        wp_enqueue_script(
            'wcs-hero-script',
            get_stylesheet_directory_uri() . '/template-parts/components/hero/hero.js',
            array( 'lottie-web' ),
            wcs_asset_version( 'template-parts/components/hero/hero.js', $child_theme->get( 'Version' ) ),
            true
        );

        wp_enqueue_style(
            'wcs-hero-style',
            get_stylesheet_directory_uri() . '/template-parts/components/hero/hero.css',
            array( 'wcs-custom-style' ),
            wcs_asset_version( 'template-parts/components/hero/hero.css', $child_theme->get( 'Version' ) )
        );

        wp_enqueue_script(
            'wcs-filter-ajax',
            get_stylesheet_directory_uri() . '/assets/js/wcs-filter-ajax.js',
            array(),
            wcs_asset_version( 'assets/js/wcs-filter-ajax.js', $child_theme->get( 'Version' ) ),
            true
        );

        wp_enqueue_script(
            'wcs-quick-view',
            get_stylesheet_directory_uri() . '/assets/js/wcs-quick-view.js',
            array(),
            wcs_asset_version( 'assets/js/wcs-quick-view.js', $child_theme->get( 'Version' ) ),
            true
        );

        wp_localize_script(
            'wcs-quick-view',
            'wcsQuickView',
            array(
                'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                'nonce'   => wp_create_nonce( 'wcs-quick-view' ),
            )
        );

        wp_enqueue_script(
            'wcs-wishlist',
            get_stylesheet_directory_uri() . '/assets/js/wcs-wishlist.js',
            array(),
            wcs_asset_version( 'assets/js/wcs-wishlist.js', $child_theme->get( 'Version' ) ),
            true
        );
    }

	if ( function_exists( 'is_product' ) && is_product() ) {
        global $product;
        $current_product = null;

        if ( function_exists( 'wc_get_product' ) ) {
            $current_product = wc_get_product( get_queried_object_id() );
        }

        if ( ! ( $current_product instanceof WC_Product ) && $product instanceof WC_Product ) {
            $current_product = $product;
        }

		// Varyasyon attribute butonları sadece variable ürünlerde gerekli.
		if ( $current_product instanceof WC_Product && $current_product->is_type( 'variable' ) ) {
			wp_enqueue_script(
				'wcs-attribute-buttons',
				get_stylesheet_directory_uri() . '/assets/js/wcs-attribute-buttons.js',
				array( 'jquery', 'wc-add-to-cart-variation' ),
				wcs_asset_version( 'assets/js/wcs-attribute-buttons.js', $child_theme->get( 'Version' ) ),
				true
			);

            wp_enqueue_script(
                'wcs-variation-cards',
                get_stylesheet_directory_uri() . '/assets/js/variation-cards.js',
                array( 'jquery', 'wc-add-to-cart-variation' ),
                wcs_asset_version( 'assets/js/variation-cards.js', $child_theme->get( 'Version' ) ),
                true
            );
		}

		// Adet arttıkça toplam fiyatın güncellenmesi için JS.
		wp_enqueue_script(
			'wcs-qty-total',
			get_stylesheet_directory_uri() . '/assets/js/wcs-qty-total.js',
			array(),
			wcs_asset_version( 'assets/js/wcs-qty-total.js', $child_theme->get( 'Version' ) ),
			true
		);

		if ( function_exists( 'wc_get_price_to_display' ) && $current_product instanceof WC_Product ) {
			$unit_price_for_total = wc_get_price_to_display( $current_product );

			wp_localize_script(
				'wcs-qty-total',
				'wcsQtyTotal',
				array(
					'currency'  => get_woocommerce_currency_symbol(),
					'unitPrice' => $unit_price_for_total,
				)
			);
		}

        wp_enqueue_style(
            'wcs-product-detail',
            get_stylesheet_directory_uri() . '/assets/css/product-detail.css',
            array( 'wcs-custom-style' ),
            wcs_asset_version( 'assets/css/product-detail.css', $child_theme->get( 'Version' ) )
        );

        wp_enqueue_script(
            'wcs-ajax-add-to-cart',
            get_stylesheet_directory_uri() . '/assets/js/wcs-ajax-add-to-cart.js',
            array( 'jquery', 'wc-add-to-cart', 'wc-cart-fragments' ),
            wcs_asset_version( 'assets/js/wcs-ajax-add-to-cart.js', $child_theme->get( 'Version' ) ),
            true
        );

        wp_enqueue_script(
            'wcs-spec-card',
            get_stylesheet_directory_uri() . '/assets/js/wcs-spec-card.js',
            array( 'jquery', 'wc-add-to-cart-variation' ),
            wcs_asset_version( 'assets/js/wcs-spec-card.js', $child_theme->get( 'Version' ) ),
            true
        );

        wp_enqueue_script(
            'wcs-m2-calculator',
            get_stylesheet_directory_uri() . '/assets/js/m2-calculator.js',
            array(),
            wcs_asset_version( 'assets/js/m2-calculator.js', $child_theme->get( 'Version' ) ),
            true
        );

        wp_enqueue_script(
            'wcs-calculator-toggle',
            get_stylesheet_directory_uri() . '/assets/js/wcs-calculator-toggle.js',
            array(),
            wcs_asset_version( 'assets/js/wcs-calculator-toggle.js', $child_theme->get( 'Version' ) ),
            true
        );

        wp_enqueue_script(
            'wcs-sticky-atc',
            get_stylesheet_directory_uri() . '/assets/js/wcs-sticky-atc.js',
            array(),
            wcs_asset_version( 'assets/js/wcs-sticky-atc.js', $child_theme->get( 'Version' ) ),
            true
        );

        wp_enqueue_script(
            'wcs-wishlist',
            get_stylesheet_directory_uri() . '/assets/js/wcs-wishlist.js',
            array(),
            wcs_asset_version( 'assets/js/wcs-wishlist.js', $child_theme->get( 'Version' ) ),
            true
        );

        wp_enqueue_style(
            'glightbox-style',
            'https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css',
            array(),
            '3.3.1'
        );

        wp_enqueue_script(
            'glightbox',
            'https://cdn.jsdelivr.net/npm/glightbox/dist/js/glightbox.min.js',
            array(),
            '3.3.1',
            true
        );

        wp_enqueue_script(
            'wcs-gallery-lightbox',
            get_stylesheet_directory_uri() . '/assets/js/wcs-gallery-lightbox.js',
            array( 'glightbox' ),
            wcs_asset_version( 'assets/js/wcs-gallery-lightbox.js', $child_theme->get( 'Version' ) ),
            true
        );

		wp_localize_script(
			'wcs-ajax-add-to-cart',
			'wcsAjaxAddToCart',
			array(
				'cartUrl' => function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'cart' ) : home_url( '/cart/' ),
			)
		);

        wp_localize_script(
            'wcs-m2-calculator',
            'wcsCalculator',
            array(
                'pricePerM2' => ( function_exists( 'wc_get_price_to_display' ) && $current_product instanceof WC_Product ) ? wc_get_price_to_display( $current_product ) : 0,
                'vatRate'    => 0.20,
                'currency'   => get_woocommerce_currency_symbol(),
            )
        );
    }

    if (
        function_exists( 'is_cart' )
        && function_exists( 'is_checkout' )
        && (
            is_cart()
            || is_checkout()
            || ( function_exists( 'is_account_page' ) && is_account_page() )
        )
    ) {
        wp_enqueue_style(
            'wcs-cart-checkout-account',
            get_stylesheet_directory_uri() . '/assets/css/cart-checkout-account.css',
            array( 'wcs-custom-style' ),
            wcs_asset_version( 'assets/css/cart-checkout-account.css', $child_theme->get( 'Version' ) )
        );
    }

	if ( function_exists( 'is_checkout' ) && is_checkout() && ( ! function_exists( 'is_order_received_page' ) || ! is_order_received_page() ) ) {
		wp_enqueue_script(
			'wcs-checkout-shipping-summary',
			get_stylesheet_directory_uri() . '/assets/js/wcs-checkout-shipping-summary.js',
			array( 'jquery', 'wc-checkout' ),
			wcs_asset_version( 'assets/js/wcs-checkout-shipping-summary.js', $child_theme->get( 'Version' ) ),
			true
		);

		wp_localize_script(
			'wcs-checkout-shipping-summary',
			'wcsCheckoutShippingSummary',
			array(
				'pendingText' => __( 'Adres girildikten sonra hesaplanır', 'woocommerce-store-child' ),
			)
		);

        wp_enqueue_script(
            'wcs-checkout-validation',
            get_stylesheet_directory_uri() . '/assets/js/wcs-checkout-validation.js',
            array( 'jquery', 'wc-checkout' ),
            wcs_asset_version( 'assets/js/wcs-checkout-validation.js', $child_theme->get( 'Version' ) ),
            true
        );
	}
}
add_action( 'wp_enqueue_scripts', 'wcs_child_enqueue_assets' );

/**
 * Add font preconnect hints for external font hosts.
 */
function wcs_add_font_preconnect_hints() {
    echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
    echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
}
add_action( 'wp_head', 'wcs_add_font_preconnect_hints', 5 );

// Blog helpers and schema.
require_once get_stylesheet_directory() . '/inc/blog-functions.php';
require_once get_stylesheet_directory() . '/inc/blog-shortcodes.php';
require_once get_stylesheet_directory() . '/inc/blog-schema.php';


/**
 * Hide Astra native header globally so custom header is used site-wide.
 */
function wcs_hide_astra_header_globally() {
    if ( is_admin() ) {
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
add_action( 'wp_head', 'wcs_hide_astra_header_globally', 99 );

/**
 * Render custom site header globally.
 */
function wcs_render_site_header() {
    if ( is_admin() ) {
        return;
    }

    get_template_part( 'template-parts/header/shop-header' );
}
add_action( 'wp_body_open', 'wcs_render_site_header', 20 );

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
 * Ana sayfa = Shop olduğunda Astra'nın woocommerce_before/after_main_content
 * hook'larına bağladığı ast-woocommerce-container wrapper'ını kaldır.
 * Bu wrapper ana sayfa özel tasarımının dışına çıkıp gereksiz sarmalıyor.
 */
function wcs_remove_astra_woo_wrapper_on_front() {
    if ( ! ( function_exists( 'is_front_page' ) && function_exists( 'is_shop' ) && is_front_page() && is_shop() ) ) {
        return;
    }
    // Astra bu hook'lara yüklediği fonksiyonları farklı sınıf/öncelik ile bağlıyor.
    // remove_action ile tüm olası bağlantıları temizle.
    remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
    remove_action( 'woocommerce_after_main_content',  'woocommerce_output_content_wrapper_end', 10 );

    // Astra kendi wrapper sınıfını genellikle bu isimdeki fonksiyonla ekler:
    if ( class_exists( 'Astra_Woocommerce' ) ) {
        remove_action( 'woocommerce_before_main_content', array( 'Astra_Woocommerce', 'woocommerce_breadcrumb' ), 10 );
        remove_action( 'woocommerce_before_main_content', array( 'Astra_Woocommerce', 'content_wrapper_start' ), 10 );
        remove_action( 'woocommerce_after_main_content',  array( 'Astra_Woocommerce', 'content_wrapper_end' ), 10 );
    }

    // Astra'nın global helper sınıfı
    if ( class_exists( 'Astra_Woo_Template_Loader' ) ) {
        remove_action( 'woocommerce_before_main_content', array( 'Astra_Woo_Template_Loader', 'content_wrapper_start' ), 10 );
        remove_action( 'woocommerce_after_main_content',  array( 'Astra_Woo_Template_Loader', 'content_wrapper_end' ), 10 );
    }

    // WooCommerce breadcrumb'ı da ana sayfada gizle (hero zaten var).
    remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );
}
add_action( 'wp', 'wcs_remove_astra_woo_wrapper_on_front', 5 );

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

    // My Account sayfasında da sidebar kapat — kendi nav'ımız var.
    if ( function_exists( 'is_account_page' ) && is_account_page() ) {
        return 'no-sidebar';
    }

    return $layout;
}
add_filter( 'astra_page_layout', 'wcs_shop_no_sidebar_layout', 99 );
add_filter( 'astra_woo_shop_sidebar_init', '__return_false' );

// My Account sayfasında Astra content wrapper genişliğini full yap.
add_filter( 'astra_content_layout_type', function( $layout ) {
    if ( function_exists( 'is_account_page' ) && is_account_page() ) {
        return 'fluid';
    }
    return $layout;
}, 99 );

/**
 * Render site footer — new template-part.
 */
function wcs_render_brand_footer() {
    if ( is_admin() ) {
        return;
    }
    get_template_part( 'template-parts/footer/site-footer' );
}
add_action( 'wp_footer', 'wcs_render_brand_footer', 20 );

/**
 * Render slide-in cart sidebar — her sayfada.
 */
function wcs_render_cart_sidebar() {
    if ( is_admin() ) { return; }
    $count        = function_exists( 'WC' ) && WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
    $is_cart_empty = function_exists( 'WC' ) && WC()->cart ? WC()->cart->is_empty() : true;
    ?>
    <div class="wcs-cart-sidebar-overlay" aria-hidden="true"></div>
    <aside class="wcs-cart-sidebar" aria-label="<?php esc_attr_e( 'Sepetiniz', 'woocommerce-store-child' ); ?>" aria-hidden="true" role="dialog">
        <header class="wcs-cart-sidebar__header">
            <div class="wcs-cart-sidebar__header-left">
                <span class="wcs-cart-sidebar__header-icon"><i class="bi bi-cart3"></i></span>
                <h2 class="wcs-cart-sidebar__title"><?php esc_html_e( 'Sepetiniz', 'woocommerce-store-child' ); ?></h2>
                <span class="wcs-cart-sidebar__count" data-wcs-cart-count><?php echo absint( $count ); ?></span>
            </div>
            <button type="button" class="wcs-cart-sidebar__close" aria-label="<?php esc_attr_e( 'Kapat', 'woocommerce-store-child' ); ?>">
                <i class="bi bi-x-lg"></i>
            </button>
        </header>
        <div class="wcs-cart-sidebar__trust">
            <span><i class="bi bi-shield-fill-check"></i> <?php esc_html_e( 'Güvenli Ödeme', 'woocommerce-store-child' ); ?></span>
            <span><i class="bi bi-truck"></i> <?php esc_html_e( 'Ücretsiz Kargo', 'woocommerce-store-child' ); ?></span>
            <span><i class="bi bi-arrow-return-left"></i> <?php esc_html_e( '30 Gün İade', 'woocommerce-store-child' ); ?></span>
        </div>
        <div class="wcs-cart-sidebar__body">
            <?php if ( $is_cart_empty ) : ?>
                <div class="wcs-cart-sidebar__empty">
                    <span class="wcs-cart-sidebar__empty-icon"><i class="bi bi-cart-x"></i></span>
                    <h3 class="wcs-cart-sidebar__empty-title"><?php esc_html_e( 'Sepetiniz su an bos', 'woocommerce-store-child' ); ?></h3>
                    <p class="wcs-cart-sidebar__empty-desc"><?php esc_html_e( 'Guvenlik filesi urunlerini kesfederek alisverise baslayin.', 'woocommerce-store-child' ); ?></p>
                    <a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="wcs-cart-sidebar__btn wcs-cart-sidebar__btn--primary">
                        <i class="bi bi-grid"></i> <?php esc_html_e( 'Alisverise Basla', 'woocommerce-store-child' ); ?>
                    </a>
                </div>
            <?php else : ?>
                <?php woocommerce_mini_cart(); ?>
            <?php endif; ?>
        </div>
        <footer class="wcs-cart-sidebar__footer">
            <form class="wcs-cart-sidebar__coupon" data-wcs-cart-coupon-form>
                <label for="wcs-cart-coupon-input" class="screen-reader-text"><?php esc_html_e( 'Kupon kodu', 'woocommerce-store-child' ); ?></label>
                <input type="text" id="wcs-cart-coupon-input" class="wcs-cart-sidebar__coupon-input" placeholder="<?php esc_attr_e( 'Kupon kodu', 'woocommerce-store-child' ); ?>" data-wcs-cart-coupon-input>
                <button type="submit" class="wcs-cart-sidebar__coupon-btn" data-wcs-cart-coupon-submit>
                    <?php esc_html_e( 'Uygula', 'woocommerce-store-child' ); ?>
                </button>
            </form>
            <div class="wcs-cart-sidebar__subtotal">
                <span class="wcs-cart-sidebar__subtotal-label"><?php esc_html_e( 'Ara Toplam', 'woocommerce-store-child' ); ?></span>
                <span class="wcs-cart-sidebar__subtotal-value"><?php if ( function_exists( 'WC' ) && WC()->cart ) echo wp_kses_post( WC()->cart->get_cart_subtotal() ); ?></span>
            </div>
            <div class="wcs-cart-sidebar__actions">
                <a href="<?php echo esc_url( wc_get_cart_url() ); ?>" class="wcs-cart-sidebar__btn wcs-cart-sidebar__btn--secondary">
                    <i class="bi bi-cart3"></i> <?php esc_html_e( 'Sepete Git', 'woocommerce-store-child' ); ?>
                </a>
                <a href="<?php echo esc_url( wc_get_checkout_url() ); ?>" class="wcs-cart-sidebar__btn wcs-cart-sidebar__btn--primary">
                    <i class="bi bi-lock-fill"></i> <?php esc_html_e( 'Ödemeye Geç', 'woocommerce-store-child' ); ?>
                </a>
            </div>
            <div class="wcs-cart-sidebar__payment-icons">
                <?php foreach ( array( 'Visa', 'Mastercard', 'Havale', 'Kapıda' ) as $m ) : ?>
                    <span class="wcs-cart-sidebar__payment-pill"><?php echo esc_html( $m ); ?></span>
                <?php endforeach; ?>
            </div>
        </footer>
    </aside>
    <?php
}
add_action( 'wp_footer', 'wcs_render_cart_sidebar', 25 );


/**
 * Central configuration for filterable WooCommerce attributes.
 *
 * @return array<string,array<string,mixed>>
 */
function wcs_get_filterable_attribute_definitions() {
    return array(
        'kullanim-amaci' => array(
            'label'   => __( 'Kullanım Amacı', 'woocommerce-store-child' ),
            'options' => array(
                __( 'Çocuk güvenlik', 'woocommerce-store-child' ),
                __( 'İnşaat', 'woocommerce-store-child' ),
                __( 'Kedi güvenlik', 'woocommerce-store-child' ),
                __( 'Balkon', 'woocommerce-store-child' ),
                __( 'Kuş Filesi', 'woocommerce-store-child' ),
                __( 'Havuz güvenlik', 'woocommerce-store-child' ),
            ),
        ),
        'mukavemet' => array(
            'label'   => __( 'Mukavemet', 'woocommerce-store-child' ),
            'options' => array(
                '400 lbs',
                '200 lbs',
            ),
        ),
        'en-boy-orani' => array(
            'label'   => __( 'En/Boy Oranı', 'woocommerce-store-child' ),
            'options' => array(
                '1.5x2',
                '2.5x10',
            ),
        ),
        'ip-kalinligi' => array(
            'label'   => __( 'İp Kalınlığı', 'woocommerce-store-child' ),
            'options' => array(
                '4mm',
                '6mm',
                '8mm',
            ),
        ),
        'goz-araligi' => array(
            'label'   => __( 'Göz Aralığı', 'woocommerce-store-child' ),
            'options' => array(
                '5x5',
                '8x8',
            ),
        ),
        'renk' => array(
            'label'   => __( 'Renk', 'woocommerce-store-child' ),
            'options' => array(
                __( 'Beyaz', 'woocommerce-store-child' ),
                __( 'Siyah', 'woocommerce-store-child' ),
                __( 'Sarı', 'woocommerce-store-child' ),
                __( 'Mavi', 'woocommerce-store-child' ),
            ),
        ),
    );
}

/**
 * Create filter attributes and their default terms for WooCommerce.
 */
function wcs_register_filterable_attributes() {
    if ( ! function_exists( 'wc_get_attribute_taxonomies' ) || ! function_exists( 'wc_create_attribute' ) ) {
        return;
    }

    $definitions         = wcs_get_filterable_attribute_definitions();
    $existing_attributes = wc_get_attribute_taxonomies();
    $existing_by_slug    = array();

    if ( ! empty( $existing_attributes ) ) {
        foreach ( $existing_attributes as $attribute ) {
            $existing_by_slug[ $attribute->attribute_name ] = true;
        }
    }

    foreach ( $definitions as $slug => $config ) {
        if ( ! isset( $existing_by_slug[ $slug ] ) ) {
            wc_create_attribute(
                array(
                    'name'         => $config['label'],
                    'slug'         => $slug,
                    'type'         => 'select',
                    'order_by'     => 'menu_order',
                    'has_archives' => false,
                )
            );
        }

        $taxonomy = 'pa_' . $slug;

        if ( ! taxonomy_exists( $taxonomy ) ) {
            continue;
        }

        foreach ( $config['options'] as $term_name ) {
            if ( ! term_exists( $term_name, $taxonomy ) ) {
                wp_insert_term( $term_name, $taxonomy );
            }
        }
    }
}
add_action( 'init', 'wcs_register_filterable_attributes', 20 );

/**
 * Render shop filter bar — pill/chip bazlı modern filtre.
 * Her attribute için aktif seçim sticky bar'da rozet olarak gösterilir.
 */
function wcs_render_shop_attribute_filters() {
    if ( ! function_exists( 'is_shop' ) || ( ! is_shop() && ! is_product_taxonomy() ) ) {
        return;
    }

    $definitions = wcs_get_filterable_attribute_definitions();

    // Aktif filtre sayısını hesapla
    $active_count = 0;
    foreach ( $definitions as $slug => $config ) {
        $key = 'filter_pa_' . $slug;
        if ( ! empty( $_GET[ $key ] ) ) $active_count++;
    }

    $shop_url = function_exists( 'is_product_taxonomy' ) && is_product_taxonomy()
        ? get_term_link( get_queried_object() )
        : wc_get_page_permalink( 'shop' );
    if ( is_wp_error( $shop_url ) ) $shop_url = wc_get_page_permalink( 'shop' );
    ?>
    <div class="wcs-filter-bar" id="wcs-filter-bar">
        <div class="wcs-filter-bar__inner">

            <!-- Sol: Filtre etiketi + aktif sayacı -->
            <div class="wcs-filter-bar__left">
                <button type="button" class="wcs-filter-bar__toggle" id="wcs-filter-toggle" aria-expanded="false" aria-controls="wcs-filter-panel">
                    <i class="bi bi-sliders2" aria-hidden="true"></i>
                    <?php esc_html_e( 'Filtrele', 'woocommerce-store-child' ); ?>
                    <?php if ( $active_count > 0 ) : ?>
                        <span class="wcs-filter-bar__active-count"><?php echo absint( $active_count ); ?></span>
                    <?php endif; ?>
                    <i class="bi bi-chevron-down wcs-filter-bar__chevron" aria-hidden="true"></i>
                </button>

                <!-- Aktif filtre rozet'leri -->
                <?php foreach ( $definitions as $slug => $config ) :
                    $key = 'filter_pa_' . $slug;
                    if ( empty( $_GET[ $key ] ) ) continue;
                    $val = sanitize_title( wp_unslash( $_GET[ $key ] ) );
                    $term = get_term_by( 'slug', $val, 'pa_' . $slug );
                    $label = $term ? $term->name : $val;
                    // URL temizle
                    $remove_url = remove_query_arg( $key, add_query_arg( array() ) );
                ?>
                    <span class="wcs-filter-bar__active-pill">
                        <span class="wcs-filter-bar__active-pill-label"><?php echo esc_html( $config['label'] ); ?>:</span>
                        <strong><?php echo esc_html( $label ); ?></strong>
                        <a href="<?php echo esc_url( $remove_url ); ?>" class="wcs-filter-bar__active-pill-remove" aria-label="<?php esc_attr_e( 'Filtreyi kaldır', 'woocommerce-store-child' ); ?>">
                            <i class="bi bi-x" aria-hidden="true"></i>
                        </a>
                    </span>
                <?php endforeach; ?>

                <?php if ( $active_count > 0 ) : ?>
                    <a href="<?php echo esc_url( $shop_url ); ?>" class="wcs-filter-bar__clear-all">
                        <i class="bi bi-x-circle"></i>
                        <?php esc_html_e( 'Temizle', 'woocommerce-store-child' ); ?>
                    </a>
                <?php endif; ?>
            </div>

            <!-- Sağ: Ürün sayısı + sıralama -->
            <div class="wcs-filter-bar__right">
                <span class="wcs-filter-bar__count" id="wcs-result-count">
                    <?php
                    global $wp_query;
                    $total = $wp_query ? $wp_query->found_posts : 0;
                    printf( esc_html__( '%d ürün', 'woocommerce-store-child' ), absint( $total ) );
                    ?>
                </span>
                <?php woocommerce_catalog_ordering(); ?>
            </div>

        </div><!-- /.wcs-filter-bar__inner -->

        <!-- Açılır filtre paneli -->
        <div class="wcs-filter-panel" id="wcs-filter-panel" hidden>
            <form class="wcs-filter-panel__form" method="get" action="<?php echo esc_url( $shop_url ); ?>">

                <?php foreach ( $definitions as $slug => $config ) :
                    $taxonomy  = 'pa_' . $slug;
                    $query_key = 'filter_' . $taxonomy;
                    if ( ! taxonomy_exists( $taxonomy ) ) continue;
                    $terms = get_terms( array( 'taxonomy' => $taxonomy, 'hide_empty' => true ) );
                    if ( is_wp_error( $terms ) || empty( $terms ) ) continue;
                    $selected = isset( $_GET[ $query_key ] ) ? sanitize_title( wp_unslash( $_GET[ $query_key ] ) ) : '';
                ?>
                    <div class="wcs-filter-panel__group">
                        <h3 class="wcs-filter-panel__group-title"><?php echo esc_html( $config['label'] ); ?></h3>
                        <div class="wcs-filter-panel__options">
                            <label class="wcs-filter-option<?php echo '' === $selected ? ' wcs-filter-option--active' : ''; ?>">
                                <input type="radio" name="<?php echo esc_attr( $query_key ); ?>" value=""
                                    <?php checked( $selected, '' ); ?> hidden>
                                <?php esc_html_e( 'Tümü', 'woocommerce-store-child' ); ?>
                            </label>
                            <?php foreach ( $terms as $term ) : ?>
                                <label class="wcs-filter-option<?php echo $selected === $term->slug ? ' wcs-filter-option--active' : ''; ?>">
                                    <input type="radio" name="<?php echo esc_attr( $query_key ); ?>"
                                        value="<?php echo esc_attr( $term->slug ); ?>"
                                        <?php checked( $selected, $term->slug ); ?> hidden>
                                    <?php echo esc_html( $term->name ); ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>

                <!-- Gizli hidden alanlar — mevcut query params koru -->
                <?php foreach ( $_GET as $key => $value ) :
                    if ( ! is_string( $key ) ) continue;
                    if ( 0 === strpos( $key, 'filter_pa_' ) || 0 === strpos( $key, 'query_type_pa_' ) || 'paged' === $key ) continue;
                ?>
                    <input type="hidden" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( wp_unslash( $value ) ); ?>">
                <?php endforeach; ?>

                <div class="wcs-filter-panel__footer">
                    <button type="submit" class="wcs-filter-panel__apply">
                        <i class="bi bi-check2" aria-hidden="true"></i>
                        <?php esc_html_e( 'Filtrele', 'woocommerce-store-child' ); ?>
                    </button>
                    <a href="<?php echo esc_url( $shop_url ); ?>" class="wcs-filter-panel__reset">
                        <?php esc_html_e( 'Sıfırla', 'woocommerce-store-child' ); ?>
                    </a>
                </div>

            </form>
        </div><!-- /.wcs-filter-panel -->

    </div><!-- /.wcs-filter-bar -->

    <script>
    (function(){
        var toggle = document.getElementById('wcs-filter-toggle');
        var panel  = document.getElementById('wcs-filter-panel');
        var bar    = document.getElementById('wcs-filter-bar');
        if (!toggle || !panel) return;

        function openPanel() {
            panel.removeAttribute('hidden');
            panel.style.display = 'block';
            toggle.setAttribute('aria-expanded', 'true');
            bar.classList.add('wcs-filter-bar--open');
        }

        function closePanel() {
            panel.setAttribute('hidden', '');
            panel.style.display = '';
            toggle.setAttribute('aria-expanded', 'false');
            bar.classList.remove('wcs-filter-bar--open');
        }

        function isOpen() {
            return bar.classList.contains('wcs-filter-bar--open');
        }

        toggle.addEventListener('click', function(e){
            e.stopPropagation();
            isOpen() ? closePanel() : openPanel();
        });

        // Radio pill seçimi → aktif class güncelle
        panel.querySelectorAll('input[type="radio"]').forEach(function(radio){
            radio.addEventListener('change', function(){
                var group = radio.closest('.wcs-filter-panel__options');
                if (group) {
                    group.querySelectorAll('.wcs-filter-option').forEach(function(lbl){ lbl.classList.remove('wcs-filter-option--active'); });
                    radio.closest('.wcs-filter-option').classList.add('wcs-filter-option--active');
                }
            });
        });

        // ESC ile kapat
        document.addEventListener('keydown', function(e){
            if (e.key === 'Escape' && isOpen()) { closePanel(); toggle.focus(); }
        });

        // Panel dışına tıkla kapat
        document.addEventListener('click', function(e){
            if (isOpen() && !bar.contains(e.target)) { closePanel(); }
        });

        // Panel kendi içindeki tıklamaları engelleme
        panel.addEventListener('click', function(e){ e.stopPropagation(); });
    })();
    </script>
    <?php
}
add_action( 'woocommerce_before_shop_loop', 'wcs_render_shop_attribute_filters', 15 );

/**
 * AJAX live search for header product search box.
 */
function wcs_ajax_live_search() {
    check_ajax_referer( 'wcs-live-search', 'nonce' );

    $term = isset( $_GET['term'] ) ? sanitize_text_field( wp_unslash( $_GET['term'] ) ) : '';
    $term_length = function_exists( 'mb_strlen' ) ? mb_strlen( $term ) : strlen( $term );
    if ( $term_length < 2 ) {
        wp_send_json_success(
            array(
                'items' => array(),
            )
        );
    }

    $query = new WC_Product_Query(
        array(
            'status' => 'publish',
            'limit'  => 8,
            'return' => 'objects',
            'search' => '*' . $term . '*',
            'orderby' => 'date',
            'order'   => 'DESC',
        )
    );

    $products = $query->get_products();
    $items    = array();

    foreach ( $products as $product ) {
        if ( ! $product instanceof WC_Product ) {
            continue;
        }

        $items[] = array(
            'id'       => $product->get_id(),
            'name'     => $product->get_name(),
            'price'    => wp_strip_all_tags( $product->get_price_html() ),
            'url'      => $product->get_permalink(),
            'image'    => wp_get_attachment_image_url( $product->get_image_id(), 'thumbnail' ),
            'in_stock' => $product->is_in_stock(),
        );
    }

    wp_send_json_success(
        array(
            'items' => $items,
        )
    );
}
add_action( 'wp_ajax_wcs_live_search', 'wcs_ajax_live_search' );
add_action( 'wp_ajax_nopriv_wcs_live_search', 'wcs_ajax_live_search' );

/**
 * AJAX quick view modal content.
 */
function wcs_ajax_quick_view() {
    check_ajax_referer( 'wcs-quick-view', 'nonce' );

    $product_id = isset( $_GET['product_id'] ) ? absint( $_GET['product_id'] ) : 0;
    if ( ! $product_id ) {
        wp_send_json_error( array( 'message' => __( 'Gecersiz urun.', 'woocommerce-store-child' ) ), 400 );
    }

    $product = wc_get_product( $product_id );
    if ( ! $product instanceof WC_Product ) {
        wp_send_json_error( array( 'message' => __( 'Urun bulunamadi.', 'woocommerce-store-child' ) ), 404 );
    }

    ob_start();
    ?>
    <article class="wcs-qv-card">
        <div class="wcs-qv-card__media">
            <a href="<?php echo esc_url( get_permalink( $product_id ) ); ?>">
                <?php echo $product->get_image( 'woocommerce_single' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            </a>
        </div>
        <div class="wcs-qv-card__content">
            <h3 class="wcs-qv-card__title">
                <a href="<?php echo esc_url( get_permalink( $product_id ) ); ?>"><?php echo esc_html( $product->get_name() ); ?></a>
            </h3>
            <div class="wcs-qv-card__price"><?php echo wp_kses_post( $product->get_price_html() ); ?></div>
            <?php if ( $product->get_short_description() ) : ?>
                <div class="wcs-qv-card__excerpt"><?php echo wp_kses_post( wp_trim_words( $product->get_short_description(), 28 ) ); ?></div>
            <?php endif; ?>
            <div class="wcs-qv-card__actions">
                <a class="wcs-card__cta" href="<?php echo esc_url( get_permalink( $product_id ) ); ?>">
                    <?php esc_html_e( 'Urune Git', 'woocommerce-store-child' ); ?>
                    <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    </article>
    <?php
    wp_send_json_success( array( 'html' => ob_get_clean() ) );
}
add_action( 'wp_ajax_wcs_quick_view', 'wcs_ajax_quick_view' );
add_action( 'wp_ajax_nopriv_wcs_quick_view', 'wcs_ajax_quick_view' );

/**
 * Render home category grid below hero on the front page.
 */
function wcs_render_home_category_grid() {
	if ( ! is_front_page() ) {
		return;
	}

	get_template_part( 'template-parts/components/home/home-category-grid' );
}
add_action( 'astra_primary_content_top', 'wcs_render_home_category_grid', 15 );

/**
 * Kart tabanlı seçicide geçerli variation_id varsa attribute uyuşmazlığı uyarısını yumuşat.
 */
add_filter(
	'woocommerce_add_to_cart_validation',
	function ( $passed, $product_id, $quantity, $variation_id = 0, $variations = array() ) {
		if ( is_product() && $variation_id > 0 ) {
			return true;
		}

		return $passed;
	},
	20,
	5
);

/**
 * Warranty setup keys.
 */
function wcs_get_warranty_start_meta_key() {
	return '_wcs_warranty_started_at';
}

/**
 * Save warranty start date when a new user registers.
 *
 * @param int $user_id Newly created user ID.
 */
function wcs_activate_warranty_on_registration( $user_id ) {
	if ( ! $user_id ) {
		return;
	}

	update_user_meta( $user_id, wcs_get_warranty_start_meta_key(), (string) current_time( 'timestamp' ) );
}
add_action( 'user_register', 'wcs_activate_warranty_on_registration', 20 );

/**
 * Get stored warranty start timestamp for a user.
 *
 * @param int $user_id User ID.
 * @return int
 */
function wcs_get_warranty_start_timestamp( $user_id ) {
	$started_at = (int) get_user_meta( $user_id, wcs_get_warranty_start_meta_key(), true );

	return $started_at > 0 ? $started_at : 0;
}

/**
 * Build warranty details array for account and activation pages.
 *
 * @param int $user_id User ID.
 * @return array<string,string|int>
 */
function wcs_get_warranty_details( $user_id ) {
	$started_at = wcs_get_warranty_start_timestamp( $user_id );

	if ( ! $started_at ) {
		return array();
	}

	$wp_timezone = wp_timezone();
	$start_date  = new DateTimeImmutable( '@' . $started_at );
	$start_date  = $start_date->setTimezone( $wp_timezone );

	$product_ends_at = $start_date->modify( '+5 years' );
	$install_ends_at = $start_date->modify( '+2 years' );

	return array(
		'started_at'        => $started_at,
		'start_date'        => wp_date( 'd.m.Y', $started_at ),
		'product_expires'   => $product_ends_at->format( 'd.m.Y' ),
		'installation_expires' => $install_ends_at->format( 'd.m.Y' ),
	);
}

/**
 * Return warranty page url used in dashboard call-to-action.
 *
 * @return string
 */
function wcs_get_warranty_page_url() {
	$page = get_page_by_path( 'garanti-aktivasyon' );

	if ( ! ( $page instanceof WP_Post ) ) {
		$page = get_page_by_path( 'garanti-aktivasyonu' );
	}

	if ( $page instanceof WP_Post ) {
		return get_permalink( $page );
	}

	return home_url( '/garanti-aktivasyon/' );
}

/**
 * Render warranty activation content via shortcode.
 *
 * @return string
 */
function wcs_render_warranty_activation_shortcode() {
	$details = array();

	if ( is_user_logged_in() ) {
		$user_id    = get_current_user_id();
		$started_at = wcs_get_warranty_start_timestamp( $user_id );

		if ( ! $started_at ) {
			wcs_activate_warranty_on_registration( $user_id );
		}

		$details = wcs_get_warranty_details( $user_id );
	}

	ob_start();
	?>
	<section class="wcs-warranty-page" aria-label="Garanti aktivasyonu">
		<header class="wcs-warranty-page__header">
			<p class="wcs-warranty-page__eyebrow"><?php esc_html_e( 'By Karaca File', 'woocommerce-store-child' ); ?></p>
			<h1><?php esc_html_e( 'Garanti Belgesi ve Kurulum Dokümantasyonu', 'woocommerce-store-child' ); ?></h1>
			<p class="wcs-warranty-page__lead">
				<?php esc_html_e( 'QR kod ile bu sayfaya ulaştınız. Garanti başlangıcı üyelik/giriş anında otomatik olarak yapılır.', 'woocommerce-store-child' ); ?>
			</p>
		</header>

		<p class="wcs-warranty-page__section-label"><?php esc_html_e( 'Garanti Aktivasyonu', 'woocommerce-store-child' ); ?></p>

		<div class="wcs-warranty-page__accordion">
			<details open>
				<summary>
					<span class="wcs-warranty-page__icon">📄</span>
					<?php esc_html_e( 'Garanti Belgesi', 'woocommerce-store-child' ); ?>
					<span class="wcs-warranty-page__chevron" aria-hidden="true">▾</span>
				</summary>
				<div class="wcs-warranty-page__panel">
					<ul>
						<li><?php esc_html_e( 'Ürün Garantisi: 5 yıl', 'woocommerce-store-child' ); ?></li>
						<li><?php esc_html_e( 'Montaj Garantisi: 2 yıl', 'woocommerce-store-child' ); ?></li>
						<li><?php esc_html_e( 'Başlangıç Tarihi: Üyelik oluşturma / giriş sonrası aktivasyon tarihi', 'woocommerce-store-child' ); ?></li>
					</ul>
					<p class="wcs-warranty-page__note">
						<?php esc_html_e( 'Garanti bilgilerinizi My Account panelinden her zaman takip edebilirsiniz.', 'woocommerce-store-child' ); ?>
					</p>
				</div>
			</details>

			<details>
				<summary>
					<span class="wcs-warranty-page__icon">🛠️</span>
					<?php esc_html_e( 'Kurulum ve Kullanım Notları', 'woocommerce-store-child' ); ?>
					<span class="wcs-warranty-page__chevron" aria-hidden="true">▾</span>
				</summary>
				<div class="wcs-warranty-page__panel">
					<ol>
						<li><?php esc_html_e( 'Kurulumda bağlantı noktalarının sağlamlığını kontrol edin.', 'woocommerce-store-child' ); ?></li>
						<li><?php esc_html_e( 'Her 6 ayda bir görsel kontrol ve bakım yapın.', 'woocommerce-store-child' ); ?></li>
						<li><?php esc_html_e( 'Ürünü amacı dışında ağır yük taşıma için kullanmayın.', 'woocommerce-store-child' ); ?></li>
					</ol>
				</div>
			</details>
		</div>

		<p class="wcs-warranty-page__section-label"><?php esc_html_e( 'Hesap Durumu', 'woocommerce-store-child' ); ?></p>

		<?php if ( ! empty( $details ) ) : ?>
			<div class="wcs-warranty-page__status is-active">
				<p><strong><?php esc_html_e( 'Garantiniz aktif.', 'woocommerce-store-child' ); ?></strong></p>
				<p><?php echo esc_html( sprintf( __( 'Başlangıç: %s', 'woocommerce-store-child' ), $details['start_date'] ) ); ?></p>
				<p><?php echo esc_html( sprintf( __( 'Ürün garantisi bitiş: %s', 'woocommerce-store-child' ), $details['product_expires'] ) ); ?></p>
				<p><?php echo esc_html( sprintf( __( 'Montaj garantisi bitiş: %s', 'woocommerce-store-child' ), $details['installation_expires'] ) ); ?></p>
				<p>
					<a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>">
						<?php esc_html_e( 'My Account sayfasında görüntüle', 'woocommerce-store-child' ); ?>
					</a>
				</p>
			</div>
		<?php else : ?>
			<div class="wcs-warranty-page__status">
				<p><strong><?php esc_html_e( 'Garanti başlangıcı için üye olun veya giriş yapın.', 'woocommerce-store-child' ); ?></strong></p>
				<?php echo do_shortcode( '[woocommerce_my_account]' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
		<?php endif; ?>
	</section>
	<?php

	return (string) ob_get_clean();
}
add_shortcode( 'wcs_warranty_activation', 'wcs_render_warranty_activation_shortcode' );



/**
 * Add rewrite support for warranty activation page slug.
 */
function wcs_register_warranty_activation_rewrite() {
	add_rewrite_rule( '^garanti-aktivasyon/?$', 'index.php?wcs_warranty_activation=1', 'top' );
	add_rewrite_rule( '^garanti-aktivasyonu/?$', 'index.php?wcs_warranty_activation=1', 'top' );
}
add_action( 'init', 'wcs_register_warranty_activation_rewrite', 20 );

/**
 * Register query var for warranty activation virtual page.
 *
 * @param array<int,string> $vars Existing query vars.
 * @return array<int,string>
 */
function wcs_register_warranty_activation_query_var( $vars ) {
	$vars[] = 'wcs_warranty_activation';

	return $vars;
}
add_filter( 'query_vars', 'wcs_register_warranty_activation_query_var' );

/**
 * Render warranty activation template even if no page exists in admin.
 */
function wcs_render_virtual_warranty_activation_page() {
	if ( ! get_query_var( 'wcs_warranty_activation' ) ) {
		return;
	}

	global $wp_query;
	$wp_query->is_404 = false;
	status_header( 200 );
	nocache_headers();

	$template_path = trailingslashit( get_stylesheet_directory() ) . 'page-templates/warranty-activation.php';

	if ( file_exists( $template_path ) ) {
		include $template_path;
		exit;
	}
}
add_action( 'template_redirect', 'wcs_render_virtual_warranty_activation_page', 1 );

/**
 * Flush rewrite rules when child theme is switched.
 */
function wcs_flush_warranty_rewrite_rules() {
	wcs_register_warranty_activation_rewrite();
	flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'wcs_flush_warranty_rewrite_rules' );

/**
 * WooCommerce My Account kayıt akışı: ad soyad alanı, doğrulama ve profil kaydı.
 */
function wcs_register_form_name_field() {
	$full_name = isset( $_POST['account_first_name'] ) ? wp_unslash( (string) $_POST['account_first_name'] ) : '';
	?>
	<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
		<label for="reg_account_first_name">
			<?php esc_html_e( 'Ad Soyad', 'woocommerce-store-child' ); ?>
			<span class="required">*</span>
		</label>
		<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="account_first_name" id="reg_account_first_name" autocomplete="name" value="<?php echo esc_attr( $full_name ); ?>" />
	</p>
	<?php
}
add_action( 'woocommerce_register_form_start', 'wcs_register_form_name_field' );

/**
 * Validate custom name field on registration.
 *
 * @param WP_Error $errors   Validation errors.
 * @param string   $username Submitted username.
 * @param string   $email    Submitted email.
 * @return WP_Error
 */
function wcs_validate_register_name( $errors, $username, $email ) {
	$full_name = isset( $_POST['account_first_name'] ) ? trim( (string) wp_unslash( $_POST['account_first_name'] ) ) : '';

	if ( '' === $full_name ) {
		$errors->add( 'account_first_name_error', __( 'Lütfen adınızı ve soyadınızı girin.', 'woocommerce-store-child' ) );
	}

	return $errors;
}
add_filter( 'woocommerce_registration_errors', 'wcs_validate_register_name', 10, 3 );

/**
 * Save custom name field to user profile after registration.
 *
 * @param int $customer_id Customer ID.
 */
function wcs_save_register_name( $customer_id ) {
	if ( ! $customer_id ) {
		return;
	}

	$full_name = isset( $_POST['account_first_name'] ) ? trim( (string) wp_unslash( $_POST['account_first_name'] ) ) : '';

	if ( '' === $full_name ) {
		return;
	}

	update_user_meta( $customer_id, 'first_name', $full_name );

	wp_update_user(
		array(
			'ID'           => $customer_id,
			'display_name' => $full_name,
		)
	);
}
add_action( 'woocommerce_created_customer', 'wcs_save_register_name', 20 );

/**
 * Force redirect to My Account after registration.
 *
 * @param string $redirect Redirect URL.
 * @return string
 */
function wcs_registration_redirect_to_my_account( $redirect ) {
	if ( function_exists( 'wc_get_page_permalink' ) ) {
		return wc_get_page_permalink( 'myaccount' );
	}

	return $redirect;
}
add_filter( 'woocommerce_registration_redirect', 'wcs_registration_redirect_to_my_account' );

/**
 * Ensure new customers are logged in automatically after registration.
 *
 * @return bool
 */
function wcs_enable_auto_login_after_registration() {
	return true;
}
add_filter( 'woocommerce_registration_auth_new_customer', 'wcs_enable_auto_login_after_registration' );

/**
 * Force My Account registration to stay enabled.
 *
 * Some environments disable this option in WooCommerce settings, which hides
 * the register form and blocks sign-up processing. We keep it on for this UX.
 *
 * @return string
 */
function wcs_force_enable_myaccount_registration() {
	return 'yes';
}
add_filter( 'pre_option_woocommerce_enable_myaccount_registration', 'wcs_force_enable_myaccount_registration' );

/**
 * Keep both guest and account checkout modes enabled.
 *
 * @return bool
 */
function wcs_enable_checkout_registration() {
	return true;
}
add_filter( 'woocommerce_checkout_registration_enabled', 'wcs_enable_checkout_registration', 20 );

/**
 * Add a dedicated body class for the My Account login/register (guest) view.
 *
 * This allows CSS to reliably switch the layout even when :has() selectors
 * are overridden by parent theme styles.
 *
 * @param array<string> $classes Existing body classes.
 * @return array<string>
 */
function wcs_body_class_myaccount_guest_login( $classes ) {
	if ( function_exists( 'is_account_page' ) && is_account_page() && ! is_user_logged_in() ) {
		$classes[] = 'wcs-ma--login';
	}

	return $classes;
}
add_filter( 'body_class', 'wcs_body_class_myaccount_guest_login', 20 );

/**
 * Do not force account creation on checkout.
 *
 * @return bool
 */
function wcs_disable_required_checkout_registration() {
	return false;
}
add_filter( 'woocommerce_checkout_registration_required', 'wcs_disable_required_checkout_registration', 20 );

/**
 * Map custom checkout mode selection into WooCommerce posted data.
 *
 * @param array<string,mixed> $data Checkout posted data.
 * @return array<string,mixed>
 */
function wcs_apply_checkout_mode_to_posted_data( $data ) {
	if ( is_user_logged_in() ) {
		return $data;
	}

	$mode = isset( $_POST['wcs_checkout_mode'] ) ? sanitize_key( wp_unslash( $_POST['wcs_checkout_mode'] ) ) : 'guest';

	if ( 'register' === $mode ) {
		$data['createaccount'] = 1;
	} else {
		$data['createaccount'] = 0;
	}

	return $data;
}
add_filter( 'woocommerce_checkout_posted_data', 'wcs_apply_checkout_mode_to_posted_data', 20 );

/**
 * Redirect to My Account after logout.
 *
 * @param string $redirect_url Default redirect URL.
 * @return string
 */
function wcs_logout_redirect_to_my_account( $redirect_url ) {
	if ( function_exists( 'wc_get_page_permalink' ) ) {
		return wc_get_page_permalink( 'myaccount' );
	}

	return $redirect_url;
}
add_filter( 'woocommerce_logout_default_redirect_url', 'wcs_logout_redirect_to_my_account' );
