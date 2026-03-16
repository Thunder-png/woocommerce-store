<?php
/**
 * Location code resolution via CBS APIs.
 *
 * @package DEMW
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Resolve Turkish city/district names and codes.
 */
class DEMW_Location_Resolver {
	/**
	 * API client.
	 *
	 * @var DEMW_API_Client
	 */
	private $api_client;

	/**
	 * Constructor.
	 *
	 * @param DEMW_API_Client $api_client API client.
	 */
	public function __construct( DEMW_API_Client $api_client ) {
		$this->api_client = $api_client;
	}

	/**
	 * Resolve order location to city/district code pair.
	 *
	 * @param WC_Order $order Order.
	 * @return array{city_code:string,district_code:string,city_name:string,district_name:string}|WP_Error
	 */
	public function resolve_for_order( WC_Order $order ) {
		$country  = (string) $order->get_shipping_country();
		$city     = (string) $order->get_shipping_city();
		$state    = (string) $order->get_shipping_state();
		$address1 = (string) $order->get_shipping_address_1();

		if ( '' === trim( $address1 ) ) {
			$country = (string) $order->get_billing_country();
			$city    = (string) $order->get_billing_city();
			$state   = (string) $order->get_billing_state();
		}

		return $this->resolve_by_parts( $country, $city, $state );
	}

	/**
	 * Resolve checkout destination fields to city/district code pair.
	 *
	 * @param string $country Country code.
	 * @param string $city City/district string.
	 * @param string $state State/province string.
	 * @return array{city_code:string,district_code:string,city_name:string,district_name:string}|WP_Error
	 */
	public function resolve_by_parts( $country, $city, $state ) {
		$country = strtoupper( trim( (string) $country ) );
		$city    = trim( (string) $city );
		$state   = trim( (string) $state );

		if ( 'TR' !== $country || '' === $city ) {
			return new WP_Error( 'demw_location_not_resolved', __( 'Country/city data is insufficient for location code resolution.', 'dhl-ecommerce-mng-woocommerce' ) );
		}

		$city_name     = $city;
		$district_name = $state;
		if ( preg_match( '/^TR\d{2}$/i', $state ) ) {
			$state_name = '';
			if ( function_exists( 'WC' ) && WC()->countries && isset( WC()->countries->states['TR'][ $state ] ) ) {
				$state_name = (string) WC()->countries->states['TR'][ $state ];
			}
			if ( '' !== $state_name ) {
				$city_name = $state_name;
			}
			$district_name = $city;
		}

		$cities = $this->api_client->get_cities();
		if ( is_wp_error( $cities ) || ! is_array( $cities ) ) {
			return new WP_Error( 'demw_city_codes_unavailable', __( 'Unable to resolve city code from CBS API.', 'dhl-ecommerce-mng-woocommerce' ) );
		}

		$city_code = '';
		foreach ( $cities as $item ) {
			if ( ! is_array( $item ) ) {
				continue;
			}
			$name = isset( $item['name'] ) ? (string) $item['name'] : '';
			$code = isset( $item['code'] ) ? (string) $item['code'] : '';
			if ( '' === $name || '' === $code ) {
				continue;
			}
			if ( $this->normalize_tr_text( $name ) === $this->normalize_tr_text( $city_name ) ) {
				$city_code = $code;
				break;
			}
		}
		if ( '' === $city_code ) {
			return new WP_Error( 'demw_city_code_not_found', __( 'City code could not be matched from CBS API.', 'dhl-ecommerce-mng-woocommerce' ) );
		}

		$districts = $this->api_client->get_districts( $city_code );
		if ( is_wp_error( $districts ) || ! is_array( $districts ) ) {
			return new WP_Error( 'demw_district_codes_unavailable', __( 'Unable to resolve district code from CBS API.', 'dhl-ecommerce-mng-woocommerce' ) );
		}

		$district_code = '';
		foreach ( $districts as $item ) {
			if ( ! is_array( $item ) ) {
				continue;
			}
			$name = isset( $item['name'] ) ? (string) $item['name'] : '';
			$code = isset( $item['code'] ) ? (string) $item['code'] : '';
			if ( '' === $name || '' === $code ) {
				continue;
			}
			if ( $this->normalize_tr_text( $name ) === $this->normalize_tr_text( $district_name ) ) {
				$district_code = $code;
				break;
			}
		}

		if ( '' === $district_code && ! empty( $districts[0]['code'] ) ) {
			$district_code = (string) $districts[0]['code'];
		}

		if ( '' === $district_code ) {
			return new WP_Error( 'demw_district_code_not_found', __( 'District code could not be matched from CBS API.', 'dhl-ecommerce-mng-woocommerce' ) );
		}

		return array(
			'city_code'     => $city_code,
			'district_code' => $district_code,
			'city_name'     => $city_name,
			'district_name' => $district_name,
		);
	}

	/**
	 * Normalize text for Turkish-insensitive comparisons.
	 *
	 * @param string $text Raw text.
	 * @return string
	 */
	private function normalize_tr_text( $text ) {
		$text = trim( (string) $text );
		if ( '' === $text ) {
			return '';
		}

		$map = array(
			'I' => 'i',
			'İ' => 'i',
			'Ş' => 's',
			'ş' => 's',
			'Ğ' => 'g',
			'ğ' => 'g',
			'Ü' => 'u',
			'ü' => 'u',
			'Ö' => 'o',
			'ö' => 'o',
			'Ç' => 'c',
			'ç' => 'c',
		);
		$text = strtr( $text, $map );

		return function_exists( 'mb_strtolower' ) ? mb_strtolower( $text ) : strtolower( $text );
	}
}
