<?php

class WC_Gateway_TradeSafe_Snapscan extends WC_Gateway_TradeSafe_Base
{
    /**
     * Constructor
     */
    public function __construct() {
        $this->id = 'tradesafe-snapscan';
        $this->method_title       = __( 'TradeSafe - SnapScan', 'woocommerce-gateway-tradesafe' );
        /* translators: 1: a href link 2: closing href */
        $this->method_description = sprintf( __( '%1$sTradeSafe%2$s', 'woocommerce-gateway-tradesafe' ), '<a href="http://www.tradesafe.co.za/">', '</a>' );
        $this->icon               = WP_PLUGIN_URL . '/' . plugin_basename( dirname( dirname( __FILE__ ) ) ) . '/assets/images/icon.svg';

        parent::__construct();
    }
}
