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

    // Lottie her sayfada yüklenmeli — header logosu için gerekli.
    wp_enqueue_script(
        'lottie-web',
        'https://cdnjs.cloudflare.com/ajax/libs/bodymovin/5.12.2/lottie.min.js',
        array(),
        '5.12.2',
        false // <head>'de yükle, logo hemen görünsün.
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

    if ( $is_shop_context ) {
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

        wp_enqueue_style(
            'wcs-hero-style',
            get_stylesheet_directory_uri() . '/template-parts/components/hero/hero.css',
            array( 'wcs-custom-style' ),
            wcs_asset_version( 'template-parts/components/hero/hero.css', $child_theme->get( 'Version' ) )
        );
    }

	if ( function_exists( 'is_product' ) && is_product() ) {
        global $product;

		// Varyasyon attribute butonları sadece variable ürünlerde gerekli.
		if ( $product instanceof WC_Product && $product->is_type( 'variable' ) ) {
			wp_enqueue_script(
				'wcs-attribute-buttons',
				get_stylesheet_directory_uri() . '/assets/js/wcs-attribute-buttons.js',
				array( 'jquery', 'wc-add-to-cart-variation' ),
				wcs_asset_version( 'assets/js/wcs-attribute-buttons.js', $child_theme->get( 'Version' ) ),
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

		if ( function_exists( 'wc_get_price_to_display' ) && $product instanceof WC_Product ) {
			$unit_price_for_total = wc_get_price_to_display( $product );

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

		wp_localize_script(
			'wcs-ajax-add-to-cart',
			'wcsAjaxAddToCart',
			array(
				'cartUrl' => function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'cart' ) : home_url( '/cart/' ),
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
}
add_action( 'wp_enqueue_scripts', 'wcs_child_enqueue_assets' );

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

    return $layout;
}
add_filter( 'astra_page_layout', 'wcs_shop_no_sidebar_layout', 99 );
add_filter( 'astra_woo_shop_sidebar_init', '__return_false' );

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
    $count = function_exists( 'WC' ) && WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
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
            <?php woocommerce_mini_cart(); ?>
        </div>
        <footer class="wcs-cart-sidebar__footer">
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

        toggle.addEventListener('click', function(){
            var open = panel.hidden === false;
            panel.hidden = open;
            toggle.setAttribute('aria-expanded', !open);
            bar.classList.toggle('wcs-filter-bar--open', !open);
        });

        // Radio pill seçimi → submit
        panel.querySelectorAll('input[type="radio"]').forEach(function(radio){
            radio.addEventListener('change', function(){
                // Aktif class güncelle
                var group = radio.closest('.wcs-filter-panel__options');
                if (group) {
                    group.querySelectorAll('.wcs-filter-option').forEach(function(lbl){ lbl.classList.remove('wcs-filter-option--active'); });
                    radio.closest('.wcs-filter-option').classList.add('wcs-filter-option--active');
                }
            });
        });

        // ESC ile kapat
        document.addEventListener('keydown', function(e){
            if (e.key === 'Escape' && !panel.hidden) {
                panel.hidden = true;
                toggle.setAttribute('aria-expanded', false);
                bar.classList.remove('wcs-filter-bar--open');
                toggle.focus();
            }
        });

        // Dışarı tıkla kapat
        document.addEventListener('click', function(e){
            if (!bar.contains(e.target) && !panel.hidden) {
                panel.hidden = true;
                toggle.setAttribute('aria-expanded', false);
                bar.classList.remove('wcs-filter-bar--open');
            }
        });
    })();
    </script>
    <?php
}
add_action( 'woocommerce_before_shop_loop', 'wcs_render_shop_attribute_filters', 15 );

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
