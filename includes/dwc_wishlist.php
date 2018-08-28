<?php
/**
 * Wishlist Functions
 */

/*================================================================================
 || Wishlist Without Login ajax function
==================================================================================*/

add_action( 'wp_ajax_nopriv_d2_add_n_remove_wishlist', 'd2_add_n_remove_wishlist_IP' );

function d2_add_n_remove_wishlist_IP(){

    /* Add To Wishlist */
    if (isset($_POST['prod_id'])) {
        $prod_id = $_POST['prod_id'];
        if (!isset($_COOKIE['d2_wishlist']['product_id_'.$prod_id])) {
            
            $domain = ($_SERVER['HTTP_HOST'] != 'localhost') ? $_SERVER['HTTP_HOST'] : false;
            setcookie('d2_wishlist[product_id_'.$prod_id.']', $prod_id, strtotime( '+10 days' ), '/', $domain, false);

            if(count($_COOKIE) > 0){
                echo json_encode(array('type' => 'success' , 'msg' => 'added_to_wishlist' ));
            }else{
                echo json_encode(array('type' => 'error' , 'msg' => 'Cookie Disable Or not Support Your Browser' ));
            }
        }else{
            echo json_encode(array('type' => 'already' , 'msg' => 'Already Add on Wishlist' ));
        }
    }

    /* Remove From wishlist */
    if (isset($_POST['del_prod_id'])) {
        $prod_id = $_POST['del_prod_id'];

        $domain = ($_SERVER['HTTP_HOST'] != 'localhost') ? $_SERVER['HTTP_HOST'] : false;

        setcookie('d2_wishlist[product_id_'.$prod_id.']', null, time()-1000, '/', $domain , 1);

        echo json_encode(array('type' => 'success' , 'msg' => 'remove_from_wishlist' ));
    }
    die();
}

/*================================================================================
 || Wishlist When User Logged In Ajax function
==================================================================================*/

add_action('wp_ajax_d2_add_n_remove_wishlist', 'd2_add_n_remove_wishlist_userID');

function d2_add_n_remove_wishlist_userID(){

    global $wpdb;
    /* Add To Wishlist */
    if (isset($_POST['prod_id'])) {

        $prod_id = $_POST['prod_id'];
        $user_id = $_POST['user_id'];
        $current_time = current_time( 'mysql' );

        $chk_exist_prod = $wpdb->get_row( "SELECT * FROM ".$wpdb->prefix."custom_whishlist WHERE user_id =".$user_id." AND product_id =".$prod_id, ARRAY_A );

        if (!$chk_exist_prod) {
            $results = $wpdb->insert( $wpdb->prefix.'custom_whishlist',
                array(
                    'user_id'   => $user_id,
                    'product_id'=> $prod_id,
                    'date'      => $current_time
                )
            );
            // On success
            if ( ! is_wp_error( $results ) ) {
                echo json_encode(array('type' => 'success' , 'msg' => 'added_to_wishlist' ));
            }
        }else{
            echo json_encode(array('type' => 'already' , 'msg' => 'Already Add on Wishlist' ));
        }
    }

    /* Remove From Wishlist */

    if (isset($_POST['del_prod_id'])) {

        $del_prod_id = $_POST['del_prod_id'];

        $del_user_id = $_POST['del_user_id'];

        $results = $wpdb->delete( 'wp_custom_whishlist', array( 'user_id' => $del_user_id, 'product_id' => $del_prod_id) );

        // On success
        if ( ! is_wp_error( $results ) ) {
            echo json_encode(array('type' => 'success' , 'msg' => 'remove_from_wishlist' ));
        }
    }
    die();
}

/*======================================================================
 || Display Wishlist Table Shortcode
=========================================================================*/

