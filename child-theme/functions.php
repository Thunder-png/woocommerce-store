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

    wp_enqueue_style(
        'wcs-shop-header-style',
        get_stylesheet_directory_uri() . '/template-parts/header/shop-header.css',
        array( 'wcs-custom-style' ),
        wcs_asset_version( 'template-parts/header/shop-header.css', $child_theme->get( 'Version' ) )
    );

    if ( is_front_page() ) {
        wp_enqueue_style(
            'wcs-home-category-grid',
            get_stylesheet_directory_uri() . '/assets/css/home-category-grid.css',
            array( 'wcs-custom-style' ),
            wcs_asset_version( 'assets/css/home-category-grid.css', $child_theme->get( 'Version' ) )
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

        $price_per_m2 = $product instanceof WC_Product ? (float) wc_get_price_to_display( $product ) : 0;

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

        wp_localize_script(
            'wcs-m2-calculator',
            'wcsCalculator',
            array(
                'pricePerM2' => $price_per_m2,
                'vatRate'    => 0.20,
                'currency'   => get_woocommerce_currency_symbol(),
            )
        );

        wp_enqueue_script(
            'wcs-attribute-buttons',
            get_stylesheet_directory_uri() . '/assets/js/wcs-attribute-buttons.js',
            array( 'jquery', 'wc-add-to-cart-variation' ),
            wcs_asset_version( 'assets/js/wcs-attribute-buttons.js', $child_theme->get( 'Version' ) ),
            true
        );

        wp_enqueue_style(
            'wcs-product-detail',
            get_stylesheet_directory_uri() . '/assets/css/product-detail.css',
            array( 'wcs-custom-style' ),
            wcs_asset_version( 'assets/css/product-detail.css', $child_theme->get( 'Version' ) )
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

            <label for="wcs-thickness"><?php esc_html_e( 'İp Kalınlığı (mm)', 'woocommerce-store-child' ); ?></label>
            <select id="wcs-thickness" name="wcs_thickness">
                <option value=""><?php esc_html_e( 'Seçiniz', 'woocommerce-store-child' ); ?></option>
                <option value="1.5">1.5 mm</option>
                <option value="2">2 mm</option>
                <option value="2.5">2.5 mm</option>
                <option value="3">3 mm</option>
                <option value="4">4 mm</option>
                <option value="6">6 mm</option>
            </select>

            <label for="wcs-mesh"><?php esc_html_e( 'Göz Boyutu', 'woocommerce-store-child' ); ?></label>
            <select id="wcs-mesh" name="wcs_mesh">
                <option value=""><?php esc_html_e( 'Seçiniz', 'woocommerce-store-child' ); ?></option>
                <option value="2x2">2x2</option>
                <option value="4x4">4x4</option>
                <option value="5x5">5x5</option>
                <option value="10x10">10x10</option>
                <option value="12x12">12x12</option>
                <option value="13x13">13x13</option>
            </select>

            <label for="wcs-color"><?php esc_html_e( 'Renk Grubu', 'woocommerce-store-child' ); ?></label>
            <select id="wcs-color" name="wcs_color">
                <option value="standard"><?php esc_html_e( 'Standart (Beyaz)', 'woocommerce-store-child' ); ?></option>
                <option value="colored"><?php esc_html_e( 'Renkli', 'woocommerce-store-child' ); ?></option>
                <option value="black"><?php esc_html_e( 'Siyah / Gri', 'woocommerce-store-child' ); ?></option>
            </select>
        </div>

        <div class="wcs-calculator__results" aria-live="polite">
            <p><?php esc_html_e( 'Area:', 'woocommerce-store-child' ); ?> <strong id="wcs-area">0.00 m²</strong></p>
            <p><?php esc_html_e( 'Base Price:', 'woocommerce-store-child' ); ?> <strong id="wcs-price">0.00</strong></p>
            <p><?php esc_html_e( 'VAT (20%):', 'woocommerce-store-child' ); ?> <strong id="wcs-vat">0.00</strong></p>
            <p><?php esc_html_e( 'Total:', 'woocommerce-store-child' ); ?> <strong id="wcs-total">0.00</strong></p>
        </div>

        <button type="button" class="wcs-calculator__add-to-cart">
            <?php esc_html_e( 'Özel ölçü ile sepete ekle', 'woocommerce-store-child' ); ?>
        </button>
    </section>
    <?php
}
add_action( 'woocommerce_before_add_to_cart_form', 'wcs_render_price_calculator', 15 );

/**
 * Özel ölçü siparişleri için sepet satırı verisini ekle.
 *
 * @param array $cart_item_data Mevcut sepet verisi.
 * @param int   $product_id     Ürün ID.
 * @param int   $variation_id   Varyasyon ID.
 * @return array
 */
function wcs_custom_order_add_cart_item_data( $cart_item_data, $product_id, $variation_id ) {
	if ( empty( $_POST['wcs_custom_order'] ) || '1' !== $_POST['wcs_custom_order'] ) {
		return $cart_item_data;
	}

	$fields = array(
		'wcs_width',
		'wcs_height',
		'wcs_thickness',
		'wcs_mesh',
		'wcs_color',
		'wcs_area',
		'wcs_price_per_m2',
		'wcs_total',
	);

	$data = array();

	foreach ( $fields as $field ) {
		if ( isset( $_POST[ $field ] ) ) {
			$data[ $field ] = sanitize_text_field( wp_unslash( $_POST[ $field ] ) );
		}
	}

	$cart_item_data['wcs_custom_order']       = 1;
	$cart_item_data['wcs_custom_order_meta']  = $data;

	return $cart_item_data;
}
add_filter( 'woocommerce_add_cart_item_data', 'wcs_custom_order_add_cart_item_data', 10, 3 );

/**
 * Özel ölçü siparişlerinde ürün birim fiyatını, hesaplanan toplam tutara göre ayarla.
 *
 * @param WC_Cart $cart Cart instance.
 */
function wcs_custom_order_before_calculate_totals( $cart ) {
	if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
		return;
	}

	if ( ! $cart instanceof WC_Cart ) {
		return;
	}

	foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {
		if ( empty( $cart_item['wcs_custom_order'] ) || empty( $cart_item['wcs_custom_order_meta']['wcs_total'] ) ) {
			continue;
		}

		$total = (float) $cart_item['wcs_custom_order_meta']['wcs_total'];
		$qty   = max( 1, (int) $cart_item['quantity'] );

		$unit_price = $total / $qty;

		if ( isset( $cart_item['data'] ) && $cart_item['data'] instanceof WC_Product ) {
			$cart_item['data']->set_price( $unit_price );
		}
	}
}
add_action( 'woocommerce_before_calculate_totals', 'wcs_custom_order_before_calculate_totals', 20 );

/**
 * Sepet satırında özel ölçü meta bilgisini göster.
 *
 * @param array $item_data Mevcut görüntülenecek veriler.
 * @param array $cart_item Sepet satırı.
 * @return array
 */
function wcs_custom_order_get_item_data( $item_data, $cart_item ) {
	if ( empty( $cart_item['wcs_custom_order_meta'] ) || ! is_array( $cart_item['wcs_custom_order_meta'] ) ) {
		return $item_data;
	}

	$meta = $cart_item['wcs_custom_order_meta'];

	if ( ! empty( $meta['wcs_width'] ) && ! empty( $meta['wcs_height'] ) ) {
		$item_data[] = array(
			'key'   => __( 'Ölçü', 'woocommerce-store-child' ),
			'value' => sprintf( '%s m x %s m', $meta['wcs_width'], $meta['wcs_height'] ),
		);
	}

	if ( ! empty( $meta['wcs_thickness'] ) ) {
		$item_data[] = array(
			'key'   => __( 'İp Kalınlığı', 'woocommerce-store-child' ),
			'value' => $meta['wcs_thickness'] . ' mm',
		);
	}

	if ( ! empty( $meta['wcs_mesh'] ) ) {
		$item_data[] = array(
			'key'   => __( 'Göz Boyutu', 'woocommerce-store-child' ),
			'value' => $meta['wcs_mesh'],
		);
	}

	if ( ! empty( $meta['wcs_color'] ) ) {
		$item_data[] = array(
			'key'   => __( 'Renk Grubu', 'woocommerce-store-child' ),
			'value' => $meta['wcs_color'],
		);
	}

	if ( ! empty( $meta['wcs_area'] ) ) {
		$item_data[] = array(
			'key'   => __( 'Hesaplanan Alan', 'woocommerce-store-child' ),
			'value' => $meta['wcs_area'] . ' m²',
		);
	}

	return $item_data;
}
add_filter( 'woocommerce_get_item_data', 'wcs_custom_order_get_item_data', 10, 2 );

/**
 * Özel ölçü siparişlerinde ürün adını ön ekle değiştir.
 *
 * @param string $name      Ürün adı.
 * @param array  $cart_item Sepet satırı.
 * @param string $key       Sepet anahtarı.
 * @return string
 */
function wcs_custom_order_cart_item_name( $name, $cart_item, $key ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
	if ( ! empty( $cart_item['wcs_custom_order'] ) ) {
		$name = __( 'Özel ölçü sipariş', 'woocommerce-store-child' ) . ' - ' . $name;
	}

	return $name;
}
add_filter( 'woocommerce_cart_item_name', 'wcs_custom_order_cart_item_name', 10, 3 );

/**
 * Render brand footer with policy links.
 */
function wcs_render_brand_footer() {
    if ( is_admin() ) {
        return;
    }

    $policy_links = array(
        array(
            'label' => __( 'Gizlilik Politikası', 'woocommerce-store-child' ),
            'url'   => home_url( '/gizlilik-politikasi/' ),
        ),
        array(
            'label' => __( 'İade ve İptal Politikası', 'woocommerce-store-child' ),
            'url'   => home_url( '/iade-ve-iptal-politikasi/' ),
        ),
        array(
            'label' => __( 'KVKK Aydınlatma Metni', 'woocommerce-store-child' ),
            'url'   => home_url( '/kvkk-aydinlatma-metni/' ),
        ),
        array(
            'label' => __( 'Ödeme ve Teslimat', 'woocommerce-store-child' ),
            'url'   => home_url( '/odeme-ve-teslimat/' ),
        ),
        array(
            'label' => __( 'Çerez Politikası', 'woocommerce-store-child' ),
            'url'   => home_url( '/cerez-politikasi/' ),
        ),
        array(
            'label' => __( 'Mesafeli Satış Sözleşmesi', 'woocommerce-store-child' ),
            'url'   => home_url( '/mesafeli-satis-sozlesmesi/' ),
        ),
    );
    ?>
    <footer class="wcs-brand-footer" aria-label="Site footer">
        <div class="wcs-brand-footer__inner">
            <div class="wcs-brand-footer__brand">
                <h3><?php esc_html_e( 'By Karaca', 'woocommerce-store-child' ); ?></h3>
                <p><?php esc_html_e( 'Güvenli alışveriş, güçlü koruma ürünleri.', 'woocommerce-store-child' ); ?></p>
            </div>

            <nav class="wcs-brand-footer__policies" aria-label="Policy links">
                <h4><?php esc_html_e( 'Politikalar', 'woocommerce-store-child' ); ?></h4>
                <ul>
                    <?php foreach ( $policy_links as $policy_link ) : ?>
                        <li>
                            <a href="<?php echo esc_url( $policy_link['url'] ); ?>"><?php echo esc_html( $policy_link['label'] ); ?></a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </nav>

            <div class="wcs-brand-footer__contact">
                <h4><?php esc_html_e( 'İletişim', 'woocommerce-store-child' ); ?></h4>
                <p><a href="mailto:info@bykaracafile.com.tr">info@bykaracafile.com.tr</a></p>
                <p>0850 380 20 06</p>
            </div>
        </div>
    </footer>
    <?php
}
add_action( 'wp_footer', 'wcs_render_brand_footer', 20 );


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
 * Render shop filter controls for configured attributes.
 */
function wcs_render_shop_attribute_filters() {
    if ( ! function_exists( 'is_shop' ) || ( ! is_shop() && ! is_product_taxonomy() ) ) {
        return;
    }

    $definitions = wcs_get_filterable_attribute_definitions();
    ?>
    <form class="wcs-product-filters" method="get" action="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">
        <div class="wcs-product-filters__grid">
            <?php foreach ( $definitions as $slug => $config ) : ?>
                <?php
                $taxonomy  = 'pa_' . $slug;
                $query_key = 'filter_' . $taxonomy;

                if ( ! taxonomy_exists( $taxonomy ) ) {
                    continue;
                }

                $terms = get_terms(
                    array(
                        'taxonomy'   => $taxonomy,
                        'hide_empty' => false,
                    )
                );

                if ( is_wp_error( $terms ) || empty( $terms ) ) {
                    continue;
                }

                $selected = isset( $_GET[ $query_key ] ) ? sanitize_title( wp_unslash( $_GET[ $query_key ] ) ) : '';
                ?>
                <label>
                    <span><?php echo esc_html( $config['label'] ); ?></span>
                    <select name="<?php echo esc_attr( $query_key ); ?>">
                        <option value=""><?php esc_html_e( 'Tümü', 'woocommerce-store-child' ); ?></option>
                        <?php foreach ( $terms as $term ) : ?>
                            <option value="<?php echo esc_attr( $term->slug ); ?>" <?php selected( $selected, $term->slug ); ?>>
                                <?php echo esc_html( $term->name ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
            <?php endforeach; ?>
        </div>

        <?php foreach ( $_GET as $key => $value ) : ?>
            <?php
            if ( ! is_string( $key ) || 0 === strpos( $key, 'filter_pa_' ) || 0 === strpos( $key, 'query_type_pa_' ) || 'paged' === $key ) {
                continue;
            }
            ?>
            <input type="hidden" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( wp_unslash( $value ) ); ?>" />
        <?php endforeach; ?>

        <div class="wcs-product-filters__actions">
            <button type="submit"><?php esc_html_e( 'Filtrele', 'woocommerce-store-child' ); ?></button>
            <a class="button" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"><?php esc_html_e( 'Temizle', 'woocommerce-store-child' ); ?></a>
        </div>
    </form>
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
	function ( $passed, $product_id, $quantity, $variation_id, $variations ) {
		// Özel ölçü siparişi ise varyasyon doğrulamasını atla.
		if ( isset( $_POST['wcs_custom_order'] ) && '1' === $_POST['wcs_custom_order'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			return true;
		}

		// Kart tabanlı seçicide geçerli variation_id varsa attribute uyuşmazlığı uyarısını yumuşat.
		if ( is_product() && $variation_id > 0 ) {
			return true;
		}

		return $passed;
	},
	20,
	5
);


