<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Scripts Class
 * 
 * Handles adding scripts functionality to the admin pages
 * as well as the front pages.
 * 
 * @package Easy Digital Downloads - Social Login
 * @since 1.0.0
 */
class EDD_Slg_Scripts{

	public function __construct() {

	}

	/**
	 * Enqueue Styles for backend on needed page
	 * 
	 * @package Easy Digital Downloads - Social Login
	 * @since 1.0.0
	 */
	public function edd_slg_admin_styles( $hook_suffix ) {

		$edd_screen_id		= edd_slg_get_edd_screen_id();

		$pages_hook_suffix = array( 'post-new.php', 'post.php', $edd_screen_id.'_page_edd-social-login', 'user-edit.php', 'profile.php' );

		//Check pages when you needed
		if( in_array( $hook_suffix, $pages_hook_suffix ) ) {

			wp_register_style( 'edd-slg-admin-styles', EDD_SLG_URL . 'includes/css/style-admin.css', array(), EDD_SLG_VERSION );
			wp_enqueue_style( 'edd-slg-admin-styles' );
		}
	}

	/**
	 * Enqueue Scripts for backend on needed page
	 * 
	 * @package Easy Digital Downloads - Social Login
	 * @since 1.0.0
	 */
	public function edd_slg_admin_scripts( $hook_suffix ) {

		$edd_screen_id		= edd_slg_get_edd_screen_id();

		$pages_hook_suffix = array( $edd_screen_id.'_page_edd-social-login' );

		//Check pages when you needed
		if( in_array( $hook_suffix, $pages_hook_suffix ) ) {

			wp_register_script( 'edd-slg-admin-scripts', EDD_SLG_URL . 'includes/js/edd-slg-admin.js', array('jquery', 'jquery-ui-sortable' ), EDD_SLG_VERSION, true );
			wp_enqueue_script( 'edd-slg-admin-scripts' );
		}
	}

