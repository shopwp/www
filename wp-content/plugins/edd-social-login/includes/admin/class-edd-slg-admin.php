<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Admin Class
 * 
 * Handles generic Admin functionality and AJAX requests.
 * 
 * @package Easy Digital Downloads - Social Login
 * @since 1.0.0
 */
class EDD_Slg_Admin {
	
	var $model, $scripts, $render;
	
	public function __construct() {
		
		global $edd_slg_model, $edd_slg_scripts, $edd_slg_render;
		
		$this->model	= $edd_slg_model;
		$this->scripts	= $edd_slg_scripts;
		$this->render	= $edd_slg_render;
	}
	
	/**
	 * Register All need admin menu page
	 * 
	 * @package Easy Digital Downloads - Social Login
	 * @since 1.0.0
	 */
	public function edd_slg_admin_menu_pages(){
		
		$edd_slg_social_login = add_submenu_page( 'edit.php?post_type=download', __( 'Easy Digital Download Social Login', 'eddslg' ), __( 'Social Login', 'eddslg' ), 'manage_shop_settings', 'edd-social-login', array( $this, 'edd_slg_social_login' ));
		//script for social login page
		
	}
	
	/**
	 * Add Social Login Page
	 * 
	 * Handles to load social login
	 * page to show social login register data
	 * 
	 * @package Easy Digital Downloads - Social Login
	 * @since 1.0.0
	 */
	public function edd_slg_social_login() {
		
		include_once( EDD_SLG_ADMIN . '/forms/edd-social-login-data.php' );
		
	}
	
	/**
	 * Pop Up On Editor
	 * 
	 * Includes the pop up on the WordPress editor
	 * 
	 * @package Easy Digital Downloads - Social Login
	 * @since 1.1.1
	 */
	public function edd_slg_shortcode_popup() {
		
		include_once( EDD_SLG_ADMIN . '/forms/edd-slg-admin-popup.php' );
	}
	
