<?php
/**
 * Plugin Name: TradeSafe Payment Gateway
 * Plugin URI: https://developer.tradesafe.co.za/docs/1.2/plugins/woocommerce
 * Description: Process payments using the TradeSafe as a payments provider.
 * Version: 2.0.0
 * Author: TradeSafe Escrow
 * Author URI: https://www.tradesafe.co.za
 * Text Domain: tradesafe-payment-gateway
 * Requires at least: 5.5
 * Requires PHP: 7.3
 * Tested up to: 5.8
 * WC tested up to: 5.6
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

	define( 'WC_GATEWAY_TRADESAFE_VERSION', '1.2.5' );
	define( 'TRADESAFE_PAYMENT_GATEWAY_BASE_DIR', untrailingslashit( plugin_dir_url( __FILE__ ) ) );

	$autoloader = dirname( __DIR__ ) . DIRECTORY_SEPARATOR . plugin_basename( __DIR__ ) . '/vendor/autoload.php';

	if ( ! is_readable( $autoloader ) ) {
		return;
	}

	$autoloader_result = require $autoloader;
	if ( ! $autoloader_result ) {
		return;
	}

	$settings = get_option( 'woocommerce_tradesafe_settings' );

	if ( ! isset( $settings['client_id'] ) || ( '' === $settings['client_id'] && '' !== get_option( 'tradesafe_client_id', '' ) ) ) {
		$settings['client_id']     = get_option( 'tradesafe_client_id' );
		$settings['client_secret'] = get_option( 'tradesafe_client_secret' );
		$settings['industry']      = get_option( 'tradesafe_transaction_industry' );

		$settings['environment'] = get_option( 'tradesafe_production_mode', 'SANDBOX' ) ? 'PROD' : 'SANDBOX';

		$settings['is_marketplace'] = get_option( 'tradesafe_transaction_marketplace', null ) ? 'yes' : 'no';

		$settings['processing_fee']  = get_option( 'tradesafe_transaction_fee_allocation' );
		$settings['commission']      = get_option( 'tradesafe_transaction_fee', null );
		$settings['commission_type'] = get_option( 'tradesafe_transaction_fee_type', null );

		$settings['buyers_accept'] = get_option( 'tradesafe_accept_transaction', null ) ? 'yes' : 'no';

		update_option( 'woocommerce_tradesafe_settings', apply_filters( 'woocommerce_settings_api_sanitized_fields_tradesafe', $settings ), 'yes' );
	}

	require_once plugin_basename( 'src/class-tradesafe.php' );
	require_once plugin_basename( 'src/class-tradesafeprofile.php' );
	require_once plugin_basename( 'src/class-wc-gateway-tradesafe.php' );
	require_once plugin_basename( 'helpers/class-tradesafeapiclient.php' );

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
			'page'    => 'wc-settings',
			'tab'     => 'checkout',
			'section' => 'tradesafe',
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
 * Return true if Dokan class exists.
 *
 * @return bool
 */
function tradesafe_has_dokan(): bool {
	return class_exists( 'WeDevs_Dokan' );
}
