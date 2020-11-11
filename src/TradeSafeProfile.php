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
        add_action('show_user_profile', ['TradeSafeProfile', 'view']);
        add_action('edit_user_profile', ['TradeSafeProfile', 'view']);
        add_action('woocommerce_account_tradesafe-settings_endpoint', ['TradeSafeProfile', 'view']);

        // Filters
        add_filter('woocommerce_account_menu_items', ['TradeSafeProfile', 'menu_link'], 40);
    }

    public static function add_endpoints()
    {
        add_rewrite_endpoint('tradesafe-settings', EP_ROOT | EP_PAGES);
    }

    /**
     * @param $menu_links
     *
     * @return array
     */
    public static function menu_link($menu_links)
    {
        $menu_links = array_slice($menu_links, 0, 5, true)
            + array('tradesafe-settings' => 'TradeSafe Details')
            + array_slice($menu_links, 5, null, true);

        return $menu_links;
    }

    /**
     * View Account
     */
    public static function view()
    {
        $user = wp_get_current_user();
        $tokenId = get_user_meta($user->ID, 'tradesafe_token_id', true);

        $client = woocommerce_tradesafe_api();

        if (is_null($client)) {
            echo "<table class='form-table' role='presentation'><tbody>";
            echo "<tr><th scope='row'>Error:</th><td> Could not connect to server</td></tr>";
            echo "</tbody></table>";
            return;
        }

        if ($tokenId) {
            $tokenData = $client->getToken($tokenId);

            include_once dirname(__DIR__) . '/templates/myaccount/view-tradesafe-token.php';
        } else {
            if (isset($_POST) && !empty($_POST)) {
                $tokenInput = [
                    'givenName' => $_POST['tradesafe_token_first_name'],
                    'familyName' => $_POST['tradesafe_token_last_name'],
                    'email' => $_POST['tradesafe_token_email'],
                    'mobile' => $_POST['tradesafe_token_mobile'],
                    'idNumber' => $_POST['tradesafe_token_id_number'],
                    'idType' => $_POST['tradesafe_token_id_type'],
                    'idCountry' => $_POST['tradesafe_token_id_country'],
                    'bank' => $_POST['tradesafe_token_bank'],
                    'accountNumber' => $_POST['tradesafe_token_bank_account_number'],
                    'accountType' => $_POST['tradesafe_token_bank_account_type'],
                ];

                $tokenData = $client->createToken($tokenInput);
                update_user_meta($user->ID, 'tradesafe_token_id', sanitize_text_field($tokenData['id']));

                include_once dirname(__DIR__) . '/templates/myaccount/view-tradesafe-token.php';
            } else {
                include_once dirname(__DIR__) . '/templates/myaccount/form-tradesafe-token.php';
            }
        }

    }

    /**
     * Unlink account
     */
    public static function unlink()
    {
        if (is_user_logged_in()) {
            $user = wp_get_current_user();
            delete_user_meta($user->ID, 'tradesafe_user_id');
            $edit_account_url = wc_get_endpoint_url('tradesafe', '', wc_get_page_permalink('myaccount'));
            wp_redirect($edit_account_url);
        } else {
            status_header(404);
            include get_query_template('404');
            exit;
        }
    }
}
