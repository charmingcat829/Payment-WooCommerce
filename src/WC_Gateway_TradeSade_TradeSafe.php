<?php

class WC_Gateway_TradeSade_TradeSafe extends WC_Gateway_TradeSafe_Base
{
    /**
     * Version
     *
     * @var string
     */
    public $version;

    /**
     * Constructor
     */
    public function __construct() {
        $this->version = WC_GATEWAY_TRADESAFE_VERSION;
        $this->id = 'tradesafe';
        $this->method_title       = __( 'TradeSafe', 'woocommerce-gateway-tradesafe' );
        /* translators: 1: a href link 2: closing href */
        $this->method_description = sprintf( __( '%1$sTradeSafe%2$s', 'woocommerce-gateway-tradesafe' ), '<a href="http://www.tradesafe.co.za/">', '</a>' );
        $this->icon               = WP_PLUGIN_URL . '/' . plugin_basename( dirname( dirname( __FILE__ ) ) ) . '/assets/images/icon.svg';
        $this->debug_email        = get_option( 'admin_email' );
        $this->available_countries  = array( 'ZA' );
        $this->available_currencies = (array)apply_filters('woocommerce_gateway_tradesafe_available_currencies', array( 'ZAR' ) );

        // Supported functionality
        $this->supports = array(
            'products',
        );

        $this->init_form_fields();
        $this->init_settings();

        // Setup default merchant data.
        $this->enabled          = $this->is_valid_for_use() ? 'yes': 'no'; // Check if the base currency supports this gateway.
        $this->production       = $this->get_option( 'production' );
        $this->title            = $this->get_option( 'title' );
        $this->description      = $this->get_option( 'description' );
        $this->client_id        = $this->get_option( 'client_id' );
        $this->client_secret    = $this->get_option( 'client_secret' );
        $this->client_callback  = $this->get_option( 'client_callback' );

        $this->base_url              = 'https://api.tradesafe.co.za/';

        add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
        add_action( 'woocommerce_receipt_tradesafe', array( $this, 'receipt_page' ) );
        add_action( 'admin_notices', array( $this, 'admin_notices' ) );
    }

    /**
     * is_valid_for_use()
     *
     * Check if this gateway is enabled and available in the base currency being traded with.
     *
     * @since 1.0.0
     * @return bool
     */
    public function is_valid_for_use() {
        $is_available          = false;
        $is_available_currency = in_array( get_woocommerce_currency(), $this->available_currencies );

        if ( $is_available_currency && $this->get_option( 'client_id' ) && $this->get_option( 'client_secret' ) ) {
            $is_available = true;
        }

        if ("yes" === $this->get_option( 'production' ) ) {
            $is_available = false;
        }

        if ("no" === $this->get_option( 'enabled' ) ) {
            $is_available = false;
        }

        return $is_available;
    }

    public function init_form_fields()
    {
        $this->form_fields = array(
            'enabled' => array(
                'title'       => __( 'Enable/Disable', 'woocommerce-gateway-tradesafe' ),
                'label'       => __( 'Enable TradeSafe', 'woocommerce-gateway-tradesafe' ),
                'type'        => 'checkbox',
                'description' => __( 'This controls whether or not this gateway is enabled within WooCommerce.', 'woocommerce-gateway-tradesafe' ),
                'default'     => 'yes',
                'desc_tip'    => true,
            ),
            'production' => array(
                'title'       => __( 'Enable Production Mode', 'woocommerce-gateway-tradesafe' ),
                'type'        => 'checkbox',
                'description' => __( 'Place the payment gateway in production mode. Requires Application Approval to function.', 'woocommerce-gateway-tradesafe' ),
                'default'     => 'no',
            ),
            'title' => array(
                'title'       => __( 'Title', 'woocommerce-gateway-tradesafe' ),
                'type'        => 'text',
                'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce-gateway-tradesafe' ),
                'default'     => __( 'TradeSafe', 'woocommerce-gateway-tradesafe' ),
                'desc_tip'    => true,
            ),
            'description' => array(
                'title'       => __( 'Description', 'woocommerce-gateway-tradesafe' ),
                'type'        => 'textarea',
                'description' => __( 'This controls the description which the user sees during checkout.', 'woocommerce-gateway-tradesafe' ),
                'default'     => '',
                'desc_tip'    => true,
            ),
            'client_id' => array(
                'title'       => __( 'Client ID', 'woocommerce-gateway-tradesafe' ),
                'type'        => 'text',
                'description' => __( 'This is the client id for your application.', 'woocommerce-gateway-tradesafe' ),
                'default'     => '',
            ),
            'client_secret' => array(
                'title'       => __( 'Client Secret', 'woocommerce-gateway-tradesafe' ),
                'type'        => 'password',
                'description' => __( 'This is the client secret for your application.', 'woocommerce-gateway-tradesafe' ),
                'default'     => '',
            ),
            'client_callback' => array(
                'title'       => __( 'Client Callback URL', 'woocommerce-gateway-tradesafe' ),
                'type'        => 'text',
                'description' => __( 'This is the client authentication callback for your application.', 'woocommerce-gateway-tradesafe' ),
                'default'     => site_url( '/tradesafe/auth/callback' ),
            ),
        );
    }

    function admin_options() {
        ?>
        <h2><?php _e('TradeSafe','woocommerce-gateway-tradesafe'); ?></h2>
        <table class="form-table">
            <?php $this->generate_settings_html(); ?>
        </table> <?php
    }
}
