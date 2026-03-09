<?php
/**
 * Template Name: Mesafeli Satış Sözleşmesi
 * Template Post Type: page
 *
 * @package WooCommerce_Store_Child
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="primary" class="site-main wcs-policy-page">
    <article class="wcs-policy-content">
        <header class="wcs-policy-header">
            <h1><?php esc_html_e( 'Mesafeli Satış Sözleşmesi', 'woocommerce-store-child' ); ?></h1>
        </header>

        <section>
            <h2>1. Taraflar</h2>
            <p><strong>Satıcı:</strong> BY KARACA İŞ GÜVENLİĞİ İNŞAAT MOBİLYA GAYRİMENKUL GIDA OTOMOTİV SANAYİ VE TİCARET LİMİTED ŞİRKETİ</p>
            <p><strong>Adres:</strong> Aşağı Eğlence Mah, Etlik, Gen. Dr. Tevfik Sağlam Cd. No:59/93, 06110 Keçiören/Ankara</p>
            <p><strong>E-posta:</strong> <a href="mailto:info@bykaracafile.com.tr">info@bykaracafile.com.tr</a></p>
            <p><strong>Telefon:</strong> 0850 380 20 06</p>
        </section>

        <section>
            <h2>2. Konu</h2>
            <p>Bu sözleşme, alıcının satıcıya ait internet sitesi üzerinden elektronik ortamda sipariş verdiği ürünün satışı, teslimi, iade ve iptal koşullarına ilişkin hak ve yükümlülükleri düzenler.</p>
        </section>

        <section>
            <h2>3. Sipariş, Ödeme ve Teslimat</h2>
            <p>Siparişin onaylanması ile birlikte alıcı, siparişe konu ürün bedelini ve varsa ek ücretleri ödeme yükümlülüğü altına girer.</p>
            <p>Ödemeler güvenli ödeme altyapıları üzerinden alınır. Siparişler ödeme onayı sonrasında işleme alınır ve kargo firması aracılığıyla teslim edilir.</p>
        </section>

        <section>
            <h2>4. Cayma, İptal ve İade Koşulları</h2>
            <p>Siparişler kargoya verilmeden önce iptal edilebilir. Kargoya verilen siparişlerde iptal mümkün olmayabilir.</p>
            <p>İade için ürünün kullanılmamış, zarar görmemiş ve orijinal ambalajında olması gerekir. Hijyen koşulları nedeniyle açılmış/kullanılmış ürünlerde iade kabul edilmeyebilir.</p>
        </section>

        <section>
            <h2>5. Uyuşmazlık ve Yürürlük</h2>
            <p>Taraflar, sözleşme kapsamında doğabilecek uyuşmazlıklarda yürürlükteki mevzuat hükümlerinin uygulanacağını kabul eder. Alıcı, sipariş oluşturduğunda bu sözleşme hükümlerini elektronik ortamda onaylamış sayılır.</p>
        </section>
    </article>
</main>
<?php
get_footer();
