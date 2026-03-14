<?php
/**
 * Shop header — Karaca File Child Theme.
 * Lottie logo (çerçevesiz), e-ticaret nav, mega kategoriler, mobil menü.
 */

defined( 'ABSPATH' ) || exit;

$lottie_url = get_stylesheet_directory_uri() . '/assets/branding/logo/logo-lottie-version.json';

$nav_links = array(
    array(
        'label'    => __( 'Ürünler', 'woocommerce-store-child' ),
        'url'      => wc_get_page_permalink( 'shop' ),
        'children' => array(
            array( 'label' => __( 'Balkon Güvenlik Filesi', 'woocommerce-store-child' ), 'slug' => 'balkon-guvenlik-filesi', 'icon' => 'bi-house-door' ),
            array( 'label' => __( 'Çocuk Filesi',           'woocommerce-store-child' ), 'slug' => 'cocuk-filesi-file-urunleri', 'icon' => 'bi-person-hearts' ),
            array( 'label' => __( 'Havuz Filesi',           'woocommerce-store-child' ), 'slug' => 'havuz-filesi-file-urunleri', 'icon' => 'bi-water' ),
            array( 'label' => __( 'Kedi Filesi',            'woocommerce-store-child' ), 'slug' => 'kedi-filesi-file-urunleri',  'icon' => 'bi-heart' ),
            array( 'label' => __( 'Merdiven Filesi',        'woocommerce-store-child' ), 'slug' => 'merdiven-filesi',            'icon' => 'bi-ladder' ),
        ),
    ),
    array(
        'label' => __( 'Özel Ölçü', 'woocommerce-store-child' ),
        'url'   => add_query_arg( 'product_tag', 'ozel-olcu', wc_get_page_permalink( 'shop' ) ),
    ),
    array(
        'label' => __( 'Montaj & Keşif', 'woocommerce-store-child' ),
        'url'   => home_url( '/montaj-kesif/' ),
    ),
    array(
        'label' => __( 'Blog', 'woocommerce-store-child' ),
        'url'   => home_url( '/blog/' ),
    ),
    array(
        'label' => __( 'İletişim', 'woocommerce-store-child' ),
        'url'   => home_url( '/iletisim/' ),
    ),
);

$current_url = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
?>

