<?php 

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Renderer Class
 *
 * To handles some small HTML content for front end
 * 
 * @package Easy Digital Downloads - Social Login
 * @since 1.0.0
 */
class EDD_Slg_Renderer {

	var $model;
	var $socialfacebook;
	var $socialgoogle;
	var $sociallinkedin;
	var $socialtwitter;
	var $socialfoursquare;
	var $socialyahoo;
	var $socialwindowslive;
	var $socialvk;
	var $socialinstagram;
	var $socialamazon;
	var $socialpaypal;
	
	public function __construct() {
		
		global $edd_slg_model,$edd_slg_social_facebook,$edd_slg_social_google,
			$edd_slg_social_linkedin,$edd_slg_social_twitter,$edd_slg_social_yahoo,
			$edd_slg_social_foursquare,$edd_slg_social_windowslive,$edd_slg_social_vk,$edd_slg_social_instagram,
			$edd_slg_social_amazon, $edd_slg_social_paypal;
		
		$this->model = $edd_slg_model;
		
		//social class objects
		$this->socialfacebook 	= $edd_slg_social_facebook;
		$this->socialgoogle		= $edd_slg_social_google;
		$this->sociallinkedin 	= $edd_slg_social_linkedin;
		$this->socialtwitter 	= $edd_slg_social_twitter;
		$this->socialyahoo		= $edd_slg_social_yahoo;
		$this->socialfoursquare	= $edd_slg_social_foursquare;
		$this->socialwindowslive = $edd_slg_social_windowslive;
		$this->socialvk 		= $edd_slg_social_vk;
		$this->socialinstagram 	= $edd_slg_social_instagram;
		$this->socialamazon 	= $edd_slg_social_amazon;
		$this->socialpaypal 	= $edd_slg_social_paypal;
	}
	
	/**
	 * Show All Social Login Buttons
	 * 
	 * Handles to show all social login buttons
	 * 
	 * @package Easy Digital Downloads - Social Login
	 * @since 1.0.0
	 */
	public function edd_slg_social_login_inner_buttons( $redirect_url = '' ) {
		
		global $edd_options, $post;
		
		// get redirect url from settings
		$login_redirect_url = isset( $edd_options['edd_slg_redirect_url'] ) ? $edd_options['edd_slg_redirect_url'] : '';
		$login_redirect_url = !empty( $redirect_url ) ? $redirect_url : $login_redirect_url; // check redirect url first from shortcode or if checkout page then use cuurent page is redirect url
		
		//load social button
		edd_slg_get_template( 'social-buttons.php' , array( 'login_redirect_url' => $login_redirect_url ) );
		
		//enqueue social front script
		wp_enqueue_script( 'edd-slg-public-script' );
	}
	
	/**
	 * Add Social Login Buttons To 
	 * Checkout page
	 * 
	 * Handles to add all social media login
	 * buttons to edd checkout page
	 * 
	 * @package Easy Digital Downloads - Social Login
	 * @since 1.0.0
	 */
	public function edd_slg_social_login_buttons( $title = '', $redirect_url = '' ) {
		
		global $edd_options, $post;
		
		//check user is logged in to site or not and any single social login button is enable or not
		if( !is_user_logged_in() && edd_slg_check_social_enable() ) {
			$this->edd_slg_social_login();			
		}
	}
	
	/**
	 * Add Social Login Buttons To 
	 * Login page
	 * 
	 * @package Easy Digital Downloads - Social Login
	 * @since 1.5.6
	 */
	public function edd_slg_social_login() {
		
		global $edd_options, $post;
		
		// get title from settings
		$login_heading = isset( $edd_options['edd_slg_login_heading'] ) ? $edd_options['edd_slg_login_heading'] : __( 'Prefer to Login with Social Media', 'eddslg' );
		
		$defaulturl = get_permalink( $post->ID );

		//session create for redirect url 
		EDD()->session->set( 'edd_slg_stcd_redirect_url', $defaulturl );
		
		//load social button wrapper for checkout page
		edd_slg_get_template( 'checkout-social-wrapper.php', array( 'login_heading' => $login_heading ) );
	}
	
	/**
	 * Add Social Login Buttons To
	 * Login page
	 * 
	 * Handles to add all social media login
	 * buttons to Login page
	 * 
	 * @package  Easy Digital Downloads - Social Login
	 * @since 1.0.1
	 */
	public function edd_slg_social_login_buttons_on_login() {

		global $edd_options, $post;

		//check user is logged in to site or not and any single social login button is enable or not
		if( !is_user_logged_in() && edd_slg_check_social_enable() ) {

			// get title from settings
			$login_heading = isset( $edd_options['edd_slg_login_heading'] ) ? $edd_options['edd_slg_login_heading'] : '';

			$redirect_url = isset( $edd_options['edd_slg_redirect_url'] ) && !empty( $edd_options['edd_slg_redirect_url'] ) 
								? $edd_options['edd_slg_redirect_url'] : site_url();

			//session create for redirect url
			$_SESSION['edd_slg_stcd_redirect_url'] = $redirect_url;

			echo '<div id="edd-slg-social-container-login" class="edd-slg-social-container' . '">';

			if( !empty($login_heading) ) {
				echo '<span><legend>' . $login_heading . '</legend></span>';
			}
			$this->edd_slg_social_login_inner_buttons( $redirect_url );

			echo '</div><!--.edd-slg-widget-content-->';
		}
	}
	
