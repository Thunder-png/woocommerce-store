<?php
/**
 * Related posts section for single blog posts.
 *
 * @package WooCommerce_Store_Child
 */

defined( 'ABSPATH' ) || exit;

if ( ! is_singular( 'post' ) ) {
	return;
}

if ( ! function_exists( 'get_related_posts_by_category' ) ) {
	return;
}

$related_posts = get_related_posts_by_category( get_the_ID(), 3 );

if ( empty( $related_posts ) ) {
	return;
}
?>

<section class="wcs-blog-related" aria-label="<?php esc_attr_e( 'Benzer yazılar', 'woocommerce-store-child' ); ?>">
	<header class="wcs-blog-related__header">
		<p class="wcs-blog-related__eyebrow">
			<?php esc_html_e( 'Benzer yazılar', 'woocommerce-store-child' ); ?>
		</p>
		<h2 class="wcs-blog-related__title">
			<?php esc_html_e( 'İlginizi çekebilecek diğer içerikler', 'woocommerce-store-child' ); ?>
		</h2>
	</header>

	<div class="wcs-blog-related__grid">
		<?php
		foreach ( $related_posts as $post ) :
			setup_postdata( $post );

			get_template_part( 'template-parts/blog/post-card' );
		endforeach;
		wp_reset_postdata();
		?>
	</div>
</section>