<header class="wcs-header" id="wcs-header" aria-label="<?php esc_attr_e( 'Site başlığı', 'woocommerce-store-child' ); ?>">
    <div class="wcs-header__inner">

        <!-- ── LOGO ─────────────────────────────────────── -->
        <a class="wcs-header__brand" href="<?php echo esc_url( home_url( '/' ) ); ?>"
           aria-label="<?php esc_attr_e( 'Karaca File — Ana sayfa', 'woocommerce-store-child' ); ?>">
            <span class="wcs-header__lottie"
                  data-wcs-lottie
                  data-lottie-src="<?php echo esc_url( $lottie_url ); ?>"
                  aria-hidden="true"></span>
        </a>

        <!-- ── NAV ──────────────────────────────────────── -->
        <nav class="wcs-header__nav" aria-label="<?php esc_attr_e( 'Ana menü', 'woocommerce-store-child' ); ?>">
            <?php foreach ( $nav_links as $link ) :
                $has_children = ! empty( $link['children'] );
                $is_active    = strpos( $current_url, $link['url'] ) !== false;
            ?>
                <?php if ( $has_children ) : ?>
                    <div class="wcs-header__nav-item wcs-header__nav-item--has-dropdown">
                        <a href="<?php echo esc_url( $link['url'] ); ?>"
                           class="wcs-header__nav-link<?php echo $is_active ? ' wcs-header__nav-link--active' : ''; ?>"
                           aria-haspopup="true" aria-expanded="false">
                            <?php echo esc_html( $link['label'] ); ?>
                            <i class="bi bi-chevron-down wcs-header__nav-chevron" aria-hidden="true"></i>
                        </a>
                        <div class="wcs-header__dropdown" role="menu">
                            <?php foreach ( $link['children'] as $child ) :
                                $term      = get_term_by( 'slug', $child['slug'], 'product_cat' );
                                $child_url = $term ? get_term_link( $term ) : wc_get_page_permalink( 'shop' );
                                if ( is_wp_error( $child_url ) ) $child_url = wc_get_page_permalink( 'shop' );
                            ?>
                                <a href="<?php echo esc_url( $child_url ); ?>"
                                   class="wcs-header__dropdown-item" role="menuitem">
                                    <span class="wcs-header__dropdown-icon" aria-hidden="true">
                                        <i class="bi <?php echo esc_attr( $child['icon'] ); ?>"></i>
                                    </span>
                                    <span><?php echo esc_html( $child['label'] ); ?></span>
                                    <i class="bi bi-arrow-right wcs-header__dropdown-arrow" aria-hidden="true"></i>
                                </a>
                            <?php endforeach; ?>
                            <a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"
                               class="wcs-header__dropdown-all" role="menuitem">
                                <i class="bi bi-grid" aria-hidden="true"></i>
                                <?php esc_html_e( 'Tüm Ürünleri Gör', 'woocommerce-store-child' ); ?>
                                <i class="bi bi-arrow-right" aria-hidden="true"></i>
                            </a>
                        </div>
                    </div>
                <?php else : ?>
                    <a href="<?php echo esc_url( $link['url'] ); ?>"
                       class="wcs-header__nav-link<?php echo $is_active ? ' wcs-header__nav-link--active' : ''; ?>">
                        <?php echo esc_html( $link['label'] ); ?>
                    </a>
                <?php endif; ?>
            <?php endforeach; ?>
        </nav>

        <!-- ── SAĞ KISIM ─────────────────────────────────── -->
        <div class="wcs-header__right">

            <!-- Arama -->
            <div class="wcs-header__search-wrap" id="wcs-search-wrap">
                <form role="search" method="get" class="wcs-header__search-form"
                      action="<?php echo esc_url( home_url( '/' ) ); ?>">
                    <input type="search"
                           class="wcs-header__search-input"
                           id="wcs-search-input"
                           placeholder="<?php esc_attr_e( 'Ürün ara…', 'woocommerce-store-child' ); ?>"
                           value="<?php echo get_search_query(); ?>"
                           name="s"
                           aria-label="<?php esc_attr_e( 'Ürün ara', 'woocommerce-store-child' ); ?>">
                    <input type="hidden" name="post_type" value="product">
                    <button type="submit" class="wcs-header__search-submit"
                            aria-label="<?php esc_attr_e( 'Ara', 'woocommerce-store-child' ); ?>">
                        <i class="bi bi-search" aria-hidden="true"></i>
                    </button>
                </form>
            </div>

            <button class="wcs-header__icon-btn" id="wcs-search-toggle"
                    aria-label="<?php esc_attr_e( 'Aramayı aç', 'woocommerce-store-child' ); ?>"
                    aria-expanded="false">
                <i class="bi bi-search" aria-hidden="true"></i>
            </button>

            <!-- Hesabım -->
            <?php if ( function_exists( 'wc_get_page_permalink' ) ) : ?>
                <a class="wcs-header__icon-btn" href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>"
                   aria-label="<?php esc_attr_e( 'Hesabım', 'woocommerce-store-child' ); ?>">
                    <i class="bi bi-person" aria-hidden="true"></i>
                </a>
            <?php endif; ?>

            <!-- Sepet -->
            <?php if ( function_exists( 'wc_get_page_permalink' ) ) : ?>
                <a class="wcs-header__icon-btn wcs-header__cart-btn"
                   href="<?php echo esc_url( wc_get_page_permalink( 'cart' ) ); ?>"
                   aria-label="<?php esc_attr_e( 'Sepeti görüntüle', 'woocommerce-store-child' ); ?>"
                   data-wcs-cart-toggle="true">
                    <i class="bi bi-cart3" aria-hidden="true"></i>
                    <?php
                    $count = function_exists( 'WC' ) && WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
                    if ( $count > 0 ) :
                    ?>
                        <span class="wcs-header__cart-badge" aria-label="<?php echo esc_attr( $count ); ?> ürün"><?php echo absint( $count ); ?></span>
                    <?php endif; ?>
                </a>
            <?php endif; ?>

            <!-- Mobil hamburger -->
            <button class="wcs-header__hamburger" id="wcs-hamburger"
                    aria-label="<?php esc_attr_e( 'Menüyü aç', 'woocommerce-store-child' ); ?>"
                    aria-expanded="false"
                    aria-controls="wcs-mobile-menu">
                <span></span><span></span><span></span>
            </button>
        </div>

    </div><!-- /.wcs-header__inner -->

    <!-- ── MOBİL MENÜ ──────────────────────────────────── -->
    <div class="wcs-header__mobile" id="wcs-mobile-menu" aria-hidden="true">
        <nav class="wcs-header__mobile-nav" aria-label="<?php esc_attr_e( 'Mobil menü', 'woocommerce-store-child' ); ?>">
            <?php foreach ( $nav_links as $link ) :
                $has_children = ! empty( $link['children'] );
            ?>
                <?php if ( $has_children ) : ?>
                    <div class="wcs-header__mobile-group">
                        <button class="wcs-header__mobile-link wcs-header__mobile-toggle" aria-expanded="false">
                            <?php echo esc_html( $link['label'] ); ?>
                            <i class="bi bi-chevron-down" aria-hidden="true"></i>
                        </button>
                        <div class="wcs-header__mobile-sub" hidden>
                            <?php foreach ( $link['children'] as $child ) :
                                $term      = get_term_by( 'slug', $child['slug'], 'product_cat' );
                                $child_url = $term ? get_term_link( $term ) : wc_get_page_permalink( 'shop' );
                                if ( is_wp_error( $child_url ) ) $child_url = wc_get_page_permalink( 'shop' );
                            ?>
                                <a href="<?php echo esc_url( $child_url ); ?>" class="wcs-header__mobile-sub-link">
                                    <i class="bi <?php echo esc_attr( $child['icon'] ); ?>" aria-hidden="true"></i>
                                    <?php echo esc_html( $child['label'] ); ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php else : ?>
                    <a href="<?php echo esc_url( $link['url'] ); ?>" class="wcs-header__mobile-link">
                        <?php echo esc_html( $link['label'] ); ?>
                    </a>
                <?php endif; ?>
            <?php endforeach; ?>

            <div class="wcs-header__mobile-actions">
                <a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" class="wcs-header__mobile-action-btn">
                    <i class="bi bi-person" aria-hidden="true"></i>
                    <?php esc_html_e( 'Hesabım', 'woocommerce-store-child' ); ?>
                </a>
                <a href="<?php echo esc_url( wc_get_page_permalink( 'cart' ) ); ?>" class="wcs-header__mobile-action-btn">
                    <i class="bi bi-cart3" aria-hidden="true"></i>
                    <?php esc_html_e( 'Sepet', 'woocommerce-store-child' ); ?>
                </a>
            </div>
        </nav>
    </div>

