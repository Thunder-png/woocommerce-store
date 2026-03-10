<?php
/**
 * Hero component for product archive pages — Enhanced UI.
 */

defined( 'ABSPATH' ) || exit;

if ( defined( 'WCS_HERO_ALREADY_RENDERED' ) ) {
	return;
}

define( 'WCS_HERO_ALREADY_RENDERED', true );

$hero_stats = array(
	array(
		'value' => '500',
		'sup'   => '+',
		'label' => __( 'Tamamlanan Proje', 'woocommerce-store-child' ),
	),
	array(
		'value' => '20',
		'sup'   => 'yıl',
		'label' => __( 'Sektör Deneyimi', 'woocommerce-store-child' ),
	),
	array(
		'value' => '48',
		'sup'   => 'sa',
		'label' => __( 'Hızlı Temin', 'woocommerce-store-child' ),
	),
);

$ticker_list = array(
	array( 'icon' => 'bi-shield-check',   'text' => __( 'Güvenlik Filesi Sistemleri', 'woocommerce-store-child' ) ),
	array( 'icon' => 'bi-gear-fill',      'text' => __( 'Endüstriyel Koruma Çözümleri', 'woocommerce-store-child' ) ),
	array( 'icon' => 'bi-building',       'text' => __( 'Şantiye ve Yapı Güvenliği', 'woocommerce-store-child' ) ),
	array( 'icon' => 'bi-sun',            'text' => __( 'UV Dayanımlı Ağ Teknolojisi', 'woocommerce-store-child' ) ),
	array( 'icon' => 'bi-tools',          'text' => __( 'Profesyonel Keşif ve Montaj', 'woocommerce-store-child' ) ),
	array( 'icon' => 'bi-boxes',          'text' => __( 'Toplu Proje Desteği', 'woocommerce-store-child' ) ),
	array( 'icon' => 'bi-patch-check',    'text' => __( 'CE Belgeli Üretim', 'woocommerce-store-child' ) ),
	array( 'icon' => 'bi-award',          'text' => __( 'EN 1263-1 Sertifikalı', 'woocommerce-store-child' ) ),
);

$spec_rows = array(
	array(
		'icon'   => 'bi-layers',
		'key'    => __( 'Malzeme', 'woocommerce-store-child' ),
		'val'    => 'HDPE / Polipropilen',
		'accent' => false,
	),
	array(
		'icon'   => 'bi-grid-3x3',
		'key'    => __( 'Ağ Göz Boyutu', 'woocommerce-store-child' ),
		'val'    => '60 × 60 mm',
		'accent' => true,
	),
	array(
		'icon'   => 'bi-lightning-charge',
		'key'    => __( 'Çekme Dayanımı', 'woocommerce-store-child' ),
		'val'    => '≥ 7,5 kN',
		'accent' => false,
	),
	array(
		'icon'   => 'bi-award',
		'key'    => __( 'Standart', 'woocommerce-store-child' ),
		'val'    => 'EN 1263-1',
		'accent' => true,
	),
);

$pills = array(
	array( 'icon' => 'bi-shield-fill-check', 'text' => __( 'UV Dayanımlı', 'woocommerce-store-child' ) ),
	array( 'icon' => 'bi-lightning-fill',    'text' => __( 'Hızlı Montaj', 'woocommerce-store-child' ) ),
	array( 'icon' => 'bi-patch-check-fill',  'text' => __( 'CE Belgeli', 'woocommerce-store-child' ) ),
);
?>

