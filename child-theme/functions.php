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
 * Check whether m² calculator should be enabled for a product.
 *
 * @param WC_Product|int|null $product Product object or product ID.
 * @return bool
 */
function wcs_is_m2_enabled_product( $product ) {
	if ( ! $product instanceof WC_Product ) {
		$product = wc_get_product( $product );
	}

	if ( ! ( $product instanceof WC_Product ) ) {
		return false;
	}

	$attribute_slug = 'pa_ozel-olcu';

	$has_m2_attribute = static function( WC_Product $target_product ) use ( $attribute_slug ) {
		$attributes = $target_product->get_attributes();

		if ( ! is_array( $attributes ) || empty( $attributes ) ) {
			return false;
		}

		$lookup_keys = array(
			$attribute_slug,
			'attribute_' . $attribute_slug,
			sanitize_title( $attribute_slug ),
			sanitize_title( str_replace( 'pa_', '', $attribute_slug ) ),
		);

		foreach ( $lookup_keys as $lookup_key ) {
			if ( array_key_exists( $lookup_key, $attributes ) ) {
				return true;
			}
		}

		return false;
	};

	if ( $has_m2_attribute( $product ) ) {
		return true;
	}

	if ( $product->is_type( 'variation' ) ) {
		$parent_id = $product->get_parent_id();

		if ( $parent_id ) {
			$parent_product = wc_get_product( $parent_id );

			if ( $parent_product instanceof WC_Product && $has_m2_attribute( $parent_product ) ) {
				return true;
			}
		}
	}

	return false;
}

/**
 * Enqueue child theme assets.
 */
