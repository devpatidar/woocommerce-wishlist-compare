<?php 
/*
 * Plugin Core functions 
 */




function dwc_get_theme_options($id=''){

	$dwc_all_plugin_options = get_option('dwc_plugin_options');
	if ($dwc_all_plugin_options && $id) {
		return $dwc_all_plugin_options[$id];
	}else{
		return $dwc_all_plugin_options;
	}
}
