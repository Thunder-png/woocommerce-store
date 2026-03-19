<?php
/**
 * WooCommerce shipping method registration for DHL MNG.
 *
 * @package DEMW
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WC_Shipping_Method' ) ) {
	return;
}

/**
 * DHL MNG shipping method.
 */
class DEMW_Shipping_Method extends WC_Shipping_Method {
	/**
	 * API client cache.
	 *
	 * @var DEMW_API_Client|null
	 */
	private $demw_api_client = null;

	/**
	 * DEMW logger cache.
	 *
	 * @var DEMW_Logger|null
	 */
	private $demw_logger = null;

	/**
	 * DEMW location resolver cache.
	 *
	 * @var DEMW_Location_Resolver|null
	 */
	private $demw_location_resolver = null;
	/**
	 * Constructor.
	 *
	 * @param int $instance_id Instance ID.
	 */
	public function __construct( $instance_id = 0 ) {
		$this->id                 = 'demw_dhl_mng';
		$this->instance_id        = absint( $instance_id );
		$this->method_title       = __( 'DHL eCommerce MNG', 'dhl-ecommerce-mng-woocommerce' );
		$this->method_description = __( 'DHL eCommerce Turkey / MNG Kargo shipping method.', 'dhl-ecommerce-mng-woocommerce' );
		$this->supports           = array(
			'shipping-zones',
			'instance-settings',
			'instance-settings-modal',
		);

		$this->init();
	}

	/**
	 * Initialize settings and hooks.
	 *
	 * @return void
	 */
	public function init() {
		$this->init_instance_form_fields();
		$this->init_settings();

		$this->title      = $this->get_option( 'title', __( 'DHL MNG Kargo', 'dhl-ecommerce-mng-woocommerce' ) );
		$this->enabled    = $this->get_option( 'enabled', 'yes' );
		$this->tax_status = $this->get_option( 'tax_status', 'taxable' );

		add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
	}

