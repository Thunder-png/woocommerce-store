<?php
/**
 * Search results template for blog and site content.
 *
 * @package WooCommerce_Store_Child
 */

defined( 'ABSPATH' ) || exit;

get_header();

$query = get_search_query();
?>

<main id="primary" class="wcs-blog wcs-blog--archive wcs-blog--search">
	<header class="wcs-blog-archive__hero">
		<nav class="wcs-blog-breadcrumb" aria-label="İçerik yolu">
			<?php
			if ( function_exists( 'render_blog_breadcrumb' ) ) {
				render_blog_breadcrumb();
			}
			?>
		</nav>

		<div class="wcs-blog-archive__hero-inner">
			<p class="wcs-blog-archive__eyebrow">Arama Sonuçları</p>
			<h1 class="wcs-blog-archive__title">
				"<?php echo esc_html( $query ); ?>" için sonuçlar
			</h1>
			<p class="wcs-blog-archive__lead">
				Güvenlik filesi blog yazılarında ve sayfalarda arama yaptınız.
				Aşağıda eşleşen içerikleri görebilirsiniz.
			</p>
		</div>

		<div class="wcs-blog-archive__meta-bar">
			<?php get_search_form(); ?>
		</div>
	</header>

	<section class="wcs-blog-archive__grid" aria-label="Arama sonuçları">
		<?php if ( have_posts() ) : ?>
			<div class="wcs-blog-archive__grid-inner">
				<?php
				while ( have_posts() ) :
					the_post();

					if ( 'post' === get_post_type() ) {
						get_template_part( 'template-parts/blog/post-card' );
					} else {
						?>
						<article <?php post_class( 'wcs-blog-search__result' ); ?>>
							<h2 class="wcs-blog-search__title">
								<a href="<?php the_permalink(); ?>">
									<?php the_title(); ?>
								</a>
							</h2>
							<p class="wcs-blog-search__excerpt">
								<?php echo esc_html( wp_trim_words( get_the_excerpt(), 24 ) ); ?>
							</p>
						</article>
						<?php
					}
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
			<div class="wcs-blog-search__empty">
				<p>
					Aramanızla tam eşleşen bir içerik bulamadık.
					Aşağıdaki kategorilerden ihtiyacınıza uygun blog yazılarını keşfedebilirsiniz.
				</p>
				<div class="wcs-blog-archive__categories" aria-label="Popüler blog kategorileri">
					<?php
					if ( function_exists( 'wcs_blog_render_category_filter' ) ) {
						wcs_blog_render_category_filter();
					}
					?>
				</div>
			</div>
		<?php endif; ?>
	</section>
</main>

<?php
get_footer();