	/**
	 * Get list of linked profile 
	 * when user login
	 * @package Easy Digital Downloads - Social Login
	 * @since 1.5.6
	 */
	public function edd_slg_social_profile() {
		
		if( is_user_logged_in() ) {
			
			$user_id = get_current_user_id();

			// get primary social account type if exist
			$primary_social	= get_user_meta( $user_id, 'edd_slg_social_user_connect_via', true );

			$message = edd_slg_messages();
			
			edd_slg_get_template( 'social-profile-list.php',array(
				'linked_profiles'     => $this->edd_get_user_social_linked_profiles(),
				'primary_social'		=> $primary_social,
				'user_id'				=> $user_id,
				'can_link'				=> edd_slg_can_show_all_social_link_container(),
				'add_more_link'			=> isset( $message['add_more_link'] ) ? $message['add_more_link'] : '',
				'connected_link_heading'=> isset( $message['connected_link_heading'] ) ? $message['connected_link_heading'] : '',
				'no_social_connected'	=> isset( $message['no_social_connected'] ) ? $message['no_social_connected'] : '',
				'connect_now_link'		=> isset( $message['connect_now_link'] ) ? $message['connect_now_link'] : '',
			));
			wp_enqueue_script( 'edd-slg-unlink-script' );
			wp_enqueue_script( 'edd-slg-public-script' );
		}
	}
	
	/**
	 * Give list of connect social media list
	 * 
	 * @package Easy Digital Downloads - Social Login
	 * @since 1.5.6
	 */	
	public function edd_get_user_social_linked_profiles( $user_id = null ) {
		
		// check useris login
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}
		
		$data =  get_user_meta( $user_id);
		
		$linked_social_login_profiles = array();
		
		$edd_social_order = get_option( 'edd_social_order' );	
		
		//get primary social account type if exist
		$primary_social		= get_user_meta( $user_id, 'edd_slg_social_user_connect_via', true );
		
		// Get list of saved profiles
		foreach ( $edd_social_order as $provider ) {
			
			if( $primary_social == $provider ) {
				
				$social_profile = get_user_meta( $user_id, 'edd_slg_social_data', true );
			} else {				
				$social_profile = get_user_meta( $user_id, 'edd_slg_social_' . $provider . '_data', true );
			}
			
			// check profile is saved
			if ( !empty( $social_profile ) || $primary_social == $provider ) {
				// add provider to profile, as it's not saved with the raw profile
				$linked_social_login_profiles[ $provider ] =  $social_profile;
			}
		}
		
