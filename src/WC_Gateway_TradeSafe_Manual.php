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

        add_action( 'woocommerce_receipt_tradesafe-manual', ['WC_Gateway_TradeSafe_Manual', 'receipt'], 10, 0 );

        parent::__construct();
    }

    public static function receipt()
    {
        global $wp;

        $user = wp_get_current_user();
        $client = woocommerce_tradesafe_api();

        $tokenData = $client->getToken(get_user_meta($user->ID, 'tradesafe_token_id', true));

        include_once __DIR__ . '/../templates/checkout/order-receipt.php';
    }
}