	/**
	 * Instance fields shown in shipping zone method modal.
	 *
	 * @return void
	 */
	public function init_instance_form_fields() {
		$this->instance_form_fields = array(
			'title'      => array(
				'title'       => __( 'Method title', 'dhl-ecommerce-mng-woocommerce' ),
				'type'        => 'text',
				'description' => __( 'Shown to customers during checkout.', 'dhl-ecommerce-mng-woocommerce' ),
				'default'     => __( 'DHL MNG Kargo', 'dhl-ecommerce-mng-woocommerce' ),
				'desc_tip'    => true,
			),
			'tax_status' => array(
				'title'   => __( 'Tax status', 'dhl-ecommerce-mng-woocommerce' ),
				'type'    => 'select',
				'default' => 'taxable',
				'options' => array(
					'taxable' => __( 'Taxable', 'dhl-ecommerce-mng-woocommerce' ),
					'none'    => _x( 'None', 'Tax status', 'dhl-ecommerce-mng-woocommerce' ),
				),
			),
			'cost'       => array(
				'title'             => __( 'Cost', 'dhl-ecommerce-mng-woocommerce' ),
				'type'              => 'price',
				'placeholder'       => '0',
				'description'       => __( 'Flat shipping cost for this method.', 'dhl-ecommerce-mng-woocommerce' ),
				'default'           => '0',
				'desc_tip'          => true,
				'sanitize_callback' => array( $this, 'sanitize_cost' ),
			),
			'rate_source' => array(
				'title'       => __( 'Rate source', 'dhl-ecommerce-mng-woocommerce' ),
				'type'        => 'select',
				'default'     => 'flat',
				'options'     => array(
					'flat' => __( 'Flat cost (manual)', 'dhl-ecommerce-mng-woocommerce' ),
					'api'  => __( 'Carrier API (standardqueryapi/calculate)', 'dhl-ecommerce-mng-woocommerce' ),
				),
				'description' => __( 'When Carrier API is selected, checkout rate is calculated from cart desi/kg and destination address.', 'dhl-ecommerce-mng-woocommerce' ),
				'desc_tip'    => true,
			),
			'fallback_cost' => array(
				'title'             => __( 'Fallback cost', 'dhl-ecommerce-mng-woocommerce' ),
				'type'              => 'price',
				'placeholder'       => '0',
				'description'       => __( 'Used when API pricing fails.', 'dhl-ecommerce-mng-woocommerce' ),
				'default'           => '0',
				'desc_tip'          => true,
				'sanitize_callback' => array( $this, 'sanitize_cost' ),
			),
			'packaging_type' => array(
				'title'   => __( 'Packaging type', 'dhl-ecommerce-mng-woocommerce' ),
				'type'    => 'select',
				'default' => '3',
				'options' => array(
					'1' => __( 'Dosya', 'dhl-ecommerce-mng-woocommerce' ),
					'2' => __( 'Mi', 'dhl-ecommerce-mng-woocommerce' ),
					'3' => __( 'Paket', 'dhl-ecommerce-mng-woocommerce' ),
					'4' => __( 'Koli', 'dhl-ecommerce-mng-woocommerce' ),
				),
			),
			'pick_up_type' => array(
				'title'   => __( 'Pick up type', 'dhl-ecommerce-mng-woocommerce' ),
				'type'    => 'select',
				'default' => '1',
				'options' => array(
					'1' => __( 'Adresten Alim', 'dhl-ecommerce-mng-woocommerce' ),
					'2' => __( 'Subeye Getirildi', 'dhl-ecommerce-mng-woocommerce' ),
				),
			),
			'free_shipping_min_amount' => array(
				'title'             => __( 'Free shipping minimum amount', 'dhl-ecommerce-mng-woocommerce' ),
				'type'              => 'price',
				'placeholder'       => '0',
				'description'       => __( 'Set to 0 to disable. When cart subtotal reaches this amount, shipping becomes free.', 'dhl-ecommerce-mng-woocommerce' ),
				'default'           => '0',
				'desc_tip'          => true,
				'sanitize_callback' => array( $this, 'sanitize_cost' ),
			),
			'handling_fee' => array(
				'title'             => __( 'Handling fee', 'dhl-ecommerce-mng-woocommerce' ),
				'type'              => 'price',
				'placeholder'       => '0',
				'description'       => __( 'Additional fixed fee added to the calculated shipping cost.', 'dhl-ecommerce-mng-woocommerce' ),
				'default'           => '0',
				'desc_tip'          => true,
				'sanitize_callback' => array( $this, 'sanitize_cost' ),
			),
			'min_shipping_cost' => array(
				'title'             => __( 'Minimum shipping cost', 'dhl-ecommerce-mng-woocommerce' ),
				'type'              => 'price',
				'placeholder'       => '0',
				'description'       => __( 'Set to 0 to disable. Enforces a minimum shipping charge.', 'dhl-ecommerce-mng-woocommerce' ),
				'default'           => '0',
				'desc_tip'          => true,
				'sanitize_callback' => array( $this, 'sanitize_cost' ),
			),
			'max_shipping_cost' => array(
				'title'             => __( 'Maximum shipping cost', 'dhl-ecommerce-mng-woocommerce' ),
				'type'              => 'price',
				'placeholder'       => '0',
				'description'       => __( 'Set to 0 to disable. Caps shipping charge at this amount.', 'dhl-ecommerce-mng-woocommerce' ),
				'default'           => '0',
				'desc_tip'          => true,
				'sanitize_callback' => array( $this, 'sanitize_cost' ),
			),
			'api_cache_ttl' => array(
				'title'       => __( 'API rate cache (minutes)', 'dhl-ecommerce-mng-woocommerce' ),
				'type'        => 'number',
				'description' => __( 'Checkout API cost cache duration. Set to 0 to disable cache.', 'dhl-ecommerce-mng-woocommerce' ),
				'default'     => '15',
				'desc_tip'    => true,
				'custom_attributes' => array(
					'min'  => '0',
					'step' => '1',
				),
			),
		);
	}