	/**
	 * Enqueue Scripts for public side
	 * 
	 * @package Easy Digital Downloads - Social Login
	 * @since 1.0.0
	 */
	public function edd_slg_public_scripts() {

		global $edd_options, $post;		
		
		//check if site is secure then use https:// else http://
		$suffix = is_ssl() ? 'https://' : 'http://';

		//check facebook social login enable or not
		if( !empty( $edd_options['edd_slg_enable_facebook'] ) && EDD_SLG_FB_APP_ID != '' && EDD_SLG_FB_APP_SECRET != '' ) {

			wp_deregister_script('facebook');
			wp_register_script('facebook', $suffix.'connect.facebook.net/'.$edd_options['edd_slg_fb_language'].'/all.js#xfbml=1&appId='.EDD_SLG_FB_APP_ID, false, EDD_SLG_VERSION );
			wp_register_script( 'edd-slg-fbinit', EDD_SLG_URL . 'includes/js/edd-slg-fbinit.js', array( 'jquery' ), EDD_SLG_VERSION, true );
			wp_localize_script( 'edd-slg-fbinit', 'EDDSlgFbInit', array( 'app_id' => EDD_SLG_FB_APP_ID ) );
		}
		
		if( !empty( $edd_options['edd_slg_enable_amazon'] ) && EDD_SLG_AMAZON_APP_ID != '' && EDD_SLG_AMAZON_APP_SECRET != '' ) {
			wp_deregister_script('amazon');			
			wp_register_script( 'amazon', 'https://api-cdn.amazon.com/sdk/login1.js' );			
		}

		//if there is no authentication data entered in settings page then so error
		$fberror = $gperror = $lierror = $twerror = $yherror = $fserror = $wlerror = $vkerror = $insterror = $amazonerror = $paypalerror ='';
		if( EDD_SLG_FB_APP_ID == '' || EDD_SLG_FB_APP_SECRET == '' ) { $fberror = '1'; }
		if( EDD_SLG_GP_CLIENT_ID == '' || EDD_SLG_GP_CLIENT_SECRET == '' ) { $gperror = '1'; }
		if( EDD_SLG_LI_APP_ID == '' || EDD_SLG_LI_APP_SECRET == '' ) { $lierror = '1'; }
		if( EDD_SLG_TW_CONSUMER_KEY == '' || EDD_SLG_TW_CONSUMER_SECRET == '' ) { $twerror = '1'; }
		if( EDD_SLG_YH_CONSUMER_KEY == '' || EDD_SLG_YH_CONSUMER_SECRET == '' || EDD_SLG_YH_APP_ID == '' ) { $yherror = '1'; }
		if( EDD_SLG_FS_CLIENT_ID == '' || EDD_SLG_FS_CLIENT_SECRET == '' ) { $fserror = '1'; }
		if( EDD_SLG_WL_CLIENT_ID == '' || EDD_SLG_WL_CLIENT_SECRET == '' ) { $wlerror = '1'; }
		if( EDD_SLG_VK_APP_ID == '' || EDD_SLG_VK_APP_SECRET == '' ) { $vkerror = '1'; }
		if( EDD_SLG_INST_APP_ID == '' || EDD_SLG_INST_APP_SECRET == '' ) { $insterror = '1'; }
		if( EDD_SLG_AMAZON_APP_ID == '' || EDD_SLG_AMAZON_APP_SECRET == '' ) { $amazonerror = '1'; }
		if( EDD_SLG_PAYPAL_APP_ID == '' || EDD_SLG_PAYPAL_APP_SECRET == '' ) { $paypalerror = '1'; }
		//get login url
		$loginurl = wp_login_url();
		$login_array = array( 
								'edd_slg_social_login'	=> 1,
							 	'eddslgnetwork'		=> 'twitter'
							 );

		if( is_singular() ) {
			$login_array['page_id'] = $post->ID;
		}
		$loginurl = add_query_arg( $login_array, $loginurl );
		
		$userid = '';
		if(is_user_logged_in()){
					$userid = get_current_user_id();
		}
		
		wp_register_script( 'edd-slg-unlink-script', EDD_SLG_URL . 'includes/js/edd-slg-unlink.js', array( 'jquery' ), EDD_SLG_VERSION, true );
		wp_localize_script( 'edd-slg-unlink-script', 'EDDSlgUnlink', array('ajaxurl' => admin_url( 'admin-ajax.php', ( is_ssl() ? 'https' : 'http' ) )) );
		
		wp_register_script( 'edd-slg-public-script', EDD_SLG_URL . 'includes/js/edd-slg-public.js', array( 'jquery' ), EDD_SLG_VERSION, true );
		wp_localize_script( 'edd-slg-public-script', 'EDDSlg', array( 
																'ajaxurl'			=>	admin_url( 'admin-ajax.php', ( is_ssl() ? 'https' : 'http' ) ),
																'fbappid'			=>  EDD_SLG_FB_APP_ID,
																'fberror'			=>	$fberror,
																'gperror'			=>	$gperror,
																'lierror'			=>	$lierror,
																'twerror'			=>	$twerror,
																'yherror'			=>	$yherror,
																'fserror'			=>	$fserror,
																'wlerror'			=>	$wlerror,
																'vkerror'			=>	$vkerror,
																'insterror'			=>	$insterror,
																'amazonerror'		=>	$amazonerror,
																'paypalerror'		=>	$paypalerror,
																'fberrormsg'		=>	'<span>'.__( 'Please enter Facebook API Key & Secret in settings page.', 'eddslg' ).'</span>',
																'gperrormsg'		=>	'<span>'.__( 'Please enter Google+ Client ID & Secret in settings page.', 'eddslg' ).'</span>',
																'lierrormsg'		=>	'<span>'.__( 'Please enter LinkedIn API Key & Secret in settings page.', 'eddslg' ).'</span>',
																'twerrormsg'		=>	'<span>'.__( 'Please enter Twitter Consumer Key & Secret in settings page.', 'eddslg' ).'</span>',
																'yherrormsg'		=>	'<span>'.__( 'Please enter Yahoo API Consumer Key, Secret & App Id in settings page.', 'eddslg' ).'</span>',
																'fserrormsg'		=>	'<span>'.__( 'Please enter Foursquare API Client ID & Secret in settings page.', 'eddslg' ).'</span>',
																'wlerrormsg'		=>	'<span>'.__( 'Please enter Windows Live API Client ID & Secret in settings page.', 'eddslg' ).'</span>',
																'vkerrormsg'		=>	'<span>'.__( 'Please enter VK API Client ID & Secret in settings page.', 'eddslg' ).'</span>',
																'insterrormsg'		=>	'<span>'.__( 'Please enter Instagram API Client ID & Secret in settings page.', 'eddslg' ).'</span>',
																'socialloginredirect'=>	$loginurl,
																'userid'			 => $userid,
																'amazonerrormsg'	 =>	'<span>'.__( 'Please enter Amazon API Key & Secret in settings page.', 'eddslg' ).'</span>',
																'paypalerrormsg'	 =>	'<span>'.__( 'Please enter Paypal API Key & Secret in settings page.', 'eddslg' ).'</span>',
															) );
															
	}

