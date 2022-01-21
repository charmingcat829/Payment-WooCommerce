=== TradeSafe Payment Gateway for WooCommerce ===
Contributors: richardnbanks, tradesafeops
Tags: tradesafe, woocommerce, dokan, payment gateway, escrow, credit card, instant eft
Requires at least: 5.6
Tested up to: 5.8
Requires PHP: 7.4
Stable tag: 2.1.3
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

The official TradeSafe plugin for WooCommerce

== Description ==
TradeSafe, backed by Standard Bank, provides an escrow payments-based solution that integrates seamlessly with your existing WordPress store.

With an average documented cart abandonment rate of 70%, it’s reported that many South Africans are becoming fearful when buying online. This is where we come in. TradeSafe bridges the trust gap and eliminates the fear when customers want to make payment upfront in full, online. We give your customers the peace of mind that they will receive what they paid for, leading to a boost in purchase confidence, and ultimately improving conversion rates.

The TradeSafe plugin includes payment gateways namely Ozow (Instant EFT), Visa/Mastercard, SnapScan and normal EFT, with many more to come.

The plugin also includes support for paying multiple vendors when using the Dokan plugin.

### How it Works

- At checkout, the customer chooses to pay using TradeSafe.
- The customer will be directed to a payment page, where they choose their payment option.
- Once payment is successful, the order is marked as PROCESSING and delivery is kicked off.
- When delivery has been completed, a new state called DELIVERED is reached. This triggers an email and sms which TradeSafe will send to the customer, asking them if they received what they ordered.
- If yes, TradeSafe releases the funds to you, and/or your vendors if trading in a multi-vendor setup. 
- The customer has 24 hours in which to make their decision. If we do not hear from the customer within that time, TradeSafe will deem this as acceptance on the buyer's part and automatically release the funds to you.
- If the customer is unhappy with the order, then the customer can submit a comment. The transaction will then be placed on hold in WooCommerce. This will give you the opportunity to enact your own return policies until a successful resolution has been achieved. 
- No refund will be processed without your consent. TradeSafe performs all refunds on your behalf. We take away this admin burden. 
- The buyer must have a material complaint and provide evidence. We will release the funds to you in the event the complaint/issue is non-material or frivolous.
 
== Frequently Asked Questions ==

= How does TradeSafe benefit me as a merchant? =

- Boosts sales conversions. TradeSafe gives new uninitiated customers the peace of mind to make that upfront purchase.
- We enable split payments for each transaction which allows your digital store to earn a commission for each transaction. No longer do you have to do this manually.
- Refunds. Just hit that button, and we will refund the customer on your behalf. 
- Wallet functionality. TradeSafe provides two settlement options whereby we either pay to the beneficiary's bank account, or we credit their escrow wallet where they can manually withdraw the funds in WooCommerce.
- We augment your store's credibility and reputational cred

= How does TradeSafe benefit my customers? =

- Removes uncertainty and stress. An escrow holding account, together with Standard Bank’s logo, gives buyers the confidence to make that upfront payment.
- We provide multiple payment options for their convenience. 
- Customers are never redirected to another site, and they never need to register on a separate platform. We ensure a seamless user journey.

= How and when do I get paid? =

We provide you two options how the funds are to be disbursed to you and/or your vendors.

1. Automatically. TradeSafe will make payment to your bank account. There is an additional small charge per transaction should you choose this option.    
2. Wallet withdraw. You can choose to withdrawal your funds from your WooCommerce dashboard. 

The payment frequencies can be set at your discretion. We will pay the funds to you either on a daily, weekly, bi-monthly, or monthly basis. Some merchants preferred to be paid at the end of the month, and some immediately. Just tell us what would suit you.

= Does the plugin support both production mode and sandbox mode for testing?

Absolutely. You can toggle between production and sandbox easily within your TradeSafe merchant dashboard.

= How do I sign up? =

Simply go to [www.tradesafe.co.za](https://www.tradesafe.co.za) and sign up for a merchant account at the top right of the page. Complete a few wizards, and you will then be granted access to the developer dashboard where you can begin installing and testing the plug-in in a testing environment.

Once you have tested, and everything is working great, then we will perform some verification procedures before taking you live.

= Where can I find documentation? =

For more information on how to install and configure the plugin, please refer to our [guide](https://developer.tradesafe.co.za/docs/1.2/plugins/woocommerce)

= Who do I reach out to if I need technical support or if I have any further queries? =

Please do not hesitate to email [support@tradesafe.co.za](mailto:support@tradesafe.co.za) or phone us on 010 020 3101. We are here to help you every step of the way.

== Changelog ==

= 2.1.3 - 2022-01-11 =

Bug Fixes
* Added optional validation to organisation details to ensure basic details are captured

= 2.1.2 - 2022-01-10 =

Bug Fixes
* Updated css to avoid any potential conflicts

= 2.1.1 - 2022-01-10 =

Bug Fixes
* Updated publish check to only apply to products

= 2.1.0 - 2022-01-06 =

Features
* Added btton to start go live request

Bug Fixes
* Changed formatting of line items added to the transaction description
