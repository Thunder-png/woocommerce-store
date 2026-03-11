<?php
/**
 * Product Archive Template Override.
 *
 * @package WooCommerce\Templates
 */

defined( 'ABSPATH' ) || exit;

get_header( 'shop' );

get_template_part( 'template-parts/components/hero/hero' );
get_template_part( 'template-parts/components/home/home-category-grid' );
echo '<div id="products-grid"></div>';

do_action( 'woocommerce_before_main_content' );

// Ana sayfa shop olarak kullanıldığında (hero + 8'li grid sonrası)
// ekstra ürün loop'unu gizle; diğer durumlarda (normal /shop ve arşivler)
// standart WooCommerce loop'u çalışsın.
if ( ! ( function_exists( 'is_front_page' ) && function_exists( 'is_shop' ) && is_front_page() && is_shop() ) ) {
	if ( woocommerce_product_loop() ) {
		do_action( 'woocommerce_before_shop_loop' );

		woocommerce_product_loop_start();

		if ( wc_get_loop_prop( 'total' ) ) {
			while ( have_posts() ) {
				the_post();
				do_action( 'woocommerce_shop_loop' );
				wc_get_template_part( 'content', 'product' );
			}
		}

		woocommerce_product_loop_end();

		do_action( 'woocommerce_after_shop_loop' );
	} else {
		do_action( 'woocommerce_no_products_found' );
	}
}

do_action( 'woocommerce_after_main_content' );

get_footer( 'shop' );
