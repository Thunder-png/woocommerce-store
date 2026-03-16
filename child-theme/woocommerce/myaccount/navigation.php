<?php
/**
 * My Account — Navigation (sol sidebar)
 * Karaca File Child Theme
 */
defined( 'ABSPATH' ) || exit;

$current_user = wp_get_current_user();
$avatar_initials = '';
if ( $current_user->display_name ) {
    $parts = explode( ' ', trim( $current_user->display_name ) );
    $avatar_initials = strtoupper( substr( $parts[0], 0, 1 ) );
    if ( isset( $parts[1] ) ) $avatar_initials .= strtoupper( substr( $parts[1], 0, 1 ) );
}

$nav_icons = array(
    'dashboard'       => 'bi-house-fill',
    'orders'          => 'bi-bag-fill',
    'downloads'       => 'bi-download',
    'edit-address'    => 'bi-geo-alt-fill',
    'edit-account'    => 'bi-person-fill',
    'warranty'        => 'bi-shield-fill-check',
    'customer-logout' => 'bi-box-arrow-right',
);

do_action( 'woocommerce_before_account_navigation' );
?>

<aside class="wcs-ma-nav" aria-label="<?php esc_attr_e( 'Hesap menüsü', 'woocommerce-store-child' ); ?>">

    <!-- Kullanıcı profil kartı -->
    <div class="wcs-ma-nav__profile">
        <div class="wcs-ma-nav__avatar" aria-hidden="true">
            <?php echo esc_html( $avatar_initials ?: '?' ); ?>
        </div>
        <div class="wcs-ma-nav__profile-info">
            <span class="wcs-ma-nav__name"><?php echo esc_html( $current_user->display_name ); ?></span>
            <span class="wcs-ma-nav__email"><?php echo esc_html( $current_user->user_email ); ?></span>
        </div>
    </div>

    <!-- Navigasyon linkleri -->
    <nav>
        <ul class="wcs-ma-nav__list">
            <?php foreach ( wc_get_account_menu_items() as $endpoint => $label ) :
                $icon    = isset( $nav_icons[ $endpoint ] ) ? $nav_icons[ $endpoint ] : 'bi-chevron-right';
                $classes = wc_get_account_menu_item_classes( $endpoint );
                $is_logout = 'customer-logout' === $endpoint;
            ?>
                <li class="wcs-ma-nav__item <?php echo esc_attr( $classes ); ?><?php echo $is_logout ? ' wcs-ma-nav__item--logout' : ''; ?>">
                    <a href="<?php echo esc_url( wc_get_account_endpoint_url( $endpoint ) ); ?>"
                       class="wcs-ma-nav__link">
                        <i class="bi <?php echo esc_attr( $icon ); ?> wcs-ma-nav__icon" aria-hidden="true"></i>
                        <span><?php echo esc_html( $label ); ?></span>
                        <?php if ( ! $is_logout ) : ?>
                            <i class="bi bi-chevron-right wcs-ma-nav__arrow" aria-hidden="true"></i>
                        <?php endif; ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </nav>

    <!-- Güven rozetleri -->
    <div class="wcs-ma-nav__trust">
        <span class="wcs-ma-nav__trust-item">
            <i class="bi bi-shield-fill-check"></i>
            <?php esc_html_e( 'CE Belgeli', 'woocommerce-store-child' ); ?>
        </span>
        <span class="wcs-ma-nav__trust-item">
            <i class="bi bi-lock-fill"></i>
            <?php esc_html_e( 'SSL Güvenli', 'woocommerce-store-child' ); ?>
        </span>
    </div>

</aside>

<?php do_action( 'woocommerce_after_account_navigation' ); ?>
