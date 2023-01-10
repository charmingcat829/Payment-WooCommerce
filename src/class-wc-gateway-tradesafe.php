<?php
/**
 * TradeSafe Gateway for WooCommerce.
 *
 * @package TradeSafe Payment Gateway
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class WC_Gateway_TradeSafe Implantation of WC_Payment_Gateway
 */
class WC_Gateway_TradeSafe extends WC_Payment_Gateway {
	/**
	 * Api Client
	 *
	 * @var string
	 */
	public $client;

	/**
	 * Version
	 *
	 * @var string
	 */
	public $version;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id                 = 'tradesafe';
		$this->method_title       = __( 'TradeSafe', 'tradesafe-payment-gateway' );
		$this->method_description = __( 'TradeSafe, backed by Standard Bank, allows for your money to be kept safely until you receive what you ordered. Simply pay using Credit/Debit card, EFT, SnapScan, Ozow, or buy it now and pay later with PayJustNow.', 'tradesafe-payment-gateway' );
		$this->icon               = TRADESAFE_PAYMENT_GATEWAY_BASE_DIR . '/assets/images/logos.svg';

		$this->client = new \TradeSafe\Helpers\TradeSafeApiClient();

		$this->version              = WC_GATEWAY_TRADESAFE_VERSION;
		$this->available_countries  = array( 'ZA' );
		$this->available_currencies = (array) apply_filters( 'woocommerce_gateway_tradesafe_available_currencies', array( 'ZAR' ) );

		// Supported functionality.
		$this->supports = array(
			'products',
			'pre-orders',
		);

		// Setup default merchant data.
		$this->has_fields  = true;
		$this->enabled     = $this->is_valid_for_use() ? 'yes' : 'no'; // Check if the base currency supports this gateway.
		$this->title       = $this->get_option( 'title' );
		$this->description = $this->get_option( 'description' );

		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_admin_order_data_after_order_details', array( $this, 'tradesafe_payment_gateway_admin_order_data_after_order_details' ) );
		add_action( 'woocommerce_receipt_tradesafe', array( $this, 'receipt_page' ) );
		add_action( 'post_action_tradesafe_deliver', array( $this, 'tradesafe_payment_gateway_admin_post_action_deliver' ) );
		add_action( 'admin_notices', array( $this, 'tradesafe_payment_gateway_admin_notice' ), 1 );

