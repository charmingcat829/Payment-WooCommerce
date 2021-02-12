<?php

/**
 * View Token Details
 */

defined('ABSPATH') || exit;

?>
<fieldset>
    <legend><?php esc_html_e('Banking Details', 'woocommerce-tradesafe-gateway'); ?> (<a href="<?php print get_site_url(); ?>?tradesafe=unlink">Delete</a>)</legend>

    <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
        <label for="tradesafe_token_bank"><?php esc_html_e('Bank', 'woocommerce-tradesafe-gateway'); ?></label>
        <?php
        foreach ($banks as $bank) {
            if ($bank['name'] === $tokenData['bankAccount']['bank'] ?? null) {
                echo esc_attr($bank['description']);
            }
        }
        ?>
    </p>
    <div class="clear"></div>

    <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-first">
        <label for="tradesafe_token_bank_account_number"><?php esc_html_e('Account number', 'woocommerce-tradesafe-gateway'); ?></label>
        <?php echo esc_attr($tokenData['bankAccount']['accountNumber'] ?? null) ?>
    </p>

    <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-last">
        <label for="tradesafe_token_bank_account_type"><?php esc_html_e('Account type', 'woocommerce-tradesafe-gateway'); ?></label>
        <?php
        foreach ($bankAccountTypes as $bankAccountType) {
            if ($bankAccountType['name'] === $tokenData['bankAccount']['accountType'] ?? null) {
                echo esc_attr($bankAccountType['description']);
            }
        }
        ?>
    </p>
</fieldset>