function wcs_child_enqueue_assets() {
    $parent_theme = wp_get_theme( get_template() );
    $child_theme  = wp_get_theme();

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
		$product_id   = $product instanceof WC_Product ? $product->get_id() : 0;

		$m2_enabled = $product_id && wcs_is_custom_measure_product( $product );

		if ( $m2_enabled ) {
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
		}

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
            'wcs-cart-sidebar',
            get_stylesheet_directory_uri() . '/assets/js/wcs-cart-sidebar.js',
            array( 'jquery', 'wc-cart-fragments' ),
            wcs_asset_version( 'assets/js/wcs-cart-sidebar.js', $child_theme->get( 'Version' ) ),
            true
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

/**
 * Check whether product has the "Özel Ölçü" attribute context enabled.
 *
 * @param WC_Product|int $product Product object or ID.
 * @return bool
 */
function wcs_is_custom_measure_product( $product ) {
	$product_obj = $product instanceof WC_Product ? $product : wc_get_product( $product );

	if ( ! $product_obj instanceof WC_Product ) {
		return false;
	}

	$product_id = $product_obj->get_id();

	if ( ! $product_id ) {
		return false;
	}

	if ( has_term( 'ozel-olcu', 'pa_en-boy-orani', $product_id ) ) {
		return true;
	}

	$terms = get_the_terms( $product_id, 'pa_en-boy-orani' );

	if ( empty( $terms ) || is_wp_error( $terms ) ) {
		return false;
	}

	foreach ( $terms as $term ) {
		$norm_name = sanitize_title( $term->name );

		if ( false !== strpos( $term->slug, 'ozel-olcu' ) || false !== strpos( $norm_name, 'ozel-olcu' ) ) {
			return true;
		}
	}

	return false;
}


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

	global $product;

	if ( ! ( $product instanceof WC_Product ) ) {
		return;
	}

	$product_id = $product->get_id();

	if ( ! $product_id ) {
		return;
	}

	if ( ! wcs_is_custom_measure_product( $product ) ) {
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
    </section>
    <?php
}
add_action( 'woocommerce_before_add_to_cart_form', 'wcs_render_price_calculator', 15 );

/**
 * Render hidden calculator payload fields inside add-to-cart form.
 */
function wcs_render_calculator_hidden_inputs() {
	if ( ! function_exists( 'is_product' ) || ! is_product() ) {
		return;
	}

	global $product;

	if ( ! ( $product instanceof WC_Product ) || ! wcs_is_custom_measure_product( $product ) ) {
		return;
	}
	?>
	<input type="hidden" name="wcs_area" id="wcs-area-hidden" value="" />
	<input type="hidden" name="wcs_unit_price_m2" id="wcs-unit-price-hidden" value="" />
	<input type="hidden" name="wcs_calculated_total" id="wcs-total-hidden" value="" />
	<?php
}
add_action( 'woocommerce_before_add_to_cart_button', 'wcs_render_calculator_hidden_inputs', 15 );

/**
 * Resolve calculator unit price from server-side attributes.
 *
 * @param string $thickness Thickness in mm.
 * @param string $mesh      Mesh size.
 * @param string $color     Color value or group.
 * @return float
 */
function wcs_resolve_unit_price_m2( $thickness, $mesh, $color ) {
	$thickness_f = (float) $thickness;
	$mesh        = strtolower( trim( (string) $mesh ) );
	$color_raw   = strtolower( trim( (string) $color ) );
	$color_group = 'standard';

	if ( false !== strpos( $color_raw, 'siyah' ) || 'black' === $color_raw ) {
		$color_group = 'black';
	} elseif (
		false !== strpos( $color_raw, 'gri' ) ||
		false !== strpos( $color_raw, 'mavi' ) ||
		false !== strpos( $color_raw, 'sari' ) ||
		false !== strpos( $color_raw, 'sarı' ) ||
		false !== strpos( $color_raw, 'turuncu' ) ||
		false !== strpos( $color_raw, 'yesil' ) ||
		false !== strpos( $color_raw, 'yeşil' ) ||
		'colored' === $color_raw
	) {
		$color_group = 'colored';
	}

	$key   = $thickness_f . '|' . $mesh;
	$price = null;

	switch ( $key ) {
		case '1.5|2x2':
			$price = 45;
			break;
		case '2|4x4':
			$price = 50;
			break;
		case '2.5|4x4':
			$price = 55;
			break;
		case '3|4x4':
			$price = 60;
			break;
		case '4|4x4':
			$price = 70;
			break;
		case '6|10x10':
			$price = 70;
			break;
		case '2|13x13':
			$price = 52;
			break;
		case '4|12x12':
			$price = 60;
			break;
		case '4|5x5':
			$price = 100;
			break;
	}

	if ( 4.0 === $thickness_f && '5x5' === $mesh && 'colored' === $color_group ) {
		$price = 105;
	}

	if ( 1.5 === $thickness_f && '2x2' === $mesh && 'colored' === $color_group ) {
		$price = 50;
	}

	if ( 2.0 === $thickness_f && '4x4' === $mesh && 'black' === $color_group ) {
		$price = 70;
	}

	if ( 3.0 === $thickness_f && '4x4' === $mesh && 'black' === $color_group ) {
		$price = 75;
	}

	if ( 6.0 === $thickness_f && '10x10' === $mesh && 'black' === $color_group ) {
		$price = 95;
	}

	return null === $price ? 0.0 : (float) $price;
}

/**
 * Calculate and sanitize posted custom measure payload.
 *
 * @param WC_Product $product Product.
 * @return array<string,float|string>|null
 */
function wcs_build_measure_payload_from_post( $product ) {
	if ( ! $product instanceof WC_Product || ! wcs_is_custom_measure_product( $product ) ) {
		return null;
	}

	$width   = isset( $_POST['wcs_width'] ) ? (float) wc_clean( wp_unslash( $_POST['wcs_width'] ) ) : 0;
	$height  = isset( $_POST['wcs_height'] ) ? (float) wc_clean( wp_unslash( $_POST['wcs_height'] ) ) : 0;
	$mesh    = isset( $_POST['wcs_mesh'] ) ? wc_clean( wp_unslash( $_POST['wcs_mesh'] ) ) : '';
	$thick   = isset( $_POST['wcs_thickness'] ) ? wc_clean( wp_unslash( $_POST['wcs_thickness'] ) ) : '';
	$color   = isset( $_POST['wcs_color'] ) ? wc_clean( wp_unslash( $_POST['wcs_color'] ) ) : '';
	$attr_ob = isset( $_POST['attribute_pa_en-boy-orani'] ) ? wc_clean( wp_unslash( $_POST['attribute_pa_en-boy-orani'] ) ) : '';

	if ( $width <= 0 || $height <= 0 || '' === $mesh || '' === $thick ) {
		return null;
	}

	$unit_price = wcs_resolve_unit_price_m2( $thick, $mesh, $color );

	if ( $unit_price <= 0 ) {
		return null;
	}

	$area             = round( $width * $height, 4 );
	$calculated_total = round( $area * $unit_price, wc_get_price_decimals() );

	if ( $area <= 0 || $calculated_total <= 0 ) {
		return null;
	}

	return array(
		'width'            => $width,
		'height'           => $height,
		'area'             => $area,
		'unit_price_m2'    => $unit_price,
		'calculated_total' => $calculated_total,
		'thickness'        => $thick,
		'mesh'             => $mesh,
		'color'            => $color,
		'attribute'        => $attr_ob,
	);
}

/**
 * Persist calculator data onto cart item.
 */
function wcs_add_measure_data_to_cart_item( $cart_item_data, $product_id, $variation_id ) {
	$product = wc_get_product( $variation_id ? $variation_id : $product_id );
	$payload = wcs_build_measure_payload_from_post( $product );

	if ( null === $payload ) {
		return $cart_item_data;
	}

	$cart_item_data['wcs_measure'] = $payload;
	$cart_item_data['wcs_measure_unique'] = md5( wp_json_encode( $payload ) . microtime() );

	return $cart_item_data;
}
add_filter( 'woocommerce_add_cart_item_data', 'wcs_add_measure_data_to_cart_item', 10, 3 );

/**
 * Show custom measure data on cart/checkout item rows.
 */
function wcs_render_measure_item_data( $item_data, $cart_item ) {
	if ( empty( $cart_item['wcs_measure'] ) || ! is_array( $cart_item['wcs_measure'] ) ) {
		return $item_data;
	}

	$measure = $cart_item['wcs_measure'];

	$item_data[] = array(
		'key'   => __( 'En', 'woocommerce-store-child' ),
		'value' => wc_format_decimal( (float) $measure['width'], 2 ) . ' m',
	);
	$item_data[] = array(
		'key'   => __( 'Boy', 'woocommerce-store-child' ),
		'value' => wc_format_decimal( (float) $measure['height'], 2 ) . ' m',
	);
	$item_data[] = array(
		'key'   => __( 'm²', 'woocommerce-store-child' ),
		'value' => wc_format_decimal( (float) $measure['area'], 2 ) . ' m²',
	);
	$item_data[] = array(
		'key'   => __( 'Birim Fiyat', 'woocommerce-store-child' ),
		'value' => wc_price( (float) $measure['unit_price_m2'] ) . '/m²',
	);

	return $item_data;
}
add_filter( 'woocommerce_get_item_data', 'wcs_render_measure_item_data', 10, 2 );

/**
 * Apply recalculated custom-measure line prices.
 */
function wcs_apply_measure_price_to_cart( $cart ) {
	if ( is_admin() && ! wp_doing_ajax() ) {
		return;
	}

	if ( ! $cart instanceof WC_Cart ) {
		return;
	}

	foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {
		if ( empty( $cart_item['wcs_measure'] ) || ! is_array( $cart_item['wcs_measure'] ) ) {
			continue;
		}

		$product = isset( $cart_item['data'] ) && $cart_item['data'] instanceof WC_Product ? $cart_item['data'] : null;

		if ( ! $product instanceof WC_Product || ! wcs_is_custom_measure_product( $product ) ) {
			continue;
		}

		$measure = $cart_item['wcs_measure'];
		$unit    = wcs_resolve_unit_price_m2( $measure['thickness'] ?? '', $measure['mesh'] ?? '', $measure['color'] ?? '' );
		$area    = isset( $measure['width'], $measure['height'] ) ? ( (float) $measure['width'] * (float) $measure['height'] ) : 0;

		if ( $unit <= 0 || $area <= 0 ) {
			continue;
		}

		$line_price = round( $unit * $area, wc_get_price_decimals() );
		$cart->cart_contents[ $cart_item_key ]['wcs_measure']['unit_price_m2'] = $unit;
		$cart->cart_contents[ $cart_item_key ]['wcs_measure']['area'] = round( $area, 4 );
		$cart->cart_contents[ $cart_item_key ]['wcs_measure']['calculated_total'] = $line_price;

		$product->set_price( $line_price );
	}
}
add_action( 'woocommerce_before_calculate_totals', 'wcs_apply_measure_price_to_cart', 20 );

/**
 * Save custom measure metadata to order line items.
 */
function wcs_add_measure_meta_to_order_item( $item, $cart_item_key, $values ) {
	if ( empty( $values['wcs_measure'] ) || ! is_array( $values['wcs_measure'] ) ) {
		return;
	}

	$measure = $values['wcs_measure'];

	$item->add_meta_data( __( 'En', 'woocommerce-store-child' ), wc_format_decimal( (float) $measure['width'], 2 ) . ' m', true );
	$item->add_meta_data( __( 'Boy', 'woocommerce-store-child' ), wc_format_decimal( (float) $measure['height'], 2 ) . ' m', true );
	$item->add_meta_data( __( 'm²', 'woocommerce-store-child' ), wc_format_decimal( (float) $measure['area'], 2 ) . ' m²', true );
	$item->add_meta_data( __( 'Birim Fiyat', 'woocommerce-store-child' ), wc_format_decimal( (float) $measure['unit_price_m2'], 2 ), true );
	$item->add_meta_data( __( 'Hesaplanan Toplam', 'woocommerce-store-child' ), wc_format_decimal( (float) $measure['calculated_total'], 2 ), true );
}
add_action( 'woocommerce_checkout_create_order_line_item', 'wcs_add_measure_meta_to_order_item', 10, 3 );

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
 * Render slide-in cart sidebar container on single product pages.
 */
function wcs_render_cart_sidebar() {
	if ( ! function_exists( 'is_product' ) || ! is_product() ) {
		return;
	}
	?>
	<div class="wcs-cart-sidebar-overlay" aria-hidden="true"></div>
	<aside class="wcs-cart-sidebar" aria-label="<?php esc_attr_e( 'Sepet özeti', 'woocommerce-store-child' ); ?>">
		<header class="wcs-cart-sidebar__header">
			<h2 class="wcs-cart-sidebar__title">
				<?php esc_html_e( 'Sepetiniz', 'woocommerce-store-child' ); ?>
			</h2>
			<button type="button" class="wcs-cart-sidebar__close" aria-label="<?php esc_attr_e( 'Sepeti kapat', 'woocommerce-store-child' ); ?>">&times;</button>
		</header>
		<div class="wcs-cart-sidebar__content">
			<?php woocommerce_mini_cart(); ?>
		</div>
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
