jQuery(function() {
    'use strict';

    // Hide add-to-cart button for variable product when there is unselected option.

	var form = jQuery('form.cart');
	form.on('check_variations', function(event, exclude) {
        if (exclude) return;
        var all_set = true;
        form.find('.variations select').each(function() {
            if (jQuery(this).val().length === 0)
                all_set = false;
        });
        if (!all_set) {
            jQuery('.wc-add-to-cart').slideUp(200);
        }
	});
});