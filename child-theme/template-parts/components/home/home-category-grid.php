<?php
/**
 * Home category grid section.
 *
 * Shows 7 main product categories as cards on the homepage.
 *
 * @package WooCommerceStoreChild
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'wc_get_page_id' ) ) {
	return;
}

$mapping = array(
	'cocuk-guvenlik-filesi'       => array(
		'title'   => __( 'Çocuk Güvenlik Filesi', 'woocommerce-store-child' ),
		'excerpt' => __( 'Çocuklar için balkon ve merdiven boşluklarında ek koruma sağlar.', 'woocommerce-store-child' ),
		'badges'  => array(
			__( 'Balkon Uyumlu', 'woocommerce-store-child' ),
			__( 'UV Dayanımlı', 'woocommerce-store-child' ),
			__( 'Kolay Montaj', 'woocommerce-store-child' ),
		),
	),
	'balkon-guvenlik-filesi'      => array(
		'title'   => __( 'Balkon Güvenlik Filesi', 'woocommerce-store-child' ),
		'excerpt' => __( 'Açık balkonlarda düşme riskine karşı görünmez bariyer oluşturur.', 'woocommerce-store-child' ),
		'badges'  => array(
			__( 'Dış Mekan Kullanım', 'woocommerce-store-child' ),
			__( 'Ölçüye Özel Üretim', 'woocommerce-store-child' ),
			__( 'Estetik Görünüm', 'woocommerce-store-child' ),
		),
	),
	'kedi-balkon-filesi'          => array(
		'title'   => __( 'Kedi Balkon Filesi', 'woocommerce-store-child' ),
		'excerpt' => __( 'Evcil dostlarınız için balkon ve pencere açıklıklarını güvene alır.', 'woocommerce-store-child' ),
		'badges'  => array(
			__( 'Evcil Hayvan Güvenliği', 'woocommerce-store-child' ),
			__( 'Çizilmeye Dayanıklı', 'woocommerce-store-child' ),
			__( 'İz Bırakmaz', 'woocommerce-store-child' ),
		),
	),
	'kus-koruma-filesi'           => array(
		'title'   => __( 'Kuş Koruma Filesi', 'woocommerce-store-child' ),
		'excerpt' => __( 'Balkon ve cephelerde kuşların konmasını ve zarar vermesini engeller.', 'woocommerce-store-child' ),
		'badges'  => array(
			__( 'Balkon ve Cephe Uyumlu', 'woocommerce-store-child' ),
			__( 'UV Dayanımlı', 'woocommerce-store-child' ),
			__( 'Hafif Yapı', 'woocommerce-store-child' ),
		),
	),
	'havuz-guvenlik-filesi'       => array(
		'title'   => __( 'Havuz Güvenlik Filesi', 'woocommerce-store-child' ),
		'excerpt' => __( 'Havuz çevresinde düşme ve kayma risklerini en aza indirir.', 'woocommerce-store-child' ),
		'badges'  => array(
			__( 'Isıya Dayanıklı', 'woocommerce-store-child' ),
			__( 'Kaymaz Yapı', 'woocommerce-store-child' ),
			__( 'Kolay Sökülüp Takılır', 'woocommerce-store-child' ),
		),
	),
	'merdiven-guvenlik-filesi'    => array(
		'title'   => __( 'Merdiven Güvenlik Filesi', 'woocommerce-store-child' ),
		'excerpt' => __( 'Merdiven boşlukları ve iç atrium alanlarını güvenli hale getirir.', 'woocommerce-store-child' ),
		'badges'  => array(
			__( 'İç Mekan Uyumlu', 'woocommerce-store-child' ),
			__( 'Yüksek Taşıma Kapasitesi', 'woocommerce-store-child' ),
			__( 'Şeffaf Görünüm', 'woocommerce-store-child' ),
		),
	),
	'ozel-olcu-file-sistemleri'   => array(
		'title'   => __( 'Özel Ölçü File Sistemleri', 'woocommerce-store-child' ),
		'excerpt' => __( 'Standart ölçülere uymayan projeler için tam ölçüye özel çözümler.', 'woocommerce-store-child' ),
		'badges'  => array(
			__( 'Proje Bazlı Çözüm', 'woocommerce-store-child' ),
			__( 'Keşif ve Montaj Desteği', 'woocommerce-store-child' ),
			__( 'Endüstriyel Uygunluk', 'woocommerce-store-child' ),
		),
	),
);

$cards = array();

foreach ( $mapping as $slug => $config ) {
	$term = get_term_by( 'slug', $slug, 'product_cat' );

	if ( ! $term || is_wp_error( $term ) ) {
		continue;
	}

	$thumbnail_id = (int) get_term_meta( $term->term_id, 'thumbnail_id', true );
	$image_html   = $thumbnail_id ? wp_get_attachment_image( $thumbnail_id, 'medium_large', false, array( 'class' => 'wcs-home-category-card__img' ) ) : '';

	// Meta üzerinden özelleştirilmiş başlık/açıklama/badge oku, yoksa mapping'e düş.
	$title_meta   = get_term_meta( $term->term_id, 'wcs_home_title', true );
	$teaser_meta  = get_term_meta( $term->term_id, 'wcs_home_teaser', true );
	$badges_meta  = get_term_meta( $term->term_id, 'wcs_home_badges', true );

	$title  = $title_meta ? $title_meta : $config['title'];
	$teaser = $teaser_meta ? $teaser_meta : $config['excerpt'];

	$badges = is_array( $badges_meta ) && ! empty( $badges_meta )
		? $badges_meta
		: ( isset( $config['badges'] ) ? $config['badges'] : array() );

	$cards[] = array(
		'term'   => $term,
		'title'  => $title,
		'teaser' => $teaser,
		'badges' => $badges,
		'image'  => $image_html,
	);
}

if ( empty( $cards ) ) {
	return;
}

$section_id = 'wcs-home-categories';
?>

<section id="<?php echo esc_attr( $section_id ); ?>" class="wcs-home-categories" aria-labelledby="wcs-home-categories-title">
	<div class="wcs-home-categories__inner">
		<header class="wcs-home-categories__header">
			<h2 id="wcs-home-categories-title" class="wcs-home-categories__title">
				<?php esc_html_e( 'İhtiyacınıza Uygun Güvenlik Filesi Çözümleri', 'woocommerce-store-child' ); ?>
			</h2>
			<p class="wcs-home-categories__subtitle">
				<?php esc_html_e( 'Balkon, merdiven, havuz ve evcil hayvan güvenliği için uygun file kategorisini seçin.', 'woocommerce-store-child' ); ?>
			</p>
		</header>

		<div class="wcs-home-categories__grid">
			<?php foreach ( $cards as $card ) : ?>
				<?php
				$term   = $card['term'];
				$link   = get_term_link( $term );

				if ( is_wp_error( $link ) ) {
					continue;
				}
				?>
				<article class="wcs-home-category-card">
					<a class="wcs-home-category-card__media" href="<?php echo esc_url( $link ); ?>">
						<?php
						if ( $card['image'] ) {
							echo $card['image']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						} else {
							?>
							<div class="wcs-home-category-card__media-placeholder">
								<span class="wcs-home-category-card__media-icon" aria-hidden="true"></span>
							</div>
							<?php
						}
						?>
					</a>

					<div class="wcs-home-category-card__body">
						<h3 class="wcs-home-category-card__title">
							<a href="<?php echo esc_url( $link ); ?>">
								<?php echo esc_html( $card['title'] ); ?>
							</a>
						</h3>

						<p class="wcs-home-category-card__excerpt">
							<?php echo esc_html( $card['teaser'] ); ?>
						</p>

						<?php if ( ! empty( $card['badges'] ) ) : ?>
							<ul class="wcs-home-category-card__badges" aria-label="<?php esc_attr_e( 'Öne çıkan özellikler', 'woocommerce-store-child' ); ?>">
								<?php
								$badge_count = 0;
								foreach ( $card['badges'] as $badge ) :
									$badge_count++;
									if ( $badge_count > 3 ) {
										break;
									}
									?>
									<li class="wcs-home-category-card__badge"><?php echo esc_html( $badge ); ?></li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>

						<a class="wcs-home-category-card__cta" href="<?php echo esc_url( $link ); ?>">
							<?php esc_html_e( 'Ürünleri Gör', 'woocommerce-store-child' ); ?>
						</a>
					</div>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>

