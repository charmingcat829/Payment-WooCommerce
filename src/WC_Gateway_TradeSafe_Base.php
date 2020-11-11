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

        $this->base_url = 'https://api-dev.tradesafe.dev';

        if ($this->get_option('production')) {
            $this->base_url = 'https://api.tradesafe.co.za';
        }

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

        if ($is_available_currency
            && get_option('tradesafe_client_id')
            && get_option('tradesafe_client_secret')
            && get_option('tradesafe_token')) {
            $is_available = true;
        }

        if ("yes" === $this->get_option('production')) {
            $is_available = false;
        }

        if ("no" === $this->get_option('enabled') || null === $this->get_option('enabled')) {
            $is_available = false;
        }

        if (!is_admin()) {
            $cart = WC()->cart->get_cart_contents();
            $vendor = null;

            foreach ($cart as $product) {
                $vendor_id = get_post_field('post_author', $product['product_id']);

                if (is_null($vendor)) {
                    $vendor = $vendor_id;
                }

                if ($vendor !== $vendor_id) {
                    $is_available = false;
                }
            }

            $user = wp_get_current_user();
            if ('' === get_user_meta($user->ID, 'tradesafe_token_id')) {
                $is_available = false;
            }
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

    public function process_payment($order_id)
    {
        global $woocommerce;

        $order = new WC_Order($order_id);

        if (!$order->meta_exists('tradesafe_transaction_id')) {
            $client = woocommerce_tradesafe_api();

            if (is_null($client)) {
                return null;
            }

            $user = wp_get_current_user();

            $transaction = $client->createTransaction([
                'title' => 'Order ' . $order->get_id(),
                'description' => 'WooCommerce Order ' . $order->get_order_key(),
                'industry' => 'GENERAL_GOODS_SERVICES',
                'value' => $order->get_total(),
                'buyerToken' => get_user_meta($user->ID, 'tradesafe_token_id', true),
                'sellerToken' => get_option('tradesafe_token')
            ]);

            $order->add_meta_data('tradesafe_transaction_id', $transaction['id'], true);
        }

        // Mark as on-hold (we're awaiting the cheque)
        $order->update_status('pending', __('Awaiting EFT payment', 'woocommerce-gateway-tradesafe'));

        // Remove cart
        $woocommerce->cart->empty_cart();

        switch ($order->get_payment_method()) {
            case "tradesafe-ecentric":
                $url = '';
                break;
            case "tradesafe-manual":
                $url = $order->get_checkout_payment_url(true);
                break;
            case "tradesafe-ozow":
                $url = '';
                break;
            case "tradesafe-snapscan":
                $url = '';
                break;
            default:
                $url = $order->get_checkout_payment_url(true);
        }

        // Return thankyou redirect
        return array(
            'result' => 'success',
            'redirect' => $url
        );
    }

    public function get_checkout_payment_url($on_checkout = false)
    {
        return apply_filters('woocommerce_get_checkout_payment_url', $pay_url, $this);
    }
}
