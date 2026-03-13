<?php
/**
 * Blog sidebar.
 *
 * @package WooCommerce_Store_Child
 */

defined( 'ABSPATH' ) || exit;

$blog_categories = get_categories(
	array(
		'hide_empty' => true,
	)
);

$recent_posts = get_posts(
	array(
		'numberposts' => 5,
		'post_type'   => 'post',
	)
);

$shop_url     = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/urunler/' );
$whatsapp_url = 'https://wa.me/905000000000?text=' . rawurlencode( 'Merhaba, güvenlik filesi hakkında bilgi ve fiyat almak istiyorum.' );
?>

<div class="wcs-blog-sidebar">
	<section class="wcs-blog-sidebar__section">
		<h2 class="wcs-blog-sidebar__title">
			<?php esc_html_e( 'Kategoriler', 'woocommerce-store-child' ); ?>
		</h2>
		<ul class="wcs-blog-sidebar__list">
			<?php foreach ( $blog_categories as $category ) : ?>
				<li>
					<a href="<?php echo esc_url( get_category_link( $category ) ); ?>">
						<?php echo esc_html( $category->name ); ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</section>

	<section class="wcs-blog-sidebar__section">
		<h2 class="wcs-blog-sidebar__title">
			<?php esc_html_e( 'Son Yazılar', 'woocommerce-store-child' ); ?>
		</h2>
		<ul class="wcs-blog-sidebar__list wcs-blog-sidebar__list--recent">
			<?php foreach ( $recent_posts as $post ) : ?>
				<li>
					<a href="<?php echo esc_url( get_permalink( $post ) ); ?>">
						<?php echo esc_html( get_the_title( $post ) ); ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</section>

	<section class="wcs-blog-sidebar__section wcs-blog-sidebar__section--cta">
		<h2 class="wcs-blog-sidebar__title">
			<?php esc_html_e( 'Alanınız için doğru çözümü bulun', 'woocommerce-store-child' ); ?>
		</h2>
		<p class="wcs-blog-sidebar__text">
			Balkon, kedi, çocuk veya havuz güvenliği için ölçülerinizi paylaşın;
			size en uygun file ve montaj çözümünü birlikte planlayalım.
		</p>
		<div class="wcs-blog-sidebar__cta-buttons">
			<a class="wcs-blog-sidebar__btn wcs-blog-sidebar__btn--primary" href="<?php echo esc_url( $shop_url ); ?>">
				<?php esc_html_e( 'Size uygun ürünleri inceleyin', 'woocommerce-store-child' ); ?>
			</a>
			<a class="wcs-blog-sidebar__btn wcs-blog-sidebar__btn--ghost" href="<?php echo esc_url( $whatsapp_url ); ?>" target="_blank" rel="noopener">
				<?php esc_html_e( 'WhatsApp ile Sor', 'woocommerce-store-child' ); ?>
			</a>
		</div>
	</section>
</div>

