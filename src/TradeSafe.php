<?php


class TradeSafe
{
    public static function init()
    {
        // Actions
        add_action('admin_init', ['TradeSafe', 'settings_api_init']);
        add_action('admin_menu', ['TradeSafe', 'register_options_page']);

        add_rewrite_rule('^tradesafe/eft-details/([0-9]+)[/]?$', 'index.php?tradesafe=eft-details&order-id=$matches[1]', 'top');
        add_action('parse_request', ['TradeSafe', 'parse_request']);

        add_rewrite_endpoint( 'tradesafe', EP_PAGES );

        add_filter( 'query_vars', function( $query_vars ) {
            $query_vars[] = 'tradesafe';
            $query_vars[] = 'order-id';

            return $query_vars;
        } );
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
            'tradesafe_setting_section',
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
            'tradesafe_setting_section'
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
            'tradesafe_setting_section'
        );
        register_setting('tradesafe', 'tradesafe_client_secret');

        add_settings_field(
            'tradesafe_production_mode',
            'Production Mode',
            [
                'TradeSafe',
                'setting_production_mode_callback'
            ],
            'tradesafe',
            'tradesafe_setting_section'
        );
        register_setting('tradesafe', 'tradesafe_production_mode');
    }

    function settings_info_callback()
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

    function settings_application_callback()
    {
        //
    }

    function setting_client_id_callback()
    {
        echo '<input name="tradesafe_client_id" id="tradesafe_client_id" type="text" value="' . get_option('tradesafe_client_id') . '" class="regular-text ltr" />';
    }

    function setting_client_secret_callback()
    {
        echo '<input name="tradesafe_client_secret" id="tradesafe_client_secret" type="password" value="' . get_option('tradesafe_client_secret') . '" class="regular-text ltr" />';
    }

    function setting_production_mode_callback()
    {
        echo '<input name="tradesafe_production_mode" id="tradesafe_production_mode" type="checkbox" value="1" ' . checked(1, get_option('tradesafe_production_mode'), false) . ' />';
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
                default:
                    status_header( 404 );
                    include get_query_template( '404' );
                    exit;
            }
        }
    }

    public static function eft_details_page($order_id)
    {
        $order = new WC_Order( $order_id );

        print_r($order);

        print "???";
    }
}
