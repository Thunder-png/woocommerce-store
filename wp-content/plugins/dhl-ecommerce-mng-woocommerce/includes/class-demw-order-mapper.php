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
		$base = $this->build_base_order_data( $order );
		if ( is_wp_error( $base ) ) {
			return $base;
		}

		$order_number    = $base['order_number'];
		$order_total     = $base['order_total'];
		$payment_method  = $base['payment_method'];
		$is_cod          = $base['is_cod'];
		$cod_amount      = $base['cod_amount'];
		$item_summary    = $base['item_summary'];
		$order_pieces    = $base['order_pieces'];
		$weight_total    = $base['weight_total'];
		$desi_total      = $base['desi_total'];
		$reference_id    = $base['reference_id'];
		$customer_id     = $base['customer_id'];
		$branch_code     = $base['branch_code'];
		$recipient_name  = $base['recipient_name'];
		$recipient_phone = $base['recipient_phone'];
		$recipient_email = $base['recipient_email'];
		$full_address    = $base['full_address'];
		$city            = $base['city'];
		$state           = $base['state'];
		$postcode        = $base['postcode'];
		$country         = $base['country'];

		// NOTE: Structure follows CreateDetailedOrderRequest schema from plus-command-api docs.
		return array(
			'order'          => array(
				'referenceId'         => $reference_id,
				'barcode'             => $reference_id,
				'billOfLandingId'     => 'WC-INV-' . $order_number,
				'isCOD'               => $is_cod ? 1 : 0,
				'codAmount'           => $cod_amount,
				'shipmentServiceType' => 1,
				'packagingType'       => 3,
				'content'             => $this->build_package_description( $item_summary ),
				'smsPreference1'      => 1,
				'smsPreference2'      => 0,
				'smsPreference3'      => 0,
				'paymentType'         => $is_cod ? 2 : 1,
				'deliveryType'        => 1,
				'description'         => sprintf( 'WooCommerce Order #%s', $order_number ),
			),
			'orderPieceList' => $order_pieces,
			'shipper'        => array(
				'customerId'           => $customer_id,
				'refCustomerId'        => '',
				'cityCode'             => 0,
				'districtCode'         => 0,
				'cityName'             => '',
				'districtName'         => '',
				'address'              => '',
				'bussinessPhoneNumber' => '',
				'email'                => '',
				'taxOffice'            => '',
				'taxNumber'            => '',
				'fullName'             => '',
				'homePhoneNumber'      => '',
				'mobilePhoneNumber'    => '',
			),
			'recipient'      => array(
				'customerId'           => 0,
				'refCustomerId'        => '',
				'cityCode'             => 0,
				'districtCode'         => 0,
				'cityName'             => $city,
				'districtName'         => $state,
				'address'              => $full_address,
				'bussinessPhoneNumber' => '',
				'email'                => $recipient_email,
				'taxOffice'            => '',
				'taxNumber'            => '',
				'fullName'             => $recipient_name,
				'homePhoneNumber'      => '',
				'mobilePhoneNumber'    => $recipient_phone,
			),
			'orderInfo'      => array(
				'orderNumber'      => $order_number,
				'paymentMethod'    => $payment_method,
				'orderTotal'       => $order_total,
				'itemSummary'      => $item_summary,
				'packageWeightKg'  => round( $weight_total, 3 ),
				'packageDesiTotal' => $desi_total,
				'country'          => $country,
				'postcode'         => $postcode,
				'branchCode'       => $branch_code,
				'packageDesc'      => $this->build_package_description( $item_summary ),
			),
		);
	}

	/**
	 * Map order to CreateRecipientRequest.
	 *
	 * @param WC_Order $order Order.
	 * @return array<string,mixed>|WP_Error
	 */
	public function map_order_to_recipient_payload( WC_Order $order ) {
		$base = $this->build_base_order_data( $order );
		if ( is_wp_error( $base ) ) {
			return $base;
		}

		$location = $this->resolve_city_district_names( $base['country'], $base['city'], $base['state'] );

		return array(
			'recipient' => array(
				'customerId'           => '',
				'refCustomerId'        => '',
				'cityCode'             => 0,
				'districtCode'         => 0,
				'cityName'             => $location['city_name'],
				'districtName'         => $location['district_name'],
				'address'              => $base['full_address'],
				'bussinessPhoneNumber' => '',
				'email'                => $base['recipient_email'],
				'taxOffice'            => '',
				'taxNumber'            => '',
				'fullName'             => $base['recipient_name'],
				'homePhoneNumber'      => '',
				'mobilePhoneNumber'    => $this->normalize_phone_for_mng( $base['recipient_phone'] ),
			),
		);
	}

	/**
	 * Map order to Standard Command CreateOrderRequest.
	 *
	 * @param WC_Order $order Order.
	 * @return array<string,mixed>|WP_Error
	 */
	public function map_order_to_standard_order_payload( WC_Order $order ) {
		$base = $this->build_base_order_data( $order );
		if ( is_wp_error( $base ) ) {
			return $base;
		}

		$marketplace = $this->resolve_marketplace_fields( $order, $base['reference_id'] );
		if ( is_wp_error( $marketplace ) ) {
			return $marketplace;
		}
		$location = $this->resolve_city_district_names( $base['country'], $base['city'], $base['state'] );

		return array(
			'order'          => array(
				'referenceId'         => $base['reference_id'],
				'barcode'             => $base['reference_id'],
				'billOfLandingId'     => 'WC-INV-' . $base['order_number'],
				'isCOD'               => $base['is_cod'] ? 1 : 0,
				'codAmount'           => $base['cod_amount'],
				'shipmentServiceType' => 1,
				'packagingType'       => 3,
				'content'             => $this->build_package_description( $base['item_summary'] ),
				'smsPreference1'      => 1,
				'smsPreference2'      => 0,
				'smsPreference3'      => 0,
				'paymentType'         => $base['is_cod'] ? 2 : 1,
				'deliveryType'        => 1,
				'description'         => sprintf( 'WooCommerce Order #%s', $base['order_number'] ),
				'marketPlaceShortCode'=> $marketplace['short_code'],
				'marketPlaceSaleCode' => $marketplace['sale_code'],
			),
			'orderPieceList' => $base['order_pieces'],
			'recipient'      => array(
				'customerId'           => '',
				'refCustomerId'        => '',
				'cityCode'             => 0,
				'districtCode'         => 0,
				'cityName'             => $location['city_name'],
				'districtName'         => $location['district_name'],
				'address'              => $base['full_address'],
				'bussinessPhoneNumber' => '',
				'email'                => $base['recipient_email'],
				'taxOffice'            => '',
				'taxNumber'            => '',
				'fullName'             => $base['recipient_name'],
				'homePhoneNumber'      => '',
				'mobilePhoneNumber'    => $base['recipient_phone'],
			),
		);
	}

	/**
	 * Map order to CreateBarcodeRequest.
	 *
	 * @param WC_Order $order Order.
	 * @return array<string,mixed>|WP_Error
	 */
	public function map_order_to_barcode_payload( WC_Order $order ) {
		$base = $this->build_base_order_data( $order );
		if ( is_wp_error( $base ) ) {
			return $base;
		}

		$message = sprintf( 'WooCommerce Order #%s', $base['order_number'] );
		$message = sanitize_text_field( $message );

		return array(
			'referenceId'                  => $base['reference_id'],
			'billOfLandingId'              => 'WC-INV-' . $base['order_number'],
			'isCOD'                        => $base['is_cod'] ? 1 : 0,
			'codAmount'                    => $base['cod_amount'],
			'printReferenceBarcodeOnError' => 0,
			'message'                      => $message,
			'additionalContent1'           => '',
			'additionalContent2'           => '',
			'additionalContent3'           => '',
			'additionalContent4'           => '',
			// Docs sample for CreateBarcodeRequest uses packagingType=2.
			'packagingType'                => 2,
			'orderPieceList'               => $base['order_pieces'],
		);
	}

	/**
	 * Build shared order data for API payloads.
	 *
	 * @param WC_Order $order Order.
	 * @return array<string,mixed>|WP_Error
	 */
	private function build_base_order_data( WC_Order $order ) {
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
		$order_pieces = array();
		$weight_total = 0.0;
		$desi_total   = 0.0;
		$piece_index  = 1;
		foreach ( $order->get_items() as $item ) {
			if ( ! $item instanceof WC_Order_Item_Product ) {
				continue;
			}

			$product = $item->get_product();
			$weight  = 0.0;
			$qty     = (int) $item->get_quantity();
			$name    = (string) $item->get_name();

			$item_summary[] = array(
				'name'     => $name,
				'quantity' => $qty,
				'total'    => (float) $item->get_total(),
			);

			$piece_kg   = 1;
			$piece_desi = 1;
			if ( $product ) {
				$piece_kg   = $this->calculate_piece_kg( $product );
				$piece_desi = $this->calculate_piece_desi( $product, $piece_kg );
				$weight     = (float) $piece_kg;
				$weight_total += ( $weight * max( 1, $qty ) );
			}

			// MNG orderPieceList with barcode/desi/kg.
			for ( $i = 0; $i < max( 1, $qty ); $i++ ) {
				$desi_total   += $piece_desi;
				$order_pieces[] = array(
					'barcode' => strtoupper( 'WC_' . $order_number . '_P' . $piece_index ),
					'desi'    => $piece_desi,
					'kg'      => $piece_kg,
					'content' => $name,
				);
				$piece_index++;
			}
		}

		$full_address = trim( $address_1 . ' ' . $address_2 );
		$reference_id = strtoupper( 'WC_' . $order_number );
		$settings     = get_option( DEMW_OPTION_KEY, array() );
		$customer_id  = isset( $settings['customer_code'] ) ? absint( (string) $settings['customer_code'] ) : 0;
		$branch_code  = isset( $settings['branch_code'] ) ? (string) $settings['branch_code'] : '';

		if ( empty( $order_pieces ) ) {
			$order_pieces[] = array(
				'barcode' => $reference_id . '_P1',
				'desi'    => 1,
				'kg'      => 1,
				'content' => __( 'Order package', 'dhl-ecommerce-mng-woocommerce' ),
			);
		}

		return array(
			'order_number'    => $order_number,
			'order_total'     => $order_total,
			'payment_method'  => $payment_method,
			'is_cod'          => $is_cod,
			'cod_amount'      => $cod_amount,
			'item_summary'    => $item_summary,
			'order_pieces'    => $order_pieces,
			'weight_total'    => $weight_total,
			'desi_total'      => $desi_total,
			'reference_id'    => $reference_id,
			'customer_id'     => $customer_id,
			'branch_code'     => $branch_code,
			'recipient_name'  => $recipient_name,
			'recipient_phone' => $recipient_phone,
			'recipient_email' => $recipient_email,
			'full_address'    => $full_address,
			'city'            => $city,
			'state'           => $state,
			'postcode'        => $postcode,
			'country'         => $country,
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

	/**
	 * Resolve marketplace fields for Standard Command.
	 *
	 * @param WC_Order $order Order.
	 * @param string   $fallback_sale_code Fallback sale code.
	 * @return array{short_code:string,sale_code:string}|WP_Error
	 */
	private function resolve_marketplace_fields( WC_Order $order, $fallback_sale_code ) {
		$manual_short = strtoupper( trim( (string) $order->get_meta( '_demw_marketplace_short_code', true ) ) );
		$manual_sale  = trim( (string) $order->get_meta( '_demw_marketplace_sale_code', true ) );

		$allowed_short_codes = array( 'TRND', 'N11' );
		if ( in_array( $manual_short, $allowed_short_codes, true ) ) {
			if ( '' === $manual_sale ) {
				return new WP_Error(
					'demw_missing_marketplace_sale_code',
					__( 'Marketplace order detected but marketPlaceSaleCode is empty. Set _demw_marketplace_sale_code order meta.', 'dhl-ecommerce-mng-woocommerce' )
				);
			}

			return array(
				'short_code' => $manual_short,
				'sale_code'  => $manual_sale,
			);
		}

		$created_via = strtolower( trim( (string) $order->get_created_via() ) );
		$short_code  = '';
		if ( false !== strpos( $created_via, 'trendyol' ) ) {
			$short_code = 'TRND';
		} elseif ( false !== strpos( $created_via, 'n11' ) ) {
			$short_code = 'N11';
		}

		$sale_code_keys = array(
			'marketPlaceSaleCode',
			'marketplace_sale_code',
			'trendyol_order_number',
			'_trendyol_order_number',
			'n11_order_id',
			'_n11_order_id',
			'n11OrderId',
		);
		$sale_code = '';
		foreach ( $sale_code_keys as $meta_key ) {
			$value = trim( (string) $order->get_meta( $meta_key, true ) );
			if ( '' !== $value ) {
				$sale_code = $value;
				break;
			}
		}

		// Infer short code from known sale-code fields when created_via is generic.
		if ( '' === $short_code ) {
			if ( '' !== trim( (string) $order->get_meta( 'trendyol_order_number', true ) ) || '' !== trim( (string) $order->get_meta( '_trendyol_order_number', true ) ) ) {
				$short_code = 'TRND';
			} elseif ( '' !== trim( (string) $order->get_meta( 'n11_order_id', true ) ) || '' !== trim( (string) $order->get_meta( '_n11_order_id', true ) ) ) {
				$short_code = 'N11';
			}
		}

		if ( '' === $short_code ) {
			return array(
				'short_code' => '',
				'sale_code'  => '',
			);
		}

		if ( '' === $sale_code ) {
			$sale_code = (string) $fallback_sale_code;
		}

		return array(
			'short_code' => $short_code,
			'sale_code'  => $sale_code,
		);
	}

	/**
	 * Normalize phone for MNG payload (prefer 10-digit local format).
	 *
	 * @param string $phone Raw phone.
	 * @return string
	 */
	private function normalize_phone_for_mng( $phone ) {
		$digits = preg_replace( '/\D+/', '', (string) $phone );
		$digits = is_string( $digits ) ? $digits : '';

		if ( '' === $digits ) {
			return '';
		}

		// Convert 90XXXXXXXXXX or 0XXXXXXXXXX to 10 digits.
		if ( 12 === strlen( $digits ) && 0 === strpos( $digits, '90' ) ) {
			$digits = substr( $digits, 2 );
		}
		if ( 11 === strlen( $digits ) && '0' === $digits[0] ) {
			$digits = substr( $digits, 1 );
		}

		if ( strlen( $digits ) > 10 ) {
			$digits = substr( $digits, -10 );
		}

		return $digits;
	}

	/**
	 * Resolve API-friendly city/district labels from Woo fields.
	 *
	 * In TR stores, Woo often stores province in state (TR06) and district in city.
	 *
	 * @param string $country Country code.
	 * @param string $city Woo city field.
	 * @param string $state Woo state field.
	 * @return array{city_name:string,district_name:string}
	 */
	private function resolve_city_district_names( $country, $city, $state ) {
		$country = strtoupper( trim( (string) $country ) );
		$city    = trim( (string) $city );
		$state   = trim( (string) $state );

		$city_name     = $city;
		$district_name = $state;

		if ( 'TR' === $country && preg_match( '/^TR\d{2}$/i', $state ) ) {
			$state_name = '';
			if ( function_exists( 'WC' ) && WC()->countries && isset( WC()->countries->states['TR'][ $state ] ) ) {
				$state_name = (string) WC()->countries->states['TR'][ $state ];
			}
			if ( '' !== $state_name ) {
				$city_name = $state_name;
			}
			$district_name = $city;
		}

		return array(
			'city_name'     => $city_name,
			'district_name' => $district_name,
		);
	}

	/**
	 * Calculate integer KG for a product.
	 *
	 * @param WC_Product $product Product.
	 * @return int
	 */
	private function calculate_piece_kg( WC_Product $product ) {
		$weight = (float) wc_get_weight( $product->get_weight(), 'kg' );
		if ( $weight <= 0 ) {
			return 1;
		}

		return max( 1, (int) ceil( $weight ) );
	}

	/**
	 * Calculate desi from dimensions.
	 *
	 * Formula: (cm x cm x cm) / 3000, rounded up.
	 *
	 * @param WC_Product $product Product.
	 * @param int        $fallback_kg Fallback value.
	 * @return int
	 */
	private function calculate_piece_desi( WC_Product $product, $fallback_kg ) {
		$length = (float) wc_get_dimension( $product->get_length(), 'cm' );
		$width  = (float) wc_get_dimension( $product->get_width(), 'cm' );
		$height = (float) wc_get_dimension( $product->get_height(), 'cm' );

		if ( $length <= 0 || $width <= 0 || $height <= 0 ) {
			return max( 1, (int) $fallback_kg );
		}

		$desi = ( $length * $width * $height ) / 3000;
		return max( 1, (int) ceil( $desi ) );
	}
}
