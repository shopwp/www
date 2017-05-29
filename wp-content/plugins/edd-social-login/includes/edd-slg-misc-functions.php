<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Misc Functions
 * 
 * All misc functions handles to 
 * different functions 
 * 
 * @package Easy Digital Downloads - Social Login
 * @since 1.0.0
 */
	/**
	 * All Social Deals Networks
	 * 
	 * Handles to return all social networks
	 * names
	 * 
	 * @package Easy Digital Downloads - Social Login
	 * @since 1.0.0
	 */
	function edd_slg_social_networks() {
		
		$socialnetworks = array(
									'facebook'		=>	__( 'Facebook', 'eddslg' ),
									'twitter'		=>	__( 'Twitter', 'eddslg' ),
									'googleplus'	=>	__( 'Google+', 'eddslg' ),
									'linkedin'		=>	__( 'LinkedIn', 'eddslg' ),
									'yahoo'			=>	__( 'Yahoo', 'eddslg' ),
									'foursquare'	=>	__( 'Foursquare', 'eddslg' ),
									'windowslive'	=>	__( 'Windows Live', 'eddslg' ),
									'vk'			=>	__( 'VK', 'eddslg' ),
									'instagram'		=>	__( 'Instagram', 'eddslg' ),
									'amazon'		=>	__( 'Amazon', 'eddslg' ),
									'paypal'		=>	__( 'Paypal', 'eddslg' ),
								);
		return apply_filters( 'edd_slg_social_networks', $socialnetworks );
	}
	
	/**
	 * Get Social Network Sorted List
	 * as per saved in options
	 * 
	 * Handles to return social networks sorted
	 * array to list in page
	 * 
	 * @package Easy Digital Downloads - Social Login
	 * @since 1.0.0
	 */
	function edd_slg_get_sorted_social_network() {
		
		global $edd_options;
		
		$edd_social_order = get_option( 'edd_social_order' );
		
		$socials = edd_slg_social_networks();
		
		if( !isset( $edd_social_order ) || empty( $edd_social_order ) ) {
			return $socials;
		}
		
		$sorted_socials = $edd_social_order;
		$return = array();
		for( $i = 0; $i < count( $socials ); $i++ ) {
			$return[$sorted_socials[$i]] = $socials[$sorted_socials[$i]];
		}
		
		return apply_filters( 'edd_slg_sorted_social_networks', $return );
	}
	
	/**
	 * Initialize some needed variables
	 * 
	 * @package Easy Digital Downloads - Social Login
	 * @since 1.0.0
	 */
	function edd_slg_initialize() {
		
		global $edd_options;		
		
		//facebook variable initialization
		$fb_app_id = isset( $edd_options['edd_slg_fb_app_id'] ) ? $edd_options['edd_slg_fb_app_id'] : '';
		$fb_app_secret = isset( $edd_options['edd_slg_fb_app_secret'] ) ? $edd_options['edd_slg_fb_app_secret'] : '';
		
		if( !defined( 'EDD_SLG_FB_APP_ID' ) ) {
			define( 'EDD_SLG_FB_APP_ID', $fb_app_id );
		}
		if( !defined( 'EDD_SLG_FB_APP_SECRET' ) ) {
			define( 'EDD_SLG_FB_APP_SECRET', $fb_app_secret );
		}
		
		//google+ variable initialization
		$gp_client_id = isset( $edd_options['edd_slg_gp_client_id'] ) ? $edd_options['edd_slg_gp_client_id'] : '';
		$gp_client_secret = isset( $edd_options['edd_slg_gp_client_secret'] ) ? $edd_options['edd_slg_gp_client_secret'] : '';
		
		if( !defined( 'EDD_SLG_GP_CLIENT_ID' ) ) {
			define( 'EDD_SLG_GP_CLIENT_ID', $gp_client_id );
		}
		if( !defined( 'EDD_SLG_GP_CLIENT_SECRET' ) ) {
			define( 'EDD_SLG_GP_CLIENT_SECRET', $gp_client_secret );
		}
		if( !defined( 'EDD_SLG_GP_REDIRECT_URL' ) ) {
			$googleurl = add_query_arg( 'eddslg', 'google', site_url() );
			define( 'EDD_SLG_GP_REDIRECT_URL', $googleurl );
		}
		
		//linkedin variable initialization
		$li_app_id = isset( $edd_options['edd_slg_li_app_id'] ) ? $edd_options['edd_slg_li_app_id'] : '';
		$li_app_secret = isset( $edd_options['edd_slg_li_app_secret'] ) ? $edd_options['edd_slg_li_app_secret'] : '';
		
		if( !defined( 'EDD_SLG_LI_APP_ID' ) ) {
			define( 'EDD_SLG_LI_APP_ID', $li_app_id );
		}
		if( !defined( 'EDD_SLG_LI_APP_SECRET' ) ) {
			define( 'EDD_SLG_LI_APP_SECRET', $li_app_secret );
		}
		if( !defined( 'EDD_SLG_LI_REDIRECT_URL' ) ) {
			$linkedinurl = add_query_arg( 'eddslg', 'linkedin', trailingslashit( site_url() ) );
			define( 'EDD_SLG_LI_REDIRECT_URL', $linkedinurl );
		}
		// For LinkedIn Port http / https
		if( !defined( 'LINKEDIN_PORT_HTTP' ) ) { //http port value
		 	define( 'LINKEDIN_PORT_HTTP', '80' );
		}
		if( !defined( 'LINKEDIN_PORT_HTTP_SSL' ) ) { //ssl port value
		  	define( 'LINKEDIN_PORT_HTTP_SSL', '443' );
		}
		
		//twitter variable initialization
		$tw_consumer_key = isset( $edd_options['edd_slg_tw_consumer_key'] ) ? $edd_options['edd_slg_tw_consumer_key'] : '';
		$tw_consumer_secrets = isset( $edd_options['edd_slg_tw_consumer_secret'] ) ? $edd_options['edd_slg_tw_consumer_secret'] : '';
		
		if( !defined( 'EDD_SLG_TW_CONSUMER_KEY' ) ) {
			define( 'EDD_SLG_TW_CONSUMER_KEY', $tw_consumer_key );
		}
		if( !defined( 'EDD_SLG_TW_CONSUMER_SECRET' ) ) {
			define( 'EDD_SLG_TW_CONSUMER_SECRET', $tw_consumer_secrets );
		}
		
		//yahoo variable initialization
		$yh_consumer_key = isset( $edd_options['edd_slg_yh_consumer_key'] ) ? $edd_options['edd_slg_yh_consumer_key'] : '';
		$yh_consumer_secret = isset( $edd_options['edd_slg_yh_consumer_secret'] ) ? $edd_options['edd_slg_yh_consumer_secret'] : '';
		$yh_app_id = isset( $edd_options['edd_slg_yh_app_id'] ) ? $edd_options['edd_slg_yh_app_id'] : '';
		
		if( !defined( 'EDD_SLG_YH_CONSUMER_KEY' ) ) {
			define( 'EDD_SLG_YH_CONSUMER_KEY', $yh_consumer_key );
		}
		if( !defined( 'EDD_SLG_YH_CONSUMER_SECRET' ) ) {
			define( 'EDD_SLG_YH_CONSUMER_SECRET', $yh_consumer_secret );
		}
		if( !defined( 'EDD_SLG_YH_APP_ID' ) ) {
			define( 'EDD_SLG_YH_APP_ID', $yh_app_id );
		}
		if( !defined( 'EDD_SLG_YH_REDIRECT_URL' ) ) {
			$yahoourl = add_query_arg( 'eddslg', 'yahoo', site_url() );
			define( 'EDD_SLG_YH_REDIRECT_URL', $yahoourl );
		}
		
		//foursquare variable initialization
		$fs_client_id = isset( $edd_options['edd_slg_fs_client_id'] ) ? $edd_options['edd_slg_fs_client_id'] : '';
		$fs_client_secrets = isset( $edd_options['edd_slg_fs_client_secret'] ) ? $edd_options['edd_slg_fs_client_secret'] : '';
		
		if( !defined( 'EDD_SLG_FS_CLIENT_ID' ) ) {
			define( 'EDD_SLG_FS_CLIENT_ID', $fs_client_id );
		}
		if( !defined( 'EDD_SLG_FS_CLIENT_SECRET' ) ) {
			define( 'EDD_SLG_FS_CLIENT_SECRET', $fs_client_secrets );
		}
		if( !defined( 'EDD_SLG_FS_REDIRECT_URL' ) ) {
			$fsredirecturl = add_query_arg( 'eddslg', 'foursquare', site_url() );
			define( 'EDD_SLG_FS_REDIRECT_URL', $fsredirecturl );
		}
		
		//windows live variable initialization
		$wl_client_id = isset( $edd_options['edd_slg_wl_client_id'] ) ? $edd_options['edd_slg_wl_client_id'] : '';
		$wl_client_secrets = isset( $edd_options['edd_slg_wl_client_secret'] ) ? $edd_options['edd_slg_wl_client_secret'] : '';
		
		if( !defined( 'EDD_SLG_WL_CLIENT_ID' ) ) {
			define( 'EDD_SLG_WL_CLIENT_ID', $wl_client_id );
		}
		if( !defined( 'EDD_SLG_WL_CLIENT_SECRET' ) ) {
			define( 'EDD_SLG_WL_CLIENT_SECRET', $wl_client_secrets );
		}
		if( !defined( 'EDD_SLG_WL_REDIRECT_URL' ) ) {
			$wlredirecturl = add_query_arg( 'eddslg', 'windowslive', site_url() );
			define( 'EDD_SLG_WL_REDIRECT_URL', $wlredirecturl );
		}
		
		//vk variable initialization
		$vk_client_id = isset( $edd_options['edd_slg_vk_app_id'] ) ? $edd_options['edd_slg_vk_app_id'] : '';
		$vk_client_secrets = isset( $edd_options['edd_slg_vk_app_secret'] ) ? $edd_options['edd_slg_vk_app_secret'] : '';
		
		if( !defined( 'EDD_SLG_VK_APP_ID' ) ) {
			define( 'EDD_SLG_VK_APP_ID', $vk_client_id );
		}
		if( !defined( 'EDD_SLG_VK_APP_SECRET' ) ) {
			define( 'EDD_SLG_VK_APP_SECRET', $vk_client_secrets );
		}
		if( !defined( 'EDD_SLG_VK_REDIRECT_URL' ) ) {
			$vkredirecturl = add_query_arg( 'eddslg', 'vk', site_url() );
			define( 'EDD_SLG_VK_REDIRECT_URL', $vkredirecturl );
		}
		
		if( !defined( 'EDD_SLG_VK_LINK' ) ) {// define vk variable for link
			$vk_link = 'https://vk.com';
			define( 'EDD_SLG_VK_LINK', $vk_link );
		}
		
		//instagram variable initialization
		$inst_client_id = isset( $edd_options['edd_slg_inst_app_id'] ) ? $edd_options['edd_slg_inst_app_id'] : '';
		$inst_client_secrets = isset( $edd_options['edd_slg_inst_app_secret'] ) ? $edd_options['edd_slg_inst_app_secret'] : '';
		
		if( !defined( 'EDD_SLG_INST_APP_ID' ) ) {
			define( 'EDD_SLG_INST_APP_ID', $inst_client_id );
		}
		if( !defined( 'EDD_SLG_INST_APP_SECRET' ) ) {
			define( 'EDD_SLG_INST_APP_SECRET', $inst_client_secrets );
		}
		if( !defined( 'EDD_SLG_INST_REDIRECT_URL' ) ) {
			$instredirecturl = add_query_arg( 'eddslg', 'instagram', site_url() );
			define( 'EDD_SLG_INST_REDIRECT_URL', $instredirecturl );
		}
		
		//Amazon variable initialization
		$amazon_client_id = isset( $edd_options['edd_slg_amazon_app_id'] ) ? $edd_options['edd_slg_amazon_app_id'] : '';
		$amazon_client_secrets = isset( $edd_options['edd_slg_amazon_app_secret'] ) ? $edd_options['edd_slg_amazon_app_secret'] : '';
		if( !defined( 'EDD_SLG_AMAZON_APP_ID' ) ) {
			define( 'EDD_SLG_AMAZON_APP_ID', $amazon_client_id );
		}
		if( !defined( 'EDD_SLG_AMAZON_APP_SECRET' ) ) {
			define( 'EDD_SLG_AMAZON_APP_SECRET', $amazon_client_secrets );
		}	
		if( !defined( 'EDD_SLG_AMAZON_REDIRECT_URL' ) ) {
			$amazonredirecturl = add_query_arg( 'eddslg', 'amazon', site_url() );
			define( 'EDD_SLG_AMAZON_REDIRECT_URL', $amazonredirecturl );
		}
	
		//Payapl variable initialization
		$paypal_client_id = isset( $edd_options['edd_slg_paypal_app_id'] ) ? $edd_options['edd_slg_paypal_app_id'] : '';
		$paypal_client_secrets = isset( $edd_options['edd_slg_paypal_app_secret'] ) ? $edd_options['edd_slg_paypal_app_secret'] : '';
		$paypal_environment = isset( $edd_options['edd_slg_paypal_environment'] ) ? $edd_options['edd_slg_paypal_environment'] : 'sandbox';
	
		if( !defined( 'EDD_SLG_PAYPAL_APP_ID' ) ) {
			define( 'EDD_SLG_PAYPAL_APP_ID', $paypal_client_id );
		}	
		if( !defined( 'EDD_SLG_PAYPAL_APP_SECRET' ) ) {
			define( 'EDD_SLG_PAYPAL_APP_SECRET', $paypal_client_secrets );
		}	
		if( !defined( 'EDD_SLG_PAYPAL_REDIRECT_URL' ) ) {
			$paypalredirecturl = add_query_arg( 'eddslg', 'paypal', site_url() );
			define( 'EDD_SLG_PAYPAL_REDIRECT_URL', $paypalredirecturl );
		}		
		if( !defined( 'EDD_SLG_PAYPAL_ENVIRONMENT' ) ) {
			define( 'EDD_SLG_PAYPAL_ENVIRONMENT', $paypal_environment );
		}
		
	}
	
	/**
	 * Checkout Page URL
	 * 
	 * Handles to return checkout page url
	 * 
	 * @package Easy Digital Downloads - Social Login
	 * @since 1.0.0
	 */
	function edd_slg_send_on_checkout_page( $queryarg = array() ) {
		
		global $edd_options;
		
		$sendcheckout		= get_permalink( $edd_options['purchase_page'] );
		$sendcheckouturl	= add_query_arg( $queryarg, $sendcheckout );
		
		wp_redirect( apply_filters( 'edd_slg_checkout_page_redirect', $sendcheckouturl, $queryarg ) );
		exit;
	}
	
	/**
	 * Check Any One Social Media
	 * Login is enable or not
	 * 
	 * Handles to Check any one social
	 * media login is enable or not
	 * 
	 * @package Easy Digital Downloads - Social Login
	 * @since 1.0.0
	 */
	function edd_slg_check_social_enable() {
		
		global $edd_options;
		
		$return = false;
		
		//check if any social is activated or not
		if( !empty( $edd_options['edd_slg_enable_facebook'] ) || !empty( $edd_options['edd_slg_enable_googleplus'] ) || 
			!empty( $edd_options['edd_slg_enable_linkedin'] ) || !empty( $edd_options['edd_slg_enable_twitter'] ) || 
			!empty( $edd_options['edd_slg_enable_yahoo'] ) 	  || !empty( $edd_options['edd_slg_enable_windowslive'] ) || 
			!empty( $edd_options['edd_slg_enable_vk'] ) 	  || !empty( $edd_options['edd_slg_enable_instagram'] ) ||
			!empty( $edd_options['edd_slg_enable_amazon'] ) 	  || !empty( $edd_options['edd_slg_enable_paypal]'] )) {			
			$return = true;
		}
		
		return apply_filters( 'edd_slg_check_social_enable', $return );
	}
	
	/**
	 * Google Redirect URL
	 * 
	 * Handle to display google redirect url description in settings
	 * 
	 * @package Easy Digital Downloads - Social Login
	 * @since 1.0.0
	 */
	function edd_gp_redirect_url_callback( $args ) {
		
		echo '<code><strong>' . EDD_SLG_GP_REDIRECT_URL . '</strong></code>';
	}
	
	/**
	 * Linkedin Redirect URL
	 * 
	 * Handle to display linkedin redirect url description in settings
	 * 
	 * @package Easy Digital Downloads - Social Login
	 * @since 1.0.0
	 */
	function edd_li_redirect_url_callback( $args ) {
		
		echo '<code><strong>' . EDD_SLG_LI_REDIRECT_URL . '</strong></code>';
	}
	
	/**
	 * Yahoo Redirect URL
	 * 
	 * Handle to display yahoo redirect url description in settings
	 * 
	 * @package Easy Digital Downloads - Social Login
	 * @since 1.0.0
	 */
	function edd_yh_redirect_url_callback( $args ) {
		
		echo '<code><strong>' . EDD_SLG_YH_REDIRECT_URL . '</strong></code>';
	}
	
	/**
	 * Windows Live Redirect URL
	 * 
	 * Handle to display windows live redirect url description in settings
	 * 
	 * @package Easy Digital Downloads - Social Login
	 * @since 1.0.0
	 */
	function edd_wl_redirect_url_callback( $args ) {
		
		echo '<code><strong>' . site_url() . '</strong></code>';
	}
	
	/**
	 * VK Redirect URL
	 * 
	 * Handle to display vk redirect url description in settings
	 * 
	 * @package Easy Digital Downloads - Social Login
	 * @since 1.3.0
	 */
	function edd_vk_redirect_url_callback( $args ) {
		
		echo '<code><strong>' . EDD_SLG_VK_REDIRECT_URL . '</strong></code>';
	}
	
	/**
	 * Instagram Redirect URL
	 * 
	 * Handle to display instagram redirect url description in settings
	 * 
	 * @package Easy Digital Downloads - Social Login
	 * @since 1.5.0
	 */
	function edd_inst_redirect_url_callback( $args ) {
		
		echo '<code><strong>' . EDD_SLG_INST_REDIRECT_URL . '</strong></code>';
	}
	
	/**
	 * Facebook Description
	 * 
	 * Handle to display facebook description in settings
	 * 
	 * @package Easy Digital Downloads - Social Login
	 * @since 1.0.0
	 */
	function edd_facebook_desc_callback( $args ) {?>
		
		<p><?php 
			printf( __( 'Before you can start using Facebook for the social login, you need to create a Facebook Application. You can get a step by step tutorial on how to create Facebook Application on our %sDocumentation%s.' , 'eddslg') , '<a href="http://wpweb.co.in/documents/social-network-integration/facebook/" target="_blank">', '</a>' ); ?>
		</p><?php
	}
	
	
	
	/**
	 * Reset Button
	 * 
	 * Handle to display reset settings button
	 * 
	 * @package Easy Digital Downloads - Social Login
	 * @since 1.5.3
	 */
	function edd_social_reset_callback( $args ) {
		
		global $edd_options;
		
		$button_value		= isset( $args['button'] ) && !empty( $args['button'] ) ? $args['button'] : __( 'Reset Settings', 'eddslg' );
		$social_reset_url	= add_query_arg( array( 'edd_slg_reset' => 'reset_settings' ), get_permalink() );
		
		$html = '';
		$reset_mesassage	= __( 'Are you sure you want to reset social login setting?', 'eddslg' );
		$html .= '<a onclick="return confirm(\''.$reset_mesassage.'\')" href="' . $social_reset_url . '" class="edd-slg-reset-settings ' . $args['size'] . '" id="edd_settings[' . $args['id'] . ']" >' . $button_value .  '</a>';
		$html .= $args['desc'];
		
		echo $html;
	}
	
	/**
	 * Google+ Description
	 * 
	 * Handle to display google+ description in settings
	 * 
	 * @package Easy Digital Downloads - Social Login
	 * @since 1.0.0
	 */
	function edd_googleplus_desc_callback( $args ) { ?>
		
		<p><?php 
			printf( __( 'Before you can start using Google+ for the social login, you need to create a Google+ Application. You can get a step by step tutorial on how to create Google+ Application on our %sDocumentation%s.' , 'eddslg') , '<a href="http://wpweb.co.in/documents/social-network-integration/google/" target="_blank">', '</a>' ); ?>
		</p><?php	
	}
	
	/**
	 * LinkedIn Description
	 * 
	 * Handle to display linkedin description in settings
	 * 
	 * @package Easy Digital Downloads - Social Login
	 * @since 1.0.0
	 */
	function edd_linkedin_desc_callback( $args ) { ?>
		
		<p><?php 
			printf( __( 'Before you can start using LinkedIn for the social login, you need to create a LinkedIn Application. You can get a step by step tutorial on how to create LinkedIn Application on our %sDocumentation%s.' , 'eddslg') , '<a href="http://wpweb.co.in/documents/social-network-integration/linkedin/" target="_blank">', '</a>' ); ?>
		</p><?php
	}
	
	/**
	 * Twitter Description
	 * 
	 * Handle to display twitter description in settings
	 * 
	 * @package Easy Digital Downloads - Social Login
	 * @since 1.0.0
	 */
	function edd_twitter_desc_callback( $args ) { ?>
		
		<p><?php 
			printf( __( 'Before you can start using Twitter for the social login, you need to create a Twitter Application. You can get a step by step tutorial on how to create Twitter Application on our %sDocumentation%s.' , 'eddslg') , '<a href="http://wpweb.co.in/documents/social-network-integration/twitter/" target="_blank">', '</a>' ); ?>
		</p><?php
	}
	
	/**
	 * Yahoo Description
	 * 
	 * Handle to display yahoo description in settings
	 * 
	 * @package Easy Digital Downloads - Social Login
	 * @since 1.0.0
	 */
	function edd_yahoo_desc_callback( $args ) { ?>
		
		<p><?php 
			printf( __( 'Before you can start using Yahoo for the social login, you need to create a Yahoo Application. You can get a step by step tutorial on how to create Yahoo Application on our %sDocumentation%s.' , 'eddslg') , '<a href="http://wpweb.co.in/documents/social-network-integration/yahoo/" target="_blank">', '</a>' ); ?>
		</p><?php
	}
	
	/**
	 * Foursquare Description
	 * 
	 * Handle to display foursquare description in settings
	 * 
	 * @package Easy Digital Downloads - Social Login
	 * @since 1.0.0
	 */
	function edd_foursquare_desc_callback( $args ) { ?>
		<p><?php 
			printf( __( 'Before you can start using Foursquare for the social login, you need to create a Foursquare Application. You can get a step by step tutorial on how to create Foursquare Application on our %sDocumentation%s.' , 'eddslg') , '<a href="http://wpweb.co.in/documents/social-network-integration/foursquare/" target="_blank">', '</a>' ); ?>
		</p><?php
	}
	
	/**
	 * Windows Live Description
	 * 
	 * Handle to display windowslive description in settings
	 * 
	 * @package Easy Digital Downloads - Social Login
	 * @since 1.0.0
	 */
	function edd_windowslive_desc_callback( $args ) { ?>
		
		<p><?php 
			printf( __( 'Before you can start using Windows Live for the social login, you need to create a Windows Live Application. You can get a step by step tutorial on how to create Windows Live Application on our %sDocumentation%s.' , 'eddslg') , '<a href="http://wpweb.co.in/documents/social-network-integration/windows_live/" target="_blank">', '</a>' ); ?>
		</p><?php
	}
	
	/**
	 * VK Description
	 * 
	 * Handle to display vk description in settings
	 * 
	 * @package Easy Digital Downloads - Social Login
	 * @since 1.3.0
	 */
	function edd_vk_desc_callback( $args ) { ?>
		
		<p><?php 
			printf( __( 'Before you can start using VK for the social login, you need to create a VK Application. You can get a step by step tutorial on how to create VK Application on our %sDocumentation%s.' , 'eddslg') , '<a href="http://wpweb.co.in/documents/social-network-integration/vk/" target="_blank">', '</a>' ); ?>
		</p><?php
	}
	
	/**
	 * Instagram Description
	 * 
	 * Handle to display instagram description in settings
	 *
	 * @package Easy Digital Downloads - Social Login
	 * @since 1.5.0
	 */
	function edd_instagram_desc_callback( $args ) { ?>
		
		<p><?php 
			printf( __( 'Before you can start using Instagram for the social login, you need to create a Instagram Application. You can get a step by step tutorial on how to create Instagram Application on our %sDocumentation%s.' , 'eddslg') , '<a href="http://wpweb.co.in/documents/social-network-integration/instagram/" target="_blank">', '</a>' ); ?>
		</p><?php
	}
	
	/**
	 * Amazon Description
	 * 
	 * Handle to display amazon description in settings
	 * 
	 * @package Easy Digital Downloads - Social Login
	 * @since 1.7.0
	 */
	function edd_amazon_desc_callback( $args ) {?>
		
		<p><?php 
			printf( __( 'Before you can start using Amazon for the social login, you need to create a Amazon Application. You can get a step by step tutorial on how to create Amazon Application on our %sDocumentation%s.' , 'eddslg') , '<a href="http://wpweb.co.in/documents/social-network-integration/amazon/" target="_blank">', '</a>' ); ?>
		</p><?php
	}

	/**
	 * Amazon Redirect URL
	 * 
	 * Handle to display amazon redirect url description in settings
	 * 
	 * @package Easy Digital Downloads - Social Login
	 * @since 1.7.0
	 */
	function edd_amazon_redirect_url_callback( $args ) {
		
		echo '<code><strong>' . EDD_SLG_AMAZON_REDIRECT_URL . '</strong></code>';
	}	
	
	
	/**
	 * Paypal Description
	 * 
	 * Handle to display paypal description in settings
	 * 
	 * @package Easy Digital Downloads - Social Login
	 * @since 1.7.0
	 */
	function edd_paypal_desc_callback( $args ) {?>
		
		<p><?php 
			printf( __( 'Before you can start using Paypal for the social login, you need to create a Paypal Application. You can get a step by step tutorial on how to create Paypal Application on our %sDocumentation%s.' , 'eddslg') , '<a href="http://wpweb.co.in/documents/social-network-integration/paypal/" target="_blank">', '</a>' ); ?>
		</p><?php
	}
	
	
	/**
	 * Amazon Redirect URL
	 * 
	 * Handle to display amazon redirect url description in settings
	 * 
	 * @package Easy Digital Downloads - Social Login
	 * @since 1.7.0
	 */
	function edd_paypal_redirect_url_callback( $args ) {
		
		echo '<code><strong>' . EDD_SLG_PAYPAL_REDIRECT_URL . '</strong></code>';
	}	
	
	/**
	 * Current Page URL
	 * 
	 * @package  Easy Digital Downloads - Social Login
	 * @since 1.0.0
	 */
	function edd_slg_get_current_page_url() {
		
		$curent_page_url = remove_query_arg( array( 'oauth_token', 'oauth_verifier' ), edd_get_current_page_url() );
		return $curent_page_url;
	}

	/**
	 * Get Easy Digital Downloads Screen ID
	 * 
	 * Handles to get edd screen id
	 * 
	 * @package Easy Digital Downloads - Social Login
	 * @since 1.5.5
	 */
	function edd_slg_get_edd_screen_id() {

		$edd_screen_id		= 'download';
		return apply_filters( 'edd_slg_get_edd_screen_id', $edd_screen_id );
	}
	
	
