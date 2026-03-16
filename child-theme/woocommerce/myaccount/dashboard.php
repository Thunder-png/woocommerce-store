<?php
/**
 * My Account — Dashboard
 * Karaca File Child Theme
 */
defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_account_dashboard' );

$current_user    = wp_get_current_user();
$warranty        = wcs_get_warranty_details( get_current_user_id() );
$order_count     = wc_get_customer_order_count( get_current_user_id() );

// Son 3 siparişi çek
$recent_orders = wc_get_orders( array(
    'customer' => get_current_user_id(),
    'limit'    => 3,
    'orderby'  => 'date',
    'order'    => 'DESC',
    'status'   => array_keys( wc_get_order_statuses() ),
) );

// Sipariş durumu renk ve etiket
$status_map = array(
    'pending'    => array( 'label' => 'Beklemede',    'color' => 'amber' ),
    'processing' => array( 'label' => 'İşleniyor',    'color' => 'blue'  ),
    'on-hold'    => array( 'label' => 'Beklemede',    'color' => 'amber' ),
    'completed'  => array( 'label' => 'Tamamlandı',   'color' => 'green' ),
    'cancelled'  => array( 'label' => 'İptal',        'color' => 'red'   ),
    'refunded'   => array( 'label' => 'İade Edildi',  'color' => 'gray'  ),
    'failed'     => array( 'label' => 'Başarısız',    'color' => 'red'   ),
);
?>

