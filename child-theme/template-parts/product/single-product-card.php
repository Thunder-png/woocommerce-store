<?php
/**
 * Single product main card layout.
 *
 * Custom product detail card inspired by marketplace-style design.
 *
 * Loaded from: child-theme/woocommerce/single-product.php
 *
 * @package WooCommerceStoreChild
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( ! $product instanceof WC_Product ) {
	return;
}

$product_id = $product->get_id();

// Basic product data.
$title        = get_the_title( $product_id );
$permalink    = get_permalink( $product_id );
$rating_count = $product->get_rating_count();
$average      = $product->get_average_rating();
$review_count = $product->get_review_count();
$is_in_stock  = $product->is_in_stock();

// Attribute helper: get terms for a taxonomy (e.g. pa_renk).
function wcs_get_product_terms_labels( $product_id, $taxonomy ) {
	$terms = get_the_terms( $product_id, $taxonomy );

	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return array();
	}

	return array_map(
		static function ( $term ) {
			return array(
				'slug' => $term->slug,
				'name' => $term->name,
			);
		},
		$terms
	);
}

// Color badge (pa_renk).
$color_terms = wcs_get_product_terms_labels( $product_id, 'pa_renk' );
$primary_color = $color_terms ? $color_terms[0]['name'] : '';

// Usage areas (pa_kullanim-amaci) for icon row.
$usage_terms = wcs_get_product_terms_labels( $product_id, 'pa_kullanim-amaci' );

// Other attributes for tag pills.
$pill_taxonomies = array(
	'pa_mukavemet',
	'pa_ip-kalinligi',
	'pa_goz-araligi',
	'pa_en-boy-orani',
);

$pill_terms = array();

foreach ( $pill_taxonomies as $pill_tax ) {
	$terms = wcs_get_product_terms_labels( $product_id, $pill_tax );

	if ( ! empty( $terms ) ) {
		$pill_terms = array_merge( $pill_terms, $terms );
	}
}

// Pricing & discount.
$price_html       = $product->get_price_html();
$regular_price    = (float) $product->get_regular_price();
$sale_price       = (float) $product->get_sale_price();
$has_sale         = $sale_price && $sale_price < $regular_price;
$discount_percent = 0;

if ( $has_sale && $regular_price > 0 ) {
	$discount_percent = round( ( ( $regular_price - $sale_price ) / $regular_price ) * 100 );
}

// Variation cards (for variable products).
$variation_cards = array();

if ( $product->is_type( 'variable' ) ) {
	$children = $product->get_children();

	foreach ( $children as $child_id ) {
		$variation = wc_get_product( $child_id );

		if ( ! $variation instanceof WC_Product_Variation ) {
			continue;
		}

		if ( ! $variation->is_purchasable() || ! $variation->is_in_stock() ) {
			continue;
		}

		$attrs = $variation->get_attributes();
		$label_parts = array();

		foreach ( $attrs as $attr_key => $attr_val ) {
			if ( ! $attr_val ) {
				continue;
			}

			$clean_key = str_replace( 'attribute_pa_', '', $attr_key );
			$term      = get_term_by( 'slug', $attr_val, 'pa_' . $clean_key );

			if ( $term && ! is_wp_error( $term ) ) {
				$label_parts[] = $term->name;
			}
		}

		$label = implode( ' • ', $label_parts );

		$var_regular = (float) $variation->get_regular_price();
		$var_sale    = (float) $variation->get_sale_price();
		$var_has_sale = $var_sale && $var_sale < $var_regular;
		$var_discount = 0;

		if ( $var_has_sale && $var_regular > 0 ) {
			$var_discount = round( ( ( $var_regular - $var_sale ) / $var_regular ) * 100 );
		}

		$variation_cards[] = array(
			'id'              => $child_id,
			'label'           => $label ?: sprintf( '#%d', $child_id ),
			'price_html'      => $variation->get_price_html(),
			'regular_price'   => $var_regular,
			'sale_price'      => $var_sale,
			'discount'        => $var_discount,
		);
	}
}
?>

<section class="wcs-product-card" aria-labelledby="wcs-product-card-title">
	<header class="wcs-product-card__top">
		<div class="wcs-product-card__chip-row">
			<?php if ( $primary_color ) : ?>
				<span class="wcs-product-card__chip wcs-product-card__chip--muted">
					<?php echo esc_html( $primary_color ); ?>
				</span>
			<?php endif; ?>

			<?php if ( $is_in_stock ) : ?>
				<span class="wcs-product-card__chip wcs-product-card__chip--success">
					<?php esc_html_e( 'Stokta Var', 'woocommerce-store-child' ); ?>
				</span>
			<?php else : ?>
				<span class="wcs-product-card__chip wcs-product-card__chip--danger">
					<?php esc_html_e( 'Stokta Yok', 'woocommerce-store-child' ); ?>
				</span>
			<?php endif; ?>
		</div>

		<button class="wcs-product-card__fav" type="button" aria-label="<?php esc_attr_e( 'Favorilere ekle', 'woocommerce-store-child' ); ?>">
			<i class="bi bi-heart"></i>
		</button>
	</header>

	<div class="wcs-product-card__body">
		<div class="wcs-product-card__media">
			<div class="wcs-product-card__media-main">
				<?php
				/**
				 * Use WooCommerce default gallery rendering.
				 */
				do_action( 'woocommerce_before_single_product_summary' );
				?>
			</div>

			<?php if ( ! empty( $color_terms ) ) : ?>
				<div class="wcs-product-card__color-dots" aria-label="<?php esc_attr_e( 'Renk seçenekleri', 'woocommerce-store-child' ); ?>">
					<?php foreach ( $color_terms as $term ) : ?>
						<span class="wcs-product-card__color-dot" data-color-slug="<?php echo esc_attr( $term['slug'] ); ?>">
							<span class="wcs-product-card__color-dot-inner"></span>
						</span>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>

		<div class="wcs-product-card__content">
			<h1 id="wcs-product-card-title" class="wcs-product-card__title">
				<?php echo esc_html( $title ); ?>
			</h1>

			<?php if ( $average > 0 ) : ?>
				<div class="wcs-product-card__rating">
					<div class="wcs-product-card__rating-stars">
						<?php echo wc_get_rating_html( $average, $rating_count ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</div>
					<span class="wcs-product-card__rating-score">
						<?php echo esc_html( number_format_i18n( $average, 1 ) ); ?>
					</span>
					<?php if ( $review_count > 0 ) : ?>
						<span class="wcs-product-card__rating-count">
							<?php
							/* translators: %d: review count */
							printf( esc_html__( '(%d değerlendirme)', 'woocommerce-store-child' ), absint( $review_count ) );
							?>
						</span>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<?php if ( $product->get_short_description() ) : ?>
				<div class="wcs-product-card__excerpt">
					<?php echo wp_kses_post( wc_format_content( $product->get_short_description() ) ); ?>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $variation_cards ) ) : ?>
				<section class="wcs-product-card__variations" aria-label="<?php esc_attr_e( 'Boyut seçenekleri', 'woocommerce-store-child' ); ?>">
					<h2 class="wcs-product-card__section-title">
						<?php esc_html_e( 'Boyut Seçenekleri', 'woocommerce-store-child' ); ?>
					</h2>

					<div class="wcs-product-card__variation-grid">
						<?php foreach ( $variation_cards as $var_card ) : ?>
							<div class="wcs-product-card__variation" data-variation-id="<?php echo esc_attr( $var_card['id'] ); ?>">
								<div class="wcs-product-card__variation-header">
									<span class="wcs-product-card__variation-label">
										<?php echo esc_html( $var_card['label'] ); ?>
									</span>

									<?php if ( $var_card['discount'] > 0 ) : ?>
										<span class="wcs-product-card__badge-discount">
											-<?php echo esc_html( $var_card['discount'] ); ?>%
										</span>
									<?php endif; ?>
								</div>

								<div class="wcs-product-card__variation-price">
									<?php echo wp_kses_post( $var_card['price_html'] ); ?>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				</section>
			<?php endif; ?>

			<?php if ( ! empty( $usage_terms ) ) : ?>
				<section class="wcs-product-card__usage" aria-label="<?php esc_attr_e( 'Kullanım alanları', 'woocommerce-store-child' ); ?>">
					<h2 class="wcs-product-card__section-title">
						<?php esc_html_e( 'Kullanım Alanları', 'woocommerce-store-child' ); ?>
					</h2>
					<div class="wcs-product-card__usage-grid">
						<?php foreach ( $usage_terms as $term ) : ?>
							<div class="wcs-product-card__usage-item">
								<div class="wcs-product-card__usage-icon">
									<i class="bi bi-shield-check" aria-hidden="true"></i>
								</div>
								<span class="wcs-product-card__usage-label">
									<?php echo esc_html( $term['name'] ); ?>
								</span>
							</div>
						<?php endforeach; ?>
					</div>
				</section>
			<?php endif; ?>

			<?php if ( ! empty( $pill_terms ) ) : ?>
				<section class="wcs-product-card__tags" aria-label="<?php esc_attr_e( 'Öne çıkan özellikler', 'woocommerce-store-child' ); ?>">
					<div class="wcs-product-card__tag-list">
						<?php foreach ( $pill_terms as $pill ) : ?>
							<span class="wcs-product-card__tag-pill">
								<?php echo esc_html( $pill['name'] ); ?>
							</span>
						<?php endforeach; ?>
					</div>
				</section>
			<?php endif; ?>

			<footer class="wcs-product-card__footer">
				<div class="wcs-product-card__price-block">
					<?php if ( $has_sale && $discount_percent > 0 && $regular_price > 0 ) : ?>
						<div class="wcs-product-card__price-main">
							<span class="wcs-product-card__price-current">
								<?php echo wp_kses_post( wc_price( $sale_price ) ); ?>
							</span>
							<span class="wcs-product-card__price-old">
								<?php echo wp_kses_post( wc_price( $regular_price ) ); ?>
							</span>
						</div>
						<div class="wcs-product-card__price-meta">
							<span class="wcs-product-card__badge-discount">
								-<?php echo esc_html( $discount_percent ); ?>%
							</span>
						</div>
					<?php else : ?>
						<div class="wcs-product-card__price-main">
							<span class="wcs-product-card__price-current">
								<?php echo wp_kses_post( $price_html ); ?>
							</span>
						</div>
					<?php endif; ?>
				</div>

				<div class="wcs-product-card__cta-block">
					<?php
					/**
					 * Calculator and add to cart form.
					 *
					 * We keep default hooks so existing JS keeps working.
					 */
					do_action( 'woocommerce_before_add_to_cart_form' );
					do_action( 'woocommerce_single_product_summary' );
					?>
				</div>
			</footer>
		</div>
	</div>
</section>

