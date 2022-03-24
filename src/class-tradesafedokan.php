<?php
/**
 * Dokan specific integration for the TradeSafe Payment Gateway.
 *
 * @package TradeSafe Payment Gateway
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class TradeSafe Dokan.
 */
class TradeSafeDokan {


	/**
	 * Initialize the plugin and load the actions and filters.
	 */
	public static function init() {
		// Actions
		add_action( 'dokan_store_profile_saved', array( 'TradeSafeDokan', 'save_withdraw_method' ), 10, 2 );
		add_action( 'dokan_after_withdraw_request', array( 'TradeSafeDokan', 'after_withdraw_request' ), 10, 3 );
		add_action( 'dokan_withdraw_content', array( 'TradeSafeDokan', 'show_tradesafe_balance' ), 5 );
		add_action( 'dokan_dashboard_left_widgets', array( 'TradeSafeDokan', 'balance_widget' ), 11 );

		// Filters
		add_filter( 'dokan_withdraw_methods', array( 'TradeSafeDokan', 'add_custom_withdraw_methods' ) );
		add_filter( 'dokan_get_seller_active_withdraw_methods', array( 'TradeSafeDokan', 'active_payment_methods' ) );
		add_filter( 'dokan_withdraw_is_valid_request', array( 'TradeSafeDokan', 'withdraw_is_valid_request' ), 10, 2 );

		if ( tradesafe_has_dokan() ) {
			// Add scripts
			wp_enqueue_script( 'tradesafe-payment-gateway-withdrawal', TRADESAFE_PAYMENT_GATEWAY_BASE_DIR . '/assets/js/withdrawal.js', array( 'jquery' ), WC_GATEWAY_TRADESAFE_VERSION, true );
		}
	}

	/**
	 * Get default withdraw methods
	 *
	 * @return array
	 */
	public static function add_custom_withdraw_methods( $methods ) {
		$methods['tradesafe'] = array(
			'title'    => __( 'TradeSafe Escrow', 'tradesafe-payment-gateway' ),
			'callback' => array( 'TradeSafeDokan', 'dokan_withdraw_method' ),
		);

		return $methods;
	}

