<?php
/**
 * Add Token Details.
 *
 * @package TradeSafe Payment Gateway
 */

defined( 'ABSPATH' ) || exit;

?>
<h3>Why do I need to provide my ID and mobile number?</h3>
<p>First off, your details are safe and secure. Your information is encrypted with the highest industry-specific
	standards which can be found in most banks.</p>
<p>Our payment service provider is TradeSafe Escrow. When the buyer deposits the funds, they will make payment to
	TradeSafe who will hold the funds in the middle (in a trust account). Once the seller has delivered what was
	ordered, then TradeSafe releases the funds to the selling party.</p>
<p>TradeSafe is required by the South African Reserve Bank to know the identities of the parties and that is why these
	details are required.</p>

<fieldset>
	<legend><?php esc_html_e( 'Personal Details', 'woocommerce-tradesafe-gateway' ); ?></legend>

	<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
		<label for="tradesafe_token_mobile"><?php esc_html_e( 'Mobile number', 'woocommerce-tradesafe-gateway' ); ?>
			&nbsp;<span
					class="required">*</span></label>
		<input type="mobile" class="woocommerce-Input woocommerce-Input--mobile input-text" name="tradesafe_token_mobile" id="tradesafe_token_mobile" autocomplete="mobile" value="<?php echo esc_attr( $token_data['user']['mobile'] ?? null ); ?>" required/>
	</p>

	<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
		<label for="tradesafe_token_id_number"><?php esc_html_e( 'ID number', 'woocommerce-tradesafe-gateway' ); ?>
			&nbsp;<span class="required">*</span></label>
		<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="tradesafe_token_id_number" id="tradesafe_token_id_number" value="<?php echo esc_attr( $token_data['user']['idNumber'] ?? null ); ?>" required/>
		<input type="hidden" name="tradesafe_token_id_type" value="NATIONAL">
		<input type="hidden" name="tradesafe_token_id_country" value="ZAF">
	</p>
</fieldset>
