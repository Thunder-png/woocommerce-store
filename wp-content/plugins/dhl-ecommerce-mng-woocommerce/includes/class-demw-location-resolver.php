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
	 * @return array{city_code:string,district_code:string,city_name:string,district_name:string,neighborhood:string,is_out_of_service:bool,is_mobile_area:bool,normalized_address:string}|WP_Error
	 */
	public function resolve_for_order( WC_Order $order ) {
		$country  = (string) $order->get_shipping_country();
		$city     = (string) $order->get_shipping_city();
		$state    = (string) $order->get_shipping_state();
		$address1 = (string) $order->get_shipping_address_1();
		$address2 = (string) $order->get_shipping_address_2();

		if ( '' === trim( $address1 ) ) {
			$country = (string) $order->get_billing_country();
			$city    = (string) $order->get_billing_city();
			$state   = (string) $order->get_billing_state();
			$address1 = (string) $order->get_billing_address_1();
			$address2 = (string) $order->get_billing_address_2();
		}

		return $this->resolve_by_parts( $country, $city, $state, trim( $address1 . ' ' . $address2 ) );
	}

	/**
	 * Resolve checkout destination fields to city/district code pair.
	 *
	 * @param string $country Country code.
	 * @param string $city City/district string.
	 * @param string $state State/province string.
	 * @param string $full_address Full address line (optional).
	 * @return array{city_code:string,district_code:string,city_name:string,district_name:string,neighborhood:string,is_out_of_service:bool,is_mobile_area:bool,normalized_address:string}|WP_Error
	 */
	public function resolve_by_parts( $country, $city, $state, $full_address = '' ) {
		$country = strtoupper( trim( (string) $country ) );
		$city    = trim( (string) $city );
		$state   = trim( (string) $state );
		$full_address = trim( (string) $full_address );

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

		$neighborhood        = '';
		$is_out_of_service   = false;
		$is_mobile_area      = false;
		$normalized_address  = $full_address;

		$neighborhoods = $this->api_client->get_neighborhoods( $city_code, $district_code );
		if ( ! is_wp_error( $neighborhoods ) && is_array( $neighborhoods ) ) {
			$neighborhood = $this->match_neighborhood_from_address( $neighborhoods, $full_address );
		}

		if ( '' !== $neighborhood ) {
			if ( '' === $normalized_address ) {
				$normalized_address = $neighborhood;
			} elseif ( false === strpos( $this->normalize_tr_text( $normalized_address ), $this->normalize_tr_text( $neighborhood ) ) ) {
				$normalized_address = $neighborhood . ' ' . $normalized_address;
			}
		}

		$out_of_service_areas = $this->api_client->get_out_of_service_areas( $city_code, $district_code );
		if ( ! is_wp_error( $out_of_service_areas ) && is_array( $out_of_service_areas ) && '' !== $neighborhood ) {
			$is_out_of_service = $this->contains_neighborhood( $out_of_service_areas, $neighborhood );
		}

		$mobile_areas = $this->api_client->get_mobile_areas( $city_code, $district_code );
		if ( ! is_wp_error( $mobile_areas ) && is_array( $mobile_areas ) && '' !== $neighborhood ) {
			$is_mobile_area = $this->contains_neighborhood( $mobile_areas, $neighborhood );
		}

		return array(
			'city_code'     => $city_code,
			'district_code' => $district_code,
			'city_name'     => $city_name,
			'district_name' => $district_name,
			'neighborhood'  => $neighborhood,
			'is_out_of_service' => $is_out_of_service,
			'is_mobile_area'    => $is_mobile_area,
			'normalized_address' => $normalized_address,
		);
	}

	/**
	 * Match best neighborhood from full address text.
	 *
	 * @param array<int,array<string,mixed>> $neighborhoods Neighborhood list.
	 * @param string                         $full_address Full address.
	 * @return string
	 */
	private function match_neighborhood_from_address( $neighborhoods, $full_address ) {
		$normalized_address = $this->normalize_tr_text( $full_address );

		foreach ( $neighborhoods as $item ) {
			if ( ! is_array( $item ) ) {
				continue;
			}
			$name = isset( $item['neighborhood'] ) ? trim( (string) $item['neighborhood'] ) : '';
			if ( '' === $name ) {
				continue;
			}
			if ( '' !== $normalized_address && false !== strpos( $normalized_address, $this->normalize_tr_text( $name ) ) ) {
				return $name;
			}
		}

		return '';
	}

	/**
	 * Check whether list contains the given neighborhood.
	 *
	 * @param array<int,array<string,mixed>> $areas Area list.
	 * @param string                         $neighborhood Neighborhood.
	 * @return bool
	 */
	private function contains_neighborhood( $areas, $neighborhood ) {
		$needle = $this->normalize_tr_text( $neighborhood );
		if ( '' === $needle ) {
			return false;
		}

		foreach ( $areas as $item ) {
			if ( ! is_array( $item ) ) {
				continue;
			}
			$name = isset( $item['neighborhood'] ) ? (string) $item['neighborhood'] : '';
			if ( '' !== $name && $this->normalize_tr_text( $name ) === $needle ) {
				return true;
			}
		}

		return false;
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
