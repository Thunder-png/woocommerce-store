<?php
/**
 * Blog helper functions.
 *
 * @package WooCommerce_Store_Child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Calculate estimated reading time for a post.
 *
 * @param int|null $post_id Post ID.
 * @return string
 */
function get_estimated_reading_time( $post_id = null ) {
	$post = $post_id ? get_post( $post_id ) : get_post();

	if ( ! $post instanceof WP_Post ) {
		return '';
	}

	$content = wp_strip_all_tags( $post->post_content );
	$words   = str_word_count( $content );

	if ( $words <= 0 ) {
		return '';
	}

	$words_per_minute = 220;
	$minutes          = max( 1, (int) ceil( $words / $words_per_minute ) );

	/* translators: %d: minutes */
	return sprintf( _n( '%d dk okuma', '%d dk okuma', $minutes, 'woocommerce-store-child' ), $minutes );
}

/**
 * Trim excerpt or content to a given word length.
 *
 * @param int      $length  Word length.
 * @param int|null $post_id Optional post ID.
 * @return string
 */
function blog_get_trimmed_excerpt( $length = 26, $post_id = null ) {
	$post = $post_id ? get_post( $post_id ) : get_post();

	if ( ! $post instanceof WP_Post ) {
		return '';
	}

	$excerpt = $post->post_excerpt;

	if ( ! $excerpt ) {
		$excerpt = wp_strip_all_tags( $post->post_content );
	}

	return wp_trim_words( $excerpt, $length );
}

/**
 * Get featured blog post.
 *
 * If a post is marked with meta key "_wcs_featured_post", prefer it.
 * Otherwise return the latest post.
 *
 * @return WP_Post|null
 */
function blog_get_featured_post() {
	$featured_query = new WP_Query(
		array(
			'post_type'      => 'post',
			'posts_per_page' => 1,
			'meta_key'       => '_wcs_featured_post',
			'meta_value'     => '1',
		)
	);

	if ( $featured_query->have_posts() ) {
		return $featured_query->posts[0];
	}

	wp_reset_postdata();

	$latest_query = new WP_Query(
		array(
			'post_type'      => 'post',
			'posts_per_page' => 1,
		)
	);

	if ( $latest_query->have_posts() ) {
		return $latest_query->posts[0];
	}

	return null;
}

/**
 * Get related posts by shared categories.
 *
 * @param int $post_id Post ID.
 * @param int $limit   Number of posts to return.
 * @return WP_Post[]
 */
function get_related_posts_by_category( $post_id, $limit = 3 ) {
	$categories = wp_get_post_categories( $post_id );

	if ( empty( $categories ) ) {
		return array();
	}

	$query = new WP_Query(
		array(
			'post_type'           => 'post',
			'posts_per_page'      => $limit,
			'post__not_in'        => array( $post_id ),
			'ignore_sticky_posts' => true,
			'category__in'        => $categories,
		)
	);

	if ( ! $query->have_posts() ) {
		return array();
	}

	return $query->posts;
}

/**
 * Render simple breadcrumb for blog context.
 */
function render_blog_breadcrumb() {
	$items = array();

	$items[] = array(
		'url'   => home_url( '/' ),
		'label' => __( 'Ana Sayfa', 'woocommerce-store-child' ),
	);

	$blog_url   = get_permalink( get_option( 'page_for_posts' ) );
	$items[]    = array(
		'url'   => $blog_url ? $blog_url : home_url( '/blog/' ),
		'label' => __( 'Blog', 'woocommerce-store-child' ),
	);

	if ( is_category() ) {
		$term = get_queried_object();
		if ( $term instanceof WP_Term ) {
			$items[] = array(
				'url'   => get_category_link( $term ),
				'label' => $term->name,
			);
		}
	} elseif ( is_singular( 'post' ) ) {
		$categories = get_the_category();
		if ( ! empty( $categories ) ) {
			$cat       = $categories[0];
			$items[]   = array(
				'url'   => get_category_link( $cat ),
				'label' => $cat->name,
			);
		}
		$items[] = array(
			'url'   => get_permalink(),
			'label' => get_the_title(),
		);
	} elseif ( is_search() ) {
		$items[] = array(
			'url'   => '',
			'label' => __( 'Arama Sonuçları', 'woocommerce-store-child' ),
		);
	}

	echo '<ol class="wcs-breadcrumb-list" itemscope itemtype="https://schema.org/BreadcrumbList">';

	foreach ( $items as $index => $item ) {
		$position = $index + 1;
		$is_last  = $position === count( $items );

		echo '<li class="wcs-breadcrumb-list__item" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';

		if ( $item['url'] && ! $is_last ) {
			printf(
				'<a href="%1$s" itemprop="item"><span itemprop="name">%2$s</span></a>',
				esc_url( $item['url'] ),
				esc_html( $item['label'] )
			);
		} else {
			printf(
				'<span itemprop="name">%s</span>',
				esc_html( $item['label'] )
			);
		}

		printf(
			'<meta itemprop="position" content="%d" />',
			(int) $position
		);

		echo '</li>';
	}

	echo '</ol>';
}

