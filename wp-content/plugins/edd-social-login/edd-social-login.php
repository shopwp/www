<?php
/**
 * Plugin Name: Easy Digital Downloads - Social Login
 * Plugin URI: https://easydigitaldownloads.com/extensions/social-login/
 * Description: Allow your customers to login and checkout with social networks such as Facebook, Twitter, Google, Yahoo, LinkedIn, Foursquare, Windows Live, VK.com, Instagram, Amazon and PayPal.
 * Version: 1.7.6
 * Author: WPWeb
 * Author URI: http://wpweb.co.in
 * Text Domain: eddslg
 * Domain Path: languages
 * 
 * @package Easy Digital Downloads - Social Login
 * @category Core
 * @author WPWeb
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Basic plugin definitions 
 * 
 * @package Easy Digital Downloads - Social Login
 * @since 1.0.0
 */
global $wpdb;

if( !defined( 'EDD_SLG_VERSION' ) ) {
	define( 'EDD_SLG_VERSION', '1.7.6' ); // Plugin version
}
if( !defined( 'EDD_SLG_URL' ) ) {
	define( 'EDD_SLG_URL', plugin_dir_url( __FILE__ ) ); // plugin url
}
if( !defined( 'EDD_SLG_DIR' ) ) {
	define( 'EDD_SLG_DIR', dirname( __FILE__ ) ); // plugin dir
}
if( !defined( 'EDD_SLG_SOCIAL_DIR' ) ) {
	define( 'EDD_SLG_SOCIAL_DIR', EDD_SLG_DIR . '/includes/social' ); // social dir
}
if( !defined( 'EDD_SLG_SOCIAL_LIB_DIR' ) ) {
	define( 'EDD_SLG_SOCIAL_LIB_DIR', EDD_SLG_DIR . '/includes/social/libraries' ); // lib dir
}
if( !defined( 'EDD_SLG_IMG_URL' ) ) {
	define( 'EDD_SLG_IMG_URL', EDD_SLG_URL . 'includes/images' ); // image url
}
if( !defined( 'EDD_SLG_ADMIN' ) ) {
	define( 'EDD_SLG_ADMIN', EDD_SLG_DIR . '/includes/admin' ); // plugin admin dir
}
if( !defined( 'EDD_SLG_USER_PREFIX' ) ) {
	define( 'EDD_SLG_USER_PREFIX', 'edd_user_' ); // username prefix
}
if( !defined( 'EDD_SLG_USER_META_PREFIX' ) ) {
	define( 'EDD_SLG_USER_META_PREFIX', 'eddslg_' ); // username prefix
}
if( !defined( 'EDD_SLG_BASENAME') ) {
	define( 'EDD_SLG_BASENAME', 'edd-social-login' );
}

$upload_dir		= wp_upload_dir();
$upload_path	= isset( $upload_dir['basedir'] ) ? $upload_dir['basedir'].'/' : ABSPATH;
$upload_url		= isset( $upload_dir['baseurl'] ) ? $upload_dir['baseurl'] : site_url();

// edd social login upload folder path
if( !defined( 'EDD_SLG_TMP_DIR' ) ) {
	define( 'EDD_SLG_TMP_DIR' , $upload_path . 'edd/eddslg-tmp' );
}

/**
 * Admin notices
 * 
 * @package Easy Digital Downloads - Social Login
 * @since 1.0.0
 */
function edd_slg_admin_notices() {
	
	if ( ! class_exists( 'Easy_Digital_Downloads' ) ) {
		
		echo '<div class="error">';
		echo "<p><strong>" . __( 'Easy Digital Downloads needs to be activated to be able to use the Social Login.', 'eddslg' ) . "</strong></p>";
		echo '</div>';
	}
}

/**
 * Check Easy Digital Downloads Plugin
 *
 * Handles to check Easy Digital Downloads plugin
 * if not activated then deactivate our plugin
 *
 * @package Easy Digital Downloads - Social Login
 * @since 1.0.0
 */