<div class="wcs-ma-dash">

    <!-- ── Karşılama başlığı ─────────────────────────── -->
    <header class="wcs-ma-dash__header">
        <div class="wcs-ma-dash__header-text">
            <h1 class="wcs-ma-dash__title">
                <?php
                printf(
                    esc_html__( 'Hoş geldiniz, %s', 'woocommerce-store-child' ),
                    '<span>' . esc_html( $current_user->display_name ) . '</span>'
                );
                ?>
            </h1>
            <p class="wcs-ma-dash__subtitle">
                <?php esc_html_e( 'Siparişlerinizi, garantilerinizi ve hesap bilgilerinizi buradan yönetebilirsiniz.', 'woocommerce-store-child' ); ?>
            </p>
        </div>
        <a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="wcs-ma-dash__shop-btn">
            <i class="bi bi-grid-fill"></i>
            <?php esc_html_e( 'Alışverişe Devam', 'woocommerce-store-child' ); ?>
        </a>
    </header>

    <!-- ── Özet istatistik kartları ─────────────────── -->
    <div class="wcs-ma-dash__stats">
        <div class="wcs-ma-stat">
            <span class="wcs-ma-stat__icon wcs-ma-stat__icon--red">
                <i class="bi bi-bag-fill"></i>
            </span>
            <div>
                <span class="wcs-ma-stat__value"><?php echo absint( $order_count ); ?></span>
                <span class="wcs-ma-stat__label"><?php esc_html_e( 'Sipariş', 'woocommerce-store-child' ); ?></span>
            </div>
        </div>
        <div class="wcs-ma-stat">
            <span class="wcs-ma-stat__icon wcs-ma-stat__icon--navy">
                <i class="bi bi-shield-fill-check"></i>
            </span>
            <div>
                <span class="wcs-ma-stat__value"><?php echo $warranty ? esc_html__( 'Aktif', 'woocommerce-store-child' ) : esc_html__( 'Yok', 'woocommerce-store-child' ); ?></span>
                <span class="wcs-ma-stat__label"><?php esc_html_e( 'Garanti', 'woocommerce-store-child' ); ?></span>
            </div>
        </div>
        <div class="wcs-ma-stat">
            <span class="wcs-ma-stat__icon wcs-ma-stat__icon--green">
                <i class="bi bi-geo-alt-fill"></i>
            </span>
            <div>
                <?php
                $has_billing  = ! empty( get_user_meta( get_current_user_id(), 'billing_address_1', true ) );
                $has_shipping = ! empty( get_user_meta( get_current_user_id(), 'shipping_address_1', true ) );
                $addr_count   = (int) $has_billing + (int) $has_shipping;
                ?>
                <span class="wcs-ma-stat__value"><?php echo absint( $addr_count ); ?></span>
                <span class="wcs-ma-stat__label"><?php esc_html_e( 'Adres', 'woocommerce-store-child' ); ?></span>
            </div>
        </div>
    </div>

    <!-- ── İçerik grid ──────────────────────────────── -->
    <div class="wcs-ma-dash__grid">

        <!-- Sol: Son siparişler -->
        <section class="wcs-ma-card" aria-labelledby="wcs-ma-orders-title">
            <header class="wcs-ma-card__header">
                <h2 id="wcs-ma-orders-title" class="wcs-ma-card__title">
                    <i class="bi bi-bag-fill"></i>
                    <?php esc_html_e( 'Son Siparişler', 'woocommerce-store-child' ); ?>
                </h2>
                <a href="<?php echo esc_url( wc_get_account_endpoint_url( 'orders' ) ); ?>" class="wcs-ma-card__link">
                    <?php esc_html_e( 'Tümü', 'woocommerce-store-child' ); ?>
                    <i class="bi bi-arrow-right"></i>
                </a>
            </header>
            <div class="wcs-ma-card__body">
                <?php if ( ! empty( $recent_orders ) ) : ?>
                    <div class="wcs-ma-orders">
                        <?php foreach ( $recent_orders as $order ) :
                            $status_key   = $order->get_status();
                            $status_info  = isset( $status_map[ $status_key ] ) ? $status_map[ $status_key ] : array( 'label' => ucfirst( $status_key ), 'color' => 'gray' );
                            $item_count   = $order->get_item_count();
                            $order_date   = $order->get_date_created() ? $order->get_date_created()->date_i18n( 'd.m.Y' ) : '—';
                        ?>
                            <div class="wcs-ma-order-row">
                                <div class="wcs-ma-order-row__left">
                                    <span class="wcs-ma-order-row__num">#<?php echo esc_html( $order->get_order_number() ); ?></span>
                                    <span class="wcs-ma-order-row__date"><?php echo esc_html( $order_date ); ?></span>
                                    <span class="wcs-ma-order-row__items">
                                        <?php printf( esc_html__( '%d ürün', 'woocommerce-store-child' ), absint( $item_count ) ); ?>
                                    </span>
                                </div>
                                <div class="wcs-ma-order-row__right">
                                    <span class="wcs-ma-order-status wcs-ma-order-status--<?php echo esc_attr( $status_info['color'] ); ?>">
                                        <?php echo esc_html( $status_info['label'] ); ?>
                                    </span>
                                    <span class="wcs-ma-order-row__total"><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></span>
                                    <a href="<?php echo esc_url( $order->get_view_order_url() ); ?>" class="wcs-ma-order-row__view">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else : ?>
                    <div class="wcs-ma-empty">
                        <i class="bi bi-bag-x wcs-ma-empty__icon"></i>
                        <p><?php esc_html_e( 'Henüz siparişiniz yok.', 'woocommerce-store-child' ); ?></p>
                        <a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="wcs-ma-empty__btn">
                            <i class="bi bi-grid-fill"></i>
                            <?php esc_html_e( 'Ürünlere Göz At', 'woocommerce-store-child' ); ?>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- Sağ: Garanti + Adresler -->
        <div class="wcs-ma-dash__right">

            <!-- Garanti Kartı -->
            <section class="wcs-ma-card wcs-ma-card--warranty" aria-labelledby="wcs-ma-warranty-title">
                <header class="wcs-ma-card__header">
                    <h2 id="wcs-ma-warranty-title" class="wcs-ma-card__title">
                        <i class="bi bi-shield-fill-check"></i>
                        <?php esc_html_e( 'Garanti Bilgileri', 'woocommerce-store-child' ); ?>
                    </h2>
                </header>
                <div class="wcs-ma-card__body">
                    <?php if ( ! empty( $warranty ) ) : ?>
                        <div class="wcs-ma-warranty-active">
                            <span class="wcs-ma-warranty-badge">
                                <i class="bi bi-check-circle-fill"></i>
                                <?php esc_html_e( 'Garanti Aktif', 'woocommerce-store-child' ); ?>
                            </span>
                            <div class="wcs-ma-warranty-items">
                                <div class="wcs-ma-warranty-item">
                                    <span class="wcs-ma-warranty-item__label">
                                        <i class="bi bi-calendar-check"></i>
                                        <?php esc_html_e( 'Başlangıç', 'woocommerce-store-child' ); ?>
                                    </span>
                                    <strong><?php echo esc_html( $warranty['start_date'] ); ?></strong>
                                </div>
                                <div class="wcs-ma-warranty-item">
                                    <span class="wcs-ma-warranty-item__label">
                                        <i class="bi bi-box-seam"></i>
                                        <?php esc_html_e( 'Ürün garantisi bitiş (5 yıl)', 'woocommerce-store-child' ); ?>
                                    </span>
                                    <strong><?php echo esc_html( $warranty['product_expires'] ); ?></strong>
                                </div>
                                <div class="wcs-ma-warranty-item">
                                    <span class="wcs-ma-warranty-item__label">
                                        <i class="bi bi-tools"></i>
                                        <?php esc_html_e( 'Montaj garantisi bitiş (2 yıl)', 'woocommerce-store-child' ); ?>
                                    </span>
                                    <strong><?php echo esc_html( $warranty['installation_expires'] ); ?></strong>
                                </div>
                            </div>
                        </div>
                    <?php else : ?>
                        <div class="wcs-ma-empty wcs-ma-empty--sm">
                            <i class="bi bi-shield-x wcs-ma-empty__icon"></i>
                            <p><?php esc_html_e( 'Aktif garanti kaydınız bulunmuyor.', 'woocommerce-store-child' ); ?></p>
                            <a href="<?php echo esc_url( wcs_get_warranty_page_url() ); ?>" class="wcs-ma-empty__btn">
                                <i class="bi bi-shield-fill-check"></i>
                                <?php esc_html_e( 'Garantiyi Başlat', 'woocommerce-store-child' ); ?>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </section>

            <!-- Adresler Kartı -->
            <section class="wcs-ma-card" aria-labelledby="wcs-ma-addr-title">
                <header class="wcs-ma-card__header">
                    <h2 id="wcs-ma-addr-title" class="wcs-ma-card__title">
                        <i class="bi bi-geo-alt-fill"></i>
                        <?php esc_html_e( 'Adreslerim', 'woocommerce-store-child' ); ?>
                    </h2>
                    <a href="<?php echo esc_url( wc_get_account_endpoint_url( 'edit-address' ) ); ?>" class="wcs-ma-card__link">
                        <i class="bi bi-pencil"></i>
                        <?php esc_html_e( 'Düzenle', 'woocommerce-store-child' ); ?>
                    </a>
                </header>
                <div class="wcs-ma-card__body wcs-ma-card__body--addresses">
                    <?php
                    $address_types = array(
                        'billing'  => array( 'icon' => 'bi-file-text',  'label' => __( 'Fatura Adresi', 'woocommerce-store-child' ) ),
                        'shipping' => array( 'icon' => 'bi-truck',       'label' => __( 'Teslimat Adresi', 'woocommerce-store-child' ) ),
                    );
                    foreach ( $address_types as $type => $meta ) :
                        $address = wc_get_account_formatted_address( $type );
                    ?>
                        <div class="wcs-ma-address-card">
                            <div class="wcs-ma-address-card__head">
                                <i class="bi <?php echo esc_attr( $meta['icon'] ); ?>"></i>
                                <span><?php echo esc_html( $meta['label'] ); ?></span>
                            </div>
                            <div class="wcs-ma-address-card__body">
                                <?php if ( $address ) : ?>
                                    <?php echo wp_kses_post( $address ); ?>
                                <?php else : ?>
                                    <span class="wcs-ma-address-card__empty">
                                        <?php esc_html_e( 'Henüz adres girilmemiş.', 'woocommerce-store-child' ); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <a href="<?php echo esc_url( wc_get_account_endpoint_url( 'edit-address/' . $type ) ); ?>"
                               class="wcs-ma-address-card__edit">
                                <?php esc_html_e( 'Düzenle', 'woocommerce-store-child' ); ?>
                                <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>

        </div><!-- /.wcs-ma-dash__right -->

    </div><!-- /.wcs-ma-dash__grid -->

</div><!-- /.wcs-ma-dash -->
