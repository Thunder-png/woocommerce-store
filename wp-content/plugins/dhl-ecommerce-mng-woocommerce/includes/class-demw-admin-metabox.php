<?php
/**
 * Admin metabox UI.
 *
 * @package DEMW
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Order screen metabox.
 */
class DEMW_Admin_Metabox {
	/**
	 * Settings.
	 *
	 * @var DEMW_Settings
	 */
	private $settings;

	/**
	 * Order actions.
	 *
	 * @var DEMW_Order_Actions
	 */
	private $order_actions;

	/**
	 * Constructor.
	 *
	 * @param DEMW_Settings      $settings Settings.
	 * @param DEMW_Order_Actions $order_actions Actions.
	 */
	public function __construct( DEMW_Settings $settings, DEMW_Order_Actions $order_actions ) {
		$this->settings      = $settings;
		$this->order_actions = $order_actions;
	}

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function hooks() {
		add_action( 'add_meta_boxes', array( $this, 'register_metabox' ) );
		add_action( 'add_meta_boxes_woocommerce_page_wc-orders', array( $this, 'register_hpos_metabox' ) );
	}

	/**
	 * Register metabox for legacy order screen.
	 *
	 * @return void
	 */
	public function register_metabox() {
		add_meta_box(
			'demw_order_metabox',
			__( 'DHL eCommerce / MNG', 'dhl-ecommerce-mng-woocommerce' ),
			array( $this, 'render_metabox' ),
			'shop_order',
			'side',
			'high'
		);
	}

	/**
	 * Register metabox for HPOS order screen.
	 *
	 * @return void
	 */
	public function register_hpos_metabox() {
		$screen_id = function_exists( 'wc_get_page_screen_id' ) ? wc_get_page_screen_id( 'shop-order' ) : 'woocommerce_page_wc-orders';
		add_meta_box(
			'demw_order_metabox',
			__( 'DHL eCommerce / MNG', 'dhl-ecommerce-mng-woocommerce' ),
			array( $this, 'render_metabox' ),
			$screen_id,
			'side',
			'high'
		);
	}

	/**
	 * Render metabox content.
	 *
	 * @param mixed $post_or_order Post/order object.
	 * @return void
	 */
	public function render_metabox( $post_or_order ) {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			echo esc_html__( 'Insufficient permissions.', 'dhl-ecommerce-mng-woocommerce' );
			return;
		}

		$order = null;
		if ( $post_or_order instanceof WC_Order ) {
			$order = $post_or_order;
		} elseif ( $post_or_order instanceof WP_Post ) {
			$order = wc_get_order( $post_or_order->ID );
		} elseif ( is_object( $post_or_order ) && method_exists( $post_or_order, 'get_id' ) ) {
			$order = wc_get_order( $post_or_order->get_id() );
		}

		if ( ! $order instanceof WC_Order ) {
			echo esc_html__( 'Order context not found.', 'dhl-ecommerce-mng-woocommerce' );
			return;
		}

		$order_id = $order->get_id();
		$meta     = array(
			'reference_id'    => (string) $order->get_meta( '_demw_reference_id', true ),
			'shipment_id'     => (string) $order->get_meta( '_demw_shipment_id', true ),
			'tracking_number' => (string) $order->get_meta( '_demw_tracking_number', true ),
			'order_created'   => DEMW_Helpers::as_bool( $order->get_meta( '_demw_order_created', true ) ),
			'order_synced_at' => absint( $order->get_meta( '_demw_order_synced_at', true ) ),
			'label_url'       => (string) $order->get_meta( '_demw_label_url', true ),
			'last_status'     => (string) $order->get_meta( '_demw_last_status', true ),
			'last_response'   => (string) $order->get_meta( '_demw_last_response', true ),
			'last_error'      => (string) $order->get_meta( '_demw_last_error', true ),
			'last_synced_at'  => (string) $order->get_meta( '_demw_last_synced_at', true ),
		);
		$notice   = $this->order_actions->pull_order_notice( $order_id );
		$actions  = array(
			'test_connection' => $this->order_actions->get_action_url( $order_id, 'test_connection' ),
			'create_shipment' => $this->order_actions->get_action_url( $order_id, 'create_shipment' ),
			'query_status'    => $this->order_actions->get_action_url( $order_id, 'query_status' ),
			'get_label'       => $this->order_actions->get_action_url( $order_id, 'get_label' ),
		);

		require DEMW_PLUGIN_DIR . 'admin/views/order-metabox.php';
	}
}
