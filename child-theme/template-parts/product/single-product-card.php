<?php
/**
 * Single Product Card — Karaca File Child Theme.
 * Sol: Galeri | Sağ: Başlık, puan, açıklama, özellikler, varyasyonlar, fiyat, güven, WA
 * @package WooCommerce_Store_Child
 */
defined( 'ABSPATH' ) || exit;
global $product;
if ( ! $product instanceof WC_Product ) return;

$product_id  = $product->get_id();
$is_simple   = $product->is_type( 'simple' );
$is_variable = $product->is_type( 'variable' );
$m2_enabled  = function_exists( 'wcs_is_custom_measure_product' ) ? wcs_is_custom_measure_product( $product ) : false;

$title        = get_the_title( $product_id );
$rating_count = $product->get_rating_count();
$average      = $product->get_average_rating();
$review_count = $product->get_review_count();
$is_in_stock  = $product->is_in_stock();
$sku          = $product->get_sku();

$categories = get_the_terms( $product_id, 'product_cat' );
$cat_name   = $cat_url = '';
if ( $categories && ! is_wp_error( $categories ) ) {
    $cat      = $categories[0];
    $cat_name = $cat->name;
    $link     = get_term_link( $cat );
    $cat_url  = is_wp_error( $link ) ? '' : $link;
}

if ( ! function_exists( 'wcs_get_product_terms_labels' ) ) {
    function wcs_get_product_terms_labels( $pid, $taxonomy ) {
        $terms = get_the_terms( $pid, $taxonomy );
        if ( is_wp_error( $terms ) || empty( $terms ) ) return array();
        return array_map( fn( $t ) => array( 'slug' => $t->slug, 'name' => $t->name ), $terms );
    }
}

$color_terms   = wcs_get_product_terms_labels( $product_id, 'pa_renk' );
$primary_color = $color_terms ? $color_terms[0]['name'] : '';
$usage_terms   = wcs_get_product_terms_labels( $product_id, 'pa_kullanim-amaci' );

$spec_defs = array(
    'pa_mukavemet'    => array( 'bi-lightning-charge', __( 'Mukavemet',    'woocommerce-store-child' ) ),
    'pa_ip-kalinligi' => array( 'bi-rulers',           __( 'İp Kalınlığı', 'woocommerce-store-child' ) ),
    'pa_goz-araligi'  => array( 'bi-grid-3x3',         __( 'Göz Aralığı',  'woocommerce-store-child' ) ),
    'pa_en-boy-orani' => array( 'bi-aspect-ratio',     __( 'En/Boy',        'woocommerce-store-child' ) ),
);
$spec_rows = array();
$pill_terms = array();
foreach ( $spec_defs as $tax => $meta ) {
    $terms = wcs_get_product_terms_labels( $product_id, $tax );
    if ( ! empty( $terms ) ) {
        $spec_rows[]  = array( 'icon' => $meta[0], 'label' => $meta[1], 'value' => implode( ', ', array_column( $terms, 'name' ) ) );
        $pill_terms   = array_merge( $pill_terms, $terms );
    }
}

$price_html         = $product->get_price_html();
$regular_price      = (float) $product->get_regular_price();
$sale_price         = (float) $product->get_sale_price();
$has_sale           = $sale_price && $sale_price < $regular_price;
$discount_percent   = ( $has_sale && $regular_price > 0 ) ? round( ( ( $regular_price - $sale_price ) / $regular_price ) * 100 ) : 0;
$unit_price_display = wc_get_price_to_display( $product );

