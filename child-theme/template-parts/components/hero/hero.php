<?php
/**
 * Hero component for product archive pages.
 */

defined( 'ABSPATH' ) || exit;

$hero_stats  = array(
    array(
        'value' => '500+',
        'label' => __( 'Proje Tamamlandi', 'woocommerce-store-child' ),
    ),
    array(
        'value' => '20+',
        'label' => __( 'Yil Deneyim', 'woocommerce-store-child' ),
    ),
    array(
        'value' => '48s',
        'label' => __( 'Hizli Teslimat', 'woocommerce-store-child' ),
    ),
);
$hero_pills  = array(
    __( 'UV Dayanimli', 'woocommerce-store-child' ),
    __( 'Hizli Montaj', 'woocommerce-store-child' ),
);
$ticker_list = array(
    __( 'Guvenlik Filesi', 'woocommerce-store-child' ),
    __( 'EN 1263-1 Standardi', 'woocommerce-store-child' ),
    __( 'CE Belgeli Uretim', 'woocommerce-store-child' ),
    __( 'Proje Bazli Uygulama', 'woocommerce-store-child' ),
    __( 'Profesyonel Montaj', 'woocommerce-store-child' ),
    __( 'Toplu Siparis Destegi', 'woocommerce-store-child' ),
);
$lottie_url  = get_stylesheet_directory_uri() . '/assets/branding/logo/logo-lottie-version.json';
?>

<section class="wcs-hero" aria-labelledby="wcs-hero-title">
    <div class="wcs-hero__vignette" aria-hidden="true"></div>

    <div class="wcs-hero__inner">
        <div class="wcs-hero__left">
            <div class="wcs-hero__tag-row">
                <span class="wcs-hero__tag"><?php esc_html_e( 'Stokta Hazir', 'woocommerce-store-child' ); ?></span>
                <span class="wcs-hero__cert"><?php esc_html_e( 'EN 1263-1 · CE Sertifikali', 'woocommerce-store-child' ); ?></span>
            </div>

            <h1 id="wcs-hero-title" class="wcs-hero__title">
                <span><?php esc_html_e( 'Yuksekte', 'woocommerce-store-child' ); ?></span>
                <span class="wcs-hero__title-red"><?php esc_html_e( 'Guvenlik', 'woocommerce-store-child' ); ?></span>
                <span class="wcs-hero__title-outline"><?php esc_html_e( 'Cozumleri', 'woocommerce-store-child' ); ?></span>
            </h1>

            <p class="wcs-hero__text">
                <?php esc_html_e( 'Insaat, endustri ve spor alanlari icin yuksek dayanimli guvenlik filesi sistemleri. Avrupa standartlarinda uretim, hizli teslimat ve profesyonel montaj destegi.', 'woocommerce-store-child' ); ?>
            </p>

            <div class="wcs-hero__actions">
                <a class="wcs-hero__btn wcs-hero__btn--primary" href="#products-grid"><?php esc_html_e( 'Urunleri Gor', 'woocommerce-store-child' ); ?></a>
                <a class="wcs-hero__btn wcs-hero__btn--link" href="#products-grid"><?php esc_html_e( 'Teknik Katalog', 'woocommerce-store-child' ); ?></a>
            </div>

            <ul class="wcs-hero__stats" aria-label="<?php esc_attr_e( 'One cikan metrikler', 'woocommerce-store-child' ); ?>">
                <?php foreach ( $hero_stats as $stat ) : ?>
                    <li class="wcs-hero__stat">
                        <span class="wcs-hero__stat-value"><?php echo esc_html( $stat['value'] ); ?></span>
                        <span class="wcs-hero__stat-label"><?php echo esc_html( $stat['label'] ); ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <aside class="wcs-hero__right" aria-label="<?php esc_attr_e( 'Teknik kart', 'woocommerce-store-child' ); ?>">
            <div class="wcs-hero__net-card">
                <svg class="wcs-hero__net-svg" viewBox="0 0 360 360" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid slice" aria-hidden="true">
                    <defs>
                        <pattern id="wcs-net-pattern" x="0" y="0" width="30" height="30" patternUnits="userSpaceOnUse">
                            <path d="M15 0 L30 15 L15 30 L0 15 Z" fill="none" stroke="rgba(193,46,55,0.38)" stroke-width="0.8" />
                            <path d="M15 3 L27 15 L15 27 L3 15 Z" fill="none" stroke="rgba(248,248,248,0.08)" stroke-width="0.5" />
                        </pattern>
                    </defs>
                    <rect width="360" height="360" fill="#111620" />
                    <rect width="360" height="360" fill="url(#wcs-net-pattern)" />
                </svg>

                <div class="wcs-hero__load">
                    <p class="wcs-hero__load-value">150</p>
                    <p class="wcs-hero__load-unit">kN / m²</p>
                    <p class="wcs-hero__load-label"><?php esc_html_e( 'Maks. Yuk Kapasitesi', 'woocommerce-store-child' ); ?></p>
                </div>
            </div>

            <div class="wcs-hero__spec-card">
                <p><?php esc_html_e( 'Malzeme', 'woocommerce-store-child' ); ?> <strong>HDPE / PP</strong></p>
                <p><?php esc_html_e( 'Ag Goz Boyutu', 'woocommerce-store-child' ); ?> <strong>60 x 60 mm</strong></p>
                <p><?php esc_html_e( 'Standart', 'woocommerce-store-child' ); ?> <strong>EN 1263-1</strong></p>
            </div>

            <div class="wcs-hero__logo" data-wcs-lottie data-lottie-src="<?php echo esc_url( $lottie_url ); ?>" aria-hidden="true"></div>

            <?php foreach ( $hero_pills as $index => $pill ) : ?>
                <span class="wcs-hero__pill wcs-hero__pill--<?php echo esc_attr( $index + 1 ); ?>"><?php echo esc_html( $pill ); ?></span>
            <?php endforeach; ?>
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
