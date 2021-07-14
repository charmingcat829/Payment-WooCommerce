<!--- BEGIN HEADER -->
# Changelog

All notable changes to this project will be documented in this file.
<!--- END HEADER -->

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