/**
 * Can Show Social Link
 * 
 * Handles to check this social link can show or not
 * 
 * @package Easy Digital Downloads - Social Login
 * @since 1.5.6
 */
function edd_slg_can_show_social_link( $social = '' ) {
	
	global $current_user;
	
	$display = false;
	
	//user id not found
	$user_id		= isset( $current_user->ID ) ? $current_user->ID : '';
	
	if( !empty( $user_id ) ) {
		
		//get primary social api
		$primary_social	= get_user_meta( $user_id, 'edd_slg_social_user_connect_via', true );
		
		//get currunt social api meta
		$social_profile = get_user_meta( $user_id, 'edd_slg_social_' . $social . '_data', true );
		
		// check  current provider is linked or not
		if ( !$social_profile && $primary_social != $social ) {
			
			$display = true;
		}
	}
	
	return apply_filters( 'edd_slg_can_display_social_link', $display, $social );
}

/**
 * Can Show Social Link Container
 * 
 * Handles to check this social link can show or not
 * 
 * @package Easy Digital Download - Social Login
 * @since 1.5.6
 */
function edd_slg_can_show_all_social_link_container() {
	
	global $current_user, $edd_options;
	
	$display = false;
	
	//user id not found
	$user_id		= isset( $current_user->ID ) ? $current_user->ID : '';
	
	if( !empty( $user_id ) ) { // if user is not empty
		
		//get all social api in order
		$edd_social_order = get_option( 'edd_social_order' );
		
		if( !empty( $edd_social_order ) ) {
			
			//profile already linked as primary account
			$primary_social		= get_user_meta( $user_id, 'edd_slg_social_user_connect_via', true );
			
			foreach ( $edd_social_order as $social ) {
				
				//profile already linked as secondary account
				$social_profile = get_user_meta( $user_id, 'edd_slg_social_' . $social . '_data', true );
				
				//if enable social account
				$enable_social	= ( !empty( $edd_options['edd_slg_enable_' . $social ] ) ) ? true : false;
				
				if ( !$social_profile && $primary_social != $social && $enable_social ) {					
					$display = true;
					break;
				}
			}
		}
	}
	
	return apply_filters( 'edd_slg_can_show_all_social_link_container', $display );
}

