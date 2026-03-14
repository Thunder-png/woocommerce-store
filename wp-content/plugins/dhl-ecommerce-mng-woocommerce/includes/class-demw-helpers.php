<?php
/**
 * Helper utilities.
 *
 * @package DEMW
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Utility helper class.
 */
class DEMW_Helpers {
	/**
	 * Recursively sanitize scalar values.
	 *
	 * @param mixed $value Value to sanitize.
	 * @return mixed
	 */
	public static function deep_sanitize( $value ) {
		if ( is_array( $value ) ) {
			foreach ( $value as $key => $item ) {
				$value[ sanitize_key( $key ) ] = self::deep_sanitize( $item );
				if ( $key !== sanitize_key( $key ) ) {
					unset( $value[ $key ] );
				}
			}

			return $value;
		}

		if ( is_scalar( $value ) || null === $value ) {
			return sanitize_text_field( (string) $value );
		}

		return '';
	}

	/**
	 * Return bool from mixed checkbox-like values.
	 *
	 * @param mixed $value Value.
	 * @return bool
	 */
	public static function as_bool( $value ) {
		return in_array( (string) $value, array( '1', 'true', 'yes', 'on' ), true );
	}

	/**
	 * Trim trailing slash from URL and sanitize.
	 *
	 * @param string $url URL.
	 * @return string
	 */
	public static function sanitize_base_url( $url ) {
		$url = esc_url_raw( trim( (string) $url ) );
		return untrailingslashit( $url );
	}

	/**
	 * Hide sensitive value for UI display.
	 *
	 * @param string $value Sensitive string.
	 * @return string
	 */
	public static function mask_secret( $value ) {
		$value = (string) $value;
		$len   = strlen( $value );
		if ( $len <= 4 ) {
			return str_repeat( '*', $len );
		}

		return substr( $value, 0, 2 ) . str_repeat( '*', max( 2, $len - 4 ) ) . substr( $value, -2 );
	}

	/**
	 * Normalize a body to a JSON string for storage.
	 *
	 * @param mixed $body Data.
	 * @return string
	 */
	public static function encode_for_storage( $body ) {
		if ( is_string( $body ) ) {
			return $body;
		}

		$encoded = wp_json_encode( $body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
		return is_string( $encoded ) ? $encoded : '';
	}
}
