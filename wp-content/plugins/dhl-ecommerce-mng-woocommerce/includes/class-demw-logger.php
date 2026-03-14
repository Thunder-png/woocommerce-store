<?php
/**
 * Logging service.
 *
 * @package DEMW
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Logger wrapper around WC logger.
 */
class DEMW_Logger {
	/**
	 * Log source.
	 *
	 * @var string
	 */
	private $source = 'demw';

	/**
	 * Write debug log if enabled.
	 *
	 * @param string $message Message.
	 * @param array  $context Context.
	 * @return void
	 */
	public function debug( $message, $context = array() ) {
		$this->write( 'debug', $message, $context );
	}

	/**
	 * Write info log.
	 *
	 * @param string $message Message.
	 * @param array  $context Context.
	 * @return void
	 */
	public function info( $message, $context = array() ) {
		$this->write( 'info', $message, $context );
	}

	/**
	 * Write error log.
	 *
	 * @param string $message Message.
	 * @param array  $context Context.
	 * @return void
	 */
	public function error( $message, $context = array() ) {
		$this->write( 'error', $message, $context );
	}

	/**
	 * Log outbound request.
	 *
	 * @param string $endpoint Endpoint.
	 * @param array  $headers Headers.
	 * @param mixed  $body Body.
	 * @param bool   $log_body Whether body logging is enabled.
	 * @return void
	 */
	public function request( $endpoint, $headers, $body, $log_body ) {
		$payload = array(
			'endpoint' => $endpoint,
			'headers'  => $this->redact_headers( (array) $headers ),
		);

		if ( $log_body ) {
			$payload['body'] = $body;
		}

		$this->write( 'debug', 'DEMW request', $payload );
	}

	/**
	 * Log inbound response.
	 *
	 * @param string $endpoint Endpoint.
	 * @param int    $status HTTP status.
	 * @param mixed  $body Body.
	 * @param bool   $log_body Whether body logging is enabled.
	 * @return void
	 */
	public function response( $endpoint, $status, $body, $log_body ) {
		$payload = array(
			'endpoint' => $endpoint,
			'status'   => (int) $status,
		);

		if ( $log_body ) {
			$payload['body'] = $body;
		}

		$this->write( 'debug', 'DEMW response', $payload );
	}

	/**
	 * Redact credentials in headers.
	 *
	 * @param array $headers Headers.
	 * @return array
	 */
	public function redact_headers( $headers ) {
		$redacted_keys = array(
			'authorization',
			'x-ibm-client-id',
			'x-ibm-client-secret',
			'x-api-key',
		);

		foreach ( $headers as $header_key => $header_value ) {
			if ( in_array( strtolower( (string) $header_key ), $redacted_keys, true ) ) {
				$headers[ $header_key ] = DEMW_Helpers::mask_secret( (string) $header_value );
			}
		}

		return $headers;
	}

	/**
	 * Write to WooCommerce logger.
	 *
	 * @param string $level Level.
	 * @param string $message Message.
	 * @param array  $context Context.
	 * @return void
	 */
	private function write( $level, $message, $context = array() ) {
		if ( ! function_exists( 'wc_get_logger' ) ) {
			return;
		}

		$logger  = wc_get_logger();
		$context = array_merge(
			array(
				'source' => $this->source,
				'ts'     => gmdate( 'c' ),
			),
			(array) $context
		);

		$logger->log( $level, (string) $message . ' ' . wp_json_encode( $context ), array( 'source' => $this->source ) );
	}
}
