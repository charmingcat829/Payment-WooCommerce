<?php

/**
 * Add Token Details
 */

defined('ABSPATH') || exit;

?>
<fieldset>
    <legend><?php esc_html_e('Banking Details', 'woocommerce-tradesafe-gateway'); ?></legend>

    <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
        <label for="tradesafe_token_bank"><?php esc_html_e('Bank', 'woocommerce-tradesafe-gateway'); ?></label>
        <select name="tradesafe_token_bank" id="tradesafe_token_bank" class="woocommerce-Input">
            <option selected="selected" disabled="disabled" hidden="hidden"></option>
            <?php
            foreach ($banks as $bank) {
                echo '<option value="' . $bank['name'] . '">' . $bank['description'] . '</option>';
            }
            ?>
        </select>
    </p>
    <div class="clear"></div>

    <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-first">
        <label for="tradesafe_token_bank_account_number"><?php esc_html_e('Account number', 'woocommerce-tradesafe-gateway'); ?></label>
        <input type="mobile" class="woocommerce-Input woocommerce-Input--mobile input-text"
               name="tradesafe_token_bank_account_number" id="tradesafe_token_bank_account_number" value=""/>
    </p>

    <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-last">
        <label for="tradesafe_token_bank_account_type"><?php esc_html_e('Account type', 'woocommerce-tradesafe-gateway'); ?></label>
        <select name="tradesafe_token_bank_account_type" id="tradesafe_token_bank_account_type">
            <option selected="selected" disabled="disabled" hidden="hidden"></option>
            <?php
            foreach ($bankAccountTypes as $bankAccountType) {
                echo '<option value="' . $bankAccountType['name'] . '">' . $bankAccountType['description'] . '</option>';
            }
            ?>
        </select>
    </p>
</fieldset>