<section class="wcs-hero" aria-labelledby="wcs-hero-title">
	<canvas id="wcs-hero-canvas" class="wcs-hero__canvas" aria-hidden="true"></canvas>
	<div class="wcs-hero__vignette" aria-hidden="true"></div>

	<div class="wcs-hero__inner">

		<!-- ── LEFT ── -->
		<div class="wcs-hero__left">

			<div class="wcs-hero__tag-row">
				<span class="wcs-hero__tag">
					<span class="wcs-hero__tag-dot" aria-hidden="true"></span>
					<?php esc_html_e( 'Stoktan Hızlı Sevk', 'woocommerce-store-child' ); ?>
				</span>
				<span class="wcs-hero__cert">
					<i class="bi bi-award" aria-hidden="true"></i>
					<?php esc_html_e( 'EN 1263-1 · CE Belgeli Üretim', 'woocommerce-store-child' ); ?>
				</span>
			</div>

			<h1 id="wcs-hero-title" class="wcs-hero__title">
				<span class="wcs-hero__title-line"><?php esc_html_e( 'Yüksekte', 'woocommerce-store-child' ); ?></span>
				<span class="wcs-hero__title-line wcs-hero__title-red"><?php esc_html_e( 'Güvenlik', 'woocommerce-store-child' ); ?></span>
				<span class="wcs-hero__title-line wcs-hero__title-outline"><?php esc_html_e( 'Sistemleri', 'woocommerce-store-child' ); ?></span>
			</h1>

			<p class="wcs-hero__text">
				<?php
				printf(
					/* translators: %s: bold text */
					esc_html__( 'İnşaat, endüstri ve spor alanları için %s çözümleri. Brand standartlarımızla üretilen sistemler; stoktan hızlı temin sayesinde 48 saat içinde teslim edilir.', 'woocommerce-store-child' ),
					'<strong>' . esc_html__( 'yüksek dayanımlı güvenlik filesi', 'woocommerce-store-child' ) . '</strong>'
				);
				?>
			</p>

			<div class="wcs-hero__actions">
				<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="wcs-hero__btn wcs-hero__btn--primary">
					<i class="bi bi-grid-fill" aria-hidden="true"></i>
					<?php esc_html_e( 'Ürünleri Gör', 'woocommerce-store-child' ); ?>
					<i class="bi bi-arrow-right wcs-hero__btn-arrow" aria-hidden="true"></i>
				</a>
				<a href="#" class="wcs-hero__btn wcs-hero__btn--link">
					<i class="bi bi-download" aria-hidden="true"></i>
					<?php esc_html_e( 'Teknik Katalog İndir', 'woocommerce-store-child' ); ?>
				</a>
			</div>

			<ul class="wcs-hero__stats" aria-label="<?php esc_attr_e( 'İstatistikler', 'woocommerce-store-child' ); ?>">
				<?php foreach ( $hero_stats as $stat ) : ?>
					<li class="wcs-hero__stat">
						<span class="wcs-hero__stat-value" data-target="<?php echo esc_attr( $stat['value'] ); ?>">
							<?php echo esc_html( $stat['value'] ); ?><sup><?php echo esc_html( $stat['sup'] ); ?></sup>
						</span>
						<span class="wcs-hero__stat-label"><?php echo esc_html( $stat['label'] ); ?></span>
					</li>
				<?php endforeach; ?>
			</ul>

		</div><!-- /.wcs-hero__left -->

		<!-- ── RIGHT ── -->
		<div class="wcs-hero__right" aria-hidden="true">

			<svg class="wcs-hero__ring" viewBox="0 0 160 160" xmlns="http://www.w3.org/2000/svg">
				<circle class="ring-bg"   cx="80" cy="80" r="70"/>
				<circle class="ring-fill" cx="80" cy="80" r="70"/>
			</svg>

			<div class="wcs-hero__net-card">
				<span class="wcs-hero__bracket-tr"></span>
				<span class="wcs-hero__bracket-bl"></span>
				<div class="wcs-hero__net-glow"></div>

				<svg class="wcs-hero__net-svg" viewBox="0 0 380 380" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid slice">
					<defs>
						<pattern id="wcs-dp" x="0" y="0" width="32" height="32" patternUnits="userSpaceOnUse">
							<path d="M16 0 L32 16 L16 32 L0 16 Z" fill="none" stroke="rgba(226,0,13,0.32)" stroke-width="0.65"/>
							<path d="M16 4 L28 16 L16 28 L4 16 Z" fill="none" stroke="rgba(255,255,255,0.07)" stroke-width="0.45"/>
							<circle cx="16" cy="0"  r="1.3" fill="rgba(255,255,255,0.14)"/>
							<circle cx="0"  cy="16" r="1.3" fill="rgba(255,255,255,0.14)"/>
							<circle cx="32" cy="16" r="1.3" fill="rgba(255,255,255,0.14)"/>
							<circle cx="16" cy="32" r="1.3" fill="rgba(255,255,255,0.14)"/>
							<circle cx="16" cy="16" r="2.0" fill="rgba(226,0,13,0.18)"/>
						</pattern>
						<linearGradient id="wcs-vg" x1="0" y1="0" x2="0" y2="1">
							<stop offset="0%"   stop-color="#0a0a0a"/>
							<stop offset="35%"  stop-color="transparent"/>
							<stop offset="65%"  stop-color="transparent"/>
							<stop offset="100%" stop-color="#0a0a0a"/>
						</linearGradient>
						<linearGradient id="wcs-hg" x1="0" y1="0" x2="1" y2="0">
							<stop offset="0%"  stop-color="#0a0a0a"/>
							<stop offset="22%" stop-color="transparent"/>
							<stop offset="100%" stop-color="transparent"/>
						</linearGradient>
					</defs>
					<rect width="380" height="380" fill="#101010"/>
					<rect width="380" height="380" fill="url(#wcs-dp)"/>
					<rect width="380" height="380" fill="url(#wcs-vg)"/>
					<rect width="380" height="380" fill="url(#wcs-hg)"/>
				</svg>

				<div class="wcs-hero__load">
					<p class="wcs-hero__load-value">150</p>
					<p class="wcs-hero__load-unit">kN / m²</p>
					<p class="wcs-hero__load-label"><?php esc_html_e( 'Maks. Yük Kapasitesi', 'woocommerce-store-child' ); ?></p>
				</div>
			</div><!-- /.wcs-hero__net-card -->

			<!-- Spec card -->
			<div class="wcs-hero__spec-card">
				<p class="wcs-hero__spec-card-title">
					<i class="bi bi-clipboard-data" aria-hidden="true"></i>
					<?php esc_html_e( 'Teknik Özellikler', 'woocommerce-store-child' ); ?>
				</p>
				<?php foreach ( $spec_rows as $row ) : ?>
					<p>
						<span class="wcs-hero__spec-key">
							<i class="bi <?php echo esc_attr( $row['icon'] ); ?>" aria-hidden="true"></i>
							<?php echo esc_html( $row['key'] ); ?>
						</span>
						<strong class="<?php echo $row['accent'] ? 'accent' : ''; ?>">
							<?php echo esc_html( $row['val'] ); ?>
						</strong>
					</p>
				<?php endforeach; ?>
			</div>

			<!-- Feature pills -->
			<?php foreach ( $pills as $i => $pill ) : ?>
				<div class="wcs-hero__pill wcs-hero__pill--<?php echo $i + 1; ?>">
					<span class="wcs-hero__pill-icon">
						<i class="bi <?php echo esc_attr( $pill['icon'] ); ?>" aria-hidden="true"></i>
					</span>
					<?php echo esc_html( $pill['text'] ); ?>
				</div>
			<?php endforeach; ?>

		</div><!-- /.wcs-hero__right -->

	</div><!-- /.wcs-hero__inner -->

	<!-- Scroll indicator -->
	<div class="wcs-hero__scroll" aria-hidden="true">
		<div class="wcs-hero__scroll-line"></div>
		<span class="wcs-hero__scroll-label">
			<i class="bi bi-chevron-double-down" aria-hidden="true"></i>
			<?php esc_html_e( 'Keşfet', 'woocommerce-store-child' ); ?>
		</span>
	</div>

	<!-- Ticker -->
	<div class="wcs-hero__ticker" aria-hidden="true">
		<div class="wcs-hero__ticker-track" id="wcs-hero-ticker">
			<?php foreach ( array_merge( $ticker_list, $ticker_list ) as $item ) : ?>
				<span class="wcs-hero__ticker-item">
					<i class="bi <?php echo esc_attr( $item['icon'] ); ?>" aria-hidden="true"></i>
					<?php echo esc_html( $item['text'] ); ?>
				</span>
			<?php endforeach; ?>
		</div>
	</div>

</section>