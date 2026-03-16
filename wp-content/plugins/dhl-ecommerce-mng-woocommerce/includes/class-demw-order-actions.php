<?php
/**
 * Order action handlers.
 *
 * @package DEMW
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles metabox operations.
 */
class DEMW_Order_Actions {
	/**
	 * Settings.
	 *
	 * @var DEMW_Settings
	 */
	private $settings;

	/**
	 * API client.
	 *
	 * @var DEMW_API_Client
	 */
	private $api_client;

	/**
	 * Mapper.
	 *
	 * @var DEMW_Order_Mapper
	 */
	private $mapper;

	/**
	 * Logger.
	 *
	 * @var DEMW_Logger
	 */
	private $logger;

	/**
	 * Location resolver.
	 *
	 * @var DEMW_Location_Resolver
	 */
	private $location_resolver;

	/**
	 * Supported action names.
	 *
	 * @var array<int,string>
	 */
	private $supported_actions = array(
		'test_connection',
		'create_shipment',
		'query_status',
		'get_label',
	);

	/**
	 * Constructor.
	 *
	 * @param DEMW_Settings     $settings Settings.
	 * @param DEMW_API_Client   $api_client API client.
	 * @param DEMW_Order_Mapper $mapper Mapper.
	 * @param DEMW_Logger            $logger Logger.
	 * @param DEMW_Location_Resolver $location_resolver Location resolver.
	 */
	public function __construct( DEMW_Settings $settings, DEMW_API_Client $api_client, DEMW_Order_Mapper $mapper, DEMW_Logger $logger, DEMW_Location_Resolver $location_resolver ) {
		$this->settings          = $settings;
		$this->api_client        = $api_client;
		$this->mapper            = $mapper;
		$this->logger            = $logger;
		$this->location_resolver = $location_resolver;
	}

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function hooks() {
		add_action( 'admin_post_demw_order_action', array( $this, 'handle_order_action' ) );
	}

	/**
	 * Build secure action URL for order metabox.
	 *
	 * @param int    $order_id Order id.
	 * @param string $action_name Action.
	 * @return string
	 */
	public function get_action_url( $order_id, $action_name ) {
		$url = add_query_arg(
			array(
				'action'      => 'demw_order_action',
				'order_id'    => absint( $order_id ),
				'demw_action' => sanitize_key( $action_name ),
			),
			admin_url( 'admin-post.php' )
		);

		return wp_nonce_url( $url, 'demw_order_action_' . absint( $order_id ) . '_' . sanitize_key( $action_name ) );
	}

