<?php

class WC_Gateway_TradeSafe_Ecentric extends WC_Gateway_TradeSafe_Base
{
    /**
     * Constructor
     */
    public function __construct() {
        $this->id = 'tradesafe-ecentric';
        $this->method_title       = __( 'TradeSafe - Credit Card', 'woocommerce-gateway-tradesafe' );
        /* translators: 1: a href link 2: closing href */
        $this->method_description = __( 'Ecentric Gateway through TradeSafe', 'woocommerce-gateway-tradesafe' );
        $this->icon               = WP_PLUGIN_URL . '/' . plugin_basename( dirname( dirname( __FILE__ ) ) ) . '/assets/images/icon.svg';

        parent::__construct();
    }
}
