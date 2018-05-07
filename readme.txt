=== Product Open Pricing (Name Your Price) for WooCommerce ===
Contributors: algoritmika, anbinder, karzin
Tags: woocommerce, product open pricing, open pricing, name your price
Requires at least: 4.4
Tested up to: 4.9
Stable tag: 1.1.3
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Open price (i.e. Name your price) products for WooCommerce.

== Description ==

**Product Open Pricing for WooCommerce** plugin lets you create open price (i.e. "name your price" or "pay your price") products in WooCommerce.

For each product you can set:

* Default (i.e. Suggested) price
* Minimum price
* Maximum price

You can also customize price step, frontend label, template and user messages.

= Feedback =
* We are open to your suggestions and feedback. Thank you for using or trying out one of our plugins!

== Installation ==

1. Upload the entire 'product-open-pricing-for-woocommerce' folder to the '/wp-content/plugins/' directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Start by visiting plugin settings at WooCommerce > Settings > Product Open Pricing.

== Changelog ==

= 1.1.3 - 07/05/2018 =
* Add min and max attributes for price input

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

= 1.1.3 =
* Add min and max attributes for price input