/**
 * Render category filter pills for blog archives.
 *
 * @param WP_Term|null $current_category Optional active category.
 */
function wcs_blog_render_category_filter( $current_category = null ) {
	$categories = get_categories(
		array(
			'hide_empty' => true,
		)
	);

	if ( empty( $categories ) ) {
		return;
	}

	$blog_url = get_permalink( get_option( 'page_for_posts' ) );
	$blog_url = $blog_url ? $blog_url : home_url( '/blog/' );

	echo '<div class="wcs-blog-category-filter">';

	printf(
		'<a href="%1$s" class="wcs-blog-category-filter__pill %2$s">%3$s</a>',
		esc_url( $blog_url ),
		is_home() && ! is_category() ? 'is-active' : '',
		esc_html__( 'Tüm Yazılar', 'woocommerce-store-child' )
	);

	foreach ( $categories as $category ) {
		$is_active = ( $current_category instanceof WP_Term && (int) $current_category->term_id === (int) $category->term_id ) || ( is_category( $category ) );

		printf(
			'<a href="%1$s" class="wcs-blog-category-filter__pill %2$s">%3$s</a>',
			esc_url( get_category_link( $category ) ),
			$is_active ? 'is-active' : '',
			esc_html( $category->name )
		);
	}

	echo '</div>';
}

/**
 * Helper to render featured post block.
 */
function wcs_blog_render_featured_post() {
	get_template_part( 'template-parts/blog/featured-post' );
}

/**
 * Register blog-specific image sizes.
 */
function wcs_blog_register_image_sizes() {
	add_image_size( 'wcs-blog-card', 800, 480, true );
	add_image_size( 'wcs-blog-featured', 1200, 720, true );
}
add_action( 'after_setup_theme', 'wcs_blog_register_image_sizes' );

/**
 * Register meta boxes for blog posts (featured, CTA, FAQ).
 */
function wcs_blog_register_meta_boxes() {
	add_meta_box(
		'wcs_blog_featured_meta',
		__( 'Blog: Öne Çıkan Yazı', 'woocommerce-store-child' ),
		'wcs_blog_render_featured_meta_box',
		'post',
		'side',
		'default'
	);

	add_meta_box(
		'wcs_blog_cta_meta',
		__( 'Blog: CTA Metinleri', 'woocommerce-store-child' ),
		'wcs_blog_render_cta_meta_box',
		'post',
		'normal',
		'default'
	);

	add_meta_box(
		'wcs_blog_faq_meta',
		__( 'Blog: SSS (FAQ)', 'woocommerce-store-child' ),
		'wcs_blog_render_faq_meta_box',
		'post',
		'normal',
		'default'
	);
}
add_action( 'add_meta_boxes', 'wcs_blog_register_meta_boxes' );

/**
 * Render featured post meta box.
 *
 * @param WP_Post $post Post object.
 */
function wcs_blog_render_featured_meta_box( $post ) {
	wp_nonce_field( 'wcs_blog_featured_meta', 'wcs_blog_featured_meta_nonce' );

	$is_featured = (bool) get_post_meta( $post->ID, '_wcs_featured_post', true );
	?>
	<p>
		<label>
			<input type="checkbox" name="wcs_featured_post" value="1" <?php checked( $is_featured ); ?> />
			<?php esc_html_e( 'Bu yazıyı blogta öne çıkan olarak göster', 'woocommerce-store-child' ); ?>
		</label>
	</p>
	<?php
}

/**
 * Render CTA meta box.
 *
 * @param WP_Post $post Post object.
 */
function wcs_blog_render_cta_meta_box( $post ) {
	wp_nonce_field( 'wcs_blog_cta_meta', 'wcs_blog_cta_meta_nonce' );

	$intro_title = get_post_meta( $post->ID, 'cta_intro_title', true );
	$intro_text  = get_post_meta( $post->ID, 'cta_intro_text', true );
	$end_title   = get_post_meta( $post->ID, 'cta_end_title', true );
	$end_text    = get_post_meta( $post->ID, 'cta_end_text', true );
	?>
	<p>
		<strong><?php esc_html_e( 'Giriş Sonrası CTA', 'woocommerce-store-child' ); ?></strong>
	</p>
	<p>
		<label for="cta_intro_title"><?php esc_html_e( 'Başlık', 'woocommerce-store-child' ); ?></label><br />
		<input type="text" id="cta_intro_title" name="cta_intro_title" class="widefat" value="<?php echo esc_attr( $intro_title ); ?>" />
	</p>
	<p>
		<label for="cta_intro_text"><?php esc_html_e( 'Metin', 'woocommerce-store-child' ); ?></label><br />
		<textarea id="cta_intro_text" name="cta_intro_text" rows="3" class="widefat"><?php echo esc_textarea( $intro_text ); ?></textarea>
	</p>

	<hr />

	<p>
		<strong><?php esc_html_e( 'Yazı Sonu CTA', 'woocommerce-store-child' ); ?></strong>
	</p>
	<p>
		<label for="cta_end_title"><?php esc_html_e( 'Başlık', 'woocommerce-store-child' ); ?></label><br />
		<input type="text" id="cta_end_title" name="cta_end_title" class="widefat" value="<?php echo esc_attr( $end_title ); ?>" />
	</p>
	<p>
		<label for="cta_end_text"><?php esc_html_e( 'Metin', 'woocommerce-store-child' ); ?></label><br />
		<textarea id="cta_end_text" name="cta_end_text" rows="3" class="widefat"><?php echo esc_textarea( $end_text ); ?></textarea>
	</p>
	<?php
}

