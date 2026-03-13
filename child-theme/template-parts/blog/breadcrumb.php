<?php
/**
 * Breadcrumb wrapper template.
 *
 * In most templates, we call render_blog_breadcrumb() directly,
 * but this file can be used as a get_template_part() fallback.
 *
 * @package WooCommerce_Store_Child
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'render_blog_breadcrumb' ) ) {
	return;
}
?>

<nav class="wcs-blog-breadcrumb" aria-label="<?php esc_attr_e( 'İçerik yolu', 'woocommerce-store-child' ); ?>">
	<?php render_blog_breadcrumb(); ?>
</nav>

