<?php
/**
 * Add Token Details.
 *
 * @package TradeSafe Payment Gateway
 */

defined( 'ABSPATH' ) || exit;

?>
<fieldset>
	<legend><?php esc_html_e( 'Banking Details', 'woocommerce-tradesafe-gateway' ); ?></legend>

	<p>
		For a payout or a refund to be made to you, we require your bank account details. Please ensure you enter your
		bank account details correctly. Neither we, nor TradeSafe, will be held responsible should the funds be paid
		into another bank account should you provide incorrect bank account details.
	</p>

	<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
		<label for="tradesafe_token_bank"><?php esc_html_e( 'Bank', 'woocommerce-tradesafe-gateway' ); ?></label>
		<select name="tradesafe_token_bank" id="tradesafe_token_bank" class="woocommerce-Input">
			<option selected="selected" disabled="disabled" hidden="hidden"></option>
			<?php
			foreach ( $banks as $bank_name => $bank_description ) {
				echo '<option value="' . esc_attr( $bank_name ) . '">' . esc_attr( $bank_description ) . '</option>';
			}
			?>
		</select>
	</p>
	<div class="clear"></div>

	<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-first">
		<label for="tradesafe_token_bank_account_number"><?php esc_html_e( 'Account number', 'woocommerce-tradesafe-gateway' ); ?></label>
		<input type="mobile" class="woocommerce-Input woocommerce-Input--mobile input-text" name="tradesafe_token_bank_account_number" id="tradesafe_token_bank_account_number" value=""/>
	</p>

	<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-last">
		<label for="tradesafe_token_bank_account_type"><?php esc_html_e( 'Account type', 'woocommerce-tradesafe-gateway' ); ?></label>
		<select name="tradesafe_token_bank_account_type" id="tradesafe_token_bank_account_type">
			<option selected="selected" disabled="disabled" hidden="hidden"></option>
			<?php
			foreach ( $bank_account_types as $bank_account_name => $bank_account_description ) {
				echo '<option value="' . esc_attr( $bank_account_name ) . '">' . esc_attr( $bank_account_description ) . '</option>';
			}
			?>
		</select>
	</p>
</fieldset>
