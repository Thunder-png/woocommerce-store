<?php
/**
 * Home products grid section.
 *
 * WooCommerce ürünlerinden beslenen ürün grid’i.
 * Kart tasarımı için child-theme/woocommerce/content-product.php kullanılır.
 *
 * @package WooCommerceStoreChild
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'wc_get_products' ) ) {
	return;
}

// Burada istediğin kriterlere göre ürünleri filtreleyebilirsin.
// Örnek: en son eklenen 8 ürün.
$products = wc_get_products(
	array(
		'status' => 'publish',
		'limit'  => 8,
		'orderby'=> 'date',
		'order'  => 'DESC',
		'return' => 'ids',
	)
);

if ( empty( $products ) ) {
	return;
}

$section_id = 'wcs-home-products';
?>

<section id="<?php echo esc_attr( $section_id ); ?>" class="wcs-home-categories" aria-labelledby="wcs-home-products-title">
	<div class="wcs-home-categories__inner">
		<header class="wcs-home-categories__header">
			<h2 id="wcs-home-products-title" class="wcs-home-categories__title">
				<?php esc_html_e( 'Öne Çıkan Güvenlik Fileleri', 'woocommerce-store-child' ); ?>
			</h2>
			<p class="wcs-home-categories__subtitle">
				<?php esc_html_e( 'WooCommerce ürün panelinden eklediğiniz ürünlerle oluşturulan ürün grid’i.', 'woocommerce-store-child' ); ?>
			</p>
		</header>

		<ul class="products columns-4">
			<?php foreach ( $products as $product_id ) : ?>
				<?php
				$post_object = get_post( $product_id );

				if ( ! $post_object ) {
					continue;
				}

				global $post;
				$backup_post = $post;
				$post        = $post_object; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

				setup_postdata( $post );
				?>

				<?php wc_get_template_part( 'content', 'product' ); ?>

				<?php
				$post = $backup_post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
				?>
			<?php endforeach; ?>

			<?php wp_reset_postdata(); ?>
		</ul>
	</div>
</section>