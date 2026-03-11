<?php
/**
 * Home category cards below hero.
 *
 * Üç ana ürün kategorisini hero altına büyük kartlar olarak gösterir.
 *
 * @package WooCommerceStoreChild
 */

defined( 'ABSPATH' ) || exit;

if ( ! taxonomy_exists( 'product_cat' ) ) {
	return;
}

$categories = get_terms(
	array(
		'taxonomy'   => 'product_cat',
		'hide_empty' => true,
		'number'     => 3,
		'orderby'    => 'menu_order',
		'order'      => 'ASC',
	)
);

if ( is_wp_error( $categories ) || empty( $categories ) ) {
	return;
}

$section_id = 'wcs-home-categories';
?>

<section id="<?php echo esc_attr( $section_id ); ?>" class="wcs-home-categories" aria-labelledby="wcs-home-products-title">
	<div class="wcs-home-categories__inner">
		<header class="wcs-home-categories__header">
			<h2 id="wcs-home-products-title" class="wcs-home-categories__title">
				<?php esc_html_e( 'Kategori Kartları', 'woocommerce-store-child' ); ?>
			</h2>
			<p class="wcs-home-categories__subtitle">
				<?php esc_html_e( 'Güvenlik filesi çözümlerini kullanım alanına göre hızlıca keşfedin.', 'woocommerce-store-child' ); ?>
			</p>
		</header>

		<div class="wcs-home-categories__grid" role="list">
			<?php foreach ( $categories as $category ) : ?>
				<?php
				$thumb_id  = get_term_meta( $category->term_id, 'thumbnail_id', true );
				$image_url = $thumb_id ? wp_get_attachment_image_url( $thumb_id, 'large' ) : '';
				$permalink = get_term_link( $category );

				if ( is_wp_error( $permalink ) ) {
					continue;
				}
				?>

				<article class="wcs-home-category-card" role="listitem">
					<a class="wcs-home-category-card__link" href="<?php echo esc_url( $permalink ); ?>">
						<?php if ( $image_url ) : ?>
							<img
								class="wcs-home-category-card__img"
								src="<?php echo esc_url( $image_url ); ?>"
								alt="<?php echo esc_attr( $category->name ); ?>"
								loading="lazy"
							/>
						<?php else : ?>
							<div class="wcs-home-category-card__media-placeholder" aria-hidden="true">
								<span class="wcs-home-category-card__media-icon"></span>
							</div>
						<?php endif; ?>

						<div class="wcs-home-category-card__overlay">
							<h3 class="wcs-home-category-card__title">
								<?php echo esc_html( $category->name ); ?>
							</h3>
							<p class="wcs-home-category-card__subtitle">
								<?php esc_html_e( 'Kategorideki ürünleri gör →', 'woocommerce-store-child' ); ?>
							</p>
						</div>
					</a>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>