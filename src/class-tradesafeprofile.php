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
		add_action( 'woocommerce_checkout_update_customer', array( 'TradeSafeProfile', 'woocommerce_checkout_update_customer' ) );

		// Filters.
		add_filter( 'woocommerce_checkout_fields', array( 'TradeSafeProfile', 'woocommerce_checkout_fields' ), 10, 1 );
	}

	/**
	 * Add TradeSafe fields to user account form.
	 */
	public static function edit_account_form() {
		$client = tradesafe_api_client();
		$user   = wp_get_current_user();

		if ( is_null( $client ) ) {
			echo "<table class='form-table' role='presentation'><tbody>";
			echo "<tr><th scope='row'>Error:</th><td> TradeSafe Payment Gateway not configured</td></tr>";
			echo '</tbody></table>';
			return;
		}

		$meta_key = 'tradesafe_token_id';

		if ( get_option( 'tradesafe_production_mode' ) ) {
			$meta_key = 'tradesafe_prod_token_id';
		}

		$token_id           = get_user_meta( $user->ID, $meta_key, true );
		$banks              = $client->getEnums( 'UniversalBranchCode' );
		$bank_account_types = $client->getEnums( 'BankAccountType' );
		$organization_types = $client->getEnums( 'OrganizationType' );
		$token_data         = null;

		if ( $token_id ) {
			$token_data = $client->getToken( $token_id );
		}

		if ( is_null( $client ) ) {
			echo "<table class='form-table' role='presentation'><tbody>";
			echo "<tr><th scope='row'>Error:</th><td> Could not connect to server</td></tr>";
			echo '</tbody></table>';
			return;
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
		$client      = tradesafe_api_client();

		if ( ! wp_verify_nonce( $nonce_value, 'save_account_details' ) ) {
			return;
		}

		if ( empty( $_POST['action'] ) || 'save_account_details' !== $_POST['action'] || is_null( $client ) ) {
			return;
		}

		$meta_key = 'tradesafe_token_id';

		if ( get_option( 'tradesafe_production_mode' ) ) {
			$meta_key = 'tradesafe_prod_token_id';
		}

		$token_id = get_user_meta( $user_id, $meta_key, true );

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

		if ( isset( $_POST['tradesafe_token_organization_name'] )
			&& isset( $_POST['tradesafe_token_organization_type'] )
			&& isset( $_POST['tradesafe_token_organization_registration_number'] ) ) {
			$organization = array(
				'name'               => sanitize_text_field( wp_unslash( $_POST['tradesafe_token_organization_name'] ?? null ) ),
				'tradeName'          => sanitize_text_field( wp_unslash( $_POST['tradesafe_token_organization_trading_name'] ?? null ) ),
				'type'               => sanitize_text_field( wp_unslash( $_POST['tradesafe_token_organization_type'] ?? null ) ),
				'registrationNumber' => sanitize_text_field( wp_unslash( $_POST['tradesafe_token_organization_registration_number'] ?? null ) ),
				'taxNumber'          => sanitize_text_field( wp_unslash( $_POST['tradesafe_token_organization_tax_number'] ?? null ) ),
			);
		}

		if ( $token_id ) {
			$token_data = $client->updateToken( $token_id, $user_info, $organization, $bank_account );
		} else {
			$token_data = $client->createToken( $user_info, $organization, $bank_account );

			update_user_meta( $user_id, $meta_key, sanitize_text_field( $token_data['id'] ) );
		}
	}

	/**
	 * Create token for user on checkout if account is incomplete.
	 *
	 * @param WC_Customer $customer User account details.
	 */
	public static function woocommerce_checkout_update_customer( WC_Customer $customer ) {
		$client = tradesafe_api_client();

		if ( '1' === get_option( 'tradesafe_require_id_number' ) && null !== $customer->get_meta( 'billing_id_number', true ) ) {
			$user_info = array(
				'givenName'  => $customer->first_name,
				'familyName' => $customer->last_name,
				'email'      => $customer->billing['email'],
				'mobile'     => $customer->billing['phone'],
				'idNumber'   => $customer->get_meta( 'billing_id_number', true ),
				'idType'     => 'NATIONAL',
				'idCountry'  => 'ZAF',
			);

			$meta_key = 'tradesafe_token_id';

			if ( get_option( 'tradesafe_production_mode' ) ) {
				$meta_key = 'tradesafe_prod_token_id';
			}

			if ( '' === $customer->get_meta( $meta_key, true ) ) {
				$token_data = $client->createToken( $user_info, null, null );
			} else {
				$token_data = $client->updateToken( $customer->get_meta( $meta_key, true ), $user_info, null, null );
			}

			$customer->update_meta_data( $meta_key, sanitize_text_field( $token_data['id'] ) );
			$customer->save_meta_data();
		}
	}

	/**
	 * Add ID number to checkout form if required.
	 *
	 * @param array $fields List of fields to display on checkout.
	 * @return array
	 */
	public static function woocommerce_checkout_fields( array $fields ) {
		$user     = wp_get_current_user();
		$meta_key = 'tradesafe_token_id';

		if ( get_option( 'tradesafe_production_mode' ) ) {
			$meta_key = 'tradesafe_prod_token_id';
		}

		$token_id = get_user_meta( $user->ID, $meta_key, true );

		if ( '1' === get_option( 'tradesafe_require_id_number' ) && '' === $token_id ) {
			$fields['billing']['billing_id_number'] = array(
				'label'       => __( 'ID Number', 'tradesafe-payment-gateway' ),
				'placeholder' => _x( 'ID Number', 'placeholder', 'tradesafe-payment-gateway' ),
				'required'    => true,
				'class'       => array( 'form-row-wide' ),
				'clear'       => true,
			);
		}

		return $fields;
	}
}
