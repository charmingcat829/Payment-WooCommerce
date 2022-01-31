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
		$banks              = $client->getEnum( 'UniversalBranchCode' );
		$bank_account_types = $client->getEnum( 'BankAccountType' );

		if ( $token_id ) {
			$token_data = $client->getToken( $token_id );

			$account_number = $token_data['bankAccount']['accountNumber'] ?? '';
			$account_type   = $token_data['bankAccount']['accountType'] ?? '';
			$bank_code      = $token_data['bankAccount']['bank'] ?? '';
		} else {
			$account_number = '';
			$account_type   = '';
			$bank_code      = '';
		}

		?>
		<div class="dokan-form-group">
			<div class="dokan-w8">
				<input name="settings[tradesafe][account_number]" value="<?php echo esc_attr( $account_number ); ?>" class="dokan-form-control" placeholder="<?php esc_attr_e( 'Your bank account number', 'tradesafe-payment-gateway' ); ?>" type="text">
			</div>
		</div>

		<div class="dokan-form-group">
			<div class="dokan-w8">
				<select name="settings[tradesafe][bank_name]" class="dokan-form-control">
					<option value="">Your bank name</option>
					<?php
					foreach ( $banks as $bank => $description ) {
						if ( $bank !== $bank_code ) {
							print '<option value="' . $bank . '">' . $description . '</option>';
						} else {
							print '<option value="' . $bank . '" selected="selected">' . $description . '</option>';
						}
					}
					?>
				</select>
			</div>
		</div>

		<div class="dokan-form-group">
			<div class="dokan-w8">
				<select name="settings[tradesafe][account_type]" class="dokan-form-control">
					<option value="">Your bank account type</option>
					<?php
					foreach ( $bank_account_types as $type => $description ) {
						if ( $type !== $account_type ) {
							print '<option value="' . $type . '">' . $description . '</option>';
						} else {
							print '<option value="' . $type . '" selected="selected">' . $description . '</option>';
						}
					}
					?>
				</select>
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

				if ( empty( $post_data['settings']['tradesafe']['account_number'] ) ) {
					wp_send_json_error( 'Invalid Account Number' );
				}

				if ( ! is_numeric( $post_data['settings']['tradesafe']['account_number'] ) ) {
					wp_send_json_error( 'Invalid Account Number' );
				}

				if ( empty( $post_data['settings']['tradesafe']['account_type'] ) ) {
					wp_send_json_error( 'Invalid Account Type' );
				}

				if ( empty( $post_data['settings']['tradesafe']['bank_name'] ) ) {
					wp_send_json_error( 'Invalid Bank' );
				}

				$client     = new \TradeSafe\Helpers\TradeSafeApiClient();
				$token_id   = get_user_meta( $store_id, tradesafe_token_meta_key(), true );
				$token_data = $client->getToken( $token_id );

				$bank_account = array(
					'accountNumber' => sanitize_text_field( $post_data['settings']['tradesafe']['account_number'] ),
					'accountType'   => sanitize_text_field( $post_data['settings']['tradesafe']['account_type'] ),
					'bank'          => sanitize_text_field( $post_data['settings']['tradesafe']['bank_name'] ),
				);

				$user         = ! empty( $token_data['user'] ) ? $token_data['user'] : null;
				$organization = ! empty( $token_data['organization'] ) ? $token_data['organization'] : null;

				try {
					$client->updateToken( $token_id, $user, $organization, $bank_account );

					return;
				} catch ( \Exception $e ) {
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