	/**
	 * Calculate shipping rates for package.
	 *
	 * @param array<string,mixed> $package Package details.
	 * @return void
	 */
	public function calculate_shipping( $package = array() ) {
		if ( 'yes' !== $this->enabled ) {
			return;
		}

		$rate_source = (string) $this->get_option( 'rate_source', 'flat' );
		$fallback    = (float) $this->get_option( 'fallback_cost', $this->get_option( 'cost', '0' ) );
		$cost        = (float) $this->get_option( 'cost', '0' );

		if ( 'api' === $rate_source ) {
			$api_cost = $this->calculate_cost_from_api( $package );
			if ( is_numeric( $api_cost ) ) {
				$cost = (float) $api_cost;
			} else {
				$cost = $fallback;
				$wc = function_exists( 'WC' ) ? WC() : null;
				if (
					function_exists( 'wc_add_notice' )
					&& function_exists( 'is_checkout' )
					&& is_checkout()
					&& ! is_admin()
					&& $wc
					&& isset( $wc->session )
					&& is_object( $wc->session )
					&& is_callable( array( $wc->session, 'get' ) )
					&& is_callable( array( $wc->session, 'set' ) )
					&& ! $wc->session->get( 'demw_checkout_fallback_notice_shown' )
				) {
					wc_add_notice( __( 'Taşıyıcı şube eşlemesi yapılamadı; mağaza yöneticisi branch_code ayarını kontrol etmelidir', 'dhl-ecommerce-mng-woocommerce' ), 'notice' );
					$wc->session->set( 'demw_checkout_fallback_notice_shown', true );
				}
			}
		}

		$cost = $this->apply_cost_adjustments( $cost, $package );
		$cost = wc_format_decimal( $cost );

		$rate = array(
			'id'       => $this->get_rate_id(),
			'label'    => $this->title,
			'cost'     => $cost,
			'package'  => $package,
			'calc_tax' => 'none' === $this->tax_status ? '' : 'per_order',
		);

		$this->add_rate( $rate );
	}

	/**
	 * Calculate shipping cost from carrier API.
	 *
	 * @param array<string,mixed> $package Package.
	 * @return float|null
	 */
	private function calculate_cost_from_api( $package ) {
		$api_client = $this->get_demw_api_client();
		if ( ! $api_client ) {
			return null;
		}

		$cache_ttl = (int) $this->get_option( 'api_cache_ttl', '15' );
		$cache_key = $this->build_api_rate_cache_key( $package );
		if ( $cache_ttl > 0 && '' !== $cache_key ) {
			$cached_cost = get_transient( $cache_key );
			if ( false !== $cached_cost && is_numeric( $cached_cost ) ) {
				return (float) $cached_cost;
			}
		}

		$city_name     = isset( $package['destination']['city'] ) ? (string) $package['destination']['city'] : '';
		$state_name    = isset( $package['destination']['state'] ) ? (string) $package['destination']['state'] : '';
		$country       = isset( $package['destination']['country'] ) ? (string) $package['destination']['country'] : '';
		$address_1     = isset( $package['destination']['address'] ) ? (string) $package['destination']['address'] : '';
		$address_2     = isset( $package['destination']['address_2'] ) ? (string) $package['destination']['address_2'] : '';
		$full_address  = trim( $address_1 . ' ' . $address_2 );
		if ( '' === $country || '' === $city_name || '' === $full_address ) {
			return null;
		}

		$resolver = $this->get_demw_location_resolver();
		if ( ! $resolver ) {
			return null;
		}

		$resolved = $resolver->resolve_by_parts( $country, $city_name, $state_name, $full_address );
		if ( is_wp_error( $resolved ) ) {
			$this->demw_log_error( 'Location resolution failed for rate calculation', array( 'error' => $resolved->get_error_message() ) );
			return null;
		}

		$order_piece_list = $this->build_order_piece_list_from_package( $package );
		if ( empty( $order_piece_list ) ) {
			return null;
		}

		$normalized_address = isset( $resolved['normalized_address'] ) ? trim( (string) $resolved['normalized_address'] ) : '';
		$payload_address    = '' !== $normalized_address ? $normalized_address : $full_address;
		$payload_address    = $this->append_location_context_to_address( $payload_address, $resolved );
		$city_code          = isset( $resolved['city_code'] ) ? trim( (string) $resolved['city_code'] ) : '';
		$district_code      = isset( $resolved['district_code'] ) ? trim( (string) $resolved['district_code'] ) : '';
		$city_code          = $this->normalize_location_code( $city_code, 1 );
		$district_code      = $this->normalize_location_code( $district_code, 1 );

		if ( '' === $city_code || '' === $district_code ) {
			return null;
		}

		$payload = array(
			'shipmentServiceType' => 1,
			'packagingType'       => absint( $this->get_option( 'packaging_type', '3' ) ),
			'paymentType'         => 1,
			'pickUpType'          => absint( $this->get_option( 'pick_up_type', '1' ) ),
			'deliveryType'        => 1,
			'cityCode'            => (int) $city_code,
			'districtCode'        => (int) $district_code,
			'address'             => $payload_address,
			'smsPreference1'      => 1,
			'smsPreference2'      => 1,
			'smsPreference3'      => 0,
			'orderPieceList'      => $order_piece_list,
		);

		$calculated_cost = $this->calculate_transport_cost_with_fallback( $api_client, $payload );
		if ( null !== $calculated_cost ) {
			$this->cache_api_rate_cost( $cache_key, $calculated_cost, $cache_ttl );
			return $calculated_cost;
		}

		return null;
	}

