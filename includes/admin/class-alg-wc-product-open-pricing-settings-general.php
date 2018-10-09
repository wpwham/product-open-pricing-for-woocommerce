<?php
/**
 * Product Open Pricing for WooCommerce - General Section Settings
 *
 * @version 1.2.2
 * @since   1.0.0
 * @author  Algoritmika Ltd.
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Alg_WC_Product_Open_Pricing_Settings_General' ) ) :

class Alg_WC_Product_Open_Pricing_Settings_General extends Alg_WC_Product_Open_Pricing_Settings_Section {

	/**
	 * Constructor.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function __construct() {
		$this->id   = '';
		$this->desc = __( 'General', 'product-open-pricing-for-woocommerce' );
		parent::__construct();
	}

	/**
	 * get_section_settings.
	 *
	 * @version 1.2.2
	 * @since   1.0.0
	 */
	function get_section_settings() {
		$default_price_step = 1 / pow( 10, absint( get_option( 'woocommerce_price_num_decimals', 2 ) ) );
		$settings = array(
			array(
				'title'    => __( 'Product Open Pricing Options', 'product-open-pricing-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'alg_wc_product_open_pricing_options',
			),
			array(
				'title'    => __( 'Product Open Pricing (Name Your Price)', 'product-open-pricing-for-woocommerce' ),
				'desc'     => '<strong>' . __( 'Enable plugin', 'product-open-pricing-for-woocommerce' ) . '</strong>',
				'desc_tip' => __( 'Let your WooCommerce store customers enter price for the product manually.', 'product-open-pricing-for-woocommerce' ),
				'id'       => 'alg_wc_product_open_pricing_enabled',
				'default'  => 'yes',
				'type'     => 'checkbox',
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_product_open_pricing_options',
			),
			array(
				'title'    => __( 'General Options', 'product-open-pricing-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'alg_wc_product_open_pricing_messages_options',
			),
			array(
				'title'    => __( 'Frontend template', 'product-open-pricing-for-woocommerce' ),
				'desc_tip' => __( 'Here you can use' ) . ': ' . '%input_id%, %open_price_input%, %currency_symbol%, %minimum_price%, %max_price%',
				'id'       => 'alg_wc_product_open_pricing_frontend_template',
				'default'  => '<label for="%input_id%">'.__( 'Name Your Price', 'product-open-pricing-for-woocommerce' ).'</label> %open_price_input% %currency_symbol%',
				'type'     => 'textarea',
				'css'      => 'width:100%;',
			),
			array(
				'title'    => __( 'Enable step', 'product-open-pricing-for-woocommerce' ),
				'desc'     => __( 'Enables up/down ticker buttons from the text field', 'product-open-pricing-for-woocommerce' ),
				'id'       => 'alg_wc_product_open_pricing_enable_step',
				'default'  => 'yes',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'Price step', 'product-open-pricing-for-woocommerce' ),
				'id'       => 'alg_wc_product_open_pricing_price_step',
				'default'  => $default_price_step,
				'type'     => 'number',
				'custom_attributes' => array( 'step' => '0.000000001', 'min' => '0.000000001' ),
			),
			array(
				'title'    => __( 'Disable quantity selector', 'product-open-pricing-for-woocommerce' ),
				'desc'     => __( 'Disable', 'product-open-pricing-for-woocommerce' ),
				'id'       => 'alg_wc_product_open_pricing_disable_qty',
				'default'  => 'yes',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'Hide price', 'product-open-pricing-for-woocommerce' ),
				'desc'     => __( 'Hides original price on single product page', 'product-open-pricing-for-woocommerce' ),
				'id'       => 'alg_wc_product_open_pricing_hide_price',
				'default'  => 'yes',
				'type'     => 'checkbox',
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_product_open_pricing_messages_options',
			),

			// Messages
			array(
				'title'    => __( 'Messages', 'product-open-pricing-for-woocommerce' ),
				'desc'     => __( 'Confirmation/Error messages, displayed after customer try to purchase', 'product-open-pricing-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'alg_wc_product_open_pricing_messages',
			),
			array(
				'title'    => __( 'Message on empty price', 'product-open-pricing-for-woocommerce' ),
				'id'       => 'alg_wc_product_open_pricing_messages_required',
				'default'  => __( 'Price is required!', 'product-open-pricing-for-woocommerce' ),
				'type'     => 'text',
				'css'      => 'width:100%;',
			),
			array(
				'title'    => __( 'Message on price too small', 'product-open-pricing-for-woocommerce' ),
				'id'       => 'alg_wc_product_open_pricing_messages_too_small',
				'default'  => __( 'Entered price is too small!', 'product-open-pricing-for-woocommerce' ),
				'type'     => 'text',
				'css'      => 'width:100%;',
			),
			array(
				'title'    => __( 'Message on price too big', 'product-open-pricing-for-woocommerce' ),
				'id'       => 'alg_wc_product_open_pricing_messages_too_big',
				'default'  => __( 'Entered price is too big!', 'product-open-pricing-for-woocommerce' ),
				'type'     => 'text',
				'css'      => 'width:100%;',
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_product_open_pricing_messages',
			),

			// Loop
			array(
				'title'    => __( 'Loop', 'product-open-pricing-for-woocommerce' ),
				'desc'     => __( 'Options regarding WooCommerce loop, like shop or category pages', 'product-open-pricing-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'alg_wc_product_open_pricing_loop',
			),
			array(
				'title'    => __( 'Display on loop', 'product-open-pricing-for-woocommerce' ),
				'desc'     => __( 'Displays the open pricing input field on loop', 'product-open-pricing-for-woocommerce' ),
				'id'       => 'alg_wc_product_open_pricing_field_on_loop',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'Frontend loop template', 'product-open-pricing-for-woocommerce' ),
				'desc'     => __( 'Overwrites the Frontend template option if input field displayed on loop', 'product-open-pricing-for-woocommerce' ),
				'desc_tip' => __( 'Here you can use' ) . ': ' . '%input_id%, %open_price_input%, %currency_symbol%, %minimum_price%, %max_price%',
				'id'       => 'alg_wc_product_open_pricing_frontend_loop_template',
				'default'  => '<label for="%input_id%">'.__( 'Name Your Price', 'product-open-pricing-for-woocommerce' ).'</label> %open_price_input% %currency_symbol%',
				'type'     => 'textarea',
				'css'      => 'width:100%;',
			),
			array(
				'title'    => __( 'Hide price', 'product-open-pricing-for-woocommerce' ),
				'desc'     => __( 'Hides original price on loop', 'product-open-pricing-for-woocommerce' ),
				'id'       => 'alg_wc_product_open_pricing_loop_hide_price',
				'default'  => 'yes',
				'type'     => 'checkbox',
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_product_open_pricing_loop',
			),
		);
		return $settings;
	}

}

endif;

return new Alg_WC_Product_Open_Pricing_Settings_General();