$variation_cards = array();
if ( $is_variable ) {
    foreach ( $product->get_children() as $child_id ) {
        $v = wc_get_product( $child_id );
        if ( ! $v instanceof WC_Product_Variation || ! $v->is_purchasable() || ! $v->is_in_stock() ) continue;
        $attrs = $v->get_attributes();
        $na    = array();
        foreach ( $attrs as $k => $val ) { if ( $val ) $na[ wc_variation_attribute_name( $k ) ] = $val; }
        $lp = array_filter( array(
            $v->get_attribute( 'pa_olcu' ) ?: $v->get_attribute( 'pa_en-boy-orani' ),
            $v->get_attribute( 'pa_ip-kalinligi' ),
            $v->get_attribute( 'pa_goz-araligi' ),
            $v->get_attribute( 'pa_renk' ),
        ) );
        $label = implode( ' · ', $lp );
        if ( '' === $label ) {
            $fb = array();
            foreach ( $attrs as $ak => $av ) {
                if ( ! $av ) continue;
                $t = get_term_by( 'slug', $av, 'pa_' . str_replace( 'attribute_pa_', '', $ak ) );
                if ( $t && ! is_wp_error( $t ) ) $fb[] = $t->name;
            }
            $label = implode( ' · ', $fb );
        }
        $vr = (float) $v->get_regular_price();
        $vs = (float) $v->get_sale_price();
        $vd = ( $vs && $vs < $vr && $vr > 0 ) ? round( ( ( $vr - $vs ) / $vr ) * 100 ) : 0;
        $variation_cards[] = array( 'id' => $child_id, 'label' => $label ?: "#$child_id", 'price_html' => $v->get_price_html(), 'discount' => $vd, 'attributes' => $na );
    }
}

// Spec card: göz aralığı, ip kalınlığı, regi (renk) — ürün/varyasyon verisine göre
$spec_card_goz  = '';
$spec_card_ip   = '';
$spec_card_renk = $primary_color;
if ( $is_simple ) {
    $goz_terms = wcs_get_product_terms_labels( $product_id, 'pa_goz-araligi' );
    $ip_terms  = wcs_get_product_terms_labels( $product_id, 'pa_ip-kalinligi' );
    $spec_card_goz = ! empty( $goz_terms ) ? $goz_terms[0]['name'] : '';
    $spec_card_ip  = ! empty( $ip_terms ) ? $ip_terms[0]['name'] : '';
} elseif ( $is_variable && ! empty( $variation_cards ) ) {
    $first_var = wc_get_product( $variation_cards[0]['id'] );
    if ( $first_var instanceof WC_Product_Variation ) {
        $spec_card_goz  = $first_var->get_attribute( 'pa_goz-araligi' ) ?: '';
        $spec_card_ip   = $first_var->get_attribute( 'pa_ip-kalinligi' ) ?: '';
        $spec_card_renk = $first_var->get_attribute( 'pa_renk' ) ?: $spec_card_renk;
    }
}
?>

