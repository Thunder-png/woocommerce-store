<?php
/**
 * Featured blog post hero block.
 *
 * @package WooCommerce_Store_Child
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'blog_get_featured_post' ) ) {
	return;
}

$featured = blog_get_featured_post();

if ( ! $featured instanceof WP_Post ) {
	return;
}

setup_postdata( $featured );

$post_id      = $featured->ID;
$reading_time = function_exists( 'get_estimated_reading_time' ) ? get_estimated_reading_time( $post_id ) : '';
$category     = get_the_category( $post_id );
$category     = ! empty( $category ) ? $category[0] : null;
?>

<section class="wcs-blog-featured" aria-label="<?php esc_attr_e( 'Öne çıkan blog yazısı', 'woocommerce-store-child' ); ?>">
	<div class="wcs-blog-featured__inner">
		<div class="wcs-blog-featured__content">
			<?php if ( $category instanceof WP_Term ) : ?>
				<a class="wcs-blog-featured__category" href="<?php echo esc_url( get_category_link( $category->term_id ) ); ?>">
					<?php echo esc_html( $category->name ); ?>
				</a>
			<?php endif; ?>

			<h2 class="wcs-blog-featured__title">
				<a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>">
					<?php echo esc_html( get_the_title( $post_id ) ); ?>
				</a>
			</h2>

			<div class="wcs-blog-featured__meta">
				<time datetime="<?php echo esc_attr( get_the_date( DATE_W3C, $post_id ) ); ?>">
					<?php echo esc_html( get_the_date( '', $post_id ) ); ?>
				</time>
				<?php if ( $reading_time ) : ?>
					<span class="wcs-blog-featured__reading-time">
						<?php echo esc_html( $reading_time ); ?>
					</span>
				<?php endif; ?>
			</div>

			<p class="wcs-blog-featured__excerpt">
				<?php
				if ( function_exists( 'blog_get_trimmed_excerpt' ) ) {
					echo esc_html( blog_get_trimmed_excerpt( 32, $post_id ) );
				} else {
					echo esc_html( wp_trim_words( get_the_excerpt( $post_id ), 32 ) );
				}
				?>
			</p>

			<div class="wcs-blog-featured__actions">
				<a class="wcs-blog-featured__button" href="<?php echo esc_url( get_permalink( $post_id ) ); ?>">
					<?php esc_html_e( 'Yazıyı Oku', 'woocommerce-store-child' ); ?>
				</a>
			</div>
		</div>

		<a class="wcs-blog-featured__image-link" href="<?php echo esc_url( get_permalink( $post_id ) ); ?>">
			<?php if ( has_post_thumbnail( $post_id ) ) : ?>
				<?php
				echo get_the_post_thumbnail(
					$post_id,
					'wcs-blog-featured',
					array(
						'class' => 'wcs-blog-featured__image',
						'alt'   => get_post_meta( get_post_thumbnail_id( $post_id ), '_wp_attachment_image_alt', true ) ?: get_the_title( $post_id ),
					)
				);
				?>
			<?php else : ?>
				<div class="wcs-blog-featured__image wcs-blog-featured__image--placeholder" aria-hidden="true"></div>
			<?php endif; ?>
		</a>
	</div>
</section>

<?php
wp_reset_postdata();

