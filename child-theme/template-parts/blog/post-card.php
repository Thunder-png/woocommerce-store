<?php
/**
 * Blog post card.
 *
 * Used in archives, category pages and search results.
 *
 * @package WooCommerce_Store_Child
 */

defined( 'ABSPATH' ) || exit;

$post_id = get_the_ID();

$reading_time = function_exists( 'get_estimated_reading_time' )
	? get_estimated_reading_time( $post_id )
	: '';

$category = get_the_category( $post_id );
$category = ! empty( $category ) ? $category[0] : null;
?>

<article <?php post_class( 'wcs-blog-card' ); ?>>
	<a class="wcs-blog-card__image-link" href="<?php the_permalink(); ?>">
		<?php if ( has_post_thumbnail( $post_id ) ) : ?>
			<?php
			the_post_thumbnail(
				'wcs-blog-card',
				array(
					'class' => 'wcs-blog-card__image',
					'alt'   => get_post_meta( get_post_thumbnail_id( $post_id ), '_wp_attachment_image_alt', true ) ?: get_the_title(),
				)
			);
			?>
		<?php else : ?>
			<div class="wcs-blog-card__image wcs-blog-card__image--placeholder" aria-hidden="true"></div>
		<?php endif; ?>

		<?php if ( $category instanceof WP_Term ) : ?>
			<span class="wcs-blog-card__category">
				<?php echo esc_html( $category->name ); ?>
			</span>
		<?php endif; ?>
	</a>

	<div class="wcs-blog-card__body">
		<h2 class="wcs-blog-card__title">
			<a href="<?php the_permalink(); ?>">
				<?php the_title(); ?>
			</a>
		</h2>

		<div class="wcs-blog-card__meta">
			<time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>">
				<?php echo esc_html( get_the_date() ); ?>
			</time>

			<?php if ( $reading_time ) : ?>
				<span class="wcs-blog-card__reading-time">
					<?php echo esc_html( $reading_time ); ?>
				</span>
			<?php endif; ?>
		</div>

		<p class="wcs-blog-card__excerpt">
			<?php
			if ( function_exists( 'blog_get_trimmed_excerpt' ) ) {
				echo esc_html( blog_get_trimmed_excerpt( 26 ) );
			} else {
				echo esc_html( wp_trim_words( get_the_excerpt(), 26 ) );
			}
			?>
		</p>

		<div class="wcs-blog-card__actions">
			<a class="wcs-blog-card__button" href="<?php the_permalink(); ?>">
				<?php esc_html_e( 'Devamını Oku', 'woocommerce-store-child' ); ?>
			</a>
		</div>
	</div>
</article>

