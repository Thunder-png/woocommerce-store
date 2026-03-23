<?php
/**
 * Site Footer — Karaca File Child Theme.
 *
 * 4 kolonlu kurumsal footer:
 * Logo + açıklama | Ürün kategorileri | Müşteri hizmetleri | İletişim & sosyal
 * Alt çizgi: politika linkleri + telif
 *
 * @package WooCommerce_Store_Child
 */

defined( 'ABSPATH' ) || exit;

$lottie_url = get_stylesheet_directory_uri() . '/assets/branding/logo/logo-lottie-version.json';

$category_links = array(
	array(
		'label' => __( 'Balkon Güvenlik Filesi', 'woocommerce-store-child' ),
		'slug'  => 'balkon-guvenlik-filesi',
		'icon'  => 'bi-house-door',
	),
	array(
		'label' => __( 'Çocuk Filesi', 'woocommerce-store-child' ),
		'slug'  => 'cocuk-filesi-file-urunleri',
		'icon'  => 'bi-person-hearts',
	),
	array(
		'label' => __( 'Havuz Filesi', 'woocommerce-store-child' ),
		'slug'  => 'havuz-filesi-file-urunleri',
		'icon'  => 'bi-water',
	),
	array(
		'label' => __( 'Kedi Filesi', 'woocommerce-store-child' ),
		'slug'  => 'kedi-filesi-file-urunleri',
		'icon'  => 'bi-heart',
	),
	array(
		'label' => __( 'Merdiven Filesi', 'woocommerce-store-child' ),
		'slug'  => 'merdiven-filesi',
		'icon'  => 'bi-ladder',
	),
	array(
		'label' => __( 'Özel Ölçü', 'woocommerce-store-child' ),
		'url'   => add_query_arg( 'product_tag', 'ozel-olcu', wc_get_page_permalink( 'shop' ) ),
		'icon'  => 'bi-rulers',
	),
);

$service_links = array(
	array(
		'label' => __( 'Tüm Ürünler', 'woocommerce-store-child' ),
		'url'   => wc_get_page_permalink( 'shop' ),
		'icon'  => 'bi-grid',
	),
	array(
		'label' => __( 'Hesabım', 'woocommerce-store-child' ),
		'url'   => wc_get_page_permalink( 'myaccount' ),
		'icon'  => 'bi-person',
	),
	array(
		'label' => __( 'Sepetim', 'woocommerce-store-child' ),
		'url'   => wc_get_page_permalink( 'cart' ),
		'icon'  => 'bi-cart3',
	),
	array(
		'label' => __( 'Montaj & Keşif', 'woocommerce-store-child' ),
		'url'   => home_url( '/montaj-kesif/' ),
		'icon'  => 'bi-tools',
	),
	array(
		'label' => __( 'Blog', 'woocommerce-store-child' ),
		'url'   => home_url( '/blog/' ),
		'icon'  => 'bi-journal-text',
	),
	array(
		'label' => __( 'İletişim', 'woocommerce-store-child' ),
		'url'   => home_url( '/iletisim/' ),
		'icon'  => 'bi-envelope',
	),
);

$policy_links = array(
	array(
		'label' => __( 'Gizlilik Politikası', 'woocommerce-store-child' ),
		'url'   => home_url( '/gizlilik-politikasi/' ),
	),
	array(
		'label' => __( 'İade ve İptal', 'woocommerce-store-child' ),
		'url'   => home_url( '/iade-ve-iptal-politikasi/' ),
	),
	array(
		'label' => __( 'KVKK', 'woocommerce-store-child' ),
		'url'   => home_url( '/kvkk-aydinlatma-metni/' ),
	),
	array(
		'label' => __( 'Ödeme & Teslimat', 'woocommerce-store-child' ),
		'url'   => home_url( '/odeme-ve-teslimat/' ),
	),
	array(
		'label' => __( 'Çerez Politikası', 'woocommerce-store-child' ),
		'url'   => home_url( '/cerez-politikasi/' ),
	),
	array(
		'label' => __( 'Mesafeli Satış Sözleşmesi', 'woocommerce-store-child' ),
		'url'   => home_url( '/mesafeli-satis-sozlesmesi/' ),
	),
);

$cart_count = function_exists( 'WC' ) && WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
?>

