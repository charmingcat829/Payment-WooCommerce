<?php

/**
 * Add Token Details
 */

defined('ABSPATH') || exit;

?>

<fieldset>
    <legend><?php esc_html_e('Organization Details (Optional)', 'woocommerce-tradesafe-gateway'); ?></legend>

    <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
        <label for="tradesafe_token_organization_name"><?php esc_html_e('Name', 'woocommerce-tradesafe-gateway'); ?></label>
        <input type="text" class="woocommerce-Input woocommerce-Input--text input-text"
               name="tradesafe_token_organization_name" id="tradesafe_token_organization_name"
               value="<?php echo esc_attr($tokenData['organization']['name'] ?? null) ?>"/>
    </p>

    <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-first">
        <label for="tradesafe_token_organization_type"><?php esc_html_e('Type', 'woocommerce-tradesafe-gateway'); ?></label>
        <select name="tradesafe_token_organization_type">
            <option selected="selected" disabled="disabled" hidden="hidden"></option>
            <?php
            foreach ($organizationType as $organizationType) {
                echo '<option value="' . $organizationType['name'] . '" ' . ($organizationType === ($tokenData['organization']['name'] ?? null) ? 'selected' : '') . '>' . $organizationType['description'] . '</option>';
            }
            ?>
        </select>
    </p>

    <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-last">
        <label for="tradesafe_token_organization_trading_name"><?php esc_html_e('Trading Name', 'woocommerce-tradesafe-gateway'); ?></label>
        <input type="text" class="woocommerce-Input woocommerce-Input--text input-text"
               name="tradesafe_token_organization_trading_name" id="tradesafe_token_organization_trading_name"
               value="<?php echo esc_attr($tokenData['organization']['tradeName'] ?? null) ?>"/>
    </p>
    <div class="clear"></div>

    <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-first">
        <label for="tradesafe_token_organization_registration_number"><?php esc_html_e('Registration Number', 'woocommerce-tradesafe-gateway'); ?></label>
        <input type="text" class="woocommerce-Input woocommerce-Input--text input-text"
               name="tradesafe_token_organization_registration_number"
               id="tradesafe_token_organization_registration_number"
               value="<?php echo esc_attr($tokenData['organization']['registration'] ?? null) ?>"/>
    </p>

    <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-last">
        <label for="tradesafe_token_organization_tax_number"><?php esc_html_e('VAT Number', 'woocommerce-tradesafe-gateway'); ?></label>
        <input type="text" class="woocommerce-Input woocommerce-Input--text input-text"
               name="tradesafe_token_organization_tax_number" id="tradesafe_token_organization_tax_number"
               value="<?php echo esc_attr($tokenData['organization']['taxNumber'] ?? null) ?>"/>
    </p>
</fieldset>