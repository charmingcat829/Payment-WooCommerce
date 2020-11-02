<?php


class WC_Gateway_TradeSafe_Base extends WC_Payment_Gateway
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
    public function __construct()
    {
        $this->version = WC_GATEWAY_TRADESAFE_VERSION;
        $this->available_countries = array('ZA');
        $this->available_currencies = (array)apply_filters('woocommerce_gateway_tradesafe_available_currencies', array('ZAR'));

        // Supported functionality
        $this->supports = array(
            'products',
        );

        $this->init_form_fields();
        $this->init_settings();

        // Setup default merchant data.
        $this->has_fields = true;
        $this->enabled = $this->is_valid_for_use() ? 'yes' : 'no'; // Check if the base currency supports this gateway.
        $this->production = $this->get_option('production');
        $this->title = $this->get_option('title');
        $this->description = $this->get_option('description');
        $this->client_id = $this->get_option('client_id');
        $this->client_secret = $this->get_option('client_secret');
        $this->client_callback = $this->get_option('client_callback');

        $this->base_url = 'https://api.tradesafe.co.za/';

        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        add_action('woocommerce_receipt_tradesafe', array($this, 'receipt_page'));
        add_action('admin_notices', array($this, 'admin_notices'));
    }

    /**
     * is_valid_for_use()
     *
     * Check if this gateway is enabled and available in the base currency being traded with.
     *
     * @return bool
     * @since 1.0.0
     */
    public function is_valid_for_use()
    {
        $is_available = false;
        $is_available_currency = in_array(get_woocommerce_currency(), $this->available_currencies);

        if ($is_available_currency && get_option('tradesafe_client_id') && get_option('tradesafe_client_secret')) {
            $is_available = true;
        }

        if ("yes" === $this->get_option('production')) {
            $is_available = false;
        }

        if ("no" === $this->get_option('enabled') || null === $this->get_option('enabled')) {
            $is_available = false;
        }

        return $is_available;
    }

    public function init_form_fields()
    {
        $this->form_fields = array(
            'enabled' => array(
                'title' => __('Enable/Disable', 'woocommerce-gateway-tradesafe'),
                'label' => __('Enable TradeSafe', 'woocommerce-gateway-tradesafe'),
                'type' => 'checkbox',
                'description' => __('This controls whether or not this gateway is enabled within WooCommerce.', 'woocommerce-gateway-tradesafe'),
                'default' => 'yes',
                'desc_tip' => true,
            ),
            'title' => array(
                'title' => __('Title', 'woocommerce-gateway-tradesafe'),
                'type' => 'text',
                'description' => __('This controls the title which the user sees during checkout.', 'woocommerce-gateway-tradesafe'),
                'default' => $this->method_title,
                'desc_tip' => true,
            ),
            'description' => array(
                'title' => __('Description', 'woocommerce-gateway-tradesafe'),
                'type' => 'textarea',
                'description' => __('This controls the description which the user sees during checkout.', 'woocommerce-gateway-tradesafe'),
                'default' => $this->method_description,
                'desc_tip' => true,
            ),
        );
    }

    public function admin_options()
    {
        ?>
        <h2><?php _e('TradeSafe', 'woocommerce-gateway-tradesafe'); ?></h2>
        <table class="form-table">
            <?php $this->generate_settings_html(); ?>
        </table> <?php
    }

    public function payment_fields()
    {
        print "???";
    }

    public function process_payment( $order_id ) {
        global $woocommerce;

        $order = new WC_Order( $order_id );

        // Mark as on-hold (we're awaiting the cheque)
        $order->update_status('on-hold', __( 'Awaiting EFT payment', 'woocommerce-gateway-tradesafe' ));

        // Remove cart
        $woocommerce->cart->empty_cart();

        // Return thankyou redirect
        return array(
            'result' => 'success',
            'redirect' => site_url('tradesafe/eft-details/' . $order_id)
        );
    }

    public function get_checkout_payment_url( $on_checkout = false ) {
        return apply_filters( 'woocommerce_get_checkout_payment_url', $pay_url, $this );
    }
}
