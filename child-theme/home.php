<?php
/**
 * Blog index (/blog) template.
 *
 * Uses the same layout as the main blog archive, but tuned for the posts page.
 *
 * @package WooCommerce_Store_Child
 */

defined( 'ABSPATH' ) || exit;

get_header();
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
			<p class="wcs-blog-archive__eyebrow">By Karaca File · Blog</p>
			<h1 class="wcs-blog-archive__title">
				Güvenlik Fileleri Blogu
			</h1>
			<p class="wcs-blog-archive__lead">
				Balkon, kedi, çocuk, havuz ve merdiven güvenliği için
				uygulamalı rehberler, montaj ipuçları ve ürün seçim önerileri.
			</p>
		</div>

		<div class="wcs-blog-archive__meta-bar">
			<?php get_search_form(); ?>

			<div class="wcs-blog-archive__categories" aria-label="Blog kategorileri">
				<?php
				if ( function_exists( 'wcs_blog_render_category_filter' ) ) {
					wcs_blog_render_category_filter();
				}
				?>
			</div>
		</div>
	</header>

	<?php
	if ( function_exists( 'wcs_blog_render_featured_post' ) ) {
		wcs_blog_render_featured_post();
	}
	?>

	<section class="wcs-blog-archive__grid" aria-label="Blog yazıları">
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
				Şu anda yayınlanmış blog yazısı bulunmuyor. Çok yakında güvenlik filesi ile ilgili detaylı içerikler burada olacak.
			</p>
		<?php endif; ?>
	</section>
</main>

<?php
get_footer();

