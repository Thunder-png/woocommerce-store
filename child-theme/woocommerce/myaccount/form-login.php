<?php
/**
 * My Account login and registration form
 *
 * @package WooCommerceStoreChild
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_customer_login_form' );

if ( 'yes' === get_option( 'woocommerce_enable_myaccount_registration' ) ) :
	?>
	<div class="u-columns col2-set wcs-account-login" id="customer_login">
		<div class="u-column1 col-1 wcs-account-login__col wcs-account-login__col--login">
	<?php else : ?>
	<div class="wcs-account-login" id="customer_login">
		<div class="wcs-account-login__col wcs-account-login__col--login">
	<?php endif; ?>

			<h2 class="wcs-account-login__title"><?php esc_html_e( 'Giriş Yap', 'woocommerce-store-child' ); ?></h2>

			<form class="woocommerce-form woocommerce-form-login login" method="post">

				<?php do_action( 'woocommerce_login_form_start' ); ?>

				<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
					<label for="username"><?php esc_html_e( 'E-posta adresi', 'woocommerce-store-child' ); ?>&nbsp;<span class="required">*</span></label>
					<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="username" id="username" autocomplete="username" value="<?php echo ! empty( $_POST['username'] ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing ?>" />
				</p>
				<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
					<label for="password"><?php esc_html_e( 'Şifre', 'woocommerce-store-child' ); ?>&nbsp;<span class="required">*</span></label>
					<input class="woocommerce-Input woocommerce-Input--text input-text" type="password" name="password" id="password" autocomplete="current-password" />
				</p>

				<?php do_action( 'woocommerce_login_form' ); ?>

				<p class="form-row">
					<label class="woocommerce-form__label woocommerce-form__label-for-checkbox woocommerce-form-login__rememberme">
						<input class="woocommerce-form__input woocommerce-form__input-checkbox" name="rememberme" type="checkbox" id="rememberme" value="forever" />
						<span><?php esc_html_e( 'Beni hatırla', 'woocommerce-store-child' ); ?></span>
					</label>
					<?php wp_nonce_field( 'woocommerce-login', 'woocommerce-login-nonce' ); ?>
					<button type="submit" class="woocommerce-button button woocommerce-form-login__submit" name="login" value="<?php esc_attr_e( 'Giriş Yap', 'woocommerce-store-child' ); ?>">
						<?php esc_html_e( 'Giriş Yap', 'woocommerce-store-child' ); ?>
					</button>
				</p>

				<p class="woocommerce-LostPassword lost_password">
					<a href="<?php echo esc_url( wp_lostpassword_url() ); ?>">
						<?php esc_html_e( 'Şifrenizi mi unuttunuz?', 'woocommerce-store-child' ); ?>
					</a>
				</p>

				<?php do_action( 'woocommerce_login_form_end' ); ?>

			</form>
		</div>

	<?php if ( 'yes' === get_option( 'woocommerce_enable_myaccount_registration' ) ) : ?>

		<div class="u-column2 col-2 wcs-account-login__col wcs-account-login__col--register">

			<h2 class="wcs-account-login__title"><?php esc_html_e( 'Kayıt Ol', 'woocommerce-store-child' ); ?></h2>

			<form method="post" class="woocommerce-form woocommerce-form-register register">

				<?php do_action( 'woocommerce_register_form_start' ); ?>

				<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
					<label for="reg_email"><?php esc_html_e( 'E-posta adresi', 'woocommerce-store-child' ); ?>&nbsp;<span class="required">*</span></label>
					<input type="email" class="woocommerce-Input woocommerce-Input--text input-text" name="email" id="reg_email" autocomplete="email" value="<?php echo ! empty( $_POST['email'] ) ? esc_attr( wp_unslash( $_POST['email'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing ?>" />
				</p>

				<?php if ( 'no' === get_option( 'woocommerce_registration_generate_password' ) ) : ?>
					<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
						<label for="reg_password"><?php esc_html_e( 'Şifre', 'woocommerce-store-child' ); ?>&nbsp;<span class="required">*</span></label>
						<input type="password" class="woocommerce-Input woocommerce-Input--text input-text" name="password" id="reg_password" autocomplete="new-password" />
					</p>
				<?php else : ?>
					<p><?php esc_html_e( 'Hesabınız için güvenli bir şifre size e-posta ile gönderilecektir.', 'woocommerce-store-child' ); ?></p>
				<?php endif; ?>

				<?php do_action( 'woocommerce_register_form' ); ?>

				<p class="woocommerce-form-row form-row">
					<?php wp_nonce_field( 'woocommerce-register', 'woocommerce-register-nonce' ); ?>
					<button type="submit" class="woocommerce-Button woocommerce-button button woocommerce-form-register__submit" name="register" value="<?php esc_attr_e( 'Kayıt Ol', 'woocommerce-store-child' ); ?>">
						<?php esc_html_e( 'Kayıt Ol', 'woocommerce-store-child' ); ?>
					</button>
				</p>

				<?php do_action( 'woocommerce_register_form_end' ); ?>

			</form>

		</div>
	<?php endif; ?>
	</div>

<?php do_action( 'woocommerce_after_customer_login_form' ); ?>

