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

<fieldset>
    <legend><?php esc_html_e('Personal Details', 'woocommerce-tradesafe-gateway'); ?></legend>

    <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
        <label for="tradesafe_token_mobile"><?php esc_html_e('Mobile number', 'woocommerce-tradesafe-gateway'); ?>
            &nbsp;<span
                    class="required">*</span></label>
        <input type="mobile" class="woocommerce-Input woocommerce-Input--mobile input-text"
               name="tradesafe_token_mobile" id="tradesafe_token_mobile" autocomplete="mobile"
               value="<?php echo esc_attr($tokenData['user']['mobile'] ?? null); ?>" required/>
    </p>

    <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
        <label for="tradesafe_token_id_number"><?php esc_html_e('ID number', 'woocommerce-tradesafe-gateway'); ?>
            &nbsp;<span class="required">*</span></label>
        <input type="text" class="woocommerce-Input woocommerce-Input--text input-text"
               name="tradesafe_token_id_number" id="tradesafe_token_id_number"
               value="<?php echo esc_attr($tokenData['user']['idNumber'] ?? null); ?>" required/>
        <input type="hidden" name="tradesafe_token_id_type" value="NATIONAL">
        <input type="hidden" name="tradesafe_token_id_country" value="ZAF">
    </p>
</fieldset>
