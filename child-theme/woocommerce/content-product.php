<?php
/**
 * Product card loop template.
 *
 * Overrides: woocommerce/templates/content-product.php
 * Path:      child-theme/woocommerce/content-product.php
 *
 * @package WooCommerceStoreChild
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( ! $product || ! $product->is_visible() ) {
	return;
}

/* ── Badges ─────────────────────────────────── */
$is_new   = ( time() - strtotime( $product->get_date_created() ) ) < ( 30 * DAY_IN_SECONDS );
$in_stock = $product->is_in_stock();
$low_stock = $in_stock && $product->get_stock_quantity() !== null && $product->get_stock_quantity() <= 5;

/* ── Category label ──────────────────────────── */
$terms    = get_the_terms( $product->get_id(), 'product_cat' );
$cat_name = ( $terms && ! is_wp_error( $terms ) ) ? esc_html( $terms[0]->name ) : '';

/* ── Specs (custom fields) ───────────────────── */
$layers  = get_post_meta( $product->get_id(), '_wcs_layers',   true );
$mesh    = get_post_meta( $product->get_id(), '_wcs_mesh',     true );
$std     = get_post_meta( $product->get_id(), '_wcs_standard', true );
$extra   = get_post_meta( $product->get_id(), '_wcs_extra',    true );

/* ── Price ───────────────────────────────────── */
$price_raw = (float) wc_get_price_to_display( $product );
$price_fmt = number_format( $price_raw, 2, ',', '.' );
$currency  = get_woocommerce_currency_symbol();
?>

<div <?php wc_product_class( 'wcs-card', $product ); ?>>

	<?php /* Corner-cut pseudo element handled in CSS */ ?>

	<!-- ── Badges ── -->
	<div class="wcs-card__badges" aria-hidden="true">
		<?php if ( $is_new ) : ?>
			<span class="wcs-card__badge wcs-card__badge--new">
				<i class="bi bi-lightning-fill"></i>
				<?php esc_html_e( 'Yeni', 'woocommerce-store-child' ); ?>
			</span>
		<?php endif; ?>

		<?php if ( $low_stock ) : ?>
			<span class="wcs-card__badge wcs-card__badge--low">
				<i class="bi bi-exclamation-triangle-fill"></i>
				<?php esc_html_e( 'Son Stok', 'woocommerce-store-child' ); ?>
			</span>
		<?php elseif ( $in_stock ) : ?>
			<span class="wcs-card__badge wcs-card__badge--stock">
				<i class="bi bi-check-circle-fill"></i>
				<?php esc_html_e( 'Stokta', 'woocommerce-store-child' ); ?>
			</span>
		<?php endif; ?>
	</div>

	<!-- ── Thumbnail ── -->
	<a href="<?php the_permalink(); ?>" class="wcs-card__img" tabindex="-1" aria-hidden="true">
		<?php if ( has_post_thumbnail() ) : ?>
			<?php echo woocommerce_get_product_thumbnail( 'woocommerce_thumbnail' ); ?>
		<?php else : ?>
			<div class="wcs-card__img-placeholder">
				<i class="bi bi-shield-fill-check wcs-card__img-icon" aria-hidden="true"></i>
			</div>
		<?php endif; ?>
		<span class="wcs-card__m2-tag"><?php esc_html_e( 'm² bazlı fiyat', 'woocommerce-store-child' ); ?></span>
	</a>

	<!-- ── Body ── -->
	<div class="wcs-card__body">

		<?php if ( $cat_name ) : ?>
			<span class="wcs-card__cat"><?php echo esc_html( $cat_name ); ?></span>
		<?php endif; ?>

		<h2 class="wcs-card__name">
			<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
		</h2>

		<!-- Specs -->
		<?php if ( $layers || $mesh || $std || $extra ) : ?>
			<div class="wcs-card__specs">
				<?php if ( $layers ) : ?>
					<span class="wcs-card__spec">
						<i class="bi bi-layers" aria-hidden="true"></i>
						<?php echo esc_html( $layers ); ?>
					</span>
				<?php endif; ?>
				<?php if ( $mesh ) : ?>
					<span class="wcs-card__spec">
						<i class="bi bi-grid-3x3" aria-hidden="true"></i>
						<?php echo esc_html( $mesh ); ?>
					</span>
				<?php endif; ?>
				<?php if ( $std ) : ?>
					<span class="wcs-card__spec">
						<i class="bi bi-award" aria-hidden="true"></i>
						<?php echo esc_html( $std ); ?>
					</span>
				<?php endif; ?>
				<?php if ( $extra ) : ?>
					<span class="wcs-card__spec">
						<i class="bi bi-lightning-charge" aria-hidden="true"></i>
						<?php echo esc_html( $extra ); ?>
					</span>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<div class="wcs-card__hr"></div>

		<!-- Footer: price + CTA -->
		<div class="wcs-card__footer">
			<div class="wcs-card__pricing">
				<div class="wcs-card__price-lbl">
					<?php esc_html_e( 'Fiyat', 'woocommerce-store-child' ); ?>
				</div>
				<div class="wcs-card__price">
					<span class="wcs-card__price-cur"><?php echo esc_html( $currency ); ?></span>
					<?php echo esc_html( $price_fmt ); ?>
					<span class="wcs-card__price-unit">/ m²</span>
				</div>
			</div>

			<a href="<?php the_permalink(); ?>" class="wcs-card__cta">
				<?php esc_html_e( 'İncele', 'woocommerce-store-child' ); ?>
				<i class="bi bi-arrow-right" aria-hidden="true"></i>
			</a>
		</div>

	</div><!-- /.wcs-card__body -->

	<span class="wcs-card__line" aria-hidden="true"></span>
</div>