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
			<h2><?php esc_html_e( 'Activate Your Order', 'net-order-activation' ); ?></h2>
			<form id="noa-activation-form">
				<div class="noa-field">
					<label for="noa_activation_code"><?php esc_html_e( 'Activation Code', 'net-order-activation' ); ?></label>
					<input type="text" id="noa_activation_code" name="activation_code" required />
				</div>
				<div class="noa-field">
					<label for="noa_phone_number"><?php esc_html_e( 'Phone Number', 'net-order-activation' ); ?></label>
					<input type="tel" id="noa_phone_number" name="phone_number" required />
				</div>
				<input type="hidden" name="noa_nonce" value="<?php echo esc_attr( wp_create_nonce( 'noa_activation_nonce' ) ); ?>" />
				<div class="noa-field noa-recaptcha-placeholder">
					<!-- Placeholder for reCAPTCHA widget integration. -->
				</div>
				<button type="submit" class="noa-button"><?php esc_html_e( 'Verify Order', 'net-order-activation' ); ?></button>
				<div class="noa-message" id="noa-activation-message"></div>
			</form>
		</section>

		<section class="noa-step noa-step-order" id="noa-step-order">
			<h2><?php esc_html_e( 'Order Found', 'net-order-activation' ); ?></h2>
			<div id="noa-order-summary">
				<!-- Order summary will be populated via AJAX. -->
			</div>
			<button type="button" class="noa-button" id="noa-go-to-account">
				<?php esc_html_e( 'Create Your Account', 'net-order-activation' ); ?>
			</button>
		</section>

		<section class="noa-step noa-step-account" id="noa-step-account">
			<h2><?php esc_html_e( 'Create Your Account', 'net-order-activation' ); ?></h2>
			<form id="noa-account-form">
				<div class="noa-field">
					<label for="noa_name"><?php esc_html_e( 'Full Name', 'net-order-activation' ); ?></label>
					<input type="text" id="noa_name" name="name" required />
				</div>
				<div class="noa-field">
					<label for="noa_email"><?php esc_html_e( 'Email Address', 'net-order-activation' ); ?></label>
					<input type="email" id="noa_email" name="email" required />
				</div>
				<div class="noa-field">
					<label for="noa_password"><?php esc_html_e( 'Password', 'net-order-activation' ); ?></label>
					<input type="password" id="noa_password" name="password" required />
				</div>
				<div class="noa-field noa-privacy">
					<label>
						<input type="checkbox" name="privacy" value="1" required />
						<?php esc_html_e( 'I agree to the privacy policy.', 'net-order-activation' ); ?>
					</label>
				</div>
				<input type="hidden" name="noa_nonce" value="<?php echo esc_attr( wp_create_nonce( 'noa_activation_nonce' ) ); ?>" />
				<input type="hidden" id="noa_order_token" name="order_token" value="" />
				<button type="submit" class="noa-button"><?php esc_html_e( 'Create Account & Link Order', 'net-order-activation' ); ?></button>
				<div class="noa-message" id="noa-account-message"></div>
			</form>
		</section>

		<section class="noa-step noa-step-success" id="noa-step-success">
			<h2><?php esc_html_e( 'Your Order Is Registered', 'net-order-activation' ); ?></h2>
			<p><?php esc_html_e( 'Your account has been created and your order has been linked. You can now log in to manage your measurements and reorder easily.', 'net-order-activation' ); ?></p>
		</section>
	</div>
	<?php

	return ob_get_clean();
}

