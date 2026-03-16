<?php
/**
 * Settings page view.
 *
 * @package DEMW
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap demw-admin">
	<h1><?php echo esc_html__( 'DHL eCommerce / MNG WooCommerce', 'dhl-ecommerce-mng-woocommerce' ); ?></h1>
	<p><?php echo esc_html__( 'Sandbox-first operational integration for WooCommerce admin shipment actions.', 'dhl-ecommerce-mng-woocommerce' ); ?></p>

	<?php if ( is_array( $notices ) && ! empty( $notices['message'] ) ) : ?>
		<div class="notice notice-<?php echo esc_attr( 'error' === $notices['type'] ? 'error' : 'success' ); ?> is-dismissible">
			<p><?php echo esc_html( $notices['message'] ); ?></p>
		</div>
	<?php endif; ?>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="demw-settings-form">
		<input type="hidden" name="action" value="demw_save_settings" />
		<?php wp_nonce_field( 'demw_save_settings' ); ?>

		<div class="demw-card">
			<h2><?php echo esc_html__( 'General', 'dhl-ecommerce-mng-woocommerce' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="demw_environment"><?php echo esc_html__( 'Environment', 'dhl-ecommerce-mng-woocommerce' ); ?></label></th>
					<td>
						<select id="demw_environment" name="demw_settings[environment]">
							<option value="development" <?php selected( 'development', ( 'sandbox' === $settings['environment'] ? 'development' : $settings['environment'] ) ); ?>><?php echo esc_html__( 'Development', 'dhl-ecommerce-mng-woocommerce' ); ?></option>
							<option value="production" <?php selected( 'production', $settings['environment'] ); ?>><?php echo esc_html__( 'Production', 'dhl-ecommerce-mng-woocommerce' ); ?></option>
						</select>
						<p class="description"><?php echo esc_html__( 'Active environment controls which base URL is used for API requests.', 'dhl-ecommerce-mng-woocommerce' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="demw_development_base_url"><?php echo esc_html__( 'Development Base URL', 'dhl-ecommerce-mng-woocommerce' ); ?></label></th>
					<td>
						<input type="url" class="regular-text" id="demw_development_base_url" name="demw_settings[development_base_url]" value="<?php echo esc_attr( isset( $settings['development_base_url'] ) ? $settings['development_base_url'] : ( $settings['sandbox_base_url'] ?? '' ) ); ?>" />
						<p class="description"><?php echo esc_html__( 'Example: https://testapi.mngkargo.com.tr/mngapi/api (host-only form is also accepted).', 'dhl-ecommerce-mng-woocommerce' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="demw_production_base_url"><?php echo esc_html__( 'Production Base URL', 'dhl-ecommerce-mng-woocommerce' ); ?></label></th>
					<td>
						<input type="url" class="regular-text" id="demw_production_base_url" name="demw_settings[production_base_url]" value="<?php echo esc_attr( $settings['production_base_url'] ); ?>" />
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="demw_timeout"><?php echo esc_html__( 'Connection Timeout (sec)', 'dhl-ecommerce-mng-woocommerce' ); ?></label></th>
					<td>
						<input type="number" min="5" max="180" id="demw_timeout" name="demw_settings[timeout]" value="<?php echo esc_attr( (string) $settings['timeout'] ); ?>" />
						<p class="description"><?php echo esc_html__( 'For slow carrier responses use 60-90 seconds. The client retries once on timeout for GET and calculate endpoints.', 'dhl-ecommerce-mng-woocommerce' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="demw_api_version"><?php echo esc_html__( 'API Version Header (x-api-version)', 'dhl-ecommerce-mng-woocommerce' ); ?></label></th>
					<td>
						<input type="text" class="regular-text" id="demw_api_version" name="demw_settings[api_version]" value="<?php echo esc_attr( $settings['api_version'] ); ?>" />
						<p class="description"><?php echo esc_html__( 'Optional. If your MNG account requires x-api-version, enter it here.', 'dhl-ecommerce-mng-woocommerce' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="demw_shipment_command_api"><?php echo esc_html__( 'Shipment Command API', 'dhl-ecommerce-mng-woocommerce' ); ?></label></th>
					<td>
						<select id="demw_shipment_command_api" name="demw_settings[shipment_command_api]">
							<option value="plus_command" <?php selected( 'plus_command', $settings['shipment_command_api'] ); ?>><?php echo esc_html__( '2-Step Flow (createRecipient + standardcmd/createOrder, then createbarcode)', 'dhl-ecommerce-mng-woocommerce' ); ?></option>
							<option value="barcode_command" <?php selected( 'barcode_command', $settings['shipment_command_api'] ); ?>><?php echo esc_html__( 'Barcode Command (createbarcode)', 'dhl-ecommerce-mng-woocommerce' ); ?></option>
						</select>
						<p class="description"><?php echo esc_html__( 'Recommended flow: first click runs createRecipient + createOrder (branch detection prep), next click runs createbarcode. If barcode returns 20001, wait briefly and retry.', 'dhl-ecommerce-mng-woocommerce' ); ?></p>
					</td>
				</tr>
			</table>
		</div>

		<div class="demw-card">
			<h2><?php echo esc_html__( 'Authentication', 'dhl-ecommerce-mng-woocommerce' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="demw_auth_type"><?php echo esc_html__( 'Auth Type', 'dhl-ecommerce-mng-woocommerce' ); ?></label></th>
					<td>
						<select id="demw_auth_type" name="demw_settings[auth_type]" data-demw-auth-type="1">
							<option value="api_key_secret" <?php selected( 'api_key_secret', $settings['auth_type'] ); ?>><?php echo esc_html__( 'API Key / Secret', 'dhl-ecommerce-mng-woocommerce' ); ?></option>
							<option value="username_password" <?php selected( 'username_password', $settings['auth_type'] ); ?>><?php echo esc_html__( 'Username / Password', 'dhl-ecommerce-mng-woocommerce' ); ?></option>
							<option value="bearer_token" <?php selected( 'bearer_token', $settings['auth_type'] ); ?>><?php echo esc_html__( 'Bearer Token', 'dhl-ecommerce-mng-woocommerce' ); ?></option>
							<option value="custom_header" <?php selected( 'custom_header', $settings['auth_type'] ); ?>><?php echo esc_html__( 'Custom Header Token', 'dhl-ecommerce-mng-woocommerce' ); ?></option>
						</select>
						<p class="description"><?php echo esc_html__( 'Only fields relevant to the selected auth mode will be used by the API client.', 'dhl-ecommerce-mng-woocommerce' ); ?></p>
					</td>
				</tr>
				<tr data-demw-auth="api_key_secret username_password">
					<th scope="row"><label for="demw_api_key"><?php echo esc_html__( 'API Key', 'dhl-ecommerce-mng-woocommerce' ); ?></label></th>
					<td>
						<input type="text" class="regular-text" id="demw_api_key" name="demw_settings[api_key]" value="<?php echo esc_attr( $settings['api_key'] ); ?>" autocomplete="off" />
						<p class="description"><?php echo esc_html__( 'Mapped to X-IBM-Client-Id header.', 'dhl-ecommerce-mng-woocommerce' ); ?></p>
					</td>
				</tr>
				<tr data-demw-auth="api_key_secret username_password">
					<th scope="row"><label for="demw_api_secret"><?php echo esc_html__( 'API Secret', 'dhl-ecommerce-mng-woocommerce' ); ?></label></th>
					<td>
						<input type="password" class="regular-text" id="demw_api_secret" name="demw_settings[api_secret]" value="<?php echo esc_attr( $settings['api_secret'] ); ?>" autocomplete="new-password" />
						<p class="description"><?php echo esc_html__( 'Mapped to X-IBM-Client-Secret header.', 'dhl-ecommerce-mng-woocommerce' ); ?></p>
					</td>
				</tr>
				<tr data-demw-auth="username_password">
					<th scope="row"><label for="demw_username"><?php echo esc_html__( 'Username / Customer Number', 'dhl-ecommerce-mng-woocommerce' ); ?></label></th>
					<td>
						<input type="text" class="regular-text" id="demw_username" name="demw_settings[username]" value="<?php echo esc_attr( $settings['username'] ); ?>" autocomplete="off" />
						<p class="description"><?php echo esc_html__( 'For MNG Identity API this maps to customerNumber in POST /token request body and must be numeric (Int64).', 'dhl-ecommerce-mng-woocommerce' ); ?></p>
					</td>
				</tr>
				<tr data-demw-auth="username_password">
					<th scope="row"><label for="demw_password"><?php echo esc_html__( 'Password', 'dhl-ecommerce-mng-woocommerce' ); ?></label></th>
					<td>
						<input type="password" class="regular-text" id="demw_password" name="demw_settings[password]" value="<?php echo esc_attr( $settings['password'] ); ?>" autocomplete="new-password" />
						<p class="description"><?php echo esc_html__( 'Used in POST /token request body as password.', 'dhl-ecommerce-mng-woocommerce' ); ?></p>
					</td>
				</tr>
				<tr data-demw-auth="bearer_token">
					<th scope="row"><label for="demw_bearer_token"><?php echo esc_html__( 'Bearer Token', 'dhl-ecommerce-mng-woocommerce' ); ?></label></th>
					<td><input type="password" class="regular-text" id="demw_bearer_token" name="demw_settings[bearer_token]" value="<?php echo esc_attr( $settings['bearer_token'] ); ?>" autocomplete="new-password" /></td>
				</tr>
				<tr data-demw-auth="custom_header">
					<th scope="row"><label for="demw_custom_header_name"><?php echo esc_html__( 'Custom Header Name', 'dhl-ecommerce-mng-woocommerce' ); ?></label></th>
					<td><input type="text" class="regular-text" id="demw_custom_header_name" name="demw_settings[custom_header_name]" value="<?php echo esc_attr( $settings['custom_header_name'] ); ?>" /></td>
				</tr>
				<tr data-demw-auth="custom_header">
					<th scope="row"><label for="demw_custom_header_value"><?php echo esc_html__( 'Custom Header Value', 'dhl-ecommerce-mng-woocommerce' ); ?></label></th>
					<td><input type="password" class="regular-text" id="demw_custom_header_value" name="demw_settings[custom_header_value]" value="<?php echo esc_attr( $settings['custom_header_value'] ); ?>" autocomplete="new-password" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="demw_customer_code"><?php echo esc_html__( 'Customer Code (Optional)', 'dhl-ecommerce-mng-woocommerce' ); ?></label></th>
					<td><input type="text" class="regular-text" id="demw_customer_code" name="demw_settings[customer_code]" value="<?php echo esc_attr( $settings['customer_code'] ); ?>" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="demw_branch_code"><?php echo esc_html__( 'Branch Code (Optional)', 'dhl-ecommerce-mng-woocommerce' ); ?></label></th>
					<td><input type="text" class="regular-text" id="demw_branch_code" name="demw_settings[branch_code]" value="<?php echo esc_attr( $settings['branch_code'] ); ?>" /></td>
				</tr>
			</table>
		</div>

		<div class="demw-card">
			<h2><?php echo esc_html__( 'Debug', 'dhl-ecommerce-mng-woocommerce' ); ?></h2>
			<fieldset>
				<label><input type="checkbox" name="demw_settings[debug_enabled]" value="1" <?php checked( 1, (int) $settings['debug_enabled'] ); ?> /> <?php echo esc_html__( 'Enable debug logging', 'dhl-ecommerce-mng-woocommerce' ); ?></label><br />
				<label><input type="checkbox" name="demw_settings[log_request_bodies]" value="1" <?php checked( 1, (int) $settings['log_request_bodies'] ); ?> /> <?php echo esc_html__( 'Log request bodies', 'dhl-ecommerce-mng-woocommerce' ); ?></label><br />
				<label><input type="checkbox" name="demw_settings[log_response_bodies]" value="1" <?php checked( 1, (int) $settings['log_response_bodies'] ); ?> /> <?php echo esc_html__( 'Log response bodies', 'dhl-ecommerce-mng-woocommerce' ); ?></label>
				<p class="description"><?php echo esc_html__( 'Do not enable body logging in production unless required for troubleshooting.', 'dhl-ecommerce-mng-woocommerce' ); ?></p>
			</fieldset>
		</div>

		<p>
			<button type="submit" class="button button-primary"><?php echo esc_html__( 'Save Settings', 'dhl-ecommerce-mng-woocommerce' ); ?></button>
		</p>
	</form>

	<div class="demw-card">
		<h2><?php echo esc_html__( 'Actions', 'dhl-ecommerce-mng-woocommerce' ); ?></h2>
		<p><?php echo esc_html__( 'Use this to test whether credentials and environment configuration can reach the API.', 'dhl-ecommerce-mng-woocommerce' ); ?></p>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="demw_test_connection" />
			<?php wp_nonce_field( 'demw_test_connection' ); ?>
			<button type="submit" class="button"><?php echo esc_html__( 'Test Connection', 'dhl-ecommerce-mng-woocommerce' ); ?></button>
		</form>
	</div>
</div>