function edd_slg_check_activation() {
	
	if ( ! class_exists( 'Easy_Digital_Downloads' ) ) {
		// is this plugin active?
		if ( is_plugin_active( plugin_basename( __FILE__ ) ) ) {
			// deactivate the plugin
	 		deactivate_plugins( plugin_basename( __FILE__ ) );
	 		// unset activation notice
	 		unset( $_GET[ 'activate' ] );
	 		// display notice
	 		add_action( 'admin_notices', 'edd_slg_admin_notices' );
		}
	}
}
//Check Easy Digital Downloads plugin is Activated or not
add_action( 'admin_init', 'edd_slg_check_activation' );

/**
 * Activation Hook
 *
 * Register plugin activation hook.
 *
 * @package Easy Digital Downloads - Social Login
 * @since 1.0.0
 */
register_activation_hook( __FILE__, 'edd_slg_install' );

/**
 * Plugin Setup (On Activation)
 *
 * Does the initial setup,
 * stest default values for the plugin options.
 *
 * @package Easy Digital Downloads - Social Login
 * @since 1.0.0
 */
function edd_slg_install() {
	
	global $wpdb, $edd_options;
	
	/*************** Default Options Saving to Options of EDD Start ***************/
	$udpopt = false;
	//check social login header is not set
	if( !isset( $edd_options['edd_slg_login_heading'] ) ) {
		$edd_options['edd_slg_login_heading'] =  __( 'Prefer to Login with Social Media', 'eddslg' );
		$udpopt = true;
	}//end if
	
	//check social login enable notification is not set
	if( !isset( $edd_options['edd_slg_enable_notification'] ) ) {
		$edd_options['edd_slg_enable_notification'] =  '';
		$udpopt = true;
	}//end if
	
	//check social login redirect url is not set
	if( !isset( $edd_options['edd_slg_redirect_url'] ) ) {
		$edd_options['edd_slg_redirect_url'] =  '';
		$udpopt = true;
	}//end if
	
	
	//check social login button shown on login page or not
	if( !isset( $edd_options['edd_slg_enable_login_page'] ) ) {
		$edd_options['edd_slg_enable_login_page'] =  '';
		$udpopt = true;
	}//end if
	
	
	//check social login button shown on myaccount page or not
	if( !isset( $edd_options['edd_slg_display_link_thank_you'] ) ) {
		$edd_options['edd_slg_display_link_thank_you'] =  '';
		$udpopt = true;
	}//end if
	
	//check social login enable facebook is not set
	if( !isset( $edd_options['edd_slg_enable_facebook'] ) ) {
		$edd_options['edd_slg_enable_facebook'] =  '';
		$udpopt = true;
	}//end if
	
	//check social login facebook app id is not set
	if( !isset( $edd_options['edd_slg_fb_app_id'] ) ) {
		$edd_options['edd_slg_fb_app_id'] =  '';
		$udpopt = true;
	}//end if
	
	//check social login facebook app secret is not set
	if( !isset( $edd_options['edd_slg_fb_app_secret'] ) ) {
		$edd_options['edd_slg_fb_app_secret'] =  '';
		$udpopt = true;
	}//end if
	
	//check social login facebook language is not set
	if( !isset( $edd_options['edd_slg_fb_language'] ) ) {
		$edd_options['edd_slg_fb_language'] =  'en_US';
		$udpopt = true;
	}//end if
	
	//check social login facebook button icon url is not set
	if( !isset( $edd_options['edd_slg_fb_icon_url'] ) ) {
		$edd_options['edd_slg_fb_icon_url'] =  EDD_SLG_IMG_URL . '/facebook.png';
		$udpopt = true;
	}//end if
	
	//check social login enable facebook avatar is not set
	if( !isset( $edd_options['edd_slg_enable_fb_avatar'] ) ) {
		$edd_options['edd_slg_enable_fb_avatar'] =  '';
		$udpopt = true;
	}//end if
	
	//check social login googleplus enable is not set
	if( !isset( $edd_options['edd_slg_enable_googleplus'] ) ) {
		$edd_options['edd_slg_enable_googleplus'] =  '';
		$udpopt = true;
	}//end if
	
	//check social login googleplus client id is not set
	if( !isset( $edd_options['edd_slg_gp_client_id'] ) ) {
		$edd_options['edd_slg_gp_client_id'] =  '';
		$udpopt = true;
	}//end if
	
	//check social login googleplus client id is not set
	if( !isset( $edd_options['edd_slg_gp_client_secret'] ) ) {
		$edd_options['edd_slg_gp_client_secret'] =  '';
		$udpopt = true;
	}//end if
	
	//check social login googleplus icon url is not set
	if( !isset( $edd_options['edd_slg_gp_icon_url'] ) ) {
		$edd_options['edd_slg_gp_icon_url'] =  EDD_SLG_IMG_URL . '/googleplus.png';
		$udpopt = true;
	}//end if
	
	//check social login enable google plus avatar is not set
	if( !isset( $edd_options['edd_slg_enable_gp_avatar'] ) ) {
		$edd_options['edd_slg_enable_gp_avatar'] =  '';
		$udpopt = true;
	}//end if
	
	//check social login linkedin enabled is not set
	if( !isset( $edd_options['edd_slg_enable_linkedin'] ) ) {
		$edd_options['edd_slg_enable_linkedin'] =  '';
		$udpopt = true;
	}//end if
	
	//check social login linkedin app id is not set
	if( !isset( $edd_options['edd_slg_li_app_id'] ) ) {
		$edd_options['edd_slg_li_app_id'] =  '';
		$udpopt = true;
	}//end if
	
	//check social login linkedin app secret is not set
	if( !isset( $edd_options['edd_slg_li_app_secret'] ) ) {
		$edd_options['edd_slg_li_app_secret'] =  '';
		$udpopt = true;
	}//end if
	
	//check social login linkedin icon url id is not set
	if( !isset( $edd_options['edd_slg_li_icon_url'] ) ) {
		$edd_options['edd_slg_li_icon_url'] =  EDD_SLG_IMG_URL . '/linkedin.png';
		$udpopt = true;
	}//end if
	
	//check social login enable linkedIn avatar is not set
	if( !isset( $edd_options['edd_slg_enable_li_avatar'] ) ) {
		$edd_options['edd_slg_enable_li_avatar'] =  '';
		$udpopt = true;
	}//end if
	
	//check social login twitter enabled is not set
	if( !isset( $edd_options['edd_slg_enable_twitter'] ) ) {
		$edd_options['edd_slg_enable_twitter'] =  '';
		$udpopt = true;
	}//end if
	
	//check social login twitter consumer key is not set
	if( !isset( $edd_options['edd_slg_tw_consumer_key'] ) ) {
		$edd_options['edd_slg_tw_consumer_key'] =  '';
		$udpopt = true;
	}//end if
	
	//check social login twitter consumer secret is not set
	if( !isset( $edd_options['edd_slg_tw_consumer_secret'] ) ) {
		$edd_options['edd_slg_tw_consumer_secret'] =  '';
		$udpopt = true;
	}//end if
	
	//check social login twitter icon url is not set
	if( !isset( $edd_options['edd_slg_tw_icon_url'] ) ) {
		$edd_options['edd_slg_tw_icon_url'] =  EDD_SLG_IMG_URL . '/twitter.png';
		$udpopt = true;
	}//end if
	
	//check social login enable twitter avatar is not set
	if( !isset( $edd_options['edd_slg_enable_tw_avatar'] ) ) {
		$edd_options['edd_slg_enable_tw_avatar'] =  '';
		$udpopt = true;
	}//end if
	
	//check social login yahoo enabled is not set
	if( !isset( $edd_options['edd_slg_enable_yahoo'] ) ) {
		$edd_options['edd_slg_enable_yahoo'] =  '';
		$udpopt = true;
	}//end if
	
	//check social login yahoo consumer key is not set
	if( !isset( $edd_options['edd_slg_yh_consumer_key'] ) ) {
		$edd_options['edd_slg_yh_consumer_key'] =  '';
		$udpopt = true;
	}//end if
	
	//check social login yahoo consumer secret is not set
	if( !isset( $edd_options['edd_slg_yh_consumer_secret'] ) ) {
		$edd_options['edd_slg_yh_consumer_secret'] =  '';
		$udpopt = true;
	}//end if
	
	//check social login yahoo app id is not set
	if( !isset( $edd_options['edd_slg_yh_app_id'] ) ) {
		$edd_options['edd_slg_yh_app_id'] =  '';
		$udpopt = true;
	}//end if
	
	//check social login yahoo icon url is not set
	if( !isset( $edd_options['edd_slg_yh_icon_url'] ) ) {
		$edd_options['edd_slg_yh_icon_url'] =  EDD_SLG_IMG_URL . '/yahoo.png';
		$udpopt = true;
	}//end if
	
	//check social login enable yahoo avatar is not set
	if( !isset( $edd_options['edd_slg_enable_yh_avatar'] ) ) {
		$edd_options['edd_slg_enable_yh_avatar'] =  '';
		$udpopt = true;
	}//end if
	
	//check social login foursquare enable is not set
	if( !isset( $edd_options['edd_slg_enable_foursquare'] ) ) {
		$edd_options['edd_slg_enable_foursquare'] =  '';
		$udpopt = true;
	}//end if
	
	//check social login foursquare client id is not set
	if( !isset( $edd_options['edd_slg_fs_client_id'] ) ) {
		$edd_options['edd_slg_fs_client_id'] =  '';
		$udpopt = true;
	}//end if
	
	//check social login foursquare client secret is not set
	if( !isset( $edd_options['edd_slg_fs_client_secret'] ) ) {
		$edd_options['edd_slg_fs_client_secret'] =  '';
		$udpopt = true;
	}//end if
	
	//check social login foursquare icon url is not set
	if( !isset( $edd_options['edd_slg_fs_icon_url'] ) ) {
		$edd_options['edd_slg_fs_icon_url'] =  EDD_SLG_IMG_URL . '/foursquare.png';
		$udpopt = true;
	}//end if
	
	//check social login enable foursquare avatar is not set
	if( !isset( $edd_options['edd_slg_enable_fs_avatar'] ) ) {
		$edd_options['edd_slg_enable_fs_avatar'] =  '';
		$udpopt = true;
	}//end if
	
	//check social login windowslive enable is not set
	if( !isset( $edd_options['edd_slg_enable_windowslive'] ) ) {
		$edd_options['edd_slg_enable_windowslive'] =  '';
		$udpopt = true;
	}//end if
	
	//check social login windowslive client id is not set
	if( !isset( $edd_options['edd_slg_wl_client_id'] ) ) {
		$edd_options['edd_slg_wl_client_id'] =  '';
		$udpopt = true;
	}//end if
	
	//check social login windowslive client secret is not set
	if( !isset( $edd_options['edd_slg_wl_client_secret'] ) ) {
		$edd_options['edd_slg_wl_client_secret'] =  '';
		$udpopt = true;
	}//end if
	
	//check social login windowslive icon url is not set
	if( !isset( $edd_options['edd_slg_wl_icon_url'] ) ) {
		$edd_options['edd_slg_wl_icon_url'] =  EDD_SLG_IMG_URL . '/windowslive.png';
		$udpopt = true;
	}//end if
	
	
	/***********************************************************/
	//check social login enable vk is not set
	if( !isset( $edd_options['edd_slg_enable_vk'] ) ) {
		$edd_options['edd_slg_enable_vk'] =  '';
		$udpopt = true;
	}//end if
	
	//check social login vk app id is not set
	if( !isset( $edd_options['edd_slg_vk_app_id'] ) ) {
		$edd_options['edd_slg_vk_client_id'] =  '';
		$udpopt = true;
	}//end if
	
	//check social login vk app secret is not set
	if( !isset( $edd_options['edd_slg_vk_app_secret'] ) ) {
		$edd_options['edd_slg_vk_client_secret'] =  '';
		$udpopt = true;
	}//end if
					
	//check social login vk button icon url is not set
	if( !isset( $edd_options['edd_slg_vk_icon_url'] ) ) {
		$edd_options['edd_slg_vk_icon_url'] =  EDD_SLG_IMG_URL . '/vk.png';
		$udpopt = true;
	}//end if
	
	//check social login enable vk avatar is not set
	if( !isset( $edd_options['edd_slg_enable_vk_avatar'] ) ) {
		$edd_options['edd_slg_enable_vk_avatar'] =  '';
		$udpopt = true;
	}//end if
	
	/***********************************************************/
	//check social login enable instagram is not set
	if( !isset( $edd_options['edd_slg_enable_instagram'] ) ) {
		$edd_options['edd_slg_enable_instagram'] =  '';
		$udpopt = true;
	}//end if
	
	//check social login instagram app id is not set
	if( !isset( $edd_options['edd_slg_inst_app_id'] ) ) {
		$edd_options['edd_slg_inst_app_id'] =  '';
		$udpopt = true;
	}//end if
	
	//check social login instagram app secret is not set
	if( !isset( $edd_options['edd_slg_inst_app_secret'] ) ) {
		$edd_options['edd_slg_inst_app_secret'] =  '';
		$udpopt = true;
	}//end if
					
	//check social login instagram button icon url is not set
	if( !isset( $edd_options['edd_slg_inst_icon_url'] ) ) {
		$edd_options['edd_slg_inst_icon_url'] =  EDD_SLG_IMG_URL . '/instagram.png';
		$udpopt = true;
	}//end if
	
	//check social login enable instagram avatar is not set
	if( !isset( $edd_options['edd_slg_enable_inst_avatar'] ) ) {
		$edd_options['edd_slg_enable_inst_avatar'] =  '';
		$udpopt = true;
	}//end if
	
	//check social link icon for facebook
	if( !isset( $edd_options['edd_slg_fb_link_icon_url'] ) ) {
		$edd_options['edd_slg_fb_link_icon_url'] =  EDD_SLG_IMG_URL . '/facebook-link.png';
		$udpopt = true;
	}//end if
	
	//check social link icon for google+
	if( !isset( $edd_options['edd_slg_gp_link_icon_url'] ) ) {
		$edd_options['edd_slg_gp_link_icon_url'] =  EDD_SLG_IMG_URL . '/googleplus-link.png';
		$udpopt = true;
	}//end if
	
	//check social link icon for linkdin
	if( !isset( $edd_options['edd_slg_li_link_icon_url'] ) ) {
		$edd_options['edd_slg_li_link_icon_url'] =  EDD_SLG_IMG_URL . '/linkedin-link.png';
		$udpopt = true;
	}//end if
	
	//check social link icon for twitter
	if( !isset( $edd_options['edd_slg_tw_link_icon_url'] ) ) {
		$edd_options['edd_slg_tw_link_icon_url'] =  EDD_SLG_IMG_URL . '/twitter-link.png';
		$udpopt = true;
	}//end if
	
	//check social link icon for yahoo
	if( !isset( $edd_options['edd_slg_yh_link_icon_url'] ) ) {
		$edd_options['edd_slg_yh_link_icon_url'] =  EDD_SLG_IMG_URL . '/yahoo-link.png';
		$udpopt = true;
	}//end if
	
	//check social link icon for foursquare
	if( !isset( $edd_options['edd_slg_fs_link_icon_url'] ) ) {
		$edd_options['edd_slg_fs_link_icon_url'] =  EDD_SLG_IMG_URL . '/foursquare-link.png';
		$udpopt = true;
	}//end if
	
	//check social link icon for windows live
	if( !isset( $edd_options['edd_slg_wl_link_icon_url'] ) ) {
		$edd_options['edd_slg_wl_link_icon_url'] =  EDD_SLG_IMG_URL . '/windowslive-link.png';
		$udpopt = true;
	}//end if
	
	//check social link icon for VK
	if( !isset( $edd_options['edd_slg_vk_link_icon_url'] ) ) {
		$edd_options['edd_slg_vk_link_icon_url'] =  EDD_SLG_IMG_URL . '/vk-link.png';
		$udpopt = true;
	}//end if
	
	//check social link icon for Instagram
	if( !isset( $edd_options['edd_slg_inst_link_icon_url'] ) ) {
		$edd_options['edd_slg_inst_link_icon_url'] =  EDD_SLG_IMG_URL . '/instagram-link.png';
		$udpopt = true;
	}//end if
	
	
	/***********************************************************/
	//check social login enable amazon is not set
	if( !isset( $edd_options['edd_slg_enable_amazon'] ) ) {
		$edd_options['edd_slg_enable_amazon'] =  '';
		$udpopt = true;
	}//end if
	
	//check social login amazon app id is not set
	if( !isset( $edd_options['edd_slg_amazon_app_id'] ) ) {
		$edd_options['edd_slg_amazon_app_id'] =  '';
		$udpopt = true;
	}//end if
	
	//check social login amazon app secret is not set
	if( !isset( $edd_options['edd_slg_amazon_app_secret'] ) ) {
		$edd_options['edd_slg_amazon_app_secret'] =  '';
		$udpopt = true;
	}//end if
					
	//check social login amazon button icon url is not set
	if( !isset( $edd_options['edd_slg_amazon_icon_url'] ) ) {
		$edd_options['edd_slg_amazon_icon_url'] =  EDD_SLG_IMG_URL . '/amazon.png';
		$udpopt = true;
	}//end if
	
	//check social link icon for amazon
	if( !isset( $edd_options['edd_slg_amazon_link_icon_url'] ) ) {
		$edd_options['edd_slg_amazon_link_icon_url'] =  EDD_SLG_IMG_URL . '/amazon-link.png';
		$udpopt = true;
	}//end if
	
	
	/***********************************************************/
	//check social login enable paypal is not set
	if( !isset( $edd_options['edd_slg_enable_paypal'] ) ) {
		$edd_options['edd_slg_enable_paypal'] =  '';
		$udpopt = true;
	}//end if
	
	//check social login paypal app id is not set
	if( !isset( $edd_options['edd_slg_paypal_app_id'] ) ) {
		$edd_options['edd_slg_paypal_app_id'] =  '';
		$udpopt = true;
	}//end if
	
	//check social login paypal app secret is not set
	if( !isset( $edd_options['edd_slg_paypal_app_secret'] ) ) {
		$edd_options['edd_slg_paypal_app_secret'] =  '';
		$udpopt = true;
	}//end if
					
	//check social login paypal button icon url is not set
	if( !isset( $edd_options['edd_slg_paypal_icon_url'] ) ) {
		$edd_options['edd_slg_paypal_icon_url'] =  EDD_SLG_IMG_URL . '/paypal.png';
		$udpopt = true;
	}//end if
	
	//check social link icon for paypal
	if( !isset( $edd_options['edd_slg_paypal_link_icon_url'] ) ) {
		$edd_options['edd_slg_paypal_link_icon_url'] =  EDD_SLG_IMG_URL . '/paypal-link.png';
		$udpopt = true;
	}//end if
	
	
	
	
	/***********************************************************/
	//check need to update the defaults value to options
	if( $udpopt == true ) { // if any of the settings need to be updated
		update_option( 'edd_settings', $edd_options );
	}
	
	/*************** Default Options Saving to Options of EDD End ***************/
	//get option for when plugin is activating first time
	$edd_slg_set_option = get_option( 'edd_slg_set_option' );
	
	//get social order options
	$edd_social_order = get_option( 'edd_social_order' );
	
	if( empty( $edd_social_order ) ) { //check plugin version option
		
		$edd_social_order = array( 'facebook', 'twitter', 'googleplus', 'linkedin', 'yahoo', 'foursquare', 'windowslive' );
		update_option( 'edd_social_order', $edd_social_order );
		//update plugin version to option 
		update_option('edd_slg_set_option','1.0');
	}
	
	$edd_slg_set_option = get_option( 'edd_slg_set_option' );
	
	if( $edd_slg_set_option == '1.0' ) {
		
		$vk_array = array( 'vk' );
		$edd_social_order = array_merge( $edd_social_order, $vk_array );
		update_option( 'edd_social_order', $edd_social_order );
		//update plugin version to option 
		update_option('edd_slg_set_option','1.1');
	}
	
	$edd_slg_set_option = get_option( 'edd_slg_set_option' );
	
	if( $edd_slg_set_option == '1.1' ) {
		
		$inst_array = array( 'instagram' );
		$edd_social_order = array_merge( $edd_social_order, $inst_array );
		update_option( 'edd_social_order', $edd_social_order );
		//update plugin version to option 
		update_option('edd_slg_set_option','1.2');
	}
	
	$edd_slg_set_option = get_option( 'edd_slg_set_option' );
	
	if( $edd_slg_set_option == '1.2' ) {
		
		// Amazon and paypal api added in api array
		$authorize_array = array( 'amazon','paypal' );
		$edd_social_order = array_merge( $edd_social_order, $authorize_array );
		update_option( 'edd_social_order', $edd_social_order );

		// Amazon and paypal options
		$authorize_options	= array( 
									'edd_slg_enable_amazon'			=> '',
									'edd_slg_amazon_app_id'		    => '',
									'edd_slg_amazon_app_secret'	    => '',
									'edd_slg_amazon_icon_url'		=> EDD_SLG_IMG_URL . '/amazon.png',
									'edd_slg_amazon_link_icon_url'	=> EDD_SLG_IMG_URL . '/amazon-link.png',
									'edd_slg_enable_paypal'			=> '',
									'edd_slg_paypal_app_id'		    => '',
									'edd_slg_paypal_app_secret'	    => '',
									'edd_slg_paypal_icon_url'		=> EDD_SLG_IMG_URL . '/paypal.png',
									'edd_slg_paypal_link_icon_url'	=> EDD_SLG_IMG_URL . '/paypal-link.png',
									'edd_slg_paypal_environment'    => 'sandbox',
								);
		
		foreach ( $authorize_options as $key => $value ) {
			update_option( $key, $value );
		}

		//update plugin version to option
		update_option( 'edd_slg_set_option', '1.3' );
	}

	//get social order options
	$edd_slg_set_option = get_option( 'edd_slg_set_option' );

	if( $edd_slg_set_option == '1.3' ) {

		// future code here
	}	
	
}