/**
 * Update Last Login Social Account
 * 
 * Handles to update last login social account
 * 
 * @package Easy Digital Download - Social Login
 * @since 1.5.6
 */
function edd_slg_update_social_last_login_timestamp( $user_id, $social_type ) {

	if( !empty( $user_id ) && !empty( $social_type ) ) { // if user id and social type is not empty

		//get primary account
		$primary_social	= get_user_meta( $user_id, 'edd_slg_social_user_connect_via', true );

		$timestamp		= current_time( 'timestamp' );
		$timestamp_gmt	= time();

		if( $primary_social == $social_type ) { // if $social_type is primary account

			update_user_meta( $user_id, 'edd_slg_social_login_timestamp', $timestamp );
			update_user_meta( $user_id, 'edd_slg_social_login_timestamp_gmt', $timestamp_gmt );
		} else { // If $social_type is secondary account

			update_user_meta( $user_id, 'edd_slg_social_' . $social_type . '_login_timestamp', $timestamp );
			update_user_meta( $user_id, 'edd_slg_social_' . $social_type . '_login_timestamp_gmt', $timestamp_gmt );
		}
	}
}

/**
 * Get Last Login Social Account
 * 
 * Handles to get last login social account
 * 
 * @package Easy Digital Download - Social Login
 * @since 1.5.6
 */
