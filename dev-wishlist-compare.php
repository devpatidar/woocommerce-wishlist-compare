<?php
/**
 * Plugin Name:       Dev Product Wishlist Compare
 * Plugin URI:        https://devpatidar.com/devwc
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            Dev Patidar
 * Author URI:        https://devpatidar.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       dev_wc
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'DWC_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'DWC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

include( DWC_PLUGIN_PATH . 'includes/dwc_plugin_functions.php');
include( DWC_PLUGIN_PATH . 'includes/dwc_wishlist.php');
include( DWC_PLUGIN_PATH . 'includes/dwc_compare.php');
include( DWC_PLUGIN_PATH . 'includes/admin_settings.php');


function requires_wordpress_version() {
	global $wp_version;
	$plugin = plugin_basename( __FILE__ );
	$plugin_data = get_plugin_data( __FILE__, false );

	if ( version_compare($wp_version, "3.3", "<" ) ) {
		if( is_plugin_active($plugin) ) {
			deactivate_plugins( $plugin );
			wp_die( "'".$plugin_data['Name']."' requires WordPress 3.3 or higher, and has been deactivated! Please upgrade WordPress and try again.<br /><br />Back to <a href='".admin_url()."'>WordPress admin</a>." );
		}
	}
}
add_action( 'admin_init', 'requires_wordpress_version' );

/*
 * Create table for wishlist when plugin are active
 */

function dwc_activation_functions() {
	// Set All Plugin Options when plugin activate
	$dwc_plugin_options_data = array(
		'dwc_shop_wishlist_btn' 		=>	'on',
		'dwc_shop_wishlist_btn_text' 	=>	'Wishlist',
		'dwc_shop_compare_btn' 			=>	'on',
		'dwc_shop_compare_btn_text' 	=>	'Compare',
		'dwc_single_wishlist_btn' 		=>	'on',
		'dwc_single_wishlist_btn_text' 	=>	'Wishlist',
		'dwc_single_compare_btn' 		=>	'on',
		'dwc_single_compare_btn_text' 	=>	'Compare',
	);
	// Update Theme Options
	update_option( 'dwc_plugin_options', $dwc_plugin_options_data, '', 'yes' );

	// Auto create table when plugin activate
	global $wpdb;
	global $jal_db_version;

	$table_name = $wpdb->prefix . 'liveshoutbox';
	
	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		name tinytext NOT NULL,
		text text NOT NULL,
		url varchar(55) DEFAULT '' NOT NULL,
		UNIQUE KEY id (id)
	) $charset_collate;";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );
}

register_activation_hook( __FILE__, 'dwc_activation_functions' );



/*================================================================================
 || Wishlist Without Login
==================================================================================*/

add_action('init', 'd2_declear_start_session', 1);

function d2_declear_start_session() {

	if(!isset($_SESSION)) {
		session_start();
	}
}

/*============================================================================
 || Add wishlist and button on Single Page
=============================================================================*/

function d2_action_woocommerce_single_product_summary() { 
    echo '<div class="d2_wc_button">';
    	if (dwc_get_theme_options('dwc_single_wishlist_btn')) {
    		do_shortcode('[dwc_add_to_wishlist_button button_text='.dwc_get_theme_options('dwc_shop_wishlist_btn_text').']');
    	}
    	if (dwc_get_theme_options('dwc_single_compare_btn')) {
    		do_shortcode('[dwc_add_to_compare_button btn_title="'.dwc_get_theme_options('dwc_shop_compare_btn_text').'"]');
    	}
    echo '</div>';
}

add_action( 'woocommerce_single_product_summary', 'd2_action_woocommerce_single_product_summary', 40 );

/*============================================================================
 || Add wishlist button on shop page
=============================================================================*/
function d2_action_woocommerce_after_shop_loop_item() {

    echo '<div class="d2_wc_button">';
    	if (dwc_get_theme_options('dwc_shop_wishlist_btn')) {
    		do_shortcode('[dwc_add_to_wishlist_button button_text='.dwc_get_theme_options('dwc_shop_wishlist_btn_text').']');
    	}
    	if (dwc_get_theme_options('dwc_shop_compare_btn')) {
    		do_shortcode('[dwc_add_to_compare_button]');
    	}
    	
    echo '</div>';
}
add_action( 'woocommerce_after_shop_loop_item', 'd2_action_woocommerce_after_shop_loop_item', 20 );

/**
 * Enqueue script for plugin
 */

function d2_add_plugin_scripts() {
	wp_enqueue_style( 'd2-style-css', plugins_url('css/style.css', __FILE__));
	wp_enqueue_script( 'd2-plugin-js', plugins_url('js/plugins.js', __FILE__) ,array(), '1.0.0', true);
	wp_localize_script( 'd2-plugin-js', 'dwc_jquery_var',array( 
		'ajax_url' 				=> admin_url( 'admin-ajax.php' ),
		'current_user_id'		=> get_current_user_id(),
		'site_url' 				=> site_url(),
		//'opt_blog_num_columns'	=> themedirect_option('opt_blog_num_columns')
		) );
	wp_enqueue_script( 'd2-plugin-js' );
}
add_action( 'wp_enqueue_scripts', 'd2_add_plugin_scripts' );

/**
 * Enqueue script for admin section only
 */

function d2_add_admin_scripts() {
	wp_enqueue_style( 'admin-css', plugins_url('css/admin-style.css', __FILE__));
	//wp_enqueue_script( 'd2-plugin-js', plugins_url('js/plugins.js', __FILE__) ,array(), '1.0.0', true);
}
add_action( 'admin_enqueue_scripts', 'd2_add_admin_scripts' );