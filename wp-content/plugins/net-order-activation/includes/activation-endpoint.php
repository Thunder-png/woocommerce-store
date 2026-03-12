<?php
/**
 * Activation endpoint and shortcode.
 *
 * @package Net_Order_Activation
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register shortcode for activation page.
 */
function noa_register_shortcodes() {
	add_shortcode( 'net_order_activation', 'noa_render_activation_page' );
}
add_action( 'init', 'noa_register_shortcodes' );

/**
 * Render activation page content.
 *
 * This outputs the multi-step UI containers that are managed by JS.
 *
 * @return string
 */
function noa_render_activation_page() {
	ob_start();
	?>
	<div class="noa-activation-wrapper">
		<section class="noa-hero">
			<h1><?php esc_html_e( 'Register Your Order', 'net-order-activation' ); ?></h1>
			<p><?php esc_html_e( 'Save your measurements and easily reorder in the future.', 'net-order-activation' ); ?></p>
		</section>

		<section class="noa-step noa-step-activation is-active" id="noa-step-activation">
			<h2><?php esc_html_e( 'Siparişinizi Aktive Edin', 'net-order-activation' ); ?></h2>
			<form id="noa-activation-form">
				<div class="noa-field">
					<label for="noa_activation_code"><?php esc_html_e( 'Aktivasyon Kodu', 'net-order-activation' ); ?></label>
					<input type="text" id="noa_activation_code" name="activation_code" required />
				</div>
				<div class="noa-field">
					<label for="noa_phone_number"><?php esc_html_e( 'Telefon Numarası', 'net-order-activation' ); ?></label>
					<input type="tel" id="noa_phone_number" name="phone_number" required />
				</div>
				<div class="noa-field noa-recaptcha-placeholder">
					<!-- Placeholder for reCAPTCHA widget integration. -->
				</div>
				<button type="submit" class="noa-button"><?php esc_html_e( 'Siparişi Doğrula', 'net-order-activation' ); ?></button>
				<div class="noa-message" id="noa-activation-message"></div>
			</form>
		</section>

		<section class="noa-step noa-step-order" id="noa-step-order">
			<h2><?php esc_html_e( 'Siparişiniz Bulundu', 'net-order-activation' ); ?></h2>
			<div id="noa-order-summary">
				<!-- Order summary will be populated via AJAX. -->
			</div>
			<button type="button" class="noa-button" id="noa-go-to-account">
				<?php esc_html_e( 'Hesabınızı Oluşturun', 'net-order-activation' ); ?>
			</button>
		</section>

		<section class="noa-step noa-step-account" id="noa-step-account">
			<h2><?php esc_html_e( 'Hesap Oluşturun', 'net-order-activation' ); ?></h2>
			<form id="noa-account-form">
				<div class="noa-field">
					<label for="noa_name"><?php esc_html_e( 'Ad Soyad', 'net-order-activation' ); ?></label>
					<input type="text" id="noa_name" name="name" required />
				</div>
				<div class="noa-field">
					<label for="noa_email"><?php esc_html_e( 'E-posta Adresi', 'net-order-activation' ); ?></label>
					<input type="email" id="noa_email" name="email" required />
				</div>
				<div class="noa-field">
					<label for="noa_password"><?php esc_html_e( 'Şifre', 'net-order-activation' ); ?></label>
					<input type="password" id="noa_password" name="password" required />
				</div>
				<div class="noa-field noa-privacy">
					<label>
						<input type="checkbox" name="privacy" value="1" required />
						<?php esc_html_e( 'Gizlilik politikasını okudum ve kabul ediyorum.', 'net-order-activation' ); ?>
					</label>
				</div>
				<input type="hidden" id="noa_order_token" name="order_token" value="" />
				<button type="submit" class="noa-button"><?php esc_html_e( 'Hesap Oluştur ve Siparişi Bağla', 'net-order-activation' ); ?></button>
				<div class="noa-message" id="noa-account-message"></div>
			</form>
		</section>

		<section class="noa-step noa-step-success" id="noa-step-success">
			<h2><?php esc_html_e( 'Siparişiniz Kaydedildi', 'net-order-activation' ); ?></h2>
			<p><?php esc_html_e( 'Hesabınız oluşturuldu ve siparişiniz hesabınıza bağlandı. Artık giriş yaparak ölçülerinizi görüntüleyebilir ve kolayca yeniden sipariş verebilirsiniz.', 'net-order-activation' ); ?></p>
		</section>
	</div>
	<?php

	return ob_get_clean();
}