function edd_slg_get_social_last_login_timestamp( $user_id, $social_type, $is_gmt = false ) {

	$social_login_timestamp	= array( 'timestamp' => '', 'timestamp_gmt' => '' );

	if( !empty( $user_id ) && !empty( $social_type ) ) { // if user id and social type is not empty

		//get primary account
		$primary_social	= get_user_meta( $user_id, 'edd_slg_social_user_connect_via', true );

		if( $primary_social == $social_type ) { // if $social_type is primary account

			$social_login_timestamp['timestamp']	= get_user_meta( $user_id, 'edd_slg_social_login_timestamp', true );
			$social_login_timestamp['timestamp_gmt']= get_user_meta( $user_id, 'edd_slg_social_login_timestamp_gmt', true );

		} else { // If $social_type is secondary account

			$social_login_timestamp['timestamp']	= get_user_meta( $user_id, 'edd_slg_social_' . $social_type . '_login_timestamp', true );
			$social_login_timestamp['timestamp_gmt']= get_user_meta( $user_id, 'edd_slg_social_' . $social_type . '_login_timestamp_gmt', true );

		}
	}

	$login_timestamp	= ( $is_gmt ) ? $social_login_timestamp['timestamp_gmt'] : $social_login_timestamp['timestamp'];

	return apply_filters( 'edd_slg_get_social_last_login_timestamp', $login_timestamp, $user_id, $social_type, $is_gmt );
}

