=== TradeSafe Payment Gateway ===
Contributors: richardnbanks, tradesafeops
Tags: tradesafe, woocommerce, dokan, credit card, eft, instant eft
Requires at least: 5.6
Tested up to: 5.8
Requires PHP: 7.4
Stable tag: 2.0.5
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

For more information on how to setup the plugin, please refer to our [guide](https://developer.tradesafe.co.za/docs/1.2/plugins/woocommerce)

== Changelog ==

= 2.0.5 - 2021-12-17 =
 * Corrected issue where production url was not loading correctly

= 2.0.4 - 2021-12-17 =
 * Removed depricated buyer accept option

= 2.0.3 - 2021-12-17 =
 * Improved error handeling

= 2.0.2 - 2021-12-17 =
 * Added helper functions to ensure compatibility with older versions of the plugin

= 2.0.1 - 2021-12-17 =
 * Added helper function for checking if production url is active

= 2.0.0 - 2021-12-14 =
 * Added new acceptance process
 * Allow checkout without needing an account
 * Ensure transaction id is saved to order meta data before redirect

== Upgrade Notice ==
This is a significat update please test prior to deploying to production.
