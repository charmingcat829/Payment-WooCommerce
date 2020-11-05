<?php


class TradeSafe
{
    public static function init()
    {
        // Actions
        add_action('admin_init', ['TradeSafe', 'settings_api_init']);
        add_action('admin_menu', ['TradeSafe', 'register_options_page']);

        add_rewrite_rule('^tradesafe/eft-details/([0-9]+)[/]?$', 'index.php?tradesafe=eft-details&order-id=$matches[1]', 'top');
        add_rewrite_rule('^tradesafe/unlink?$', 'index.php?tradesafe=unlink', 'top');
        add_action('parse_request', ['TradeSafe', 'parse_request']);

        add_rewrite_endpoint('tradesafe', EP_PAGES);

        add_filter('query_vars', function ($query_vars) {
            $query_vars[] = 'tradesafe';
            $query_vars[] = 'order-id';

            return $query_vars;
        });
    }

    /**
     * Settings Page
     */
    public static function settings_api_init()
    {
        add_settings_section(
            'tradesafe_info_section',
            'Callback URL\'s',
            [
                'TradeSafe',
                'settings_info_callback'
            ],
            'tradesafe'
        );

        add_settings_section(
            'tradesafe_settings_section',
            'Application Settings',
            [
                'TradeSafe',
                'settings_application_callback'
            ],
            'tradesafe'
        );

        add_settings_field(
            'tradesafe_client_id',
            'Client ID',
            [
                'TradeSafe',
                'setting_client_id_callback'
            ],
            'tradesafe',
            'tradesafe_settings_section'
        );
        register_setting('tradesafe', 'tradesafe_client_id');

        add_settings_field(
            'tradesafe_client_secret',
            'Client Secret',
            [
                'TradeSafe',
                'setting_client_secret_callback'
            ],
            'tradesafe',
            'tradesafe_settings_section'
        );
        register_setting('tradesafe', 'tradesafe_client_secret');

        add_settings_field(
            'tradesafe_token',
            'Token',
            [
                'TradeSafe',
                'setting_token_callback'
            ],
            'tradesafe',
            'tradesafe_settings_section'
        );
        register_setting('tradesafe', 'tradesafe_token');

        add_settings_field(
            'tradesafe_production_mode',
            'Production Mode',
            [
                'TradeSafe',
                'setting_production_mode_callback'
            ],
            'tradesafe',
            'tradesafe_settings_section'
        );
        register_setting('tradesafe', 'tradesafe_production_mode');

        add_settings_section(
            'tradesafe_transaction_section',
            'Transaction Settings',
            [
                'TradeSafe',
                'settings_transaction_callback'
            ],
            'tradesafe'
        );

        add_settings_field(
            'tradesafe_transaction_agent',
            'Agent / Marketplace',
            [
                'TradeSafe',
                'setting_transaction_agent_callback'
            ],
            'tradesafe',
            'tradesafe_transaction_section'
        );
        register_setting('tradesafe', 'tradesafe_transaction_agent');

        add_settings_field(
            'tradesafe_transaction_fee',
            'Agent Fee',
            [
                'TradeSafe',
                'setting_transaction_fee_callback'
            ],
            'tradesafe',
            'tradesafe_transaction_section'
        );
        register_setting('tradesafe', 'tradesafe_transaction_fee');

        add_settings_field(
            'tradesafe_transaction_fee_type',
            'Agent Fee Type',
            [
                'TradeSafe',
                'setting_transaction_fee_type_callback'
            ],
            'tradesafe',
            'tradesafe_transaction_section'
        );
        register_setting('tradesafe', 'tradesafe_transaction_fee_type');

        add_settings_field(
            'tradesafe_transaction_fee_allocation',
            'Agent Fee Allocation',
            [
                'TradeSafe',
                'setting_transaction_fee_allocation_callback'
            ],
            'tradesafe',
            'tradesafe_transaction_section'
        );
        register_setting('tradesafe', 'tradesafe_transaction_fee_allocation');
    }

    public static function settings_info_callback()
    {
        $urls = [
            'callback' => site_url('/tradesafe/callback/'),
            'auth_callback' => site_url('/tradesafe/oauth/callback/'),
        ];

        echo '<p>The following URL\'s can be used to register your application with TradeSafe.</p>';
        echo '<table class="form-table">
        <tbody>
        <tr>
            <th scope="row">Callback URL</th>
            <td>' . esc_attr($urls['callback']) . '</td>
        </tr>
        <tr>
            <th scope="row">Auth Callback URL</th>
            <td>' . esc_attr($urls['auth_callback']) . '</td>
        </tr>
        </tbody>
    </table>';
    }