/**
 * Social Login Messages
 * 
 * Handles to change social login mesages
 * and links displayed at front side
 * 
 @package Easy Digital Download - Social Login
 * @since 1.5.6
 */
function edd_slg_messages() {
	
	return apply_filters( 'edd_slg_messages', array(
								'connected_link_heading'	=> __( 'Your account is connected to the following social login providers.','eddslg' ),
								'no_social_connected'		=> __( 'You have no social login profiles connected.','eddslg' ),
								'add_more_link'				=> __( 'Add More...', 'eddslg' ),
								'connect_now_link'			=> __( 'Connect one now', 'eddslg' ),
								'account_unlinked_notice'	=> __( '%s account was successfully unlinked from your account.', 'eddslg' ),
								'already_linked_error'		=> __( 'This account is already linked with another account.', 'eddslg' ),
								'account_exist_error'		=> __( 'This account is already exist', 'eddslg' ),
								'fberrormsg'				=> __( 'Please enter Facebook API Key & Secret in settings page.', 'eddslg' ),
								'gperrormsg'				=> __( 'Please enter Google+ Client ID & Secret in settings page.', 'eddslg' ),
								'lierrormsg'				=> __( 'Please enter LinkedIn API Key & Secret in settings page.', 'eddslg' ),
								'twerrormsg'				=> __( 'Please enter Twitter Consumer Key & Secret in settings page.', 'eddslg' ),
								'yherrormsg'				=> __( 'Please enter Yahoo API Consumer Key, Secret & App Id in settings page.', 'eddslg' ),
								'fserrormsg'				=> __( 'Please enter Foursquare API Client ID & Secret in settings page.', 'eddslg' ),
								'wlerrormsg'				=> __( 'Please enter Windows Live API Client ID & Secret in settings page.', 'eddslg' ),
								'vkerrormsg'				=> __( 'Please enter VK API Client ID & Secret in settings page.', 'eddslg' ),
								'insterrormsg'				=> __( 'Please enter Instagram API Client ID & Secret in settings page.', 'eddslg' ),
								'amazonerrormsg'			=> __( 'Please enter Amazon API Client ID & Secret in settings page.', 'eddslg' ),
								'paypalerrormsg'			=> __( 'Please enter Paypal API Client ID & Secret in settings page.', 'eddslg' ),
							));
}

