<?php


class TradeSafeProfile
{
    public static function init()
    {
        if (!class_exists('WC_Payment_Gateway')) {
            return;
        }

        self::init_hooks();
    }

    // Initializes WordPress hooks
    private static function init_hooks()
    {
        // Actions
        add_action('woocommerce_edit_account_form', ['TradeSafeProfile', 'edit_account_form']);
        add_action('woocommerce_save_account_details', ['TradeSafeProfile', 'save_account_details']);
    }

    public static function edit_account_form()
    {
        $client = woocommerce_tradesafe_api();
        $user = wp_get_current_user();

        $tokenId = get_user_meta($user->ID, 'tradesafe_token_id', true);
        $banks = $client->getEnums('UniversalBranchCode');
        $bankAccountTypes = $client->getEnums('BankAccountType');
        $organizationType = $client->getEnums('OrganizationType');
        $tokenData = null;

        if ($tokenId) {
            $tokenData = $client->getToken($tokenId);
        }

        if (is_null($client)) {
            echo "<table class='form-table' role='presentation'><tbody>";
            echo "<tr><th scope='row'>Error:</th><td> Could not connect to server</td></tr>";
            echo "</tbody></table>";
            return;
        }

        include_once dirname(__DIR__) . '/templates/myaccount/form-tradesafe-token-user.php';
        include_once dirname(__DIR__) . '/templates/myaccount/form-tradesafe-token-organization.php';

        if ($tokenData) {
            include_once dirname(__DIR__) . '/templates/myaccount/view-tradesafe-token-bank-account.php';
        } else {
            include_once dirname(__DIR__) . '/templates/myaccount/form-tradesafe-token-bank-account.php';
        }
    }

    public static function save_account_details($user_id)
    {
        $client = woocommerce_tradesafe_api();
        $tokenId = get_user_meta($user_id, 'tradesafe_token_id', true);

        $userInfo = [
            'givenName' => $_POST['account_first_name'],
            'familyName' => $_POST['account_last_name'],
            'email' => $_POST['account_email'],
            'mobile' => $_POST['tradesafe_token_mobile'],
            'idNumber' => $_POST['tradesafe_token_id_number'],
            'idType' => $_POST['tradesafe_token_id_type'],
            'idCountry' => $_POST['tradesafe_token_id_country'],
        ];

        $bankAccount = null;
        $organization = null;

        if (isset($_POST['tradesafe_token_bank_account_number'])) {
            $bankAccount = [
                'accountNumber' => $_POST['tradesafe_token_bank_account_number'],
                'accountType' => $_POST['tradesafe_token_bank_account_type'],
                'bank' => $_POST['tradesafe_token_bank'],
            ];
        }

        if (isset($_POST['tradesafe_token_organization_name'])
            && isset($_POST['tradesafe_token_organization_type'])
            && isset($_POST['tradesafe_token_organization_registration_number'])) {
            $organization = [
                'name' => $_POST['tradesafe_token_organization_name'],
                'tradeName' => $_POST['tradesafe_token_organization_trading_name'],
                'type' => $_POST['tradesafe_token_organization_type'],
                'registrationNumber' => $_POST['tradesafe_token_organization_registration_number'],
                'taxNumber' => $_POST['tradesafe_token_organization_tax_number'],
            ];
        }

        if ($tokenId) {
            $tokenData = $client->updateToken($tokenId, $userInfo, $organization, $bankAccount);
        } else {
            $tokenData = $client->createToken($userInfo, $organization, $bankAccount);
            update_user_meta($user_id, 'tradesafe_token_id', sanitize_text_field($tokenData['id']));
        }
    }
}