		if ( is_admin() ) {
			wp_enqueue_script( 'tradesafe-payment-gateway-settings', TRADESAFE_PAYMENT_GATEWAY_BASE_DIR . '/assets/js/settings.js', array( 'jquery' ), WC_GATEWAY_TRADESAFE_VERSION, true );
			wp_enqueue_style( 'tradesafe-payment-gateway-settings', TRADESAFE_PAYMENT_GATEWAY_BASE_DIR . '/assets/css/style.css', array(), WC_GATEWAY_TRADESAFE_VERSION );

			if ( isset( $_GET['page'] ) && isset( $_GET['tab'] ) && isset( $_GET['section'] ) && $_GET['page'] === 'wc-settings' && $_GET['tab'] === 'checkout' && $_GET['section'] === 'tradesafe' ) {
				$this->init_form_fields();
				$this->init_settings();
			}
		}
	}

	/**
	 * Get title function.
	 *
	 * @return string
	 */
	public function get_title() {
		// show the title with an icon on the checkout page alone
		if ( ! is_checkout() ) {
			return parent::get_title();
		}

		$logo_url = plugins_url( '../assets/images/icon.svg', __FILE__ );
		$img      = '<img src="' . $logo_url . '" style="height: 1.4em;margin-left: 0px;margin-right: 0.3em;display: inline;float: none;" class="' . $this->id . '-payment-method-title-icon" alt="TradeSafe logo" />';
		$title    = '<span style="display: inline-flex;align-items: center;vertical-align: middle;">' . $img . parent::get_title() . '</span>';
		return apply_filters( 'woocommerce_gateway_title', $title, $this->id );
	}

	/**
	 * Get icon function.
	 *
	 * @return string
	 */
	public function get_icon() {
		$icon = '<img src="' . $this->icon . '" style="max-height:26px;" class="' . $this->id . '-payment-method-icon" alt="TradeSafe payment options" />';
		return apply_filters( 'woocommerce_gateway_icon', $icon, $this->id );
	}

	/**
	 * Check if this gateway is enabled and available in the base currency being traded with.
	 *
	 * @return bool
	 * @since 1.0.0
	 */
	public function is_valid_for_use() {
		$is_available_currency = in_array( get_woocommerce_currency(), $this->available_currencies, true );

		if ( ! $is_available_currency ) {
			return false;
		}

		$settings = get_option( 'woocommerce_tradesafe_settings', array() );

		if ( ! isset( $settings['client_id'] )
			|| '' === $settings['client_id']
			|| ! isset( $settings['client_secret'] )
			|| '' === $settings['client_secret'] ) {
			return false;
		}

		$profile = $this->client->profile();

		if ( isset( $profile['error'] ) ) {
			return false;
		}

		if ( 'no' === $this->get_option( 'enabled' ) || null === $this->get_option( 'enabled' ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Check if the gateway is available for use.
	 *
	 * @return bool
	 */
	public function is_available() {
		if ( 'yes' === $this->enabled ) {
			// Prevent using this gateway on frontend if there are any configuration errors.
			return true;
		}

		return parent::is_available();
	}

	/**
	 * Display warning if API has been enabled for production but not configured on the settings.
	 *
	 * @return void
	 */
	function tradesafe_payment_gateway_admin_notice() {
		if ( isset( $_GET['page'] )
			&& isset( $_GET['tab'] )
			&& isset( $_GET['section'] )
			&& $_GET['page'] === 'wc-settings'
			&& $_GET['tab'] === 'checkout'
			&& $_GET['section'] === 'tradesafe' ) {
			return;
		}

		$settings_url = add_query_arg(
			array(
				'page'    => 'wc-settings',
				'tab'     => 'checkout',
				'section' => 'tradesafe',
			),
			admin_url( 'admin.php' )
		);

		 $client  = new \TradeSafe\Helpers\TradeSafeApiClient();
		 $profile = $client->profile();

		if ( isset( $profile['error'] ) ) {
			echo '<div class="notice notice-error">
		        <h2>TradeSafe plugin cannot connect to the API!</h2>
		        <p>There is a problem connecting to the TradeSafe API please check that the client ID and client secret are correctly configured. If the problem persists please contact TradeSafe support.</p>
		        <p><strong>REASON:</strong> ' . $profile['error'] . '</p>
		        <p><a href="' . esc_url( $settings_url ) . '" class="button button-primary button-large">Take me to the settings page!</a></p>
		    </div>';
		}

		if ( true === $this->is_valid_for_use()
			&& false === tradesafe_is_prod() ) {
			echo '<div class="notice notice-warning is-dismissible">
                <h2>Warning you are running the TradeSafe plugin in sandbox mode!</h2>
                <p>Any users who crate orders while in this mode will not be charged! To fix this go to the TradeSafe settings page and change the environment to "Live".</p>
                <p><a href="' . esc_url( $settings_url ) . '" class="button button-primary button-large">Take me to the settings page!</a></p>
            </div>';
		}
	}

	/**
	 * Define Gateway settings fields.
	 */
	public function init_form_fields() {
		$settings = get_option( 'woocommerce_tradesafe_settings', array() );

		$form = array(
			'enabled'     => array(
				'title'       => __( 'Enable/Disable', 'tradesafe-payment-gateway' ),
				'label'       => __( 'Enable TradeSafe', 'tradesafe-payment-gateway' ),
				'type'        => 'checkbox',
				'description' => __( 'This controls whether or not this gateway is enabled within WooCommerce.', 'tradesafe-payment-gateway' ),
				'default'     => 'yes',
				'desc_tip'    => true,
			),
			'title'       => array(
				'title'       => __( 'Title', 'tradesafe-payment-gateway' ),
				'type'        => 'text',
				'description' => __( 'This controls the title which the user sees during checkout.', 'tradesafe-payment-gateway' ),
				'default'     => $this->method_title,
				'desc_tip'    => true,
			),
			'description' => array(
				'title'       => __( 'Description', 'tradesafe-payment-gateway' ),
				'type'        => 'textarea',
				'description' => __( 'This controls the description which the user sees during checkout.', 'tradesafe-payment-gateway' ),
				'default'     => $this->method_description,
				'desc_tip'    => true,
			),
		);

		$form['setup_details'] = array(
			'title'       => __( 'Callback Details', 'tradesafe-payment-gateway' ),
			'description' => __( 'The following urls are used when registering your application with TradeSafe.', 'tradesafe-payment-gateway' ),
			'type'        => 'setup_details',
		);

		$form['debug_details'] = array(
			'title'       => __( 'Plugin Details', 'tradesafe-payment-gateway' ),
			'description' => __( 'Various details about your WordPress install used for debugging and support.', 'tradesafe-payment-gateway' ),
			'type'        => 'debug_details',
		);

		$form['application_details'] = array(
			'title'       => __( 'Application Details', 'tradesafe-payment-gateway' ),
			'description' => __( 'Details of your application registered with TradeSafe.', 'tradesafe-payment-gateway' ),
			'type'        => 'application_details',
		);

		$form['application_section_title'] = array(
			'title'       => __( 'Application Settings', 'tradesafe-payment-gateway' ),
			'type'        => 'title',
			'description' => __( 'API configuration', 'tradesafe-payment-gateway' ),
		);

		$form['client_id'] = array(
			'title'       => __( 'Client ID', 'tradesafe-payment-gateway' ),
			'type'        => 'text',
			'description' => __( 'Client ID for your application.', 'tradesafe-payment-gateway' ),
			'default'     => null,
			'desc_tip'    => true,
		);

		$form['client_secret'] = array(
			'title'       => __( 'Client Secret', 'tradesafe-payment-gateway' ),
			'type'        => 'password',
			'description' => __( 'Client secret for your application.', 'tradesafe-payment-gateway' ),
			'default'     => null,
			'desc_tip'    => true,
		);

		if ( $this->client->production() ) {
			$form['environment'] = array(
				'title'    => __( 'Environment', 'tradesafe-payment-gateway' ),
				'type'     => 'select',
				'default'  => 'SIT',
				'options'  => array(
					'SIT'  => 'Sandbox',
					'PROD' => 'Live',
				),
				'desc_tip' => false,
			);
		} else {
			$form['environment'] = array(
				'title'       => __( 'Environment', 'tradesafe-payment-gateway' ),
				'type'        => 'row',
				'description' => __( 'To access the live environment, you will need to complete the go-live process for your application', 'tradesafe-payment-gateway' ),
				'value'       => 'Sandbox',
			);

			if ( isset( $settings['client_id'] ) && '' !== $settings['client_id'] ) {
				$form['go_live'] = array(
					'title'       => __( 'Go Live', 'tradesafe-payment-gateway' ),
					'description' => __( 'Take your store live to start processing transactions with real world currency.', 'tradesafe-payment-gateway' ),
					'type'        => 'go_live',
					'value'       => $settings['client_id'],
				);
			}
		}

		$form['delivery_section_title'] = array(
			'title'       => __( 'Delivery Settings', 'tradesafe-payment-gateway' ),
			'type'        => 'title',
			'description' => __( 'Additional settings for deliveries', 'tradesafe-payment-gateway' ),
		);

		$form['delivery_delay_notification'] = array(
			'title'       => __( 'Delivery Notification', 'tradesafe-payment-gateway' ),
			'label'       => 'Delay Notification',
			'type'        => 'checkbox',
			'description' => __( 'TradeSafe sends an email and a SMS to the customer asking if they received what was ordered once the order has been marked as DELIVERED. Some courier companies, such as uAfrica, marks the order as COMPLETED (TradeSafe then marks as DELIVERED) once the order has been fulfilled and not when it has been delivered. This means that a customer might receive an email and a SMS before the goods were delivered. To prevent this, please specify the number of business days you would like to initiate the goods acceptance process after the order has been marked as DELIVERED.', 'tradesafe-payment-gateway' ),
			'default'     => false,
			'desc_tip'    => false,
		);

		$form['delivery_days'] = array(
			'title'       => __( 'Days to Delay', 'tradesafe-payment-gateway' ),
			'type'        => 'select',
			'description' => __( 'Number of days it takes to deliver the goods or service.', 'tradesafe-payment-gateway' ),
			'default'     => '5',
			'class'       => 'delivery',
			'options'     => array(
				'1'  => '1 Day',
				'2'  => '2 Days',
				'3'  => '3 Days',
				'4'  => '4 Days',
				'5'  => '5 Days',
				'6'  => '6 Days',
				'7'  => '1 Week',
				'14' => '2 Weeks',
				'21' => '3 Weeks',
			),
			'desc_tip'    => true,
		);

		// $form['inspection_days'] = array(
		// 'title'       => __( 'Days to Inspect', 'tradesafe-payment-gateway' ),
		// 'type'        => 'select',
		// 'description' => __( 'Number of days for the buyer to inspect the goods or service.', 'tradesafe-payment-gateway' ),
		// 'default'     => '1',
		// 'options'     => array(
		// '1' => '1 Day',
		// '2' => '2 Days',
		// '3' => '3 Days',
		// '4' => '4 Days',
		// '5' => '5 Days',
		// '6' => '6 Days',
		// '7' => '1 Week',
		// ),
		// 'desc_tip'    => true,
		// );

		$form['marketplace_section_title'] = array(
			'title'       => __( 'Marketplace Settings', 'tradesafe-payment-gateway' ),
			'type'        => 'title',
			'description' => __( 'Additional settings for creating a marketplace', 'tradesafe-payment-gateway' ),
		);

		$form['is_marketplace'] = array(
			'title'       => __( 'Is this website a Marketplace?', 'tradesafe-payment-gateway' ),
			'label'       => 'Enable Marketplace Support',
			'type'        => 'checkbox',
			'description' => __( 'You are a marketplace owner who is paid a commission and has multiple vendors onboarded onto your store', 'tradesafe-payment-gateway' ),
			'default'     => false,
			'desc_tip'    => false,
			'class'       => 'test',
		);

		$form['marketplace_section_open_box'] = array(
			'type'  => 'open_box',
			'class' => 'is-marketplace',
		);

		$form['commission'] = array(
			'title'             => __( 'Marketplace Commission Fee', 'tradesafe-payment-gateway' ),
			'type'              => 'number',
			'description'       => __( 'What is the amount that is payable to you the marketplace owner for every transaction', 'tradesafe-payment-gateway' ),
			'default'           => 10,
			'desc_tip'          => false,
			'custom_attributes' => array(
				'min'  => 1,
				'step' => 0.01,
			),
		);

		$form['commission_type'] = array(
			'title'    => __( 'Marketplace Commission Type', 'tradesafe-payment-gateway' ),
			'type'     => 'select',
			'default'  => 'PERCENT',
			'options'  => array(
				'PERCENT' => 'Percentage',
				'FIXED'   => 'Fixed Value',
			),
			'desc_tip' => false,
		);

		$form['commission_allocation'] = array(
			'title'    => __( 'Marketplace Commission Fee Allocation', 'tradesafe-payment-gateway' ),
			'type'     => 'select',
			'default'  => 'VENDOR',
			'options'  => array(
				'BUYER'  => 'Buyer',
				'VENDOR' => 'Vendor',
			),
			'desc_tip' => false,
		);

		if ( tradesafe_has_dokan() ) {
			$form['commission'] = array(
				'title'       => __( 'Marketplace Commission Fee', 'tradesafe-payment-gateway' ),
				'description' => __( 'What is the amount that is payable to you the marketplace owner for every transaction.', 'tradesafe-payment-gateway' ),
				'type'        => 'row',
				'value'       => dokan_get_option( 'admin_percentage', 'dokan_selling', 0 ),
			);

			$form['commission_type'] = array(
				'title'       => __( 'Marketplace Commission Type', 'tradesafe-payment-gateway' ),
				'description' => __( 'What type of commission been changed.', 'tradesafe-payment-gateway' ),
				'type'        => 'row',
				'value'       => ucwords( dokan_get_option( 'commission_type', 'dokan_selling', 'percentage' ) ),
			);

			$form['commission_allocation'] = array(
				'title'       => __( 'Marketplace Commission Fee Allocation', 'tradesafe-payment-gateway' ),
				'description' => __( 'Who will pay the commission.' ),
				'type'        => 'row',
				'value'       => 'Vendor',
			);
		}

		$form['payout_method'] = array(
			'title'       => __( 'When should Vendors be Paid Out?', 'tradesafe-payment-gateway' ),
			'description' => 'A R5 fee (excl.) is incurred for payouts. If "Once a month" is selected this fee is waived.',
			'type'        => 'select',
			'default'     => 'IMMEDIATE',
			'options'     => array(
				'ACCOUNT'   => 'Manual Withdrawal',
				'IMMEDIATE' => 'Immediate',
				'DAILY'     => 'Once a Day',
				'WEEKLY'    => 'Once a week',
				'BIMONTHLY' => 'Twice a month',
				'MONTHLY'   => 'Once a month',
			),
		);

		$form['marketplace_section_close_box'] = array(
			'type' => 'close_box',
		);

		$form['transaction_section_title'] = array(
			'title'       => __( 'Transaction Settings', 'tradesafe-payment-gateway' ),
			'type'        => 'title',
			'description' => __( 'Default settings for new transactions', 'tradesafe-payment-gateway' ),
		);

		$form['industry'] = array(
			'title'       => __( 'Industry', 'tradesafe-payment-gateway' ),
			'type'        => 'select',
			'description' => __( 'Which industry will your transactions be classified as?', 'tradesafe-payment-gateway' ),
			'default'     => 'GENERAL_GOODS_SERVICES',
			'options'     => $this->client->getEnum( 'Industry' ),
			'desc_tip'    => false,
		);

		$form['processing_fee'] = array(
			'title'       => __( 'Processing Fee', 'tradesafe-payment-gateway' ),
			'type'        => 'select',
			'description' => __( 'Who absorbs TradeSafe’s fee?', 'tradesafe-payment-gateway' ),
			'default'     => 'SELLER',
			'options'     => array(
				'BUYER'        => 'Buyer',
				'SELLER'       => 'Seller',
				'BUYER_SELLER' => 'Buyer / Seller',
			),
			'desc_tip'    => false,
		);

		$this->form_fields = $form;
	}

	/**
	 * Init settings for gateways.
	 */
	public function init_settings() {
		parent::init_settings();

		if ( empty( $this->settings['industry'] ) ) {
			$this->settings['industry'] = 'GENERAL_GOODS_SERVICES';
		}

		if ( empty( $this->settings['processing_fee'] ) ) {
			$this->settings['processing_fee'] = 'SELLER';
		}

		if ( empty( $this->settings['commission_allocation'] ) ) {
			$this->settings['commission_allocation'] = 'VENDOR';
		}
	}

	/**
	 * Processes and saves options.
	 * If there is an error thrown, will continue to save and validate fields, but will leave the erroring field out.
	 *
	 * @return bool was anything saved?
	 */
	public function process_admin_options() {
		delete_transient( 'tradesafe_client_token' );

		return parent::process_admin_options();
	}

	public function validate_client_id_field( $key, $value ): string {
		if ( empty( $value ) ) {
			return '';
		}

		if ( preg_match( '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $value ) === 1 ) {
			return $value;
		}

		return '';
	}

	/**
	 * Create html for the details needed to setup the application.
	 *
	 * @var $key string
	 * @var $data array
	 */
	public function generate_setup_details_html( string $key, array $data ) {
		$urls = array(
			'oauth_callback' => site_url( '/tradesafe/oauth/callback/' ),
			'callback'       => site_url( '/tradesafe/callback/' ),
			'success'        => wc_get_endpoint_url( 'orders', '', get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) ),
			'failure'        => wc_get_endpoint_url( 'orders', '', get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) ),
		);

		ob_start();
		?>
		<tr>
			<td colspan="2" id="tradesafe-callback-details" class="details">
				<div class="details-box callback-details">
					<h3><?php esc_attr_e( $data['title'] ); ?></h3>
					<p><?php esc_attr_e( $data['description'] ); ?></p>
					<table class="form-table">
						<tbody>
						<tr>
							<th scope="row">OAuth Callback URL</th>
							<td><?php esc_attr_e( $urls['oauth_callback'] ); ?></td>
						</tr>
						<tr>
							<th scope="row">API Callback URL</th>
							<td><?php esc_attr_e( $urls['callback'] ); ?></td>
						</tr>
						<tr>
							<th scope="row">Success URL</th>
							<td><?php esc_attr_e( $urls['success'] ); ?></td>
						</tr>
						<tr>
							<th scope="row">Failure URL</th>
							<td><?php esc_attr_e( $urls['failure'] ); ?></td>
						</tr>
						</tbody>
					</table>
					<p>
						<a href="https://developer.tradesafe.co.za/"
						   class="button-secondary button alt button-large button-next" target="_blank">Register
							Application</a>
					</p>
				</div>
			</td>
		</tr>
		<?php
		return ob_get_clean();
	}

	/**
	 *
	 */
	public function generate_debug_details_html( $key, $data ) {
		$ping_result = $this->client->ping();

		ob_start();
		?>
		<tr>
			<td colspan="2" id="tradesafe-plugin-details" class="details">
				<div class="details-box plugin-details">
					<h3><?php esc_attr_e( $data['title'] ); ?> <small>(<a href="#" class="toggle-plugin-details">show</a>)</small>
					</h3>
					<p><?php esc_attr_e( $data['description'] ); ?></p>
					<table class="form-table">
						<tbody>
						<tr>
							<th scope="row">PHP Version</th>
							<td><?php esc_attr_e( phpversion() ); ?></td>
						</tr>
						<tr>
							<th scope="row">WordPress Version</th>
							<td><?php esc_attr_e( get_bloginfo( 'version' ) ); ?></td>
						</tr>
						<tr>
							<th scope="row">Woocommerce Version</th>
							<td><?php esc_attr_e( WC_VERSION ); ?></td>
						</tr>
						<tr>
							<th scope="row">Plugin Version</th>
							<td><?php esc_attr_e( WC_GATEWAY_TRADESAFE_VERSION ); ?></td>
						</tr>
						<tr>
							<th scope="row">API Domain</th>
							<td>
								<?php
								esc_attr_e( $ping_result['api']['domain'] );
								esc_attr_e( ' [' . ( $ping_result['api']['status'] ? 'OK' : 'ERROR' ) . ']' )
								?>
							</td>
						</tr>
						<?php
						if ( $ping_result['api']['reason'] ) {
							echo '<tr><th scope="row">API Error</th><td>' . esc_attr( $ping_result['api']['reason'] ) . '</td></tr>';
						}
						?>
						<tr>
							<th scope="row">Authentication Domain</th>
							<td>
								<?php
								esc_attr_e( $ping_result['auth']['domain'] );
								esc_attr_e( ' [' . ( $ping_result['auth']['status'] ? 'OK' : 'ERROR' ) . ']' )
								?>
							</td>
						</tr>
						<?php
						if ( $ping_result['auth']['reason'] ) {
							echo '<tr><th scope="row">Authentication Error</th><td>' . esc_attr( $ping_result['auth']['reason'] ) . '</td></tr>';
						}
						?>
						</tbody>
					</table>
				</div>
			</td>
		</tr>
		<?php
		return ob_get_clean();
	}

	/**
	 * @param $key
	 * @param $data
	 * @return false|string
	 */
	public function generate_application_details_html( $key, $data ) {
		$profile    = $this->client->profile();
		$production = $this->client->production();

		if ( isset( $profile['error'] ) ) {
			$body  = "<tr><th scope='row'>Error:</th><td> Could not connect to server</td></tr>";
			$body .= "<tr><th scope='row'>Reason:</th><td> " . esc_attr( $profile['error'] ) . '</td></tr>';
		} else {
			$body  = "<tr><th scope='row'>Organization Name:</th><td>" . esc_attr( $profile['organization']['name'] ) . '</td></tr>';
			$body .= "<tr><th scope='row'>Registration Number:</th><td>" . esc_attr( $profile['organization']['registration'] ) . '</td></tr>';

			if ( $profile['organization']['taxNumber'] ) {
				$body .= "<tr><th scope='row'>Tax Number:</th><td>" . esc_attr( $profile['organization']['taxNumber'] ) . '</td></tr>';
			}

			$body .= "<tr><th scope='row'>Name:</th><td>" . esc_attr( $profile['user']['givenName'] ) . ' ' . esc_attr( $profile['user']['familyName'] ) . '</td></tr>';
			$body .= "<tr><th scope='row'>Email:</th><td>" . esc_attr( $profile['user']['email'] ) . '</td></tr>';
			$body .= "<tr><th scope='row'>Mobile:</th><td>" . esc_attr( $profile['user']['mobile'] ) . '</td></tr>';

			$body .= "<tr><th scope='row'>Go-Live Completed:</th><td>" . esc_attr( $production ? 'Yes' : 'No' ) . '</td></tr>';
		}

		ob_start();
		?>
		<tr>
			<td colspan="2" id="tradesafe-application-details" class="details">
				<div class="details-box application-details">
					<h3><?php esc_attr_e( $data['title'] ); ?> <small>(<a href="#" class="toggle-application-details"><?php print $production ? 'show' : 'hide'; ?></a>)</small></h3>
					<p><?php esc_attr_e( $data['description'] ); ?></p>
					<table class="form-table" style="<?php print $production ? 'display: none' : 'display: table'; ?>">
						<tbody>
						<?php echo $body; ?>
						</tbody>
					</table>
				</div>
			</td>
		</tr>
		<?php
		return ob_get_clean();
	}

	/**
	 * @param $key
	 * @param $data
	 * @return void
	 */
	public function generate_go_live_html( $key, $data ) {
		ob_start();
		?>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="woocommerce_tradesafe_environment"></label>
			</th>
			<td class="forminp">
				<fieldset>
					<a href="https://developer.tradesafe.co.za//applications/<?php esc_attr_e( $data['value'] ); ?>/go-live"
					   class="button-primary" target="_blank">Request Go-Live</a>
					<p class="description"><?php esc_attr_e( $data['description'] ); ?></p>
				</fieldset>
			</td>
		</tr>
		<?php
		return ob_get_clean();
	}

	/**
	 * @param $key
	 * @param $data
	 * @return false|string
	 */
	public function generate_row_html( $key, $data ) {
		ob_start();
		?>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="woocommerce_tradesafe_environment"><?php esc_attr_e( $data['title'] ); ?></label>
			</th>
			<td class="forminp">
				<fieldset>
					<?php esc_attr_e( $data['value'] ); ?>
					<p class="description"><?php esc_attr_e( $data['description'] ); ?></p>
				</fieldset>
			</td>
		</tr>
		<?php
		return ob_get_clean();
	}

	public function generate_open_box_html( $key, $data ) {
		ob_start();
		?>
		</tbody>
		</table>
		<div class="<?php esc_attr_e( $data['class'] ); ?>">
		<table class="form-table">
		<tbody>
		<?php
		return ob_get_clean();
	}

	public function generate_close_box_html( $key, $data ) {
		ob_start();
		?>
		</tbody>
		</table>
		</div>
		<table class="form-table">
		<tbody>
		<?php
		return ob_get_clean();
	}

	/**
	 * Generate page for the gateway setting page.
	 */
	public function admin_options() {
		?>
		<h2><?php esc_attr_e( 'TradeSafe', 'tradesafe-payment-gateway' ); ?></h2>
		<table class="form-table">
			<?php $this->generate_settings_html(); ?>
		</table>
		<?php
	}

	/**
	 * Create a transaction on TradeSafe and link it to an order.
	 *
	 * @param int $order_id WooCommerce Order Id.
	 * @return array|null
	 * @throws Exception
	 */
	public function process_payment( $order_id ): ?array {
		global $woocommerce;

		$client = new \TradeSafe\Helpers\TradeSafeApiClient();
		$order  = new WC_Order( $order_id );

		if ( is_null( $client ) || is_array( $client ) ) {
			return null;
		}

		$order->set_payment_method_title( parent::get_title() );
		$order->save();

		if ( ! $order->meta_exists( 'tradesafe_transaction_id' ) ) {
			$user = wp_get_current_user();

			$profile = $client->profile();

			$item_list = array();
			$vendors   = array();
			foreach ( $order->get_items() as $item ) {
				// Get product owner.
				$product = get_post( $item['product_id'] );

				if ( tradesafe_is_marketplace() && ! tradesafe_has_dokan() ) {
					if ( ! isset( $vendors[ $product->post_author ] ) ) {
						$vendors[ $product->post_author ]['total'] = 0;
					}

					$vendors[ $product->post_author ]['total'] += $item->get_total();
				}

				// Add item to list for description.
				$item_list[] = html_entity_decode( esc_attr( $item->get_name() ) . ': ' . strip_tags( wc_price( $order->get_line_subtotal( $item ), array( 'currency' => $order->get_currency() ) ) ) . ' (excl)' );
			}

			$payout_interval = 'IMMEDIATE';
			$settings        = get_option( 'woocommerce_tradesafe_settings', array() );

			if ( empty( $settings['payout_method'] ) ) {
				$payout_interval = $settings['payout_method'];
			}

			if ( empty( $settings['delivery_days'] ) ) {
				$settings['delivery_days'] = '5';
			}

			if ( empty( $settings['inspection_days'] ) ) {
				$settings['inspection_days'] = '1';
			}

			$allocations[] = array(
				'title'         => 'Order ' . $order->get_id(),
				'description'   => implode( PHP_EOL, $item_list ),
				'value'         => ( (float) $order->get_subtotal() + (float) $order->get_total_fees() - (float) $order->get_discount_total() + (float) $order->get_shipping_total() + (float) $order->get_total_tax() ),
				'daysToDeliver' => $settings['delivery_days'],
				'daysToInspect' => $settings['inspection_days'],
			);

			if ( $user->ID === 0 ) {
				$userDetails = array(
					'givenName'  => $order->data['billing']['first_name'],
					'familyName' => $order->data['billing']['last_name'],
					'email'      => $order->data['billing']['email'],
					'mobile'     => $order->data['billing']['phone'],
				);

				$token_data = $client->createToken(
					$userDetails,
					null,
					null,
					$payout_interval
				);

				$parties[] = array(
					'role'  => 'BUYER',
					'token' => $token_data['id'],
				);
			} else {
				$parties[] = array(
					'role'  => 'BUYER',
					'token' => tradesafe_get_token_id( (int) $user->ID ),
				);
			}

			$parties[] = array(
				'role'  => 'SELLER',
				'token' => $profile['id'],
			);

			if ( tradesafe_has_dokan() ) {
				$sub_orders = get_children(
					array(
						'post_parent' => dokan_get_prop( $order, 'id' ),
						'post_type'   => 'shop_order',
						'post_status' => array(
							'wc-pending',
							'wc-completed',
							'wc-processing',
							'wc-on-hold',
							'wc-delivered',
							'wc-cancelled',
						),
					)
				);

				if ( ! $sub_orders ) {
					$parties[] = array(
						'role'          => 'BENEFICIARY_MERCHANT',
						'token'         => tradesafe_get_token_id( dokan_get_seller_id_by_order( $order->ID ) ),
						'fee'           => dokan()->commission->get_earning_by_order( $order ),
						'feeType'       => 'FLAT',
						'feeAllocation' => 'SELLER',
					);
				} else {
					foreach ( $sub_orders as $sub_order_post ) {
						$sub_order = wc_get_order( $sub_order_post->ID );

						$parties[] = array(
							'role'          => 'BENEFICIARY_MERCHANT',
							'token'         => tradesafe_get_token_id( dokan_get_seller_id_by_order( $sub_order->get_id() ) ),
							'fee'           => dokan()->commission->get_earning_by_order( $sub_order ),
							'feeType'       => 'FLAT',
							'feeAllocation' => 'SELLER',
						);
					}
				}
			} else {
				foreach ( $vendors as $vendor_id => $vendor ) {
					$fee = 0;
					if ( tradesafe_fee_allocation() === 'SELLER' ) {
						switch ( tradesafe_commission_type() ) {
							case 'PERCENT':
								$fee = $vendor['total'] * ( tradesafe_commission_value() / 100 );
								break;
							case 'FIXED':
								$fee = tradesafe_commission_value();
								break;
						}
					}

					$parties[] = array(
						'role'          => 'BENEFICIARY_MERCHANT',
						'token'         => tradesafe_get_token_id( (int) $vendor_id ),
						'fee'           => $vendor['total'] - $fee,
						'feeType'       => 'FLAT',
						'feeAllocation' => 'SELLER',
					);
				}
			}

			// Check all parties have a token.
			foreach ( $parties as $party ) {
				if ( null === $party['token'] || '' === $party['token'] ) {
					wc_add_notice( 'There was a problem processing your transaction. Please contact support.', $notice_type = 'error' );

					if ( WP_DEBUG ) {
						wc_add_notice( json_encode( $parties, JSON_PRETTY_PRINT ), $notice_type = 'error' );
					}

					return array(
						'result'   => 'failure',
						'messages' => 'Invalid token for ' . $party['role'],
					);
				}
			}

			// TODO: Add check for Gobuddy once plugin is avaliable
			$custom_parties = apply_filters( 'tradesafe_payment_gateway_add_parties', array() );

			foreach ( $custom_parties as $party ) {
				if ( empty( $party['role'] ) && ! in_array( $party['role'], array( 'BENEFICIARY_ADVISER', 'BENEFICIARY_CONSULTANT', 'BENEFICIARY_DELIVERY_COMPANY', 'BENEFICIARY_FINANCIAL_INSTITUTION', 'BENEFICIARY_INTERMEDIARY', 'BENEFICIARY_LEGAL_COUNSEL', 'BENEFICIARY_SUB_AGENT', 'BENEFICIARY_WHOLESALER', 'BENEFICIARY_OTHER' ) ) ) {
					break;
				}

				if ( empty( $party['token_id'] ) && is_string( $party['token_id'] ) ) {
					break;
				}

				if ( empty( $party['fee'] ) && is_float( $party['fee'] ) ) {
					break;
				}

				$parties[] = array(
					'role'          => $party['role'],
					'token'         => $party['token_id'],
					'fee'           => $party['fee'],
					'feeType'       => 'FLAT',
					'feeAllocation' => 'SELLER',
				);
			}

			$transaction = $client->createTransaction(
				array(
					'title'         => 'Order ' . $order->get_id(),
					'description'   => implode( PHP_EOL, $item_list ),
					'industry'      => tradesafe_industry(),
					'feeAllocation' => tradesafe_fee_allocation(),
					'reference'     => $order->get_order_key() . '-' . time(),
				),
				$allocations,
				$parties
			);

			$order->add_meta_data( 'tradesafe_transaction_id', $transaction['id'], true );
			$order->save_meta_data();

			$transaction_id = $transaction['id'];
		} else {
			$transaction_id = $order->get_meta( 'tradesafe_transaction_id', true );
		}

		// Mark as pending.
		$order->update_status( 'pending', __( 'Awaiting payment.', 'tradesafe-payment-gateway' ) );

		// Remove cart.
		$woocommerce->cart->empty_cart();

		// Return redirect.
		return array(
			'result'   => 'success',
			'redirect' => $client->getTransactionDepositLink( $transaction_id ),
		);
	}

	/**
	 * Add complete order button to order page if transaction is in transit.
	 *
	 * @param $order
	 * @return void
	 */
	public function tradesafe_payment_gateway_admin_order_data_after_order_details( $order ) {
		if ( ! $order instanceof WC_Order || ! $order->get_id() ) {
			return;
		}

		$settings = get_option( 'woocommerce_tradesafe_settings', array() );

		if ( ! isset( $settings['delivery_delay_notification'] )
			 || 'yes' !== $settings['delivery_delay_notification'] ) {
			return;
		}

		$client      = new \TradeSafe\Helpers\TradeSafeApiClient();
		$transaction = $client->getTransaction( $order->get_meta( 'tradesafe_transaction_id', true ) );

		if ( 'IN_TRANSIT' === $transaction['allocations'][0]['state'] ) {
			$url = add_query_arg(
				array(
					'post'   => $order->get_id(),
					'action' => 'tradesafe_deliver',
				),
				admin_url( 'post.php' )
			);

			ob_start();
			?>
			<p class="form-field form-field-wide tradesafe-complete-order">
				<a href="<?php echo esc_url( $url ); ?>" class="button button-secondary button-large">Mark as Delivered</a>
			</p>
			<?php
			ob_end_flush();
		}
	}

	/**
	 *
	 * @param $post_id
	 * @return void
	 */
	public function tradesafe_payment_gateway_admin_post_action_deliver( $post_id ) {
		$client = new \TradeSafe\Helpers\TradeSafeApiClient();
		$order  = new WC_Order( $post_id );

		$transaction = $client->getTransaction( $order->get_meta( 'tradesafe_transaction_id', true ) );

		try {
			$client->allocationCompleteDelivery( $transaction['allocations'][0]['id'] );
		} catch ( \Exception $e ) {
			$logger = wc_get_logger();
			$logger->error( $e->getMessage() . ': ' . $e->getErrorDetails()['message'] ?? null, array( 'source' => 'tradesafe-payment-gateway' ) );

			$message = sprintf( 'Order could not be marked as delivered. Reason: %s', $e->getMessage() );

			$order->add_order_note( $message );
		}

		$url = add_query_arg(
			array(
				'post'   => $post_id,
				'action' => 'edit',
			),
			admin_url( 'post.php' )
		);

		wp_redirect( $url );
		exit;
	}
}