	/**
	 * Validate Settings
	 * 
	 * Handles to validate settings
	 * 
	 * @package Easy Digital Downloads - Social Login
	 * @since 1.1.1
	 */
	function edd_slg_settings_validate( $input ) {
		
		// General Settings
		$input['edd_slg_login_heading'] 	= $this->model->edd_slg_escape_slashes_deep( trim($input['edd_slg_login_heading']) );
		$input['edd_slg_redirect_url'] 		= $this->model->edd_slg_escape_slashes_deep( trim($input['edd_slg_redirect_url']) );
		
		// Facebook Settings
		$input['edd_slg_fb_app_id'] 		= $this->model->edd_slg_escape_slashes_deep( trim($input['edd_slg_fb_app_id']) );
		$input['edd_slg_fb_app_secret'] 	= $this->model->edd_slg_escape_slashes_deep( trim($input['edd_slg_fb_app_secret']) );
		$input['edd_slg_fb_icon_url'] 		= $this->model->edd_slg_escape_slashes_deep( trim($input['edd_slg_fb_icon_url']) );
		$input['edd_slg_fb_link_icon_url'] 	= $this->model->edd_slg_escape_slashes_deep( trim($input['edd_slg_fb_link_icon_url']) );
		
		// Google+ Settings 
		$input['edd_slg_gp_client_id'] 		= $this->model->edd_slg_escape_slashes_deep( trim($input['edd_slg_gp_client_id']) );
		$input['edd_slg_gp_client_secret'] 	= $this->model->edd_slg_escape_slashes_deep( trim($input['edd_slg_gp_client_secret']) );
		$input['edd_slg_gp_icon_url'] 		= $this->model->edd_slg_escape_slashes_deep( trim($input['edd_slg_gp_icon_url']) );
		$input['edd_slg_gp_link_icon_url'] 		= $this->model->edd_slg_escape_slashes_deep( trim($input['edd_slg_gp_link_icon_url']) );
		
		// LinkedIn Settings
		$input['edd_slg_li_app_id'] 		= $this->model->edd_slg_escape_slashes_deep( trim($input['edd_slg_li_app_id']) );
		$input['edd_slg_li_app_secret'] 	= $this->model->edd_slg_escape_slashes_deep( trim($input['edd_slg_li_app_secret']) );
		$input['edd_slg_li_icon_url'] 		= $this->model->edd_slg_escape_slashes_deep( trim($input['edd_slg_li_icon_url']) );
		$input['edd_slg_li_link_icon_url'] 	= $this->model->edd_slg_escape_slashes_deep( trim($input['edd_slg_li_link_icon_url']) );
		
		// Twitter Settings
		$input['edd_slg_tw_consumer_key'] 	= $this->model->edd_slg_escape_slashes_deep( trim($input['edd_slg_tw_consumer_key']) );
		$input['edd_slg_tw_consumer_secret']= $this->model->edd_slg_escape_slashes_deep( trim($input['edd_slg_tw_consumer_secret']) );
		$input['edd_slg_tw_icon_url'] 		= $this->model->edd_slg_escape_slashes_deep( trim($input['edd_slg_tw_icon_url']) );
		$input['edd_slg_tw_link_icon_url'] 		= $this->model->edd_slg_escape_slashes_deep( trim($input['edd_slg_tw_link_icon_url']) );
		
		// Yahoo Settings
		$input['edd_slg_yh_consumer_key'] 	= $this->model->edd_slg_escape_slashes_deep( trim($input['edd_slg_yh_consumer_key']) );
		$input['edd_slg_yh_consumer_secret']= $this->model->edd_slg_escape_slashes_deep( trim($input['edd_slg_yh_consumer_secret']) );
		$input['edd_slg_yh_app_id'] 		= $this->model->edd_slg_escape_slashes_deep( trim($input['edd_slg_yh_app_id']) );
		$input['edd_slg_yh_icon_url'] 		= $this->model->edd_slg_escape_slashes_deep( trim($input['edd_slg_yh_icon_url']) );
		$input['edd_slg_yh_link_icon_url'] 		= $this->model->edd_slg_escape_slashes_deep( trim($input['edd_slg_yh_link_icon_url']) );
		
		// Foursquare Settings
		$input['edd_slg_fs_client_id'] 		= $this->model->edd_slg_escape_slashes_deep( trim($input['edd_slg_fs_client_id']) );
		$input['edd_slg_fs_client_secret'] 	= $this->model->edd_slg_escape_slashes_deep( trim($input['edd_slg_fs_client_secret']) );
		$input['edd_slg_fs_icon_url'] 		= $this->model->edd_slg_escape_slashes_deep( trim($input['edd_slg_fs_icon_url']) );
		$input['edd_slg_fs_link_icon_url'] 		= $this->model->edd_slg_escape_slashes_deep( trim($input['edd_slg_fs_link_icon_url']) );
		
		// Windows Live Settings
		$input['edd_slg_wl_client_id'] 		= $this->model->edd_slg_escape_slashes_deep( trim($input['edd_slg_wl_client_id']) );
		$input['edd_slg_wl_client_secret'] 	= $this->model->edd_slg_escape_slashes_deep( trim($input['edd_slg_wl_client_secret']) );
		$input['edd_slg_wl_icon_url'] 		= $this->model->edd_slg_escape_slashes_deep( trim($input['edd_slg_wl_icon_url']) );
		$input['edd_slg_wl_link_icon_url'] 		= $this->model->edd_slg_escape_slashes_deep( trim($input['edd_slg_wl_link_icon_url']) );
		
		// VK Settings
		$input['edd_slg_vk_app_id'] 		= $this->model->edd_slg_escape_slashes_deep( trim($input['edd_slg_vk_app_id']) );
		$input['edd_slg_vk_app_secret'] 	= $this->model->edd_slg_escape_slashes_deep( trim($input['edd_slg_vk_app_secret']) );
		$input['edd_slg_vk_icon_url'] 		= $this->model->edd_slg_escape_slashes_deep( trim($input['edd_slg_vk_icon_url']) );
		$input['edd_slg_vk_link_icon_url'] 		= $this->model->edd_slg_escape_slashes_deep( trim($input['edd_slg_vk_link_icon_url']) );
		
		// Instagram Settings
		$input['edd_slg_inst_app_id'] 		= $this->model->edd_slg_escape_slashes_deep( trim($input['edd_slg_inst_app_id']) );
		$input['edd_slg_inst_app_secret'] 	= $this->model->edd_slg_escape_slashes_deep( trim($input['edd_slg_inst_app_secret']) );
		$input['edd_slg_inst_icon_url'] 	= $this->model->edd_slg_escape_slashes_deep( trim($input['edd_slg_inst_icon_url']) );
		$input['edd_slg_inst_link_icon_url'] 	= $this->model->edd_slg_escape_slashes_deep( trim($input['edd_slg_inst_link_icon_url']) );
		
		// Amazon Settings
		$input['edd_slg_amazon_app_id'] 		= $this->model->edd_slg_escape_slashes_deep( trim($input['edd_slg_amazon_app_id']) );
		$input['edd_slg_amazon_app_secret'] 	= $this->model->edd_slg_escape_slashes_deep( trim($input['edd_slg_amazon_app_secret']) );
		$input['edd_slg_amazon_icon_url'] 	    = $this->model->edd_slg_escape_slashes_deep( trim($input['edd_slg_amazon_icon_url']) );
		$input['edd_slg_amazon_link_icon_url'] 	= $this->model->edd_slg_escape_slashes_deep( trim($input['edd_slg_amazon_link_icon_url']) );
		
		
		// Amazon Settings
		$input['edd_slg_paypal_app_id'] 		= $this->model->edd_slg_escape_slashes_deep( trim($input['edd_slg_paypal_app_id']) );
		$input['edd_slg_paypal_app_secret'] 	= $this->model->edd_slg_escape_slashes_deep( trim($input['edd_slg_paypal_app_secret']) );
		$input['edd_slg_paypal_icon_url'] 	    = $this->model->edd_slg_escape_slashes_deep( trim($input['edd_slg_paypal_icon_url']) );
		$input['edd_slg_paypal_link_icon_url'] 	= $this->model->edd_slg_escape_slashes_deep( trim($input['edd_slg_paypal_link_icon_url']) );
		$input['edd_slg_paypal_environment'] 	= $this->model->edd_slg_escape_slashes_deep( trim($input['edd_slg_paypal_environment']) );
		
		// Checkbox Settings
		$input['edd_slg_enable_notification'] = ( isset( $input['edd_slg_enable_notification'] ) && $input['edd_slg_enable_notification'] == 1 ) ? 1 : 0;
		$input['edd_slg_enable_facebook']	  = ( isset( $input['edd_slg_enable_facebook'] ) && $input['edd_slg_enable_facebook'] == 1  ) ? 1 : 0;
		$input['edd_slg_enable_fb_avatar']	  = ( isset( $input['edd_slg_enable_fb_avatar'] ) && $input['edd_slg_enable_fb_avatar'] == 1  ) ? 1 : 0;
		$input['edd_slg_enable_googleplus']	  = ( isset( $input['edd_slg_enable_googleplus'] ) && $input['edd_slg_enable_googleplus'] == 1  ) ? 1 : 0;
		$input['edd_slg_enable_gp_avatar']	  = ( isset( $input['edd_slg_enable_gp_avatar'] ) && $input['edd_slg_enable_gp_avatar'] == 1  ) ? 1 : 0;
		$input['edd_slg_enable_linkedin']	  = ( isset( $input['edd_slg_enable_linkedin'] ) && $input['edd_slg_enable_linkedin'] == 1 ) ? 1 : 0;
		$input['edd_slg_enable_li_avatar']	  = ( isset( $input['edd_slg_enable_li_avatar'] ) && $input['edd_slg_enable_li_avatar'] == 1 ) ? 1 : 0;
		$input['edd_slg_enable_twitter']	  = ( isset( $input['edd_slg_enable_twitter'] ) && $input['edd_slg_enable_twitter'] == 1 ) ? 1 : 0;
		$input['edd_slg_enable_tw_avatar']	  = ( isset( $input['edd_slg_enable_tw_avatar'] ) && $input['edd_slg_enable_tw_avatar'] == 1 ) ? 1 : 0;
		$input['edd_slg_enable_yahoo']	      = ( isset( $input['edd_slg_enable_yahoo'] ) && $input['edd_slg_enable_yahoo'] == 1 ) ? 1 : 0;
		$input['edd_slg_enable_yh_avatar']	  = ( isset( $input['edd_slg_enable_yh_avatar'] ) && $input['edd_slg_enable_yh_avatar'] == 1 ) ? 1 : 0;
		$input['edd_slg_enable_foursquare']	  = ( isset( $input['edd_slg_enable_foursquare'] ) && $input['edd_slg_enable_foursquare'] == 1 ) ? 1 : 0;
		$input['edd_slg_enable_fs_avatar']	  = ( isset( $input['edd_slg_enable_fs_avatar'] ) && $input['edd_slg_enable_fs_avatar'] == 1 ) ? 1 : 0;
		$input['edd_slg_enable_windowslive']  = ( isset( $input['edd_slg_enable_windowslive'] ) && $input['edd_slg_enable_windowslive'] == 1 ) ? 1 : 0;
		$input['edd_slg_enable_vk']	          = ( isset( $input['edd_slg_enable_vk'] ) && $input['edd_slg_enable_vk'] == 1 ) ? 1 : 0;
		$input['edd_slg_enable_vk_avatar']	  = ( isset( $input['edd_slg_enable_vk_avatar'] ) && $input['edd_slg_enable_vk_avatar'] == 1 ) ? 1 : 0;
		$input['edd_slg_enable_instagram']	  = ( isset( $input['edd_slg_enable_instagram'] ) && $input['edd_slg_enable_instagram'] == 1 ) ? 1 : 0;
		$input['edd_slg_enable_inst_avatar']  = ( isset( $input['edd_slg_enable_inst_avatar'] ) && $input['edd_slg_enable_inst_avatar'] == 1 ) ? 1 : 0;
		$input['edd_slg_enable_amazon']	      = ( isset( $input['edd_slg_enable_amazon'] ) && $input['edd_slg_enable_amazon'] == 1 ) ? 1 : 0;
		$input['edd_slg_enable_paypal']	      = ( isset( $input['edd_slg_enable_paypal'] ) && $input['edd_slg_enable_paypal'] == 1 ) ? 1 : 0;
		
		return $input;
	}
	
