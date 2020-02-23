=== Product Open Pricing (Name Your Price) for WooCommerce ===
Contributors: wpwham
Tags: woocommerce, product open pricing, open pricing, name your price
Requires at least: 4.4
Tested up to: 5.3
Stable tag: 1.4.4
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Open price (i.e. Name your price) products for WooCommerce.

== Description ==

**Product Open Pricing for WooCommerce** plugin lets you create open price (i.e. "name your price" or "pay your price") products in WooCommerce.

For each product you can optionally set:

* **Default** (i.e. Suggested) price
* **Minimum** price
* **Maximum** price

In general settings you can also:

* Customize **frontend template**
* Enable/disable open price input on **shop and category pages**
* Set **input style** and pattern
* Enable/disable **quantity selector**
* Set **price step** on frontend
* Enable/disable **step ticker**
* Optionally **show the original price** (for reference)
* Customize **user messages**

It's important to note that the free version allows enabling open pricing for only one product at a time.

= Premium =

The [premium version](https://wpwham.com/products/product-open-pricing-name-your-price-for-woocommerce/ "Upgrade to Product Open Pricing for WooCommerce Pro") will allow setting open pricing on multiple (i.e. unlimited number) products without restrictions.

= More =
* We are open to your suggestions and feedback.
* Visit the [Product Open Pricing (Name Your Price) for WooCommerce plugin page](https://wpwham.com/products/product-open-pricing-name-your-price-for-woocommerce/).
* Thank you for using or trying out one of our plugins!

== Installation ==

1. Upload the entire plugin folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the "Plugins" menu in WordPress.
3. Start by visiting plugin settings at "WooCommerce > Settings > Product Open Pricing".

== Changelog ==

= 1.4.4 - 2020-02-23 =
* UPDATE: bump tested versions

= 1.4.3 - 2019-12-18 =
* FIX: issue where min_price=0 ignored.

= 1.4.2 - 2019-11-15 =
* FIX: if "force number of decimals" is checked in the settings, a user can't change the price field on a mobile device.

= 1.4.1 - 2019-11-15 =
* UPDATE: bump tested versions

= 1.4.0 - 2019-09-11 =
* FIX: removed "min" and "max" attributes from price input field. (This was causing browser-based validation messages to appear before our own validation messages. If for some reason you want to reverse this change, use the filter 'wpw_product_open_pricing_input_custom_attributes'.)
* UPDATE: updated .pot file for translations

= 1.3.2 - 2019-03-19 =
* Dev - Plugin author data updated.

= 1.3.1 - 2019-02-22 =
* Dev - Single Product Page Options - "Input field position" options added.
* Dev - Shop/Category Page Options - "Input field loop position" options added.
* Dev - Frontend Options - "Force number of decimals" option added.

= 1.3.0 - 2019-02-08 =
* Dev - Advanced - "Fix mini cart" option added.
* Dev - Admin - "Add Open Pricing column" option added.
* Dev - Messages - Default values for "Message on price too low" and "Message on price too high" changed.
* Dev - "Raw" input is now allowed in all corresponding settings.
* Dev - Major code refactoring and clean up.
* Dev - Admin settings restyled.

= 1.2.5 - 2019-01-29 =
* Dev - "Input style" option added.
* Dev - "Input pattern" option added.
* Dev - Admin settings restyled and descriptions updated.
* Dev - Plugin URI updated.

= 1.2.4 - 2019-01-17 =
* Fix - "Enable step ticker" option fixed.

= 1.2.3 - 2018-10-21 =
* Add warning on description about free version restriction which allows open pricing for only one product at a time.

= 1.2.2 - 2018-10-09 =
* Fix compatibility with Currency Switcher on getting value from request.
* Add option to display original price on both loop and single product page.

= 1.2.1 - 2018-08-18 =
* Fix input sanitizing.

= 1.2.0 - 2018-08-17 =
* Fix woocommerce_loop_add_to_cart_link filter with correct args quantity.

= 1.1.9 - 2018-08-16 =
* Add option to include open input field on loop.
* Add option to overwrite frontend template option if input field is displayed on loop.
* Improve open price input sanitizing.
* Add compatibility with ajax add to cart if loop option is enabled.
* Remove Frontend Label option.
* Fix min/max prices when using currency switcher.
* Rearrange message options on admin.

= 1.1.8 - 2018-08-06 =
* Improve compatibility with Currency Switcher for WooCommerce plugin converting min and max value.

= 1.1.7 - 2018-08-06 =
* Change the way to override product prices, replacing 'get_cart_item_open_price_from_session()' by 'override_product_price()' and replacing 'woocommerce_get_cart_item_from_session' filter by 'woocommerce_before_calculate_totals'.
* Fix compatibility with Currency Switcher for WooCommerce plugin.

= 1.1.6 - 2018-07-26 =
* Add compatibility with Currency Switcher for WooCommerce plugin.
* Add 'aopwc_frontend_input_filter' filter to setup where the frontend field is going to be displayed. Default is 'woocommerce_before_add_to_cart_button'.

= 1.1.5 - 2018-06-21 =
* Add option to remove up/down ticker buttons from the input field.

= 1.1.4 - 2018-06-18 =
* Add %minimum_price% and %max_price% to frontend template.
* Add span to %currency_symbol% template.
* Update "WC tested up to".

= 1.1.3 - 2018-05-07 =
* Add min and max attributes for price input.

= 1.1.2 - 2018-04-10 =
* Dev - "Price step" step decreased to `0.000000001`.
* Dev - Admin settings descriptions updated.
* Dev - Plugin settings array stored as main class property.

= 1.1.1 - 2018-01-02 =
* Dev - "Disable Quantity Selector" option added.
* Dev - Admin settings restyled.
* Dev - "WC tested up to" added to plugin header.

= 1.1.0 - 2017-07-25 =
* Dev - WooCommerce v3 compatibility - Getting product ID and status with functions (instead of accessing properties directly).
* Dev - WooCommerce v3 compatibility - `woocommerce_get_price` replaced with `woocommerce_product_get_price`.
* Dev - Autoloading plugin options.
* Dev - Link updated from http://coder.fm to https://wpcodefactory.com.
* Dev - Plugin header ("Text Domain" etc.) updated.
* Dev - POT file added.
* Dev - Code cleanup.

= 1.0.0 - 2017-02-01 =
* Initial Release.
