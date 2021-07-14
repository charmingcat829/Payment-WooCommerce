<?php
/**
 * Initialises the plugin and implements the admin settings page and callback urls.
 *
 * @package TradeSafe Payment Gateway
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class TradeSafe.
 */
class TradeSafe {

	/**
	 * Initialize the plugin and load the actions and filters.
	 */
	public static function init() {
		// Actions.
		add_action( 'admin_init', array( 'TradeSafe', 'settings_api_init' ) );
		add_action( 'admin_menu', array( 'TradeSafe', 'register_options_page' ) );

		add_action( 'woocommerce_cart_calculate_fees', array( 'TradeSafe', 'add_gateway_fee' ), PHP_INT_MAX );
		add_action( 'woocommerce_order_status_completed', array( 'TradeSafe', 'complete_transaction' ), PHP_INT_MAX );
		add_action( 'woocommerce_review_order_before_payment', array( 'TradeSafe', 'refresh_checkout' ) );
		add_action( 'admin_notices', array( 'TradeSafe', 'seller_account_incomplete_notice' ), -10000, 0 );
		add_action( 'dokan_dashboard_content_inside_before', array( 'TradeSafe', 'seller_account_incomplete_notice' ) );

		// Disable publish for standard woocommerce products.
		add_action( 'admin_head', array( 'TradeSafe', 'disable_publish_button' ) );

		if ( tradesafe_has_dokan() ) {
			// Disable add new product button when using dokan.
			add_action( 'wp_head', array( 'TradeSafe', 'disable_add_product_button' ) );
		}

		add_filter( 'pre_update_option_dokan_selling', array( 'TradeSafe', 'override_dokan_selling' ) );

		add_filter( 'woocommerce_my_account_my_orders_actions', array( 'TradeSafe', 'accept_order' ), 10, 2 );
		add_filter( 'woocommerce_available_payment_gateways', array( 'TradeSafe', 'availability' ), 10, 2 );

		add_filter( 'woocommerce_checkout_fields', array( 'TradeSafe', 'checkout_field_defaults' ), 20 );

		add_rewrite_rule( '^tradesafe/eft-details/([0-9]+)[/]?$', 'index.php?tradesafe=eft-details&order-id=$matches[1]', 'top' );
		add_rewrite_rule( '^tradesafe/accept/([0-9]+)[/]?$', 'index.php?tradesafe=accept&order-id=$matches[1]', 'top' );
		add_rewrite_rule( '^tradesafe/callback$', 'index.php?tradesafe=callback', 'top' );
		add_rewrite_rule( '^tradesafe/unlink?$', 'index.php?tradesafe=unlink', 'top' );
		add_action( 'parse_request', array( 'TradeSafe', 'parse_request' ) );

		add_rewrite_endpoint( 'tradesafe', EP_PAGES );

		add_filter(
			'query_vars',
			function ( $query_vars ) {
				$query_vars[] = 'tradesafe';
				$query_vars[] = 'order-id';

				return $query_vars;
			}
		);
	}