	/**
	 * Handle admin-post order actions.
	 *
	 * @return void
	 */
	public function handle_order_action() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'Insufficient permissions.', 'dhl-ecommerce-mng-woocommerce' ) );
		}

		$order_id    = isset( $_GET['order_id'] ) ? absint( $_GET['order_id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$action_name = isset( $_GET['demw_action'] ) ? sanitize_key( wp_unslash( $_GET['demw_action'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( $order_id < 1 || ! in_array( $action_name, $this->supported_actions, true ) ) {
			wp_die( esc_html__( 'Invalid action request.', 'dhl-ecommerce-mng-woocommerce' ) );
		}

		check_admin_referer( 'demw_order_action_' . $order_id . '_' . $action_name );

		$order = wc_get_order( $order_id );
		if ( ! $order instanceof WC_Order ) {
			wp_die( esc_html__( 'Order not found.', 'dhl-ecommerce-mng-woocommerce' ) );
		}

		$result = $this->execute_action( $order, $action_name );
		if ( is_wp_error( $result ) ) {
			$this->set_order_notice( $order_id, 'error', $result->get_error_message() );
		} else {
			$this->set_order_notice( $order_id, 'success', (string) ( $result['message'] ?? __( 'Operation completed.', 'dhl-ecommerce-mng-woocommerce' ) ) );
		}

		wp_safe_redirect( $this->get_order_edit_url( $order_id ) );
		exit;
	}

	/**
	 * Get and clear order-level notice.
	 *
	 * @param int $order_id Order id.
	 * @return array<string,string>|null
	 */
	public function pull_order_notice( $order_id ) {
		$key    = 'demw_order_notice_' . get_current_user_id() . '_' . absint( $order_id );
		$notice = get_transient( $key );
		delete_transient( $key );
		return is_array( $notice ) ? $notice : null;
	}

	/**
	 * Execute action by name.
	 *
	 * @param WC_Order $order Order.
	 * @param string   $action_name Action.
	 * @return array<string,mixed>|WP_Error
	 */
	private function execute_action( WC_Order $order, $action_name ) {
		switch ( $action_name ) {
			case 'test_connection':
				return $this->run_test_connection( $order );

			case 'create_shipment':
				return $this->run_create_shipment( $order );

			case 'query_status':
				return $this->run_query_status( $order );

			case 'get_label':
				return $this->run_get_label( $order );
		}

		return new WP_Error( 'demw_invalid_order_action', __( 'Unsupported action.', 'dhl-ecommerce-mng-woocommerce' ) );
	}

	/**
	 * Action: test connection.
	 *
	 * @param WC_Order $order Order.
	 * @return array<string,mixed>|WP_Error
	 */
	private function run_test_connection( WC_Order $order ) {
		$result = $this->api_client->test_connection();
		if ( is_wp_error( $result ) ) {
			$this->persist_last_error( $order, $result->get_error_message() );
			return $result;
		}

		$this->update_common_meta( $order );
		$this->save_exchange_meta( $order );
		$this->add_order_note( $order, __( 'DHL/MNG connection test succeeded.', 'dhl-ecommerce-mng-woocommerce' ) );

		return array( 'message' => __( 'Connection test succeeded.', 'dhl-ecommerce-mng-woocommerce' ) );
	}

	/**
	 * Action: create shipment.
	 *
	 * @param WC_Order $order Order.
	 * @return array<string,mixed>|WP_Error
	 */
	private function run_create_shipment( WC_Order $order ) {
		$command_api = (string) $this->settings->get( 'shipment_command_api', 'plus_command' );
		$reference_id = (string) $order->get_meta( '_demw_reference_id', true );
		if ( '' === $reference_id ) {
			$reference_id = strtoupper( 'WC_' . (string) $order->get_order_number() );
			$order->update_meta_data( '_demw_reference_id', $reference_id );
			$order->save();
		}

		if ( 'plus_command' === $command_api ) {
			$order_created = DEMW_Helpers::as_bool( $order->get_meta( '_demw_order_created', true ) );
			if ( ! $order_created ) {
				return $this->run_stage_recipient_and_order( $order, $reference_id );
			}
		}

		return $this->run_stage_barcode( $order, $reference_id, $command_api );
	}

	/**
	 * Action: query status.
	 *
	 * @param WC_Order $order Order.
	 * @return array<string,mixed>|WP_Error
	 */
	private function run_query_status( WC_Order $order ) {
		$shipment_id     = (string) $order->get_meta( '_demw_shipment_id', true );
		$tracking_number = (string) $order->get_meta( '_demw_tracking_number', true );
		$reference_id    = (string) $order->get_meta( '_demw_reference_id', true );
		$old_status      = (string) $order->get_meta( '_demw_last_status', true );

		$result = '' !== $reference_id
			? $this->api_client->query_shipment_status_by_reference( $reference_id )
			: $this->api_client->query_shipment_status( $shipment_id, $tracking_number );
		if ( is_wp_error( $result ) ) {
			$this->persist_last_error( $order, $result->get_error_message() );
			$this->save_exchange_meta( $order );
			return $result;
		}

		$data       = is_array( $result['data'] ) ? $result['data'] : array();
		$new_status = $this->extract_value( $data, array( 'shipment.shipmentLastMove', 'shipment.shipmentStatusCode', 'shipmentStatus', 'status', '0.shipmentLastMove', '0.eventStatus', 'eventStatus' ) );
		if ( '' === $new_status ) {
			$new_status = __( 'Status response received', 'dhl-ecommerce-mng-woocommerce' );
		}

		$order->update_meta_data( '_demw_last_status', $new_status );
		$order->update_meta_data( '_demw_last_error', '' );
		$this->update_common_meta( $order );
		$this->save_exchange_meta( $order );
		$order->save();

		if ( $new_status !== $old_status ) {
			$this->add_order_note( $order, sprintf( __( 'DHL/MNG status updated: %s', 'dhl-ecommerce-mng-woocommerce' ), $new_status ) );
		}

		return array( 'message' => sprintf( __( 'Shipment status refreshed: %s', 'dhl-ecommerce-mng-woocommerce' ), $new_status ) );
	}

	/**
	 * Action: get label.
	 *
	 * @param WC_Order $order Order.
	 * @return array<string,mixed>|WP_Error
	 */
	private function run_get_label( WC_Order $order ) {
		$shipment_id     = (string) $order->get_meta( '_demw_shipment_id', true );
		$tracking_number = (string) $order->get_meta( '_demw_tracking_number', true );
		$reference_id    = (string) $order->get_meta( '_demw_reference_id', true );

		$result = $this->api_client->get_label( $shipment_id, $tracking_number, $reference_id );
		if ( is_wp_error( $result ) ) {
			$this->persist_last_error( $order, $result->get_error_message() );
			$this->save_exchange_meta( $order );
			return $result;
		}

		$data      = is_array( $result['data'] ) ? $result['data'] : array();
		$label_url = $this->extract_value( $data, array( 'labelUrl', 'trackingUrl', 'shipmentFollowUrl', 'shipment.shipmentFollowUrl', 'url', 'pdfUrl' ) );
		$shipment_from_response = $this->extract_value( $data, array( 'shipmentId', 'shipment.shipmentId' ) );
		$tracking_from_response = $this->extract_value( $data, array( 'trackingNumber', 'barcode', 'shipmentPieceList.0.barcode', 'cargoBarcode' ) );

		if ( '' === $shipment_id && '' !== $shipment_from_response ) {
			$order->update_meta_data( '_demw_shipment_id', $shipment_from_response );
		}
		if ( '' === $tracking_number && '' !== $tracking_from_response ) {
			$order->update_meta_data( '_demw_tracking_number', $tracking_from_response );
		}

		$order->update_meta_data( '_demw_label_url', $label_url );
		$order->update_meta_data( '_demw_label_data', DEMW_Helpers::encode_for_storage( $data ) );
		$order->update_meta_data( '_demw_last_error', '' );
		$this->update_common_meta( $order );
		$this->save_exchange_meta( $order );
		$order->save();

		$note = '' !== $label_url
			? sprintf( __( 'DHL/MNG label retrieved: %s', 'dhl-ecommerce-mng-woocommerce' ), $label_url )
			: __( 'DHL/MNG label response retrieved.', 'dhl-ecommerce-mng-woocommerce' );
		$this->add_order_note( $order, $note );

		return array( 'message' => __( 'Label retrieval completed.', 'dhl-ecommerce-mng-woocommerce' ) );
	}

	/**
	 * Update common order meta fields.
	 *
	 * @param WC_Order $order Order.
	 * @return void
	 */
	private function update_common_meta( WC_Order $order ) {
		$order->update_meta_data( '_demw_carrier', 'dhl_ecommerce_mng' );
		$order->update_meta_data( '_demw_environment', (string) $this->settings->get( 'environment', 'sandbox' ) );
		$order->update_meta_data( '_demw_last_synced_at', current_time( 'mysql' ) );
	}

	/**
	 * Persist last API exchange into order meta.
	 *
	 * @param WC_Order $order Order.
	 * @return void
	 */
	private function save_exchange_meta( WC_Order $order ) {
		$exchange      = $this->api_client->get_last_exchange();
		$request_body  = isset( $exchange['request_body'] ) ? $this->redact_sensitive_data( $exchange['request_body'] ) : array();
		$response_body = isset( $exchange['response_body'] ) ? $this->redact_sensitive_data( $exchange['response_body'] ) : array();

		$order->update_meta_data( '_demw_last_request', DEMW_Helpers::encode_for_storage( $request_body ) );
		$order->update_meta_data( '_demw_last_response', DEMW_Helpers::encode_for_storage( $response_body ) );
		$order->save();
	}

	/**
	 * Save last error message to meta and log technical details.
	 *
	 * @param WC_Order $order Order.
	 * @param string   $error_message Error.
	 * @return void
	 */
	private function persist_last_error( WC_Order $order, $error_message ) {
		$order->update_meta_data( '_demw_last_error', sanitize_text_field( (string) $error_message ) );
		$this->update_common_meta( $order );
		$order->save();

		$this->logger->error(
			'Order action failed',
			array(
				'order_id' => $order->get_id(),
				'error'    => $error_message,
			)
		);
	}

	/**
	 * Add internal order note.
	 *
	 * @param WC_Order $order Order.
	 * @param string   $message Message.
	 * @return void
	 */
	private function add_order_note( WC_Order $order, $message ) {
		$order->add_order_note( sanitize_text_field( $message ), false, true );
	}

	/**
	 * Store temporary notice for order edit page.
	 *
	 * @param int    $order_id Order id.
	 * @param string $type Type.
	 * @param string $message Message.
	 * @return void
	 */
	private function set_order_notice( $order_id, $type, $message ) {
		set_transient(
			'demw_order_notice_' . get_current_user_id() . '_' . absint( $order_id ),
			array(
				'type'    => sanitize_key( $type ),
				'message' => sanitize_text_field( (string) $message ),
			),
			60
		);
	}

	/**
	 * Read first available nested value from candidate paths.
	 *
	 * @param array<string,mixed> $data Data.
	 * @param array<int,string>   $paths Dot paths.
	 * @return string
	 */
	private function extract_value( $data, $paths ) {
		foreach ( $paths as $path ) {
			$segments = explode( '.', $path );
			$current  = $data;
			foreach ( $segments as $segment ) {
				if ( is_array( $current ) && array_key_exists( $segment, $current ) ) {
					$current = $current[ $segment ];
					continue;
				}
				$current = null;
				break;
			}

			if ( is_scalar( $current ) && '' !== (string) $current ) {
				return (string) $current;
			}
		}

		return '';
	}

	/**
	 * Redact sensitive keys from nested arrays before storing order meta.
	 *
	 * @param mixed $value Data.
	 * @return mixed
	 */
	private function redact_sensitive_data( $value ) {
		$sensitive_keys = array( 'password', 'api_secret', 'bearer_token', 'authorization', 'jwt', 'refreshToken', 'token' );

		if ( is_array( $value ) ) {
			foreach ( $value as $key => $item ) {
				if ( in_array( (string) $key, $sensitive_keys, true ) ) {
					$value[ $key ] = '[redacted]';
					continue;
				}
				$value[ $key ] = $this->redact_sensitive_data( $item );
			}
		}

		return $value;
	}

	/**
	 * Get order edit URL for both legacy and HPOS screens.
	 *
	 * @param int $order_id Order id.
	 * @return string
	 */
	private function get_order_edit_url( $order_id ) {
		$order_id = absint( $order_id );
		$is_hpos  = false;

		if ( class_exists( '\Automattic\WooCommerce\Utilities\OrderUtil' ) ) {
			$is_hpos = \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled();
		}

		if ( $is_hpos ) {
			return admin_url( 'admin.php?page=wc-orders&action=edit&id=' . $order_id );
		}

		return admin_url( 'post.php?post=' . $order_id . '&action=edit' );
	}

	/**
	 * Stage 1+2: Create recipient and order.
	 *
	 * @param WC_Order $order Order.
	 * @param string   $fallback_reference_id Fallback reference id.
	 * @return array<string,mixed>|WP_Error
	 */
	private function run_stage_recipient_and_order( WC_Order $order, $fallback_reference_id ) {
		$recipient_payload = $this->mapper->map_order_to_recipient_payload( $order );
		if ( is_wp_error( $recipient_payload ) ) {
			$this->persist_last_error( $order, $recipient_payload->get_error_message() );
			return $recipient_payload;
		}
		$recipient_payload = $this->enrich_payload_recipient_location( $recipient_payload, $order );
		if ( is_wp_error( $recipient_payload ) ) {
			$this->persist_last_error( $order, $recipient_payload->get_error_message() );
			return $recipient_payload;
		}
		$recipient_validation = $this->validate_recipient_payload_for_branch_resolution( $recipient_payload );
		if ( is_wp_error( $recipient_validation ) ) {
			$this->persist_last_error( $order, $recipient_validation->get_error_message() );
			return $recipient_validation;
		}

		$recipient_result = $this->api_client->create_recipient( $recipient_payload );
		if ( is_wp_error( $recipient_result ) ) {
			$this->persist_last_error( $order, $recipient_result->get_error_message() );
			$this->save_exchange_meta( $order );
			return $recipient_result;
		}

		$order_payload = $this->mapper->map_order_to_standard_order_payload( $order );
		if ( is_wp_error( $order_payload ) ) {
			$this->persist_last_error( $order, $order_payload->get_error_message() );
			return $order_payload;
		}
		$order_payload = $this->enrich_payload_recipient_location( $order_payload, $order );
		if ( is_wp_error( $order_payload ) ) {
			$this->persist_last_error( $order, $order_payload->get_error_message() );
			return $order_payload;
		}
		$order_validation = $this->validate_recipient_payload_for_branch_resolution( $order_payload );
		if ( is_wp_error( $order_validation ) ) {
			$this->persist_last_error( $order, $order_validation->get_error_message() );
			return $order_validation;
		}

		$order_result = $this->api_client->create_order( $order_payload );
		if ( is_wp_error( $order_result ) ) {
			$this->persist_last_error( $order, $order_result->get_error_message() );
			$this->save_exchange_meta( $order );
			return $order_result;
		}

		$order_data    = is_array( $order_result['data'] ) ? $order_result['data'] : array();
		$reference_id  = $this->extract_value( $order_data, array( 'referenceId' ) );
		if ( '' === $reference_id ) {
			$reference_id = (string) $fallback_reference_id;
		}

		$order->update_meta_data( '_demw_reference_id', $reference_id );
		$order->update_meta_data( '_demw_order_created', 1 );
		$order->update_meta_data( '_demw_order_synced_at', time() );
		$order->update_meta_data( '_demw_last_status', __( 'Recipient and order data synced', 'dhl-ecommerce-mng-woocommerce' ) );
		$order->update_meta_data( '_demw_last_error', '' );
		$this->update_common_meta( $order );
		$this->save_exchange_meta( $order );
		$order->save();

		$this->add_order_note(
			$order,
			sprintf(
				/* translators: %s: reference id */
				__( 'DHL/MNG stage completed: createRecipient + createOrder. Reference: %s. Run Create Shipment again to generate barcode.', 'dhl-ecommerce-mng-woocommerce' ),
				$reference_id
			)
		);

		return array( 'message' => __( 'Recipient and order synced. Click Create Shipment again for barcode generation.', 'dhl-ecommerce-mng-woocommerce' ) );
	}

	/**
	 * Stage 3: Barcode generation.
	 *
	 * @param WC_Order $order Order.
	 * @param string   $reference_id Reference id.
	 * @param string   $command_api Command API setting.
	 * @return array<string,mixed>|WP_Error
	 */
	private function run_stage_barcode( WC_Order $order, $reference_id, $command_api ) {
		$payload = $this->mapper->map_order_to_barcode_payload( $order );
		if ( is_wp_error( $payload ) ) {
			$this->persist_last_error( $order, $payload->get_error_message() );
			return $payload;
		}

		if ( 'plus_command' === $command_api ) {
			$wait_error = $this->get_barcode_cooldown_error( $order );
			if ( is_wp_error( $wait_error ) ) {
				return $wait_error;
			}
		}

		if ( isset( $payload['referenceId'] ) && '' === (string) $payload['referenceId'] ) {
			$payload['referenceId'] = $reference_id;
		}

		$result = $this->api_client->create_barcode( $payload );
		if ( is_wp_error( $result ) ) {
			$this->persist_last_error( $order, $result->get_error_message() );
			$this->save_exchange_meta( $order );
			return $result;
		}

		$data            = is_array( $result['data'] ) ? $result['data'] : array();
		$shipment_id     = $this->extract_value( $data, array( 'shipmentId', 'orderInvoiceId', 'shipment.shipmentId' ) );
		$tracking_number = $this->extract_value( $data, array( 'trackingNumber', 'barcode', 'shipmentPieceList.0.barcode', 'barcodes.0.value' ) );
		if ( '' === $tracking_number && isset( $payload['referenceId'] ) ) {
			$tracking_number = (string) $payload['referenceId'];
		}
		if ( '' === $shipment_id && '' === $tracking_number ) {
			$this->persist_last_error( $order, __( 'Shipment created but shipment ID/tracking number was missing in response.', 'dhl-ecommerce-mng-woocommerce' ) );
		}

		$order->update_meta_data( '_demw_carrier', 'dhl_ecommerce_mng' );
		$order->update_meta_data( '_demw_reference_id', $reference_id );
		$order->update_meta_data( '_demw_shipment_id', $shipment_id );
		$order->update_meta_data( '_demw_tracking_number', $tracking_number );
		$order->update_meta_data( '_demw_last_status', __( 'Shipment created', 'dhl-ecommerce-mng-woocommerce' ) );
		$order->update_meta_data( '_demw_last_error', '' );
		$this->update_common_meta( $order );
		$this->save_exchange_meta( $order );
		$order->save();

		$note = sprintf(
			/* translators: 1: shipment id, 2: tracking number */
			__( 'DHL/MNG barcode shipment created. Shipment ID: %1$s, Tracking: %2$s', 'dhl-ecommerce-mng-woocommerce' ),
			'' !== $shipment_id ? $shipment_id : '-',
			'' !== $tracking_number ? $tracking_number : '-'
		);
		$this->add_order_note( $order, $note );

		return array( 'message' => __( 'Shipment created successfully.', 'dhl-ecommerce-mng-woocommerce' ) );
	}

	/**
	 * Build wait error when barcode is requested too soon.
	 *
	 * @param WC_Order $order Order.
	 * @return WP_Error|null
	 */
	private function get_barcode_cooldown_error( WC_Order $order ) {
		$synced_at = absint( $order->get_meta( '_demw_order_synced_at', true ) );
		$wait_sec  = 60;
		if ( $synced_at > 0 && ( time() - $synced_at ) < $wait_sec ) {
			$remaining = max( 1, $wait_sec - ( time() - $synced_at ) );
			return new WP_Error(
				'demw_wait_for_branch_resolution',
				sprintf(
					/* translators: %d: seconds */
					__(
						'Order/recipient synced successfully, but destination branch may still be resolving on carrier side. Please wait %d seconds, then run Create Shipment again.',
						'dhl-ecommerce-mng-woocommerce'
					),
					$remaining
				)
			);
		}

		return null;
	}

	/**
	 * Enrich payload recipient block with city/district codes.
	 *
	 * @param array<string,mixed> $payload Payload.
	 * @param WC_Order            $order Order.
	 * @return array<string,mixed>|WP_Error
	 */
	private function enrich_payload_recipient_location( $payload, WC_Order $order ) {
		if ( ! isset( $payload['recipient'] ) || ! is_array( $payload['recipient'] ) ) {
			return $payload;
		}

		$resolved = $this->location_resolver->resolve_for_order( $order );
		if ( is_wp_error( $resolved ) ) {
			return new WP_Error(
				'demw_location_resolution_failed',
				sprintf(
					/* translators: %s: details */
					__( 'Recipient location codes could not be resolved from CBS API. Shipment payload was not sent to prevent branch resolution errors. Detail: %s', 'dhl-ecommerce-mng-woocommerce' ),
					$resolved->get_error_message()
				)
			);
		}

		$payload['recipient']['cityCode']     = (int) $resolved['city_code'];
		$payload['recipient']['districtCode'] = (int) $resolved['district_code'];
		$payload['recipient']['cityName']     = (string) $resolved['city_name'];
		$payload['recipient']['districtName'] = (string) $resolved['district_name'];
		if ( ! empty( $resolved['normalized_address'] ) ) {
			$payload['recipient']['address'] = (string) $resolved['normalized_address'];
		}

		$order->update_meta_data( '_demw_resolved_neighborhood', (string) ( $resolved['neighborhood'] ?? '' ) );
		$order->update_meta_data( '_demw_is_mobile_area', ! empty( $resolved['is_mobile_area'] ) ? 1 : 0 );
		$order->update_meta_data( '_demw_is_out_of_service_area', ! empty( $resolved['is_out_of_service'] ) ? 1 : 0 );

		return $payload;
	}

	/**
	 * Validate recipient payload fields required for destination branch detection.
	 *
	 * @param array<string,mixed> $payload Payload.
	 * @return true|WP_Error
	 */
	private function validate_recipient_payload_for_branch_resolution( $payload ) {
		if ( ! isset( $payload['recipient'] ) || ! is_array( $payload['recipient'] ) ) {
			return new WP_Error( 'demw_missing_recipient_block', __( 'Recipient block is missing in shipment payload.', 'dhl-ecommerce-mng-woocommerce' ) );
		}

		$recipient = $payload['recipient'];
		$city_code = absint( $recipient['cityCode'] ?? 0 );
		$district_code = absint( $recipient['districtCode'] ?? 0 );
		$address = trim( (string) ( $recipient['address'] ?? '' ) );
		$full_name = trim( (string) ( $recipient['fullName'] ?? '' ) );
		$mobile_phone = trim( (string) ( $recipient['mobilePhoneNumber'] ?? '' ) );
		$home_phone = trim( (string) ( $recipient['homePhoneNumber'] ?? '' ) );
		$business_phone = trim( (string) ( $recipient['bussinessPhoneNumber'] ?? '' ) );
		$customer_id = trim( (string) ( $recipient['customerId'] ?? '' ) );

		if ( $city_code < 1 || $district_code < 1 ) {
			return new WP_Error(
				'demw_missing_location_codes',
				__( 'Recipient cityCode/districtCode is empty. CBS location mapping must succeed before createRecipient/createOrder.', 'dhl-ecommerce-mng-woocommerce' )
			);
		}

		if ( '' === $address ) {
			return new WP_Error(
				'demw_missing_recipient_address',
				__( 'Recipient address is empty. Branch detection requires a full address.', 'dhl-ecommerce-mng-woocommerce' )
			);
		}

		if ( '' === $customer_id && '' === $full_name ) {
			return new WP_Error(
				'demw_missing_recipient_name',
				__( 'Recipient fullName is empty. Either customerId or fullName must be provided.', 'dhl-ecommerce-mng-woocommerce' )
			);
		}

		if ( '' === $mobile_phone && '' === $home_phone && '' === $business_phone ) {
			return new WP_Error(
				'demw_missing_recipient_phone',
				__( 'At least one recipient phone number is required for branch resolution.', 'dhl-ecommerce-mng-woocommerce' )
			);
		}

		return true;
	}
}
