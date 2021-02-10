<?php
/**
 * Plugin Name: WooCommerce TradeSafe Gateway
 * Plugin URI: https://developer.tradesafe.co.za
 * Description: Process payments using the TradeSafe as a payments provider.
 * Author: TradeSafe
 * Author URI: https://www.tradesafe.co.za/
 * Version: 1.0.0
 * Requires at least: 4.4
 * Tested up to: 5.4
 * WC tested up to: 4.0
 * WC requires at least: 2.6
 *
 */
defined('ABSPATH') || exit;

/**
 * Initialize the gateway.
 * @since 1.0.0
 */
function woocommerce_tradesafe_init()
{
    if (!class_exists('WC_Payment_Gateway')) {
        return;
    }

    define('WC_GATEWAY_TRADESAFE_VERSION', '1.0.0');

    $autoloader = dirname(__DIR__) . DIRECTORY_SEPARATOR . plugin_basename(__DIR__) . '/vendor/autoload.php';

    if (!is_readable($autoloader)) {
        return;
    }

    $autoloader_result = require $autoloader;
    if (!$autoloader_result) {
        return;
    }

    require_once(plugin_basename('src/TradeSafe.php'));
    require_once(plugin_basename('src/TradeSafeProfile.php'));
    require_once(plugin_basename('src/WC_Gateway_TradeSafe_Base.php'));
    require_once(plugin_basename('src/WC_Gateway_TradeSafe_Manual.php'));
    require_once(plugin_basename('src/WC_Gateway_TradeSafe_Ozow.php'));
    require_once(plugin_basename('src/WC_Gateway_TradeSafe_Ecentric.php'));
    require_once(plugin_basename('src/WC_Gateway_TradeSafe_Snapscan.php'));

    load_plugin_textdomain('woocommerce-gateway-tradesafe', false, trailingslashit(dirname(plugin_basename(__FILE__))));
    add_filter('woocommerce_payment_gateways', 'woocommerce_tradesafe_add_gateway');
}

add_action('plugins_loaded', 'woocommerce_tradesafe_init', 0);
add_action('init', ['TradeSafe', 'init']);
add_action('init', ['TradeSafeProfile', 'init']);
add_action('init', ['TradeSafeProfile', 'add_endpoints']);

function woocommerce_tradesafe_plugin_links($links)
{
    $settings_url = add_query_arg(
        array(
            'page' => 'tradesafe'
        ),
        admin_url('admin.php')
    );

    $plugin_links = array(
        '<a href="' . esc_url($settings_url) . '">' . __('Settings', 'woocommerce-gateway-tradesafe') . '</a>',
        '<a href="https://www.tradesafe.co.za/support/">' . __('Support', 'woocommerce-gateway-tradesafe') . '</a>',
        '<a href="https://developer.tradesafe.co.za/docs...?">' . __('Docs', 'woocommerce-gateway-tradesafe') . '</a>',
    );

    return array_merge($plugin_links, $links);
}

add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'woocommerce_tradesafe_plugin_links');


/**
 * Add the gateway to WooCommerce
 * @since 1.0.0
 */
function woocommerce_tradesafe_add_gateway($methods)
{
    $methods[] = 'WC_Gateway_TradeSafe_Manual';
    $methods[] = 'WC_Gateway_TradeSafe_Ozow';
    $methods[] = 'WC_Gateway_TradeSafe_Ecentric';
//    $methods[] = 'WC_Gateway_TradeSafe_Snapscan';
    return $methods;
}

function woocommerce_tradesafe_api()
{
    $domain = 'api-developer.tradesafe.dev';

    if (get_option('tradesafe_production_mode') && true == false) {
        $domain = 'api.tradesafe.co.za';
    }

    $client = new \TradeSafe\Api\Client($domain);

    try {
        $client->configure(get_option('tradesafe_client_id'), get_option('tradesafe_client_secret'), site_url('/tradesafe/oauth/callback/'));

        if (get_transient('tradesafe_client_token')) {
            $client->setAuthToken(get_transient('tradesafe_client_token'));
        } else {
            $accessToken = $client->generateAuthToken();

            if (is_array($accessToken)) {
                // Get number of seconds token is valid
                $expires = $accessToken['expires'] - time() - 30;
                set_transient('tradesafe_client_token', $accessToken['token'], $expires);
            }
        }
    } catch (Exception $e) {
        error_log('TradeSafe: ERROR: ' . $e->getMessage());
    }

    return $client;
}
