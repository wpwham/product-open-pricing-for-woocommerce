<?php
/**
 * Product Open Pricing for WooCommerce - Per Product Section Settings
 *
 * @version 1.3.0
 * @since   1.0.0
 * @author  Algoritmika Ltd.
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Alg_WC_Product_Open_Pricing_Settings_Per_Product' ) ) :

class Alg_WC_Product_Open_Pricing_Settings_Per_Product {

	/**
	 * Constructor.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function __construct() {
		$this->id = 'per_product';
		if ( 'yes' === get_option( 'alg_wc_product_open_pricing_enabled', 'yes' ) ) {
			add_action( 'add_meta_boxes',                                  array( $this, 'add_meta_box' ) );
			add_action( 'save_post_product',                               array( $this, 'save_meta_box' ), PHP_INT_MAX, 2 );
			add_filter( 'alg_wc_product_open_pricing_save_meta_box_value', array( $this, 'save_meta_box_value' ), PHP_INT_MAX, 2 );
			add_action( 'admin_notices',                                   array( $this, 'admin_notices' ) );
		}
	}

	/**
	 * save_meta_box_value.
	 *
	 * @version 1.3.0
	 * @since   1.0.0
	 */
	function save_meta_box_value( $option_value, $option_name ) {
		if ( 'no' === $option_value || apply_filters( 'alg_wc_product_open_pricing', false, 'per_product_settings' ) || 'alg_wc_product_open_pricing_enabled' != $option_name ) {
			return $option_value;
		}
		$args = array(
			'post_type'      => 'product',
			'post_status'    => 'any',
			'posts_per_page' => 1,
			'meta_key'       => '_' . $option_name,
			'meta_value'     => 'yes',
			'post__not_in'   => array( get_the_ID() ),
			'fields'         => 'ids',
		);
		$loop = new WP_Query( $args );
		$c = $loop->found_posts + 1;
		if ( $c >= 2 ) {
			add_filter( 'redirect_post_location', array( $this, 'add_notice_query_var' ), 99 );
			return 'no';
		}
		return $option_value;
	}

	/**
	 * add_notice_query_var.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function add_notice_query_var( $location ) {
		remove_filter( 'redirect_post_location', array( $this, 'add_notice_query_var' ), 99 );
		return add_query_arg( array( 'alg_wc_product_open_pricing_admin_notice' => true ), $location );
	}

	/**
	 * admin_notices.
	 *
	 * @version 1.3.0
	 * @since   1.0.0
	 * @todo    [dev] (maybe) three?
	 */
	function admin_notices() {
		if ( ! isset( $_GET['alg_wc_product_open_pricing_admin_notice'] ) ) {
			return;
		}
		echo '<div class="error"><p><div class="message">' .
			sprintf( 'Free plugin\'s version is limited to only one open pricing product enabled at a time. You will need to get <a href="%s" target="_blank">Product Open Pricing for WooCommerce Pro</a> to add unlimited number of open pricing products.', 'https://wpwham.com/products/product-open-pricing-name-your-price-for-woocommerce/' ) .
		'</div></p></div>';
	}

	/**
	 * get_meta_box_options.
	 *
	 * @version 1.2.5
	 * @since   1.0.0
	 */
	function get_meta_box_options() {
		$options = array(
			array(
				'name'       => 'alg_wc_product_open_pricing_enabled',
				'default'    => 'no',
				'type'       => 'select',
				'options'    => array(
					'yes' => __( 'Yes', 'product-open-pricing-for-woocommerce' ),
					'no'  => __( 'No', 'product-open-pricing-for-woocommerce' ),
				),
				'title'      => '<strong>' . __( 'Enabled', 'product-open-pricing-for-woocommerce' ) . '</strong>',
			),
			array(
				'name'       => 'alg_wc_product_open_pricing_default_price',
				'default'    => '',
				'type'       => 'price',
				'title'      => __( 'Default price', 'product-open-pricing-for-woocommerce' ) . ' (' . get_woocommerce_currency_symbol() . ')',
				'tooltip'    => __( 'Default (i.e. Suggested) price', 'product-open-pricing-for-woocommerce' ),
			),
			array(
				'name'       => 'alg_wc_product_open_pricing_min_price',
				'default'    => 1,
				'type'       => 'price',
				'title'      => __( 'Min price', 'product-open-pricing-for-woocommerce' ) . ' (' . get_woocommerce_currency_symbol() . ')',
			),
			array(
				'name'       => 'alg_wc_product_open_pricing_max_price',
				'default'    => '',
				'type'       => 'price',
				'title'      => __( 'Max price', 'product-open-pricing-for-woocommerce' ) . ' (' . get_woocommerce_currency_symbol() . ')',
			),
		);
		return $options;
	}

	/**
	 * save_meta_box.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function save_meta_box( $post_id, $post ) {
		// Check that we are saving with current metabox displayed.
		if ( ! isset( $_POST[ 'alg_wc_product_open_pricing_' . $this->id . '_save_post' ] ) ) {
			return;
		}
		// Save options
		foreach ( $this->get_meta_box_options() as $option ) {
			if ( 'title' === $option['type'] ) {
				continue;
			}
			$is_enabled = ( isset( $option['enabled'] ) && 'no' === $option['enabled'] ) ? false : true;
			if ( $is_enabled ) {
				$option_value  = ( isset( $_POST[ $option['name'] ] ) ) ? $_POST[ $option['name'] ] : $option['default'];
				$the_post_id   = ( isset( $option['product_id'] )     ) ? $option['product_id']     : $post_id;
				$the_meta_name = ( isset( $option['meta_name'] ) )      ? $option['meta_name']      : '_' . $option['name'];
				update_post_meta( $the_post_id, $the_meta_name, apply_filters( 'alg_wc_product_open_pricing_save_meta_box_value', $option_value, $option['name'] ) );
			}
		}
	}

	/**
	 * add_meta_box.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function add_meta_box() {
		add_meta_box(
			'alg_wc_product_open_pricing_' . $this->id,
			__( 'Product Open Pricing', 'product-open-pricing-for-woocommerce' ),
			array( $this, 'create_meta_box' ),
			'product',
			'normal',
			'high'
		);
	}

	/**
	 * create_meta_box.
	 *
	 * @version 1.2.5
	 * @since   1.0.0
	 */
	function create_meta_box() {
		$current_post_id = get_the_ID();
		$html = '';
		$html .= '<table class="widefat striped">';
		foreach ( $this->get_meta_box_options() as $option ) {
			$is_enabled = ( isset( $option['enabled'] ) && 'no' === $option['enabled'] ) ? false : true;
			if ( $is_enabled ) {
				if ( 'title' === $option['type'] ) {
					$html .= '<tr>';
					$html .= '<th colspan="2" style="text-align:left;">' . $option['title'] . '</th>';
					$html .= '</tr>';
				} else {
					$custom_attributes = '';
					$the_post_id   = ( isset( $option['product_id'] ) ) ? $option['product_id'] : $current_post_id;
					$the_meta_name = ( isset( $option['meta_name'] ) )  ? $option['meta_name']  : '_' . $option['name'];
					if ( get_post_meta( $the_post_id, $the_meta_name ) ) {
						$option_value = get_post_meta( $the_post_id, $the_meta_name, true );
					} else {
						$option_value = ( isset( $option['default'] ) ) ? $option['default'] : '';
					}
					$input_ending = '';
					if ( 'select' === $option['type'] ) {
						if ( isset( $option['multiple'] ) ) {
							$custom_attributes = ' multiple';
							$option_name       = $option['name'] . '[]';
						} else {
							$option_name       = $option['name'];
						}
						$options = '';
						foreach ( $option['options'] as $select_option_key => $select_option_value ) {
							$selected = '';
							if ( is_array( $option_value ) ) {
								foreach ( $option_value as $single_option_value ) {
									$selected .= selected( $single_option_value, $select_option_key, false );
								}
							} else {
								$selected = selected( $option_value, $select_option_key, false );
							}
							$options .= '<option value="' . $select_option_key . '" ' . $selected . '>' . $select_option_value . '</option>';
						}
					} else {
						$input_ending = ' id="' . $option['name'] . '" name="' . $option['name'] . '" value="' . $option_value . '">';
						if ( isset( $option['custom_attributes'] ) ) {
							$input_ending = ' ' . $option['custom_attributes'] . $input_ending;
						}
					}
					switch ( $option['type'] ) {
						case 'price':
							$field_html = '<input class="short wc_input_price" type="number" step="0.0001"' . $input_ending;
							break;
						case 'date':
							$field_html = '<input class="input-text" display="date" type="text"' . $input_ending;
							break;
						case 'textarea':
							$field_html = '<textarea style="min-width:300px;"' . ' id="' . $option['name'] . '" name="' . $option['name'] . '">' . $option_value . '</textarea>';
							break;
						case 'select':
							$field_html = '<select' . $custom_attributes . ' id="' . $option['name'] . '" name="' . $option_name . '">' . $options . '</select>';
							break;
						default:
							$field_html = '<input class="short" type="' . $option['type'] . '"' . $input_ending;
							break;
					}
					$html .= '<tr>';
					$maybe_tooltip = ( isset( $option['tooltip'] ) && '' != $option['tooltip'] ) ?
						'<span style="float:right;">' . wc_help_tip( $option['tooltip'], true ) . '</span>' :
						'';
					$html .= '<th style="text-align:left;width:150px;">' . $option['title'] . $maybe_tooltip . '</th>';
					if ( isset( $option['desc'] ) && '' != $option['desc'] ) {
						$html .= '<td style="font-style:italic;">' . $option['desc'] . '</td>';
					}
					$html .= '<td>' . $field_html . '</td>';
					$html .= '</tr>';
				}
			}
		}
		$html .= '</table>';
		$html .= '<input type="hidden" name="alg_wc_product_open_pricing_' . $this->id . '_save_post" value="alg_wc_product_open_pricing_' . $this->id . '_save_post">';
		echo $html;
	}

}

endif;

return new Alg_WC_Product_Open_Pricing_Settings_Per_Product();
