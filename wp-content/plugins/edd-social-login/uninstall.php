<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Uninstall
 *
 * Does delete the created tables and all the plugin options
 * when uninstalling the plugin
 *
 * @package Easy Digital Downloads - Social Login
 * @since 1.0.0
 */

// check if the plugin really gets uninstalled 
if( !defined( 'WP_UNINSTALL_PLUGIN' ) ) 
	exit();
	
global $edd_options;

// check remove data on uninstall is checked, if yes then delete plugin data
if( edd_get_option( 'uninstall_on_delete' ) ) {				
	
	//delete social order option 
	delete_option('edd_social_order');
	
	//delete plugin version option 
	delete_option('edd_slg_set_option');
		
	// Unset all option values from edd global array to delete it
	unset( $edd_options['edd_slg_login_heading'] );
	unset( $edd_options['edd_slg_enable_notification'] );
	unset( $edd_options['edd_slg_redirect_url'] );
	
	unset( $edd_options['edd_slg_enable_facebook'] );	
	unset( $edd_options['edd_slg_fb_app_id'] );
	unset( $edd_options['edd_slg_fb_app_secret'] );	
	unset( $edd_options['edd_slg_fb_language'] );
	unset( $edd_options['edd_slg_fb_icon_url'] );
	unset( $edd_options['edd_slg_fb_link_icon_url'] );
	unset( $edd_options['edd_slg_enable_fb_avatar'] );
	
	unset( $edd_options['edd_slg_enable_googleplus'] );				
	unset( $edd_options['edd_slg_gp_client_id'] );
	unset( $edd_options['edd_slg_gp_client_secret'] );
	unset( $edd_options['edd_slg_gp_icon_url'] );
	unset( $edd_options['edd_slg_gp_link_icon_url'] );
	unset( $edd_options['edd_slg_enable_gp_avatar'] );
		
	unset( $edd_options['edd_slg_enable_linkedin'] );
	unset( $edd_options['edd_slg_li_app_id'] );
	unset( $edd_options['edd_slg_li_app_secret'] );
	unset( $edd_options['edd_slg_li_icon_url'] );
	unset( $edd_options['edd_slg_li_link_icon_url'] );
	unset( $edd_options['edd_slg_enable_li_avatar'] );
	
	unset( $edd_options['edd_slg_enable_twitter'] );
	unset( $edd_options['edd_slg_tw_consumer_key'] );
	unset( $edd_options['edd_slg_tw_consumer_secret'] );
	unset( $edd_options['edd_slg_tw_icon_url'] );
	unset( $edd_options['edd_slg_tw_link_icon_url'] );
	unset( $edd_options['edd_slg_enable_tw_avatar'] );
	
	unset( $edd_options['edd_slg_enable_yahoo'] );
	unset( $edd_options['edd_slg_yh_consumer_key'] );
	unset( $edd_options['edd_slg_yh_consumer_secret'] );
	unset( $edd_options['edd_slg_yh_app_id'] );
	unset( $edd_options['edd_slg_yh_icon_url'] );
	unset( $edd_options['edd_slg_yh_link_icon_url'] );
	unset( $edd_options['edd_slg_enable_yh_avatar'] );
	
	unset( $edd_options['edd_slg_enable_foursquare'] );
	unset( $edd_options['edd_slg_fs_client_id'] );
	unset( $edd_options['edd_slg_fs_client_secret'] );
	unset( $edd_options['edd_slg_fs_icon_url'] );
	unset( $edd_options['edd_slg_fs_link_icon_url'] );
	unset( $edd_options['edd_slg_enable_fs_avatar'] );
	
	unset( $edd_options['edd_slg_enable_windowslive'] );
	unset( $edd_options['edd_slg_wl_client_id'] );
	unset( $edd_options['edd_slg_wl_client_secret'] );
	unset( $edd_options['edd_slg_wl_icon_url'] );
	unset( $edd_options['edd_slg_wl_link_icon_url'] );
	
	unset( $edd_options['edd_slg_enable_vk'] );
	unset( $edd_options['edd_slg_vk_app_id'] );
	unset( $edd_options['edd_slg_vk_app_secret'] );
	unset( $edd_options['edd_slg_vk_icon_url'] );
	unset( $edd_options['edd_slg_vk_link_icon_url'] );
	unset( $edd_options['edd_slg_enable_vk_avatar'] );
	
	unset( $edd_options['edd_slg_enable_instagram'] );
	unset( $edd_options['edd_slg_inst_app_id'] );
	unset( $edd_options['edd_slg_inst_app_secret'] );
	unset( $edd_options['edd_slg_inst_icon_url'] );
	unset( $edd_options['edd_slg_inst_link_icon_url'] );
	unset( $edd_options['edd_slg_enable_inst_avatar'] );
	
	// update edd_settings option
	update_option( 'edd_settings', $edd_options );
}
?>