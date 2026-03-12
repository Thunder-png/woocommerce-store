<?php
/**
 * Lost password form
 *
 * @package WooCommerceStoreChild
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_lost_password_form' ); ?>

<form method="post" class="woocommerce-ResetPassword lost_reset_password wcs-account-lost-password">

	<h2 class="wcs-account-lost-password__title"><?php esc_html_e( 'Şifrenizi mi unuttunuz?', 'woocommerce-store-child' ); ?></h2>
	<p class="wcs-account-lost-password__intro">
		<?php esc_html_e( 'Lütfen hesabınızla ilişkili e-posta adresini girin. Şifrenizi sıfırlamak için size bir bağlantı göndereceğiz.', 'woocommerce-store-child' ); ?>
	</p>

	<p class="woocommerce-form-row woocommerce-form-row--first form-row form-row-first">
		<label for="user_login"><?php esc_html_e( 'E-posta adresi', 'woocommerce-store-child' ); ?>&nbsp;<span class="required">*</span></label>
		<input class="woocommerce-Input woocommerce-Input--text input-text" type="text" name="user_login" id="user_login" autocomplete="username" />
	</p>
	<div class="clear"></div>

	<?php do_action( 'woocommerce_lostpassword_form' ); ?>

	<p class="woocommerce-form-row form-row">
		<input type="hidden" name="wc_reset_password" value="true" />
		<?php wp_nonce_field( 'lost_password', 'woocommerce-lost-password-nonce' ); ?>
		<button type="submit" class="woocommerce-Button button" value="<?php esc_attr_e( 'Şifre sıfırlama bağlantısı gönder', 'woocommerce-store-child' ); ?>">
			<?php esc_html_e( 'Şifre sıfırlama bağlantısı gönder', 'woocommerce-store-child' ); ?>
		</button>
	</p>

	<p class="wcs-account-lost-password__back">
		<a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>">
			<?php esc_html_e( 'Giriş sayfasına dön', 'woocommerce-store-child' ); ?>
		</a>
	</p>

</form>

<?php do_action( 'woocommerce_after_lost_password_form' ); ?>

