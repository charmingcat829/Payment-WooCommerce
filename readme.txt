=== TradeSafe Payment Gateway ===
Contributors: richardnbanks, tradesafeops
Tags: tradesafe, woocommerce, dokan, credit card, eft, instant eft
Requires at least: 5.6
Tested up to: 5.7
Requires PHP: 7.4
Stable tag: 1.2.3
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

The official TradeSafe plugin for WooCommerce

== Description ==
The TradeSafe plugin for WooCommerce allows TradeSafe Escrow to be used as a payment gateway when buying and selling
goods and services through WooCommerce.

The TradeSafe plugin includes payment gateways namely Ozow (Instant EFT), Visa/Mastercard, SnapScan and normal EFT, with more to come.

The plugin also includes support for paying multiple vendors when using the Dokan plugin.

== Frequently Asked Questions ==

= Where can I find documentation? =

For more information on how to setup the plugin, please refer to our [guide](https://developer.tradesafe.co.za/docs/1.1/plugins/woocommerce)

== Changelog ==

= 1.2.3 - 2021-07-14 =
 * Added status section to plugin settings page to help with debugging

= 1.2.2 - 2021-07-13 =
 * Added check to avoid transactions with missing tokens

= 1.2.1 - 2021-06-30 =
 * Updated tradesafe php client to support tokens without id numbers

= 1.2.0 - 2021-06-30 =
 * Removed ID number field from checkout page as it will now be requested on the payment page

== Upgrade Notice ==
ID Numbers are not required on checkout and will be captured when the buyer makes a payment.
