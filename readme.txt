=== TradeSafe Payment Gateway ===
Contributors: richardnbanks, tradesafeops
Tags: tradesafe, woocommerce, dokan, credit card, eft, instant eft
Requires at least: 5.6
Tested up to: 5.7
Requires PHP: 7.4
Stable tag: 1.0.3
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

= 1.0.3 - 2021-06-17 =
 * Updated exclude list to fix issue where not all files are deployed wordpress.org

= 1.0.2 - 2021-06-14 =
 * Updated plugin page description

= 1.0.1 - 2021-06-11 =
 * Updated plugin info and links for final release

= 1.0.0 - 2021-06-07 =
 * Added notice for users about bank account details
 * Added settings allow or disallow user to change order/transaction state
 * Added token validation for buyers and sellers to ensure their account is completed
 * Calculate cart total without escrow fee
 * Display accept button on order in processing state
 * Updated field type for tax number
 * Updated get option function to return single value for validation check
 * Updated TradeSafe api library
 * Updated variable name for TradeSafe fee allocation

== Upgrade Notice ==
Initial release