<footer class="wcs-footer" aria-label="<?php esc_attr_e( 'Site alt bilgisi', 'woocommerce-store-child' ); ?>">

	<!-- ── ÜST KESİT: Newsletter CTA ──────────────────────── -->
	<div class="wcs-footer__cta-strip">
		<div class="wcs-footer__cta-inner">
			<div class="wcs-footer__cta-text">
				<span class="wcs-footer__cta-eyebrow">
					<i class="bi bi-shield-fill-check" aria-hidden="true"></i>
					<?php esc_html_e( 'Güvenli Alışveriş Garantisi', 'woocommerce-store-child' ); ?>
				</span>
				<h2 class="wcs-footer__cta-title">
					<?php esc_html_e( 'Projeniz için Ücretsiz Keşif Talep Edin', 'woocommerce-store-child' ); ?>
				</h2>
				<p class="wcs-footer__cta-desc">
					<?php esc_html_e( 'Uzman ekibimiz ölçüm, öneri ve kurulum için yanınızda. Ankara ve çevre illere ücretsiz keşif hizmeti.', 'woocommerce-store-child' ); ?>
				</p>
			</div>
			<div class="wcs-footer__cta-actions">
				<a href="<?php echo esc_url( home_url( '/iletisim/' ) ); ?>" class="wcs-footer__cta-btn wcs-footer__cta-btn--primary">
					<i class="bi bi-calendar-check" aria-hidden="true"></i>
					<?php esc_html_e( 'Keşif Randevusu Al', 'woocommerce-store-child' ); ?>
				</a>
				<a href="tel:+908503802006" class="wcs-footer__cta-btn wcs-footer__cta-btn--ghost">
					<i class="bi bi-telephone" aria-hidden="true"></i>
					0850 380 20 06
				</a>
			</div>
		</div>
	</div>

	<!-- ── ANA FOOTER GRID ─────────────────────────────────── -->
	<div class="wcs-footer__main">
		<div class="wcs-footer__grid">

			<!-- Kolon 1: Marka -->
			<div class="wcs-footer__col wcs-footer__col--brand">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"
				   class="wcs-footer__logo-link"
				   aria-label="<?php esc_attr_e( 'Karaca File Ana Sayfa', 'woocommerce-store-child' ); ?>">
					<span class="wcs-footer__lottie"
						  data-wcs-lottie
						  data-lottie-src="<?php echo esc_url( $lottie_url ); ?>"
						  aria-hidden="true"></span>
				</a>

				<p class="wcs-footer__brand-desc">
					<?php esc_html_e( '20 yıllık deneyimle balkon, çocuk, havuz, kedi ve merdiven güvenliği için CE belgeli profesyonel file sistemleri üretiyoruz.', 'woocommerce-store-child' ); ?>
				</p>

				<!-- Sertifika rozeti -->
				<div class="wcs-footer__badges">
					<span class="wcs-footer__badge">
						<i class="bi bi-patch-check-fill" aria-hidden="true"></i>
						CE Belgeli
					</span>
					<span class="wcs-footer__badge">
						<i class="bi bi-award" aria-hidden="true"></i>
						EN 1263-1
					</span>
					<span class="wcs-footer__badge">
						<i class="bi bi-shield-check" aria-hidden="true"></i>
						SSL Güvenli
					</span>
				</div>

				<!-- Sosyal medya -->
				<div class="wcs-footer__social" aria-label="<?php esc_attr_e( 'Sosyal medya', 'woocommerce-store-child' ); ?>">
					<a href="https://www.instagram.com/karacafile" target="_blank" rel="noopener noreferrer"
					   class="wcs-footer__social-btn" aria-label="Instagram">
						<i class="bi bi-instagram" aria-hidden="true"></i>
					</a>
					<a href="https://www.facebook.com/karacafile" target="_blank" rel="noopener noreferrer"
					   class="wcs-footer__social-btn" aria-label="Facebook">
						<i class="bi bi-facebook" aria-hidden="true"></i>
					</a>
					<a href="https://wa.me/908503802006" target="_blank" rel="noopener noreferrer"
					   class="wcs-footer__social-btn wcs-footer__social-btn--wa" aria-label="WhatsApp">
						<i class="bi bi-whatsapp" aria-hidden="true"></i>
					</a>
					<a href="https://www.youtube.com/@karacafile" target="_blank" rel="noopener noreferrer"
					   class="wcs-footer__social-btn" aria-label="YouTube">
						<i class="bi bi-youtube" aria-hidden="true"></i>
					</a>
				</div>
			</div>

			<!-- Kolon 2: Ürün Kategorileri -->
			<div class="wcs-footer__col">
				<h3 class="wcs-footer__col-title">
					<i class="bi bi-grid-fill" aria-hidden="true"></i>
					<?php esc_html_e( 'Ürün Kategorileri', 'woocommerce-store-child' ); ?>
				</h3>
				<ul class="wcs-footer__links" aria-label="<?php esc_attr_e( 'Kategoriler', 'woocommerce-store-child' ); ?>">
					<?php foreach ( $category_links as $item ) :
						if ( isset( $item['slug'] ) ) {
							$term = get_term_by( 'slug', $item['slug'], 'product_cat' );
							$url  = $term ? get_term_link( $term ) : wc_get_page_permalink( 'shop' );
							if ( is_wp_error( $url ) ) $url = wc_get_page_permalink( 'shop' );
						} else {
							$url = $item['url'];
						}
					?>
						<li>
							<a href="<?php echo esc_url( $url ); ?>" class="wcs-footer__link">
								<i class="bi <?php echo esc_attr( $item['icon'] ); ?>" aria-hidden="true"></i>
								<?php echo esc_html( $item['label'] ); ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>

			<!-- Kolon 3: Müşteri Hizmetleri -->
			<div class="wcs-footer__col">
				<h3 class="wcs-footer__col-title">
					<i class="bi bi-headset" aria-hidden="true"></i>
					<?php esc_html_e( 'Müşteri Hizmetleri', 'woocommerce-store-child' ); ?>
				</h3>
				<ul class="wcs-footer__links">
					<?php foreach ( $service_links as $item ) : ?>
						<li>
							<a href="<?php echo esc_url( $item['url'] ); ?>" class="wcs-footer__link">
								<i class="bi <?php echo esc_attr( $item['icon'] ); ?>" aria-hidden="true"></i>
								<?php echo esc_html( $item['label'] ); ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>

			<!-- Kolon 4: İletişim -->
			<div class="wcs-footer__col">
				<h3 class="wcs-footer__col-title">
					<i class="bi bi-geo-alt-fill" aria-hidden="true"></i>
					<?php esc_html_e( 'İletişim', 'woocommerce-store-child' ); ?>
				</h3>

				<ul class="wcs-footer__contact-list">
					<li class="wcs-footer__contact-item">
						<span class="wcs-footer__contact-icon" aria-hidden="true">
							<i class="bi bi-telephone-fill"></i>
						</span>
						<div>
							<span class="wcs-footer__contact-label"><?php esc_html_e( 'Telefon', 'woocommerce-store-child' ); ?></span>
							<a href="tel:+908503802006" class="wcs-footer__contact-val">0850 380 20 06</a>
						</div>
					</li>
					<li class="wcs-footer__contact-item">
						<span class="wcs-footer__contact-icon" aria-hidden="true">
							<i class="bi bi-whatsapp"></i>
						</span>
						<div>
							<span class="wcs-footer__contact-label">WhatsApp</span>
							<a href="https://wa.me/908503802006" target="_blank" rel="noopener noreferrer"
							   class="wcs-footer__contact-val">Hızlı Mesaj Gönder</a>
						</div>
					</li>
					<li class="wcs-footer__contact-item">
						<span class="wcs-footer__contact-icon" aria-hidden="true">
							<i class="bi bi-envelope-fill"></i>
						</span>
						<div>
							<span class="wcs-footer__contact-label">E-posta</span>
							<a href="mailto:info@bykaracafile.com.tr" class="wcs-footer__contact-val">info@bykaracafile.com.tr</a>
						</div>
					</li>
					<li class="wcs-footer__contact-item">
						<span class="wcs-footer__contact-icon" aria-hidden="true">
							<i class="bi bi-geo-alt-fill"></i>
						</span>
						<div>
							<span class="wcs-footer__contact-label"><?php esc_html_e( 'Adres', 'woocommerce-store-child' ); ?></span>
							<span class="wcs-footer__contact-val">
								<?php esc_html_e( 'Ankara, Türkiye', 'woocommerce-store-child' ); ?>
							</span>
						</div>
					</li>
				</ul>

				<!-- Çalışma saatleri -->
				<div class="wcs-footer__hours">
					<span class="wcs-footer__hours-title">
						<i class="bi bi-clock" aria-hidden="true"></i>
						<?php esc_html_e( 'Çalışma Saatleri', 'woocommerce-store-child' ); ?>
					</span>
					<span class="wcs-footer__hours-val">
						<?php esc_html_e( 'Pzt – Cmt: 09:00 – 18:00', 'woocommerce-store-child' ); ?>
					</span>
				</div>
			</div>

		</div><!-- /.wcs-footer__grid -->
	</div><!-- /.wcs-footer__main -->

	<!-- ── ALT ÇİZGİ ──────────────────────────────────────── -->
	<div class="wcs-footer__bottom">
		<div class="wcs-footer__bottom-inner">

			<!-- Politika linkleri -->
			<nav class="wcs-footer__policy-links" aria-label="<?php esc_attr_e( 'Yasal sayfalar', 'woocommerce-store-child' ); ?>">
				<?php foreach ( $policy_links as $i => $pl ) : ?>
					<?php if ( $i > 0 ) : ?>
						<span class="wcs-footer__policy-sep" aria-hidden="true">·</span>
					<?php endif; ?>
					<a href="<?php echo esc_url( $pl['url'] ); ?>" class="wcs-footer__policy-link">
						<?php echo esc_html( $pl['label'] ); ?>
					</a>
				<?php endforeach; ?>
			</nav>

			<!-- Telif -->
			<p class="wcs-footer__copy">
				© <?php echo date_i18n( 'Y' ); ?>
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="wcs-footer__copy-brand">
					<?php esc_html_e( 'Karaca File', 'woocommerce-store-child' ); ?>
				</a>
				<?php esc_html_e( '— Tüm hakları saklıdır.', 'woocommerce-store-child' ); ?>
			</p>

			<!-- Ödeme yöntemleri -->
			<div class="wcs-footer__payments" aria-label="<?php esc_attr_e( 'Ödeme yöntemleri', 'woocommerce-store-child' ); ?>">
				<?php
				$payment_methods = array( 'Visa', 'Mastercard', 'Havale', 'Kapıda Ödeme' );
				foreach ( $payment_methods as $pm ) :
				?>
					<span class="wcs-footer__payment-pill"><?php echo esc_html( $pm ); ?></span>
				<?php endforeach; ?>
			</div>

		</div>
	</div>