</header><!-- /.wcs-header -->

<script>
(function () {
    'use strict';

    // ── Scroll: sticky + shrink ──────────────────────────
    var header = document.getElementById('wcs-header');
    if (header) {
        window.addEventListener('scroll', function () {
            header.classList.toggle('wcs-header--scrolled', window.scrollY > 60);
        }, { passive: true });
    }

    // ── Hamburger → mobil menü ───────────────────────────
    var ham   = document.getElementById('wcs-hamburger');
    var menu  = document.getElementById('wcs-mobile-menu');
    if (ham && menu) {
        ham.addEventListener('click', function () {
            var open = ham.getAttribute('aria-expanded') === 'true';
            ham.setAttribute('aria-expanded', !open);
            menu.setAttribute('aria-hidden', open);
            ham.classList.toggle('wcs-header__hamburger--open', !open);
            menu.classList.toggle('wcs-header__mobile--open', !open);
            document.body.classList.toggle('wcs-no-scroll', !open);
        });
    }

    // ── Mobil accordion ─────────────────────────────────
    document.querySelectorAll('.wcs-header__mobile-toggle').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var sub  = btn.nextElementSibling;
            var open = btn.getAttribute('aria-expanded') === 'true';
            btn.setAttribute('aria-expanded', !open);
            if (open) { sub.hidden = true; } else { sub.hidden = false; }
        });
    });

    // ── Search toggle ────────────────────────────────────
    var searchToggle = document.getElementById('wcs-search-toggle');
    var searchWrap   = document.getElementById('wcs-search-wrap');
    var searchInput  = document.getElementById('wcs-search-input');
    if (searchToggle && searchWrap) {
        searchToggle.addEventListener('click', function () {
            var open = searchWrap.classList.toggle('wcs-header__search-wrap--open');
            searchToggle.setAttribute('aria-expanded', open);
            if (open && searchInput) { setTimeout(function () { searchInput.focus(); }, 80); }
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                searchWrap.classList.remove('wcs-header__search-wrap--open');
                searchToggle.setAttribute('aria-expanded', false);
            }
        });
    }

    // ── Dropdown hover + focus ───────────────────────────
    document.querySelectorAll('.wcs-header__nav-item--has-dropdown').forEach(function (item) {
        var link = item.querySelector('.wcs-header__nav-link');
        item.addEventListener('mouseenter', function () {
            link && link.setAttribute('aria-expanded', 'true');
        });
        item.addEventListener('mouseleave', function () {
            link && link.setAttribute('aria-expanded', 'false');
        });
    });

    // ── Lottie init (tüm sayfalarda) ────────────────────
    function initHeaderLottie() {
        var containers = document.querySelectorAll('[data-wcs-lottie]');
        if (!containers.length || !window.lottie) return;
        containers.forEach(function (el) {
            if (el.getAttribute('data-wcs-lottie-ready') === '1') return;
            var src = el.getAttribute('data-lottie-src');
            if (!src) return;
            window.lottie.loadAnimation({
                container: el,
                renderer:  'svg',
                loop:      true,
                autoplay:  true,
                path:      src,
            });
            el.setAttribute('data-wcs-lottie-ready', '1');
        });
    }

    if (window.lottie) {
        initHeaderLottie();
    } else {
        window.addEventListener('load', function () {
            if (window.lottie) initHeaderLottie();
        });
        // Fallback: poll kısa süre
        var attempts = 0;
        var poll = setInterval(function () {
            if (window.lottie) { initHeaderLottie(); clearInterval(poll); }
            if (++attempts > 20) clearInterval(poll);
        }, 150);
    }

})();
</script>
