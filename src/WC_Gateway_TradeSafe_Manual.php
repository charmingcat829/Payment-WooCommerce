<?php

class WC_Gateway_TradeSafe_Manual extends WC_Gateway_TradeSafe_Base
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->id = 'tradesafe-manual';
        $this->method_title = __('TradeSafe - Manual EFT', 'woocommerce-gateway-tradesafe');
        /* translators: 1: a href link 2: closing href */
        $this->method_description = __('Manual EFT through TradeSafe', 'woocommerce-gateway-tradesafe');
        $this->icon = WP_PLUGIN_URL . '/' . plugin_basename(dirname(dirname(__FILE__))) . '/assets/images/icon.svg';

        add_action('woocommerce_receipt_tradesafe-manual', ['WC_Gateway_TradeSafe_Manual', 'receipt'], 10, 0);
        add_action('woocommerce_order_details_after_order_table', ['WC_Gateway_TradeSafe_Manual', 'receipt'], 10, 0);

        parent::__construct();
    }

    public static function receipt()
    {
        global $wp;

        if (isset($wp->query_vars['view-order'])) {
            $order_id = $wp->query_vars['view-order'];
        }

        if (isset($wp->query_vars['order-pay'])) {
            $order_id = $wp->query_vars['order-pay'];
        }

        if (!isset($order_id)) {
            return;
        }

        $order = wc_get_order($order_id);

        if ($order->get_payment_method() === 'tradesafe-manual' && $order->get_status() === 'on-hold') {
            $user = wp_get_current_user();
            $client = woocommerce_tradesafe_api();


            if (is_null($client)) {
                echo "<table class='form-table' role='presentation'><tbody>";
                echo "<tr><th scope='row'>Error:</th><td> Could not connect to server</td></tr>";
                echo "</tbody></table>";
                return;
            }

            $tokenData = $client->getToken(get_user_meta($user->ID, 'tradesafe_token_id', true));

            include_once __DIR__ . '/../templates/checkout/deposit-details.php';
        }
    }
}
