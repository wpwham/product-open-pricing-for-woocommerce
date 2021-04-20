/**
 * alg-wc-pop-frontend.js.
 *
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
	$( document ).ready( function(){
		$( '.alg_open_price' ).each( function(){
			alg_wc_pop_force_decimals( $( this ) );
			$( this ).on( 'change', function(){
				alg_wc_pop_force_decimals( $( this ) );
			});
		});
	});
	
})( jQuery );
