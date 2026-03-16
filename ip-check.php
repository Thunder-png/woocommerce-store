<?php
/**
 * Temporary outbound IP check endpoint.
 *
 * Upload to site root, open in browser, then delete.
 */

header( 'Content-Type: text/plain; charset=utf-8' );
header( 'X-Robots-Tag: noindex, nofollow', true );

$ip = @file_get_contents( 'https://api.ipify.org' );
if ( false === $ip || '' === trim( (string) $ip ) ) {
	http_response_code( 502 );
	echo 'Unable to resolve outbound IP.';
	exit;
}

echo trim( (string) $ip );
