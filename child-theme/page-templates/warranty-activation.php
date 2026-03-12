<?php
/**
 * Template Name: Garanti Aktivasyonu
 *
 * @package WooCommerceStoreChild
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<main id="primary" class="site-main wcs-page-main">
	<div class="wcs-page-main__inner">
		<?php echo do_shortcode( '[wcs_warranty_activation]' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</div>
</main>

<?php
get_footer();