</footer><!-- /.wcs-footer -->

<button id="wcs-back-to-top" class="wcs-back-to-top" aria-label="<?php esc_attr_e( 'Sayfa basina git', 'woocommerce-store-child' ); ?>" hidden>
	<i class="bi bi-arrow-up"></i>
</button>

<nav class="wcs-mobile-bottom-nav" aria-label="<?php esc_attr_e( 'Mobil alt menu', 'woocommerce-store-child' ); ?>">
	<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="wcs-mobile-bottom-nav__item">
		<i class="bi bi-house"></i>
		<span><?php esc_html_e( 'Ana Sayfa', 'woocommerce-store-child' ); ?></span>
	</a>
	<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="wcs-mobile-bottom-nav__item">
		<i class="bi bi-grid"></i>
		<span><?php esc_html_e( 'Urunler', 'woocommerce-store-child' ); ?></span>
	</a>
	<a href="<?php echo esc_url( wc_get_cart_url() ); ?>" class="wcs-mobile-bottom-nav__item wcs-mobile-bottom-nav__item--cart">
		<i class="bi bi-cart3"></i>
		<span><?php esc_html_e( 'Sepet', 'woocommerce-store-child' ); ?></span>
		<span class="wcs-mobile-bottom-nav__badge" data-wcs-mobile-cart-count><?php echo absint( $cart_count ); ?></span>
	</a>
	<a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" class="wcs-mobile-bottom-nav__item">
		<i class="bi bi-person"></i>
		<span><?php esc_html_e( 'Hesabim', 'woocommerce-store-child' ); ?></span>
	</a>
</nav>
