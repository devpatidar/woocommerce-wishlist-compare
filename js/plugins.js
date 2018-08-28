/* Plugin Main Jquery */

jQuery(document).ready(function($){

	/*
     * Wishlist Jquery Ajax Process
     */
    // Add To Wishlist Process
    jQuery('.d2_add_to_wishlist').one( "click", function(){
        var this_var = jQuery(this);
        this_var.parent().parent().find('.button a,.tool-tip a').addClass('d2d_product_loader');
        var prod_id = jQuery(this).attr('data-product_id');
        jQuery.ajax({
            type : 'POST',
            dataType: 'json',
            url : dwc_jquery_var.ajax_url,
            data : {
                action : 'd2_add_n_remove_wishlist',
                prod_id : prod_id,
                user_id : dwc_jquery_var.current_user_id,
            },
            success: function(response) {
                if(response.type =='success'){
                    this_var.hide();
                    this_var.parent('.wishlist_button').find('.browse_wishlist').fadeIn();
                }
                if (response.type == 'error') {
                    jQuery('.d2_add_to_wishlist_'+prod_id ).after('<p id="login_error"> ERROR : Cookies are blocked or not supported by your browser. You must <a href="https://codex.wordpress.org/Cookies">enable cookies</a> to use WordPress.<br></p>');
                }
            }
        })
    });
    // Remove From Wishlist Process
    jQuery('.d2_remove_from_wishlist').one( "click", function(){
        jQuery(this).parents("div.d2d_wishlist_loop").addClass("d2d_wishlist_cross");
        var del_prod_id = jQuery(this).attr('data-product_id');
        jQuery.ajax({
            type : 'POST',
            dataType: 'json',
            url : dwc_jquery_var.ajax_url,
            data : {
                action : 'd2_add_n_remove_wishlist',
                del_prod_id : del_prod_id,
                del_user_id : dwc_jquery_var.current_user_id,
            },
            success: function(response) {
                if(response.type =='success'){
                    jQuery('#wishlist_product_id_'+del_prod_id ).fadeOut( "slow", function() {
                    jQuery(this).remove();  
                    });
                    jQuery("div.d2d_wishlist_loop").removeClass("d2d_wishlist_cross");
                    var count = jQuery("#display_wishlist_data .d2d_wishlist_loop").length;

                    if ( count < 2 ) {
                        location.reload();
                    }
                }
            }
        })
    });

    /*
     * Compare Ajax Process
     */
    // Add To Compare Ajax Process
    jQuery('.d2_add_to_compare').one( "click", function(){
        var this_var = jQuery(this);
        
        var compare_prod_id = jQuery(this).attr('data-compare_id');
        jQuery.ajax({
            type : 'POST',
            dataType: 'json',
            url : dwc_jquery_var.ajax_url,
            data : {
                action : 'd2_add_n_remove_compare',
                compare_prod_id : compare_prod_id,
            },
            success: function(response) {
                //this_var.removeClass('d2d_product_loader');
                if(response.type =='success'){
                    this_var.hide();
                    this_var.parent('.compare_button').find('.browse_compare').fadeIn();
                }
                if (response.type == 'error') {
                    jQuery('.d2_add_to_compare_'+compare_prod_id ).after('<p id="login_error"> ERROR : Cookies are blocked or not supported by your browser. You must <a href="https://codex.wordpress.org/Cookies">enable cookies</a> to use WordPress.<br></p>');
                }
                this_var.parent().parent().find('.button a,.tool-tip a').removeClass('d2d_product_loader');
            }
        })
    });
    // Remove From Compare List
    jQuery('.d2_remove_from_compare').one( "click", function(){
        jQuery(this).parents(".d2d_repeat_compare").addClass("d2d_compare_cross");
        var del_compare_prod_id = jQuery(this).attr('data-compare_id');
        jQuery.ajax({
            type : 'POST',
            dataType: 'json',
            url : dwc_jquery_var.ajax_url,
            data : {
                action : 'd2_add_n_remove_compare',
                del_compare_prod_id : del_compare_prod_id,
            },
            success: function(response) {
                if(response.type =='success'){
                    jQuery(".d2d_repeat_compare").removeClass("d2d_compare_cross");
                    jQuery('#cm_product_id_'+del_compare_prod_id ).fadeOut( "slow", function() {
                        jQuery(this).remove();  
                    });

                    var count = jQuery("#d2d_compare_list_id .d2d_repeat_compare").length;

                    if ( count < 2 ) {
                        location.reload();
                    }
                }
            }
        })
    });


});