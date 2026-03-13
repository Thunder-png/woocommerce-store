<?php
/**
 * Single blog post template.
 *
 * @package WooCommerce_Store_Child
 */

defined( 'ABSPATH' ) || exit;

get_header();

if ( have_posts() ) :
	while ( have_posts() ) :
		the_post();

		$reading_time = function_exists( 'get_estimated_reading_time' )
			? get_estimated_reading_time( get_the_ID() )
			: '';
		?>

		<main id="primary" class="wcs-blog wcs-blog--single">
			<article <?php post_class( 'wcs-blog-single' ); ?> itemscope itemtype="https://schema.org/Article">
				<header class="wcs-blog-single__hero">
					<nav class="wcs-blog-breadcrumb" aria-label="İçerik yolu">
						<?php
						if ( function_exists( 'render_blog_breadcrumb' ) ) {
							render_blog_breadcrumb();
						}
						?>
					</nav>

					<div class="wcs-blog-single__meta-top">
						<?php
						$category = get_the_category();
						if ( ! empty( $category ) ) :
							?>
							<a class="wcs-blog-single__category" href="<?php echo esc_url( get_category_link( $category[0]->term_id ) ); ?>">
								<?php echo esc_html( $category[0]->name ); ?>
							</a>
						<?php endif; ?>
					</div>

					<h1 class="wcs-blog-single__title" itemprop="headline">
						<?php the_title(); ?>
					</h1>

					<div class="wcs-blog-single__meta">
						<time class="wcs-blog-single__date" datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>" itemprop="datePublished">
							<?php echo esc_html( get_the_date() ); ?>
						</time>

						<?php if ( $reading_time ) : ?>
							<span class="wcs-blog-single__reading-time">
								<?php echo esc_html( $reading_time ); ?>
							</span>
						<?php endif; ?>

						<?php if ( get_the_author() ) : ?>
							<span class="wcs-blog-single__author" itemprop="author" itemscope itemtype="https://schema.org/Person">
								<?php esc_html_e( 'Yazar:', 'woocommerce-store-child' ); ?>
								<span itemprop="name"><?php the_author(); ?></span>
							</span>
						<?php endif; ?>
					</div>

					<?php if ( has_post_thumbnail() ) : ?>
						<figure class="wcs-blog-single__thumb" itemprop="image" itemscope itemtype="https://schema.org/ImageObject">
							<?php
							the_post_thumbnail(
								'large',
								array(
									'class' => 'wcs-blog-single__thumb-img',
									'alt'   => get_post_meta( get_post_thumbnail_id(), '_wp_attachment_image_alt', true ) ?: get_the_title(),
								)
							);
							?>
						</figure>
					<?php endif; ?>
				</header>

				<div class="wcs-blog-single__layout">
					<div class="wcs-blog-single__content" itemprop="articleBody">
						<?php
						if ( function_exists( 'render_blog_cta_block' ) ) {
							render_blog_cta_block( get_the_ID(), 'intro' );
						}
						?>

						<div class="wcs-blog-single__entry">
							<?php the_content(); ?>
						</div>

						<?php
						if ( function_exists( 'render_blog_cta_block' ) ) {
							render_blog_cta_block( get_the_ID(), 'end' );
						}
						?>

						<?php
						if ( function_exists( 'render_blog_faq_block' ) ) {
							render_blog_faq_block( get_the_ID() );
						}
						?>

						<?php
						wp_link_pages(
							array(
								'before' => '<div class="page-links">' . esc_html__( 'Sayfalar:', 'woocommerce-store-child' ),
								'after'  => '</div>',
							)
						);
						?>
					</div>

					<aside class="wcs-blog-single__sidebar" aria-label="<?php esc_attr_e( 'Blog yan paneli', 'woocommerce-store-child' ); ?>">
						<?php get_template_part( 'template-parts/blog/sidebar' ); ?>
					</aside>
				</div>

				<footer class="wcs-blog-single__footer">
					<?php get_template_part( 'template-parts/blog/related-posts' ); ?>
				</footer>
			</article>
		</main>

		<?php
	endwhile;
endif;

get_footer();

