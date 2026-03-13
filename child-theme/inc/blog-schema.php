<?php
/**
 * Blog schema (JSON-LD) helpers.
 *
 * @package WooCommerce_Store_Child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Output JSON-LD schema for single blog posts.
 */
function wcs_blog_output_schema() {
	if ( ! is_singular( 'post' ) ) {
		return;
	}

	$post = get_post();

	if ( ! $post instanceof WP_Post ) {
		return;
	}

	$schema = array(
		'@context'        => 'https://schema.org',
		'@type'           => 'Article',
		'headline'        => get_the_title( $post ),
		'datePublished'   => get_the_date( DATE_W3C, $post ),
		'dateModified'    => get_the_modified_date( DATE_W3C, $post ),
		'mainEntityOfPage'=> get_permalink( $post ),
		'author'          => array(
			'@type' => 'Person',
			'name'  => get_the_author_meta( 'display_name', $post->post_author ),
		),
	);

	if ( has_post_thumbnail( $post ) ) {
		$thumb_id  = get_post_thumbnail_id( $post );
		$image_url = $thumb_id ? wp_get_attachment_image_url( $thumb_id, 'full' ) : '';

		if ( $image_url ) {
			$schema['image'] = $image_url;
		}
	}

	// BreadcrumbList.
	$breadcrumb_items = array();
	$position         = 1;

	$breadcrumb_items[] = array(
		'@type'    => 'ListItem',
		'position' => $position++,
		'name'     => __( 'Ana Sayfa', 'woocommerce-store-child' ),
		'item'     => home_url( '/' ),
	);

	$blog_url = get_permalink( get_option( 'page_for_posts' ) );
	$blog_url = $blog_url ? $blog_url : home_url( '/blog/' );

	$breadcrumb_items[] = array(
		'@type'    => 'ListItem',
		'position' => $position++,
		'name'     => __( 'Blog', 'woocommerce-store-child' ),
		'item'     => $blog_url,
	);

	$categories = get_the_category( $post->ID );
	if ( ! empty( $categories ) ) {
		$cat = $categories[0];
		$breadcrumb_items[] = array(
			'@type'    => 'ListItem',
			'position' => $position++,
			'name'     => $cat->name,
			'item'     => get_category_link( $cat ),
		);
	}

	$breadcrumb_items[] = array(
		'@type'    => 'ListItem',
		'position' => $position++,
		'name'     => get_the_title( $post ),
		'item'     => get_permalink( $post ),
	);

	$breadcrumb_schema = array(
		'@context'        => 'https://schema.org',
		'@type'           => 'BreadcrumbList',
		'itemListElement' => $breadcrumb_items,
	);

	// FAQ schema if available.
	$faq_schema = null;
	$faq_items  = get_post_meta( $post->ID, '_wcs_blog_faq_items', true );

	if ( is_array( $faq_items ) && ! empty( $faq_items ) ) {
		$faq_entities = array();

		foreach ( $faq_items as $item ) {
			$question = isset( $item['question'] ) ? trim( (string) $item['question'] ) : '';
			$answer   = isset( $item['answer'] ) ? trim( (string) $item['answer'] ) : '';

			if ( '' === $question || '' === $answer ) {
				continue;
			}

			$faq_entities[] = array(
				'@type'          => 'Question',
				'name'           => $question,
				'acceptedAnswer' => array(
					'@type' => 'Answer',
					'text'  => wp_strip_all_tags( $answer ),
				),
			);
		}

		if ( ! empty( $faq_entities ) ) {
			$faq_schema = array(
				'@context'   => 'https://schema.org',
				'@type'      => 'FAQPage',
				'mainEntity' => $faq_entities,
			);
		}
	}

	$schemas = array(
		$schema,
		$breadcrumb_schema,
	);

	if ( $faq_schema ) {
		$schemas[] = $faq_schema;
	}

	echo '<script type="application/ld+json">' . wp_json_encode( $schemas ) . '</script>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}
add_action( 'wp_head', 'wcs_blog_output_schema', 60 );