		return apply_filters( 'edd_get_user_social_linked_profiles', $linked_social_login_profiles );
	}
	
	/**
	 * Display Unlink buttons
	 * User Connected list	 
	 * 
	 * @package Easy Digital Downloads - Social Login
	 * @since 1.5.6
	 */	
	public function edd_slg_myaccount_social_login_buttons(){
		
		global $post;
		global $edd_options;
		
		if ( has_shortcode( $post->post_content, 'edd_profile_editor' ) ) { //is my account page		
			$this->edd_slg_social_profile();
		}
	}
	
	
	/**
	 * Social Link button on thankyou page
	 * 
	 * Handles to display social link buttons on thankyou page
	 * 
	 * @package  Easy Digital Downloads - Social Login
	 * @since 1.5.6
	 */
	public function edd_slg_maybe_render_social_link_buttons() {

		global $post;
		if ( is_user_logged_in() && has_shortcode( $post->post_content, 'edd_receipt' )
			&& edd_slg_check_social_enable() && edd_slg_link_display_on_thankyou_page() ) {

			 	//display link buttons
			 	edd_slg_link_buttons();
		}
	}
	
	/**
	 * Show Facebook Login Button
	 * 
	 * Handles to show facebook social login
	 * button
	 * 
	 * @package Easy Digital Downloads - Social Login
	 * @since 1.0.0
	 */
	public function edd_slg_login_facebook() {

		global $edd_options;
		
		//check facebook is enable or not
		if( !empty( $edd_options['edd_slg_enable_facebook'] ) ) {
		
			$fbimgurl = isset( $edd_options['edd_slg_fb_icon_url'] ) && !empty( $edd_options['edd_slg_fb_icon_url'] ) 
						? $edd_options['edd_slg_fb_icon_url'] : EDD_SLG_IMG_URL . '/facebook.png';
	
			//load facebook button
			edd_slg_get_template( 'social-buttons/facebook.php', array( 'fbimgurl' => $fbimgurl ) );
			
			if( EDD_SLG_FB_APP_ID != '' && EDD_SLG_FB_APP_SECRET != '' ) {
			
				//enqueue FB init script
				wp_enqueue_script( 'facebook' );
				wp_enqueue_script( 'edd-slg-fbinit' );
			}
		}
	}
	
	/**
	 * Show Facebook Login Link Button
	 * 
	 * Handles to show facebook social login link
	 * button
	 * 
	 * @package Easy Digital Downloads - Social Login
	 * @since 1.5.6
	 */
	public function edd_slg_login_link_facebook() {

		global $edd_options;
		
		$show_link = edd_slg_can_show_social_link( 'facebook' );
		//check facebook is enable or not
		if( !empty($edd_options['edd_slg_enable_facebook']) && $show_link ) {
		
			$fblinkimgurl = isset( $edd_options['edd_slg_fb_link_icon_url'] ) && !empty( $edd_options['edd_slg_fb_link_icon_url'] ) 
						? $edd_options['edd_slg_fb_link_icon_url'] : EDD_SLG_IMG_URL . '/facebook-link.png';
	
			//load facebook button
			edd_slg_get_template( 'social-link-buttons/facebook_link.php', array( 'fblinkimgurl' => $fblinkimgurl ) );
			
			if( EDD_SLG_FB_APP_ID != '' && EDD_SLG_FB_APP_SECRET != '' ) {
			
				//enqueue FB init script
				wp_enqueue_script( 'facebook' );
				wp_enqueue_script( 'edd-slg-fbinit' );
			}
		}
	}
	
	/**
	 * Show Google+ Login Button
	 * 
	 * Handles to show google+ social login
	 * button
	 * 
	 * @package Easy Digital Downloads - Social Login
	 * @since 1.0.0
	 */
	public function edd_slg_login_googleplus() {

		global $edd_options;
		
		//check google+ is enable or not
		if( !empty( $edd_options['edd_slg_enable_googleplus'] ) ) {
		
			$gpimgurl = isset( $edd_options['edd_slg_gp_icon_url'] ) && !empty( $edd_options['edd_slg_gp_icon_url'] ) 
						? $edd_options['edd_slg_gp_icon_url'] : EDD_SLG_IMG_URL . '/googleplus.png';
	
			//load googleplus button
			edd_slg_get_template( 'social-buttons/googleplus.php', array( 'gpimgurl' => $gpimgurl ) );
			
			if( EDD_SLG_GP_CLIENT_ID != '' && EDD_SLG_GP_CLIENT_SECRET != '' ) {
			
				$gp_authurl = $this->socialgoogle->edd_slg_get_google_auth_url();
				
				echo '<input type="hidden" class="edd-slg-social-gp-redirect-url" id="edd_slg_social_gp_redirect_url" name="edd_slg_social_gp_redirect_url" value="'.$gp_authurl.'"/>';
			}
		}
	}
	
	/**
	 * Show Google+ Login Link Button
	 * 
	 * Handles to show google+ social login link
	 * button
	 * 
	 * @package Easy Digital Downloads - Social Login
	 * @since 1.5.6
	 */
	public function edd_slg_login_link_googleplus() {

		global $edd_options;
		
		$show_link = edd_slg_can_show_social_link( 'googleplus' );
		
		//check google+ is enable or not
		if( !empty($edd_options['edd_slg_enable_googleplus']) && $show_link ) {
		
			$gplinkimgurl = isset( $edd_options['edd_slg_gp_link_icon_url'] ) && !empty( $edd_options['edd_slg_gp_link_icon_url'] ) 
						? $edd_options['edd_slg_gp_link_icon_url'] : EDD_SLG_IMG_URL . '/googleplus-link.png';
	
			//load googleplus button
			edd_slg_get_template( 'social-link-buttons/googleplus_link.php', array( 'gplinkimgurl' => $gplinkimgurl ) );
			
			if( EDD_SLG_GP_CLIENT_ID != '' && EDD_SLG_GP_CLIENT_SECRET != '' ) {
			
				$gp_authurl = $this->socialgoogle->edd_slg_get_google_auth_url();
				
				echo '<input type="hidden" class="edd-slg-social-gp-redirect-url" id="edd_slg_social_gp_redirect_url" name="edd_slg_social_gp_redirect_url" value="'.$gp_authurl.'"/>';
			}
		}
	}
	
	/**
	 * Show Linkedin Login Button
	 * 
	 * Handles to show linkedin social login
	 * button
	 * 
	 * @package Easy Digital Downloads - Social Login
	 * @since 1.0.0
	 */
	public function edd_slg_login_linkedin() {

		global $edd_options;
		
		//check linkedin is enable or not
		if( !empty( $edd_options['edd_slg_enable_linkedin'] ) ) {
		
			$liimgurl = isset( $edd_options['edd_slg_li_icon_url'] ) && !empty( $edd_options['edd_slg_li_icon_url'] ) 
						? $edd_options['edd_slg_li_icon_url'] : EDD_SLG_IMG_URL . '/linkedin.png';
	
			//load linkedin button
			edd_slg_get_template( 'social-buttons/linkedin.php', array( 'liimgurl' => $liimgurl ) );
			
			if( EDD_SLG_LI_APP_ID != '' && EDD_SLG_LI_APP_SECRET != '' ) {
			
				$li_authurl = $this->sociallinkedin->edd_slg_linkedin_auth_url();
				
				echo '<input type="hidden" class="edd-slg-social-li-redirect-url" id="edd_slg_social_li_redirect_url" name="edd_slg_social_li_redirect_url" value="'.$li_authurl.'"/>';
			}
		}
	}
	
	/**
	 * Show Linkedin Link Button
	 * 
	 * Handles to show linkedin social login
	 * button	 
	 * 
	 * @package Easy Digital Downloads - Social Login
	 * @since 1.5.6
	 */
	public function edd_slg_login_link_linkedin() {

		global $edd_options;		
		
		$show_link = edd_slg_can_show_social_link( 'linkedin' );
		
		//check linkedin is enable or not
		if( !empty($edd_options['edd_slg_enable_linkedin']) && $show_link ) {
		
			$lilinkimgurl = isset( $edd_options['edd_slg_li_link_icon_url'] ) && !empty( $edd_options['edd_slg_li_link_icon_url'] ) 
						? $edd_options['edd_slg_li_link_icon_url'] : EDD_SLG_IMG_URL . '/linkedin-link.png';
	
			//load linkedin button
			edd_slg_get_template( 'social-link-buttons/linkedin_link.php', array( 'lilinkimgurl' => $lilinkimgurl ) );
			
			if( EDD_SLG_LI_APP_ID != '' && EDD_SLG_LI_APP_SECRET != '' ) {
			
				$li_authurl = $this->sociallinkedin->edd_slg_linkedin_auth_url();
				
				echo '<input type="hidden" class="edd-slg-social-li-redirect-url" id="edd_slg_social_li_redirect_url" name="edd_slg_social_li_redirect_url" value="'.$li_authurl.'"/>';
			}
		}
	}
	
	/**
	 * Show Twitter Login Button
	 * 
	 * Handles to show twitter social login
	 * button
	 * 
	 * @package Easy Digital Downloads - Social Login
	 * @since 1.0.0
	 */
	public function edd_slg_login_twitter() {

		global $edd_options;
		
		//check twitter is enable or not
		if( !empty( $edd_options['edd_slg_enable_twitter'] ) ) {
		
			$twimgurl = isset( $edd_options['edd_slg_tw_icon_url'] ) && !empty( $edd_options['edd_slg_tw_icon_url'] ) 
						? $edd_options['edd_slg_tw_icon_url'] : EDD_SLG_IMG_URL . '/twitter.png';
	
			//load twitter button
			edd_slg_get_template( 'social-buttons/twitter.php', array( 'twimgurl' => $twimgurl ) );
	
			if( EDD_SLG_TW_CONSUMER_KEY != '' && EDD_SLG_TW_CONSUMER_SECRET != '' ) {
				
				$tw_authurl = $this->socialtwitter->edd_slg_get_twitter_auth_url();
				
				echo '<input type="hidden" class="edd-slg-social-tw-redirect-url" id="edd_slg_social_tw_redirect_url" name="edd_slg_social_tw_redirect_url" value="'.$tw_authurl.'" />';
				
			}
		}
	}
	
	/**
	 * Show Twitter Link Button
	 * 
	 * Handles to show twitter social link button
	 * 
	 * @package Easy Digital Downloads - Social Login
	 * @since 1.5.5
	 */
	public function edd_slg_login_link_twitter() {

		global $edd_options;
		
		$show_link = edd_slg_can_show_social_link( 'twitter' );
		
		//check twitter is enable or not
		if( !empty($edd_options['edd_slg_enable_twitter']) && $show_link ) {
			
			$twlinkimgurl = isset( $edd_options['edd_slg_tw_link_icon_url'] ) && !empty( $edd_options['edd_slg_tw_link_icon_url'] ) 
						? $edd_options['edd_slg_tw_link_icon_url'] : EDD_SLG_IMG_URL . '/twitter-link.png';
	
			//load twitter button
			edd_slg_get_template( 'social-link-buttons/twitter_link.php', array( 'twlinkimgurl' => $twlinkimgurl ) );
	
			if( EDD_SLG_TW_CONSUMER_KEY != '' && EDD_SLG_TW_CONSUMER_SECRET != '' ) {
				
				$tw_authurl = $this->socialtwitter->edd_slg_get_twitter_auth_url();
				
				echo '<input type="hidden" class="edd-slg-social-tw-redirect-url" id="edd_slg_social_tw_redirect_url" name="edd_slg_social_tw_redirect_url" value="'.$tw_authurl.'" />';
			}
		}
	}
	
	/**
	 * Show Yahoo Login Button
	 * 
	 * Handles to show yahoo social login
	 * button
	 * 
	 * @package Easy Digital Downloads - Social Login
	 * @since 1.0.0
	 */
	public function edd_slg_login_yahoo() {

		global $edd_options;
		
		//check yahoo is enable or not
		if( !empty( $edd_options['edd_slg_enable_yahoo'] ) ) {
		
			$yhimgurl = isset( $edd_options['edd_slg_yh_icon_url'] ) && !empty( $edd_options['edd_slg_yh_icon_url'] ) 
						? $edd_options['edd_slg_yh_icon_url'] : EDD_SLG_IMG_URL . '/yahoo.png';
	
			//load yahoo button
			edd_slg_get_template( 'social-buttons/yahoo.php', array( 'yhimgurl' => $yhimgurl ) );
			
			if( EDD_SLG_YH_CONSUMER_KEY != '' && EDD_SLG_YH_CONSUMER_SECRET != '' && EDD_SLG_YH_APP_ID != '' ) {
			
				$yh_authurl = $this->socialyahoo->edd_slg_get_yahoo_auth_url();
				
				echo '<input type="hidden" class="edd-slg-social-yh-redirect-url" id="edd_slg_social_yh_redirect_url" name="edd_slg_social_yh_redirect_url" value="'.$yh_authurl.'"/>';
			}
		}
	}
	
	/**
	 * Show Yahoo Link Button
	 * 
	 * Handles to show yahoo social login button
	 * 
	 * @package Easy Digital Downloads - Social Login
	 * @since 1.5.6
	 */
	public function edd_slg_login_link_yahoo() {

		global $edd_options;
		
		$show_link = edd_slg_can_show_social_link( 'yahoo' );
		
		//check yahoo is enable or not
		if( !empty($edd_options['edd_slg_enable_yahoo']) && $show_link ) {
		
			$yhlinkimgurl = isset( $edd_options['edd_slg_yh_link_icon_url'] ) && !empty( $edd_options['edd_slg_yh_link_icon_url'] ) 
						? $edd_options['edd_slg_yh_link_icon_url'] : EDD_SLG_IMG_URL . '/yahoo-link.png';
	
			//load yahoo button
			edd_slg_get_template( 'social-link-buttons/yahoo_link.php', array( 'yhlinkimgurl' => $yhlinkimgurl ) );
			
			if( EDD_SLG_YH_CONSUMER_KEY != '' && EDD_SLG_YH_CONSUMER_SECRET != '' && EDD_SLG_YH_APP_ID != '' ) {
			
				$yh_authurl = $this->socialyahoo->edd_slg_get_yahoo_auth_url();
				
				echo '<input type="hidden" class="edd-slg-social-yh-redirect-url" id="edd_slg_social_yh_redirect_url" name="edd_slg_social_yh_redirect_url" value="'.$yh_authurl.'"/>';
			}
		}
	}
	
	/**
	 * Show Foursquare Login Button
	 * 
	 * Handles to show foursquare social login button
	 * 
	 * @package Easy Digital Downloads - Social Login
	 * @since 1.0.0
	 */
	public function edd_slg_login_foursquare() {

		global $edd_options;
		
		//check yahoo is enable or not
		if( !empty( $edd_options['edd_slg_enable_foursquare'] ) ) {
		
			$fsimgurl = isset( $edd_options['edd_slg_fs_icon_url'] ) && !empty( $edd_options['edd_slg_fs_icon_url'] ) 
						? $edd_options['edd_slg_fs_icon_url'] : EDD_SLG_IMG_URL . '/foursquare.png';
	
			//load foursquare button
			edd_slg_get_template( 'social-buttons/foursquare.php', array( 'fsimgurl' => $fsimgurl ) );
	
			if( EDD_SLG_FS_CLIENT_ID != '' && EDD_SLG_FS_CLIENT_SECRET != '' ) {
			
				$fs_authurl = $this->socialfoursquare->edd_slg_get_foursquare_auth_url();
				
				echo '<input type="hidden" class="edd-slg-social-fs-redirect-url" id="edd_slg_social_fs_redirect_url" name="edd_slg_social_fs_redirect_url" value="'.$fs_authurl.'"/>';
			}
		}
	}
	
	/**
	 * Show Foursquare Login Link Button
	 * 
	 * Handles to show foursquare social login link button
	 * 
	 * @package Easy Digital Downloads - Social Login
	 * @since 1.5.6
	 */
	public function edd_slg_login_link_foursquare() {

		global $edd_options;
		$show_link = edd_slg_can_show_social_link( 'foursquare' );
		
		//check foursquare is enable or not
		if( !empty($edd_options['edd_slg_enable_foursquare']) && $show_link ) {
		
			$fslinkimgurl = isset( $edd_options['edd_slg_fs_link_icon_url'] ) && !empty( $edd_options['edd_slg_fs_link_icon_url'] ) 
						? $edd_options['edd_slg_fs_link_icon_url'] : EDD_SLG_IMG_URL . '/foursquare-link.png';
	
			//load foursquare button
			edd_slg_get_template( 'social-link-buttons/foursquare_link.php', array( 'fslinkimgurl' => $fslinkimgurl ) );
	
			if( EDD_SLG_FS_CLIENT_ID != '' && EDD_SLG_FS_CLIENT_SECRET != '' ) {
			
				$fs_authurl = $this->socialfoursquare->edd_slg_get_foursquare_auth_url();
				
				echo '<input type="hidden" class="edd-slg-social-fs-redirect-url" id="edd_slg_social_fs_redirect_url" name="edd_slg_social_fs_redirect_url" value="'.$fs_authurl.'"/>';
			}
		}
	}
	
	/**
	 * Show Windows Live Login Button
	 * 
	 * Handles to show windowlive social login button
	 * 
	 * @package Easy Digital Downloads - Social Login
	 * @since 1.0.0
	 */
	public function edd_slg_login_windowslive() {

		global $edd_options;
		
		//check yahoo is enable or not
		if( !empty( $edd_options['edd_slg_enable_windowslive'] ) ) {
		
			$wlimgurl = isset( $edd_options['edd_slg_wl_icon_url'] ) && !empty( $edd_options['edd_slg_wl_icon_url'] ) 
						? $edd_options['edd_slg_wl_icon_url'] : EDD_SLG_IMG_URL . '/windowslive.png';
	
			//load windows live button
			edd_slg_get_template( 'social-buttons/windowslive.php', array( 'wlimgurl' => $wlimgurl ) );
			
			if( EDD_SLG_WL_CLIENT_ID != '' && EDD_SLG_WL_CLIENT_SECRET != '' ) {
			
				$wl_authurl = $this->socialwindowslive->edd_slg_get_wl_auth_url();
				
				echo '<input type="hidden" class="edd-slg-social-wl-redirect-url" id="edd_slg_social_wl_redirect_url" name="edd_slg_social_wl_redirect_url" value="'.$wl_authurl.'"/>';
			}
		}
	}
	
	/**
	 * Show Windows Live Link Button
	 * 
	 * Handles to show window live social link button
	 * 
	 * @package Easy Digital Downloads - Social Login
	 * @since 1.5.6
	 */
	public function edd_slg_login_link_windowslive() {

		global $edd_options;	
		
		$show_link = edd_slg_can_show_social_link( 'windowslive' );
		
		//check foursquare is enable or not
		if( !empty($edd_options['edd_slg_enable_windowslive']) && $show_link ) {
		
			$wllinkimgurl = isset( $edd_options['edd_slg_wl_link_icon_url'] ) && !empty( $edd_options['edd_slg_wl_link_icon_url'] ) 
						? $edd_options['edd_slg_wl_link_icon_url'] : EDD_SLG_IMG_URL . '/windowslive-link.png';
	
			//load windows live button
			edd_slg_get_template( 'social-link-buttons/windowslive_link.php', array( 'wllinkimgurl' => $wllinkimgurl ) );
			
			if( EDD_SLG_WL_CLIENT_ID != '' && EDD_SLG_WL_CLIENT_SECRET != '' ) {
			
				$wl_authurl = $this->socialwindowslive->edd_slg_get_wl_auth_url();
				
				echo '<input type="hidden" class="edd-slg-social-wl-redirect-url" id="edd_slg_social_wl_redirect_url" name="edd_slg_social_wl_redirect_url" value="'.$wl_authurl.'"/>';
			}
		}
	}
	
	/**
	 * Show VK Login Button
	 * 
	 * Handles to show vk social login button
	 * 
	 * @package Easy Digital Downloads - Social Login
	 * @since 1.3.0
	 */
	public function edd_slg_login_vk() {

		global $edd_options;
		
		//check vk is enable or not
		if( !empty( $edd_options['edd_slg_enable_vk'] ) ) {
		
			$vkimgurl = isset( $edd_options['edd_slg_vk_icon_url'] ) && !empty( $edd_options['edd_slg_vk_icon_url'] ) 
						? $edd_options['edd_slg_vk_icon_url'] : EDD_SLG_IMG_URL . '/vk.png';
	
			//load vk button
			edd_slg_get_template( 'social-buttons/vk.php', array( 'vkimgurl' => $vkimgurl ) );
			
			if( EDD_SLG_VK_APP_ID != '' && EDD_SLG_VK_APP_SECRET != '' ) {
			
				$vk_authurl = $this->socialvk->edd_slg_get_vk_auth_url();
				
				echo '<input type="hidden" class="edd-slg-social-vk-redirect-url" id="edd_slg_social_vk_redirect_url" name="edd_slg_social_vk_redirect_url" value="'.$vk_authurl.'"/>';
				
			}
		}
	}
	
	/**
	 * Show VK Login Link Button
	 * 
	 * Handles to show vk social login link button
	 * 
	 * @package Easy Digital Downloads - Social Login
	 * @since 1.5.6
	 */
	public function edd_slg_login_link_vk() {

		global $edd_options;
			
		$show_link = edd_slg_can_show_social_link( 'vk' );
		
		//check VK is enable or not
		if( !empty($edd_options['edd_slg_enable_vk']) && $show_link ) {
		
			$vklinkimgurl = isset( $edd_options['edd_slg_vk_link_icon_url'] ) && !empty( $edd_options['edd_slg_vk_link_icon_url'] ) 
						? $edd_options['edd_slg_vk_link_icon_url'] : EDD_SLG_IMG_URL . '/vk-link.png';
	
			//load vk button
			edd_slg_get_template( 'social-link-buttons/vk_link.php', array( 'vklinkimgurl' => $vklinkimgurl ) );
			
			if( EDD_SLG_VK_APP_ID != '' && EDD_SLG_VK_APP_SECRET != '' ) {
			
				$vk_authurl = $this->socialvk->edd_slg_get_vk_auth_url();
				
				echo '<input type="hidden" class="edd-slg-social-vk-redirect-url" id="edd_slg_social_vk_redirect_url" name="edd_slg_social_vk_redirect_url" value="'.$vk_authurl.'"/>';
			}
		}
	}
	
	/**
	 * Show Instagram Login Button
	 * 
	 * Handles to show instagram social login button
	 * 
	 * @package Easy Digital Downloads - Social Login
	 * @since 1.3.0
	 */
	public function edd_slg_login_instagram() {

		global $edd_options;
		
		//check instagram is enable or not
		if( !empty( $edd_options['edd_slg_enable_instagram'] ) ) {
		
			$instimgurl = isset( $edd_options['edd_slg_inst_icon_url'] ) && !empty( $edd_options['edd_slg_inst_icon_url'] ) 
						? $edd_options['edd_slg_inst_icon_url'] : EDD_SLG_IMG_URL . '/instagram.png';
	
			//load vk button
			edd_slg_get_template( 'social-buttons/instagram.php', array( 'instimgurl' => $instimgurl ) );
			
			if( EDD_SLG_INST_APP_ID != '' && EDD_SLG_INST_APP_SECRET != '' ) {
			
				$inst_authurl = $this->socialinstagram->edd_slg_get_instagram_auth_url();
				
				echo '<input type="hidden" class="edd-slg-social-inst-redirect-url" id="edd_slg_social_inst_redirect_url" name="edd_slg_social_inst_redirect_url" value="'.$inst_authurl.'"/>';
			}
		}
	}
	
	/**
	 * Show Instagram Login Link Button
	 * 
	 * Handles to show instagram social login link button
	 * 
	 * @package Easy Digital Downloads - Social Login
	 * @since 1.5.6
	 */
	public function edd_slg_login_link_instagram() {

		global $edd_options;
		
		$show_link = edd_slg_can_show_social_link( 'instagram' );
		
		//check instagram is enable or not
		if( !empty($edd_options['edd_slg_enable_instagram']) && $show_link ) {
		
			$instlinkimgurl = isset( $edd_options['edd_slg_inst_link_icon_url'] ) && !empty( $edd_options['edd_slg_inst_link_icon_url'] ) 
						? $edd_options['edd_slg_inst_link_icon_url'] : EDD_SLG_IMG_URL . '/instagram-link.png';
	
			//load vk button
			edd_slg_get_template( 'social-link-buttons/instagram_link.php', array( 'instlinkimgurl' => $instlinkimgurl ) );
			
			if( EDD_SLG_INST_APP_ID != '' && EDD_SLG_INST_APP_SECRET != '' ) {
			
				$inst_authurl = $this->socialinstagram->edd_slg_get_instagram_auth_url();
				
				echo '<input type="hidden" class="edd-slg-social-inst-redirect-url" id="edd_slg_social_inst_redirect_url" name="edd_slg_social_inst_redirect_url" value="'.$inst_authurl.'"/>';
			}
		}
	}	
	
	
	/**
	 * Show Amazon Login Button
	 * 
	 * Handles to show amazon social login button
	 * 
	 * @package Easy Digital Downloads - Social Login
	 * @since 1.7.0
	 */
	public function edd_slg_login_amazon() {
		
		global $edd_options;				
		//check amazon is enable or not
		if( !empty( $edd_options['edd_slg_enable_amazon'] ) ) {
			
			$amazonimgurl = isset( $edd_options['edd_slg_amazon_icon_url'] ) && !empty( $edd_options['edd_slg_amazon_icon_url'] ) 
						? $edd_options['edd_slg_amazon_icon_url'] : EDD_SLG_IMG_URL . '/amazon.png';			
			
			//load amazon button
			edd_slg_get_template( 'social-buttons/amazon.php', array( 'amazonimgurl' => $amazonimgurl) );
			
			if( EDD_SLG_AMAZON_APP_ID != '' && EDD_SLG_AMAZON_APP_SECRET != '' ) {
				$amazon_authurl = $this->socialamazon->edd_slg_get_amazon_auth_url();							
				echo '<input type="hidden" class="edd-slg-social-amazon-redirect-url" id="edd_slg_social_amazon_redirect_url" name="edd_slg_social_amazon_redirect_url" value="'.$amazon_authurl.'"/>';				
				wp_enqueue_script( 'amazon' );
			}
			
		}
	}
	
	
	
	/**
	 * Show Amazon Login Button
	 * 
	 * Handles to show amazon social login button
	 * 
	 * @package Easy Digital Downloads - Social Login
	 * @since 1.7.0
	 */
	public function edd_slg_login_link_amazon() {
		
		global $edd_options;
		
		$show_link = edd_slg_can_show_social_link( 'amazon' );
		
		//check amazon is enable or not
		if( !empty($edd_options['edd_slg_enable_amazon']) && $show_link ) {
			
			$amazonimglinkurl = isset( $edd_options['edd_slg_amazon_link_icon_url'] ) && !empty( $edd_options['edd_slg_amazon_link_icon_url'] ) 
						? $edd_options['edd_slg_amazon_link_icon_url'] : EDD_SLG_IMG_URL . '/amazon-link.png';
			
			//load amazon button
			edd_slg_get_template( 'social-link-buttons/amazon_link.php', array( 'amazonimgurl' => $amazonimglinkurl) );
			
			if( EDD_SLG_AMAZON_APP_ID != '' && EDD_SLG_AMAZON_APP_SECRET != '' ) {
			$amazon_authurl = $this->socialamazon->edd_slg_get_amazon_auth_url();				
				echo '<input type="hidden" class="edd-slg-social-amazon-redirect-url" id="edd_slg_social_amazon_redirect_url" name="edd_slg_social_amazon_redirect_url" value="'.$amazon_authurl.'"/>';
				
			wp_enqueue_script( 'amazon' );
			}
			
		}
	}
	
	
	/**
	 * Show Paypal Login Button
	 * 
	 * Handles to show paypal social login button
	 * 
	 * @package Easy Digital Downloads - Social Login
	 * @since 1.7.0
	 */
	public function edd_slg_login_paypal() {
		
		global $edd_options;
				
		//check paypal is enable or not
		if( !empty($edd_options['edd_slg_enable_paypal']) ) {		
			
			$paypalimgurl = isset( $edd_options['edd_slg_paypal_icon_url'] ) && !empty( $edd_options['edd_slg_paypal_icon_url'] ) 
						? $edd_options['edd_slg_paypal_icon_url'] : EDD_SLG_IMG_URL . '/paypal.png';			
			
			//load paypal button
			edd_slg_get_template( 'social-buttons/paypal.php', array( 'paypalimgurl' => $paypalimgurl) );
			
			if( EDD_SLG_PAYPAL_APP_ID != '' && EDD_SLG_PAYPAL_APP_SECRET != '' ) {
				$paypal_authurl = $this->socialpaypal->edd_slg_get_paypal_auth_url();			
				echo '<input type="hidden" class="edd-slg-social-paypal-redirect-url" id="edd_slg_social_paypal_redirect_url" name="edd_slg_social_paypal_redirect_url" value="'.$paypal_authurl.'"/>';
			}			
		}
	}
	
	
	
	/**
	 * Show Paypal Login Button
	 * 
	 * Handles to show paypal social login button
	 * 
	* @package Easy Digital Downloads - Social Login
	 * @since 1.7.0
	 */
	public function edd_slg_login_link_paypal() {
		
		global $edd_options;
		
		$show_link = edd_slg_can_show_social_link( 'paypal' );
		
		//check paypal is enable or not
		if( !empty($edd_options['edd_slg_enable_paypal']) && $show_link ) {
			
			$paypalimglinkurl = isset( $edd_options['edd_slg_paypal_link_icon_url'] ) && !empty( $edd_options['edd_slg_paypal_link_icon_url'] ) 
						? $edd_options['edd_slg_paypal_link_icon_url'] : EDD_SLG_IMG_URL . '/paypal-link.png';
						
			//load paypal button
			edd_slg_get_template( 'social-link-buttons/paypal_link.php', array( 'paypalimglinkurl' => $paypalimglinkurl) );
			
			if( EDD_SLG_PAYPAL_APP_ID != '' && EDD_SLG_PAYPAL_APP_SECRET != '' ) {
			$paypal_authurl = $this->socialpaypal->edd_slg_get_paypal_auth_url();
				
				echo '<input type="hidden" class="edd-slg-social-paypal-redirect-url" id="edd_slg_social_paypal_redirect_url" name="edd_slg_social_paypal_redirect_url" value="'.$paypal_authurl.'"/>';			
			}
			
		}
	}	
	
	
	/**
	 * Show login wrapper class on checkout page
	 * 
	 * @package Easy Digital Downloads - Social Login
	 * @since 1.4.2
	 */
	public function edd_slg_checkout_wrapper_social_login_content() {

		global $post;
		
		$redirect_url = get_permalink( $post->ID );
		$this->edd_slg_social_login_inner_buttons( $redirect_url );
	}
}