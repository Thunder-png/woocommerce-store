<?php
/**
 * Template Name: Çerez Politikası
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
            <h1><?php esc_html_e( 'Çerez Politikası', 'woocommerce-store-child' ); ?></h1>
        </header>

        <section>
            <h2>1. Çerez Kullanımı</h2>
            <p>Web sitemiz, kullanıcı deneyimini geliştirmek ve hizmetlerin güvenli şekilde sunulmasını sağlamak amacıyla çerezler kullanır.</p>
        </section>

        <section>
            <h2>2. Çerezlerin Kullanım Amaçları</h2>
            <ul>
                <li>Site performansını ölçmek ve iyileştirmek</li>
                <li>Kullanıcı tercihlerini hatırlamak</li>
                <li>Güvenli oturum yönetimi sağlamak</li>
                <li>Oturum açma ve ekran tercihlerini saklamak</li>
            </ul>
        </section>

        <section>
            <h2>3. Üçüncü Taraf İçerikler</h2>
            <p>Sitede gömülü içerikler (video, görsel vb.) yer alabilir. Bu içerikler ilgili üçüncü taraf sitelerin kendi çerez ve izleme politikalarına tabi olabilir.</p>
        </section>

        <section>
            <h2>4. Çerez Tercihleri</h2>
            <p>Tarayıcı ayarlarınız üzerinden çerezleri kısıtlayabilir veya tamamen devre dışı bırakabilirsiniz. Ancak bazı çerezlerin kapatılması, web sitesinin bazı işlevlerinin beklenen şekilde çalışmamasına neden olabilir.</p>
        </section>
    </article>
</main>
<?php
get_footer();