/**
 * Load Text Domain
 *
 * This gets the plugin ready for translation.
 *
 * @package Easy Digital Downloads - Social Login
 * @since 1.5.2
 */
function edd_slg_load_text_domain() {
	
	// Set filter for plugin's languages directory
	$edd_slg_lang_dir	= dirname( plugin_basename( __FILE__ ) ) . '/languages/';
	$edd_slg_lang_dir	= apply_filters( 'edd_slg_languages_directory', $edd_slg_lang_dir );
	
	// Traditional WordPress plugin locale filter
	$locale	= apply_filters( 'plugin_locale',  get_locale(), 'eddslg' );
	$mofile	= sprintf( '%1$s-%2$s.mo', 'eddslg', $locale );
	
	// Setup paths to current locale file
	$mofile_local	= $edd_slg_lang_dir . $mofile;
	$mofile_global	= WP_LANG_DIR . '/' . EDD_SLG_BASENAME . '/' . $mofile;
	
	if ( file_exists( $mofile_global ) ) { // Look in global /wp-content/languages/edd-social-login folder
		load_textdomain( 'eddslg', $mofile_global );
	} elseif ( file_exists( $mofile_local ) ) { // Look in local /wp-content/plugins/edd-social-login/languages/ folder
		load_textdomain( 'eddslg', $mofile_local );
	} else { // Load the default language files
		load_plugin_textdomain( 'eddslg', false, $edd_slg_lang_dir );
	}
}