/**
 * Social link buttons
 * 
 * @package  Easy Digital Download - Social Login
 * @since 1.5.6
 */
function edd_slg_link_buttons( $redirect_url = '' ) {
	
	global $edd_options;
	
	$can_show_container = edd_slg_can_show_all_social_link_container();
	
	$link_button_html	= '';
	
	if( $can_show_container ) { // can show container
		
		// get redirect url from settings
		$link_redirect_url = isset( $edd_options['edd_slg_redirect_url'] ) ? $edd_options['edd_slg_redirect_url'] : '';
		$link_redirect_url = !empty( $redirect_url ) ? $redirect_url : $link_redirect_url; // check redirect url first from shortcode or if checkout page then use cuurent page is redirect url
		
		ob_start(); ?>
		<p><?php echo __( 'You can link your account to the following providers:', 'eddslg' );?></p>
		<fieldset class="edd-slg-social-container edd-slg-social-wrap edd-slg-social-container-checkout edd-social-link-buttons">
			<input type="hidden" class="edd-slg-redirect-url" id="edd_slg_redirect_url" value="<?php echo $link_redirect_url;?>" />
			<!-- Display buttons which are not linked--><?php 
			
			do_action ( 'edd_slg_checkout_social_login_link' );?>
			<div class="edd-slg-login-error"></div>
		</fieldset><?php
		
		$link_button_html .= ob_get_clean();
	}
	
	echo apply_filters( 'edd_slg_link_buttons', $link_button_html );
	wp_enqueue_script( 'edd-slg-public-script' );
}

