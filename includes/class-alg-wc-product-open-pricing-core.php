<?php
/**
 * Product Open Pricing for WooCommerce - Core Class
 *
 * @version 1.1.6
 * @since   1.0.0
 * @author  Algoritmika Ltd.
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Alg_WC_Product_Open_Pricing_Core' ) ) :

class Alg_WC_Product_Open_Pricing_Core {

	/**
	 * Constructor.
	 *
	 * @version 1.1.6
	 * @since   1.0.0
	 */
	function __construct() {
		if ( 'yes' === get_option( 'alg_wc_product_open_pricing_enabled', 'yes' ) ) {
			$this->is_wc_version_below_3 = version_compare( get_option( 'woocommerce_version', null ), '3.0.0', '<' );
			$get_price_filter = ( $this->is_wc_version_below_3 ? 'woocommerce_get_price' : 'woocommerce_product_get_price' );
			add_filter( $get_price_filter,                        array( $this, 'get_open_price' ), PHP_INT_MAX, 2 );
			add_filter( 'woocommerce_get_price_html',             array( $this, 'hide_original_price' ), PHP_INT_MAX, 2 );
			add_filter( 'woocommerce_get_variation_price_html',   array( $this, 'hide_original_price' ), PHP_INT_MAX, 2 );
			if ( 'yes' === get_option( 'alg_wc_product_open_pricing_disable_qty', 'yes' ) ) {
				add_filter( 'woocommerce_is_sold_individually',   array( $this, 'hide_quantity_input_field' ), PHP_INT_MAX, 2 );
			}
			add_filter( 'woocommerce_is_purchasable',             array( $this, 'is_purchasable' ), PHP_INT_MAX, 2 );
			add_filter( 'woocommerce_product_supports',           array( $this, 'disable_add_to_cart_ajax' ), PHP_INT_MAX, 3 );
			add_filter( 'woocommerce_product_add_to_cart_url',    array( $this, 'add_to_cart_url' ), PHP_INT_MAX, 2 );
			add_filter( 'woocommerce_product_add_to_cart_text',   array( $this, 'add_to_cart_text' ), PHP_INT_MAX, 2 );
			add_filter( 'woocommerce_add_to_cart_validation',     array( $this, 'validate_open_price_on_add_to_cart' ), PHP_INT_MAX, 2 );
			add_filter( 'woocommerce_add_cart_item_data',         array( $this, 'add_open_price_to_cart_item_data' ), PHP_INT_MAX, 3 );
			add_filter( 'woocommerce_add_cart_item',              array( $this, 'add_open_price_to_cart_item' ), PHP_INT_MAX, 2 );
			add_action( 'woocommerce_before_calculate_totals',    array( $this, 'convert_price_if_using_currency_switcher' ), 10, 1 );
			add_filter( 'woocommerce_get_cart_item_from_session', array( $this, 'get_cart_item_open_price_from_session' ), PHP_INT_MAX, 3 );

			$placeholder_filter = sanitize_text_field( apply_filters( 'aopwc_frontend_input_filter', 'woocommerce_before_add_to_cart_button' ) );
			add_action( $placeholder_filter,  array( $this, 'add_open_price_input_field_to_frontend' ), PHP_INT_MAX );
		}
	}

	/**
	 * Converts pricing, if using currency switcher
	 *
	 * @version 1.1.6
	 * @since   1.1.6
	 */
	public function convert_price_if_using_currency_switcher( $cart_obj ) {

		if (
			( is_admin() && ! defined( 'DOING_AJAX' ) ) ||
			! function_exists( 'alg_wc_currency_switcher_plugin' )
		) {
			return;
		}
		foreach ( $cart_obj->get_cart() as $key => $item ) {
			if (
				! isset( $item['alg_open_price_curr'] ) ||
				! isset( $item['alg_open_price'] )
			) {
				continue;
			}

			$current_currency_code = alg_get_current_currency_code();
			$default_currency      = get_option( 'woocommerce_currency' );

			// Converts Back, since WooCommerce gets in the way
			$final_value = alg_convert_price( array(
				'price'         => $item['alg_open_price'],
				'currency_from' => $current_currency_code,
				'currency'      => $default_currency,
				'format_price'  => 'no'
			) );
			$item['data']->set_price( $final_value );
			$item['alg_open_price'] = $final_value;

			// Converts again if different currency
			if (
				! empty( $item['alg_open_price_curr'] ) &&
				$item['alg_open_price_curr'] != $current_currency_code
			) {
				$final_value                 = alg_convert_price( array(
					'price'         => $item['alg_open_price'],
					'currency_from' => $item['alg_open_price_curr'],
					'currency'      => $current_currency_code,
					'format_price'  => 'no'
				) );
				$item['alg_open_price_curr'] = $current_currency_code;
				$item['data']->set_price( $final_value );
			}
		}
	}

	/**
	 * get_product_or_variation_parent_id.
	 *
	 * @version 1.1.0
	 * @since   1.1.0
	 * @todo    (maybe) just product id (i.e. no parent for variation)
	 */
	function get_product_or_variation_parent_id( $_product ) {
		return ( $this->is_wc_version_below_3 ? $_product->id : ( $_product->is_type( 'variation' ) ? $_product->get_parent_id() : $_product->get_id() ) );
	}

	/**
	 * get_product_status.
	 *
	 * @version 1.1.0
	 * @since   1.1.0
	 */
	function get_product_status( $_product ) {
		return ( $this->is_wc_version_below_3 ? $_product->post->post_status : $_product->get_status() );
	}

	/**
	 * is_open_price_product.
	 *
	 * @version 1.1.0
	 * @since   1.0.0
	 */
	function is_open_price_product( $_product ) {
		return ( 'yes' === get_post_meta( $this->get_product_or_variation_parent_id( $_product ), '_' . 'alg_wc_product_open_pricing_enabled', true ) );
	}

	/**
	 * disable_add_to_cart_ajax.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function disable_add_to_cart_ajax( $supports, $feature, $_product ) {
		if ( $this->is_open_price_product( $_product ) && 'ajax_add_to_cart' === $feature ) {
			$supports = false;
		}
		return $supports;
	}

	/**
	 * is_purchasable - makes products with no price set (i.e. empty price) still purchasable.
	 *
	 * @version 1.1.0
	 * @since   1.0.0
	 */
	function is_purchasable( $purchasable, $_product ) {
		if ( $this->is_open_price_product( $_product ) ) {
			$purchasable = true;
			if ( ! $_product->exists() ) {
				// Products must exist of course
				$purchasable = false;
			} elseif ( $this->get_product_status( $_product ) !== 'publish' && ! current_user_can( 'edit_post', $this->get_product_or_variation_parent_id( $_product ) ) ) {
				// Check the product is published
				$purchasable = false;
			}
		}
		return $purchasable;
	}

	/**
	 * add_to_cart_text.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function add_to_cart_text( $text, $_product ) {
		return ( $this->is_open_price_product( $_product ) ) ? __( 'Read more', 'woocommerce' ) : $text;
	}

	/**
	 * add_to_cart_url.
	 *
	 * @version 1.1.0
	 * @since   1.0.0
	 */
	function add_to_cart_url( $url, $_product ) {
		return ( $this->is_open_price_product( $_product ) ) ? get_permalink( $this->get_product_or_variation_parent_id( $_product ) ) : $url;
	}

	/**
	 * hide_quantity_input_field.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function hide_quantity_input_field( $return, $_product ) {
		return ( $this->is_open_price_product( $_product ) ) ? true : $return;
	}

	/**
	 * hide_original_price.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function hide_original_price( $price, $_product ) {
		return ( $this->is_open_price_product( $_product ) ) ? '' : $price;
	}

	/**
	 * get_open_price.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function get_open_price( $price, $_product ) {
		return ( $this->is_open_price_product( $_product ) && isset( $_product->alg_open_price ) ) ? $_product->alg_open_price : $price;
	}

	/**
	 * validate_open_price_on_add_to_cart.
	 *
	 * @version 1.1.0
	 * @since   1.0.0
	 */
	function validate_open_price_on_add_to_cart( $passed, $product_id ) {
		$_product = wc_get_product( $product_id );
		if ( $this->is_open_price_product( $_product ) ) {
			$min_price = get_post_meta( $product_id, '_' . 'alg_wc_product_open_pricing_min_price', true );
			$max_price = get_post_meta( $product_id, '_' . 'alg_wc_product_open_pricing_max_price', true );
			if ( $min_price > 0 ) {
				if ( ! isset( $_POST['alg_open_price'] ) || '' === $_POST['alg_open_price'] ) {
					wc_add_notice( get_option( 'alg_wc_product_open_pricing_messages_required', __( 'Price is required!', 'product-open-pricing-for-woocommerce' ) ), 'error' );
					return false;
				}
				if ( $_POST['alg_open_price'] < $min_price ) {
					wc_add_notice( get_option( 'alg_wc_product_open_pricing_messages_too_small', __( 'Entered price is too small!', 'product-open-pricing-for-woocommerce' ) ), 'error' );
					return false;
				}
			}
			if ( $max_price > 0 ) {
				if ( isset( $_POST['alg_open_price'] ) && $_POST['alg_open_price'] > $max_price ) {
					wc_add_notice( get_option( 'alg_wc_product_open_pricing_messages_too_big', __( 'Entered price is too big!', 'product-open-pricing-for-woocommerce' ) ), 'error' );
					return false;
				}
			}
		}
		return $passed;
	}

	/**
	 * get_cart_item_open_price_from_session.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function get_cart_item_open_price_from_session( $item, $values, $key ) {
		if ( array_key_exists( 'alg_open_price', $values ) ) {
			$item['data']->alg_open_price = $values['alg_open_price'];
		}
		return $item;
	}

	/**
	 * add_open_price_to_cart_item_data.
	 *
	 * @version 1.1.6
	 * @since   1.0.0
	 */
	function add_open_price_to_cart_item_data( $cart_item_data, $product_id, $variation_id ) {
		if ( isset( $_POST['alg_open_price'] ) ) {
			$cart_item_data['alg_open_price'] = $_POST['alg_open_price'];
		}
		if ( function_exists( 'alg_wc_currency_switcher_plugin' ) ) {
			$current_currency_code = alg_get_current_currency_code();
			$cart_item_data['alg_open_price_curr'] = $current_currency_code;
		}

		return $cart_item_data;
	}

	/**
	 * add_open_price_to_cart_item.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function add_open_price_to_cart_item( $cart_item_data, $cart_item_key ) {
		if ( isset( $cart_item_data['alg_open_price'] ) ) {
			$cart_item_data['data']->alg_open_price = $cart_item_data['alg_open_price'];
		}
		return $cart_item_data;
	}

	/**
	 * add_open_price_input_field_to_frontend.
	 *
	 * @version 1.1.5
	 * @since   1.0.0
	 * @todo    set min and max as in product's settings
	 * @todo    step on per product basis
	 */
	function add_open_price_input_field_to_frontend() {
		$_product = wc_get_product();
		if ( $this->is_open_price_product( $_product ) ) {
			// Title
			$title = get_option( 'alg_wc_product_open_pricing_label_frontend', __( 'Name Your Price', 'product-open-pricing-for-woocommerce' ) );
			// The field - Value
			$value = ( isset( $_POST['alg_open_price'] ) ) ? $_POST['alg_open_price'] : get_post_meta( $this->get_product_or_variation_parent_id( $_product ), '_' . 'alg_wc_product_open_pricing_default_price', true );

			// Min and Max
			$min = get_post_meta( $this->get_product_or_variation_parent_id( $_product ), '_' . 'alg_wc_product_open_pricing_min_price', true );
			$max = get_post_meta( $this->get_product_or_variation_parent_id( $_product ), '_' . 'alg_wc_product_open_pricing_max_price', true );

			// The field - Custom attributes
			$custom_attributes = '';
			$default_price_step = 1 / pow( 10, absint( get_option( 'woocommerce_price_num_decimals', 2 ) ) );
			$custom_attributes .= 'step="' . get_option( 'alg_wc_product_open_pricing_price_step', $default_price_step ) . '" ';
			$custom_attributes .= 'min="'.esc_attr($min).'" ';
			$custom_attributes .= 'max="'.esc_attr($max).'" ';
			// The field - Final assembly
			$input_field = '<input '
				. 'type="number" '
				. 'class="text" '
				. 'style="width:75px;text-align:center;" '
				. 'name="alg_open_price" '
				. 'id="alg_open_price" '
				. 'value="' . $value . '" '
				. $custom_attributes . '>';
			// Currency symbol
			$currency_symbol_template = '<span class="popfwc-currency-symbol">'.get_woocommerce_currency_symbol().'</span>';
			$min_template = '<span class="popfwc-min">'.$min.'</span>';
			$max_template = '<span class="popfwc-max">'.$max.'</span>';

			// Output
			echo str_replace(
				array( '%frontend_label%', '%open_price_input%', '%currency_symbol%', '%minimum_price%', '%max_price%' ),
				array( $title, $input_field, $currency_symbol_template, $min_template, $max_template ),
				get_option( 'alg_wc_product_open_pricing_frontend_template', '<label for="alg_open_price">%frontend_label%</label> %open_price_input% %currency_symbol%' )
			);

			// Disable step, if necessary
			$step_enabled = get_option( 'alg_wc_product_open_pricing_enable_step', 'yes' );
			if ( $step_enabled !== 'yes' ) {
				?>
				<style>
					#alg_open_price[type='number'] {
						-moz-appearance:textfield;
					}
					#alg_open_price::-webkit-outer-spin-button,
					#alg_open_price::-webkit-inner-spin-button {
						-webkit-appearance: none;
						margin: 0;
					}
				</style>
				<?php
			}
		}
	}

}

endif;

return new Alg_WC_Product_Open_Pricing_Core();
