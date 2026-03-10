<?php
/**
 * Custom product card template.
 *
 * @package WooCommerce\Templates
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( empty( $product ) || ! $product->is_visible() ) {
    return;
}

$filterable_attributes = function_exists( 'wcs_get_filterable_attribute_definitions' )
    ? wcs_get_filterable_attribute_definitions()
    : array();
?>
<li <?php wc_product_class( 'wcs-product-card', $product ); ?>>
    <article class="wcs-product-card__inner">
        <a class="wcs-product-card__image-link" href="<?php the_permalink(); ?>" aria-label="<?php the_title_attribute(); ?>">
            <?php do_action( 'woocommerce_before_shop_loop_item_title' ); ?>
        </a>

        <div class="wcs-product-card__content">
            <h2 class="woocommerce-loop-product__title wcs-product-card__title">
                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
            </h2>

            <div class="wcs-product-card__price">
                <?php do_action( 'woocommerce_after_shop_loop_item_title' ); ?>
            </div>

            <?php if ( ! empty( $filterable_attributes ) ) : ?>
                <ul class="wcs-product-card__attributes" aria-label="<?php esc_attr_e( 'Product specifications', 'woocommerce-store-child' ); ?>">
                    <?php foreach ( $filterable_attributes as $attribute_slug => $attribute_config ) : ?>
                        <?php
                        $taxonomy = 'pa_' . $attribute_slug;

                        if ( ! taxonomy_exists( $taxonomy ) ) {
                            continue;
                        }

                        $terms = wc_get_product_terms(
                            $product->get_id(),
                            $taxonomy,
                            array(
                                'fields' => 'names',
                            )
                        );

                        if ( empty( $terms ) ) {
                            continue;
                        }
                        ?>
                        <li>
                            <span class="wcs-product-card__attr-label"><?php echo esc_html( $attribute_config['label'] ); ?>:</span>
                            <span class="wcs-product-card__attr-value"><?php echo esc_html( implode( ', ', $terms ) ); ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <div class="wcs-product-card__actions">
                <?php do_action( 'woocommerce_after_shop_loop_item' ); ?>
            </div>
        </div>
    </article>
</li>
