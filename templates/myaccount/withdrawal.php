<?php

defined( 'ABSPATH' ) || exit;

?>

<?php if ( $token['valid'] ) : ?>
	<?php if ( $pending ) : ?>
	<fieldset id="withdrawal-request">
		<legend><?php esc_html_e( 'Pending Withdrawal Requests', 'woocommerce-tradesafe-gateway' ); ?></legend>
	</fieldset>
	<?php else : ?>
		<form method="post" id="withdrawal-request">
			<h3>Available Balance: <?php echo wc_price( esc_html( $token['balance'] ) ); ?></h3>

			<p>Please note: All withdrawals incur a R5 withdrawal fee.</p>

			<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-first">
				<label for="tradesafe_withdrawal_request"><?php esc_html_e( 'Amount', 'woocommerce-tradesafe-gateway' ); ?></label>
				<input type="number" class="woocommerce-Input woocommerce-Input--text input-text"
					   name="tradesafe_withdrawal_request"
					   id="tradesafe_withdrawal_request"
					   value="<?php esc_html_e( $token['balance'] ); ?>"
					   min="<?php echo $token['balance'] > 5 ? '5' : '0'; ?>"
					   max="<?php esc_html_e( $token['balance'] ); ?>"
					   step="0.01"/>
			</p>
			<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-last">
				<label>&nbsp;</label>
				<?php wp_nonce_field( 'tradesafe_update_token', 'tradesafe-update-token-nonce' ); ?>
			<input class="button button-primary" type="submit" name="withdrawal_submit" value="Request Withdraw">
			</p>
		</form>
	<?php endif; ?>

	<fieldset id="banking-details">
		<legend><?php esc_html_e( 'Banking Details', 'woocommerce-tradesafe-gateway' ); ?></legend>

		<p>All withdrawals will be made to the following account.</p>

		<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
			<label for="tradesafe_token_bank"><?php esc_html_e( 'Bank', 'woocommerce-tradesafe-gateway' ); ?></label>
			<?php
			foreach ( $banks as $bank_name => $bank_description ) {
				if ( $bank_name === $token['bankAccount']['bank'] ) {
					esc_attr_e( $bank_description );
				}
			}
			?>
		</p>
		<div class="clear"></div>

		<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-first">
			<label for="tradesafe_token_bank_account_number"><?php esc_html_e( 'Account number', 'woocommerce-tradesafe-gateway' ); ?></label>
			<?php esc_attr_e( $token['bankAccount']['accountNumber'] ?? null ); ?>
		</p>

		<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-last">
			<label for="tradesafe_token_bank_account_type"><?php esc_html_e( 'Account type', 'woocommerce-tradesafe-gateway' ); ?></label>
			<?php
			foreach ( $bank_account_types as $bank_account_name => $bank_account_description ) {
				if ( $bank_account_name === $token['bankAccount']['accountType'] ) {
					esc_attr_e( $bank_account_description );
				}
			}
			?>
		</p>

		<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row">
			<label for="tradesafe_token_payout_interval"><?php esc_html_e( 'Automatic Payout Interval', 'woocommerce-tradesafe-gateway' ); ?></label>
			<?php
			foreach ( $payout_interval as $interval_name => $interval_description ) {
				if ( $interval_name === $token['settings']['payout']['interval'] ) {
					esc_attr_e( $interval_description, 'woocommerce-tradesafe-gateway' );
				}
			}
			?>
		</p>
	</fieldset>

	<fieldset id="personal-details">
		<legend><?php esc_html_e( 'Personal Details', 'woocommerce-tradesafe-gateway' ); ?></legend>

		<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-first">
			<label for="tradesafe_token_user_given_name"><?php esc_html_e( 'First Name', 'woocommerce-tradesafe-gateway' ); ?></label>
			<?php esc_attr_e( $token['user']['givenName'] ?? null ); ?>
		</p>

		<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-last">
			<label for="tradesafe_token_user_family_name"><?php esc_html_e( 'Last Name', 'woocommerce-tradesafe-gateway' ); ?></label>
			<?php esc_attr_e( $token['user']['familyName'] ?? null ); ?>
		</p>

		<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-first">
			<label for="tradesafe_token_user_mobile"><?php esc_html_e( 'Mobile', 'woocommerce-tradesafe-gateway' ); ?></label>
			<?php esc_attr_e( $token['user']['mobile'] ?? null ); ?>
		</p>

		<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-last">
			<label for="tradesafe_token_user_email"><?php esc_html_e( 'Email', 'woocommerce-tradesafe-gateway' ); ?></label>
			<?php esc_attr_e( $token['user']['email'] ?? null ); ?>
		</p>

		<?php if ( is_null( $token['organization'] ) ) : ?>
		<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row">
			<label for="tradesafe_token_user_id_number"><?php esc_html_e( 'ID Number', 'woocommerce-tradesafe-gateway' ); ?></label>
			<?php esc_attr_e( $token['user']['idNumber'] ?? null ); ?>
		</p>
		<?php endif; ?>
	</fieldset>

	<?php if ( ! is_null( $token['organization'] ) ) : ?>
	<fieldset id="organization-details">
		<legend><?php esc_html_e( 'Organisation Details', 'woocommerce-tradesafe-gateway' ); ?></legend>

		<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-first">
			<label for="tradesafe_token_organization_name"><?php esc_html_e( 'Name', 'woocommerce-tradesafe-gateway' ); ?></label>
			<?php
			esc_attr_e( $token['organization']['name'] );

			foreach ( $organization_types as $organization_name => $organization_description ) {
				if ( $token['organization']['type'] === $organization_name ) {
					echo ' (' . esc_attr( $organization_description ) . ')';
				}
			}
			?>
		</p>

		<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-last">
			<label for="tradesafe_token_organization_trade_name"><?php esc_html_e( 'Trade Name', 'woocommerce-tradesafe-gateway' ); ?></label>
			<?php esc_attr_e( $token['organization']['tradeName'] ?? 'N/A' ); ?>
		</p>

		<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-first">
			<label for="tradesafe_token_organization_registration_number"><?php esc_html_e( 'Registration Number', 'woocommerce-tradesafe-gateway' ); ?></label>
			<?php esc_attr_e( $token['organization']['registration'] ); ?>
			<p>If registering as a sole prop you must enter your ID number in place of a business registration number.</p>
		</p>

		<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-last">
			<label for="tradesafe_token_organization_tax_number"><?php esc_html_e( 'Vat Number', 'woocommerce-tradesafe-gateway' ); ?></label>
			<?php esc_attr_e( $token['organization']['taxNumber'] ?? 'N/A' ); ?>
		</p>
	</fieldset>
	<?php endif; ?>
