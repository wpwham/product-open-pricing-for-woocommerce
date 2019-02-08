=== Product Open Pricing (Name Your Price) for WooCommerce ===
Contributors: algoritmika, anbinder, karzin
Tags: woocommerce, product open pricing, open pricing, name your price
Requires at least: 4.4
Tested up to: 5.0
Stable tag: 1.3.0
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
* Enable/disable open price input **shop and category pages**
* Set **input style** and pattern
* Enable/disable **step ticker**
* Set **price step** on frontend
* Enable/disable **quantity selector**
* Optionally **show the original price** (for reference)
* Customize **user messages**

It's important to note the free version allows enabling open pricing for only one product at a time.

= Premium =

The [premium version](https://wpfactory.com/item/product-open-pricing-woocommerce/ "Upgrade to Product Open Pricing for WooCommerce Pro") will allow setting open pricing on multiple (i.e. unlimited number) products without restrictions.

= More =
* We are open to your suggestions and feedback.
* Visit the [Product Open Pricing (Name Your Price) for WooCommerce plugin page](https://wpfactory.com/item/product-open-pricing-woocommerce).
* Thank you for using or trying out one of our plugins!

== Installation ==

1. Upload the entire plugin folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the "Plugins" menu in WordPress.
3. Start by visiting plugin settings at "WooCommerce > Settings > Product Open Pricing".

== Changelog ==

= 1.3.0 - 08/02/2019 =
* Dev - Advanced - "Fix mini cart" option added.
* Dev - Messages - Default values for "Message on price too low" and "Message on price too high" chnaged.
* Dev - "Raw" input is now allowed in all suitable settings.
* Dev - Major code refactoring and clean up.
* Dev - Admin settings restyled.

= 1.2.5 - 29/01/2019 =
* Dev - "Input style" option added.
* Dev - "Input pattern" option added.
* Dev - Admin settings restyled and descriptions updated.
* Dev - Plugin URI updated.

= 1.2.4 - 17/01/2019 =
* Fix - "Enable step ticker" option fixed.

= 1.2.3 - 21/10/2018 =
* Add warning on description about free version restriction which allows open pricing for only one product at a time.

= 1.2.2 - 09/10/2018 =
* Fix compatibility with Currency Switcher on getting value from request.
* Add option to display original price on both loop and single product page.

= 1.2.1 - 18/08/2018 =
* Fix input sanitizing.

= 1.2.0 - 17/08/2018 =
* Fix woocommerce_loop_add_to_cart_link filter with correct args quantity.

= 1.1.9 - 16/08/2018 =
* Add option to include open input field on loop.
* Add option to overwrite frontend template option if input field is displayed on loop.
* Improve open price input sanitizing.
* Add compatibility with ajax add to cart if loop option is enabled.
* Remove Frontend Label option.
* Fix min/max prices when using currency switcher.
* Rearrange message options on admin.

= 1.1.8 - 06/08/2018 =
* Improve compatibility with Currency Switcher for WooCommerce plugin converting min and max value.

= 1.1.7 - 06/08/2018 =
* Change the way to override product prices, replacing 'get_cart_item_open_price_from_session()' by 'override_product_price()' and replacing 'woocommerce_get_cart_item_from_session' filter by 'woocommerce_before_calculate_totals'.
* Fix compatibility with Currency Switcher for WooCommerce plugin.

= 1.1.6 - 26/07/2018 =
* Add compatibility with Currency Switcher for WooCommerce plugin.
* Add 'aopwc_frontend_input_filter' filter to setup where the frontend field is going to be displayed. Default is 'woocommerce_before_add_to_cart_button'.

= 1.1.5 - 21/06/2018 =
* Add option to remove up/down ticker buttons from the input field.

= 1.1.4 - 18/06/2018 =
* Add %minimum_price% and %max_price% to frontend template.
* Add span to %currency_symbol% template.
* Update "WC tested up to".

= 1.1.3 - 07/05/2018 =
* Add min and max attributes for price input.

= 1.1.2 - 10/04/2018 =
* Dev - "Price step" step decreased to `0.000000001`.
* Dev - Admin settings descriptions updated.
* Dev - Plugin settings array stored as main class property.

= 1.1.1 - 02/01/2018 =
* Dev - "Disable Quantity Selector" option added.
* Dev - Admin settings restyled.
* Dev - "WC tested up to" added to plugin header.

= 1.1.0 - 25/07/2017 =
* Dev - WooCommerce v3 compatibility - Getting product ID and status with functions (instead of accessing properties directly).
* Dev - WooCommerce v3 compatibility - `woocommerce_get_price` replaced with `woocommerce_product_get_price`.
* Dev - Autoloading plugin options.
* Dev - Link updated from http://coder.fm to https://wpcodefactory.com.
* Dev - Plugin header ("Text Domain" etc.) updated.
* Dev - POT file added.
* Dev - Code cleanup.

= 1.0.0 - 01/02/2017 =
* Initial Release.

== Upgrade Notice ==

= 1.0.0 =
This is the first release of the plugin.