/**
 * Display Or Not On Thankyou Page
 * 
 * Handles to check whether it display on thankyou page or not
 * 
 * @package Easy Digital Download - Social Login
 * @since 1.5.6
 */
function edd_slg_link_display_on_thankyou_page() {

	global $edd_options;

	$enable	= false;

	$link_on_thankyou_page = isset( $edd_options['edd_slg_display_link_thank_you'] ) ? $edd_options['edd_slg_display_link_thank_you'] : '';

	if( isset($link_on_thankyou_page) && !empty($link_on_thankyou_page)) {
		$enable = true;
	}

	return apply_filters( 'edd_slg_link_display_on_thankyou_page', $enable );
}

/**
 * Display Link Buttons On MyAccount
 * 
 * Handles to display link buttons on my account page
 * 
 * @package Easy Digital Download - Social Login
 * @since 1.5.6
 */
function edd_slg_login_display_on_myaccount_page() {

	global $edd_options;

	$enable	= false;
	$login_on_myaccount_page = isset( $edd_options['edd_slg_enable_login_page'] ) ? $edd_options['edd_slg_enable_login_page'] : '';

	if( isset($login_on_myaccount_page) && !empty($login_on_myaccount_page) ) {
		$enable = true;
	}

	return apply_filters( 'edd_slg_login_display_on_myaccount_page', $enable );
}

/**
 * Display custom messages 
 * 
 * @package Easy Digital Download - Social Login
 * @since 1.5.6
 */
function edd_slg_success_message( $message, $display = true ) {
	
	$message_text	= '';
	
	$message_text .= '<div class="edd_success edd-alert edd-alert-success"><p id="edd_slg_success"><strong>'.__( 'Success', 'eddslg' ).'</strong> : ';
	$message_text .= $message;
	$message_text .= '</p></div>';
	
	$message_text = apply_filters( 'edd_slg_success_message', $message_text );
	
	if( $display ) {
		echo $message_text;
	} else {
		return $message_text;
	}
}

/**
 * Insert value in array
 * 
 * Handles to add row in some array after some key
 * 
 * @package Easy Digital Download - Social Login
 * @since 1.7.6
 */
function edd_slg_insert_array_after ( $array, $insert_key, $element ) {
		
	$new_array = array();

	foreach ( $array as $key => $value ) {
	
		$new_array[ $key ] = $value;
	
		if ( $insert_key == $key ) {
	
			foreach ( $element as $k => $v ) {
				$new_array[ $k ] = $v;
			}
		}
	}
	
	return $new_array;
}