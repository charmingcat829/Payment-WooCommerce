<!--- BEGIN HEADER -->
# Changelog

All notable changes to this project will be documented in this file.
<!--- END HEADER -->

## [2.11.1](https://github.com/tradesafe/tradesafe-payment-gateway/compare/v2.11.0...v2.11.1) (2023-01-20)

### Bug Fixes

* Changed error handeling for mark as delivered button ([bcbca8](https://github.com/tradesafe/tradesafe-payment-gateway/commit/bcbca88860ab1348792cd7fc2036644a9745896d))
* Reduce the delivery days from 7 days to 5 ([bf5d47](https://github.com/tradesafe/tradesafe-payment-gateway/commit/bf5d470f5d4061fc6d823ff4e633c0285015613d))


---

## [2.11.0](https://github.com/tradesafe/tradesafe-payment-gateway/compare/v2.10.0...v2.11.0) (2023-01-06)

### Features

* Added button to mark in transit orders as delivered ([0af891](https://github.com/tradesafe/tradesafe-payment-gateway/commit/0af891779db4c2a0f02e5aa4de8f659ca8d63696))

### Bug Fixes

* Do not load transaction on order page if notification delay is not enabled ([799bb3](https://github.com/tradesafe/tradesafe-payment-gateway/commit/799bb3207015c5efd97b1b8d238b56bcd1913a04))


---

## [2.10.0](https://github.com/tradesafe/tradesafe-payment-gateway/compare/v2.9.2...v2.10.0) (2022-12-14)

### Features

* Added filter to add parties to a transaction ([415318](https://github.com/tradesafe/tradesafe-payment-gateway/commit/415318c8d1256784719a6d6341050e02a7f5107b))


---

## [2.9.2](https://github.com/tradesafe/tradesafe-payment-gateway/compare/v2.9.1...v2.9.2) (2022-12-12)

### Bug Fixes

* Added check for update function to avoid undefined constant ([6c6e73](https://github.com/tradesafe/tradesafe-payment-gateway/commit/6c6e73f211447d0cdb6deb9ec98282df61e1ced6))


---

## [2.9.1](https://github.com/tradesafe/tradesafe-payment-gateway/compare/v2.9.0...v2.9.1) (2022-12-12)

### Bug Fixes

* Hide days for delay field if not used ([3ffd8f](https://github.com/tradesafe/tradesafe-payment-gateway/commit/3ffd8f5eb742f41449ea4916494a595de59bba1b))


---

## [2.9.0](https://github.com/tradesafe/tradesafe-payment-gateway/compare/v2.8.0...v2.9.0) (2022-12-12)

### Features

* Added option to defer delivery notifications ([6d7205](https://github.com/tradesafe/tradesafe-payment-gateway/commit/6d720538b15f6542ae9e1831c03dc7d387f2914a))

### Bug Fixes

* Allow cancelled orders to be reactivated ([28f2e2](https://github.com/tradesafe/tradesafe-payment-gateway/commit/28f2e2248ca6fbd8d367056f166b6ab22ef0efcf))
* Changed how the token expiry date is checked ([96470a](https://github.com/tradesafe/tradesafe-payment-gateway/commit/96470a81ae3e3dcb26f0847c58e9e0e6f11d7e93))
* Don't init form on every page load ([0839cf](https://github.com/tradesafe/tradesafe-payment-gateway/commit/0839cfcd8cc5a8dd2fd76ef0ddaeba219224fc5d))
* Hide application details if authenticated ([312105](https://github.com/tradesafe/tradesafe-payment-gateway/commit/3121055e61a902f7a8c93a85285cdeeff6c9c07f))
* Reduce number of queries retrieving bank account details ([fa1c08](https://github.com/tradesafe/tradesafe-payment-gateway/commit/fa1c08baa58981208ac71fac3a42cbf332762e34))


---

## [2.8.0](https://github.com/tradesafe/tradesafe-payment-gateway/compare/v2.7.1...v2.8.0) (2022-12-02)

### Features

* Added function to update gateway description automatically ([53d8d9](https://github.com/tradesafe/tradesafe-payment-gateway/commit/53d8d9dccbf5eee244cfc72ebdba3ca4fe0af4a3))

### Bug Fixes

* Changed character decoding for line items ([65670f](https://github.com/tradesafe/tradesafe-payment-gateway/commit/65670f0c1e6503be497886b7183d6456db491b43))


---

## [2.7.2](https://github.com/tradesafe/tradesafe-payment-gateway/compare/v2.7.1...v2.7.2) (2022-11-30)

### Bug Fixes

* Changed character decoding for line items ([65670f](https://github.com/tradesafe/tradesafe-payment-gateway/commit/65670f0c1e6503be497886b7183d6456db491b43))


---

## [2.7.1](https://github.com/tradesafe/tradesafe-payment-gateway/compare/v2.7.0...v2.7.1) (2022-11-21)

### Bug Fixes

* Added support for custom processing fees ([cfc3e1](https://github.com/tradesafe/tradesafe-payment-gateway/commit/cfc3e161bcf179982f2b254a63aca274b4f5d091))


---

## [2.7.0](https://github.com/tradesafe/tradesafe-payment-gateway/compare/v2.6.3...v2.7.0) (2022-11-16)

### Features

* Added content encoding to api requests ([2ead02](https://github.com/tradesafe/tradesafe-payment-gateway/commit/2ead020a49ee9aaaa10731c28caec6ddfe6c706f))

### Bug Fixes

* Set order payment method title to prevent escaped html from showing in the payment method field ([3cfa01](https://github.com/tradesafe/tradesafe-payment-gateway/commit/3cfa01a8a2e835f22cf35158bfcb6259655d733a))


---

## [2.6.3](https://github.com/tradesafe/tradesafe-payment-gateway/compare/v2.6.2...v2.6.3) (2022-11-10)

### Bug Fixes

* Changed how access tokens are stored for reuse ([411b3e](https://github.com/tradesafe/tradesafe-payment-gateway/commit/411b3e89fd5a5a3c4b9ade2aa202b2ab34161113))


---

## [2.6.2](https://github.com/tradesafe/tradesafe-payment-gateway/compare/v2.6.1...v2.6.2) (2022-11-08)

### Bug Fixes

* Updated user agent string ([b90378](https://github.com/tradesafe/tradesafe-payment-gateway/commit/b90378c93ea9b69976ed333977e290d99aec0593))


---

## [2.6.1](https://github.com/tradesafe/tradesafe-payment-gateway/compare/v2.6.0...v2.6.1) (2022-11-07)

### Bug Fixes

* Optimised how tokens are generated to avoid unnecessary api calls ([1b6821](https://github.com/tradesafe/tradesafe-payment-gateway/commit/1b68218ac4750dde5621abc6b0a1a88939cba0fe))


---

## [2.6.0](https://github.com/tradesafe/tradesafe-payment-gateway/compare/v2.5.3...v2.6.0) (2022-10-14)
### Features

* Added helper function to lookup token data ([31c730](https://github.com/tradesafe/tradesafe-payment-gateway/commit/31c730feec9fa3be761fffec9f4473aa3e68168d))


---

## [2.5.3](https://github.com/tradesafe/tradesafe-payment-gateway/compare/v2.5.2...v2.5.3) (2022-09-20)
### Bug Fixes

* Don't apply defaults to order if user id is zero ([23eecc](https://github.com/tradesafe/tradesafe-payment-gateway/commit/23eeccf367bb3182a1e037a985e40492d1b6cbbb))


---

## [2.5.2](https://github.com/tradesafe/tradesafe-payment-gateway/compare/v2.5.1...v2.5.2) (2022-09-16)
### Bug Fixes

* Changed when classes are initialised ([76abd7](https://github.com/tradesafe/tradesafe-payment-gateway/commit/76abd785c47c69d3e46712d61368adf892ecb431))


---

## [2.5.1](https://github.com/tradesafe/tradesafe-payment-gateway/compare/v2.5.0...v2.5.1) (2022-09-09)
### Bug Fixes

* Updated plugin descriptions ([c9ec0b](https://github.com/tradesafe/tradesafe-payment-gateway/commit/c9ec0b496069985e2dbd0b719e095034385f5f72))


---

## [2.5.0](https://github.com/tradesafe/tradesafe-payment-gateway/compare/v2.4.11...v2.5.0) (2022-09-08)
### Features

* Added new checkout page ([af1645](https://github.com/tradesafe/tradesafe-payment-gateway/commit/af1645d40fe9801f015db4ca951bc3717b155cad))


---

## [2.4.11](https://github.com/tradesafe/tradesafe-payment-gateway/compare/v2.4.10...v2.4.11) (2022-07-28)
### Bug Fixes

* Removed hidden class from banking details when using Dokan ([32e0e5](https://github.com/tradesafe/tradesafe-payment-gateway/commit/32e0e5c973aa2cd6b4c5eb5fa8762d90d24079f6))


---

## [2.4.10](https://github.com/tradesafe/tradesafe-payment-gateway/compare/v2.4.9...v2.4.10) (2022-07-27)
### Bug Fixes

* Changed how bank details checkbox is checked ([9cf919](https://github.com/tradesafe/tradesafe-payment-gateway/commit/9cf9198182a9dcdcb874298c67b759d8a785fcae))


---

## [2.4.9](https://github.com/tradesafe/tradesafe-payment-gateway/compare/v2.4.8...v2.4.9) (2022-07-20)
### Bug Fixes

* Corrected issue where a vendor id is not loaded correctly when using dokan ([7d857d](https://github.com/tradesafe/tradesafe-payment-gateway/commit/7d857defc722582a2daa2514202cdaf7be3ec043))


---

## [2.4.8](https://github.com/tradesafe/tradesafe-payment-gateway/compare/v2.4.7...v2.4.8) (2022-07-20)
### Bug Fixes

* Updated avalibility filter to verify order total or cart total instead of both ([22045c](https://github.com/tradesafe/tradesafe-payment-gateway/commit/22045caa480f142a8fc4998b05b4d25866ff368c))


---

## [2.4.7](https://github.com/tradesafe/tradesafe-payment-gateway/compare/v2.4.6...v2.4.7) (2022-07-18)
### Bug Fixes

* Allow callbacks to update orders with a failed status ([be4489](https://github.com/tradesafe/tradesafe-payment-gateway/commit/be4489f5ddbcf4bd4f6e280f472be1f988823c23))
* Changed how plugin avalibility is checked ([a798a1](https://github.com/tradesafe/tradesafe-payment-gateway/commit/a798a16cc5adb56b93365e44d4a8b5a380881eff))


---

## [2.4.6](https://github.com/tradesafe/tradesafe-payment-gateway/compare/v2.4.5...v2.4.6) (2022-05-23)
### Bug Fixes

* Changed how errors are logged ([1e8762](https://github.com/tradesafe/tradesafe-payment-gateway/commit/1e87623eb612169d21295f12a453c0d184671093))


---

## [2.4.5](https://github.com/tradesafe/tradesafe-payment-gateway/compare/v2.4.4...v2.4.5) (2022-05-20)
### Bug Fixes

* Add support for setting up payment settings in dokan wizard ([d7d166](https://github.com/tradesafe/tradesafe-payment-gateway/commit/d7d166a94809ccacdb932e7559efdccb9b1b5653))


---

## [2.4.4](https://github.com/tradesafe/tradesafe-payment-gateway/compare/v2.4.3...v2.4.4) (2022-05-19)
### Bug Fixes

* Always load token id through helper function instead of checking directly ([084fd9](https://github.com/tradesafe/tradesafe-payment-gateway/commit/084fd90748222365df449256976fb6648c87e3fe))


---

## [2.4.3](https://github.com/tradesafe/tradesafe-payment-gateway/compare/v2.4.2...v2.4.3) (2022-05-17)
### Bug Fixes

* Added extra debug information to error response when saving a token while debug is set to true ([4dd511](https://github.com/tradesafe/tradesafe-payment-gateway/commit/4dd511975d9cc9544dcc0ffb868f75333d9f69c8))

---

## [2.4.2](https://github.com/tradesafe/tradesafe-payment-gateway/compare/v2.4.1...v2.4.2) (2022-05-17)
### Bug Fixes

* Removed vendor id from active payment methods function ([31b96c](https://github.com/tradesafe/tradesafe-payment-gateway/commit/31b96ca150db7d318e33b17e1481ce7b1b444575))

---

## [2.4.1](https://github.com/tradesafe/tradesafe-payment-gateway/compare/v2.4.0...v2.4.1) (2022-05-17)
### Bug Fixes

* Updated how withdraw settings are saved to support new withdraw method page ([695521](https://github.com/tradesafe/tradesafe-payment-gateway/commit/695521eb65d748a73255328896b2bc5cc61f978a))

---

## [2.4.0](https://github.com/tradesafe/tradesafe-payment-gateway/compare/v2.3.7...v2.4.0) (2022-05-10)
### Features

* Allow admin users to view basic tradesafe details when editing a user ([7887e2](https://github.com/tradesafe/tradesafe-payment-gateway/commit/7887e214d9163755225c21c34e57bb3288c90e11))

---

## [2.3.7](https://github.com/tradesafe/tradesafe-payment-gateway/compare/v2.3.6...v2.3.7) (2022-05-09)
### Bug Fixes

* Changed option title to reduce confusion ([589b0b](https://github.com/tradesafe/tradesafe-payment-gateway/commit/589b0b9a5d403ff01a269c1d2359ae7671c5e07c))
* Ensure that token metadata is properly saved ([2dff6c](https://github.com/tradesafe/tradesafe-payment-gateway/commit/2dff6cedae9daf6897b61b0842956e93689538bb))

---

## [2.3.6](https://github.com/tradesafe/tradesafe-payment-gateway/compare/v2.3.5...v2.3.6) (2022-04-22)
### Bug Fixes

* Allow decimal values for marketplace commission ([7f2576](https://github.com/tradesafe/tradesafe-payment-gateway/commit/7f25762dfd84325d2b63138ac6b71a27cee39b97))

---

## [2.3.5](https://github.com/tradesafe/tradesafe-payment-gateway/compare/v2.3.4...v2.3.5) (2022-04-11)
### Bug Fixes

* Changed how id for sub order post is retrieved ([1db173](https://github.com/tradesafe/tradesafe-payment-gateway/commit/1db173ec6044c68cf1677bfac663b51f86114d0b))

---

## [2.3.4](https://github.com/tradesafe/tradesafe-payment-gateway/compare/v2.3.3...v2.3.4) (2022-04-11)
### Bug Fixes

* Changed how data is validated when creating a token with basic infomation ([f89962](https://github.com/tradesafe/tradesafe-payment-gateway/commit/f89962dc2ed78303bab9a5a290ca22c998c6ffac))

---

## [2.3.3](https://github.com/tradesafe/tradesafe-payment-gateway/compare/v2.3.2...v2.3.3) (2022-04-09)
### Bug Fixes

* Added check to ensure fee allocation is set correctly ([036547](https://github.com/tradesafe/tradesafe-payment-gateway/commit/036547e281dadaf89412c68bd8a6b1d4098ce52b))

---

## [2.3.2](https://github.com/tradesafe/tradesafe-payment-gateway/compare/v2.3.1...v2.3.2) (2022-04-07)
### Bug Fixes

* Removed old incomplete account messages ([15880f](https://github.com/tradesafe/tradesafe-payment-gateway/commit/15880feaae43d5e8d7279e8a619f9533b044fae8))

---

## [2.3.1](https://github.com/tradesafe/tradesafe-payment-gateway/compare/v2.3.0...v2.3.1) (2022-03-24)
---

## [2.3.0](https://github.com/tradesafe/tradesafe-payment-gateway/compare/v2.2.3...v2.3.0) (2022-03-24)
### Features

* Added function to retrieve or create a token id for a user ([fec055](https://github.com/tradesafe/tradesafe-payment-gateway/commit/fec055e11e9e8c963d839686395e13dfadacab87))
* Added withdraw function with support for the doken dashboard ([be0c68](https://github.com/tradesafe/tradesafe-payment-gateway/commit/be0c68986ec8a3fa539af62d72a42706622efe3a))
* Changed how token id's are retrieved when creating a transaction ([4d4c62](https://github.com/tradesafe/tradesafe-payment-gateway/commit/4d4c62b844274f54912d4e48e94e15ede4916f5d))

### Bug Fixes

* Simplified the qraphql mutation used for creating or updating a token ([75570b](https://github.com/tradesafe/tradesafe-payment-gateway/commit/75570b31c6c8cbb5c9f171f8ba2960456251dbdd))

---

## [2.2.3](https://github.com/tradesafe/tradesafe-payment-gateway/compare/v2.2.2...v2.2.3) (2022-02-09)
### Bug Fixes

* Changed how the existence of data is validated when creating a token ([f6f603](https://github.com/tradesafe/tradesafe-payment-gateway/commit/f6f6030b3f153b4e1f1a10445a4a7a3241709647))

---

## [2.2.2](https://github.com/tradesafe/tradesafe-payment-gateway/compare/v2.2.1...v2.2.2) (2022-02-07)
### Bug Fixes

* Added additional validation to order state transisions ([eee15e](https://github.com/tradesafe/tradesafe-payment-gateway/commit/eee15e863f44f7f4c4787cf3aea9487d534ad069))

---

## [2.2.1](https://github.com/tradesafe/tradesafe-payment-gateway/compare/v2.2.0...v2.2.1) (2022-02-01)
### Bug Fixes

* Do not disable updates for products if user is admin ([776f09](https://github.com/tradesafe/tradesafe-payment-gateway/commit/776f09357be326493ca119afa9f4ab4bce1a1095))

---

## [2.2.0](https://github.com/tradesafe/tradesafe-payment-gateway/compare/v2.1.3...v2.2.0) (2022-01-31)
### Features

* Added withdraw support for dokan ([a88689](https://github.com/tradesafe/tradesafe-payment-gateway/commit/a8868933ed25142b5f8f16d24e8c4aa7597d24f2))

### Bug Fixes

* Set tokens to payout on a montly basis ([0d27ad](https://github.com/tradesafe/tradesafe-payment-gateway/commit/0d27ad37d24e1099223371973b02b600444d3a8f))

---

## [2.1.3](https://github.com/tradesafe/tradesafe-payment-gateway/compare/v2.1.2...v2.1.3) (2022-01-11)
### Bug Fixes

* Added optional validation to organisation details to ensure basic details are captured ([59662e](https://github.com/tradesafe/tradesafe-payment-gateway/commit/59662e72eaaf39286a0b66a6ef651d201f0ab4c1))

---

## [2.1.2](https://github.com/tradesafe/tradesafe-payment-gateway/compare/v2.1.1...v2.1.2) (2022-01-10)
### Bug Fixes

* Updated css to avoid any potential conflicts ([df65ef](https://github.com/tradesafe/tradesafe-payment-gateway/commit/df65ef03a9914073ee82b1ac23f1c8436bd1bb6c))

---

## [2.1.1](https://github.com/tradesafe/tradesafe-payment-gateway/compare/v2.1.0...v2.1.1) (2022-01-10)
### Bug Fixes

* Updated publish check to only apply to products ([e996eb](https://github.com/tradesafe/tradesafe-payment-gateway/commit/e996eb0cd11fa2c3cbbc5af5534be1764335b4f5))

---

## [2.1.0](https://github.com/tradesafe/tradesafe-payment-gateway/compare/v2.0.5...v2.1.0) (2022-01-06)
### Features

* Added btton to start go live request ([43f2ac](https://github.com/tradesafe/tradesafe-payment-gateway/commit/43f2acdea6ed3127699b3a112a516152a3211013))

### Bug Fixes

* Changed formatting of line items added to the transaction description ([b1d4e4](https://github.com/tradesafe/tradesafe-payment-gateway/commit/b1d4e4caaeee8c9a82290bf0d58db20b4151ca79))

---

## [2.0.5](https://github.com/tradesafe/tradesafe-payment-gateway/compare/v2.0.4...v2.0.5) (2021-12-17)
### Bug Fixes

* Corrected issue where production url was not loading correctly ([bf1e58](https://github.com/tradesafe/tradesafe-payment-gateway/commit/bf1e5862cc5476a5aad3f0de00e7fb83c75037cb))

---

## [2.0.4](https://github.com/tradesafe/tradesafe-payment-gateway/compare/v2.0.3...v2.0.4) (2021-12-17)
### Bug Fixes

* Removed depricated buyer accept option ([103c12](https://github.com/tradesafe/tradesafe-payment-gateway/commit/103c12002ee11e247063bfd0e19ed99772a84ee7))

---

## [2.0.3](https://github.com/tradesafe/tradesafe-payment-gateway/compare/v2.0.2...v2.0.3) (2021-12-17)
### Bug Fixes

* Improved error handeling ([d1fad8](https://github.com/tradesafe/tradesafe-payment-gateway/commit/d1fad85bcb3cbda9335301cce54f997e7d8a9e46))

---

## [2.0.2](https://github.com/tradesafe/tradesafe-payment-gateway/compare/v2.0.1...v2.0.2) (2021-12-17)
### Bug Fixes

* Added helper functions to ensure compatibility with older versions of the plugin ([256fad](https://github.com/tradesafe/tradesafe-payment-gateway/commit/256fadd96b638851189bec56fc21bdd5fcccaf77))

---

## [2.0.1](https://github.com/tradesafe/tradesafe-payment-gateway/compare/v2.0.0...v2.0.1) (2021-12-17)
### Bug Fixes

* Added helper function for checking if production url is active ([b39a79](https://github.com/tradesafe/tradesafe-payment-gateway/commit/b39a796510365a346f6ce041388fc17be1a03c39))

---

## [2.0.0](https://github.com/tradesafe/tradesafe-payment-gateway/compare/v1.2.5...v1.3.0) (2021-12-14)
### Features

* Added new acceptance process ([c28975](https://github.com/tradesafe/tradesafe-payment-gateway/commit/c28975732a3a9b6de7f703867aa313c14431a52e))
* Allow checkout without needing an account ([e02f6c](https://github.com/tradesafe/tradesafe-payment-gateway/commit/e02f6cd4c48f75b4608d6117941d77a16e705889))

### Bug Fixes

* Ensure transaction id is saved to order meta data before redirect ([550e89](https://github.com/tradesafe/tradesafe-payment-gateway/commit/550e89b85cb49e60a62a0a37e600e13b872d10bf))

---

## [1.2.5](https://github.com/tradesafe/plugin-woocommerce/compare/v1.2.4...v1.2.5) (2021-08-12)
### Bug Fixes

* Updated to WordPress support to version 5.8 and WooCommerce support to version 5.5 ([560333](https://github.com/tradesafe/plugin-woocommerce/commit/56033394ff038bda11af88cd3f5e3e7f9f3e57be))

---

## [1.2.4](https://github.com/tradesafe/plugin-woocommerce/compare/v1.2.3...v1.2.4) (2021-07-14)


### Bug Fixes

* Improved error handeling when client details are invalid ([3a2e3d](https://github.com/tradesafe/plugin-woocommerce/commit/3a2e3d54dc65d6299c5e73d661a3fd4ae33e7685))

---

## [1.2.3](https://github.com/tradesafe/plugin-woocommerce/compare/v1.2.2...v1.2.3) (2021-07-14)


### Bug Fixes

* Added status section to plugin settings page to help with debugging ([d304a6](https://github.com/tradesafe/plugin-woocommerce/commit/d304a65a203fe20cdc474fcb2d0427abe20da668))

---

## [1.2.2](https://github.com/tradesafe/plugin-woocommerce/compare/v1.2.1...v1.2.2) (2021-07-13)


### Bug Fixes

* Added check to avoid transcations with missing tokens ([c830bc](https://github.com/tradesafe/plugin-woocommerce/commit/c830bcbdd7dc227cc0fbd7fba91b12e4fa3a3323))

---

## [1.2.1](https://github.com/tradesafe/plugin-woocommerce/compare/v1.2.0...v1.2.1) (2021-06-30)


### Bug Fixes

* Updated tradesafe php client to support tokens without id numbers ([c4e65c](https://github.com/tradesafe/plugin-woocommerce/commit/c4e65ccb2ecc5fa6895fd3f94f56bc3d581732c2))

---

## [1.2.0](https://github.com/tradesafe/plugin-woocommerce/compare/v1.1.0...v1.2.0) (2021-06-30)


### Features

* Removed ID number field from checkout page as it will now be requested on the payment page ([ab7bb0](https://github.com/tradesafe/plugin-woocommerce/commit/ab7bb0a48f9abee4b20cdc131ff7eafeb000d5cf))

---

## [1.1.0](https://github.com/tradesafe/plugin-woocommerce/compare/v1.0.6...v1.1.0) (2021-06-25)


### Features

* Added the option to include a users id number on checkout ([92d11a](https://github.com/tradesafe/plugin-woocommerce/commit/92d11ace044a5c8931eadf2475cae0acedac21b0))

---

## [1.0.6](https://github.com/tradesafe/plugin-woocommerce/compare/v1.0.5...v1.0.6) (2021-06-22)


---

## [1.0.5](https://github.com/tradesafe/plugin-woocommerce/compare/v1.0.4...v1.0.5) (2021-06-21)


### Bug Fixes

* Don't call wp_die if api connection fails ([d48f88](https://github.com/tradesafe/plugin-woocommerce/commit/d48f8898c035d77dd40537c76e92bb049e8a40f6))

---

## [1.0.4](https://github.com/tradesafe/plugin-woocommerce/compare/v1.0.3...v1.0.4) (2021-06-17)


### Bug Fixes

* Removed reference to internal development API ([b38ceb](https://github.com/tradesafe/plugin-woocommerce/commit/b38cebea7e414e31ab87979f7fca8593d6784483))

---

## [1.0.3](https://github.com/tradesafe/plugin-woocommerce/compare/v1.0.2...v1.0.3) (2021-06-17)


### Bug Fixes

* Updated exclude list to fix issue where not all files are deployed wordpress.org ([18caec](https://github.com/tradesafe/plugin-woocommerce/commit/18caeccdef559f0215e07a9125a51deca1096133))

---

## [1.0.2](https://github.com/tradesafe/tradesafe-payment-gateway/compare/v1.0.1...v1.0.2) (2021-06-14)


---

## [1.0.1](https://github.com/tradesafe/tradesafe-payment-gateway/compare/v1.0.0...v1.0.1) (2021-06-11)


### Bug Fixes

* Updated plugin info and links for final release ([74c9a6](https://github.com/tradesafe/tradesafe-payment-gateway/commit/74c9a62da904be149b53b405950380eff037e61b))

---

## [1.0.0](https://github.com/tradesafe/woocommerce-tradesafe-gateway/compare/53203b526cdc2551bec20f42649b2e1be2baeeb7...v1.0.0) (2021-06-08)


### Features

* Added notice for users about bank account details ([c1cfa2](https://github.com/tradesafe/woocommerce-tradesafe-gateway/commit/c1cfa2123f043cb9f5d3f0fc460814d3394a4cb8))
* Added settings allow or disallow user to change order/transaction state ([681d82](https://github.com/tradesafe/woocommerce-tradesafe-gateway/commit/681d82a09bdb5046f60d7bbbd14a4f684c14d34e))
* Added token validation for buyers and sellers to ensure their account is completed ([64704d](https://github.com/tradesafe/woocommerce-tradesafe-gateway/commit/64704d5336fbb4422b4b36c6c4b68294bc24e595))

### Bug Fixes

* Calculate cart total without escrow fee ([606924](https://github.com/tradesafe/woocommerce-tradesafe-gateway/commit/606924edc1b2d5409c2822d31a3361b68bf6fa28))
* Display accept button on order in proccessing state ([6d7896](https://github.com/tradesafe/woocommerce-tradesafe-gateway/commit/6d78961c3cdba02805c50592cea80dcb811728e4))
* Updated field type for tax number ([0499d0](https://github.com/tradesafe/woocommerce-tradesafe-gateway/commit/0499d089842f26a4136ba5a2a16f0387a8362b96))
* Updated get option function to return single value for validation check ([679d45](https://github.com/tradesafe/woocommerce-tradesafe-gateway/commit/679d45ef057fbe3eedcba7954dea93f3a39cb4c9))
* Updated tradesafe api library ([340bfa](https://github.com/tradesafe/woocommerce-tradesafe-gateway/commit/340bfa65324a7713b1aba128452f7dbe8f1f3fa4))
* Updated variable name broken by refactoring ([9518ea](https://github.com/tradesafe/woocommerce-tradesafe-gateway/commit/9518ea629869031e8a280950bc4c9d8b76e7aa6f))
* Updated variable name for tradesafe fee allocation ([a0bcb2](https://github.com/tradesafe/woocommerce-tradesafe-gateway/commit/a0bcb2ceabd7d1d0b86e72eccb7642598b4ebfd6))

##### Settings

* Added missing section definition and admin settings page ([72eb1f](https://github.com/tradesafe/woocommerce-tradesafe-gateway/commit/72eb1f1f6213779b2ad5ed254163660aa1519c28))

---

