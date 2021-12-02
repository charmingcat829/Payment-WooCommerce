<?php
/**
 * Add Token Details.
 *
 * @package TradeSafe Payment Gateway
 */

defined( 'ABSPATH' ) || exit;

?>
<fieldset>
	<legend><?php esc_html_e( 'Organization Details (Optional)', 'woocommerce-tradesafe-gateway' ); ?></legend>

	<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
		<label for="tradesafe_token_organization_name"><?php esc_html_e( 'Name', 'woocommerce-tradesafe-gateway' ); ?></label>
		<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="tradesafe_token_organization_name" id="tradesafe_token_organization_name" value="<?php echo esc_attr( $token_data['organization']['name'] ?? null ); ?>"/>
	</p>

	<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-first">
		<label for="tradesafe_token_organization_type"><?php esc_html_e( 'Type', 'woocommerce-tradesafe-gateway' ); ?></label>
		<select name="tradesafe_token_organization_type">
			<option selected="selected" disabled="disabled" hidden="hidden"></option>
			<?php
			foreach ( $organization_types as $organization_name => $organization_description ) {
				echo '<option value="' . esc_attr( $organization_name ) . '" ' . ( ( $token_data['organization']['type'] ?? null ) === $organization_name ? 'selected' : '' ) . '>' . esc_attr( $organization_description ) . '</option>';
			}
			?>
		</select>
	</p>

	<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-last">
		<label for="tradesafe_token_organization_trading_name"><?php esc_html_e( 'Trading Name', 'woocommerce-tradesafe-gateway' ); ?></label>
		<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="tradesafe_token_organization_trading_name" id="tradesafe_token_organization_trading_name" value="<?php echo esc_attr( $token_data['organization']['tradeName'] ?? null ); ?>"/>
	</p>
	<div class="clear"></div>

	<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-first">
		<label for="tradesafe_token_organization_registration_number"><?php esc_html_e( 'Registration Number', 'woocommerce-tradesafe-gateway' ); ?></label>
		<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="tradesafe_token_organization_registration_number" id="tradesafe_token_organization_registration_number" value="<?php echo esc_attr( $token_data['organization']['registration'] ?? null ); ?>"/>
	</p>

	<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-last">
		<label for="tradesafe_token_organization_tax_number"><?php esc_html_e( 'VAT Number', 'woocommerce-tradesafe-gateway' ); ?></label>
		<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="tradesafe_token_organization_tax_number" id="tradesafe_token_organization_tax_number" value="<?php echo esc_attr( $token_data['organization']['taxNumber'] ?? null ); ?>"/>
	</p>
</fieldset>
