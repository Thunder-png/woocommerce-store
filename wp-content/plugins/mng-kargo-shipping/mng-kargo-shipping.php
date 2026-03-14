<?php
/**
 * Plugin Name: MNG Kargo Shipping for WooCommerce
 * Description: WooCommerce siparişlerini MNG Kargo API ile entegre eder; checkout'ta MNG kargo seçeneği, otomatik gönderi oluşturma, barkod ve takip bilgisi sağlar.
 * Author:      bykaraca
 * Version:     1.0.0
 * Text Domain: mng-kargo-shipping
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! defined( 'MNG_KARGO_SHIPPING_VERSION' ) ) {
    define( 'MNG_KARGO_SHIPPING_VERSION', '1.0.0' );
}

if ( ! defined( 'MNG_KARGO_SHIPPING_PLUGIN_FILE' ) ) {
    define( 'MNG_KARGO_SHIPPING_PLUGIN_FILE', __FILE__ );
}

if ( ! defined( 'MNG_KARGO_SHIPPING_PATH' ) ) {
    define( 'MNG_KARGO_SHIPPING_PATH', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'MNG_KARGO_SHIPPING_URL' ) ) {
    define( 'MNG_KARGO_SHIPPING_URL', plugin_dir_url( __FILE__ ) );
}

/**
 * Basit autoloader.
 */
function mng_kargo_shipping_autoload( $class ) {
    if ( 0 !== strpos( $class, 'MNG_Kargo_' ) ) {
        return;
    }

    if ( 'MNG_Kargo_API_Client' === $class ) {
        $file = 'mng-kargo-api-client';
    } else {
        $file = strtolower( str_replace( array( 'MNG_Kargo_', '_' ), array( '', '-' ), $class ) );
    }
    $path = MNG_KARGO_SHIPPING_PATH . 'includes/class-' . $file . '.php';

    if ( file_exists( $path ) ) {
        require_once $path;
    }
}
spl_autoload_register( 'mng_kargo_shipping_autoload' );

/**
 * WooCommerce gerekli mi?
 */
function mng_kargo_shipping_missing_wc_notice() {
    if ( ! current_user_can( 'activate_plugins' ) ) {
        return;
    }

    echo '<div class="notice notice-error"><p>';
    esc_html_e( 'MNG Kargo Shipping eklentisi çalışmak için WooCommerce gerektirir.', 'mng-kargo-shipping' );
    echo '</p></div>';
}

function mng_kargo_shipping_check_dependencies() {
    if ( ! class_exists( 'WooCommerce' ) ) {
        add_action( 'admin_notices', 'mng_kargo_shipping_missing_wc_notice' );
    }
}
add_action( 'plugins_loaded', 'mng_kargo_shipping_check_dependencies', 5 );

/**
 * Ana bootstrap.
 */
function mng_kargo_shipping_init() {
    if ( ! class_exists( 'WooCommerce' ) ) {
        return;
    }

    // Shipping method'i kaydet.
    add_filter(
        'woocommerce_shipping_methods',
        function ( $methods ) {
            $methods['mng_kargo'] = 'MNG_Kargo_Shipping_Method';
            return $methods;
        }
    );

    // API client, order handler, admin metaboxlarını hazırla.
    if ( class_exists( 'MNG_Kargo_API_Client' ) ) {
        MNG_Kargo_API_Client::instance();
    }

    if ( class_exists( 'MNG_Kargo_Order_Handler' ) ) {
        MNG_Kargo_Order_Handler::instance();
    }

    if ( is_admin() && class_exists( 'MNG_Kargo_Admin_Metabox' ) ) {
        MNG_Kargo_Admin_Metabox::instance();
    }
}
add_action( 'woocommerce_shipping_init', 'mng_kargo_shipping_init' );

/**
 * Metin alanı yükleme.
 */
