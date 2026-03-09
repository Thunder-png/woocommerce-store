<?php
/**
 * Policy page view: Ödeme ve Teslimat Politikası.
 *
 * @package WooCommerce_Store_Child
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="primary" class="site-main wcs-policy-page">
    <article class="wcs-policy-content">
        <header class="wcs-policy-header">
            <h1><?php esc_html_e( 'Ödeme ve Teslimat Politikası', 'woocommerce-store-child' ); ?></h1>
        </header>

        <section>
            <h2>1. Ödeme Güvenliği</h2>
            <p>Web sitemizde gerçekleştirilen tüm ödeme işlemleri SSL (Secure Socket Layer) güvenlik sertifikası ile korunmaktadır.</p>
        </section>

        <section>
            <h2>2. Kart Bilgilerinin Korunması</h2>
            <p>Kredi kartı veya banka kartı bilgileriniz firma tarafından saklanmaz, kaydedilmez veya doğrudan erişilmez. Ödeme işlemleri yetkili ödeme kuruluşlarının güvenli altyapısı üzerinden yürütülür.</p>
        </section>

        <section>
            <h2>3. Kullanılan Ödeme Altyapıları</h2>
            <ul>
                <li>Sanal POS hizmeti sağlayıcıları</li>
                <li>Bankalar</li>
                <li>Lisanslı ödeme kuruluşları</li>
            </ul>
        </section>

        <section>
            <h2>4. Havale / EFT İşlemleri</h2>
            <p>Havale/EFT ile yapılacak ödemelerde yalnızca BY KARACA İŞ GÜVENLİĞİ İNŞAAT MOBİLYA GAYRİMENKUL GIDA OTOMOTİV SANAYİ VE TİCARET LİMİTED ŞİRKETİ'ne ait resmi hesaplar kullanılmalıdır.</p>
        </section>

        <section>
            <h2>5. Teslimat ve Kargo</h2>
            <p>Siparişleriniz ödeme onayı sonrasında hazırlanır ve anlaşmalı kargo firmaları ile teslimata çıkarılır. Teslimat adresi bilgilerinin doğru ve eksiksiz girilmesi müşterinin sorumluluğundadır.</p>
            <p>İade süreçlerinde kargo ücretleri iade nedenine göre değişiklik gösterebilir; kusurlu ürünlerde iade kargo bedeli firma tarafından karşılanır.</p>
        </section>
    </article>
</main>
<?php
get_footer();