    public static function settings_application_callback()
    {
        if (get_option('tradesafe_client_id') && get_option('tradesafe_client_secret')) {
            $client = woocommerce_tradesafe_api();

            $tokenData = $client->getToken(get_option('tradesafe_token'));

            echo "<table class='form-table' role='presentation'><tbody>";
            echo "<tr><th scope='row'>Name:</th><td>" . esc_attr($tokenData['name']) . "</td></tr>";
            echo "<tr><th scope='row'>Email:</th><td>" . esc_attr($tokenData['user']['email']) . "</td></tr>";
            echo "<tr><th scope='row'>Mobile:</th><td>" . esc_attr($tokenData['user']['mobile']) . "</td></tr>";
            echo "</tbody></table>";
        }
    }

    public static function settings_transaction_callback()
    {
        //
    }

    public static function setting_client_id_callback()
    {
        echo '<input name="tradesafe_client_id" id="tradesafe_client_id" type="text" value="' . get_option('tradesafe_client_id') . '" class="regular-text ltr" />';
    }

    public static function setting_client_secret_callback()
    {
        echo '<input name="tradesafe_client_secret" id="tradesafe_client_secret" type="password" value="' . get_option('tradesafe_client_secret') . '" class="regular-text ltr" />';
    }

    public static function setting_token_callback()
    {
        echo '<input name="tradesafe_token" id="tradesafe_token" type="text" value="' . get_option('tradesafe_token') . '" class="regular-text ltr" />';
    }

    public static function setting_production_mode_callback()
    {
        echo '<input name="tradesafe_production_mode" id="tradesafe_production_mode" type="checkbox" value="1" ' . checked(1, get_option('tradesafe_production_mode'), false) . ' />';
    }

    public static function setting_transaction_agent_callback()
    {
        echo '<input name="tradesafe_transaction_agent" id="tradesafe_transaction_agent" type="checkbox" value="1" ' . checked(1, get_option('tradesafe_transaction_agent'), false) . ' />';
    }

    public static function setting_transaction_fee_callback()
    {
        echo '<input name="tradesafe_transaction_fee" id="tradesafe_transaction_fee" type="number" value="' . get_option('tradesafe_transaction_fee') . '" class="small-text ltr" />';
    }

    public static function setting_transaction_fee_type_callback()
    {
        echo '<select name="tradesafe_transaction_fee_type" class="small-text ltr">';
        echo '<option ' . (get_option('tradesafe_transaction_fee_type') === 'percent' ? 'selected' : '') . ' value="percent">Percent</option>';
        echo '<option ' . (get_option('tradesafe_transaction_fee_type') === 'fixed' ? 'selected' : '') . ' value="fixed">Fixed</option>';
        echo '</select>';
    }

    public static function setting_transaction_fee_allocation_callback()
    {
        echo '<select name="tradesafe_transaction_fee_allocation" class="small-text ltr">';
        echo '<option ' . (get_option('tradesafe_transaction_fee_allocation') === 'seller' ? 'selected' : '') . ' value="seller">Seller</option>';
        echo '<option ' . (get_option('tradesafe_transaction_fee_allocation') === 'buyer' ? 'selected' : '') . ' value="buyer">Buyer</option>';
        echo '</select>';
    }

    // Add the link to the settings menu
    public static function register_options_page()
    {
        add_menu_page(
            __('TradeSafe', 'woocommerce-gateway-tradesafe'),
            __('TradeSafe', 'woocommerce-gateway-tradesafe'),
            'manage_options',
            'tradesafe',
            [
                'TradeSafe',
                'settings_page',
            ],
            'dashicons-admin-settings',
            58
        );
    }

    // Display settings page
    public static function settings_page()
    {
        include_once __DIR__ . '/../partials/settings.php';
    }


    public static function parse_request($wp)
    {
        if (array_key_exists('tradesafe', $wp->query_vars)) {
            switch ($wp->query_vars['tradesafe']) {
                case "eft-details":
                    self::eft_details_page($wp->query_vars['order-id']);
                    break;
                case "unlink":
                    $user = wp_get_current_user();
                    delete_user_meta($user->ID, 'tradesafe_token_id');
                    wp_redirect(wc_get_page_permalink('myaccount'));
                    break;
                default:
                    status_header(404);
                    include get_query_template('404');
                    exit;
            }
        }
    }
}
