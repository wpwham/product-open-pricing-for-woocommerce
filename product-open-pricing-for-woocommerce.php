<?php
/*
Plugin Name: Product Open Pricing (Name Your Price) for WooCommerce
Plugin URI: https://wpcodefactory.com/item/product-open-pricing-woocommerce/
Description: Open price (i.e. Name your price) products for WooCommerce.
Version: 1.1.3
Author: Algoritmika Ltd
Author URI: http://www.algoritmika.com
Text Domain: product-open-pricing-for-woocommerce
Domain Path: /langs
Copyright: © 2018 Algoritmika Ltd.
WC tested up to: 3.3
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Check if WooCommerce is active
$plugin = 'woocommerce/woocommerce.php';
if (
	! in_array( $plugin, apply_filters( 'active_plugins', get_option( 'active_plugins', array() ) ) ) &&
	! ( is_multisite() && array_key_exists( $plugin, get_site_option( 'active_sitewide_plugins', array() ) ) )
) {
	return;
}

if ( 'product-open-pricing-for-woocommerce.php' === basename( __FILE__ ) ) {
	// Check if Pro is active, if so then return
	$plugin = 'product-open-pricing-for-woocommerce-pro/product-open-pricing-for-woocommerce-pro.php';
	if (
		in_array( $plugin, apply_filters( 'active_plugins', get_option( 'active_plugins', array() ) ) ) ||
		( is_multisite() && array_key_exists( $plugin, get_site_option( 'active_sitewide_plugins', array() ) ) )
	) {
		return;
	}
}

if ( ! class_exists( 'Alg_WC_Product_Open_Pricing' ) ) :

/**
 * Main Alg_WC_Product_Open_Pricing Class
 *
 * @class   Alg_WC_Product_Open_Pricing
 * @version 1.1.2
 * @since   1.0.0
 */
final class Alg_WC_Product_Open_Pricing {

	/**
	 * Plugin version.
	 *
	 * @var   string
	 * @since 1.0.0
	 */
	public $version = '1.1.3';

	/**
	 * @var   Alg_WC_Product_Open_Pricing The single instance of the class
	 * @since 1.0.0
	 */
	protected static $_instance = null;

	/**
	 * Main Alg_WC_Product_Open_Pricing Instance
	 *
	 * Ensures only one instance of Alg_WC_Product_Open_Pricing is loaded or can be loaded.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 * @static
	 * @return  Alg_WC_Product_Open_Pricing - Main instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Alg_WC_Product_Open_Pricing Constructor.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 * @access  public
	 */
	function __construct() {

		// Set up localisation
		load_plugin_textdomain( 'product-open-pricing-for-woocommerce', false, dirname( plugin_basename( __FILE__ ) ) . '/langs/' );

		// Include required files
		$this->includes();

		// Settings & Scripts
		if ( is_admin() ) {
			add_filter( 'woocommerce_get_settings_pages', array( $this, 'add_woocommerce_settings_tab' ) );
			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'action_links' ) );
		}
	}

	/**
	 * Show action links on the plugin screen
	 *
	 * @version 1.1.0
	 * @since   1.0.0
	 * @param   mixed $links
	 * @return  array
	 */
	function action_links( $links ) {
		$custom_links = array();
		$custom_links[] = '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=alg_wc_product_open_pricing' ) . '">' . __( 'Settings', 'woocommerce' ) . '</a>';
		if ( 'product-open-pricing-for-woocommerce.php' === basename( __FILE__ ) ) {
			$custom_links[] = '<a href="https://wpcodefactory.com/item/product-open-pricing-woocommerce/">' . __( 'Unlock All', 'product-open-pricing-for-woocommerce' ) . '</a>';
		}
		return array_merge( $custom_links, $links );
	}

	/**
	 * Include required core files used in admin and on the frontend.
	 *
	 * @version 1.1.2
	 * @since   1.0.0
	 */
	function includes() {
		// Settings
		require_once( 'includes/admin/class-alg-wc-product-open-pricing-settings-section.php' );
		$this->settings = array();
		$this->settings['general'] = require_once( 'includes/admin/class-alg-wc-product-open-pricing-settings-general.php' );
		if ( is_admin() && get_option( 'alg_wc_product_open_pricing_version', '' ) !== $this->version ) {
			foreach ( $this->settings as $section ) {
				foreach ( $section->get_settings() as $value ) {
					if ( isset( $value['default'] ) && isset( $value['id'] ) ) {
						$autoload = isset( $value['autoload'] ) ? ( bool ) $value['autoload'] : true;
						add_option( $value['id'], $value['default'], '', ( $autoload ? 'yes' : 'no' ) );
					}
				}
			}
			update_option( 'alg_wc_product_open_pricing_version', $this->version );
		}
		// Metaboxes (per Product Settings)
		require_once( 'includes/admin/class-alg-wc-product-open-pricing-settings-per-product.php' );
		// Core
		require_once( 'includes/class-alg-wc-product-open-pricing-core.php' );
	}

	/**
	 * Add Product Open Pricing settings tab to WooCommerce settings.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function add_woocommerce_settings_tab( $settings ) {
		$settings[] = include( 'includes/admin/class-alg-wc-settings-product-open-pricing.php' );
		return $settings;
	}

	/**
	 * Get the plugin url.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 * @return  string
	 */
	function plugin_url() {
		return untrailingslashit( plugin_dir_url( __FILE__ ) );
	}

	/**
	 * Get the plugin path.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 * @return  string
	 */
	function plugin_path() {
		return untrailingslashit( plugin_dir_path( __FILE__ ) );
	}

}

endif;

if ( ! function_exists( 'alg_wc_product_open_pricing' ) ) {
	/**
	 * Returns the main instance of Alg_WC_Product_Open_Pricing to prevent the need to use globals.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 * @return  Alg_WC_Product_Open_Pricing
	 */
	function alg_wc_product_open_pricing() {
		return Alg_WC_Product_Open_Pricing::instance();
	}
}

alg_wc_product_open_pricing();
