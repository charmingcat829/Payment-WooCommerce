<?php
/**
 * Plugin Name: TradeSafe Payment Gateway
 * Plugin URI: https://developer.tradesafe.co.za/docs/1.1/plugins/woocommerce
 * Description: Process payments using the TradeSafe as a payments provider.
 * Version: 1.2.4
 * Author: TradeSafe Escrow
 * Author URI: https://www.tradesafe.co.za
 * Text Domain: tradesafe-payment-gateway
 * Requires at least: 5.5
 * Requires PHP: 7.4
 * Tested up to: 5.7
 * WC tested up to: 5.4
 * WC requires at least: 4.6
 *
 * @package TradeSafe Payment Gateway
 */

defined( 'ABSPATH' ) || exit;

/**
 * Initialize the gateway.
 *
 * @since 1.0.0
 */
function woocommerce_tradesafe_init() {
	if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
		return;
	}

	define( 'WC_GATEWAY_TRADESAFE_VERSION', '1.2.4' );

	$autoloader = dirname( __DIR__ ) . DIRECTORY_SEPARATOR . plugin_basename( __DIR__ ) . '/vendor/autoload.php';

	if ( ! is_readable( $autoloader ) ) {
		return;
	}

	$autoloader_result = require $autoloader;
	if ( ! $autoloader_result ) {
		return;
	}

	require_once plugin_basename( 'src/class-tradesafe.php' );
	require_once plugin_basename( 'src/class-tradesafeprofile.php' );
	require_once plugin_basename( 'src/class-wc-gateway-tradesafe.php' );

	load_plugin_textdomain( 'tradesafe-payment-gateway', false, trailingslashit( dirname( plugin_basename( __FILE__ ) ) ) );
	add_filter( 'woocommerce_payment_gateways', 'woocommerce_tradesafe_add_gateway' );
}

add_action( 'plugins_loaded', 'woocommerce_tradesafe_init', 0 );
add_action( 'init', array( 'TradeSafe', 'init' ) );
add_action( 'init', array( 'TradeSafeProfile', 'init' ) );

/**
 * Add action links to the entry on the plugin page.
 *
 * @param array $links Array of action links.
 * @return array
 */
function woocommerce_tradesafe_plugin_links( $links ): array {
	$settings_url = add_query_arg(
		array(
			'page' => 'tradesafe',
		),
		admin_url( 'admin.php' )
	);

	$plugin_links = array(
		'<a href="' . esc_url( $settings_url ) . '">' . __( 'Settings', 'tradesafe-payment-gateway' ) . '</a>',
	);

	return array_merge( $plugin_links, $links );
}

add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'woocommerce_tradesafe_plugin_links' );

/**
 * Add additional links under the plugin description.
 *
 * @param array  $links An array of existing links.
 * @param string $file Name of the plugin file been loaded.
 * @return array
 */
function tradesafe_payment_gateway_plugin_row_meta( array $links, string $file ): array {
	if ( strpos( $file, 'tradesafe-payment-gateway.php' ) !== false ) {
		$new_links = array(
			'<a href="https://developer.tradesafe.co.za/docs/1.1/plugins/woocommerce">' . __( 'Docs', 'tradesafe-payment-gateway' ) . '</a>',
			'<a href="https://www.tradesafe.co.za/support/">' . __( 'Support', 'tradesafe-payment-gateway' ) . '</a>',
		);

		$links = array_merge( $links, $new_links );
	}

	return $links;
}

add_filter( 'plugin_row_meta', 'tradesafe_payment_gateway_plugin_row_meta', 10, 2 );


/**
 * Add the gateway to WooCommerce.
 *
 * @param array $methods Array of payment gateway methods.
 * @since 1.0.0
 */
function woocommerce_tradesafe_add_gateway( $methods ) {
	$methods[] = 'WC_Gateway_TradeSafe';
	return $methods;
}

/**
 * Initiate the TradeSafe API Client.
 *
 * @return \TradeSafe\Api\Client
 */
function tradesafe_api_client() {
	require 'config.php';

	$domain = $api_domains['sit'];

	if ( get_option( 'tradesafe_production_mode' ) ) {
		$domain = $api_domains['prod'];
	}

	$client = new \TradeSafe\Api\Client( $domain, $auth_domain );

	try {
		$client->configure( get_option( 'tradesafe_client_id' ), get_option( 'tradesafe_client_secret' ), site_url( '/tradesafe/oauth/callback/' ) );

		if ( get_transient( 'tradesafe_client_token' ) ) {
			$client->setAuthToken( get_transient( 'tradesafe_client_token' ) );
		} else {
			$access_token = $client->generateAuthToken();

			if ( is_array( $access_token ) ) {
				// Get number of seconds token is valid.
				$expires = $access_token['expires'] - time() - 30;
				set_transient( 'tradesafe_client_token', $access_token['token'], $expires );
			}
		}
	} catch ( Exception $e ) {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG === true ) {
		    // phpcs:disable WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( 'TradeSafe Error: ' . $e->getMessage() );
            // phpcs:enable WordPress.PHP.DevelopmentFunctions.error_log_error_log
		}

		return array(
			'error' => 'TradeSafe Error: ' . $e->getMessage(),
		);
	}

	return $client;
}

/**
 * Return true if Dokan class exists.
 *
 * @return bool
 */
function tradesafe_has_dokan(): bool {
	return class_exists( 'WeDevs_Dokan' );
}