<?php else : ?>
	<form method="post">
		<h3>Why do I need to provide my ID / Business Registration and mobile number?</h3>
		<p>First off, your details are safe and secure. Your information is encrypted with the highest industry-specific
			standards which can be found in most banks.</p>
		<p>Our payment service provider is TradeSafe Escrow. When the buyer deposits the funds, they will make payment
			to
			TradeSafe who will hold the funds in the middle (in a trust account). Once the seller has delivered what was
			ordered, then TradeSafe releases the funds to the selling party.</p>
		<p>TradeSafe is required by the South African Reserve Bank to know the identities of the parties and that is why
			these
			details are required.</p>

		<?php if ( $errors ) : ?>
			<div class="woocommerce-error">
				<div><strong>The following errors occurred while updating your details:</strong></div>
				<?php foreach ( $errors as $error ) : ?>
					<div><?php esc_html_e( $error, 'tradesafe-payment-gateway' ); ?></div>
				<?php endforeach; ?>
			</div>
			<div class="clear"></div>
		<?php endif; ?>

		<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
			<label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">
				<input type="checkbox" class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox"
					   name="is_organization" <?php checked( ! empty( $is_organization ), true ); // WPCS: input var ok, csrf ok. ?>
					   id="is_organization"/>
				<span class="woocommerce-is-organization-text"><?php esc_html_e( 'Is this account for an organisation?', 'woocommerce' ); ?></span>
			</label>
		</p>
		<div class="clear"></div>

		<fieldset id="user-details">
			<legend><?php esc_html_e( 'Personal Details', 'woocommerce-tradesafe-gateway' ); ?></legend>

			<p class="woocommerce-form-row woocommerce-form-row--first form-row form-row-first">
				<label for="tradesafe_token_given_name"><?php esc_html_e( 'First name', 'woocommerce' ); ?>&nbsp;<span
							class="required">*</span></label>
				<input type="text" class="woocommerce-Input woocommerce-Input--text input-text"
					   name="tradesafe_token_given_name"
					   id="tradesafe_token_given_name" autocomplete="given-name"
					   value="<?php esc_attr_e( $token['user']['givenName'] ?? null ); ?>"/>
			</p>
			<p class="woocommerce-form-row woocommerce-form-row--last form-row form-row-last">
				<label for="tradesafe_token_family_name"><?php esc_html_e( 'Last name', 'woocommerce' ); ?>&nbsp;<span
							class="required">*</span></label>
				<input type="text" class="woocommerce-Input woocommerce-Input--text input-text"
					   name="tradesafe_token_family_name"
					   id="tradesafe_token_family_name" autocomplete="family-name"
					   value="<?php esc_attr_e( $token['user']['familyName'] ?? null ); ?>"/>
			</p>
			<div class="clear"></div>

			<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
				<label for="tradesafe_token_email"><?php esc_html_e( 'Email address', 'woocommerce' ); ?>&nbsp;<span
							class="required">*</span></label>
				<input type="email" class="woocommerce-Input woocommerce-Input--email input-text"
					   name="tradesafe_token_email"
					   id="tradesafe_token_email" autocomplete="email"
					   value="<?php esc_attr_e( $token['user']['email'] ?? null ); ?>"/>
			</p>
			<div class="clear"></div>

			<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
				<label for="tradesafe_token_mobile"><?php esc_html_e( 'Mobile Number', 'woocommerce' ); ?>&nbsp;<span
							class="required">*</span></label>
				<input type="tel" class="woocommerce-Input woocommerce-Input--mobile input-text"
					   name="tradesafe_token_mobile"
					   id="tradesafe_token_mobile" autocomplete="mobile"
					   value="<?php esc_attr_e( $token['user']['mobile'] ?? null ); ?>"/>
			</p>
			<div class="clear"></div>

			<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide toggle-id-number">
				<label for="tradesafe_token_id_number"><?php esc_html_e( 'ID Number', 'woocommerce' ); ?>&nbsp;<span
							class="required">*</span></label>
				<input type="text" class="woocommerce-Input woocommerce-Input--mobile input-text"
					   name="tradesafe_token_id_number"
					   id="tradesafe_token_id_number" autocomplete="id-number"
					   value="<?php esc_attr_e( $token['user']['idNumber'] ?? null ); ?>"/>
			</p>
			<div class="clear"></div>
		</fieldset>

		<fieldset id="organization-details">
			<legend><?php esc_html_e( 'Organization Details', 'woocommerce-tradesafe-gateway' ); ?></legend>

			<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
				<label for="tradesafe_token_organization_name"><?php esc_html_e( 'Name', 'woocommerce-tradesafe-gateway' ); ?></label>
				<input type="text" class="woocommerce-Input woocommerce-Input--text input-text"
					   name="tradesafe_token_organization_name" id="tradesafe_token_organization_name"
					   value="<?php esc_attr_e( $token['organization']['name'] ?? null ); ?>"/>
			</p>

			<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-first">
				<label for="tradesafe_token_organization_type"><?php esc_html_e( 'Type', 'woocommerce-tradesafe-gateway' ); ?></label>
				<select name="tradesafe_token_organization_type">
					<option selected="selected" disabled="disabled" hidden="hidden"></option>
					<?php
					foreach ( $organization_types as $organization_name => $organization_description ) {
						echo '<option value="' . esc_attr( $organization_name ) . '" ' . ( ( $token['organization']['type'] ?? null ) === $organization_name ? 'selected' : '' ) . '>' . esc_attr( $organization_description ) . '</option>';
					}
					?>
				</select>
			</p>

			<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-last">
				<label for="tradesafe_token_organization_trading_name"><?php esc_html_e( 'Trading Name', 'woocommerce-tradesafe-gateway' ); ?></label>
				<input type="text" class="woocommerce-Input woocommerce-Input--text input-text"
					   name="tradesafe_token_organization_trading_name" id="tradesafe_token_organization_trading_name"
					   value="<?php esc_attr_e( $token['organization']['tradeName'] ?? null ); ?>"/>
			</p>
			<div class="clear"></div>

			<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-first">
				<label for="tradesafe_token_organization_registration_number"><?php esc_html_e( 'Registration Number', 'woocommerce-tradesafe-gateway' ); ?></label>
				<input type="text" class="woocommerce-Input woocommerce-Input--text input-text"
					   name="tradesafe_token_organization_registration_number"
					   id="tradesafe_token_organization_registration_number"
					   value="<?php esc_attr_e( $token['organization']['registration'] ?? null ); ?>"/>
			</p>

			<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-last">
				<label for="tradesafe_token_organization_tax_number"><?php esc_html_e( 'VAT Number', 'woocommerce-tradesafe-gateway' ); ?></label>
				<input type="text" class="woocommerce-Input woocommerce-Input--text input-text"
					   name="tradesafe_token_organization_tax_number" id="tradesafe_token_organization_tax_number"
					   value="<?php esc_attr_e( $token['organization']['taxNumber'] ?? null ); ?>"/>
			</p>
		</fieldset>

		<fieldset id="banking-details">
			<legend><?php esc_html_e( 'Banking Details', 'woocommerce-tradesafe-gateway' ); ?></legend>

			<p>
				For a payout or a refund to be made to you, we require your bank account details. Please ensure you
				enter your bank account details correctly. Neither we, nor TradeSafe, will be held responsible should
				the funds be paid into another bank account should you provide incorrect bank account details.
			</p>

			<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
				<label for="tradesafe_token_bank"><?php esc_html_e( 'Bank', 'woocommerce-tradesafe-gateway' ); ?>
					&nbsp;<span class="required">*</span></label>
				<select name="tradesafe_token_bank" id="tradesafe_token_bank" class="woocommerce-Input">
					<option selected="<?php esc_attr( ( $token['bankAccount']['bank'] ?? null ) === null ? 'selected' : '' ); ?>"
							disabled="disabled" hidden="hidden"></option>
					<?php
					foreach ( $banks as $bank_name => $bank_description ) {
						$selected = null;
						if ( $bank_name === $token['bankAccount']['bank'] ) {
							$selected = ' selected="selected"';
						}
						echo '<option value="' . esc_attr( $bank_name ) . '"' . $selected . '>' . esc_attr( $bank_description ) . '</option>';
					}
					?>
				</select>
			</p>
			<div class="clear"></div>

			<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-first">
				<label for="tradesafe_token_bank_account_number"><?php esc_html_e( 'Account number', 'woocommerce-tradesafe-gateway' ); ?>
					&nbsp;<span class="required">*</span></label>
				<input type="text" class="woocommerce-Input woocommerce-Input--mobile input-text"
					   name="tradesafe_token_bank_account_number" id="tradesafe_token_bank_account_number"
					   value="<?php esc_attr_e( $token['bankAccount']['accountNumber'] ?? null ); ?>"/>
			</p>

			<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-last">
				<label for="tradesafe_token_bank_account_type"><?php esc_html_e( 'Account type', 'woocommerce-tradesafe-gateway' ); ?>
					&nbsp;<span class="required">*</span></label>
				<select name="tradesafe_token_bank_account_type" id="tradesafe_token_bank_account_type"
						value="<?php esc_attr_e( $token['bankAccount']['accountType'] ?? null ); ?>">
					<option selected="<?php esc_attr( ( $token['bankAccount']['accountType'] ?? null ) === null ? 'selected' : '' ); ?>"
							disabled="disabled" hidden="hidden"></option>
					<?php
					foreach ( $bank_account_types as $bank_account_name => $bank_account_description ) {
						$selected = null;
						if ( $bank_account_name === $token['bankAccount']['accountType'] ) {
							$selected = ' selected="selected"';
						}
						echo '<option value="' . esc_attr( $bank_account_name ) . '"' . $selected . '>' . esc_attr( $bank_account_description ) . '</option>';
					}
					?>
				</select>
			</p>
		</fieldset>

		<?php wp_nonce_field( 'tradesafe_update_token', 'tradesafe-update-token-nonce' ); ?>

		<input type="submit" name="update_token_submit">
	</form>
<?php endif; ?>