	/**
	 * Reset Social Settings
	 * 
	 * Handle to reset social settings
	 * 
	 * @package Easy Digital Downloads - Social Login
	 * @since 1.5.3
	 */
	function edd_slg_reset_social_settings() {
		
		if( isset( $_GET['edd_slg_reset'] ) && $_GET['edd_slg_reset'] == 'reset_settings'
			&& isset( $_GET['page'] ) && $_GET['page'] == 'edd-settings' ) {
				
				global $edd_options;
				
				$edd_options['edd_slg_login_heading'] =  __( 'Prefer to Login with Social Media', 'eddslg' );
				$edd_options['edd_slg_enable_notification'] =  '';
				$edd_options['edd_slg_redirect_url'] =  '';
				$edd_options['edd_slg_enable_login_page']='';
				$edd_options['edd_slg_display_link_thank_you']='';
				$edd_options['edd_slg_enable_facebook'] =  '';
				$edd_options['edd_slg_fb_app_id'] =  '';
				$edd_options['edd_slg_fb_app_secret'] =  '';
				$edd_options['edd_slg_fb_language'] =  'en_US';
				$edd_options['edd_slg_fb_icon_url'] =  EDD_SLG_IMG_URL . '/facebook.png';
				$edd_options['edd_slg_fb_link_icon_url'] = EDD_SLG_IMG_URL . '/facebook-link.png';
				$edd_options['edd_slg_enable_fb_avatar'] =  '';
				$edd_options['edd_slg_enable_googleplus'] =  '';
				$edd_options['edd_slg_gp_client_id'] =  '';
				$edd_options['edd_slg_gp_client_secret'] =  '';
				$edd_options['edd_slg_gp_icon_url'] =  EDD_SLG_IMG_URL . '/googleplus.png';
				$edd_options['edd_slg_gp_link_icon_url'] =  EDD_SLG_IMG_URL . '/googleplus-link.png';				
				$edd_options['edd_slg_enable_gp_avatar'] =  '';
				$edd_options['edd_slg_enable_linkedin'] =  '';
				$edd_options['edd_slg_li_app_id'] =  '';
				$edd_options['edd_slg_li_app_secret'] =  '';
				$edd_options['edd_slg_li_icon_url'] =  EDD_SLG_IMG_URL . '/linkedin.png';
				$edd_options['edd_slg_li_link_icon_url'] =  EDD_SLG_IMG_URL . '/linkedin-link.png';
				$edd_options['edd_slg_enable_li_avatar'] =  '';
				$edd_options['edd_slg_enable_twitter'] =  '';
				$edd_options['edd_slg_tw_consumer_key'] =  '';
				$edd_options['edd_slg_tw_consumer_secret'] =  '';
				$edd_options['edd_slg_tw_icon_url'] =  EDD_SLG_IMG_URL . '/twitter.png';
				$edd_options['edd_slg_tw_link_icon_url'] =  EDD_SLG_IMG_URL . '/twitter-link.png';
				$edd_options['edd_slg_enable_tw_avatar'] =  '';
				$edd_options['edd_slg_enable_yahoo'] =  '';
				$edd_options['edd_slg_yh_consumer_key'] =  '';
				$edd_options['edd_slg_yh_consumer_secret'] =  '';
				$edd_options['edd_slg_yh_app_id'] =  '';
				$edd_options['edd_slg_yh_icon_url'] =  EDD_SLG_IMG_URL . '/yahoo.png';
				$edd_options['edd_slg_yh_link_icon_url'] =  EDD_SLG_IMG_URL . '/yahoo-link.png';
				$edd_options['edd_slg_enable_yh_avatar'] =  '';
				$edd_options['edd_slg_enable_foursquare'] =  '';
				$edd_options['edd_slg_fs_client_id'] =  '';
				$edd_options['edd_slg_fs_client_secret'] =  '';
				$edd_options['edd_slg_fs_icon_url'] =  EDD_SLG_IMG_URL . '/foursquare.png';
				$edd_options['edd_slg_fs_link_icon_url'] =  EDD_SLG_IMG_URL . '/foursquare-link.png';
				$edd_options['edd_slg_enable_fs_avatar'] =  '';
				$edd_options['edd_slg_enable_windowslive'] =  '';
				$edd_options['edd_slg_wl_client_id'] =  '';
				$edd_options['edd_slg_wl_client_secret'] =  '';
				$edd_options['edd_slg_wl_icon_url'] =  EDD_SLG_IMG_URL . '/windowslive.png';
				$edd_options['edd_slg_wl_link_icon_url'] =  EDD_SLG_IMG_URL . '/windowslive-link.png';
				$edd_options['edd_slg_enable_vk'] =  '';
				$edd_options['edd_slg_vk_app_id'] =  '';
				$edd_options['edd_slg_vk_app_secret'] =  '';
				$edd_options['edd_slg_vk_icon_url'] =  EDD_SLG_IMG_URL . '/vk.png';
				$edd_options['edd_slg_vk_link_icon_url'] =  EDD_SLG_IMG_URL . '/vk-link.png';
				$edd_options['edd_slg_enable_vk_avatar'] =  '';
				$edd_options['edd_slg_enable_instagram'] =  '';
				$edd_options['edd_slg_inst_app_id'] =  '';
				$edd_options['edd_slg_inst_app_secret'] =  '';
				$edd_options['edd_slg_inst_icon_url'] =  EDD_SLG_IMG_URL . '/instagram.png';
				$edd_options['edd_slg_inst_link_icon_url'] =  EDD_SLG_IMG_URL . '/instagram-link.png';
				$edd_options['edd_slg_enable_inst_avatar'] =  '';
				$edd_options['edd_slg_enable_amazon'] =  '';
				$edd_options['edd_slg_amazon_app_id'] =  '';
				$edd_options['edd_slg_amazon_app_secret'] =  '';
				$edd_options['edd_slg_amazon_icon_url'] =  EDD_SLG_IMG_URL . '/amazon.png';
				$edd_options['edd_slg_amazon_link_icon_url'] =  EDD_SLG_IMG_URL . '/amazon-link.png';
				$edd_options['edd_slg_enable_paypal'] =  '';
				$edd_options['edd_slg_paypal_app_id'] =  '';
				$edd_options['edd_slg_paypal_app_secret'] =  '';
				$edd_options['edd_slg_paypal_icon_url'] =  EDD_SLG_IMG_URL . '/paypal.png';
				$edd_options['edd_slg_paypal_link_icon_url'] =  EDD_SLG_IMG_URL . '/paypal-link.png';
				$edd_options['edd_slg_paypal_environment'] =  'sandbox';
				
				update_option( 'edd_settings', $edd_options );
				
				$edd_social_order = array( 'facebook', 'twitter', 'googleplus', 'linkedin', 'yahoo', 'foursquare', 'windowslive', 'vk', 'instagram', 'amazon', 'paypal' );
				update_option( 'edd_social_order', $edd_social_order );
				
				$redirectargs = array( 
									'post_type'			=>	'download', 
									'page'				=>	'edd-settings', 
									'tab'				=>	'extensions', 
									'settings-updated' 	=>	'edd_slg_reset',
									'edd_slg_reset' 	=>	false
								);
				
				$redirect_url = add_query_arg( $redirectargs, admin_url( 'edit.php' ) );
				wp_redirect( $redirect_url );
				exit;
		}
	}
	
