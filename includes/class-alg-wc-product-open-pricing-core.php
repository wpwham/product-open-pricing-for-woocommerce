<?php
/**
 * Product Open Pricing for WooCommerce - Core Class
 *
 * @version 1.7.1
 * @since   1.0.0
 * @author  Algoritmika Ltd.
 * @author  WP Wham
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Alg_WC_Product_Open_Pricing_Core' ) ) :

class Alg_WC_Product_Open_Pricing_Core {
	
	public $is_wc_version_below_3 = null;
	
	private $adjusted_items_cache = array();

	/**
	 * Constructor.
	 *
	 * @version 1.7.0
	 * @since   1.0.0
	 * @todo    [dev] (maybe) add AJAX/instant updating (#11777)
	 * @todo    [feature] open pricing **per variation** (#11726)
	 */
	function __construct() {
		if ( 'yes' === get_option( 'alg_wc_product_open_pricing_enabled', 'yes' ) ) {
			$this->is_wc_version_below_3 = version_compare( get_option( 'woocommerce_version', null ), '3.0.0', '<' );

			// Price
			$get_price_filter = ( $this->is_wc_version_below_3 ? 'woocommerce_get_price' : 'woocommerce_product_get_price' );
			add_filter( $get_price_filter,                        array( $this, 'get_open_price' ), PHP_INT_MAX, 2 );
			add_filter( 'woocommerce_get_price_html',             array( $this, 'hide_original_price' ), PHP_INT_MAX, 2 );
			add_filter( 'woocommerce_get_variation_price_html',   array( $this, 'hide_original_price' ), PHP_INT_MAX, 2 );

			// Qty
			if ( 'yes' === get_option( 'alg_wc_product_open_pricing_disable_qty', 'yes' ) ) {
				add_filter( 'woocommerce_is_sold_individually',   array( $this, 'hide_quantity_input_field' ), PHP_INT_MAX, 2 );
			}

			// Is purchasable
			add_filter( 'woocommerce_is_purchasable',             array( $this, 'is_purchasable' ), PHP_INT_MAX, 2 );

			// Add to cart
			add_filter( 'woocommerce_product_supports',           array( $this, 'disable_add_to_cart_ajax' ), PHP_INT_MAX, 3 );
			add_filter( 'woocommerce_product_add_to_cart_url',    array( $this, 'add_to_cart_url' ), PHP_INT_MAX, 2 );
			add_filter( 'woocommerce_product_add_to_cart_text',   array( $this, 'add_to_cart_text' ), PHP_INT_MAX, 2 );
			add_filter( 'woocommerce_add_to_cart_validation',     array( $this, 'validate_open_price_on_add_to_cart' ), PHP_INT_MAX, 2 );
			add_filter( 'woocommerce_add_cart_item_data',         array( $this, 'add_open_price_to_cart_item_data' ), PHP_INT_MAX, 3 );
			add_filter( 'woocommerce_add_cart_item',              array( $this, 'add_open_price_to_cart_item' ), PHP_INT_MAX, 2 );

			// Other hooks
			add_action( 'woocommerce_before_calculate_totals',    array( $this, 'override_product_price' ), 10, 1 );
			add_action( 'aopwc_value',                            array( $this, 'convert_price_currency_switcher' ), 10, 2 );
			add_filter( 'woocommerce_loop_add_to_cart_link',      array( $this, 'add_attribute_on_add_to_cart_button' ), 10, 2 );
			add_action( 'wp_footer',                              array( $this, 'sync_add_to_cart_button_attribute' ) );

			// Frontend filter on Single Product Page
			$placeholder_filter = get_option( 'alg_wc_product_open_pricing_field_position', 'woocommerce_before_add_to_cart_button' );
			$placeholder_filter = sanitize_text_field( apply_filters( 'aopwc_frontend_input_filter', $placeholder_filter ) );
			if ( ! empty( $placeholder_filter ) ) {
				$placeholder_filter_priority = get_option( 'alg_wc_product_open_pricing_field_position_priority', 9999 );
				$placeholder_filter_priority = intval( apply_filters( 'aopwc_frontend_input_filter_priority', $placeholder_filter_priority ) );
				add_action( $placeholder_filter,                  array( $this, 'add_open_price_input_field_to_frontend' ), $placeholder_filter_priority );
			}

			// Frontend filter on Loop
			if ( 'yes' === get_option( 'alg_wc_product_open_pricing_field_on_loop', 'no' ) ) {
				$placeholder_filter_loop = get_option( 'alg_wc_product_open_pricing_field_loop_position', 'woocommerce_after_shop_loop_item' );
				$placeholder_filter_loop = sanitize_text_field( apply_filters( 'aopwc_frontend_input_filter_loop', $placeholder_filter_loop ) );
				if ( ! empty( $placeholder_filter_loop ) ) {
					$placeholder_filter_loop_priority = get_option( 'alg_wc_product_open_pricing_field_loop_position_priority', 9 );
					$placeholder_filter_loop_priority = intval( apply_filters( 'aopwc_frontend_input_filter_loop_priority', $placeholder_filter_loop_priority ) );
					add_action( $placeholder_filter_loop,         array( $this, 'add_open_price_input_field_to_frontend' ), $placeholder_filter_loop_priority );
				}
			}

			// Fix mini cart item price
			if ( 'yes' === get_option( 'alg_wc_product_open_pricing_fix_mini_cart', 'no' ) ) {
				add_action( 'wp_loaded',                          array( $this, 'fix_mini_cart' ), PHP_INT_MAX );
			}

			// Admin "Open Pricing" column
			if ( 'yes' === get_option( 'alg_wc_product_open_pricing_add_admin_column', 'no' ) ) {
				add_filter( 'manage_edit-product_columns',        array( $this, 'add_product_open_pricing_admin_column' ),    PHP_INT_MAX );
				add_action( 'manage_product_posts_custom_column', array( $this, 'render_product_open_pricing_admin_column' ), PHP_INT_MAX );
			}

			// Frontend script
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts_frontend' ) );
			
			// third party compatibilities
			add_filter( 'woocommerce_paypal_express_checkout_hide_button_on_product_page', array( $this, 'woocommerce_paypal_express_checkout_hide_button_on_product_page' ) );
			add_filter( 'wc_stripe_hide_payment_request_on_product_page', array( $this, 'woocommerce_stripe_hide_payment_request_on_product_page' ), 10, 2 );
			
		}
	}
	
	/**
	 * enqueue_scripts_frontend.
	 *
	 * @version 1.6.0
	 * @since   1.3.1
	 */
	function enqueue_scripts_frontend() {
		wp_enqueue_script(
			'alg-wc-pop-frontend',
			alg_wc_product_open_pricing()->plugin_url() . '/includes/js/alg-wc-pop-frontend.js',
			array( 'jquery' ),
			alg_wc_product_open_pricing()->version,
			true
		);
		wp_localize_script(
			'alg-wc-pop-frontend',
			'alg_wc_pop_data_object',
			array(
				'force_decimals' => get_option( 'alg_wc_product_open_pricing_force_decimal_width_enabled', 'no' ) === 'yes',
				'force_decimal_width' => get_option( 'alg_wc_product_open_pricing_force_decimal_width', get_option( 'woocommerce_price_num_decimals', 2 ) ),
			)
		);

	}

	/**
	 * add_product_open_pricing_admin_column.
	 *
	 * @version 1.3.0
	 * @since   1.3.0
	 * @todo    [dev] (maybe) add "Open Pricing Data" column (i.e. default, min and max prices)
	 */
	function add_product_open_pricing_admin_column( $columns ) {
		$columns['alg_wc_pop_is_open_pricing'] = __( 'Open Pricing', 'product-open-pricing-for-woocommerce' );
		return $columns;
	}

	/**
	 * render_product_open_pricing_admin_column.
	 *
	 * @version 1.3.0
	 * @since   1.3.0
	 */
	function render_product_open_pricing_admin_column( $column ) {
		$_product = wc_get_product( get_the_ID() );
		
		if ( ! is_a( $_product, 'WC_Product' ) ) {
			return;
		}
		
		if ( 'alg_wc_pop_is_open_pricing' == $column && $this->is_open_price_product( $_product ) ) {
			echo '<span style="font-weight:bold;color:green;">&check;</span>';
		}
	}

	/*
	 * is_frontend()
	 *
	 * @version 1.3.0
	 * @since   1.3.0
	 * @return  boolean
	 */
	function is_frontend() {
		if ( ! is_admin() ) {
			return true;
		} elseif ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return ( ! isset( $_REQUEST['action'] ) || ! is_string( $_REQUEST['action'] ) || ! in_array( $_REQUEST['action'], array(
					'woocommerce_load_variations',
				) ) );
		} else {
			return false;
		}
	}

	/**
	 * fix_mini_cart.
	 *
	 * @version 1.3.0
	 * @since   1.3.0
	 * @todo    [dev] this is only temporary solution! (#11860)
	 */
	function fix_mini_cart() {
		if ( $this->is_frontend() && function_exists( 'WC' ) && null !== WC() && isset( WC()->cart ) && is_object( WC()->cart ) && method_exists( WC()->cart, 'calculate_totals' ) ) {
			WC()->cart->calculate_totals();
		}
	}

	/**
	 * Syncs add to cart button attribute with open price value.
	 *
	 * @version 1.1.9
	 * @since   1.1.9
	 */
	function sync_add_to_cart_button_attribute() {
		if (
			'yes' !== get_option( 'alg_wc_product_open_pricing_field_on_loop', 'no' ) ||
			is_product() ||
			is_checkout() ||
			is_cart() ||
			! is_woocommerce()
		) {
			return;
		}
		?>
		<script>
			var popfwc_sync = {
				init: function () {
					var open_prices = document.querySelectorAll('.alg_open_price');
					[].forEach.call(open_prices, function (el) {
						var product_id = el.getAttribute('data-product_id');
						var add_to_cart_btn = document.querySelector('.add_to_cart_button[data-product_id="' + product_id + '"]');
						var href = add_to_cart_btn.getAttribute("href");
						el.addEventListener('input', function (evt) {
							add_to_cart_btn.setAttribute('data-alg_open_price', this.value);
							if (href.indexOf('alg_open_price') !== -1) {
								var new_href = href.replace(/alg_open_price=\d*/i, "alg_open_price=" + this.value);
							} else {
								var new_href = href + '&alg_open_price=' + this.value
							}
							add_to_cart_btn.setAttribute('href', new_href);
						});
					});
				}
			};
			document.addEventListener("DOMContentLoaded", function () {
				popfwc_sync.init();
			});
		</script>
		<?php
	}

	/**
	 * Adds attribute data-alg_open_price on 'Add to Cart' button on loop pages.
	 *
	 * @version 1.6.0
	 * @since   1.1.9
	 * @param   $link
	 * @param   $product
	 * @return  string
	 */
	function add_attribute_on_add_to_cart_button( $link, $product ) {
	
		// if required extensions are missing, stop here
		if ( ! class_exists( 'DOMDocument') || ! function_exists( 'mb_convert_encoding' ) ) {
			return $link;
		}
		
		if (
			'yes' !== get_option( 'alg_wc_product_open_pricing_field_on_loop', 'no' ) ||
			! $this->is_open_price_product( $product )
		) {
			return $link;
		}
		
		// Ensure UTF-8 is respected by using 'mb_convert_encoding'
		$link = mb_convert_encoding( $link, 'HTML-ENTITIES', 'UTF-8' );

		$id = $this->get_product_or_variation_parent_id( $product );

		$product_link = add_query_arg( array(
			'add-to-cart' => $id
		), get_permalink( $id ) );

		$value = get_post_meta( $id, '_' . 'alg_wc_product_open_pricing_default_price', true );

		$dom = new DOMDocument();
		
		if ( version_compare( LIBXML_DOTTED_VERSION, '2.7.8', '>=' ) ) {
			@$dom->loadHTML( '<html><body>'.$link.'</body></html>', LIBXML_HTML_NODEFDTD );
		} else {
			@$dom->loadHTML( '<!DOCTYPE html><html><body>'.$link.'</body></html>' );
		}
		
		$x = new DOMXPath( $dom );

		foreach ( $x->query( "//a" ) as $node ) {
			$node->setAttribute( "data-alg_open_price", $value );
			$node->setAttribute( "href", $product_link);
			if ( ! empty( $value ) ) {
				$href = $node->getAttribute( "href" );
				if ( false === strpos( $href, 'alg_open_price' ) ) {
					$node->setAttribute( "href", $href . '&alg_open_price=' . $value );
				}
			}
		}
		$newHtml = $dom->saveHtml();
		
		if ( version_compare( LIBXML_DOTTED_VERSION, '2.7.8', '<' ) ) {
			$newHtml = str_replace( '<!DOCTYPE html>', '', $newHtml );
		}
		$newHtml = str_replace( '<html><body>', '', $newHtml );
		$newHtml = str_replace( '</body></html>', '', $newHtml );

		return $newHtml;
	}

	/**
	 * Converts min and max price if using currency switcher.
	 *
	 * @version 1.4.6
	 * @since   1.1.8
	 * @param   $value
	 * @param   $value_type
	 * @return  bool|float|int|mixed|string
	 */
	function convert_price_currency_switcher( $value, $value_type ) {
		if (
			is_admin() ||
			! function_exists( 'alg_wc_currency_switcher_plugin' ) ||
			( $value_type != 'min' && $value_type != 'max' && $value_type != 'value' ) ||
			$value === ''
		) {
			return $value;
		}

		$current_currency_code = alg_get_current_currency_code();
		$default_currency      = get_option( 'woocommerce_currency' );

		$value = alg_convert_price( array(
			'price'         => $value,
			'currency_from' => $default_currency,
			'currency'      => $current_currency_code,
			'format_price'  => 'no'
		) );
		$value = alg_wc_cs_round_and_pretty( $value, $current_currency_code );

		return $value;
	}
	
	/**
	 * Overrides product price.
	 *
	 * @version 1.6.0
	 * @since   1.1.7
	 * @param   $cart_obj
	 */
	function override_product_price( $cart_obj ) {
		
		if ( is_admin() ) {
			return;
		}
		
		$cart = $cart_obj->get_cart();
		
		foreach ( $cart as $item ) {
			
			if ( ! isset( $item['alg_open_price'] ) ) {
				continue;
			}
			
			if ( isset( $this->adjusted_items_cache[ $item['key'] ] ) ) {
				// prevents double adjustments
				continue;
			}
			
			$price = floatval( $item['data']->get_price() );
			
			// take WC product price out of the $price... we want only what's left,
			// i.e. anything that's been added on by other plugins.
			$db_price = floatval( get_post_meta( $item['data']->get_id(), '_price', true ) );
			if ( $db_price ) {
				$price = $price - $db_price;
			}
			if ( $price < 0 ) {
				$price = 0;
			}
			
			if ( function_exists( 'alg_wc_currency_switcher_plugin' ) ) {
				
				// compatibility for Currency Switcher for WooCommerce
				$current_currency_code = alg_get_current_currency_code();
				$default_currency      = get_option( 'woocommerce_currency' );
				
				if ( ! isset( $item['alg_open_price_curr'] ) ) {
					$item['alg_open_price_curr'] = $default_currency;
				}
				
				// Convert $price back to shop's default currency
				// (If there's anything in $price at this point its not from us... its from
				// some other plugin like Product Addons, therefore we need to deal in the
				// same currency.)
				$price = alg_convert_price( array(
					'price'         => $price,
					'currency_from' => $current_currency_code,
					'currency'      => $default_currency,
					'format_price'  => 'no'
				) );
				
				// Do we need to convert the open pricing product too?
				if (
					! empty( $item['alg_open_price_curr'] )
					&& $item['alg_open_price_curr'] !== $default_currency
				) {
					
					// If open pricing product was added in a currency other than the shop's default,
					// convert that value back to the shop default and combine with $price.
					$price = $price + alg_convert_price( array(
						'price'         => $item['alg_open_price'],
						'currency_from' => $item['alg_open_price_curr'],
						'currency'      => $default_currency,
						'format_price'  => 'no'
					) );
					// at this point, $price is the correct value in the shop's default currency,
					// and will be converted automatically to the user's currency by Currency Switcher
					// later on, if applicable.
					
				} else {
					
					// Open pricing product was added in shop's default currency. No conversion
					// needed.
					$price = $price + $item['alg_open_price'];
					
				}
				
			} else {
				
				// no special handling needed... straight addition
				$price = $price + $item['alg_open_price'];
				
			}
			
			$item['data']->set_price( $price );
			$this->adjusted_items_cache[ $item['key'] ] = true;
			
		}
	}
	
	/**
	 * get_product_or_variation_parent_id.
	 *
	 * @version 1.1.0
	 * @since   1.1.0
	 * @todo    [dev] (maybe) just product id (i.e. no parent for variation)
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
	 * @version 1.1.9
	 * @since   1.0.0
	 */
	function disable_add_to_cart_ajax( $supports, $feature, $_product ) {
		if (
			'yes' !== get_option( 'alg_wc_product_open_pricing_field_on_loop', 'no' ) &&
			$this->is_open_price_product( $_product ) &&
			'ajax_add_to_cart' === $feature
		) {
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
	 * @version 1.5.0
	 * @since   1.0.0
	 */
	function add_to_cart_text( $text, $_product ) {
		if ( $this->is_open_price_product( $_product ) ) {
			if ( get_option( 'wpw_pop_add_to_cart_text' ) > '' ) {
				// if "add to cart" text is specified in settings, always use it for open pricing products
				return get_option( 'wpw_pop_add_to_cart_text' );
			} elseif ( get_option( 'alg_wc_product_open_pricing_field_on_loop', 'no' ) !== 'yes' ) {
				// if display in loop is NOT enabled, we can't add to cart until the price is determined.
				// therefore we use the 'read more' message and direct to the product page.
				// ( see add_to_cart_url() )
				return __( 'Read more', 'woocommerce' );
			}
		}
		return $text;
	}

	/**
	 * add_to_cart_url.
	 *
	 * @version 1.1.9
	 * @since   1.0.0
	 */
	function add_to_cart_url( $url, $_product ) {
		if ( 'yes' !== get_option( 'alg_wc_product_open_pricing_field_on_loop', 'no' ) ) {
			return ( $this->is_open_price_product( $_product ) ) ? get_permalink( $this->get_product_or_variation_parent_id( $_product ) ) : $url;
		} else {
			return $url;
		}
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
	 * @version 1.2.2
	 * @since   1.0.0
	 */
	function hide_original_price( $price, $_product ) {
		$hide_single = filter_var( get_option( 'alg_wc_product_open_pricing_hide_price', 'yes' ), FILTER_VALIDATE_BOOLEAN );
		$hide_loop   = filter_var( get_option( 'alg_wc_product_open_pricing_loop_hide_price', 'yes' ), FILTER_VALIDATE_BOOLEAN );
		if (
			! $this->is_open_price_product( $_product ) ||
			( is_product() && ! $hide_single ) ||
			( ! is_product() && ! $hide_loop )
		) {
			return $price;
		}
		$price = '';

		return $price;
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
	 * @version 1.3.0
	 * @since   1.0.0
	 */
	function validate_open_price_on_add_to_cart( $passed, $product_id ) {
		$_product = wc_get_product( $product_id );
		
		if ( ! is_a( $_product, 'WC_Product' ) ) {
			return $passed;
		}
		
		if ( $this->is_open_price_product( $_product ) ) {
			$min_price = get_post_meta( $product_id, '_' . 'alg_wc_product_open_pricing_min_price', true );
			$max_price = get_post_meta( $product_id, '_' . 'alg_wc_product_open_pricing_max_price', true );

			$min_price = apply_filters( 'aopwc_value', $min_price, 'min' );
			$max_price = apply_filters( 'aopwc_value', $max_price, 'max' );

			if ( $min_price >= 0 ) {
				if ( ! isset( $_REQUEST['alg_open_price'] ) || '' === $_REQUEST['alg_open_price'] ) {
					wc_add_notice( get_option( 'alg_wc_product_open_pricing_messages_required',
						__( 'Price is required!', 'product-open-pricing-for-woocommerce' ) ), 'error' );
					return false;
				}
				if ( $_REQUEST['alg_open_price'] < $min_price ) {
					wc_add_notice( get_option( 'alg_wc_product_open_pricing_messages_too_small',
						__( 'Price is too low!', 'product-open-pricing-for-woocommerce' ) ), 'error' );
					return false;
				}
			}
			if ( $max_price > 0 ) {
				if ( isset( $_REQUEST['alg_open_price'] ) && $_REQUEST['alg_open_price'] > $max_price ) {
					wc_add_notice( get_option( 'alg_wc_product_open_pricing_messages_too_big',
						__( 'Price is too high!', 'product-open-pricing-for-woocommerce' ) ), 'error' );
					return false;
				}
			}
		}
		return $passed;
	}

	/**
	 * Sanitizes open price input value.
	 *
	 * @version 1.2.1
	 * @since   1.1.9
	 * @param   $open_price
	 * @return  mixed
	 */
	function sanitize_open_price( $open_price ) {
		return filter_var( sanitize_text_field( $open_price ), FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION );
	}

	/**
	 * add_open_price_to_cart_item_data.
	 *
	 * @version 1.6.0
	 * @since   1.0.0
	 */
	function add_open_price_to_cart_item_data( $cart_item_data, $product_id, $variation_id ) {
		
		$_product = wc_get_product( $product_id );
		
		// make sure $_product didn't return something unexpected
		if ( ! is_a( $_product, 'WC_Product' ) ) {
			return $cart_item_data;
		}
		
		// make sure we're acting only on open price products (in case of grouped products, for example)
		if ( ! $this->is_open_price_product( $_product ) ) {
			return $cart_item_data;
		}
		
		if ( isset( $_REQUEST['alg_open_price'] ) ) {
			$cart_item_data['alg_open_price'] = $this->sanitize_open_price( $_REQUEST['alg_open_price'] );
		}
		
		if ( isset( $_REQUEST['addon-wpw_pop_open_price'] ) ) {
			$cart_item_data['alg_open_price'] = $this->sanitize_open_price( $_REQUEST['addon-wpw_pop_open_price'] );
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
	 * @version 1.6.0
	 * @since   1.0.0
	 * @todo    [feature] (maybe) step on **per product** basis
	 */
	function add_open_price_input_field_to_frontend() {
		$_product = wc_get_product();
		
		if ( ! is_a( $_product, 'WC_Product' ) ) {
			return;
		}
		
		if ( $this->is_open_price_product( $_product ) ) {
			$product_id = $this->get_product_or_variation_parent_id( $_product );

			$is_loop = ( strpos( current_filter(), 'loop' ) !== false );

			// The field - Value
			if ( is_product() ) {
				$value = ( isset( $_REQUEST['alg_open_price'] ) ) ? $this->sanitize_open_price( $_REQUEST['alg_open_price'] ) :
					apply_filters( 'aopwc_value', get_post_meta( $product_id, '_' . 'alg_wc_product_open_pricing_default_price', true ), 'value' );
			} else {
				$value = apply_filters( 'aopwc_value', get_post_meta( $product_id, '_' . 'alg_wc_product_open_pricing_default_price', true ), 'value' );
			}

			$input_id = "alg_open_price_".$product_id;

			// Min and Max
			$min = get_post_meta( $product_id, '_' . 'alg_wc_product_open_pricing_min_price', true );
			$max = get_post_meta( $product_id, '_' . 'alg_wc_product_open_pricing_max_price', true );

			$min = apply_filters( 'aopwc_value', $min, 'min' );
			$max = apply_filters( 'aopwc_value', $max, 'max' );

			// The field - Custom attributes
			$custom_attributes = '';
			// $default_price_step = 1 / pow( 10, absint( get_option( 'woocommerce_price_num_decimals', 2 ) ) );
			$custom_attributes .= 'step="' . ( get_option( 'alg_wc_product_open_pricing_price_step' ) > '' ? get_option( 'alg_wc_product_open_pricing_price_step' ) : 'any' ). '" ';
			$custom_attributes = apply_filters( 'wpw_product_open_pricing_input_custom_attributes', $custom_attributes, $product_id );

			// The field - Final assembly
			$input_field = '<input '
				. 'type="number" '
				. 'data-product_id="'.$product_id.'" '
				. 'class="alg_open_price text" '
				. 'style="' . get_option( 'alg_wc_product_open_pricing_input_style', 'width:75px;text-align:center;' ) . '" '
				. 'name="alg_open_price" '
				. 'id="'.$input_id.'" '
				. 'value="' . $value . '" '
				. 'pattern="' . str_replace( '%backslash%', '\\', get_option( 'alg_wc_product_open_pricing_input_pattern', '' ) ) . '" '
				. $custom_attributes . '>';

			// Currency symbol
			$currency_symbol_template = '<span class="popfwc-currency-symbol">' . get_woocommerce_currency_symbol() . '</span>';
			$min_template = '<span class="popfwc-min">' . $min . '</span>';
			$max_template = '<span class="popfwc-max">' . $max . '</span>';

			// Template
			$template_single = get_option( 'alg_wc_product_open_pricing_frontend_template', '<label for="%input_id%">' .
				__( 'Name Your Price', 'product-open-pricing-for-woocommerce' ) . '</label> %open_price_input% %currency_symbol%' );
			$template        = $template_single;
			if ( $is_loop ) {
				$template_loop = get_option( 'alg_wc_product_open_pricing_frontend_loop_template', '<label for="%input_id%">' .
					__( 'Name Your Price', 'product-open-pricing-for-woocommerce' ) . '</label> %open_price_input% %currency_symbol%' );
				$template      = empty( $template_loop ) ? $template_single : $template_loop;
			}

			// Title is not being used anymore, but maintained in code for compatibility
			$title = get_option( 'alg_wc_product_open_pricing_label_frontend', __( 'Name Your Price', 'product-open-pricing-for-woocommerce' ) );

			// Output
			echo str_replace(
				array( '%input_id%', '%open_price_input%', '%currency_symbol%', '%minimum_price%', '%max_price%', '%frontend_label%' ),
				array( $input_id, $input_field, $currency_symbol_template, $min_template, $max_template, $title ),
				$template
			);

			// Disable step, if necessary
			$step_enabled = get_option( 'alg_wc_product_open_pricing_enable_step', 'yes' );
			if ( $step_enabled !== 'yes' ) {
				?>
				<style>
					.alg_open_price[type='number'] {
						-moz-appearance:textfield;
					}
					.alg_open_price::-webkit-outer-spin-button,
					.alg_open_price::-webkit-inner-spin-button {
						-webkit-appearance: none;
						margin: 0;
					}
				</style>
				<?php
			}
		}
	}
	
	/*
	 * woocommerce_paypal_express_checkout_hide_button_on_product_page.
	 *
	 * @since 1.7.0
	 */
	public function woocommerce_paypal_express_checkout_hide_button_on_product_page( $value ) {
		global $product;
		if ( $this->is_open_price_product( $product ) ) {
			return true;
		}
		return $value;
	}
	
	/*
	 * woocommerce_stripe_hide_payment_request_on_product_page.
	 *
	 * @since 1.7.0
	 */
	public function woocommerce_stripe_hide_payment_request_on_product_page( $value, $product ) {
		if ( is_a( $product, 'WC_Product' ) && $this->is_open_price_product( $product ) ) {
			return true;
		}
		return $value;
	}
	
}

endif;

return new Alg_WC_Product_Open_Pricing_Core();
