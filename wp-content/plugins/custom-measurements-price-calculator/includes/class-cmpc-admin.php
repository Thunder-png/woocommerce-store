<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin integration: product edit fields.
 */
class CMPC_Admin {

	/**
	 * Init hooks.
	 */
	public static function init() {
		add_action( 'woocommerce_product_options_general_product_data', array( __CLASS__, 'add_product_fields' ) );
		add_action( 'woocommerce_admin_process_product_object', array( __CLASS__, 'save_product_fields' ) );
	}

	/**
	 * Add custom fields to simple product general tab.
	 */
	public static function add_product_fields() {
		global $product_object;

		// Only show for simple products.
		if ( $product_object instanceof WC_Product && ! $product_object->is_type( 'simple' ) ) {
			return;
		}

		echo '<div class="options_group show_if_simple">';

		woocommerce_wp_checkbox(
			array(
				'id'          => CMPC_Helpers::META_ENABLE,
				'value'       => $product_object ? $product_object->get_meta( CMPC_Helpers::META_ENABLE, true ) : '',
				'label'       => __( 'Özel ölçü fiyatlandırmayı etkinleştir', 'custom-measurements-price-calculator' ),
				'description' => __( 'Bu simple ürün için fiyat, En x Boy x m² fiyatı olarak hesaplanır.', 'custom-measurements-price-calculator' ),
			)
		);

		woocommerce_wp_text_input(
			array(
				'id'                => CMPC_Helpers::META_BASE_PRICE,
				'value'             => $product_object ? $product_object->get_meta( CMPC_Helpers::META_BASE_PRICE, true ) : '',
				'label'             => __( 'm² Fiyatı', 'custom-measurements-price-calculator' ),
				'desc_tip'          => true,
				'description'       => __( 'Bu ürün için metre kare başına fiyat.', 'custom-measurements-price-calculator' ),
				'type'              => 'number',
				'custom_attributes' => array(
					'min'  => '0',
					'step' => '0.01',
				),
			)
		);

		woocommerce_wp_text_input(
			array(
				'id'                => CMPC_Helpers::META_MIN_WIDTH,
				'value'             => $product_object ? $product_object->get_meta( CMPC_Helpers::META_MIN_WIDTH, true ) : '',
				'label'             => __( 'Minimum En (m)', 'custom-measurements-price-calculator' ),
				'type'              => 'number',
				'custom_attributes' => array(
					'min'  => '0',
					'step' => '0.01',
				),
			)
		);

		woocommerce_wp_text_input(
			array(
				'id'                => CMPC_Helpers::META_MIN_HEIGHT,
				'value'             => $product_object ? $product_object->get_meta( CMPC_Helpers::META_MIN_HEIGHT, true ) : '',
				'label'             => __( 'Minimum Boy (m)', 'custom-measurements-price-calculator' ),
				'type'              => 'number',
				'custom_attributes' => array(
					'min'  => '0',
					'step' => '0.01',
				),
			)
		);

		woocommerce_wp_text_input(
			array(
				'id'                => CMPC_Helpers::META_MAX_WIDTH,
				'value'             => $product_object ? $product_object->get_meta( CMPC_Helpers::META_MAX_WIDTH, true ) : '',
				'label'             => __( 'Maksimum En (m)', 'custom-measurements-price-calculator' ),
				'type'              => 'number',
				'custom_attributes' => array(
					'min'  => '0',
					'step' => '0.01',
				),
			)
		);

		woocommerce_wp_text_input(
			array(
				'id'                => CMPC_Helpers::META_MAX_HEIGHT,
				'value'             => $product_object ? $product_object->get_meta( CMPC_Helpers::META_MAX_HEIGHT, true ) : '',
				'label'             => __( 'Maksimum Boy (m)', 'custom-measurements-price-calculator' ),
				'type'              => 'number',
				'custom_attributes' => array(
					'min'  => '0',
					'step' => '0.01',
				),
			)
		);

		echo '</div>';
	}

	/**
	 * Save product meta fields.
	 *
	 * @param WC_Product $product Product object.
	 */
	public static function save_product_fields( $product ) {
		if ( ! $product instanceof WC_Product ) {
			return;
		}

		if ( ! $product->is_type( 'simple' ) ) {
			// Clear meta if switching away from simple.
			$product->delete_meta_data( CMPC_Helpers::META_ENABLE );
			$product->delete_meta_data( CMPC_Helpers::META_BASE_PRICE );
			$product->delete_meta_data( CMPC_Helpers::META_MIN_WIDTH );
			$product->delete_meta_data( CMPC_Helpers::META_MIN_HEIGHT );
			$product->delete_meta_data( CMPC_Helpers::META_MAX_WIDTH );
			$product->delete_meta_data( CMPC_Helpers::META_MAX_HEIGHT );
			return;
		}

		$enable = isset( $_POST[ CMPC_Helpers::META_ENABLE ] ) ? 'yes' : '';
		$product->update_meta_data( CMPC_Helpers::META_ENABLE, $enable );

		$base_price = isset( $_POST[ CMPC_Helpers::META_BASE_PRICE ] ) ? wc_clean( wp_unslash( $_POST[ CMPC_Helpers::META_BASE_PRICE ] ) ) : '';
		$product->update_meta_data( CMPC_Helpers::META_BASE_PRICE, $base_price );

		$min_w = isset( $_POST[ CMPC_Helpers::META_MIN_WIDTH ] ) ? wc_clean( wp_unslash( $_POST[ CMPC_Helpers::META_MIN_WIDTH ] ) ) : '';
		$product->update_meta_data( CMPC_Helpers::META_MIN_WIDTH, $min_w );

		$min_h = isset( $_POST[ CMPC_Helpers::META_MIN_HEIGHT ] ) ? wc_clean( wp_unslash( $_POST[ CMPC_Helpers::META_MIN_HEIGHT ] ) ) : '';
		$product->update_meta_data( CMPC_Helpers::META_MIN_HEIGHT, $min_h );

		$max_w = isset( $_POST[ CMPC_Helpers::META_MAX_WIDTH ] ) ? wc_clean( wp_unslash( $_POST[ CMPC_Helpers::META_MAX_WIDTH ] ) ) : '';
		$product->update_meta_data( CMPC_Helpers::META_MAX_WIDTH, $max_w );

		$max_h = isset( $_POST[ CMPC_Helpers::META_MAX_HEIGHT ] ) ? wc_clean( wp_unslash( $_POST[ CMPC_Helpers::META_MAX_HEIGHT ] ) ) : '';
		$product->update_meta_data( CMPC_Helpers::META_MAX_HEIGHT, $max_h );
	}
}