	/**
	 * Reset Social Settings
	 * 
	 * Handle to reset social settings
	 * 
	 * @package Easy Digital Downloads - Social Login
	 * @since 1.5.3
	 */
	public function edd_admin_ssl_notice(){
		
		global $edd_options;
		
		$edd_social_order = get_option( 'edd_social_order' );	
		
		foreach ( $edd_social_order as $provider ) {
			
			global ${"edd_slg_social_".$provider};
			
			if( !empty($edd_options['edd_slg_enable_'.$provider])  && isset(${"edd_slg_social_".$provider}->requires_ssl) && ${"edd_slg_social_".$provider}->requires_ssl) { ?>
			<div class="error">
        		<p><?php _e( 'Easy Digital Downloads Social Login : <b>'. $provider .'</b> requires SSL for authentication. ', 'eddslg' ); ?></p>
    		</div>
    
	<?php }
		}	
	}
	
	
	/**
	 * Add 'Social Profiles' column to the Users admin table
	 *
	 * @package Easy Digital Downloads - Social Login
	 * @since 1.7.6
	 */
	public function edd_slg_add_user_columns ( $columns ) {
		
		return edd_slg_insert_array_after( $columns, 'email', array( 'edd_social_login_profiles' => __( 'Primary Social Profile', 'eddslg' ) ) );
	}
	