/**
 * Render FAQ meta box.
 *
 * @param WP_Post $post Post object.
 */
function wcs_blog_render_faq_meta_box( $post ) {
	wp_nonce_field( 'wcs_blog_faq_meta', 'wcs_blog_faq_meta_nonce' );

	$faq_items = get_post_meta( $post->ID, '_wcs_blog_faq_items', true );

	if ( ! is_array( $faq_items ) ) {
		$faq_items = array();
	}

	$max_items = 5;
	?>
	<p>
		<?php esc_html_e( 'Bu yazı için en fazla 5 adet soru-cevap ekleyebilirsiniz. Boş alanlar frontend’de gösterilmez.', 'woocommerce-store-child' ); ?>
	</p>

	<table class="widefat striped">
		<thead>
			<tr>
				<th style="width:40%;"><?php esc_html_e( 'Soru', 'woocommerce-store-child' ); ?></th>
				<th><?php esc_html_e( 'Cevap', 'woocommerce-store-child' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php for ( $i = 0; $i < $max_items; $i++ ) : ?>
				<?php
				$question = isset( $faq_items[ $i ]['question'] ) ? $faq_items[ $i ]['question'] : '';
				$answer   = isset( $faq_items[ $i ]['answer'] ) ? $faq_items[ $i ]['answer'] : '';
				?>
				<tr>
					<td>
						<input
							type="text"
							name="wcs_faq_items[<?php echo esc_attr( (string) $i ); ?>][question]"
							class="widefat"
							value="<?php echo esc_attr( $question ); ?>"
						/>
					</td>
					<td>
						<textarea
							name="wcs_faq_items[<?php echo esc_attr( (string) $i ); ?>][answer]"
							rows="3"
							class="widefat"
						><?php echo esc_textarea( $answer ); ?></textarea>
					</td>
				</tr>
			<?php endfor; ?>
		</tbody>
	</table>
	<?php
}

/**
 * Save blog meta fields.
 *
 * @param int $post_id Post ID.
 */
function wcs_blog_save_meta_boxes( $post_id ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( isset( $_POST['post_type'] ) && 'post' !== $_POST['post_type'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		return;
	}

	// Featured.
	if ( isset( $_POST['wcs_blog_featured_meta_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wcs_blog_featured_meta_nonce'] ) ), 'wcs_blog_featured_meta' ) ) {
		$is_featured = isset( $_POST['wcs_featured_post'] ) && '1' === (string) $_POST['wcs_featured_post']; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		update_post_meta( $post_id, '_wcs_featured_post', $is_featured ? '1' : '' );
	}

	// CTA.
	if ( isset( $_POST['wcs_blog_cta_meta_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wcs_blog_cta_meta_nonce'] ) ), 'wcs_blog_cta_meta' ) ) {
		$intro_title = isset( $_POST['cta_intro_title'] ) ? sanitize_text_field( wp_unslash( $_POST['cta_intro_title'] ) ) : '';
		$intro_text  = isset( $_POST['cta_intro_text'] ) ? wp_kses_post( wp_unslash( $_POST['cta_intro_text'] ) ) : '';
		$end_title   = isset( $_POST['cta_end_title'] ) ? sanitize_text_field( wp_unslash( $_POST['cta_end_title'] ) ) : '';
		$end_text    = isset( $_POST['cta_end_text'] ) ? wp_kses_post( wp_unslash( $_POST['cta_end_text'] ) ) : '';

		update_post_meta( $post_id, 'cta_intro_title', $intro_title );
		update_post_meta( $post_id, 'cta_intro_text', $intro_text );
		update_post_meta( $post_id, 'cta_end_title', $end_title );
		update_post_meta( $post_id, 'cta_end_text', $end_text );
	}

	// FAQ.
	if ( isset( $_POST['wcs_blog_faq_meta_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wcs_blog_faq_meta_nonce'] ) ), 'wcs_blog_faq_meta' ) ) {
		$items_raw = isset( $_POST['wcs_faq_items'] ) ? (array) wp_unslash( $_POST['wcs_faq_items'] ) : array();

		$clean_items = array();

		foreach ( $items_raw as $item ) {
			$question = isset( $item['question'] ) ? sanitize_text_field( $item['question'] ) : '';
			$answer   = isset( $item['answer'] ) ? wp_kses_post( $item['answer'] ) : '';

			if ( '' === $question && '' === $answer ) {
				continue;
			}

			$clean_items[] = array(
				'question' => $question,
				'answer'   => $answer,
			);
		}

		update_post_meta( $post_id, '_wcs_blog_faq_items', $clean_items );
	}
}
add_action( 'save_post', 'wcs_blog_save_meta_boxes' );

