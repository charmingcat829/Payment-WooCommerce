<?php

/**
 * Add Token Details
 */

defined('ABSPATH') || exit;

?>
<h3>ARE MY DETAILS SAFE?</h3>
<p>
    TradeSafe forces HTTPS for all services using TLS (SSL) including our public website and the Application.
    All bank account details are encrypted with AES-256. Decryption keys are stored on separate machines from the
    application.
    In English, your details are encrypted with the highest industry-specific standards (which can be found in most
    banks), making your information confidential, secure and safe.
</p>

<form class="woocommerce-tradesafe-EditAccountForm edit-account" action="" method="post">

    <p class="woocommerce-form-row woocommerce-form-row--first form-row form-row-first">
        <label for="tradesafe_token_first_name"><?php esc_html_e('First name', 'woocommerce-tradesafe-gateway'); ?>
            &nbsp;<span class="required">*</span></label>
        <input type="text" class="woocommerce-Input woocommerce-Input--text input-text"
               name="tradesafe_token_first_name" id="tradesafe_token_first_name" autocomplete="given-name"
               value="<?php echo esc_attr($user->first_name); ?>" required/>
    </p>
    <p class="woocommerce-form-row woocommerce-form-row--last form-row form-row-last">
        <label for="tradesafe_token_last_name"><?php esc_html_e('Last name', 'woocommerce-tradesafe-gateway'); ?>
            &nbsp;<span class="required">*</span></label>
        <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="tradesafe_token_last_name"
               id="tradesafe_token_last_name" autocomplete="family-name"
               value="<?php echo esc_attr($user->last_name); ?>" required/>
    </p>
    <div class="clear"></div>

    <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
        <label for="tradesafe_token_email"><?php esc_html_e('Email address', 'woocommerce-tradesafe-gateway'); ?>
            &nbsp;<span class="required">*</span></label>
        <input type="email" class="woocommerce-Input woocommerce-Input--email input-text" name="tradesafe_token_email"
               id="tradesafe_token_email" autocomplete="email" value="<?php echo esc_attr($user->user_email); ?>"
               required/>
    </p>

    <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
        <label for="tradesafe_token_mobile"><?php esc_html_e('Mobile number', 'woocommerce-tradesafe-gateway'); ?>&nbsp;<span
                    class="required">*</span></label>
        <input type="mobile" class="woocommerce-Input woocommerce-Input--mobile input-text"
               name="tradesafe_token_mobile" id="tradesafe_token_mobile" autocomplete="mobile"
               value="" required/>
    </p>

    <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
        <label for="tradesafe_token_id_number"><?php esc_html_e('ID number', 'woocommerce-tradesafe-gateway'); ?>
            &nbsp;<span class="required">*</span></label>
        <input type="text" class="woocommerce-Input woocommerce-Input--mobile input-text"
               name="tradesafe_token_id_number" id="tradesafe_token_id_number"
               value="" required/>
        <input type="hidden" name="tradesafe_token_id_type" value="NATIONAL">
        <input type="hidden" name="tradesafe_token_id_country" value="ZAF">
    </p>

    <fieldset>
        <legend><?php esc_html_e('Banking Details', 'woocommerce-tradesafe-gateway'); ?></legend>

        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="tradesafe_token_bank"><?php esc_html_e('Bank', 'woocommerce-tradesafe-gateway'); ?>
                &nbsp;<span class="required">*</span></label>
            <select name="tradesafe_token_bank" id="tradesafe_token_bank" class="woocommerce-Input" required>
                <option selected="selected" disabled="disabled" hidden="hidden"></option>
                <option value="ABSA"> Absa Bank</option>
                <option value="AFRICAN"> African Bank</option>
                <option value="CAPITEC"> Capitec Bank</option>
                <option value="DISCOVERY"> Discovery Bank</option>
                <option value="FNB"> First National Bank</option>
                <option value="INVESTEC"> Investec Bank</option>
                <option value="MTN"> MTN Banking</option>
                <option value="NEDBANK"> Nedbank</option>
                <option value="SBSA"> Standard Bank South Africa</option>
                <option value="SAPO"> Postbank</option>
                <option value="SASFIN"> Sasfin Bank</option>
            </select>
        </p>
        <div class="clear"></div>

        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-first">
            <label for="tradesafe_token_bank_account_number"><?php esc_html_e('Account number', 'woocommerce-tradesafe-gateway'); ?>
                &nbsp;<span class="required">*</span></label>
            <input type="mobile" class="woocommerce-Input woocommerce-Input--mobile input-text"
                   name="tradesafe_token_bank_account_number" id="tradesafe_token_bank_account_number" autocomplete="mobile"
                   value="" required/>
        </p>

        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-last">
            <label for="tradesafe_token_bank_account_type"><?php esc_html_e('Account type', 'woocommerce-tradesafe-gateway'); ?>
                &nbsp;<span class="required">*</span></label>
            <select name="tradesafe_token_bank_account_type" id="tradesafe_token_bank_account_type" required>
                <option selected="selected" disabled="disabled" hidden="hidden"></option>
                <option value="CHEQUE"> Cheque/Current Account</option>
                <option value="SAVINGS"> Savings Account</option>
                <option value="TRANSMISSION"> Transmission Account</option>
            </select>
        </p>
    </fieldset>

    <fieldset>
        <legend><?php esc_html_e('Organization Details', 'woocommerce-tradesafe-gateway'); ?></legend>

        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="tradesafe_token_organization_name"><?php esc_html_e('Name', 'woocommerce-tradesafe-gateway'); ?></label>
            <input type="text" class="woocommerce-Input woocommerce-Input--email input-text"
                   name="tradesafe_token_organization_name" id="tradesafe_token_organization_name"
                   value=""/>
        </p>

        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-first">
            <label for="tradesafe_token_organization_type"><?php esc_html_e('Type', 'woocommerce-tradesafe-gateway'); ?></label>
            <select name="tradesafe_token_organization_type">
                <option selected="selected" disabled="disabled" hidden="hidden"></option>
                <option value="SOLE_PROP"> Sole Proprietorship</option>
                <option value="PRIVATE"> Private Company</option>
                <option value="PUBLIC"> Public Company</option>
                <option value="STATE"> State Owned Enterprise</option>
                <option value="INC"> Personal Liability Company</option>
                <option value="CC"> Close Corporation</option>
                <option value="NPC"> Not for Profit Company</option>
                <option value="TRUST"> Trust</option>
                <option value="OTHER"> Other</option>
            </select>
        </p>

        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-last">
            <label for="tradesafe_token_organization_trading_name"><?php esc_html_e('Trading Name', 'woocommerce-tradesafe-gateway'); ?></label>
            <input type="text" class="woocommerce-Input woocommerce-Input--email input-text"
                   name="tradesafe_token_organization_trading_name" id="tradesafe_token_organization_trading_name"
                   value=""/>
        </p>
        <div class="clear"></div>

        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-first">
            <label for="tradesafe_token_organization_registration_number"><?php esc_html_e('Registration Number', 'woocommerce-tradesafe-gateway'); ?></label>
            <input type="text" class="woocommerce-Input woocommerce-Input--email input-text"
                   name="tradesafe_token_organization_registration_number" id="tradesafe_token_organization_registration_number"
                   value=""/>
        </p>

        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-last">
            <label for="tradesafe_token_organization_tax_number"><?php esc_html_e('VAT Number', 'woocommerce-tradesafe-gateway'); ?></label>
            <input type="email" class="woocommerce-Input woocommerce-Input--email input-text"
                   name="tradesafe_token_organization_tax_number" id="tradesafe_token_organization_tax_number"
                   value=""/>
        </p>
    </fieldset>
    <div class="clear"></div>

    <p>
        <?php wp_nonce_field('save_tradesafe_token_details', 'save-account-details-nonce'); ?>
        <button type="submit" class="woocommerce-tradesafe-Button button" name="save_tradesafe_token_details"
                value="<?php esc_attr_e('Save changes', 'woocommerce-tradesafe-gateway'); ?>"><?php esc_html_e('Save changes', 'woocommerce-tradesafe-gateway'); ?></button>
        <input type="hidden" name="action" value="save_tradesafe_token_details"/>
    </p>
</form>