	/**
	 * Retry calculate call on destination-branch resolution errors.
	 *
	 * @param DEMW_API_Client     $api_client API client.
	 * @param array<string,mixed> $payload Calculate payload.
	 * @return float|null
	 */
	private function calculate_transport_cost_with_fallback( DEMW_API_Client $api_client, $payload ) {
		$result = $api_client->calculate_transport_cost( $payload );
		$cost   = $this->extract_calculated_amount( $result );
		if ( null !== $cost ) {
			return $cost;
		}

		if ( ! is_wp_error( $result ) ) {
			return null;
		}

		if ( 'demw_destination_branch_not_found' !== (string) $result->get_error_code() ) {
			$this->demw_log_error( 'Rate calculation failed', array( 'error' => $result->get_error_message() ) );
			return null;
		}

		$city_code     = isset( $payload['cityCode'] ) ? trim( (string) $payload['cityCode'] ) : '';
		$district_code = isset( $payload['districtCode'] ) ? trim( (string) $payload['districtCode'] ) : '';
		if ( '' === $city_code || '' === $district_code ) {
			return null;
		}

		$initial_retries = $this->build_location_retry_payloads( $payload );
		foreach ( $initial_retries as $retry_payload ) {
			$retry_result = $api_client->calculate_transport_cost( $retry_payload );
			$retry_cost   = $this->extract_calculated_amount( $retry_result );
			if ( null !== $retry_cost ) {
				return $retry_cost;
			}
		}

		$districts = $api_client->get_districts( $city_code );
		if ( is_wp_error( $districts ) || ! is_array( $districts ) ) {
			return null;
		}

		$attempted_codes = array( $district_code => true );
		$attempt_count   = 0;
		foreach ( $districts as $district_item ) {
			$candidate_code = isset( $district_item['code'] ) ? trim( (string) $district_item['code'] ) : '';
			$candidate_code = $this->normalize_location_code( $candidate_code, 1 );
			if ( '' === $candidate_code || isset( $attempted_codes[ $candidate_code ] ) ) {
				continue;
			}

			$attempted_codes[ $candidate_code ] = true;
			$attempt_count++;
			if ( $attempt_count > 3 ) {
				break;
			}

			$retry_payload                  = $payload;
			$retry_payload['districtCode'] = (int) $candidate_code;
			$variant_payloads              = $this->build_location_retry_payloads( $retry_payload );

			foreach ( $variant_payloads as $variant_payload ) {
				$retry_result = $api_client->calculate_transport_cost( $variant_payload );
				$retry_cost   = $this->extract_calculated_amount( $retry_result );
				if ( null !== $retry_cost ) {
					return $retry_cost;
				}
			}
		}

		$demw_settings = get_option( 'demw_settings', array() );
		$branch_code   = is_array( $demw_settings ) && isset( $demw_settings['branch_code'] ) ? (string) $demw_settings['branch_code'] : '';

		$this->demw_log_error(
			'Rate calculation failed after district fallback attempts',
			array(
				'error'         => $result->get_error_message(),
				'city_code'     => $city_code,
				'district_code' => $district_code,
				'cityCode'      => isset( $payload['cityCode'] ) ? (string) $payload['cityCode'] : '',
				'districtCode'  => isset( $payload['districtCode'] ) ? (string) $payload['districtCode'] : '',
				'address'       => isset( $payload['address'] ) ? (string) $payload['address'] : '',
				'branch_code'   => $branch_code,
			)
		);
		return null;
	}