	/**
	 * Callback for TradeSafe in store settings
	 *
	 * @param array $store_settings
	 * @global WP_User $current_user
	 */
	public static function dokan_withdraw_method( $store_settings ) {
		$client             = new \TradeSafe\Helpers\TradeSafeApiClient();
		$user               = wp_get_current_user();
		$token_id           = get_user_meta( $user->ID, tradesafe_token_meta_key(), true );
		$settings           = get_option( 'woocommerce_tradesafe_settings', array() );
		$banks              = $client->getEnum( 'UniversalBranchCode' );
		$bank_account_types = $client->getEnum( 'BankAccountType' );
		$organization_types = $client->getEnum( 'OrganizationType' );
		$intervals          = $client->getEnum( 'PayoutInterval' );

		if ( $token_id ) {
			$token_data = $client->getToken( $token_id );

			$given_name  = $token_data['user']['givenName'] ?? '';
			$family_name = $token_data['user']['familyName'] ?? '';
			$email       = $token_data['user']['email'] ?? '';
			$mobile      = $token_data['user']['mobile'] ?? '';
			$id_number   = $token_data['user']['idNumber'] ?? '';

			$organization_name         = $token_data['organization']['name'] ?? '';
			$organization_trade_name   = $token_data['organization']['tradeName'] ?? '';
			$organization_type         = $token_data['organization']['type'] ?? '';
			$organization_registration = $token_data['organization']['registration'] ?? '';
			$organization_tax_number   = $token_data['organization']['taxNumber'] ?? '';

			$account_number = $token_data['bankAccount']['accountNumber'] ?? '';
			$account_type   = $token_data['bankAccount']['accountType'] ?? '';
			$bank_code      = $token_data['bankAccount']['bank'] ?? '';

			$interval = $token_data['settings']['payout']['interval'];
		} else {
			$given_name  = '';
			$family_name = '';
			$email       = $user->user_email;
			$mobile      = '';
			$id_number   = '';

			$organization_name         = '';
			$organization_trade_name   = '';
			$organization_type         = '';
			$organization_registration = '';
			$organization_tax_number   = '';

			$account_number = '';
			$account_type   = '';
			$bank_code      = '';

			$interval = $settings['payout_method'];
		}

		?>

		<div class="dokan-form-group">
			<div class="dokan-w12 dokan-text-left">
				<div class="checkbox">
					<label>
						<input name="settings[tradesafe][is_organization]" value="no" type="hidden">
						<input id="is_organization" name="settings[tradesafe][is_organization]" value="yes"
							   type="checkbox" <?php echo $organization_name !== '' ? 'checked' : ''; ?>> Is this
						account for an organisation?
					</label>
				</div>
			</div>
		</div>

		<div class="dokan-form-group dokan-text-left" id="personal-details">
			<label>Personal Details</label>
			<div class="dokan-form-group">
				<div class="dokan-w12">
					<input name="settings[tradesafe][given_name]" value="<?php echo esc_attr( $given_name ); ?>"
						   class="dokan-form-control"
						   placeholder="<?php esc_attr_e( 'First Name', 'tradesafe-payment-gateway' ); ?>" type="text">
				</div>
			</div>

			<div class="dokan-form-group">
				<div class="dokan-w12">
					<input name="settings[tradesafe][family_name]" value="<?php echo esc_attr( $family_name ); ?>"
						   class="dokan-form-control"
						   placeholder="<?php esc_attr_e( 'Last Name', 'tradesafe-payment-gateway' ); ?>" type="text">
				</div>
			</div>

			<div class="dokan-form-group">
				<div class="dokan-w12">
					<input name="settings[tradesafe][email]" value="<?php echo esc_attr( $email ); ?>"
						   class="dokan-form-control"
						   placeholder="<?php esc_attr_e( 'Email', 'tradesafe-payment-gateway' ); ?>" type="email">
				</div>
			</div>

			<div class="dokan-form-group">
				<div class="dokan-w12">
					<input name="settings[tradesafe][mobile]" value="<?php echo esc_attr( $mobile ); ?>"
						   class="dokan-form-control"
						   placeholder="<?php esc_attr_e( 'Mobile', 'tradesafe-payment-gateway' ); ?>" type="tel">
				</div>
			</div>

			<div class="dokan-form-group toggle-id-number">
				<div class="dokan-w12">
					<input name="settings[tradesafe][id_number]" value="<?php echo esc_attr( $id_number ); ?>"
						   class="dokan-form-control"
						   placeholder="<?php esc_attr_e( 'ID Number', 'tradesafe-payment-gateway' ); ?>" type="text">
				</div>
			</div>
		</div>

		<div class="dokan-form-group dokan-text-left" id="organization-details">
			<label>Organisation Details</label>
			<div class="dokan-form-group">
				<div class="dokan-w12">
					<input name="settings[tradesafe][organization_name]"
						   value="<?php echo esc_attr( $organization_name ); ?>" class="dokan-form-control"
						   placeholder="<?php esc_attr_e( 'Name', 'tradesafe-payment-gateway' ); ?>" type="text">
				</div>
			</div>

			<div class="dokan-form-group">
				<div class="dokan-w12">
					<select name="settings[tradesafe][organization_type]" class="dokan-form-control">
						<option value="" hidden="hidden">Business Type</option>
						<?php
						foreach ( $organization_types as $type => $description ) {
							if ( $type !== $organization_type ) {
								print '<option value="' . $type . '">' . $description . '</option>';
							} else {
								print '<option value="' . $type . '" selected="selected">' . $description . '</option>';
							}
						}
						?>
					</select>
				</div>
			</div>

			<div class="dokan-form-group">
				<div class="dokan-w12">
					<input name="settings[tradesafe][organization_trade_name]"
						   value="<?php echo esc_attr( $organization_trade_name ); ?>" class="dokan-form-control"
						   placeholder="<?php esc_attr_e( 'Trade Name', 'tradesafe-payment-gateway' ); ?>" type="text">
				</div>
			</div>

			<div class="dokan-form-group">
				<div class="dokan-w12">
					<input name="settings[tradesafe][organization_registration]"
						   value="<?php echo esc_attr( $organization_registration ); ?>" class="dokan-form-control"
						   placeholder="<?php esc_attr_e( 'Registration Number', 'tradesafe-payment-gateway' ); ?>"
						   type="text">
					<p class="description">If registering as a sole prop you must enter your ID number in place of a
						business registration number.</p>
				</div>
			</div>

			<div class="dokan-form-group">
				<div class="dokan-w12">
					<input name="settings[tradesafe][organization_tax_number]"
						   value="<?php echo esc_attr( $organization_tax_number ); ?>" class="dokan-form-control"
						   placeholder="<?php esc_attr_e( 'Vat Number', 'tradesafe-payment-gateway' ); ?>" type="text">
				</div>
			</div>
		</div>

		<?php if ( ! is_null( $account_number ) ) : ?>
		<div id="change_details_form" class="dokan-form-group">
			<div class="dokan-w12 dokan-text-left">
				<div class="checkbox">
					<label>
						<input name="settings[tradesafe][update_banking_details]" value="no" type="hidden">
						<input id="change_details" name="settings[tradesafe][update_banking_details]" value="yes"
							   type="checkbox"> I would like to change my banking details
					</label>
				</div>
			</div>
		</div>
		<?php endif; ?>

		<div class="dokan-form-group dokan-text-left <?php echo ! is_null( $account_number ) ? 'hidden' : ''; ?>" id="banking-details">
			<label>Banking Details</label>
			<div class="dokan-form-group">
				<div class="dokan-w12">
					<input name="settings[tradesafe][account_number]" value="" class="dokan-form-control"
						   placeholder="<?php esc_attr_e( 'Your bank account number', 'tradesafe-payment-gateway' ); ?>"
						   type="text">
				</div>
			</div>

			<div class="dokan-form-group">
				<div class="dokan-w12">
					<select name="settings[tradesafe][bank_name]" class="dokan-form-control">
						<option value="" selected="selected">Your bank name</option>
						<?php
						foreach ( $banks as $bank => $description ) {
							print '<option value="' . $bank . '">' . $description . '</option>';
						}
						?>
					</select>
				</div>
			</div>

			<div class="dokan-form-group">
				<div class="dokan-w12">
					<select name="settings[tradesafe][account_type]" class="dokan-form-control">
						<option value="" selected="selected">Your bank account type</option>
						<?php
						foreach ( $bank_account_types as $type => $description ) {
							print '<option value="' . $type . '">' . $description . '</option>';
						}
						?>
					</select>
				</div>
			</div>
		</div>

		<div class="dokan-form-group dokan-text-left" id="payout-interval">
			<label>Automatic Payout Interval</label>

			<div class="dokan-form-group">
				<div class="dokan-w12">
					<select name="settings[tradesafe][payout_interval]" class="dokan-form-control">
						<?php
						foreach ( $intervals as $interval_key => $description ) {
							if ( $interval === $interval_key ) {
								print '<option value="' . $interval_key . '" selected="selected">' . $description . '</option>';
							} else {
								print '<option value="' . $interval_key . '">' . $description . '</option>';
							}
						}
						?>
					</select>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Save store settings
	 *
	 * @return void
	 */
	public static function save_withdraw_method( $store_id, $dokan_settings ) {
		$post_data = wp_unslash( $_POST );

		if ( wp_verify_nonce( $post_data['_wpnonce'], 'dokan_payment_settings_nonce' ) ) {
			if ( isset( $post_data['settings']['tradesafe'] ) ) {

				// Personal Details
				if ( empty( $post_data['settings']['tradesafe']['given_name'] ) ) {
					wp_send_json_error( 'First name is required' );
				}

				if ( empty( $post_data['settings']['tradesafe']['family_name'] ) ) {
					wp_send_json_error( 'Last name is required' );
				}

				if ( empty( $post_data['settings']['tradesafe']['email'] ) ) {
					wp_send_json_error( 'Email is required' );
				}

				if ( empty( $post_data['settings']['tradesafe']['mobile'] ) ) {
					wp_send_json_error( 'Mobile number is required' );
				}

				if ( 'yes' !== $post_data['settings']['tradesafe']['is_organization'] ) {
					if ( empty( $post_data['settings']['tradesafe']['id_number'] ) ) {
						wp_send_json_error( 'ID number is required' );
					}
				} else {
					if ( empty( $post_data['settings']['tradesafe']['organization_name'] ) ) {
						wp_send_json_error( 'Organisation name is required' );
					}

					if ( empty( $post_data['settings']['tradesafe']['organization_type'] ) ) {
						wp_send_json_error( 'Organisation type is required' );
					}

					if ( empty( $post_data['settings']['tradesafe']['organization_registration'] ) ) {
						wp_send_json_error( 'Organisation registration number is required' );
					}
				}

				// Banking Details
				if ( ! empty( $post_data['settings']['tradesafe']['account_number'] )
					&& ! is_numeric( $post_data['settings']['tradesafe']['account_number'] ) ) {
					wp_send_json_error( 'Invalid Account Number' );
				}

				if ( ! empty( $post_data['settings']['tradesafe']['account_number'] )
					&& empty( $post_data['settings']['tradesafe']['bank_name'] ) ) {
					wp_send_json_error( 'Invalid Bank' );
				}

				if ( ! empty( $post_data['settings']['tradesafe']['account_number'] )
					&& empty( $post_data['settings']['tradesafe']['account_type'] ) ) {
					wp_send_json_error( 'Invalid Account Type' );
				}

				$client   = new \TradeSafe\Helpers\TradeSafeApiClient();
				$token_id = get_user_meta( $store_id, tradesafe_token_meta_key(), true );

				$user = array(
					'givenName'  => sanitize_text_field( $post_data['settings']['tradesafe']['given_name'] ),
					'familyName' => sanitize_text_field( $post_data['settings']['tradesafe']['family_name'] ),
					'email'      => sanitize_email( $post_data['settings']['tradesafe']['email'] ),
					'mobile'     => sanitize_text_field( $post_data['settings']['tradesafe']['mobile'] ),
				);

				$organization = null;
				if ( 'yes' !== $post_data['settings']['tradesafe']['is_organization'] ) {
					$user['idNumber']  = sanitize_text_field( $post_data['settings']['tradesafe']['id_number'] );
					$user['idType']    = 'NATIONAL';
					$user['idCountry'] = 'ZAF';
				} else {
					$organization = array(
						'name'               => $post_data['settings']['tradesafe']['organization_name'],
						'type'               => $post_data['settings']['tradesafe']['organization_type'],
						'registrationNumber' => $post_data['settings']['tradesafe']['organization_registration'],
					);

					if ( ! empty( $post_data['settings']['tradesafe']['organization_trade_name'] ) ) {
						$organization['tradeName'] = $post_data['settings']['tradesafe']['organization_trade_name'];
					}

					if ( ! empty( $post_data['settings']['tradesafe']['organization_tax_number'] ) ) {
						$organization['taxNumber'] = $post_data['settings']['tradesafe']['organization_tax_number'];
					}
				}

				$bank_account = null;
				if ( ! empty( $post_data['settings']['tradesafe']['account_number'] ) ) {
					$bank_account = array(
						'accountNumber' => sanitize_text_field( $post_data['settings']['tradesafe']['account_number'] ),
						'accountType'   => sanitize_text_field( $post_data['settings']['tradesafe']['account_type'] ),
						'bank'          => sanitize_text_field( $post_data['settings']['tradesafe']['bank_name'] ),
					);
				}

				try {
					$client->updateToken( $token_id, $user, $organization, $bank_account, sanitize_text_field( $post_data['settings']['tradesafe']['payout_interval'] ) );

					return;
				} catch ( \GraphQL\Exception\QueryError $e ) {
					wp_send_json_error( 'There was a problem updating your account details' );
				}
			}
		}

		wp_send_json_error( 'There was a problem updating your account details' );
	}


	/**
	 * Get active withdraw methods for seller.
	 *
	 * @return array
	 */
	public static function active_payment_methods( $active_payment_methods ) {
		$client     = new \TradeSafe\Helpers\TradeSafeApiClient();
		$token_id   = get_user_meta( dokan_get_current_user_id(), tradesafe_token_meta_key(), true );
		$token_data = $client->getToken( $token_id );

		if ( ! empty( $token_data['bankAccount']['accountNumber'] ) ) {
			array_push( $active_payment_methods, 'tradesafe' );
		}

		return $active_payment_methods;
	}

	/**
	 * Check if there are enough funds available in TradeSafe.
	 *
	 * @param $valid
	 * @param $args
	 * @return void|WP_Error
	 */
	public static function withdraw_is_valid_request( $valid, $args ) {
		if ( 'tradesafe' === $args['method'] ) {
			$client     = new \TradeSafe\Helpers\TradeSafeApiClient();
			$token_id   = get_user_meta( sanitize_key( $args['user_id'] ), tradesafe_token_meta_key(), true );
			$token_data = $client->getToken( $token_id );
			$amount     = (float) sanitize_text_field( $args['amount'] );

			if ( $amount > $token_data['balance'] ) {
				return new WP_Error( 'tradesafe-invalid-withdraw', 'Not enough funds available' );
			}
		}
	}

	/**
	 * Initiate withdrawal with via TradeSafe and automatically approve the request.
	 *
	 * @param $user_id
	 * @param $amount
	 * @param $method
	 * @return void
	 */
	public static function after_withdraw_request( $user_id, $amount, $method ) {
		if ( 'tradesafe' === $method ) {
			$client   = new \TradeSafe\Helpers\TradeSafeApiClient();
			$token_id = get_user_meta( sanitize_key( $user_id ), tradesafe_token_meta_key(), true );

			$withdraw_requests = dokan()->withdraw->get_withdraw_requests( sanitize_key( $user_id ) );

			foreach ( $withdraw_requests as $request ) {
				if ( $request->method === 'tradesafe' && (float) $request->amount == (float) $amount ) {
					$withdraw = new \WeDevs\Dokan\Withdraw\Withdraw( (array) $request );

					if ( $client->tokenAccountWithdraw( $token_id, (float) $amount ) === true ) {
						// TODO: Approve request
						$withdraw->set_status( dokan()->withdraw->get_status_code( 'approved' ) );
						$withdraw->save();
					} else {
						// TODO: Cancel request
						$withdraw->set_status( dokan()->withdraw->get_status_code( 'cancelled' ) );
						$withdraw->save();
					}
				}
			}
		}
	}

	/**
	 * Display the token balance on the withdrawal page.
	 *
	 * @return void
	 */
	public static function show_tradesafe_balance() {
		$client     = new \TradeSafe\Helpers\TradeSafeApiClient();
		$token_id   = get_user_meta( dokan_get_current_user_id(), tradesafe_token_meta_key(), true );
		$token_data = $client->getToken( $token_id );

		$message = sprintf( __( 'TradeSafe Escrow Balance: %s ', 'tradesafe-payment-gateway' ), wc_price( $token_data['balance'] ) );

		$message .= '<br/><small>' . sprintf( __( 'A R5 fee (excl.) is incurred for withdrawals from TradeSafe.', 'tradesafe-payment-gateway' ) ) . '</small>';

		dokan_get_template_part(
			'global/dokan',
			'message',
			array(
				'message' => $message,
			)
		);
	}

	/**
	 * Display the token balance on the withdrawal page.
	 *
	 * @return void
	 */
	public static function balance_widget() {
		$client     = new \TradeSafe\Helpers\TradeSafeApiClient();
		$token_id   = get_user_meta( dokan_get_current_user_id(), tradesafe_token_meta_key(), true );
		$token_data = $client->getToken( $token_id );

		$message = sprintf( __( 'TradeSafe Escrow Balance: %s ', 'tradesafe-payment-gateway' ), wc_price( $token_data['balance'] ) );

		dokan_get_template_part(
			'global/dokan',
			'message',
			array(
				'message' => $message,
			)
		);
	}
}
