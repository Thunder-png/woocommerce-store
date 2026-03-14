<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * WooCommerce MNG Kargo shipping method.
 */
class MNG_Kargo_Shipping_Method extends WC_Shipping_Method {

    public function __construct( $instance_id = 0 ) {
        $this->id                 = 'mng_kargo';
        $this->instance_id        = absint( $instance_id );
        $this->method_title       = __( 'MNG Kargo', 'mng-kargo-shipping' );
        $this->method_description = __( 'MNG Kargo ile gönderi oluşturma ve takip.', 'mng-kargo-shipping' );
        $this->supports           = array(
            'shipping-zones',
            'instance-settings',
            'instance-settings-modal',
        );
        $this->init();
    }

    public function init() {
        $this->init_form_fields();
        $this->init_settings();

        $this->enabled    = $this->get_option( 'enabled', 'yes' );
        $this->title      = $this->get_option( 'title', __( 'MNG Kargo', 'mng-kargo-shipping' ) );
        $this->tax_status = $this->get_option( 'tax_status', 'taxable' );
        $this->cost       = $this->get_option( 'cost', '0' );

        add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
    }

    public function init_form_fields() {
        $this->instance_form_fields = array(
            'enabled' => array(
                'title'   => __( 'Etkinleştir', 'mng-kargo-shipping' ),
                'type'    => 'checkbox',
                'label'   => __( 'MNG Kargo kargo yöntemini etkinleştir', 'mng-kargo-shipping' ),
                'default' => 'yes',
            ),
            'title'   => array(
                'title'       => __( 'Başlık', 'mng-kargo-shipping' ),
                'type'        => 'text',
                'description' => __( 'Müşteriye görünen kargo adı.', 'mng-kargo-shipping' ),
                'default'     => __( 'MNG Kargo', 'mng-kargo-shipping' ),
                'desc_tip'    => true,
            ),
            'cost'    => array(
                'title'       => __( 'Ücret', 'mng-kargo-shipping' ),
                'type'        => 'number',
                'description' => __( 'Sabit kargo ücreti (0 = ücretsiz).', 'mng-kargo-shipping' ),
                'default'     => '0',
                'min'         => '0',
                'step'        => '0.01',
                'desc_tip'    => true,
            ),
            'tax_status' => array(
                'title'   => __( 'Vergi durumu', 'mng-kargo-shipping' ),
                'type'    => 'select',
                'default' => 'taxable',
                'options' => array(
                    'taxable' => __( 'Vergilendirilebilir', 'mng-kargo-shipping' ),
                    'none'    => _x( 'Vergi yok', 'Tax status', 'mng-kargo-shipping' ),
                ),
            ),
        );
    }

    public function calculate_shipping( $package = array() ) {
        if ( 'yes' !== $this->enabled ) {
            return;
        }

        $rate = array(
            'id'       => $this->get_rate_id(),
            'label'    => $this->title,
            'cost'     => (float) $this->cost,
            'package'  => $package,
            'taxes'    => '',
            'calc_tax' => 'per_order',
        );

        $this->add_rate( $rate );
    }

    public function get_instance_form_fields() {
        return $this->instance_form_fields;
    }

}