	/**
	 * Extract calculated amount from calculate endpoint response.
	 *
	 * @param array<string,mixed>|WP_Error $result API result.
	 * @return float|null
	 */
	private function extract_calculated_amount( $result ) {
		if ( is_wp_error( $result ) || ! is_array( $result ) || ! isset( $result['data'] ) ) {
			return null;
		}

		$data        = is_array( $result['data'] ) ? $result['data'] : array();
		$final_total = isset( $data['finalTotal'] ) ? (float) $data['finalTotal'] : null;
		if ( null !== $final_total && $final_total >= 0 ) {
			return $final_total;
		}

		$sub_total = isset( $data['subTotal'] ) ? (float) $data['subTotal'] : null;
		if ( null !== $sub_total && $sub_total >= 0 ) {
			return $sub_total;
		}

		return null;
	}

	/**
	 * Enrich free-text address with district/city if missing.
	 *
	 * @param string              $address  Base address.
	 * @param array<string,mixed> $resolved Resolved location result.
	 * @return string
	 */
	private function append_location_context_to_address( $address, $resolved ) {
		$address  = trim( (string) $address );
		$district = isset( $resolved['district_name'] ) ? trim( (string) $resolved['district_name'] ) : '';
		$city     = isset( $resolved['city_name'] ) ? trim( (string) $resolved['city_name'] ) : '';

		$normalized_address = $this->normalize_tr_text( $address );
		if ( '' !== $district && false === strpos( $normalized_address, $this->normalize_tr_text( $district ) ) ) {
			$address .= ' ' . $district;
			$normalized_address = $this->normalize_tr_text( $address );
		}
		if ( '' !== $city && false === strpos( $normalized_address, $this->normalize_tr_text( $city ) ) ) {
			$address .= ' ' . $city;
		}

		return trim( $address );
	}

	/**
	 * Normalize Turkish text for insensitive contains checks.
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

	/**
	 * Normalize numeric location codes for calculate endpoint.
	 *
	 * @param string $code Raw code.
	 * @param int    $pad_length Zero-padding length.
	 * @return string
	 */
	private function normalize_location_code( $code, $pad_length ) {
		$code = trim( (string) $code );
		if ( '' === $code ) {
			return '';
		}

		$code = preg_replace( '/\D+/', '', $code );
		$code = is_string( $code ) ? $code : '';
		if ( '' === $code ) {
			return '';
		}

		// Keep canonical CBS value. Only pad a single-digit value (e.g. 6 -> 06).
		if ( ctype_digit( $code ) && 1 === strlen( $code ) && $pad_length > 1 ) {
			return str_pad( $code, $pad_length, '0', STR_PAD_LEFT );
		}

		return $code;
	}

	/**
	 * Build retry payload variants for alternative location-code formats.
	 *
	 * @param array<string,mixed> $payload Base payload.
	 * @return array<int,array<string,mixed>>
	 */
	private function build_location_retry_payloads( $payload ) {
		$base_city     = isset( $payload['cityCode'] ) ? $this->normalize_location_code( (string) $payload['cityCode'], 1 ) : '';
		$base_district = isset( $payload['districtCode'] ) ? $this->normalize_location_code( (string) $payload['districtCode'], 1 ) : '';
		if ( '' === $base_city || '' === $base_district ) {
			return array();
		}

		$city_variants     = $this->build_location_code_variants( $base_city, 1 );
		$district_variants = $this->build_location_code_variants( $base_district, 1 );
		$retry_payloads    = array();
		$seen              = array();

		foreach ( $city_variants as $city_variant ) {
			foreach ( $district_variants as $district_variant ) {
				$variant_payload                  = $payload;
				$variant_payload['cityCode']     = (int) $city_variant;
				$variant_payload['districtCode'] = (int) $district_variant;
				$variant_key                     = $city_variant . '|' . $district_variant;
				if ( isset( $seen[ $variant_key ] ) ) {
					continue;
				}
				$seen[ $variant_key ] = true;
				$retry_payloads[]     = $variant_payload;

				// Prevent aggressive request fan-out during checkout.
				if ( count( $retry_payloads ) >= 6 ) {
					return $retry_payloads;
				}
			}
		}

		return $retry_payloads;
	}

