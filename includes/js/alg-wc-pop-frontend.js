/**
 * alg-wc-pop-frontend.js.
 *
 * @version 1.3.1
 * @since   1.3.1
 */

function alg_wc_pop_force_decimals(e){
	e.val(parseFloat(e.val()).toFixed(alg_wc_pop_data_object.force_decimal_width));
}

jQuery(document).ready(function(){
	jQuery(".alg_open_price").each(function(){
		alg_wc_pop_force_decimals(jQuery(this));
		jQuery(this).on('propertychange change click keyup input paste',function(){alg_wc_pop_force_decimals(jQuery(this))});
	});
});
