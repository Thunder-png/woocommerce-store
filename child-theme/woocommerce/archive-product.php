<?php
/**
 * Product Archive Template Override — Karaca File Child Theme.
 *
 * Ana sayfa = Shop sayfası olduğunda:
 *   Hero → Trust Bar → Kategori Grid → Özel Ölçü → Ürün Grid → Neden Karaca File → CTA
 *
 * Normal /shop ve kategori arşivlerinde:
 *   Hero → WooCommerce ürün loop'u
 *
 * @package WooCommerce_Store_Child
 */

defined( 'ABSPATH' ) || exit;

$is_home_shop = function_exists( 'is_front_page' ) && function_exists( 'is_shop' )
	&& is_front_page() && is_shop();

get_header( 'shop' );
get_template_part( 'template-parts/components/hero/hero' );
do_action( 'woocommerce_before_main_content' );

if ( $is_home_shop ) :

	// TRUST BAR
	$trust_items = array(
		array( 'bi-shield-fill-check', 'CE Belgeli Üretim',   'EN 1263-1 Sertifikalı' ),
		array( 'bi-truck',             '48 Saat Teslimat',    'Stoktan hızlı sevk' ),
		array( 'bi-rulers',            'Özel Ölçü Üretim',    'İstediğiniz boyutta sipariş' ),
		array( 'bi-telephone-fill',    'Teknik Destek',       'Uzman ekip, ücretsiz keşif' ),
	);
	?>
	<div class="wcs-trust-bar">
		<div class="wcs-trust-bar__inner">
			<?php foreach ( $trust_items as $item ) : ?>
				<div class="wcs-trust-bar__item">
					<span class="wcs-trust-bar__icon" aria-hidden="true"><i class="bi <?php echo esc_attr( $item[0] ); ?>"></i></span>
					<div class="wcs-trust-bar__text">
						<strong class="wcs-trust-bar__title"><?php echo esc_html( $item[1] ); ?></strong>
						<span class="wcs-trust-bar__sub"><?php echo esc_html( $item[2] ); ?></span>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>

	<?php
	// CATEGORY GRID
	$category_data = array(
		array( 'balkon-guvenlik-filesi',       'Balkon Güvenlik Filesi', 'Apartman ve villa balkonları için',  'bi-house-door',    'balkon-kategori.jpeg' ),
		array( 'cocuk-filesi-file-urunleri',   'Çocuk Filesi',           'Güvenli yaşam alanları için',       'bi-person-hearts', 'cocuk-balkon-kategori.jpeg' ),
		array( 'havuz-filesi-file-urunleri',   'Havuz Filesi',           'Su alanı güvenlik çözümleri',       'bi-water',         'havuz-filesi-kategori.jpeg' ),
		array( 'kedi-filesi-file-urunleri',    'Kedi Filesi',            'Evcil hayvan güvenliği için',       'bi-heart',         'kedi-filesi-kategori.jpeg' ),
		array( 'merdiven-filesi',              'Merdiven Filesi',        'Merdiven boşlukları için',          'bi-ladder',        'merdiven-filesi-kategori.jpg' ),
	);

	$banner_base = get_stylesheet_directory_uri() . '/assets/site-banner/';
	?>

	<section class="wcs-fp-categories">
		<div class="wcs-fp-section-head">
			<span class="wcs-fp-eyebrow"><i class="bi bi-grid-fill"></i> Ürün Kategorileri</span>
			<h2 class="wcs-fp-section-title">Güvenlik Filesi Çeşitleri</h2>
			<p class="wcs-fp-section-desc">Balkon, çocuk, havuz, kedi ve merdiven güvenliği için profesyonel file sistemleri.</p>
		</div>
		<div class="wcs-fp-cat-grid">
			<?php foreach ( $category_data as $i => $cat ) :
				$term = get_term_by( 'slug', $cat[0], 'product_cat' );
				$link = $term ? get_term_link( $term ) : wc_get_page_permalink( 'shop' );
				if ( is_wp_error( $link ) ) $link = wc_get_page_permalink( 'shop' );
				?>
				<a href="<?php echo esc_url( $link ); ?>" class="wcs-fp-cat-card wcs-fp-cat-card--<?php echo $i+1; ?>">
					<div class="wcs-fp-cat-card__img-wrap">
						<img src="<?php echo esc_url( $banner_base . $cat[4] ); ?>" alt="<?php echo esc_attr( $cat[1] ); ?>" loading="lazy" decoding="async" class="wcs-fp-cat-card__img">
						<div class="wcs-fp-cat-card__overlay" aria-hidden="true"></div>
					</div>
					<div class="wcs-fp-cat-card__body">
						<span class="wcs-fp-cat-card__icon"><i class="bi <?php echo esc_attr( $cat[3] ); ?>"></i></span>
						<div>
							<h3 class="wcs-fp-cat-card__title"><?php echo esc_html( $cat[1] ); ?></h3>
							<p class="wcs-fp-cat-card__sub"><?php echo esc_html( $cat[2] ); ?></p>
						</div>
						<span class="wcs-fp-cat-card__arrow"><i class="bi bi-arrow-right"></i></span>
					</div>
				</a>
			<?php endforeach; ?>
		</div>
	</section>

	<!-- OZEL OLCU -->
	<section class="wcs-fp-custom">
		<div class="wcs-fp-custom__inner">
			<div class="wcs-fp-custom__left">
				<span class="wcs-fp-eyebrow wcs-fp-eyebrow--light"><i class="bi bi-rulers"></i> Özel Ölçü Sistemi</span>
				<h2 class="wcs-fp-custom__title">Tam Ölçünüze<br><span class="wcs-fp-custom__title-accent">Üretim Yapıyoruz</span></h2>
				<p class="wcs-fp-custom__desc">Balkon, teras veya özel alanınızın boyutlarını girin. m² bazlı fiyatlandırma sistemi ile anında teklif alın, hızlıca sipariş oluşturun.</p>
				<ul class="wcs-fp-custom__feats">
					<li><i class="bi bi-check-circle-fill"></i> Genişlik × yükseklik girerek anında m² hesaplama</li>
					<li><i class="bi bi-check-circle-fill"></i> Kesip dikilerek özel boyuta üretim</li>
					<li><i class="bi bi-check-circle-fill"></i> Sipariş sonrası kargo ile hızlı teslim</li>
					<li><i class="bi bi-check-circle-fill"></i> Minimum 1 m² — maksimum sınırsız alan</li>
				</ul>
				<a href="<?php echo esc_url( add_query_arg( 'product_tag', 'ozel-olcu', wc_get_page_permalink( 'shop' ) ) ); ?>" class="wcs-fp-custom__btn">
					<i class="bi bi-calculator"></i> Özel Ölçü Ürünleri Gör <i class="bi bi-arrow-right"></i>
				</a>
			</div>

			<div class="wcs-fp-custom__right">
				<div class="wcs-fp-calc">
					<header class="wcs-fp-calc__header"><i class="bi bi-calculator-fill"></i> <span>Hızlı m² Hesaplama</span></header>
					<div class="wcs-fp-calc__body">
						<div class="wcs-fp-calc__field">
							<label for="wcs-calc-w" class="wcs-fp-calc__label"><i class="bi bi-arrows-expand-vertical"></i> Genişlik (cm)</label>
							<input type="number" id="wcs-calc-w" class="wcs-fp-calc__input" min="1" max="2000" step="1" value="150" placeholder="örn. 150">
						</div>
						<div class="wcs-fp-calc__field">
							<label for="wcs-calc-h" class="wcs-fp-calc__label"><i class="bi bi-arrows-expand"></i> Yükseklik (cm)</label>
							<input type="number" id="wcs-calc-h" class="wcs-fp-calc__input" min="1" max="2000" step="1" value="200" placeholder="örn. 200">
						</div>
						<div class="wcs-fp-calc__result" aria-live="polite">
							<span class="wcs-fp-calc__result-label">Toplam Alan</span>
							<span class="wcs-fp-calc__result-value" id="wcs-calc-val">3.00 m²</span>
						</div>
						<a href="<?php echo esc_url( add_query_arg( 'product_tag', 'ozel-olcu', wc_get_page_permalink( 'shop' ) ) ); ?>" class="wcs-fp-calc__cta">
							<i class="bi bi-cart-plus"></i> Bu Ölçüde Sipariş Ver
						</a>
					</div>
					<footer class="wcs-fp-calc__footer"><i class="bi bi-info-circle"></i> Nihai fiyat ürün sayfasında hesaplanır.</footer>
				</div>
			</div>
		</div>
	</section>

	<!-- OZEL OLCU URUN GRID -->
	<?php
	// home-category-grid.php'deki mantıkla özel ölçü tag'ına sahip ürünleri çek.
	$ozel_products = array();
	if ( function_exists( 'wc_get_products' ) ) {
		foreach ( array( 'ozel-olcu', 'özel-ölçü', 'özel-olcu' ) as $tag_slug ) {
			$ozel_products = wc_get_products( array(
				'status'  => 'publish',
				'limit'   => 6,
				'orderby' => 'date',
				'order'   => 'DESC',
				'tag'     => array( $tag_slug ),
			) );
			if ( ! empty( $ozel_products ) ) break;
		}
	}
	?>

	<?php if ( ! empty( $ozel_products ) ) : ?>
	<section class="wcs-home-categories wcs-home-custom-products" aria-label="<?php esc_attr_e( 'Özel ölçü ürünleri', 'woocommerce-store-child' ); ?>">
		<div class="wcs-home-categories__inner">
			<header class="wcs-home-categories__header">
				<h2 class="wcs-home-categories__title"><?php esc_html_e( 'Özel Ölçüde Satın Al', 'woocommerce-store-child' ); ?></h2>
				<p class="wcs-home-categories__subtitle"><?php esc_html_e( 'Ölçünü gir, fiyatı anında hesapla — istediğin boyutta sipariş ver.', 'woocommerce-store-child' ); ?></p>
			</header>
			<div class="wcs-home-categories__grid wcs-home-categories__grid--products" role="list">
				<?php foreach ( $ozel_products as $ozel_product ) :
					if ( ! $ozel_product instanceof WC_Product ) continue;
					$op_permalink = $ozel_product->get_permalink();
					$op_image_id  = $ozel_product->get_image_id();
					$op_image_url = $op_image_id ? wp_get_attachment_image_url( $op_image_id, 'medium_large' ) : '';
					$op_regular   = (float) $ozel_product->get_regular_price();
					$op_sale      = (float) $ozel_product->get_sale_price();
					$op_discount  = ( $op_sale && $op_regular > 0 ) ? round( ( ( $op_regular - $op_sale ) / $op_regular ) * 100 ) : 0;
				?>
					<article class="wcs-home-category-card wcs-home-product-card" role="listitem">
						<a class="wcs-home-category-card__link" href="<?php echo esc_url( $op_permalink ); ?>">
							<?php if ( $op_image_url ) : ?>
								<img class="wcs-home-category-card__img"
									 src="<?php echo esc_url( $op_image_url ); ?>"
									 alt="<?php echo esc_attr( $ozel_product->get_name() ); ?>"
									 loading="lazy"
                                     decoding="async">
							<?php else : ?>
								<div class="wcs-home-category-card__media-placeholder" aria-hidden="true">
									<span class="wcs-home-category-card__media-icon"></span>
								</div>
							<?php endif; ?>

							<?php if ( $op_discount > 0 ) : ?>
								<span class="wcs-home-product-card__badge">-<?php echo absint( $op_discount ); ?>%</span>
							<?php endif; ?>

							<div class="wcs-home-category-card__overlay">
								<span class="wcs-home-product-card__ozel-tag">
									<i class="bi bi-rulers"></i>
									<?php esc_html_e( 'Özel Ölçü', 'woocommerce-store-child' ); ?>
								</span>
								<h3 class="wcs-home-category-card__title"><?php echo esc_html( $ozel_product->get_name() ); ?></h3>
								<p class="wcs-home-product-card__price"><?php echo wp_kses_post( $ozel_product->get_price_html() ); ?></p>
								<p class="wcs-home-category-card__subtitle"><?php esc_html_e( 'Ölçü gir & sipariş ver →', 'woocommerce-store-child' ); ?></p>
							</div>
						</a>
					</article>
				<?php endforeach; ?>
			</div>

			<!-- Tüm özel ölçü ürünleri linki -->
			<div class="wcs-home-ozel-footer">
				<a href="<?php echo esc_url( add_query_arg( 'product_tag', 'ozel-olcu', wc_get_page_permalink( 'shop' ) ) ); ?>" class="wcs-home-ozel-footer__link">
					<?php esc_html_e( 'Tüm Özel Ölçü Ürünlerini Gör', 'woocommerce-store-child' ); ?>
					<i class="bi bi-arrow-right"></i>
				</a>
			</div>
		</div>
	</section>
	<?php endif; ?>

	<!-- FEATURED PRODUCTS -->
	<section class="wcs-fp-products">
		<div class="wcs-fp-products__inner">
			<div class="wcs-fp-section-head">
				<span class="wcs-fp-eyebrow"><i class="bi bi-star-fill"></i> Öne Çıkan Ürünler</span>
				<h2 class="wcs-fp-section-title">Popüler Güvenlik Fileleri</h2>
			</div>
		<?php
		if ( woocommerce_product_loop() ) {
			do_action( 'woocommerce_before_shop_loop' );
			?>
			<div id="wcs-products-wrap">
			<?php
			woocommerce_product_loop_start();
			if ( wc_get_loop_prop( 'total' ) ) {
				while ( have_posts() ) {
					the_post();
					do_action( 'woocommerce_shop_loop' );
					wc_get_template_part( 'content', 'product' );
				}
			}
			woocommerce_product_loop_end();
			do_action( 'woocommerce_after_shop_loop' );
			?>
			</div><!-- /#wcs-products-wrap -->
			<?php
		} else {
			?>
			<div id="wcs-products-wrap">
			<?php do_action( 'woocommerce_no_products_found' ); ?>
			</div><!-- /#wcs-products-wrap -->
			<?php
		}
		?>
	</div>
