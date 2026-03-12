<?php
/**
 * Home category and custom-size product cards below hero.
 *
 * @package WooCommerceStoreChild
 */

defined( 'ABSPATH' ) || exit;

$categories = array();

if ( taxonomy_exists( 'product_cat' ) ) {
	$categories = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => true,
			'number'     => 3,
			'orderby'    => 'menu_order',
			'order'      => 'ASC',
		)
	);
}

$custom_products = array();

if ( function_exists( 'wc_get_products' ) ) {
	$custom_tag_slugs = array( 'ozel-olcu', 'özel-ölçü', 'özel-olcu' );

	foreach ( $custom_tag_slugs as $tag_slug ) {
		$custom_products = wc_get_products(
			array(
				'status'  => 'publish',
				'limit'   => 6,
				'orderby' => 'date',
				'order'   => 'DESC',
				'tag'     => array( $tag_slug ),
			)
		);

		if ( ! empty( $custom_products ) ) {
			break;
		}
	}
}

$has_categories = ! is_wp_error( $categories ) && ! empty( $categories );
$has_products   = ! empty( $custom_products );

if ( ! $has_categories && ! $has_products ) {
	return;
}
?>

<?php if ( $has_categories ) : ?>
	<section class="wcs-home-categories wcs-home-categories--top" aria-label="<?php esc_attr_e( 'Kategori kartları', 'woocommerce-store-child' ); ?>">
		<div class="wcs-home-categories__inner">
			<header class="wcs-home-categories__divider" aria-hidden="true">
				<span><?php esc_html_e( 'Kategori Kartları (3 Adet)', 'woocommerce-store-child' ); ?></span>
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
								<img class="wcs-home-category-card__img" src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $category->name ); ?>" loading="lazy" />
							<?php else : ?>
								<div class="wcs-home-category-card__media-placeholder" aria-hidden="true">
									<span class="wcs-home-category-card__media-icon"></span>
								</div>
							<?php endif; ?>
							<div class="wcs-home-category-card__overlay">
								<h3 class="wcs-home-category-card__title"><?php echo esc_html( $category->name ); ?></h3>
								<p class="wcs-home-category-card__subtitle"><?php esc_html_e( 'Kategoriyi keşfet →', 'woocommerce-store-child' ); ?></p>
							</div>
						</a>
					</article>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
<?php endif; ?>

<?php if ( $has_products ) : ?>
	<section class="wcs-home-categories wcs-home-custom-products" aria-label="<?php esc_attr_e( 'İstediğiniz ölçüde ürünler', 'woocommerce-store-child' ); ?>">
		<div class="wcs-home-categories__inner">
			<header class="wcs-home-categories__header">
				<h2 class="wcs-home-categories__title"><?php esc_html_e( 'İstediğiniz ölçüde ürünler', 'woocommerce-store-child' ); ?></h2>
				<p class="wcs-home-categories__subtitle"><?php esc_html_e( 'Özel ölçüye uygun ürünleri keşfedin ve ihtiyacınıza göre kolayca sipariş verin.', 'woocommerce-store-child' ); ?></p>
			</header>
			<div class="wcs-home-categories__grid" role="list">
				<?php foreach ( $custom_products as $product ) : ?>
					<?php
					if ( ! $product instanceof WC_Product ) {
						continue;
					}

					$permalink = $product->get_permalink();
					$image_id  = $product->get_image_id();
					$image_url = $image_id ? wp_get_attachment_image_url( $image_id, 'medium_large' ) : '';
					?>
					<article class="wcs-home-category-card wcs-home-product-card" role="listitem">
						<a class="wcs-home-category-card__link" href="<?php echo esc_url( $permalink ); ?>">
							<?php if ( $image_url ) : ?>
								<img class="wcs-home-category-card__img" src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $product->get_name() ); ?>" loading="lazy" />
							<?php else : ?>
								<div class="wcs-home-category-card__media-placeholder" aria-hidden="true">
									<span class="wcs-home-category-card__media-icon"></span>
								</div>
							<?php endif; ?>
							<div class="wcs-home-category-card__overlay">
								<h3 class="wcs-home-category-card__title"><?php echo esc_html( $product->get_name() ); ?></h3>
								<p class="wcs-home-product-card__price"><?php echo wp_kses_post( $product->get_price_html() ); ?></p>
								<p class="wcs-home-category-card__subtitle"><?php esc_html_e( 'Ürünü incele →', 'woocommerce-store-child' ); ?></p>
							</div>
						</a>
					</article>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
<?php endif; ?>
