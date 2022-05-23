<?php
/**
 * Links WooCommerce Account page to a TradeSafe user token.
 *
 * @package TradeSafe Payment Gateway
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class TradeSafeProfile
 */
class TradeSafeProfile {



	/**
	 * Initiate the class.
	 */
	public static function init() {
		if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
			return;
		}

		self::init_hooks();
	}

	/**
	 * Initializes WordPress hooks.
	 */
	private static function init_hooks() {
		// Actions.
		add_filter( 'query_vars', array( 'TradeSafeProfile', 'query_vars' ), 0 );
		// add_action( 'woocommerce_edit_account_form', array( 'TradeSafeProfile', 'edit_account_form' ) );
		// add_action( 'woocommerce_save_account_details', array( 'TradeSafeProfile', 'save_account_details' ) );
		// add_action( 'woocommerce_save_account_details_errors', array( 'TradeSafeProfile', 'save_account_details_errors' ), 10, 1 );
		add_action( 'woocommerce_checkout_update_customer', array( 'TradeSafeProfile', 'woocommerce_checkout_update_customer' ) );

		// Withdrawal Page
		if ( tradesafe_is_marketplace() && ! tradesafe_has_dokan() ) {
			// Actions.
			add_action( 'woocommerce_account_tradesafe-withdrawal_endpoint', array( 'TradeSafeProfile', 'withdrawal_endpoint' ) );

			// Filters.
			add_filter( 'woocommerce_account_menu_items', array( 'TradeSafeProfile', 'woocommerce_account_menu_items' ) );
			add_filter( 'the_title', array( 'TradeSafeProfile', 'withdrawal_endpoint_title' ) );

			// Add scripts
			wp_enqueue_script( 'tradesafe-payment-gateway-withdrawal', TRADESAFE_PAYMENT_GATEWAY_BASE_DIR . '/assets/js/withdrawal.js', array( 'jquery' ), WC_GATEWAY_TRADESAFE_VERSION, true );
		}

		if ( is_admin() ) {
			add_action( 'edit_user_profile', array( 'TradeSafeProfile', 'edit_user_profile_token' ) );
			add_action( 'show_user_profile', array( 'TradeSafeProfile', 'edit_user_profile_token' ) );
		}

		// Rewrites.
		add_rewrite_endpoint( 'tradesafe-withdrawal', EP_ROOT | EP_PAGES );
	}

	/**
	 * Add new query var.
	 *
	 * @param array $vars
	 * @return array
	 */
	public static function query_vars( array $vars ): array {
		$vars[] = 'tradesafe-withdrawal';

		return $vars;
	}

	/**
	 * Add TradeSafe fields to user account form.
	 */
	public static function edit_account_form() {
		$client = new \TradeSafe\Helpers\TradeSafeApiClient();
		$user   = wp_get_current_user();

		if ( is_null( $client ) || is_array( $client ) ) {
			echo "<table class='form-table' role='presentation'><tbody>";
			echo "<tr><th scope='row'>Error:</th><td> TradeSafe Payment Gateway not configured</td></tr>";
			echo '</tbody></table>';
			return;
		}

		$token_id           = tradesafe_get_token_id( $user->ID );
		$banks              = $client->getEnum( 'UniversalBranchCode' );
		$bank_account_types = $client->getEnum( 'BankAccountType' );
		$organization_types = $client->getEnum( 'OrganizationType' );
		$token_data         = null;

		if ( $token_id ) {
			$token_data = $client->getToken( $token_id );
		}

		include_once dirname( __DIR__ ) . '/templates/myaccount/form-tradesafe-token-user.php';
		include_once dirname( __DIR__ ) . '/templates/myaccount/form-tradesafe-token-organization.php';

		if ( isset( $token_data['bankAccount'] ) ) {
			include_once dirname( __DIR__ ) . '/templates/myaccount/view-tradesafe-token-bank-account.php';
		} else {
			include_once dirname( __DIR__ ) . '/templates/myaccount/form-tradesafe-token-bank-account.php';
		}
	}

	/**
	 * Save the details submitted by the user to their account.
	 *
	 * @param int $user_id User Id.
	 * @throws Exception
	 */
	public static function save_account_details( int $user_id ) {
		// Nonce check copied from woocommerce/includes/class-wc-form-handler.php@save_account_details.
        $nonce_value = wc_get_var($_REQUEST['save-account-details-nonce'], wc_get_var($_REQUEST['_wpnonce'], '')); // @codingStandardsIgnoreLine.
		$client      = new \TradeSafe\Helpers\TradeSafeApiClient();

		if ( ! wp_verify_nonce( $nonce_value, 'save_account_details' ) ) {
			return;
		}

		if ( empty( $_POST['action'] ) || 'save_account_details' !== $_POST['action'] || is_array( $client ) ) {
			return;
		}

		$token_id = tradesafe_get_token_id( $user_id );

		$user_info = array(
			'givenName'  => sanitize_text_field( wp_unslash( $_POST['account_first_name'] ?? null ) ),
			'familyName' => sanitize_text_field( wp_unslash( $_POST['account_last_name'] ?? null ) ),
			'email'      => sanitize_email( wp_unslash( $_POST['account_email'] ?? null ) ),
			'mobile'     => sanitize_text_field( wp_unslash( $_POST['tradesafe_token_mobile'] ?? null ) ),
			'idNumber'   => sanitize_text_field( wp_unslash( $_POST['tradesafe_token_id_number'] ?? null ) ),
			'idType'     => sanitize_text_field( wp_unslash( $_POST['tradesafe_token_id_type'] ?? null ) ),
			'idCountry'  => sanitize_text_field( wp_unslash( $_POST['tradesafe_token_id_country'] ?? null ) ),
		);

		$bank_account = null;
		$organization = null;

		if ( isset( $_POST['tradesafe_token_bank_account_number'] )
			&& ! is_null( $_POST['tradesafe_token_bank_account_number'] )
			&& isset( $_POST['tradesafe_token_bank_account_type'] )
			&& ! is_null( $_POST['tradesafe_token_bank_account_type'] )
			&& isset( $_POST['tradesafe_token_bank'] )
			&& ! is_null( $_POST['tradesafe_token_bank'] ) ) {
			$bank_account = array(
				'accountNumber' => sanitize_text_field( wp_unslash( $_POST['tradesafe_token_bank_account_number'] ?? null ) ),
				'accountType'   => sanitize_text_field( wp_unslash( $_POST['tradesafe_token_bank_account_type'] ?? null ) ),
				'bank'          => sanitize_text_field( wp_unslash( $_POST['tradesafe_token_bank'] ?? null ) ),
			);
		}

		if ( ! empty( $_POST['tradesafe_token_organization_name'] )
			&& ! empty( $_POST['tradesafe_token_organization_type'] )
			&& ! empty( $_POST['tradesafe_token_organization_registration_number'] ) ) {
			$organization = array(
				'name'               => sanitize_text_field( wp_unslash( $_POST['tradesafe_token_organization_name'] ) ),
				'tradeName'          => sanitize_text_field( wp_unslash( $_POST['tradesafe_token_organization_trading_name'] ?? null ) ),
				'type'               => sanitize_text_field( wp_unslash( $_POST['tradesafe_token_organization_type'] ) ),
				'registrationNumber' => sanitize_text_field( wp_unslash( $_POST['tradesafe_token_organization_registration_number'] ) ),
				'taxNumber'          => sanitize_text_field( wp_unslash( $_POST['tradesafe_token_organization_tax_number'] ?? null ) ),
			);
		}

		$payout_interval = 'IMMEDIATE';
		$settings        = get_option( 'woocommerce_tradesafe_settings', array() );

		if ( isset( $settings['payout_method'] ) ) {
			$payout_interval = $settings['payout_method'];
		}

		if ( $token_id ) {
			$token_data = $client->updateToken( $token_id, $user_info, $organization, $bank_account, $payout_interval );
		} else {
			$token_data = $client->createToken( $user_info, $organization, $bank_account, $payout_interval );

			update_user_meta( $user_id, tradesafe_token_meta_key(), sanitize_text_field( $token_data['id'] ) );
		}
	}

	/**
	 * Validate data before saving account details.
	 *
	 * @param array $args Arguments.
	 */
	public static function save_account_details_errors( $args ) {
		// Are any of the organization fields not empty.
		if ( ! empty( $_POST['tradesafe_token_organization_name'] )
			|| ! empty( $_POST['tradesafe_token_organization_trading_name'] )
			|| ! empty( $_POST['tradesafe_token_organization_type'] )
			|| ! empty( $_POST['tradesafe_token_organization_registration_number'] )
			|| ! empty( $_POST['tradesafe_token_organization_tax_number'] ) ) {

			// Check that optional required fields are set before trying to save.
			if ( empty( $_POST['tradesafe_token_organization_name'] )
				|| empty( $_POST['tradesafe_token_organization_type'] )
				|| empty( $_POST['tradesafe_token_organization_registration_number'] ) ) {
				$args->add( 'error', __( 'Organization details are incomplete:', 'woocommerce' ), '' );
			}

			if ( empty( $_POST['tradesafe_token_organization_name'] ) ) {
				$args->add( 'error', __( 'Organization name is missing.', 'woocommerce' ), '' );
			}

			if ( empty( $_POST['tradesafe_token_organization_type'] ) ) {
				$args->add( 'error', __( 'Organization type is missing.', 'woocommerce' ), '' );
			}

			if ( empty( $_POST['tradesafe_token_organization_registration_number'] ) ) {
				$args->add( 'error', __( 'Organization registration number is missing.', 'woocommerce' ), '' );
			}
		}
	}

	/**
	 * Create token for user on checkout if account is incomplete.
	 *
	 * @param WC_Customer $customer User account details.
	 */
	public static function woocommerce_checkout_update_customer( WC_Customer $customer ) {
		$client = new \TradeSafe\Helpers\TradeSafeApiClient();

		if ( '' === $customer->get_meta( tradesafe_token_meta_key(), true ) ) {
			$user_info = array(
				'givenName'  => $customer->first_name,
				'familyName' => $customer->last_name,
				'email'      => $customer->billing['email'],
				'mobile'     => $customer->billing['phone'],
			);

			$payout_interval = 'IMMEDIATE';
			$settings        = get_option( 'woocommerce_tradesafe_settings', array() );

			if ( isset( $settings['payout_method'] ) ) {
				$payout_interval = $settings['payout_method'];
			}

			$token_data = $client->createToken( $user_info, null, null, $payout_interval );

			$customer->update_meta_data( tradesafe_token_meta_key(), sanitize_text_field( $token_data['id'] ) );
			$customer->save_meta_data();
		}
	}

	public static function woocommerce_account_menu_items( $items ): array {
		// Search for the item position and +1 since is after the selected item key.
		$position = array_search( 'edit-account', array_keys( $items ) ) + 1;

		// New items to add to menu.
		$new_items = array(
			'tradesafe-withdrawal' => __( 'Withdrawals', 'tradesafe-payment-gateway' ),
		);

		// Insert the new item.
		$array  = array_slice( $items, 0, $position, true );
		$array += $new_items;
		$array += array_slice( $items, $position, count( $items ) - $position, true );

		return $array;
	}

	/*
	* Change endpoint title.
	*
	* @param string $title
	* @return string
	*/
	public static function withdrawal_endpoint_title( $title ) {
		global $wp_query;

		$is_endpoint = isset( $wp_query->query_vars['tradesafe-withdrawal'] );

		if ( $is_endpoint && ! is_admin() && is_main_query() && in_the_loop() && is_account_page() ) {
			// New page title.
			$title = __( 'Withdrawals', 'tradesafe-payment-gateway' );

			remove_filter( 'the_title', array( 'TradeSafeProfile', 'withdrawal_endpoint_title' ) );
		}

		return $title;
	}

	public static function withdrawal_endpoint() {
		$dir             = plugin_dir_path( __FILE__ );
		$tokenId         = tradesafe_get_token_id( get_current_user_id() );
		$client          = new \TradeSafe\Helpers\TradeSafeApiClient();
		$token           = $client->getToken( $tokenId );
		$form_errors     = null;
		$is_organization = false;
		$pending         = null;

		if ( ! is_null( $token['organization'] ) ) {
			$is_organization = true;
		}

		// TODO: Get pending request?

		if ( ! empty( $_POST ) ) {
            $nonce_value = wc_get_var($_REQUEST['tradesafe-update-token-nonce'], wc_get_var($_REQUEST['_wpnonce'], '')); // @codingStandardsIgnoreLine.

			if ( wp_verify_nonce( $nonce_value, 'tradesafe_update_token' ) ) {
				if ( ! empty( $_POST['withdrawal_submit'] ) && ! empty( $_POST['tradesafe_withdrawal_request'] ) ) {
					self::process_withdrawal_request( $client, $tokenId );
				}

				if ( ! empty( $_POST['update_token_submit'] ) ) {
					$validation = self::validate_token_details( $_POST );

					if ( is_wp_error( $validation ) && $validation->has_errors() ) {
						$form_errors = $validation->get_error_messages();

						if ( isset( $_POST['is_organization'] ) && 'on' === $_POST['is_organization'] ) {
							$is_organization = true;
						}

						$token['user']['givenName']  = sanitize_text_field( $_POST['tradesafe_token_given_name'] ?? null );
						$token['user']['familyName'] = sanitize_text_field( $_POST['tradesafe_token_family_name'] ?? null );
						$token['user']['email']      = sanitize_email( $_POST['tradesafe_token_email'] ?? null );
						$token['user']['mobile']     = sanitize_text_field( $_POST['tradesafe_token_mobile'] ?? null );
						$token['user']['idNumber']   = sanitize_text_field( $_POST['tradesafe_token_id_number'] ?? null );

						$token['organization']['name']         = sanitize_text_field( $_POST['tradesafe_token_organization_name'] ?? null );
						$token['organization']['type']         = sanitize_text_field( $_POST['tradesafe_token_organization_type'] ?? null );
						$token['organization']['tradeName']    = sanitize_text_field( $_POST['tradesafe_token_organization_trading_name'] ?? null );
						$token['organization']['registration'] = sanitize_text_field( $_POST['tradesafe_token_organization_registration_number'] ?? null );
						$token['organization']['taxNumber']    = sanitize_text_field( $_POST['tradesafe_token_organization_tax_number'] ?? null );

						$token['bankAccount']['accountNumber'] = sanitize_text_field( $_POST['tradesafe_token_bank_account_number'] ?? null );
						$token['bankAccount']['accountType']   = sanitize_text_field( $_POST['tradesafe_token_bank_account_type'] ?? null );
						$token['bankAccount']['bank']          = sanitize_text_field( $_POST['tradesafe_token_bank'] ?? null );
					} else {
						$user = array(
							'givenName'  => sanitize_text_field( $_POST['tradesafe_token_given_name'] ?? null ),
							'familyName' => sanitize_text_field( $_POST['tradesafe_token_family_name'] ?? null ),
							'email'      => sanitize_text_field( $_POST['tradesafe_token_email'] ?? null ),
							'mobile'     => sanitize_text_field( $_POST['tradesafe_token_mobile'] ?? null ),
						);

						if ( ! empty( $_POST['tradesafe_token_id_number'] ) ) {
							$user += array(
								'idNumber'  => sanitize_text_field( $_POST['tradesafe_token_id_number'] ),
								'idType'    => 'NATIONAL',
								'idCountry' => 'ZAF',
							);
						}

						$organization = null;

						if ( ! empty( $_POST['tradesafe_token_organization_name'] ) ) {
							$organization = array(
								'name'         => sanitize_text_field( $_POST['tradesafe_token_organization_name'] ?? null ),
								'type'         => sanitize_text_field( $_POST['tradesafe_token_organization_type'] ?? null ),
								'tradeName'    => sanitize_text_field( $_POST['tradesafe_token_organization_trading_name'] ?? null ),
								'registration' => sanitize_text_field( $_POST['tradesafe_token_organization_registration_number'] ?? null ),
								'taxNumber'    => sanitize_text_field( $_POST['tradesafe_token_organization_tax_number'] ?? null ),
							);
						}

						$bank_account = null;

						if ( ! empty( $_POST['tradesafe_token_bank_account_number'] )
							&& ! empty( $_POST['tradesafe_token_bank_account_type'] )
							&& ! empty( $_POST['tradesafe_token_bank'] ) ) {
							$bank_account = array(
								'accountNumber' => sanitize_text_field( $_POST['tradesafe_token_bank_account_number'] ?? null ),
								'accountType'   => sanitize_text_field( $_POST['tradesafe_token_bank_account_type'] ?? null ),
								'bank'          => sanitize_text_field( $_POST['tradesafe_token_bank'] ?? null ),
							);
						}

						try {
							$token = $client->updateToken( $tokenId, $user, $organization, $bank_account );
						} catch ( \GraphQL\Exception\QueryError $e ) {
							$error_message = $e->getMessage();

							if ( WP_DEBUG ) {
								$error_message .= "\n\n<pre>" . json_encode( $e->getErrorDetails(), JSON_PRETTY_PRINT ) . '</pre>';
							}

							$logger = wc_get_logger();
							$logger->error( $error_message, array( 'source' => 'tradesafe-payment-gateway' ) );

							error_log( $error_message );
						}
					}
				}
			}
		}

		wc_get_template(
			'myaccount/withdrawal.php',
			array(
				'token'              => $token,
				'errors'             => $form_errors,
				'organization_types' => $client->getEnum( 'OrganizationType' ),
				'bank_account_types' => $client->getEnum( 'BankAccountType' ),
				'banks'              => $client->getEnum( 'UniversalBranchCode' ),
				'payout_interval'    => $client->getEnum( 'PayoutInterval' ),
				'is_organization'    => $is_organization,
				'pending'            => $pending,
			),
			'',
			$dir . '../templates/'
		);
	}

	public static function validate_token_details( $fields ): WP_Error {
		$error = new WP_Error();

		if ( empty( $fields['tradesafe_token_given_name'] ) ) {
			$error->add( 'tradesafe_token_given_name', 'First name is required.' );
		}

		if ( empty( $fields['tradesafe_token_given_name'] ) ) {
			$error->add( 'tradesafe_token_family_name', 'Last name is required.' );
		}

		if ( ! empty( $fields['is_organization'] ) && 'on' === $fields['is_organization'] ) {
			if ( empty( $fields['tradesafe_token_organization_name'] ) ) {
				$error->add( 'tradesafe_token_organization_name', 'Organization name is required.' );
			}

			if ( empty( $fields['tradesafe_token_organization_type'] ) ) {
				$error->add( 'tradesafe_token_organization_type', 'Organization type is required.' );
			}

			if ( empty( $fields['tradesafe_token_organization_registration_number'] ) ) {
				$error->add( 'tradesafe_token_organization_registration_number', 'Organization registration number is required.' );
			}
		} else {
			if ( empty( $fields['tradesafe_token_id_number'] ) ) {
				$error->add( 'tradesafe_token_id_number', 'ID number is required.' );
			}
		}

		if ( empty( $fields['tradesafe_token_bank'] ) ) {
			$error->add( 'tradesafe_token_bank', 'A bank must be selected.' );
		}

		if ( empty( $fields['tradesafe_token_bank_account_number'] ) ) {
			$error->add( 'tradesafe_token_bank_account_number', 'Bank account number is required.' );
		}

		if ( empty( $fields['tradesafe_token_bank_account_type'] ) ) {
			$error->add( 'tradesafe_token_bank_account_type', 'Bank account type is required.' );
		}

		return $error;
	}

	public static function process_withdrawal_request( \TradeSafe\Helpers\TradeSafeApiClient $client, $tokenId ) {
		try {
			$client->tokenAccountWithdraw( $tokenId, (float) sanitize_text_field( $_POST['tradesafe_withdrawal_request'] ) );
		} catch ( \GraphQL\Exception\QueryError $e ) {
			$error = $e->getErrorDetails();

			print '<div class="woocommerce-error">';
			esc_html_e( $error['message'] . '. ' . $error['extensions']['reason'] );
			print '</div>';
		}
	}

	/**
	 * @throws Exception
	 */
	public static function edit_user_profile_token( WP_User $user ) {
		$token_id = tradesafe_get_token_id( $user->ID );

		echo '<h2>TradeSafe Details</h2>';

		if ( $token_id ) {
			$client     = new \TradeSafe\Helpers\TradeSafeApiClient();
			$token_data = $client->getToken( $token_id );

			ob_start();
			?>
			<table class="form-table" role="presentation">
				<tbody>
				<tr id="token_id">
					<th><label>Token ID</label></th>
					<td><?php echo sanitize_text_field( $token_data['id'] ); ?></td>
				</tr>
				<tr id="reference">
					<th><label>Reference</label></th>
					<td><?php echo sanitize_text_field( $token_data['reference'] ); ?></td>
				</tr>
				<tr id="bank_account">
					<th><label>Has Bank Account assigned</label></th>
					<td><?php echo ! empty( $token_data['bankAccount'] ) ? 'Yes' : 'No'; ?></td>
				</tr>
				<tr id="payout">
					<th><label>Payout Method</label></th>
					<td><?php echo sanitize_text_field( $token_data['settings']['payout']['interval'] ); ?></td>
				</tr>
				<tr id="balance">
					<th><label>Account Balance</label></th>
					<td><?php echo 'R ' . number_format( sanitize_text_field( $token_data['balance'] ), 2, '.', ' ' ); ?></td>
				</tr>
				</tbody>
			</table>
			<?php
			ob_end_flush();
		} else {
			echo '<p>No information available.</p>';
		}
	}
}