<div class="wcs-sp-wrap">

    <!-- BREADCRUMB -->
    <nav class="wcs-sp-breadcrumb" aria-label="<?php esc_attr_e( 'Sayfa yolu', 'woocommerce-store-child' ); ?>">
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="wcs-sp-breadcrumb__item">
            <i class="bi bi-house" aria-hidden="true"></i><?php esc_html_e( 'Ana Sayfa', 'woocommerce-store-child' ); ?>
        </a>
        <i class="bi bi-chevron-right wcs-sp-breadcrumb__sep" aria-hidden="true"></i>
        <a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="wcs-sp-breadcrumb__item"><?php esc_html_e( 'Mağaza', 'woocommerce-store-child' ); ?></a>
        <?php if ( $cat_name && $cat_url ) : ?>
            <i class="bi bi-chevron-right wcs-sp-breadcrumb__sep" aria-hidden="true"></i>
            <a href="<?php echo esc_url( $cat_url ); ?>" class="wcs-sp-breadcrumb__item"><?php echo esc_html( $cat_name ); ?></a>
        <?php endif; ?>
        <i class="bi bi-chevron-right wcs-sp-breadcrumb__sep" aria-hidden="true"></i>
        <span class="wcs-sp-breadcrumb__item wcs-sp-breadcrumb__item--current" aria-current="page"><?php echo esc_html( $title ); ?></span>
    </nav>

    <!-- ANA KART -->
    <section class="wcs-sp-card" aria-labelledby="wcs-sp-title">

        <!-- SOL: Galeri -->
        <div class="wcs-sp-card__gallery">
            <div class="wcs-sp-card__gallery-badges">
                <?php if ( $has_sale && $discount_percent > 0 ) : ?>
                    <span class="wcs-sp-badge wcs-sp-badge--sale">-<?php echo absint( $discount_percent ); ?>%</span>
                <?php endif; ?>
                <?php if ( $is_in_stock ) : ?>
                    <span class="wcs-sp-badge wcs-sp-badge--stock"><i class="bi bi-check-circle-fill"></i> <?php esc_html_e( 'Stokta', 'woocommerce-store-child' ); ?></span>
                <?php else : ?>
                    <span class="wcs-sp-badge wcs-sp-badge--nostock"><i class="bi bi-x-circle-fill"></i> <?php esc_html_e( 'Tükendi', 'woocommerce-store-child' ); ?></span>
                <?php endif; ?>
            </div>
            <div class="wcs-sp-card__gallery-inner">
                <?php do_action( 'woocommerce_before_single_product_summary' ); ?>
            </div>
            <?php
            get_template_part( 'template-parts/product/product-spec-card', null, array(
                'spec_goz'  => $spec_card_goz,
                'spec_ip'   => $spec_card_ip,
                'spec_renk' => $spec_card_renk,
                'is_var'    => $is_variable,
            ) );
            ?>
            <?php if ( ! empty( $color_terms ) ) : ?>
                <div class="wcs-sp-card__color-row">
                    <?php foreach ( $color_terms as $ct ) : ?><span class="wcs-sp-card__color-dot" title="<?php echo esc_attr( $ct['name'] ); ?>"><span class="wcs-sp-card__color-dot-inner"></span></span><?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- SAĞ: İçerik -->
        <div class="wcs-sp-card__info">

            <div class="wcs-sp-card__meta-row">
                <?php if ( $cat_name && $cat_url ) : ?>
                    <a href="<?php echo esc_url( $cat_url ); ?>" class="wcs-sp-card__cat-badge"><i class="bi bi-tag"></i> <?php echo esc_html( $cat_name ); ?></a>
                <?php endif; ?>
                <button
                    class="wcs-sp-card__fav wcs-wishlist-btn"
                    type="button"
                    data-wcs-wishlist
                    data-product-id="<?php echo esc_attr( $product_id ); ?>"
                    aria-label="<?php esc_attr_e( 'Favorilere ekle', 'woocommerce-store-child' ); ?>"
                    aria-pressed="false">
                    <i class="bi bi-heart"></i>
                </button>
            </div>

            <h1 id="wcs-sp-title" class="wcs-sp-card__title"><?php echo esc_html( $title ); ?></h1>

            <?php if ( $average > 0 ) : ?>
                <div class="wcs-sp-card__rating">
                    <?php echo wc_get_rating_html( $average, $rating_count ); // phpcs:ignore ?>
                    <span class="wcs-sp-card__rating-num"><?php echo esc_html( number_format_i18n( $average, 1 ) ); ?></span>
                    <?php if ( $review_count > 0 ) : ?>
                        <a href="#reviews" class="wcs-sp-card__rating-link"><?php printf( esc_html__( '%d yorum', 'woocommerce-store-child' ), absint( $review_count ) ); ?></a>
                    <?php endif; ?>
                    <?php if ( $sku ) : ?><span class="wcs-sp-card__sku">SKU: <?php echo esc_html( $sku ); ?></span><?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if ( $product->get_short_description() ) : ?>
                <div class="wcs-sp-card__excerpt"><?php echo wp_kses_post( wc_format_content( $product->get_short_description() ) ); ?></div>
            <?php endif; ?>

            <?php if ( ! empty( $spec_rows ) ) : ?>
                <div class="wcs-sp-card__specs">
                    <?php foreach ( $spec_rows as $spec ) : ?>
                        <div class="wcs-sp-card__spec-row">
                            <span class="wcs-sp-card__spec-icon"><i class="bi <?php echo esc_attr( $spec['icon'] ); ?>"></i></span>
                            <span class="wcs-sp-card__spec-label"><?php echo esc_html( $spec['label'] ); ?></span>
                            <span class="wcs-sp-card__spec-value"><?php echo esc_html( $spec['value'] ); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if ( ! empty( $usage_terms ) ) : ?>
                <div class="wcs-sp-card__usage-pills">
                    <?php foreach ( $usage_terms as $ut ) : ?>
                        <span class="wcs-sp-card__usage-pill"><i class="bi bi-shield-check"></i> <?php echo esc_html( $ut['name'] ); ?></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if ( ! empty( $pill_terms ) ) : ?>
                <div class="wcs-sp-card__attr-pills">
                    <?php foreach ( $pill_terms as $pt ) : ?><span class="wcs-sp-card__attr-pill"><?php echo esc_html( $pt['name'] ); ?></span><?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Fiyat blok -->
            <div class="wcs-sp-card__price-block" data-wcs-unit-price="<?php echo esc_attr( $unit_price_display ); ?>">
                <div class="wcs-sp-card__price-row">
                    <?php if ( $has_sale && $discount_percent > 0 ) : ?>
                        <span class="wcs-sp-card__price-current"><?php echo wp_kses_post( wc_price( $sale_price ) ); ?></span>
                        <span class="wcs-sp-card__price-old"><?php echo wp_kses_post( wc_price( $regular_price ) ); ?></span>
                        <span class="wcs-sp-card__price-badge">-<?php echo absint( $discount_percent ); ?>%</span>
                    <?php else : ?>
                        <span class="wcs-sp-card__price-current"><?php echo wp_kses_post( $price_html ); ?></span>
                    <?php endif; ?>
                </div>
                <div class="wcs-sp-card__price-total">
                    <span class="wcs-sp-card__price-total-label"><?php esc_html_e( 'Toplam:', 'woocommerce-store-child' ); ?></span>
                    <span class="wcs-sp-card__price-total-value"><?php echo wp_kses_post( wc_price( $unit_price_display ) ); ?></span>
                </div>
            </div>

            <!-- Varyasyon kartları -->
            <?php if ( $is_variable && ! empty( $variation_cards ) ) : ?>
                <div class="wcs-sp-card__variations">
                    <h2 class="wcs-sp-card__section-title"><i class="bi bi-collection"></i> <?php esc_html_e( 'Boyut & Özellik Seçin', 'woocommerce-store-child' ); ?></h2>
                    <div class="wcs-sp-card__var-grid">
                        <?php foreach ( $variation_cards as $i => $vc ) : ?>
                            <div class="wcs-sp-card__var-card<?php echo $i === 0 ? ' is-active' : ''; ?>"
                                 tabindex="0" role="radio"
                                 aria-checked="<?php echo $i === 0 ? 'true' : 'false'; ?>"
                                 data-variation-id="<?php echo absint( $vc['id'] ); ?>"
                                 data-attributes="<?php echo esc_attr( wp_json_encode( $vc['attributes'] ) ); ?>">
                                <div class="wcs-sp-card__var-header">
                                    <span class="wcs-sp-card__var-label"><?php echo esc_html( $vc['label'] ); ?></span>
                                    <?php if ( $vc['discount'] > 0 ) : ?><span class="wcs-sp-card__var-badge">-<?php echo absint( $vc['discount'] ); ?>%</span><?php endif; ?>
                                </div>
                                <div class="wcs-sp-card__var-price"><?php echo wp_kses_post( $vc['price_html'] ); ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- m² toggle -->
            <?php if ( $m2_enabled ) : ?>
                <button class="wcs-sp-calc-toggle wcs-calculator-toggle" type="button">
                    <i class="bi bi-calculator"></i>
                    <?php esc_html_e( 'Özel Ölçü (m² Hesapla)', 'woocommerce-store-child' ); ?>
                    <i class="bi bi-chevron-down wcs-sp-calc-toggle__chevron"></i>
                </button>
            <?php endif; ?>

            <!-- WooCommerce add to cart form -->
            <div class="wcs-sp-card__woo-form wcs-sp-atc-section">
                <?php if ( function_exists( 'woocommerce_template_single_add_to_cart' ) ) woocommerce_template_single_add_to_cart(); ?>
            </div>

            <!-- WhatsApp CTA -->
            <a href="https://wa.me/908503802006?text=<?php echo rawurlencode( sprintf( __( 'Merhaba, "%s" ürünü hakkında bilgi almak istiyorum.', 'woocommerce-store-child' ), $title ) ); ?>"
               target="_blank" rel="noopener noreferrer" class="wcs-sp-card__wa-btn">
                <i class="bi bi-whatsapp"></i>
                <?php esc_html_e( 'WhatsApp ile Bilgi Al / Sipariş Ver', 'woocommerce-store-child' ); ?>
            </a>

            <!-- Güven bandı -->
            <div class="wcs-sp-card__trust">
                <div class="wcs-sp-card__trust-item"><i class="bi bi-shield-fill-check"></i><span><?php esc_html_e( 'CE Belgeli', 'woocommerce-store-child' ); ?></span></div>
                <div class="wcs-sp-card__trust-item"><i class="bi bi-truck"></i><span><?php esc_html_e( 'Ücretsiz Kargo', 'woocommerce-store-child' ); ?></span></div>
                <div class="wcs-sp-card__trust-item"><i class="bi bi-arrow-return-left"></i><span><?php esc_html_e( '30 Gün İade', 'woocommerce-store-child' ); ?></span></div>
                <div class="wcs-sp-card__trust-item"><i class="bi bi-headset"></i><span><?php esc_html_e( 'Teknik Destek', 'woocommerce-store-child' ); ?></span></div>
            </div>

            <!-- Teslimat bilgisi -->
            <div class="wcs-sp-card__delivery">
                <div class="wcs-sp-card__delivery-row">
                    <i class="bi bi-clock"></i>
                    <div><strong><?php esc_html_e( 'Stok Ürün', 'woocommerce-store-child' ); ?></strong><span><?php esc_html_e( '1–3 iş günü teslimat', 'woocommerce-store-child' ); ?></span></div>
                </div>
                <div class="wcs-sp-card__delivery-row">
                    <i class="bi bi-rulers"></i>
                    <div><strong><?php esc_html_e( 'Özel Ölçü', 'woocommerce-store-child' ); ?></strong><span><?php esc_html_e( '3–5 iş günü üretim + kargo', 'woocommerce-store-child' ); ?></span></div>
                </div>
            </div>

        </div><!-- /.wcs-sp-card__info -->

    </section><!-- /.wcs-sp-card -->

</div><!-- /.wcs-sp-wrap -->

<div class="wcs-sticky-atc">
    <div class="wcs-sticky-atc__inner">
        <div class="wcs-sticky-atc__meta">
            <span class="wcs-sticky-atc__title"><?php echo esc_html( $title ); ?></span>
            <span class="wcs-sticky-atc__price"><?php echo wp_kses_post( $price_html ); ?></span>
        </div>
        <button type="button" class="wcs-sticky-atc__btn" data-wcs-sticky-atc-button>
            <i class="bi bi-cart-plus"></i>
            <?php esc_html_e( 'Sepete Ekle', 'woocommerce-store-child' ); ?>
        </button>
    </div>
</div>
