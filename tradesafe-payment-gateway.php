<?php
/**
 * Plugin Name: TradeSafe Payment Gateway
 * Plugin URI: https://developer.tradesafe.co.za/docs/1.2/plugins/woocommerce
 * Description: Process secure payments with TradeSafe. Customers can make payment either through Card, EFT, Instant EFT, Buy Now Pay Later, or QR code enabled devices. Give your store instant credibility. TradeSafe is backed by Standard Bank.
 * Version: 2.7.0
 * Author: TradeSafe Escrow
 * Author URI: https://www.tradesafe.co.za
 * Text Domain: tradesafe-payment-gateway
 * Requires at least: 5.5
 * Requires PHP: 7.4
 * Tested up to: 6.1
 * WC tested up to: 7.1
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

	define( 'WC_GATEWAY_TRADESAFE_VERSION', '2.7.0' );
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

		update_option( 'woocommerce_tradesafe_settings', apply_filters( 'woocommerce_settings_api_sanitized_fields_tradesafe', $settings ), 'yes' );
	}

	require_once plugin_basename( 'src/class-tradesafe.php' );
	require_once plugin_basename( 'src/class-tradesafedokan.php' );
	require_once plugin_basename( 'src/class-tradesafeprofile.php' );
	require_once plugin_basename( 'src/class-wc-gateway-tradesafe.php' );
	require_once plugin_basename( 'helpers/class-tradesafeapiclient.php' );

	add_action( 'init', array( 'TradeSafe', 'init' ) );
	add_action( 'init', array( 'TradeSafeProfile', 'init' ) );
	add_action( 'init', array( 'TradeSafeDokan', 'init' ) );

	load_plugin_textdomain( 'tradesafe-payment-gateway', false, trailingslashit( dirname( plugin_basename( __FILE__ ) ) ) );
	add_filter( 'woocommerce_payment_gateways', 'woocommerce_tradesafe_add_gateway' );
}

add_action( 'plugins_loaded', 'woocommerce_tradesafe_init', 0 );

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
			'<a href="https://developer.tradesafe.co.za/docs/1.2/plugins/woocommerce">' . __( 'Docs', 'tradesafe-payment-gateway' ) . '</a>',
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
 * Check if production environment is enabled.
 *
 * @return bool
 */
function tradesafe_is_prod(): bool {
	$settings = get_option( 'woocommerce_tradesafe_settings', array() );

	if ( isset( $settings['environment'] ) ) {
		return 'PROD' === $settings['environment'];
	}

	return get_option( 'tradesafe_production_mode', false );
}

/**
 * Check if production environment is enabled.
 *
 * @return bool
 */
function tradesafe_is_marketplace(): bool {
	$settings = get_option( 'woocommerce_tradesafe_settings', array() );

	if ( tradesafe_has_dokan() ) {
		return true;
	}

	if ( isset( $settings['is_marketplace'] ) ) {
		return 'yes' === $settings['is_marketplace'];
	}

	return get_option( 'tradesafe_transaction_marketplace', false );
}

/**
 * Return true if Dokan class exists.
 *
 * @return bool
 */
function tradesafe_has_dokan(): bool {
	return class_exists( 'WeDevs_Dokan' );
}

/**
 * Who pays the escrow fee.
 *
 * @return string
 */
function tradesafe_fee_allocation(): string {
	$settings = get_option( 'woocommerce_tradesafe_settings', array() );

	$fee_allocation = get_option( 'processing_fee', 'SELLER' );

	if ( isset( $settings['processing_fee'] ) ) {
		$fee_allocation = $settings['processing_fee'];
	}

	// Check that valid value is set
	switch ( $fee_allocation ) {
		case 'BUYER':
		case 'SELLER':
		case 'BUYER_SELLER':
			return $fee_allocation;
		default:
			return 'SELLER';
	}
}

/**
 * Which industry does the store operate.
 *
 * @return string
 */
function tradesafe_industry(): string {
	$settings = get_option( 'woocommerce_tradesafe_settings', array() );

	if ( isset( $settings['industry'] ) ) {
		return $settings['industry'];
	}

	return get_option( 'tradesafe_transaction_industry' );
}

/**
 * What type of fee is charged.
 *
 * @return string
 */
function tradesafe_commission_type(): string {
	$settings = get_option( 'woocommerce_tradesafe_settings', array() );

	if ( isset( $settings['commission_type'] ) ) {
		return $settings['commission_type'];
	}

	return get_option( 'tradesafe_transaction_fee_type' );
}

/**
 * What is the value of the fee charged.
 *
 * @return string
 */
function tradesafe_commission_value(): string {
	$settings = get_option( 'woocommerce_tradesafe_settings', array() );

	if ( isset( $settings['commission'] ) ) {
		return $settings['commission'];
	}

	return get_option( 'tradesafe_transaction_fee' );
}

/**
 * Set meta key based on environment.
 *
 * @return string
 */
function tradesafe_token_meta_key(): string {
	$meta_key = 'tradesafe_token_id';

	if ( tradesafe_is_prod() ) {
		$meta_key = 'tradesafe_prod_token_id';
	}

	return $meta_key;
}

/**
 * Get or create token ID for user.
 *
 * @param int $user_id
 * @return string
 * @throws Exception
 */
function tradesafe_get_token_id( int $user_id ): ?string {
	$token = get_user_meta( $user_id, tradesafe_token_meta_key(), true );

	if ( ! empty( $token ) ) {
		return $token;
	}

	// If Token was not found for user create one and return the id
	$client   = new \TradeSafe\Helpers\TradeSafeApiClient();
	$settings = get_option( 'woocommerce_tradesafe_settings', array() );

	$customer = new WC_Customer( $user_id );

	$payout_interval = 'IMMEDIATE';

	if ( isset( $settings['payout_method'] ) ) {
		$payout_interval = $settings['payout_method'];
	}

	$user = array(
		'givenName'  => $customer->get_first_name() ?? null,
		'familyName' => $customer->get_last_name() ?? null,
		'email'      => $customer->get_email(),
		'mobile'     => $customer->get_billing_phone() ?? null,
	);

	try {
		$token_data = $client->createToken(
			$user,
			null,
			null,
			$payout_interval
		);

		$customer->update_meta_data( tradesafe_token_meta_key(), sanitize_text_field( $token_data['id'] ) );
		$customer->save_meta_data();

		return sanitize_text_field( $token_data['id'] );
	} catch ( \GraphQL\Exception\QueryError $e ) {
		$logger = wc_get_logger();
		$logger->error( $e->getMessage() . ': ' . $e->getErrorDetails()['message'] ?? null, array( 'source' => 'tradesafe-payment-gateway' ) );
	}

	return null;
}

/**
 * Get or create token ID for user.
 *
 * @param int $user_id
 * @return string
 */
function tradesafe_get_token( int $user_id ): ?array {
	try {
		$tokenId = get_user_meta( $user_id, tradesafe_token_meta_key(), true );

		// If Token was not found for user create one and return the id
		$client = new \TradeSafe\Helpers\TradeSafeApiClient();

		return $client->getToken( $tokenId );
	} catch ( \GraphQL\Exception\QueryError $e ) {
		$logger = wc_get_logger();
		$logger->error( $e->getMessage() . ': ' . $e->getErrorDetails()['message'] ?? null, array( 'source' => 'tradesafe-payment-gateway' ) );
	}

	return null;
}
