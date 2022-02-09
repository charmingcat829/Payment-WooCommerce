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
		add_action( 'woocommerce_edit_account_form', array( 'TradeSafeProfile', 'edit_account_form' ) );
		add_action( 'woocommerce_save_account_details', array( 'TradeSafeProfile', 'save_account_details' ) );
		add_action( 'woocommerce_save_account_details_errors', array( 'TradeSafeProfile', 'save_account_details_errors' ), 10, 1 );
		add_action( 'woocommerce_checkout_update_customer', array( 'TradeSafeProfile', 'woocommerce_checkout_update_customer' ) );
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

		$token_id           = get_user_meta( $user->ID, tradesafe_token_meta_key(), true );
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

		$token_id = get_user_meta( $user_id, tradesafe_token_meta_key(), true );

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
}