	/**
	 * Build unique numeric code variants.
	 *
	 * @param string $code Base code.
	 * @param int    $preferred_length Preferred left-pad length.
	 * @return array<int,string>
	 */
	private function build_location_code_variants( $code, $preferred_length ) {
		$code = $this->normalize_location_code( $code, max( 1, (int) $preferred_length ) );
		if ( '' === $code ) {
			return array();
		}

		$variants = array( $code );
		$trimmed  = ltrim( $code, '0' );
		if ( '' !== $trimmed ) {
			$variants[] = $trimmed;
		}

		if ( '' !== $trimmed && strlen( $trimmed ) < (int) $preferred_length ) {
			$variants[] = str_pad( $trimmed, (int) $preferred_length, '0', STR_PAD_LEFT );
		}

		return array_values( array_unique( array_filter( $variants ) ) );
	}

	/**
	 * Build cache key for API shipping-rate calls.
	 *
	 * @param array<string,mixed> $package Package.
	 * @return string
	 */
	private function build_api_rate_cache_key( $package ) {
		$destination = isset( $package['destination'] ) && is_array( $package['destination'] ) ? $package['destination'] : array();
		$contents    = isset( $package['contents'] ) && is_array( $package['contents'] ) ? $package['contents'] : array();

		$fingerprint_items = array();
		foreach ( $contents as $item ) {
			if ( ! is_array( $item ) || empty( $item['data'] ) || ! $item['data'] instanceof WC_Product ) {
				continue;
			}
			/** @var WC_Product $product */
			$product = $item['data'];
			$fingerprint_items[] = array(
				'id'  => (int) $product->get_id(),
				'qty' => isset( $item['quantity'] ) ? (int) $item['quantity'] : 1,
				'kg'  => $this->calculate_piece_kg( $product ),
				'desi'=> $this->calculate_piece_desi( $product, 1 ),
			);
		}

		$fingerprint = array(
			'instance_id'   => (int) $this->instance_id,
			'destination'   => array(
				'country'   => isset( $destination['country'] ) ? (string) $destination['country'] : '',
				'city'      => isset( $destination['city'] ) ? (string) $destination['city'] : '',
				'state'     => isset( $destination['state'] ) ? (string) $destination['state'] : '',
				'address'   => isset( $destination['address'] ) ? (string) $destination['address'] : '',
				'address_2' => isset( $destination['address_2'] ) ? (string) $destination['address_2'] : '',
			),
			'items'         => $fingerprint_items,
			'packagingType' => (string) $this->get_option( 'packaging_type', '3' ),
			'pickUpType'    => (string) $this->get_option( 'pick_up_type', '1' ),
		);

		return 'demw_ship_rate_' . md5( wp_json_encode( $fingerprint ) );
	}

	/**
	 * Cache API shipping cost.
	 *
	 * @param string $cache_key Cache key.
	 * @param float  $cost      Shipping cost.
	 * @param int    $cache_ttl Cache TTL in minutes.
	 * @return void
	 */
	private function cache_api_rate_cost( $cache_key, $cost, $cache_ttl ) {
		if ( $cache_ttl <= 0 || '' === $cache_key ) {
			return;
		}

		set_transient( $cache_key, (float) $cost, $cache_ttl * MINUTE_IN_SECONDS );
	}

	/**
	 * Apply post-calculation shipping rules.
	 *
	 * @param float               $base_cost Base calculated cost.
	 * @param array<string,mixed> $package   Package details.
	 * @return float
	 */
	private function apply_cost_adjustments( $base_cost, $package ) {
		$cost       = max( 0, (float) $base_cost );
		$subtotal   = isset( $package['contents_cost'] ) ? (float) $package['contents_cost'] : 0.0;
		$free_min   = (float) $this->get_option( 'free_shipping_min_amount', '0' );
		$handling   = (float) $this->get_option( 'handling_fee', '0' );
		$min_cost   = (float) $this->get_option( 'min_shipping_cost', '0' );
		$max_cost   = (float) $this->get_option( 'max_shipping_cost', '0' );

		if ( $free_min > 0 && $subtotal >= $free_min ) {
			return 0.0;
		}

		$cost += $handling;
		$cost  = max( 0, $cost );

		if ( $min_cost > 0 ) {
			$cost = max( $min_cost, $cost );
		}

		if ( $max_cost > 0 ) {
			$cost = min( $max_cost, $cost );
		}

		return $cost;
	}

