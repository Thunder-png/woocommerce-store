<?php
/**
 * Blog shortcodes.
 *
 * @package WooCommerce_Store_Child
 */

defined( 'ABSPATH' ) || exit;

/**
 * [blog_cta] shortcode.
 *
 * Usage: [blog_cta position="intro|end"]
 *
 * @param array<string,string> $atts Attributes.
 * @return string
 */
function wcs_blog_cta_shortcode( $atts ) {
	$atts = shortcode_atts(
		array(
			'position' => 'inline',
		),
		$atts,
		'blog_cta'
	);

	$position = in_array( $atts['position'], array( 'intro', 'end' ), true ) ? $atts['position'] : 'intro';

	ob_start();

	$post_id = get_the_ID();

	// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
	$position_param = $position;
	// Local variables for template.
	$position = $position_param;

	get_template_part(
		'template-parts/blog/cta-block',
		null,
		array(
			'post_id'  => $post_id,
			'position' => $position_param,
		)
	);

	return (string) ob_get_clean();
}
add_shortcode( 'blog_cta', 'wcs_blog_cta_shortcode' );

/**
 * [blog_faq] shortcode.
 *
 * Simply renders the FAQ block for the current post if available.
 *
 * @return string
 */
function wcs_blog_faq_shortcode() {
	if ( ! is_singular( 'post' ) ) {
		return '';
	}

	ob_start();

	$post_id = get_the_ID();

	get_template_part(
		'template-parts/blog/faq-block',
		null,
		array(
			'post_id' => $post_id,
		)
	);

	return (string) ob_get_clean();
}
add_shortcode( 'blog_faq', 'wcs_blog_faq_shortcode' );