/**
 * Add plugin action links
 *
 * Adds a Settings, Docs link to the plugin list.
 *
 * @package Easy Digital Downloads - Social Login
 * @since 1.5.1
 */
function edd_slg_add_plugin_links( $links ) {
	$plugin_links = array(
		'<a href="edit.php?post_type=download&page=edd-settings&tab=extensions">' . __( 'Settings', 'eddslg' ) . '</a>',
		'<a href="http://wpweb.co.in/documents/edd-social-login/">' . __( 'Docs', 'eddslg' ) . '</a>'		
	);
	
	return array_merge( $plugin_links, $links );
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'edd_slg_add_plugin_links' );	

//add action to load plugin
add_action( 'plugins_loaded', 'edd_slg_plugin_loaded' );

function edd_slg_plugin_loaded() { 
	
	//check easy digital downloads is activated or not
	if( class_exists( 'Easy_Digital_Downloads' ) ) {
		
		// load first text domain
		edd_slg_load_text_domain();
		
		//check EDD_License class is exist
		if( class_exists( 'EDD_License' ) ) {
			
			// Instantiate the licensing / updater. Must be placed in the main plugin file
			$license = new EDD_License( __FILE__, 'Social Login', EDD_SLG_VERSION, 'WPWeb' );
		}
		
		/**
		 * Deactivation Hook
		 *
		 * Register plugin deactivation hook.
		 *
		 * @package Easy Digital Downloads - Social Login
		 * @since 1.0.0
		 */
		register_deactivation_hook( __FILE__, 'edd_slg_uninstall');
		
		/**
		 * Plugin Setup (On Deactivation)
		 *
		 * Delete  plugin options.
		 *
		 * @package Easy Digital Downloads - Social Login
		 * @since 1.0.0
		 */
		function edd_slg_uninstall() {
			
		}
			
		/**
		 * Start Session
		 * 
		 * @package Easy Digital Downloads - Social Login
		 * @since 1.0.0
		 */
		function edd_slg_start_session() {
			
			if( !session_id() ) {
				session_start();
			}
		}
		
		//add action init for starting a session
		add_action( 'init', 'edd_slg_start_session');
	
		/**
		 * Includes Files
		 * 
		 * Includes some required files for plugin
		 *
		 * @package Easy Digital Downloads - Social Login
		 * @since 1.0.0
		 */
		global $edd_slg_model, $edd_slg_scripts,
			$edd_slg_render, $edd_slg_shortcodes,
			$edd_slg_public, $edd_slg_admin;
		
		// loads the Misc Functions file
		require_once ( EDD_SLG_DIR . '/includes/edd-slg-misc-functions.php' );
		edd_slg_initialize();
		
		//social class loads
		require_once( EDD_SLG_SOCIAL_DIR . '/edd-slg-social.php');
		
		//Model Class for generic functions
		require_once( EDD_SLG_DIR . '/includes/class-edd-slg-model.php' );
		$edd_slg_model = new EDD_Slg_Model();
		
		//Scripts Class for scripts / styles
		require_once( EDD_SLG_DIR . '/includes/class-edd-slg-scripts.php' );
		$edd_slg_scripts = new EDD_Slg_Scripts();
		$edd_slg_scripts->add_hooks();
		
		//Renderer Class for HTML
		require_once( EDD_SLG_DIR . '/includes/class-edd-slg-renderer.php' );
		$edd_slg_render = new EDD_Slg_Renderer();
		
		//Shortcodes class for handling shortcodes
		require_once( EDD_SLG_DIR . '/includes/class-edd-slg-shortcodes.php' );
		$edd_slg_shortcodes = new EDD_Slg_Shortcodes();
		$edd_slg_shortcodes->add_hooks();
	
		//Public Class for public functionlities
		require_once( EDD_SLG_DIR . '/includes/class-edd-slg-public.php' );
		$edd_slg_public = new EDD_Slg_Public();
		$edd_slg_public->add_hooks();
		
		//Admin Pages Class for admin site
		require_once( EDD_SLG_ADMIN . '/class-edd-slg-admin.php' );
		$edd_slg_admin = new EDD_Slg_Admin();
		$edd_slg_admin->add_hooks();
		
		//Register Widget
		require_once( EDD_SLG_DIR . '/includes/widgets/class-edd-slg-login-buttons.php');
		
		//Loads the Templates Functions file
		require_once ( EDD_SLG_DIR . '/includes/edd-slg-template-functions.php' );
		
		//Loads the Template Hook File
		require_once( EDD_SLG_DIR . '/includes/edd-slg-template-hooks.php' );
	}
}