	/**
	 * Settings Page.
	 */
	public static function settings_api_init() {
		add_settings_section(
			'tradesafe_info_section',
			'Callback URL\'s',
			array(
				'TradeSafe',
				'settings_info_callback',
			),
			'tradesafe'
		);

		add_settings_section(
			'tradesafe_settings_section',
			'Application Settings',
			array(
				'TradeSafe',
				'settings_application_callback',
			),
			'tradesafe'
		);

		add_settings_field(
			'tradesafe_client_id',
			'Client ID',
			array(
				'TradeSafe',
				'setting_client_id_callback',
			),
			'tradesafe',
			'tradesafe_settings_section'
		);
		register_setting( 'tradesafe', 'tradesafe_client_id' );

		add_settings_field(
			'tradesafe_client_secret',
			'Client Secret',
			array(
				'TradeSafe',
				'setting_client_secret_callback',
			),
			'tradesafe',
			'tradesafe_settings_section'
		);
		register_setting( 'tradesafe', 'tradesafe_client_secret' );

		add_settings_field(
			'tradesafe_production_mode',
			'Production Mode',
			array(
				'TradeSafe',
				'setting_production_mode_callback',
			),
			'tradesafe',
			'tradesafe_settings_section'
		);
		register_setting( 'tradesafe', 'tradesafe_production_mode' );

		add_settings_section(
			'tradesafe_transaction_section',
			'Transaction Settings',
			array(
				'TradeSafe',
				'settings_transaction_callback',
			),
			'tradesafe'
		);

		add_settings_field(
			'tradesafe_transaction_industry',
			'Industry',
			array(
				'TradeSafe',
				'setting_transaction_industry_callback',
			),
			'tradesafe',
			'tradesafe_transaction_section'
		);
		register_setting( 'tradesafe', 'tradesafe_transaction_industry' );

		add_settings_field(
			'tradesafe_fee_allocation',
			'Who absorbs TradeSafe’s fee (this is 0.75% of the transaction value)?',
			array(
				'TradeSafe',
				'setting_tradesafe_fee_allocation_callback',
			),
			'tradesafe',
			'tradesafe_transaction_section'
		);
		register_setting( 'tradesafe', 'tradesafe_fee_allocation' );

		add_settings_field(
			'tradesafe_gateway_fee_allocation',
			'Who absorbs the remaining payment gateway fee?',
			array(
				'TradeSafe',
				'setting_tradesafe_gateway_fee_allocation_callback',
			),
			'tradesafe',
			'tradesafe_transaction_section'
		);
		register_setting( 'tradesafe', 'tradesafe_gateway_fee_allocation' );

		add_settings_field(
			'tradesafe_accept_transaction',
			'Allow buyers to accept goods to release funds',
			array(
				'TradeSafe',
				'setting_tradesafe_accept_transaction_callback',
			),
			'tradesafe',
			'tradesafe_transaction_section'
		);
		register_setting( 'tradesafe', 'tradesafe_accept_transaction' );

		if ( tradesafe_has_dokan() ) {
			add_settings_field(
				'tradesafe_payout_fee',
				'Who absorbs the pay-out fee (R10 for every additional vendor)?',
				array(
					'TradeSafe',
					'setting_payout_fee_dokan_callback',
				),
				'tradesafe',
				'tradesafe_transaction_section'
			);
			add_settings_field(
				'tradesafe_transaction_fee',
				'Marketplace Commission Fee',
				array(
					'TradeSafe',
					'setting_transaction_fee_dokan_callback',
				),
				'tradesafe',
				'tradesafe_transaction_section'
			);

			add_settings_field(
				'tradesafe_transaction_fee_type',
				'Marketplace Commission Type',
				array(
					'TradeSafe',
					'setting_transaction_fee_type_dokan_callback',
				),
				'tradesafe',
				'tradesafe_transaction_section'
			);

			add_settings_field(
				'tradesafe_transaction_fee_allocation',
				'Marketplace Commission Fee Allocation',
				array(
					'TradeSafe',
					'setting_transaction_fee_allocation_dokan_callback',
				),
				'tradesafe',
				'tradesafe_transaction_section'
			);
		} else {
			add_settings_field(
				'tradesafe_transaction_marketplace',
				'Is this website a Marketplace?',
				array(
					'TradeSafe',
					'setting_transaction_agent_callback',
				),
				'tradesafe',
				'tradesafe_transaction_section'
			);
			register_setting( 'tradesafe', 'tradesafe_transaction_marketplace' );

			add_settings_field(
				'tradesafe_transaction_fee',
				'Marketplace Commission Fee',
				array(
					'TradeSafe',
					'setting_transaction_fee_callback',
				),
				'tradesafe',
				'tradesafe_transaction_section'
			);

			add_settings_field(
				'tradesafe_transaction_fee_type',
				'Marketplace Commission Type',
				array(
					'TradeSafe',
					'setting_transaction_fee_type_callback',
				),
				'tradesafe',
				'tradesafe_transaction_section'
			);

			add_settings_field(
				'tradesafe_transaction_fee_allocation',
				'Marketplace Commission Fee Allocation',
				array(
					'TradeSafe',
					'setting_transaction_fee_allocation_callback',
				),
				'tradesafe',
				'tradesafe_transaction_section'
			);
		}

		register_setting( 'tradesafe', 'tradesafe_transaction_fee' );
		register_setting( 'tradesafe', 'tradesafe_transaction_fee_type' );
		register_setting( 'tradesafe', 'tradesafe_transaction_fee_allocation' );
	}

	/**
	 * Set empty value to zero.
	 *
	 * @param mixed $value Option value.
	 * @return int|mixed
	 */
	public static function sanitize_boolean( $value ) {
		if ( empty( $value ) ) {
			$value = 0;
		}

		return $value;
	}

	/**
	 * Check if a token is correcctly configured based on the users role.
	 *
	 * @param string $role the role to check.
	 * @return bool
	 */
	private static function is_valid_token( string $role ): bool {
		$client   = tradesafe_api_client();
		$user     = wp_get_current_user();
		$meta_key = 'tradesafe_token_id';
		$valid    = false;

		if ( is_null( $client ) || is_array( $client ) ) {
			return false;
		}

		if ( get_option( 'tradesafe_production_mode' ) ) {
			$meta_key = 'tradesafe_prod_token_id';
		}

		$token_id = get_user_meta( $user->ID, $meta_key, true );

		if ( $token_id ) {
			try {
				$token_data = $client->getToken( $token_id );

				switch ( $role ) {
					case 'seller':
						if ( isset( $token_data['bankAccount']['accountNumber'] ) && '' !== $token_data['bankAccount']['accountNumber'] ) {
							$valid = true;
						}
						break;
					case 'buyer':
						if ( isset( $token_data['user']['idNumber'] ) && '' !== $token_data['user']['idNumber'] ) {
							$valid = true;
						}
						break;
				}
			} catch ( \Exception $e ) {
				$valid = false;
			}
		}

		return $valid;
	}

