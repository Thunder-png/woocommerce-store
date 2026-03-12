<?php
/**
 * Template Name: Garanti Aktivasyonu
 *
 * @package WooCommerceStoreChild
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<main id="primary" class="site-main wcs-page-main">
	<div class="wcs-page-main__inner">
		<section class="wcs-guarantee-doc">
			<div class="wcs-guarantee-doc__inner">
				<header class="wcs-guarantee-doc__header">
					<div class="wcs-guarantee-doc__eyebrow">
						<span>GARANTİ HİZMETİ</span>
					</div>
					<h1 class="wcs-guarantee-doc__title">Garanti Aktivasyonu</h1>
					<p class="wcs-guarantee-doc__subtitle">
						Satın aldığınız Karaca File ürününüz için garanti kaydını birkaç adımda tamamlayın.
					</p>
				</header>

				<p class="section-label">Genel Bilgilendirme</p>
				<div class="accordion">
					<details open>
						<summary>
							<span class="sum-icon">🛡️</span>
							Garanti Kapsamı ve Süresi
							<svg class="sum-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
								<path d="M6 9l6 6 6-6" />
							</svg>
						</summary>
						<div class="acc-body">
							<div class="tag">5 Yıl Garanti</div>
							<p>
								Karaca File güvenlik fileleri, üretim ve malzeme kaynaklı çürüme ve yırtılmalara karşı
								<strong>5 yıl garanti</strong> altındadır. Garanti, ürünün kullanım kılavuzu ve montaj
								talimatlarına uygun olarak kullanılması koşuluyla geçerlidir.
							</p>
							<p>
								Garanti süreniz, ürünün satın alındığı tarih ile başlar ve aktivasyon işlemini tamamladığınızda
								kaydınız sistemlerimize işlenir.
							</p>
							<div class="note-box">
								⚠️ Ürünün amacı dışında kullanılması, yanlış montaj yapılması veya düzenli kontrollerin
								aksatılması durumunda, güvenlik özellikleri ve garanti geçerliliği olumsuz etkilenebilir.
							</div>
						</div>
					</details>

					<details>
						<summary>
							<span class="sum-icon">ℹ️</span>
							Garanti Aktivasyonu Neden Önemli?
							<svg class="sum-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
								<path d="M6 9l6 6 6-6" />
							</svg>
						</summary>
						<div class="acc-body">
							<p>
								Garanti aktivasyonu, ürününüzle ilgili olası bir servis, bakım veya değişim talebinde
								süreçlerin daha hızlı ve şeffaf ilerlemesi için gereklidir. Kayıt altına alınan bilgiler,
								yalnızca garanti hizmetinin sunulması amacıyla kullanılmaktadır.
							</p>
							<ul>
								<li>Satın alma tarihinin ve ürün bilgilerinin doğrulanmasını sağlar.</li>
								<li>Servis ve bakım taleplerinde sizi daha hızlı bilgilendirmemize yardımcı olur.</li>
								<li>Garanti sürenizin net olarak takip edilmesini mümkün kılar.</li>
							</ul>
						</div>
					</details>
				</div>

				<p class="section-label">Aktivasyon Adımları</p>
				<div class="accordion">
					<details open>
						<summary>
							<span class="sum-icon">✅</span>
							Adım Adım Garanti Kaydı
							<svg class="sum-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
								<path d="M6 9l6 6 6-6" />
							</svg>
						</summary>
						<div class="acc-body">
							<div class="step">
								<div class="step-num">1</div>
								<div class="step-body">
									<strong>Sipariş Bilgilerinizi Hazırlayın</strong>
									<p>
										Fatura veya e-fatura üzerindeki sipariş numarası, satın alma tarihi ve satıcı bilgilerini
										hazır bulundurun.
									</p>
								</div>
							</div>
							<div class="step">
								<div class="step-num">2</div>
								<div class="step-body">
									<strong>Ürün ve Montaj Bilgilerini Girin</strong>
									<p>
										Garanti formunda sizden istenen ürün tipi, kullanım alanı (balkon, merdiven vb.) ve montaj
										tarihini eksiksiz doldurun.
									</p>
								</div>
							</div>
							<div class="step">
								<div class="step-num">3</div>
								<div class="step-body">
									<strong>İletişim ve Onay</strong>
									<p>
										İletişim bilgilerinizi paylaşın ve KVKK / aydınlatma metnini onaylayın. Başvurunuz
										onaylandığında, kayıt bilgileriniz e-posta veya SMS yoluyla size iletilir.
									</p>
								</div>
							</div>
						</div>
					</details>
				</div>

				<p class="section-label">Garanti Aktivasyon Formu</p>
				<div class="accordion">
					<details open>
						<summary>
							<span class="sum-icon">📝</span>
							Formu Doldurarak Kaydınızı Tamamlayın
							<svg class="sum-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
								<path d="M6 9l6 6 6-6" />
							</svg>
						</summary>
						<div class="acc-body">
							<?php
							// Net Order Activation plugin form (QR / sipariş aktivasyonu).
							echo do_shortcode( '[net_order_activation]' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							?>
							<p class="wcs-guarantee-doc__form-note">
								Formu tamamlayarak siparişinizi hesabınıza kaydedebilir ve garanti sürecinizi başlatabilirsiniz.
							</p>
						</div>
					</details>
				</div>
			</div>
		</section>
	</div>
</main>

<?php
get_footer();