</section>

	<!-- WHY KARACA FILE -->
	<section class="wcs-fp-why">
		<div class="wcs-fp-why__inner">
			<div class="wcs-fp-section-head wcs-fp-section-head--center">
				<span class="wcs-fp-eyebrow"><i class="bi bi-award"></i> Neden Karaca File?</span>
				<h2 class="wcs-fp-section-title">20 Yıllık Uzmanlık, Güvenilir Üretim</h2>
			</div>
			<div class="wcs-fp-why__grid">
				<?php
				$why = array(
					array( 'bi-patch-check-fill',  'red',  'CE & EN 1263-1 Sertifikalı',    'Tüm ürünlerimiz Avrupa güvenlik standartlarını karşılar. Belgesiz satış yapmıyoruz.' ),
					array( 'bi-rulers',            'navy', 'Her Ölçüye Üretim',             'Standart boyutların yanı sıra özel ölçüde kesip dikilerek üretim yapıyoruz.' ),
					array( 'bi-truck',             'red',  'Hızlı Kargo & Teslimat',        'Stok ürünlerde 48 saat içinde kargoya verilir. Takip linki e-posta ile iletilir.' ),
					array( 'bi-tools',             'navy', 'Ücretsiz Keşif & Montaj',       'Teknik ekibimiz ölçüm ve kurulum için bölgenize gelir. İstanbul ve çevre illere hizmet.' ),
					array( 'bi-chat-dots',         'red',  'Uzman Teknik Destek',           'Ürün seçiminden montaja kadar her adımda size eşlik eden uzman ekip.' ),
					array( 'bi-shield-fill-check', 'navy', 'Garanti & İade Güvencesi',      'Ürün memnuniyeti esas alınır. İade ve garanti süreçleri şeffaf biçimde yönetilir.' ),
				);
				foreach ( $why as $w ) :
					?>
					<div class="wcs-fp-why__card wcs-fp-why__card--<?php echo esc_attr( $w[1] ); ?>">
						<span class="wcs-fp-why__icon"><i class="bi <?php echo esc_attr( $w[0] ); ?>"></i></span>
						<h3 class="wcs-fp-why__card-title"><?php echo esc_html( $w[2] ); ?></h3>
						<p class="wcs-fp-why__card-desc"><?php echo esc_html( $w[3] ); ?></p>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<!-- CTA BANNER -->
	<section class="wcs-fp-cta">
		<div class="wcs-fp-cta__inner">
			<div class="wcs-fp-cta__text">
				<h2 class="wcs-fp-cta__title">Projeniz için Teklif Alın</h2>
				<p class="wcs-fp-cta__sub">Toplu sipariş, özel ölçü veya montaj için uzmanlarımızla iletişime geçin.</p>
			</div>
			<div class="wcs-fp-cta__actions">
				<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="wcs-fp-cta__btn wcs-fp-cta__btn--primary">
					<i class="bi bi-grid-fill"></i> Tüm Ürünler
				</a>
				<a href="tel:+90" class="wcs-fp-cta__btn wcs-fp-cta__btn--outline">
					<i class="bi bi-telephone"></i> Bizi Ara
				</a>
			</div>
		</div>
	</section>

	<script>
	(function(){
		var w=document.getElementById('wcs-calc-w'),h=document.getElementById('wcs-calc-h'),v=document.getElementById('wcs-calc-val');
		if(w&&h&&v){
			function c(){v.textContent=((parseFloat(w.value)||0)/100*((parseFloat(h.value)||0)/100)).toFixed(2)+' m²';}
			w.addEventListener('input',c); h.addEventListener('input',c); c();
		}
		if('IntersectionObserver' in window){
			var obs=new IntersectionObserver(function(entries){entries.forEach(function(e){if(e.isIntersecting){e.target.classList.add('wcs-fp--visible');obs.unobserve(e.target);}});},{threshold:0.1});
			document.querySelectorAll('.wcs-fp-cat-card,.wcs-fp-why__card,.wcs-trust-bar__item').forEach(function(el,i){el.style.transitionDelay=(i%4)*0.07+'s';obs.observe(el);});
		}
	})();
	</script>

<?php else :

	if ( woocommerce_product_loop() ) {
		do_action( 'woocommerce_before_shop_loop' );
		?>
		<div id="wcs-products-wrap">
		<?php
		woocommerce_product_loop_start();
		if ( wc_get_loop_prop( 'total' ) ) {
			while ( have_posts() ) {
				the_post();
				do_action( 'woocommerce_shop_loop' );
				wc_get_template_part( 'content', 'product' );
			}
		}
		woocommerce_product_loop_end();
		do_action( 'woocommerce_after_shop_loop' );
		?>
		</div><!-- /#wcs-products-wrap -->
		<?php
	} else {
		?>
		<div id="wcs-products-wrap">
		<?php do_action( 'woocommerce_no_products_found' ); ?>
		</div><!-- /#wcs-products-wrap -->
		<?php
	}

endif;

do_action( 'woocommerce_after_main_content' );
get_footer( 'shop' );
