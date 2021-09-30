/**
 * alg-wc-pop-frontend.js.
 *
 * @version 1.7.0
 * @since   1.3.1
 */

(function($){
	
	function alg_wc_pop_force_decimals(e){
		var value = parseFloat( e.val() );
		if ( isNaN( value ) ) {
			value = 0;
		}
		e.val( value.toFixed( alg_wc_pop_data_object.force_decimal_width ) );
	}
	
	function wpw_pop_product_addons_compatibility( amount ){
		if ( $( '#product-addons-total' ).length ) {
			$( '#product-addons-total' ).data( 'price', amount );
			$( '.cart' ).trigger( 'woocommerce-product-addons-update' );
		}
	}
	
	$( document ).ready( function(){
		if (
			typeof alg_wc_pop_data_object.force_decimals !== "undefined"
			&& alg_wc_pop_data_object.force_decimals
		) {
			$( '.alg_open_price' ).each( function(){
				$( this ).on( 'change', function(){
					alg_wc_pop_force_decimals( $( this ) );
				});
			});
		}
		$( '.alg_open_price' ).first().on( 'change', function(){
			wpw_pop_product_addons_compatibility( $( this ).val() );
		});
		$( '.alg_open_price' ).first().trigger( 'change' );
	});
	
})( jQuery );