	/**
	 * Render social profile icons in the 'Social Profiles' column of the Users admin table
	 *
	 * @package Easy Digital Downloads - Social Login
	 * @since 1.7.6
	 */
	public function edd_slg_user_column_values ( $output, $column_name, $user_id ) {
		
		if ( $column_name === 'edd_social_login_profiles' ) {
			
			$edd_user = get_user_by( 'id', $user_id );
			if ( !empty( $user_id ) && !empty( $edd_user ) ){
				
				$edd_user_soc_login_prof = get_user_meta( $user_id, 'edd_slg_social_user_connect_via', true );
				if ( !empty( $edd_user_soc_login_prof ) ) {
					
					$provider	= EDD_SLG_IMG_URL . "/" . $edd_user_soc_login_prof . "-provider.png";
					$output 	.= '<img src="' . $provider . '" >';
				} else {
					$output .= __( 'N/A', 'eddslg');
				}
			}
		}
		
		return $output;
	}
	
	/**
	 * Render social profile icons in the user edit screen
	 *
	 * @package Easy Digital Downloads - Social Login
	 * @since 1.7.6
	 */
	function edd_slg_show_user_profiles( $user ) { 
		
		$user_id = $user->ID;
		$primaryProfile = __( 'N/A', 'eddslg');
		
		$linked_profiles = $this->render->edd_get_user_social_linked_profiles( $user_id );
		
		//get primary social account type if exist
		$primary_social		= get_user_meta( $user_id, 'edd_slg_social_user_connect_via', true );		
		if ( !empty( $primary_social ) ) {					
				$provider	= EDD_SLG_IMG_URL . "/" . $primary_social . "-provider.png";
				$primaryProfile	= '<img src="' . $provider . '" >';
		}		
		?>
		
		<h2><?php _e( 'Social Profiles', 'eddslg' ); ?></h2>
			<table class="form-table">
				<tr>
					<th> <?php _e( 'Primary Social Profile', 'eddslg' ); ?></th>
					<td><?php echo $primaryProfile; ?></td>
				</tr>
				<tr>
					<th> <?php _e( 'Linked Social Profiles', 'eddslg' ); ?></th>
					<td>
					<?php
					$edd_linked_profiles = 0;
					if( !empty( $linked_profiles ) ) {
						
						foreach ( $linked_profiles as $profile => $value ) {
							if( $profile != $primary_social ) {
								$provider		= EDD_SLG_IMG_URL . "/" . $profile . "-provider.png";
								echo '<img src="'.$provider.'" class="edd-slg-linked-provider-image">';
								$edd_linked_profiles++;
							}
						}
					}
					if( $edd_linked_profiles == 0 ) {
						_e( 'N/A', 'eddslg');
					}
					?>
					</td>
				</tr>
			</table>
	<?php
	}
	/**
	 * Adding Hooks
	 * 
	 * @package Easy Digital Downloads - Social Login
	 * @since 1.0.0
	 */
	public function add_hooks() {
		
		//add admin menu pages
		add_action ( 'admin_menu', array($this,'edd_slg_admin_menu_pages' ));
		
		//add filter to add settings
		add_filter( 'edd_settings_extensions', array( $this->model , 'edd_slg_settings') );
		
		//add filter to section setting 
		add_filter( 'edd_settings_sections_extensions', array( $this->model, 'edd_slg_settings_section' ) );
		
		//add filter to add settings
		add_filter( 'edd_settings_extensions-eddslg_sanitize', array( $this, 'edd_slg_settings_validate') );
		
		// mark up for popup
		add_action( 'admin_footer-post.php', array( $this,'edd_slg_shortcode_popup' ) );
		add_action( 'admin_footer-post-new.php', array( $this,'edd_slg_shortcode_popup' ) );
		
		// add action to reset social settings
		add_action( 'admin_init', array( $this, 'edd_slg_reset_social_settings' ) );
		
		if(!is_ssl()){
			add_action( 'admin_notices', array( $this,'edd_admin_ssl_notice' ) ); 
		}
		
		// add social profiles column to the Users admin table
		add_filter( 'manage_users_columns',       array( $this, 'edd_slg_add_user_columns' ), 11 );
		add_filter( 'manage_users_custom_column', array( $this, 'edd_slg_user_column_values' ), 11, 3 );
		
		add_action( 'show_user_profile', array( $this, 'edd_slg_show_user_profiles' ) );
		add_action( 'edit_user_profile', array( $this, 'edd_slg_show_user_profiles' ) );
	}
}