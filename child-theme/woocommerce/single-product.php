<?php
/**
 * Single Product page template override.
 *
 * Uses custom `wcs-product-card` layout while keeping WooCommerce hooks.
 *
 * @package WooCommerceStoreChild
 */

defined( 'ABSPATH' ) || exit;

get_header( 'shop' );

/**
 * Hook: woocommerce_before_main_content.
 *
 * @hooked woocommerce_output_content_wrapper - 10 (outputs opening divs for the content)
 * @hooked woocommerce_breadcrumb - 20
 */
do_action( 'woocommerce_before_main_content' );

?>

<?php while ( have_posts() ) : ?>
	<?php the_post(); ?>

	<?php if ( post_password_required() ) : ?>
		<?php echo get_the_password_form(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<?php
		// Do not render product layout if password protected.
		continue;
		?>
	<?php endif; ?>

	<?php
	global $product;
	?>

	<div id="product-<?php the_ID(); ?>" <?php wc_product_class( '', $product ); ?>>
		<?php
		get_template_part( 'template-parts/product/single-product-card' );

		/**
		 * Hook: woocommerce_after_single_product_summary.
		 *
		 * @hooked woocommerce_output_product_data_tabs - 10
		 * @hooked woocommerce_upsell_display - 15
		 * @hooked woocommerce_output_related_products - 20
		 */
		do_action( 'woocommerce_after_single_product_summary' );
		?>
	</div>

<?php endwhile; // end of the loop. ?>

<?php
/**
 * Hook: woocommerce_after_main_content.
 *
 * @hooked woocommerce_output_content_wrapper_end - 10 (outputs closing divs for the content)
 */
do_action( 'woocommerce_after_main_content' );

/**
 * Hook: woocommerce_sidebar.
 *
 * @hooked woocommerce_get_sidebar - 10
 */
do_action( 'woocommerce_sidebar' );

get_footer( 'shop' );

