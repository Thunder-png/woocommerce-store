<?php
/**
 * Hero component for product archive pages.
 */

defined( 'ABSPATH' ) || exit;

if ( defined( 'WCS_HERO_ALREADY_RENDERED' ) ) {
    return;
}

define( 'WCS_HERO_ALREADY_RENDERED', true );

$hero_stats = array(
    array(
        'value' => '500+',
        'label' => __( 'Tamamlanan Proje', 'woocommerce-store-child' ),
    ),
    array(
        'value' => 'EN 1263-1',
        'label' => __( 'Sertifikasyon', 'woocommerce-store-child' ),
    ),
    array(
        'value' => '48 Saat',
        'label' => __( 'Hızlı Termin', 'woocommerce-store-child' ),
    ),
);

$ticker_list = array(
    __( 'Güvenlik Filesi Sistemleri', 'woocommerce-store-child' ),
    __( 'Endüstriyel Koruma Çözümleri', 'woocommerce-store-child' ),
    __( 'Şantiye ve Yapı Güvenliği', 'woocommerce-store-child' ),
    __( 'UV Dayanımlı Ağ Teknolojisi', 'woocommerce-store-child' ),
    __( 'Profesyonel Keşif ve Montaj', 'woocommerce-store-child' ),
    __( 'Toplu Proje Desteği', 'woocommerce-store-child' ),
);
?>

<section class="wcs-hero" aria-labelledby="wcs-hero-title">
    <canvas id="wcs-hero-canvas" class="wcs-hero__canvas" aria-hidden="true"></canvas>
    <div class="wcs-hero__vignette" aria-hidden="true"></div>

    <div class="wcs-hero__inner">
        <div class="wcs-hero__left">
            <div class="wcs-hero__tag-row">
                <span class="wcs-hero__tag">
                    <span class="wcs-hero__tag-dot" aria-hidden="true"></span>
                    <span><?php esc_html_e( 'Stoktan Hızlı Sevk', 'woocommerce-store-child' ); ?></span>
                </span>
                <span class="wcs-hero__cert"><?php esc_html_e( 'EN 1263-1 · CE Belgeli Üretim', 'woocommerce-store-child' ); ?></span>
            </div>

            <h1 id="wcs-hero-title" class="wcs-hero__title">
                <span class="wcs-hero__title-line"><?php esc_html_e( 'Yüksekte', 'woocommerce-store-child' ); ?></span>
                <span class="wcs-hero__title-line wcs-hero__title-red"><?php esc_html_e( 'Güvenlik', 'woocommerce-store-child' ); ?></span>
                <span class="wcs-hero__title-line wcs-hero__title-outline"><?php esc_html_e( 'Sistemleri', 'woocommerce-store-child' ); ?></span>
            </h1>

            <p class="wcs-hero__text">
                <?php esc_html_e( 'İnşaat, endüstri ve spor alanları için yüksek dayanımlı güvenlik filesi çözümleri. Brand standartlarımızla üretilen sistemler; hızlı termin, yerinde keşif ve profesyonel montaj ile sunulur.', 'woocommerce-store-child' ); ?>
            </p>

            <div class="wcs-hero__actions">
                <a class="wcs-hero__btn wcs-hero__btn--primary" href="#products-grid"><i class="bi bi-grid" aria-hidden="true"></i> <?php esc_html_e( 'Ürünleri İncele', 'woocommerce-store-child' ); ?></a>
                <a class="wcs-hero__btn wcs-hero__btn--link" href="#products-grid"><i class="bi bi-file-earmark-text" aria-hidden="true"></i> <?php esc_html_e( 'Teknik Bilgi Al', 'woocommerce-store-child' ); ?></a>
            </div>

            <ul class="wcs-hero__stats" aria-label="<?php esc_attr_e( 'Öne çıkan metrikler', 'woocommerce-store-child' ); ?>">
                <?php foreach ( $hero_stats as $stat ) : ?>
                    <li class="wcs-hero__stat">
                        <span class="wcs-hero__stat-value"><?php echo esc_html( $stat['value'] ); ?></span>
                        <span class="wcs-hero__stat-label"><?php echo esc_html( $stat['label'] ); ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <aside class="wcs-hero__right" aria-label="<?php esc_attr_e( 'Teknik görsel panel', 'woocommerce-store-child' ); ?>">
            <div class="wcs-hero__net-card">
                <svg class="wcs-hero__net-svg" viewBox="0 0 360 360" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid slice" aria-hidden="true">
                    <defs>
                        <pattern id="wcs-net-pattern" x="0" y="0" width="30" height="30" patternUnits="userSpaceOnUse">
                            <path d="M15 0 L30 15 L15 30 L0 15 Z" fill="none" stroke="rgba(226,0,13,0.38)" stroke-width="0.8" />
                            <path d="M15 3 L27 15 L15 27 L3 15 Z" fill="none" stroke="rgba(248,248,248,0.08)" stroke-width="0.5" />
                        </pattern>
                    </defs>
                    <rect width="360" height="360" fill="#111620" />
                    <rect width="360" height="360" fill="url(#wcs-net-pattern)" />
                </svg>

                <div class="wcs-hero__load">
                    <p class="wcs-hero__load-value">150</p>
                    <p class="wcs-hero__load-unit">kN / m²</p>
                    <p class="wcs-hero__load-label"><?php esc_html_e( 'Maksimum Yük Kapasitesi', 'woocommerce-store-child' ); ?></p>
                </div>
            </div>

            <div class="wcs-hero__spec-card">
                <p><?php esc_html_e( 'Malzeme', 'woocommerce-store-child' ); ?> <strong>HDPE / PP</strong></p>
                <p><?php esc_html_e( 'Ağ Göz Boyutu', 'woocommerce-store-child' ); ?> <strong>60 x 60 mm</strong></p>
                <p><?php esc_html_e( 'UV Dayanım', 'woocommerce-store-child' ); ?> <strong>+120 Ay</strong></p>
            </div>

            <span class="wcs-hero__pill wcs-hero__pill--1"><?php esc_html_e( 'Yüksek Mukavemet', 'woocommerce-store-child' ); ?></span>
            <span class="wcs-hero__pill wcs-hero__pill--2"><?php esc_html_e( 'Sahada Hızlı Montaj', 'woocommerce-store-child' ); ?></span>
        </aside>
    </div>

    <div class="wcs-hero__ticker" aria-hidden="true">
        <div class="wcs-hero__ticker-track">
            <?php for ( $i = 0; $i < 2; $i++ ) : ?>
                <?php foreach ( $ticker_list as $item ) : ?>
                    <span class="wcs-hero__ticker-item"><?php echo esc_html( $item ); ?></span>
                <?php endforeach; ?>
            <?php endfor; ?>
        </div>
    </div>
</section>
