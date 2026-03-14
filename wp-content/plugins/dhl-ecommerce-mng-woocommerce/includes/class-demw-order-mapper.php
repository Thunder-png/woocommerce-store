<?php
/**
 * Order mapping layer.
 *
 * @package DEMW
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Converts WooCommerce orders to shipment payload.
 */
class DEMW_Order_Mapper {
	/**
	 * Map order data to API payload.
	 *
	 * @param WC_Order $order Order object.
	 * @return array<string,mixed>|WP_Error
	 */
	public function map_order_to_shipment_payload( WC_Order $order ) {
		$recipient_name  = trim( $order->get_formatted_shipping_full_name() );
		$recipient_phone = trim( (string) $order->get_shipping_phone() );
		$recipient_email = trim( (string) $order->get_billing_email() );

		if ( '' === $recipient_name ) {
			$recipient_name = trim( $order->get_formatted_billing_full_name() );
		}
		if ( '' === $recipient_phone ) {
			$recipient_phone = trim( (string) $order->get_billing_phone() );
		}

		$address_1 = trim( (string) $order->get_shipping_address_1() );
		$address_2 = trim( (string) $order->get_shipping_address_2() );
		$city      = trim( (string) $order->get_shipping_city() );
		$state     = trim( (string) $order->get_shipping_state() );
		$postcode  = trim( (string) $order->get_shipping_postcode() );
		$country   = trim( (string) $order->get_shipping_country() );

		if ( '' === $address_1 ) {
			$address_1 = trim( (string) $order->get_billing_address_1() );
			$address_2 = trim( (string) $order->get_billing_address_2() );
			$city      = trim( (string) $order->get_billing_city() );
			$state     = trim( (string) $order->get_billing_state() );
			$postcode  = trim( (string) $order->get_billing_postcode() );
			$country   = trim( (string) $order->get_billing_country() );
		}

		if ( '' === $recipient_name || '' === $recipient_phone || '' === $address_1 || '' === $city || '' === $country ) {
			return new WP_Error(
				'demw_incomplete_address',
				__( 'Order is missing required recipient/address information (name, phone, address, city, country).', 'dhl-ecommerce-mng-woocommerce' )
			);
		}

		$order_number   = (string) $order->get_order_number();
		$order_total    = (float) $order->get_total();
		$payment_method = (string) $order->get_payment_method();
		$is_cod         = in_array( $payment_method, array( 'cod' ), true );
		$cod_amount     = $is_cod ? $order_total : 0.0;

		$item_summary = array();
		$weight_total = 0.0;
		foreach ( $order->get_items() as $item ) {
			if ( ! $item instanceof WC_Order_Item_Product ) {
				continue;
			}

			$product = $item->get_product();
			$qty     = (int) $item->get_quantity();
			$name    = (string) $item->get_name();

			$item_summary[] = array(
				'name'     => $name,
				'quantity' => $qty,
				'total'    => (float) $item->get_total(),
			);

			if ( $product ) {
				$weight = (float) wc_get_weight( $product->get_weight(), 'kg' );
				$weight_total += ( $weight * max( 1, $qty ) );
			}
		}

		$full_address = trim( $address_1 . ' ' . $address_2 );

		// NOTE: Placeholder mapping; confirm final field names/types against official DHL/MNG API docs for production.
		return array(
			'referenceId'         => strtoupper( 'WC_' . $order_number ),
			'barcode'             => strtoupper( 'WC_' . $order_number ),
			'isCOD'               => $is_cod ? 1 : 0,
			'codAmount'           => $cod_amount,
			'content'             => $this->build_package_description( $item_summary ),
			'paymentType'         => $is_cod ? 2 : 1, // Placeholder: verify enum mapping from final docs.
			'deliveryType'        => 1, // Placeholder: address delivery.
			'shipmentServiceType' => 1, // Placeholder: standard delivery.
			'packagingType'       => 3, // Placeholder: package.
			'recipient'           => array(
				'fullName'          => $recipient_name,
				'mobilePhoneNumber' => $recipient_phone,
				'email'             => $recipient_email,
				'address'           => $full_address,
				'cityName'          => $city,
				'districtName'      => $state,
				'postalCode'        => $postcode,
				'countryCode'       => $country,
			),
			'orderInfo'           => array(
				'orderNumber'      => $order_number,
				'paymentMethod'    => $payment_method,
				'orderTotal'       => $order_total,
				'itemSummary'      => $item_summary,
				'packageWeightKg'  => round( $weight_total, 3 ),
				'packageDesc'      => $this->build_package_description( $item_summary ),
			),
		);
	}

	/**
	 * Build human readable package content.
	 *
	 * @param array<int,array<string,mixed>> $items Items.
	 * @return string
	 */
	private function build_package_description( $items ) {
		if ( empty( $items ) ) {
			return __( 'WooCommerce order package', 'dhl-ecommerce-mng-woocommerce' );
		}

		$parts = array();
		foreach ( $items as $item ) {
			$parts[] = sprintf( '%s x%s', $item['name'], $item['quantity'] );
		}

		$text = implode( ', ', $parts );
		if ( function_exists( 'mb_substr' ) ) {
			return mb_substr( $text, 0, 250 );
		}

		return substr( $text, 0, 250 );
	}
}
