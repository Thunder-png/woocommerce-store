<?php
/**
 * Category archive template for blog.
 *
 * @package WooCommerce_Store_Child
 */

defined( 'ABSPATH' ) || exit;

get_header();

$category       = get_queried_object();
$category_title = $category instanceof WP_Term ? $category->name : __( 'Blog Kategorisi', 'woocommerce-store-child' );
$description    = $category instanceof WP_Term ? term_description( $category ) : '';
?>

<main id="primary" class="wcs-blog wcs-blog--archive">
	<header class="wcs-blog-archive__hero">
		<nav class="wcs-blog-breadcrumb" aria-label="İçerik yolu">
			<?php
			if ( function_exists( 'render_blog_breadcrumb' ) ) {
				render_blog_breadcrumb();
			}
			?>
		</nav>

		<div class="wcs-blog-archive__hero-inner">
			<p class="wcs-blog-archive__eyebrow">Blog Kategorisi</p>
			<h1 class="wcs-blog-archive__title">
				<?php echo esc_html( $category_title ); ?>
			</h1>
			<?php if ( $description ) : ?>
				<div class="wcs-blog-archive__lead">
					<?php echo wp_kses_post( wpautop( $description ) ); ?>
				</div>
			<?php else : ?>
				<p class="wcs-blog-archive__lead">
					<?php
					printf(
						/* translators: %s: category name */
						esc_html__( '%s ile ilgili rehberler, montaj ipuçları ve gerçek kullanım senaryoları.', 'woocommerce-store-child' ),
						esc_html( $category_title )
					);
					?>
				</p>
			<?php endif; ?>
		</div>

		<div class="wcs-blog-archive__meta-bar">
			<?php get_search_form(); ?>

			<div class="wcs-blog-archive__categories" aria-label="Blog kategorileri">
				<?php
				if ( function_exists( 'wcs_blog_render_category_filter' ) ) {
					wcs_blog_render_category_filter( $category );
				}
				?>
			</div>
		</div>
	</header>

	<section class="wcs-blog-archive__grid" aria-label="Kategori yazıları">
		<?php if ( have_posts() ) : ?>
			<div class="wcs-blog-archive__grid-inner">
				<?php
				while ( have_posts() ) :
					the_post();

					get_template_part( 'template-parts/blog/post-card' );
				endwhile;
				?>
			</div>

			<div class="wcs-blog-archive__pagination">
				<?php
				the_posts_pagination(
					array(
						'prev_text' => __( 'Önceki', 'woocommerce-store-child' ),
						'next_text' => __( 'Sonraki', 'woocommerce-store-child' ),
					)
				);
				?>
			</div>
		<?php else : ?>
			<p class="wcs-blog-archive__empty">
				Bu kategoride henüz içerik bulunmuyor. Diğer blog yazılarımıza göz atabilirsiniz.
			</p>
		<?php endif; ?>
	</section>
</main>

<?php
get_footer();

