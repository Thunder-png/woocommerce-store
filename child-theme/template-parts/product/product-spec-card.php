<?php
/**
 * Karaca File — Ürün Spec Kartı (göz aralığı, ip kalınlığı, regi).
 * Single product gallery altında gösterilir; veriler ürün/varyasyon verisine göre doldurulur.
 *
 * @package WooCommerce_Store_Child
 */

defined( 'ABSPATH' ) || exit;

$args = wp_parse_args(
    $args ?? array(),
    array(
        'spec_goz'  => '',
        'spec_ip'   => '',
        'spec_renk' => '',
        'is_var'    => false,
    )
);

$spec_goz  = $args['spec_goz'];
$spec_ip   = $args['spec_ip'];
$spec_renk = $args['spec_renk'];
$is_var    = (bool) $args['is_var'];

$goz_display  = $spec_goz ? esc_html( $spec_goz ) : '—';
$ip_display    = $spec_ip ? esc_html( $spec_ip ) : '—';
$renk_display  = $spec_renk ? esc_html( $spec_renk ) : '';
?>

<div class="wcs-spec-card kfc-card" data-spec-goz="<?php echo esc_attr( $spec_goz ); ?>" data-spec-ip="<?php echo esc_attr( $spec_ip ); ?>" data-spec-renk="<?php echo esc_attr( $spec_renk ); ?>" data-is-variable="<?php echo $is_var ? '1' : '0'; ?>">

	<div class="kfc-panel-top">
		<canvas class="kfc-net" aria-hidden="true"></canvas>

		<div class="kfc-logo-top">
			<svg viewBox="0 0 140 54" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
				<text x="4" y="22" font-family="'DM Sans',sans-serif" font-weight="600" font-size="16" fill="white" font-style="italic">By</text>
				<path d="M36 28 Q60 4 82 18" stroke="#d42b2b" stroke-width="3.5" fill="none" stroke-linecap="round"/>
				<text x="36" y="42" font-family="'Bebas Neue',sans-serif" font-size="30" fill="white" letter-spacing="2">KARACA</text>
			</svg>
			<div class="kfc-tagline"><?php esc_html_e( 'İŞ GÜVENLİĞİ', 'woocommerce-store-child' ); ?><br><?php esc_html_e( 'AĞ SİSTEMLERİ', 'woocommerce-store-child' ); ?></div>
		</div>

		<div class="kfc-color-dot-wrap"<?php echo $renk_display ? '' : ' style="display:none;"'; ?>>
			<div class="kfc-color-dot" style="background: var(--wcs-spec-color, #f5f0e8);"></div>
			<span class="kfc-color-label"><?php echo $renk_display; ?></span>
		</div>

		<div class="kfc-measure-wrap">
			<div class="kfc-diamond">
				<span class="kfc-rope-text tl">KARACAFILE</span>
				<span class="kfc-rope-text tr">KARACAFILE</span>
				<span class="kfc-rope-text bl">KARACAFILE</span>
				<span class="kfc-rope-text br">KARACAFILE</span>
			</div>
			<div class="kfc-arrows" aria-hidden="true">
				<svg viewBox="0 0 200 200">
					<defs>
						<marker id="kfc-ah" markerWidth="6" markerHeight="6" refX="3" refY="3" orient="auto">
							<path d="M0,0 L6,3 L0,6 Z" fill="white"/>
						</marker>
						<marker id="kfc-ah2" markerWidth="6" markerHeight="6" refX="3" refY="3" orient="auto-start-reverse">
							<path d="M0,0 L6,3 L0,6 Z" fill="white"/>
						</marker>
					</defs>
					<line x1="70" y1="130" x2="130" y2="70" stroke="white" stroke-width="1.8" marker-start="url(#kfc-ah2)" marker-end="url(#kfc-ah)"/>
					<line x1="70" y1="70" x2="130" y2="130" stroke="white" stroke-width="1.8" marker-start="url(#kfc-ah2)" marker-end="url(#kfc-ah)"/>
				</svg>
			</div>
			<div class="kfc-center-label"><span class="kfc-center-value"><?php echo $goz_display; ?></span></div>
			<div class="kfc-badge-ip kfc-badge-ip--left"><span class="kfc-badge-ip-value"><?php echo $ip_display; ?></span></div>
			<div class="kfc-badge-ip kfc-badge-ip--right"><span class="kfc-badge-ip-value"><?php echo $ip_display; ?></span></div>
		</div>
	</div>

	<div class="kfc-panel-bottom">
		<div class="kfc-logo-bottom">
			<svg viewBox="0 0 140 54" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
				<text x="4" y="22" font-family="'DM Sans',sans-serif" font-weight="600" font-size="16" fill="#1a1a1a" font-style="italic">By</text>
				<path d="M36 28 Q60 4 82 18" stroke="#d42b2b" stroke-width="3.5" fill="none" stroke-linecap="round"/>
				<text x="36" y="42" font-family="'Bebas Neue',sans-serif" font-size="30" fill="#1a1a1a" letter-spacing="2">KARACA</text>
			</svg>
			<div class="kfc-tagline kfc-tagline--dark"><?php esc_html_e( 'İŞ GÜVENLİĞİ', 'woocommerce-store-child' ); ?><br><?php esc_html_e( 'AĞ SİSTEMLERİ', 'woocommerce-store-child' ); ?></div>
		</div>
		<div class="kfc-specs">
			<div class="kfc-spec-line kfc-spec-line--goz"><?php echo $goz_display; ?> <?php esc_html_e( 'GÖZ ARALIĞI', 'woocommerce-store-child' ); ?></div>
			<div class="kfc-spec-line kfc-spec-line--ip"><?php echo $ip_display; ?> <?php esc_html_e( 'İPLİK KALINLIĞI', 'woocommerce-store-child' ); ?></div>
		</div>
	</div>

	<div class="kfc-footer-bar">
		<div class="kfc-footer-dot"></div>
		<span><?php esc_html_e( 'GÜVENLİK FİLESİ ÇEŞİTLERİ', 'woocommerce-store-child' ); ?></span>
		<div class="kfc-footer-dot"></div>
	</div>

</div>