	/**
	 * Build DEMW API client on demand.
	 *
	 * @return DEMW_API_Client|null
	 */
	private function get_demw_api_client() {
		if ( $this->demw_api_client instanceof DEMW_API_Client ) {
			return $this->demw_api_client;
		}

		if ( ! class_exists( 'DEMW_Settings' ) || ! class_exists( 'DEMW_Auth' ) || ! class_exists( 'DEMW_Logger' ) || ! class_exists( 'DEMW_API_Client' ) ) {
			return null;
		}

		$this->demw_logger = new DEMW_Logger();
		$settings          = new DEMW_Settings( $this->demw_logger );
		$auth              = new DEMW_Auth( $settings );
		$this->demw_api_client = new DEMW_API_Client( $settings, $auth, $this->demw_logger );
		return $this->demw_api_client;
	}

	/**
	 * Build DEMW location resolver on demand.
	 *
	 * @return DEMW_Location_Resolver|null
	 */
	private function get_demw_location_resolver() {
		if ( $this->demw_location_resolver instanceof DEMW_Location_Resolver ) {
			return $this->demw_location_resolver;
		}

		$api_client = $this->get_demw_api_client();
		if ( ! $api_client || ! class_exists( 'DEMW_Location_Resolver' ) ) {
			return null;
		}

		$this->demw_location_resolver = new DEMW_Location_Resolver( $api_client );
		return $this->demw_location_resolver;
	}

	/**
	 * Build orderPieceList from cart package items.
	 *
	 * @param array<string,mixed> $package Package.
	 * @return array<int,array<string,mixed>>
	 */
	private function build_order_piece_list_from_package( $package ) {
		$items       = isset( $package['contents'] ) && is_array( $package['contents'] ) ? $package['contents'] : array();
		$order_pieces = array();
		$piece_index = 1;

		foreach ( $items as $item ) {
			if ( ! is_array( $item ) || empty( $item['data'] ) || ! $item['data'] instanceof WC_Product ) {
				continue;
			}

			/** @var WC_Product $product */
			$product = $item['data'];
			$qty     = isset( $item['quantity'] ) ? max( 1, absint( $item['quantity'] ) ) : 1;
			$name    = (string) $product->get_name();

			$piece_kg   = $this->calculate_piece_kg( $product );
			$piece_desi = $this->calculate_piece_desi( $product, $piece_kg );

			for ( $i = 0; $i < $qty; $i++ ) {
				$order_pieces[] = array(
					'barcode' => 'WC_CHECKOUT_P' . $piece_index,
					'desi'    => $piece_desi,
					'kg'      => $piece_kg,
					'content' => $name,
				);
				$piece_index++;
			}
		}

		if ( empty( $order_pieces ) ) {
			$order_pieces[] = array(
				'barcode' => 'WC_CHECKOUT_P1',
				'desi'    => 1,
				'kg'      => 1,
				'content' => __( 'Checkout package', 'dhl-ecommerce-mng-woocommerce' ),
			);
		}

		return $order_pieces;
	}

	/**
	 * Calculate integer KG from product weight.
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
	 * Calculate desi from product dimensions.
	 *
	 * @param WC_Product $product Product.
	 * @param int        $fallback_kg Fallback kg value.
	 * @return int
	 */
	private function calculate_piece_desi( WC_Product $product, $fallback_kg ) {
		$length = (float) wc_get_dimension( $product->get_length(), 'cm' );
		$width  = (float) wc_get_dimension( $product->get_width(), 'cm' );
		$height = (float) wc_get_dimension( $product->get_height(), 'cm' );

		if ( $length <= 0 || $width <= 0 || $height <= 0 ) {
			return max( 1, (int) $fallback_kg );
		}

		return max( 1, (int) ceil( ( $length * $width * $height ) / 3000 ) );
	}

	/**
	 * Log DEMW shipping-rate error safely.
	 *
	 * @param string               $message Message.
	 * @param array<string,mixed>  $context Context.
	 * @return void
	 */
	private function demw_log_error( $message, $context = array() ) {
		if ( ! $this->demw_logger instanceof DEMW_Logger ) {
			return;
		}
		$this->demw_logger->error( $message, $context );
	}
}
