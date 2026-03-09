<?php
/**
 * Native WordPress page template by slug.
 *
 * @package WooCommerce_Store_Child
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="primary" class="site-main wcs-policy-page">
    <div class="wcs-policy-page__container">
        <?php while ( have_posts() ) : the_post(); ?>
            <article <?php post_class( 'wcs-policy-content' ); ?>>
                <header class="wcs-policy-header">
                    <h1 class="entry-title"><?php the_title(); ?></h1>
                </header>

                <div class="entry-content">
                    <?php the_content(); ?>
                </div>
            </article>
        <?php endwhile; ?>
    </div>
</main>
<?php
get_footer();