function d2_wishlist_table( $atts, $content = null ) {
    global $wpdb;
    $atts = shortcode_atts( array(
        'per_page' => 5,
        'class' => 'wishlist_table'
    ), $atts );
    $wishlist_product_id = $user_prod_wishlist = '';
    if (is_user_logged_in()) {
        $user_id = get_current_user_id();
        $user_prod_wishlist = $wpdb->get_results( "SELECT * FROM ".$wpdb->prefix."custom_whishlist WHERE user_id = ".$user_id." LIMIT " . $atts['per_page'] , ARRAY_A);
        foreach ($user_prod_wishlist as $key => $value) {
            $wishlist_product_id[].= $value['product_id'];
        }
    } elseif (isset($_COOKIE['d2_wishlist'])) {
        $user_prod_wishlist = $_COOKIE['d2_wishlist'];
        foreach ($user_prod_wishlist as $name => $ipvalue) {
            $ipvalue = htmlspecialchars($ipvalue);
            $wishlist_product_id[].= $ipvalue;
        }
    }
?>

<div class="d2d_wishlist_page">
    <?php if (isset($_GET['add-to-cart'])) {
        $Product_id = $_GET['add-to-cart'];
        $get_pro_details = wc_get_product($Product_id);
        $pro_title = $get_pro_details->post->post_title;
    ?>
    <div class="woocommerce-message">
        <a href="<?php echo site_url('/cart/'); ?>" class="button wc-forward">View Cart</a>“<?php echo $pro_title; ?>” has been added to your cart.
    </div>
    <?php } ?>
    <div id="display_wishlist_data" class="<?php echo $atts['class']; ?> clearfix">

        <?php if ($user_prod_wishlist) :
            foreach ($wishlist_product_id as $key => $wishlist_product_id) {
                $product_details = wc_get_product($wishlist_product_id);
                $prod_post_id =  $product_details->post->ID;
                $product_permalink = $product_details->get_permalink();
                $image = wp_get_attachment_image_src( get_post_thumbnail_id( $product_details->id ), 'single-post-thumbnail' );
                $imagepath = $image[0];
                $stock =  $product_details->get_stock_quantity();
        ?>
                <div id="wishlist_product_id_<?php echo $wishlist_product_id; ?>" class="d2d_wishlist_loop col-md-12 col-sm-6 col-xs-12">
                    <div class="product-remove"><a title="Remove this product" href="javascript:void(0)" class="remove d2_remove_from_wishlist" data-product_id="<?php echo $wishlist_product_id; ?>"><i class="fa fa-times" aria-hidden="true"></i></a></div>
                    <div class="col-md-2"><a href="javascript:void(0);"><img alt="product-image" src="<?php echo $imagepath; ?>" class="img-responsive img-hover"></a></div>
                    <div class="col-md-8 d2d_content-wishlist">
                        <div class="heading"><a href="<?php echo $product_permalink; ?>"><?php echo $product_details->post->post_title; ?></a></div>
                        <hr class="following">
                        <p><?php echo wp_trim_words( $product_details->post->post_content, 30, '...' ); ?></p>
                    </div>

                    <div class="col-md-2 d2d_cart-wishlist">

                        <div class="price"><i aria-hidden="true" class=""></i><?php echo $product_details->get_price_html(); ?></div>
                        <?php if($stock=='0') { ?>
                            <p class="product woocommerce add_to_cart_inline d2d-out-stock" style="border:4px solid #ccc; padding: 12px;">
                                <a rel="nofollow" href="<?php echo get_permalink($wishlist_product_id); ?>" class="button">View Product</a>
                            </p> 
                        <?php }elseif( $product_details->is_type( 'variable' ) ){ ?>
                            <p class="product woocommerce add_to_cart_inline d2d-out-stock" style="border:4px solid #ccc; padding: 12px;">
                                <a rel="nofollow" href="<?php echo get_permalink($wishlist_product_id); ?>" class="button">Select Options</a>
                            </p>
                        <?php }else{
                            echo do_shortcode('[add_to_cart id='.$product_details->id.' show_price="false"]');
                        }
                        ?>
                      <div class="stock"><?php if ($product_details->is_in_stock()) { echo '<i aria-hidden="true" class="fa fa-check"></i> STOCK'; }else{ echo '<i aria-hidden="true" class="fa fa-times"></i> SOLD OUT'; } ?></div>
                    </div>
                </div>
            <?php }
        else : ?>
            <tr>
                <td class="wishlist-empty" colspan="6">No products were added to the wishlist</td>
            </tr>
        <?php endif; ?>
    </div>
</div>
<?php }

// use shortcode for this [d2_display_wishlist per_page = 5 class =wishlist]
add_shortcode( 'dwc_wishlist', 'd2_wishlist_table' );

/*===============================================================
 || Add To Wishlist Button
=================================================================*/

function d2_add_to_wishlist_button_func( $atts, $content = null ) { 

    global $wpdb;

    $atts = shortcode_atts( array(
        'button_text' => 'Add&nbspTo&nbspWishlist',
        'class' => '',
    ), $atts );

    $product_id = get_the_id();

    $wishlist_page = (dwc_get_theme_options('dwc_shop_wishlist_browse_page')) ? (esc_url( get_page_link(dwc_get_theme_options('dwc_shop_wishlist_browse_page')))) : (site_url('/wishlist/'));

    if (is_user_logged_in()) {

        $user_id = get_current_user_id();

        $mylink = $wpdb->get_row( "SELECT * FROM ".$wpdb->prefix."custom_whishlist WHERE user_id = ".$user_id." AND product_id = ".$product_id );

        if (!empty($mylink)) {
            echo '<div class="d2d_wihlst_added_wrap ">
                    <div class="tool-tip"><a class="browse_wishlist" href="'.$wishlist_page.'">Browse Wishlist</a></div>
                </div>';
        }else{
            echo '<div id="d2d-wihlst-wrap-id" class="d2d_wihlst_wrap">
                <div class="wishlist_button">
                    <a href="javascript:void(0)" data-product_id="'.$product_id.'" class="d2_add_to_wishlist button">'.$atts['button_text'].'</a>
                    <div class="browse_wishlist" style="display:none;"><a class="button" href="'.$wishlist_page.'">Browse Wishlist</a></div>
                </div>
            </div>';
        }
    } else{
        if (isset($_COOKIE['d2_wishlist']['product_id_'.$product_id])) {
            echo '<div class="d2d_wihlst_wrap">
                <div class="tool-tip"><a class="browse_wishlist" href="'.$wishlist_page.'">Browse Wishlist</a></div>
            </div>';
        }else{
            echo '<div id="d2d-wihlst-wrap-id" class="d2d_wihlst_wrap">
                <div class="button">
                    <a href="javascript:void(0)" data-product_id="'.$product_id.'" class="d2_add_to_wishlist">'.$atts['button_text'].'</a>
                    <div class="browse_wishlist" style="display:none;"><a href="'.$wishlist_page.'">Browse Wishlist</a></div>
                </div>
            </div>';
        }
    }
}

// Use shortcode for this [d2_add_to_wishlist_button button_text=Add to Wishlist]
add_shortcode( 'dwc_add_to_wishlist_button', 'd2_add_to_wishlist_button_func' );