function mng_kargo_shipping_load_textdomain() {
    load_plugin_textdomain(
        'mng-kargo-shipping',
        false,
        dirname( plugin_basename( __FILE__ ) ) . '/languages/'
    );
}
add_action( 'init', 'mng_kargo_shipping_load_textdomain' );

/**
 * WooCommerce menüsüne MNG Kargo ayar sayfası ekler.
 */
function mng_kargo_shipping_add_settings_page() {
    if ( ! class_exists( 'WooCommerce' ) ) {
        return;
    }
    add_submenu_page(
        'woocommerce',
        __( 'MNG Kargo Ayarları', 'mng-kargo-shipping' ),
        __( 'MNG Kargo', 'mng-kargo-shipping' ),
        'manage_woocommerce',
        'mng-kargo-shipping',
        'mng_kargo_shipping_render_settings_page'
    );
}
add_action( 'admin_menu', 'mng_kargo_shipping_add_settings_page', 99 );

function mng_kargo_shipping_render_settings_page() {
    if ( ! class_exists( 'MNG_Kargo_API_Client' ) ) {
        $path = MNG_KARGO_SHIPPING_PATH . 'includes/class-mng-kargo-api-client.php';
        if ( file_exists( $path ) ) {
            require_once $path;
        }
    }
    if ( ! class_exists( 'MNG_Kargo_API_Client' ) ) {
        echo '<div class="wrap"><p>' . esc_html__( 'MNG Kargo API sınıfı yüklenemedi.', 'mng-kargo-shipping' ) . '</p></div>';
        return;
    }
    $option_key = MNG_Kargo_API_Client::OPTION_KEY;
    $saved = get_option( $option_key, array() );
    if ( ! is_array( $saved ) ) {
        $saved = array();
    }
    $defaults = array(
        'customer_number'       => '',
        'password'             => '',
        'ibm_client_id'        => '',
        'ibm_client_secret'    => '',
        'api_env'              => 'test',
        'shipment_service_type' => '1',
        'payment_type'         => '1',
    );
    $settings = wp_parse_args( $saved, $defaults );

    if ( isset( $_POST['mng_kargo_save'] ) && check_admin_referer( 'mng_kargo_settings' ) && current_user_can( 'manage_woocommerce' ) ) {
        $settings = array(
            'customer_number'       => isset( $_POST['customer_number'] ) ? sanitize_text_field( wp_unslash( $_POST['customer_number'] ) ) : '',
            'password'             => isset( $_POST['password'] ) ? sanitize_text_field( wp_unslash( $_POST['password'] ) ) : '',
            'ibm_client_id'        => isset( $_POST['ibm_client_id'] ) ? sanitize_text_field( wp_unslash( $_POST['ibm_client_id'] ) ) : '',
            'ibm_client_secret'    => isset( $_POST['ibm_client_secret'] ) ? sanitize_text_field( wp_unslash( $_POST['ibm_client_secret'] ) ) : '',
            'api_env'              => isset( $_POST['api_env'] ) && 'prod' === $_POST['api_env'] ? 'prod' : 'test',
            'shipment_service_type' => isset( $_POST['shipment_service_type'] ) ? sanitize_text_field( wp_unslash( $_POST['shipment_service_type'] ) ) : '1',
            'payment_type'         => isset( $_POST['payment_type'] ) ? sanitize_text_field( wp_unslash( $_POST['payment_type'] ) ) : '1',
        );
        update_option( $option_key, $settings );
        delete_transient( MNG_Kargo_API_Client::TOKEN_TRANSIENT_KEY );
        echo '<div class="notice notice-success"><p>' . esc_html__( 'Ayarlar kaydedildi.', 'mng-kargo-shipping' ) . '</p></div>';
    }
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'MNG Kargo API Ayarları', 'mng-kargo-shipping' ); ?></h1>
        <p><?php esc_html_e( 'WooCommerce → Ayarlar → Kargo bölümünden bir bölgeye "MNG Kargo" ekleyin. Gönderi oluşturma için aşağıdaki bilgileri doldurun.', 'mng-kargo-shipping' ); ?></p>
        <form method="post" action="">
            <?php wp_nonce_field( 'mng_kargo_settings' ); ?>
            <table class="form-table">
                <tr>
                    <th><label for="customer_number"><?php esc_html_e( 'Müşteri numarası', 'mng-kargo-shipping' ); ?></label></th>
                    <td><input type="text" id="customer_number" name="customer_number" value="<?php echo esc_attr( $settings['customer_number'] ); ?>" class="regular-text" /></td>
                </tr>
                <tr>
                    <th><label for="password"><?php esc_html_e( 'Şifre', 'mng-kargo-shipping' ); ?></label></th>
                    <td><input type="password" id="password" name="password" value="<?php echo esc_attr( $settings['password'] ); ?>" class="regular-text" /></td>
                </tr>
                <tr>
                    <th><label for="ibm_client_id"><?php esc_html_e( 'IBM Client ID', 'mng-kargo-shipping' ); ?></label></th>
                    <td><input type="text" id="ibm_client_id" name="ibm_client_id" value="<?php echo esc_attr( $settings['ibm_client_id'] ); ?>" class="regular-text" /></td>
                </tr>
                <tr>
                    <th><label for="ibm_client_secret"><?php esc_html_e( 'IBM Client Secret', 'mng-kargo-shipping' ); ?></label></th>
                    <td><input type="password" id="ibm_client_secret" name="ibm_client_secret" value="<?php echo esc_attr( $settings['ibm_client_secret'] ); ?>" class="regular-text" /></td>
                </tr>
                <tr>
                    <th><label for="api_env"><?php esc_html_e( 'API ortamı', 'mng-kargo-shipping' ); ?></label></th>
                    <td>
                        <select id="api_env" name="api_env">
                            <option value="test" <?php selected( $settings['api_env'], 'test' ); ?>><?php esc_html_e( 'Test', 'mng-kargo-shipping' ); ?></option>
                            <option value="prod" <?php selected( $settings['api_env'], 'prod' ); ?>><?php esc_html_e( 'Canlı', 'mng-kargo-shipping' ); ?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><label for="shipment_service_type"><?php esc_html_e( 'Hizmet tipi', 'mng-kargo-shipping' ); ?></label></th>
                    <td>
                        <select id="shipment_service_type" name="shipment_service_type">
                            <option value="1" <?php selected( $settings['shipment_service_type'], '1' ); ?>><?php esc_html_e( 'Standart', 'mng-kargo-shipping' ); ?></option>
                            <option value="7" <?php selected( $settings['shipment_service_type'], '7' ); ?>><?php esc_html_e( 'Gün içi', 'mng-kargo-shipping' ); ?></option>
                            <option value="8" <?php selected( $settings['shipment_service_type'], '8' ); ?>><?php esc_html_e( 'Akşam', 'mng-kargo-shipping' ); ?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><label for="payment_type"><?php esc_html_e( 'Ödeme tarafı', 'mng-kargo-shipping' ); ?></label></th>
                    <td>
                        <select id="payment_type" name="payment_type">
                            <option value="1" <?php selected( $settings['payment_type'], '1' ); ?>><?php esc_html_e( 'Gönderici öder', 'mng-kargo-shipping' ); ?></option>
                            <option value="2" <?php selected( $settings['payment_type'], '2' ); ?>><?php esc_html_e( 'Alıcı öder', 'mng-kargo-shipping' ); ?></option>
                            <option value="3" <?php selected( $settings['payment_type'], '3' ); ?>><?php esc_html_e( 'Platform öder', 'mng-kargo-shipping' ); ?></option>
                        </select>
                    </td>
                </tr>
            </table>
            <p class="submit">
                <button type="submit" name="mng_kargo_save" class="button button-primary"><?php esc_html_e( 'Kaydet', 'mng-kargo-shipping' ); ?></button>
            </p>
        </form>
    </div>
    <?php
}

