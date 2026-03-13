<?php
/**
 * FAQ block for single blog posts.
 *
 * @package WooCommerce_Store_Child
 *
 * @param int $post_id Current post ID.
 */

defined( 'ABSPATH' ) || exit;

if ( ! isset( $post_id ) ) {
	$post_id = get_the_ID();
}

$faq_items = get_post_meta( $post_id, '_wcs_blog_faq_items', true );

if ( ! is_array( $faq_items ) || empty( $faq_items ) ) {
	return;
}
?>

<section class="wcs-blog-faq" aria-labelledby="wcs-blog-faq-title">
	<header class="wcs-blog-faq__header">
		<p class="wcs-blog-faq__eyebrow">
			<?php esc_html_e( 'Sık sorulan sorular', 'woocommerce-store-child' ); ?>
		</p>
		<h2 id="wcs-blog-faq-title" class="wcs-blog-faq__title">
			<?php esc_html_e( 'Güvenlik filesi hakkında merak edilenler', 'woocommerce-store-child' ); ?>
		</h2>
	</header>

	<div class="wcs-blog-faq__items" itemscope itemtype="https://schema.org/FAQPage">
		<?php foreach ( $faq_items as $index => $item ) : ?>
			<?php
			$question = isset( $item['question'] ) ? trim( (string) $item['question'] ) : '';
			$answer   = isset( $item['answer'] ) ? trim( (string) $item['answer'] ) : '';

			if ( '' === $question || '' === $answer ) {
				continue;
			}
			?>
			<article class="wcs-blog-faq__item" itemscope itemprop="mainEntity" itemtype="https://schema.org/Question">
				<button class="wcs-blog-faq__question" type="button" aria-expanded="false">
					<span class="wcs-blog-faq__q-label" itemprop="name">
						<?php echo esc_html( $question ); ?>
					</span>
					<span class="wcs-blog-faq__chevron" aria-hidden="true">+</span>
				</button>
				<div class="wcs-blog-faq__answer" hidden itemprop="acceptedAnswer" itemscope itemtype="https://schema.org/Answer">
					<div class="wcs-blog-faq__answer-inner" itemprop="text">
						<?php echo wp_kses_post( wpautop( $answer ) ); ?>
					</div>
				</div>
			</article>
		<?php endforeach; ?>
	</div>
</section>