	/**
	 * Generate the required urls needed for application registration.
	 */
	public static function settings_info_callback() {
		$urls = array(
			'oauth_callback' => site_url( '/tradesafe/oauth/callback/' ),
			'callback'       => site_url( '/tradesafe/callback/' ),
			'success'        => wc_get_endpoint_url( 'orders', '', get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) ),
			'failure'        => wc_get_endpoint_url( 'orders', '', get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) ),
		);

		echo '<p>The following URL\'s can be used to register your application with TradeSafe.</p>';
		echo '<table class="form-table">
        <tbody>
        <tr>
            <th scope="row">OAuth Callback URL</th>
            <td>' . esc_attr( $urls['oauth_callback'] ) . '</td>
        </tr>
        <tr>
            <th scope="row">API Callback URL</th>
            <td>' . esc_attr( $urls['callback'] ) . '</td>
        </tr>
        <tr>
            <th scope="row">Success URL</th>
            <td>' . esc_attr( $urls['success'] ) . '</td>
        </tr>
        <tr>
            <th scope="row">Failure URL</th>
            <td>' . esc_attr( $urls['failure'] ) . '</td>
        </tr>
        </tbody>
    </table>';

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG === true ) {
			require plugin_dir_path( __FILE__ ) . '../config.php';

			echo '<h2>Plugin Status</h2>';
			echo '<table class="form-table"><tbody>';

			// PHP Version.
			echo '<tr><th>PHP Version</th><td>' . esc_attr( phpversion() ) . '</td></tr>';
			echo '<tr><th>WordPress Version</th><td>' . esc_attr( get_bloginfo( 'version' ) ) . '</td></tr>';
			echo '<tr><th>Woocommerce Version</th><td>' . esc_attr( WC_VERSION ) . '</td></tr>';
			echo '<tr><th>Plugin Version</th><td>' . esc_attr( WC_GATEWAY_TRADESAFE_VERSION ) . '</td></tr>';

			$domain = $api_domains['sit'];

			if ( get_option( 'tradesafe_production_mode' ) ) {
				$domain = $api_domains['prod'];
			}

			$api_status        = false;
			$api_status_reason = null;

			$auth_status        = false;
			$auth_status_reason = null;

			try {
				$client = new \GuzzleHttp\Client(
					array(
						'base_uri' => sprintf( 'https://%s/', $domain ),
						'timeout'  => 2.0,
					)
				);

				$response = $client->request( 'GET', 'api/ping' );

				if ( $response->getStatusCode() === 200 && $response->getBody()->getContents() === 'pong' ) {
					$api_status = true;
				} else {
					$auth_status_reason = sprintf( '[%s]: %s', $response->getStatusCode(), $response->getBody()->getContents() );
				}
			} catch ( \Exception $e ) {
				$api_status_reason = $e->getMessage();
			}

			echo '<tr><th>API Domain</th><td>' . esc_attr( $domain ) . ' [' . ( $api_status ? 'OK' : 'ERROR' ) . ']</td></tr>';

			if ( $api_status_reason ) {
				echo '<tr><th>API Error</th><td>' . esc_attr( $api_status_reason ) . '</td></tr>';
			}

			try {
				$client = new \GuzzleHttp\Client(
					array(
						'base_uri' => sprintf( 'https://%s/', $auth_domain ),
						'timeout'  => 2.0,
					)
				);

				$response = $client->request( 'GET', 'ping' );

				if ( $response->getStatusCode() === 200 && $response->getBody()->getContents() === 'pong' ) {
					$auth_status = true;
				} else {
					$auth_status_reason = sprintf( '[%s]: %s', $response->getStatusCode(), $response->getBody()->getContents() );
				}
			} catch ( \Exception $e ) {
				$auth_status_reason = $e->getMessage();
			}

			echo '<tr><th>Authentication Domain</th><td>' . esc_attr( $auth_domain ) . ' [' . ( $auth_status ? 'OK' : 'ERROR' ) . ']</td></tr>';

			if ( $auth_status_reason ) {
				echo '<tr><th>Authentication Error</th><td>' . esc_attr( $auth_status_reason ) . '</td></tr>';
			}

			echo '</tbody></table>';
		}
	}

	/**
	 * Display the status of connection to the API.
	 *
	 * If a request is successful display the admins details. Otherwise display an error message.
	 */
	public static function settings_application_callback() {
		if ( get_option( 'tradesafe_client_id' ) && get_option( 'tradesafe_client_secret' ) ) {
			$client = tradesafe_api_client();

			if ( is_array( $client ) && isset( $client['error'] ) ) {
				echo "<table class='form-table' role='presentation'><tbody>";
				echo "<tr><th scope='row'>Error:</th><td> Could not connect to server</td></tr>";
				echo "<tr><th scope='row'>Reason:</th><td> " . esc_attr( $client['error'] ) . '</td></tr>';
				echo '</tbody></table>';
				return;
			} elseif ( is_null( $client ) ) {
				echo "<table class='form-table' role='presentation'><tbody>";
				echo "<tr><th scope='row'>Error:</th><td> Could not connect to server?</td></tr>";
				echo '</tbody></table>';
				return;
			}

			try {
				$profile    = $client->getProfile();
				$token_data = $client->getToken( $profile['token'] );

				echo "<table class='form-table' role='presentation'><tbody>";
				echo "<tr><th scope='row'>Organization Name:</th><td>" . esc_attr( $token_data['organization']['name'] ) . '</td></tr>';
				echo "<tr><th scope='row'>Registration Number:</th><td>" . esc_attr( $token_data['organization']['registration'] ) . '</td></tr>';
				if ( $token_data['organization']['taxNumber'] ) {
					echo "<tr><th scope='row'>Tax Number:</th><td>" . esc_attr( $token_data['organization']['taxNumber'] ) . '</td></tr>';
				}
				echo "<tr><th scope='row'>Name:</th><td>" . esc_attr( $token_data['user']['givenName'] ) . ' ' . esc_attr( $token_data['user']['familyName'] ) . '</td></tr>';
				echo "<tr><th scope='row'>Email:</th><td>" . esc_attr( $token_data['user']['email'] ) . '</td></tr>';
				echo "<tr><th scope='row'>Mobile:</th><td>" . esc_attr( $token_data['user']['mobile'] ) . '</td></tr>';
				echo '</tbody></table>';
			} catch ( \GuzzleHttp\Exception\ClientException $e ) {
				echo "<table class='form-table' role='presentation'><tbody>";
				echo "<tr><th scope='row'>Error:</th><td> Could not connect to server</td></tr>";
				echo "<tr><th scope='row'>Code:</th><td> " . esc_attr( $e->getCode() ) . '</td></tr>';
				echo '</tbody></table>';
				return;
			} catch ( Exception $e ) {
				echo "<table class='form-table' role='presentation'><tbody>";
				echo "<tr><th scope='row'>Error:</th><td> " . esc_attr( $e->getMessage() ) . '</td></tr>';
				echo '</tbody></table>';
				return;
			}
		}
	}

	/**
	 * Transaction settings section.
	 */
	public static function settings_transaction_callback() {
	}

	/**
	 * Client ID field.
	 */
	public static function setting_client_id_callback() {
		echo '<input name="tradesafe_client_id" id="tradesafe_client_id" type="text" value="' . esc_attr( get_option( 'tradesafe_client_id' ) ) . '" class="regular-text ltr" />';
	}

	/**
	 * Client secret field.
	 */
	public static function setting_client_secret_callback() {
		echo '<input name="tradesafe_client_secret" id="tradesafe_client_secret" type="password" value="' . esc_attr( get_option( 'tradesafe_client_secret' ) ) . '" class="regular-text ltr" />';
	}

	/**
	 * Production mode toggle.
	 */
	public static function setting_production_mode_callback() {
		echo '<input name="tradesafe_production_mode" id="tradesafe_production_mode" type="checkbox" value="1" ' . checked( 1, esc_attr( get_option( 'tradesafe_production_mode', 0 ) ), false ) . ' />';
		echo '<p class="description" id="tradesafe_production_mode_description">Use the production API. <strong>Do not enable this option until you have completed testing and have requested your application to be approved.</strong></p>';
	}

	/**
	 * Default industry for transactions.
	 */
	public static function setting_transaction_industry_callback() {
		$client = tradesafe_api_client();

		$industries = array(
			array(
				'name'        => 'GENERAL_GOODS_SERVICES',
				'description' => 'General Goods & Services',
			),
		);

		if ( ! is_null( $client ) && is_object( $client ) ) {
			$industries = $client->getEnums( 'Industry' );
		}

		echo '<select name="tradesafe_transaction_industry" class="small-text ltr">';

		foreach ( $industries as $industry ) {
			echo '<option ' . ( esc_attr( get_option( 'tradesafe_transaction_industry', 'GENERAL_GOODS_SERVICES' ) ) === $industry['name'] ? 'selected' : '' ) . ' value="' . esc_attr( $industry['name'] ) . '">' . esc_attr( $industry['description'] ) . '</option>';
		}

		echo '</select>';
	}

	/**
	 * Set which role should cover TradeSafe's fee.
	 */
	public static function setting_tradesafe_fee_allocation_callback() {
		echo '<select name="tradesafe_fee_allocation" class="small-text ltr">';
		echo '<option ' . ( get_option( 'tradesafe_fee_allocation', 'SELLER' ) === 'seller' ? 'selected' : '' ) . ' value="SELLER">Seller / Marketplace</option>';
		echo '<option ' . ( get_option( 'tradesafe_fee_allocation' ) === 'BUYER' ? 'selected' : '' ) . ' value="BUYER">Buyer</option>';
		echo '</select>';
	}

	/**
	 * Set which role should pay the additional gateway fee (Credit Card, Instant EFT, Snapscan).
	 */
	public static function setting_tradesafe_gateway_fee_allocation_callback() {
		echo '<select name="tradesafe_gateway_fee_allocation" class="small-text ltr">';
		echo '<option ' . ( get_option( 'tradesafe_gateway_fee_allocation', 'SELLER' ) === 'seller' ? 'selected' : '' ) . ' value="SELLER">Seller / Marketplace</option>';
		echo '<option ' . ( get_option( 'tradesafe_gateway_fee_allocation' ) === 'BUYER' ? 'selected' : '' ) . ' value="BUYER">Buyer</option>';
		echo '</select>';
	}

	/**
	 * Allow/Disable the acceptance of an order/transaction by the buyer.
	 */
	public static function setting_tradesafe_accept_transaction_callback() {
		echo '<input name="tradesafe_accept_transaction" id="tradesafe_accept_transaction" type="checkbox" value="1" ' . checked( 1, get_option( 'tradesafe_accept_transaction', true ), false ) . ' />';
	}

	/**
	 * Enable support for multiple vendors is not using a multi-vendor plugin.
	 */
	public static function setting_transaction_agent_callback() {
		echo '<input name="tradesafe_transaction_marketplace" id="tradesafe_transaction_marketplace" type="checkbox" value="1" ' . checked( 1, get_option( 'tradesafe_transaction_marketplace' ), false ) . ' />';
	}

	/**
	 * Set the commission fee.
	 */
	public static function setting_transaction_fee_callback() {
		echo '<input name="tradesafe_transaction_fee" id="tradesafe_transaction_fee" type="number" value="' . esc_attr( get_option( 'tradesafe_transaction_fee' ) ) . '" class="small-text ltr" />';
	}

	/**
	 * Set the commission type.
	 */
	public static function setting_transaction_fee_type_callback() {
		echo '<select name="tradesafe_transaction_fee_type" class="small-text ltr">';
		echo '<option ' . ( esc_attr( get_option( 'tradesafe_transaction_fee_type' ) ) === 'PERCENT' ? 'selected' : '' ) . ' value="PERCENT">Percent</option>';
		echo '<option ' . ( esc_attr( get_option( 'tradesafe_transaction_fee_type' ) ) === 'FIXED' ? 'selected' : '' ) . ' value="FIXED">Fixed</option>';
		echo '</select>';
	}

	/**
	 * Set which role should pay the commission fee.
	 */
	public static function setting_transaction_fee_allocation_callback() {
		echo '<select name="tradesafe_transaction_fee_allocation" class="small-text ltr">';
		echo '<option ' . ( esc_attr( get_option( 'tradesafe_transaction_fee_allocation', 'SELLER' ) ) === 'seller' ? 'selected' : '' ) . ' value="SELLER">Vendor</option>';
		echo '<option ' . ( esc_attr( get_option( 'tradesafe_transaction_fee_allocation' ) ) === 'BUYER' ? 'selected' : '' ) . ' value="BUYER">Buyer</option>';
		echo '</select>';
	}

	/**
	 * Set the role to pay the commission when using Dokan.
	 */
	public static function setting_payout_fee_dokan_callback() {
		echo '<select name="tradesafe_payout_fee" class="small-text ltr">';
		echo '<option ' . ( get_option( 'tradesafe_payout_fee', 'SELLER' ) === 'seller' ? 'selected' : '' ) . ' value="SELLER">Marketplace</option>';
		echo '<option ' . ( get_option( 'tradesafe_payout_fee' ) === 'BUYER' ? 'selected' : '' ) . ' value="BUYER">Buyer</option>';
		echo '<option ' . ( get_option( 'tradesafe_payout_fee' ) === 'VENDOR' ? 'selected' : '' ) . ' value="VENDOR">Vendor</option>';
		echo '</select>';
	}

	/**
	 * Show the commission set by the Dokan plugin.
	 */
	public static function setting_transaction_fee_dokan_callback() {
		echo esc_attr( dokan_get_option( 'admin_percentage', 'dokan_selling', 0 ) )
			. ' (<a href="' . esc_attr( admin_url( 'admin.php?page=dokan#/settings' ) ) . '">Change</a>)';
	}


	/**
	 * Show the commission type set by the Dokan plugin.
	 */
	public static function setting_transaction_fee_type_dokan_callback() {
		echo esc_attr( ucwords( dokan_get_option( 'commission_type', 'dokan_selling', 'percentage' ) ) )
			. ' (<a href="' . esc_attr( admin_url( 'admin.php?page=dokan#/settings' ) ) . '">Change</a>)';
	}

	/**
	 * Show who will pay the commission set by the Dokan plugin.
	 */
	public static function setting_transaction_fee_allocation_dokan_callback() {
		echo 'Vendor';
	}

	/**
	 * Add a link to the admin sidebar.
	 */
	public static function register_options_page() {
		add_menu_page(
			__( 'TradeSafe', 'tradesafe-payment-gateway' ),
			__( 'TradeSafe', 'tradesafe-payment-gateway' ),
			'manage_options',
			'tradesafe',
			array(
				'TradeSafe',
				'settings_page',
			),
			'dashicons-admin-settings',
			58
		);
	}

	/**
	 * Display the admin settings form.
	 */
	public static function settings_page() {
		// Don't allow sellers top alter order statues.
		if ( tradesafe_has_dokan() ) {
			$options = get_option( 'dokan_selling', array() );

			if ( 'on' === $options['order_status_change'] ) {
				$options['order_status_change'] = 'off';
				update_option( 'dokan_selling', $options );
			}
		}

		include_once __DIR__ . '/../partials/settings.php';
	}

	/**
	 * Handle routing for TradeSafe URLs.
	 *
	 * @param WP $wp Current WordPress environment instance.
	 */
	public static function parse_request( $wp ) {
		if ( array_key_exists( 'tradesafe', $wp->query_vars ) ) {
			switch ( $wp->query_vars['tradesafe'] ) {
				case 'callback':
					$data = json_decode( file_get_contents( 'php://input' ), true );

					if ( is_null( $data ) ) {
						wp_die(
							'No Data',
							'An Error Occurred While Processing Callback',
							array(
								'code' => 400,
							)
						);
					}

					$signature = $data['signature'];
					unset( $data['signature'] );

					$request = '';
					foreach ( $data as $value ) {
						$request .= $value;
					}

					$signature_check = hash_hmac( 'sha256', $request, get_option( 'tradesafe_client_id' ) );

					// TODO: Change how signature check works.
					if ( true ) {
						$query = wc_get_orders(
							array(
								'meta_key'     => 'tradesafe_transaction_id',
								'meta_value'   => $data['id'],
								'meta_compare' => '=',
							)
						);

						if ( ! isset( $query[0] ) ) {
							wp_die(
								'Invalid Transaction ID',
								'An Error Occurred While Processing Callback',
								array(
									'code' => 400,
								)
							);
						}

						$order = $query[0];

						if ( 'FUNDS_DEPOSITED' === $data['state'] ) {
							$order->update_status( 'on-hold', __( 'Awaiting Manual EFT payment.', 'tradesafe-payment-gateway' ) );
						}

						if ( ( $order->has_status( 'on-hold' ) || $order->has_status( 'pending' ) ) && 'FUNDS_RECEIVED' === $data['state'] ) {
							$client = tradesafe_api_client();

							$transaction = $client->getTransaction( $order->get_meta( 'tradesafe_transaction_id', true ) );
							$client->allocationStartDelivery( $transaction['allocations'][0]['id'] );

							$order->update_status( 'processing', 'Funds have been received by TradeSafe.' );
						}

						exit;
					} else {
						wp_die(
							'Invalid Signature',
							'An Error Occurred While Processing Callback',
							array(
								'code' => 400,
							)
						);
					}
					// Either exit is called or error is thrown.
				case 'eft-details':
					self::eft_details_page( $wp->query_vars['order-id'] );
					break;
				case 'accept':
					$order = wc_get_order( $wp->query_vars['order-id'] );
					$order->update_status( 'completed', 'Transaction Completed. Paying out funds to parties.' );
					wp_safe_redirect( wc_get_endpoint_url( 'orders', '', get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) ) );
					exit;
				case 'unlink':
					$user = wp_get_current_user();

					$meta_key = 'tradesafe_token_id';

					if ( get_option( 'tradesafe_production_mode' ) ) {
						$meta_key = 'tradesafe_prod_token_id';
					}

					delete_user_meta( $user->ID, $meta_key );
					wp_safe_redirect( wc_get_endpoint_url( 'edit-account', '', get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) ) );
					exit;
				default:
					status_header( 404 );
					include get_query_template( '404' );
					exit;
			}
		}
	}

	/**
	 * Calculate and add escrow fee to checkout page.
	 */
	public static function add_gateway_fee() {
		$client = tradesafe_api_client();

		if ( is_admin() && ! defined( 'DOING_AJAX' ) || is_null( $client ) || is_array( $client ) ) {
			return;
		}

		$totals = WC()->cart->get_totals();

		$base_value = $totals['subtotal']
			+ $totals['shipping_total']
			- $totals['discount_total']
			+ $totals['fee_total'];

		foreach ( WC()->cart->get_taxes() as $tax ) {
			$base_value += $tax;
		}

		$calculation = $client->getCalculation( $base_value, get_option( 'tradesafe_fee_allocation' ), get_option( 'tradesafe_transaction_industry' ) );

		if ( get_option( 'tradesafe_transaction_fee_allocation' ) === 'BUYER' ) {
			$fee = 0;

			switch ( get_option( 'tradesafe_transaction_fee_type' ) ) {
				case 'FIXED':
					$fee = get_option( 'tradesafe_transaction_fee' );
					break;
				case 'PERCENTAGE':
					$fee = $base_value * ( get_option( 'tradesafe_transaction_fee' ) / 100 );
					break;
			}

			WC()->cart->add_fee( 'Marketplace Fee', $fee, false );
		}

		if ( get_option( 'tradesafe_fee_allocation' ) === 'BUYER' ) {
			WC()->cart->add_fee( 'Escrow Fee', $calculation['processingFeeTotal'], false );
		}

		// Getting current chosen payment gateway.
		$chosen_payment_method = false;
		$available_gateways    = WC()->payment_gateways->get_available_payment_gateways();
		$default_gateway       = get_option( 'woocommerce_default_gateway' );
        // phpcs:disable WordPress.Security.NonceVerification.Recommended
		if ( isset( WC()->session->chosen_payment_method ) ) {
			$chosen_payment_method = WC()->session->chosen_payment_method;
		} elseif ( ! empty( $_REQUEST['payment_method'] ) ) {
			$chosen_payment_method = sanitize_key( $_REQUEST['payment_method'] );
		} elseif ( '' !== ( $default_gateway ) ) {
			$chosen_payment_method = $default_gateway;
		} elseif ( ! empty( $available_gateways ) ) {
			$chosen_payment_method = current( array_keys( $available_gateways ) );
		}
		if ( ! isset( $available_gateways[ $chosen_payment_method ] ) ) {
			$chosen_payment_method = false;
		}
        // phpcs:enable WordPress.Security.NonceVerification.Recommended
	}

	/**
	 * If user changed the payment method, reload the checkout data to show/hide the escrow fee.
	 */
	public static function refresh_checkout() {
		?>
		<script type="text/javascript">
			(function ($) {
				$('form.checkout').on('change', 'input[name^="payment_method"]', function () {
					$('body').trigger('update_checkout');
				});
			})(jQuery);
		</script>
		<?php
	}

	/**
	 * Add accept button to orders that have been set to processing.
	 *
	 * @param array    $actions Array of existing actions.
	 * @param WC_Order $order WooCommerce order.
	 * @return array
	 */
	public static function accept_order( array $actions, WC_Order $order ): array {
		if ( $order->has_status( 'processing' ) && get_option( 'tradesafe_accept_transaction', true ) ) {
			$action_slug = 'tradesafe_accept';

			$actions[ $action_slug ] = array(
				'url'  => home_url( '/tradesafe/accept/' . $order->get_order_number() ),
				'name' => 'Accept',
			);
		}

		return $actions;
	}

	/**
	 * Update a transaction when the order state has been changed to completed.
	 *
	 * @param int $order_id WooCommerce order id.
	 */
	public static function complete_transaction( int $order_id ) {
		$client = tradesafe_api_client();
		$order  = wc_get_order( $order_id );

		try {
			$transaction = $client->getTransaction( $order->get_meta( 'tradesafe_transaction_id', true ) );
			$client->allocationAcceptDelivery( $transaction['allocations'][0]['id'] );
		} catch ( \Exception $e ) {
			$order->set_status( 'processing', 'Error occurred while completing transaction on TradeSafe.' );
			wp_die(
				esc_attr( $e->getMessage() ),
				'Error occurred while completing transaction on TradeSafe',
				array(
					'code' => 400,
				)
			);
		}
	}

	/**
	 * Check if an order meets the minimum requirements to process a payment.
	 *
	 * @param array $available_gateways Array of allowed payment gateways.
	 * @return array
	 */
	public static function availability( array $available_gateways ): array {
		if ( is_admin() ) {
			return $available_gateways;
		}

        // phpcs:disable WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET['key'] ) ) {
			$key      = wp_unslash( sanitize_key( $_GET['key'] ) );
			$order_id = wc_get_order_id_by_order_key( $key );
			$order    = wc_get_order( $order_id );

			if ( $order->get_total() < 50 ) {
				unset( $available_gateways['tradesafe'] );
			}
		}
        // phpcs:enable WordPress.Security.NonceVerification.Recommended

		if ( WC()->cart->total !== 0 && WC()->cart->total < 50 ) {
			unset( $available_gateways['tradesafe'] );
		}

		return $available_gateways;
	}

	/**
	 * Display a message to a seller/vender if their account is incomplete.
	 */
	public static function seller_account_incomplete_notice() {
		$valid_account = self::is_valid_token( 'seller' );

		if ( false === $valid_account ) {
			$class   = 'notice notice-warning';
			$title   = __( 'Your account is incomplete!', 'tradesafe-payment-gateway' );
			$message = __( 'Our payment service provider is TradeSafe Escrow. TradeSafe keeps the funds safe in the middle and will release the funds to you once delivery is completed successfully. Sellers are guaranteed payment.', 'tradesafe-payment-gateway' );
			$more    = __( 'TradeSafe forces HTTPS for all services using TLS (SSL) including their public website and the Application. All bank account details are encrypted with AES-256. Decryption keys are stored on separate machines from the application. In English, your details are encrypted with the highest industry-specific standards (which can be found in most banks), making your information confidential, secure, and safe.', 'tradesafe-payment-gateway' );

			printf( '<div class="%1$s"><h3>%2$s</h3><p>%3$s</p><p>%4$s</p><p><a href="%5$s" class="button-secondary button alt button-large button-next">Update Account</a></p></div>', esc_attr( $class ), esc_html( $title ), esc_html( $message ), esc_html( $more ), esc_url( wc_get_endpoint_url( 'edit-account', '', get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) ) ) );
		}
	}

	/**
	 * Display a message to a buyer if their account is incomplete.
	 *
	 * @deprecated ID Number captured on payment page
	 */
	public static function buyer_account_incomplete_notice() {
		$valid_account = self::is_valid_token( 'buyer' );

		if ( false === $valid_account ) {
			$class   = 'notice notice-warning';
			$title   = __( 'Your account is incomplete!', 'tradesafe-payment-gateway' );
			$message = __( 'You may receive a message below that there are no available payment providers as your user account is incomplete. Please click on the button below to update your account to access additional payment methods. Once done, you will be able to proceed with checkout.', 'tradesafe-payment-gateway' );

			printf( '<div class="%1$s"><h3>%2$s</h3><p>%3$s</p><p><a href="%4$s" class="button-secondary button alt button-large button-next">Update Account</a></p></div>', esc_attr( $class ), esc_html( $title ), esc_html( $message ), esc_url( wc_get_endpoint_url( 'edit-account', '', get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) ) ) );
		}
	}

	/**
	 * Display a message to a buyer if their vendor is incomplete.
	 *
	 * @deprecated ID Number captured on payment page
	 */
	public static function vendor_account_incomplete_notice() {
		$valid_account = self::is_valid_token( 'buyer' );

		if ( false === $valid_account ) {
			$class   = 'notice notice-warning';
			$title   = __( 'Your account is incomplete!', 'tradesafe-payment-gateway' );
			$message = __( 'You may receive a message below that there are no available payment providers as your user account is incomplete. Please click on the button below to update your account to access additional payment methods. Once done, you will be able to proceed with checkout.', 'tradesafe-payment-gateway' );

			printf( '<div class="%1$s"><h3>%2$s</h3><p>%3$s</p><p><a href="%4$s" class="button-secondary button alt button-large button-next">Update Account</a></p></div>', esc_attr( $class ), esc_html( $title ), esc_html( $message ), esc_url( wc_get_endpoint_url( 'edit-account', '', get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) ) ) );
		}
	}

	/**
	 * If a user has already added their mobile number. Automatically add it to the checkout page.
	 *
	 * @param array $fields Array of fields for the checkout form.
	 * @return array
	 */
	public static function checkout_field_defaults( array $fields ): array {
		$client = tradesafe_api_client();
		$user   = wp_get_current_user();

		$meta_key = 'tradesafe_token_id';

		if ( get_option( 'tradesafe_production_mode' ) ) {
			$meta_key = 'tradesafe_prod_token_id';
		}

		$token_id = get_user_meta( $user->ID, $meta_key, true );

		if ( $token_id ) {
			$token_data = $client->getToken( $token_id );

			if ( isset( $token_data['user']['mobile'] ) && '' !== $token_data['user']['mobile'] ) {
				$fields['billing']['billing_phone']['placeholder'] = $token_data['user']['mobile'];
				$fields['billing']['billing_phone']['default']     = $token_data['user']['mobile'];
			}
		}

		return $fields;
	}

	/**
	 * Disable the product publish button if a users account is incomplete.
	 */
	public static function disable_publish_button() {
		$valid_account = self::is_valid_token( 'seller' );

		if ( $valid_account ) {
			return;
		}

		?>
		<script type="text/javascript">
			window.onload = function () {
				document.getElementById('publish').disabled = true;
			}
		</script>
		<?php
	}

	/**
	 * Disable the att product button on the Dokan dashboard if a users account is incomplete.
	 */
	public static function disable_add_product_button() {
		if ( isset( $_SERVER['REQUEST_URI'] ) && str_contains( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ), 'dashboard/products' ) ) {
			$valid_account = self::is_valid_token( 'seller' );

			if ( $valid_account ) {
				return;
			}

			?>
			<script type="text/javascript">
				window.onload = function () {
					let buttons = document.getElementsByClassName('dokan-add-new-product');

					Array.prototype.forEach.call(buttons, function (el) {
						el.style.visibility = 'hidden'
					});
				}
			</script>
			<?php
		}
	}

	/**
	 * Don't allow vendors to change the status of an order.
	 *
	 * The function disables the setting if an admin tries to enable it.
	 *
	 * @param array $value Array for configuration flags.
	 * @return array
	 */
	public static function override_dokan_selling( array $value ): array {
		$value['order_status_change'] = 'off';

		return $value;
	}
}
