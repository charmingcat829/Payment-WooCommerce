<?php
/**
 * Checkout Order Receipt Template
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/order-receipt.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<h3>Banking Details</h3>
<ul class="order_details banking_details">
    <li class="bank">
        Account Name: <strong>TradeSafe Escrow</strong>
    </li>
    <li class="bank">
        Bank: <strong>Standard Bank South Africa</strong>
    </li>
    <li class="date">
        Account Number: <strong>2960060</strong>
    </li>
    <li class="total">
        Branch Code: <strong>051001</strong>
    </li>
    <li class="total">
        Payment Reference: <strong><?php echo $tokenData['reference']; ?></strong>
    </li>
</ul>
