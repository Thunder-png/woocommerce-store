<?php
/**
 * Inline CTA block for blog posts.
 *
 * @package WooCommerce_Store_Child
 *
 * @param int    $post_id  Current post ID.
 * @param string $position CTA position: intro|end.
 */

defined( 'ABSPATH' ) || exit;

if ( ! isset( $post_id ) ) {
	$post_id = get_the_ID();
}

if ( ! isset( $position ) ) {
	$position = 'intro';
}

$defaults = array(
	'intro' => array(
		'title' => __( 'Bu yazıda bahsettiğimiz alan için doğru güvenlik filesini seçin.', 'woocommerce-store-child' ),
		'text'  => __( 'Balkon, kedi, çocuk veya merdiven güvenliği için ölçünüze uygun hazır çözümleri inceleyin, dilerseniz online teklif alın.', 'woocommerce-store-child' ),
	),
	'end'   => array(
		'title' => __( 'Bir sonraki adım: Alanınız için en güvenli file çözümünü birlikte seçelim.', 'woocommerce-store-child' ),
		'text'  => __( 'Fotoğraf ve ölçü göndererek birkaç dakika içinde size özel fiyat alabilir veya ürün sayfalarımızdan doğrudan sipariş oluşturabilirsiniz.', 'woocommerce-store-child' ),
	),
);

$meta_prefix = 'cta_' . ( 'intro' === $position ? 'intro' : 'end' ) . '_';

$title = get_post_meta( $post_id, $meta_prefix . 'title', true );
$text  = get_post_meta( $post_id, $meta_prefix . 'text', true );

if ( ! $title ) {
	$title = $defaults[ $position ]['title'];
}

if ( ! $text ) {
	$text = $defaults[ $position ]['text'];
}

$shop_url      = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/urunler/' );
$whatsapp_url  = 'https://wa.me/905000000000?text=' . rawurlencode( 'Merhaba, güvenlik filesi hakkında bilgi ve fiyat almak istiyorum.' );
$quote_page    = get_page_by_path( 'teklif-al' );
$quote_url     = $quote_page instanceof WP_Post ? get_permalink( $quote_page ) : home_url( '/teklif-al/' );
$categories_cta = home_url( '/kategori/balkon-guvenlik-filesi/' );
?>

<section class="wcs-blog-cta wcs-blog-cta--<?php echo 'intro' === $position ? 'intro' : 'end'; ?>">
	<div class="wcs-blog-cta__inner">
		<div class="wcs-blog-cta__content">
			<p class="wcs-blog-cta__eyebrow">
				<?php esc_html_e( 'Güvenlik filesi çözümleri', 'woocommerce-store-child' ); ?>
			</p>
			<h2 class="wcs-blog-cta__title">
				<?php echo esc_html( $title ); ?>
			</h2>
			<p class="wcs-blog-cta__text">
				<?php echo esc_html( $text ); ?>
			</p>
		</div>

		<div class="wcs-blog-cta__actions" aria-label="<?php esc_attr_e( 'Eylem butonları', 'woocommerce-store-child' ); ?>">
			<a class="wcs-blog-cta__btn wcs-blog-cta__btn--primary" href="<?php echo esc_url( $shop_url ); ?>">
				<?php esc_html_e( 'Ürünleri İncele', 'woocommerce-store-child' ); ?>
			</a>

			<a class="wcs-blog-cta__btn wcs-blog-cta__btn--outline" href="<?php echo esc_url( $quote_url ); ?>">
				<?php esc_html_e( 'Teklif Al', 'woocommerce-store-child' ); ?>
			</a>

			<a class="wcs-blog-cta__btn wcs-blog-cta__btn--ghost" href="<?php echo esc_url( $whatsapp_url ); ?>" target="_blank" rel="noopener">
				<?php esc_html_e( 'WhatsApp ile Sor', 'woocommerce-store-child' ); ?>
			</a>
		</div>
	</div>
</section>