	/**
	 * Enqueue Styles
	 * 
	 * Loads the css file for the front end.
	 * 
	 * @package Easy Digital Downloads - Social Login
	 * @since 1.0.0
	 */
	public function edd_slg_public_styles() {

		wp_register_style( 'edd-slg-public-style', EDD_SLG_URL . 'includes/css/style-public.css', array(), EDD_SLG_VERSION );
		wp_enqueue_style( 'edd-slg-public-style' );
	}

	/**
	 * Register and Enqueue Script For
	 * Chart
	 * 
	 * Handles to load chart scipts
	 * 
	 * @package Easy Digital Downloads - Social Login
	 * @since 1.0.0
	 */
	public function edd_slg_chart_scripts( $hook_suffix ) {

		$edd_screen_id		= edd_slg_get_edd_screen_id();

		$pages_hook_suffix = array( $edd_screen_id.'_page_edd-social-login' );

		//Check pages when you needed
		if( in_array( $hook_suffix, $pages_hook_suffix ) ) {
		
			//check if site is secure then use https:// else http://
			$suffix = is_ssl() ? 'https://' : 'http://';
			
			wp_register_script( 'google-jsapi', $suffix.'www.google.com/jsapi', array('jquery'), EDD_SLG_VERSION, false ); // in header
			wp_enqueue_script( 'google-jsapi' );
		}
	}
	
	/**
	 * Display button in post / page container
	 *
	 * Handles to display button in post / page container
	 * 
	 * @package Easy Digital Downloads - Social Login
	 * @since 1.1.0
	 */
	public function edd_slg_shortcode_display_button( $buttons ) {
	 
		array_push( $buttons, "|", "edd_social_login" );
		return $buttons;
	}

	/**
	 * Include js for add button in post / page container
	 * 
	 * Handles to include js for add button in post / page container
	 * 
	 * @package Easy Digital Downloads - Social Login
	 * @since 1.1.0
	 */
	public function edd_slg_shortcode_button( $plugin_array ) {

		$plugin_array['edd_social_login'] = EDD_SLG_URL . 'includes/js/edd-slg-shortcodes.js?ver='.EDD_SLG_VERSION;
		return $plugin_array;
	}

	/**
	 * Display button in post / page container
	 * 
	 * Handles to display button in post / page container
	 * 
	 * @package Easy Digital Downloads - Social Login
	 * @since 1.1.0
	 */
	public function edd_slg_add_shortcode_button() {

		if( current_user_can( 'manage_options' ) || current_user_can( 'edit_posts' ) ) {
			add_filter( 'mce_external_plugins', array( $this, 'edd_slg_shortcode_button' ) );
   			add_filter( 'mce_buttons', array( $this, 'edd_slg_shortcode_display_button' ) );
		}
	}

	/**
	 * Add Faceook Root Div
	 * 
	 * Handles to add facebook root
	 * div to page
	 * 
	 * @package Easy Digital Downloads - Social Login
	 * @since 1.0.0
	 **/
	public function edd_slg_fb_root() {

		echo '<div id="fb-root"></div>';
	}

	/**
	 * Adding Hooks
	 * 
	 * Adding proper hoocks for the scripts.
	 * 
	 * @package Easy Digital Downloads - Social Login
	 * @since 1.0.0
	 */
	public function add_hooks() {

		//add styles for back end
		add_action( 'admin_enqueue_scripts', array($this, 'edd_slg_admin_styles') );

		//add script to back side for social login
		add_action( 'admin_enqueue_scripts', array($this, 'edd_slg_admin_scripts') );

		//add script for chart in social login
		add_action( 'admin_enqueue_scripts', array( $this, 'edd_slg_chart_scripts' ) );

		//add script to front side for social login
		add_action( 'wp_enqueue_scripts', array( $this, 'edd_slg_public_scripts' ) );
		
		//add styles for login page
		add_action( 'login_enqueue_scripts', array( $this, 'edd_slg_public_styles' ) );

		//add scripts for login page
		add_action( 'login_enqueue_scripts', array( $this, 'edd_slg_public_scripts' ) );

		//add styles for front end
		add_action( 'wp_enqueue_scripts', array( $this, 'edd_slg_public_styles' ) );

		// add filters for add add button in post / page container
		add_action( 'admin_init', array( $this, 'edd_slg_add_shortcode_button' ) );

		//add facebook root div
		add_action( 'wp_footer', array( $this, 'edd_slg_fb_root' ) );
		
		
	}
}