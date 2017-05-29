<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Shortcodes Class
 *
 * Handles shortcodes functionality of plugin
 *
 * @package Easy Digital Downloads - Social Login
 * @since 1.1.0
 */
class EDD_Slg_Shortcodes {
	
	var $model,$render;
	
	function __construct(){
		
		global $edd_slg_render,$edd_slg_model;
		
		$this->render = $edd_slg_render;
		$this->model = $edd_slg_model;
		
	}
	
	/**
	 * Show All Social Login Buttons
	 * 
	 * Handles to show all social login buttons on the viewing page
	 * whereever user put shortcode
	 * 
	 * @package Easy Digital Downloads - Social Login
	 * @since 1.1.0
	 */
	
	public function edd_slg_social_login( $atts, $content ) {
	
		global $edd_options, $post;
		
		extract( shortcode_atts( array(	
			'title'			=>	'',
	    	'redirect_url'	=>	'',
	    	'showonpage'	=>	false	
		), $atts ) );
		
		$showbuttons = true;
		
		
		// if show only on inners pages is set and current page is not inner page 
		if( !empty( $showonpage ) &&  !is_singular() ) { $showbuttons = false; }
		
		//check show social buttons or not
		if( $showbuttons ) {
			
			//check user is logged in to site or not and any single social login button is enable or not
			if( !is_user_logged_in() && edd_slg_check_social_enable() ) {
				
				// login heading from setting page
				$login_heading = isset( $edd_options['edd_slg_login_heading'] ) ? $edd_options['edd_slg_login_heading'] : '';
				//  check title first from shortcode
				$login_heading = !empty( $title ) ? $title : $login_heading;
				
				// get redirect url from settings 
				$defaulturl = isset( $edd_options['edd_slg_redirect_url'] ) && !empty( $edd_options['edd_slg_redirect_url'] ) 
									? $edd_options['edd_slg_redirect_url'] : edd_slg_get_current_page_url();
				
				//redirect url for shortcode
				$defaulturl = isset( $redirect_url ) && !empty( $redirect_url ) ? $redirect_url : $defaulturl; 
				
				//session create for access token & secrets		
				EDD()->session->set( 'edd_slg_stcd_redirect_url', $defaulturl );
				
				// get html for all social login buttons
				ob_start();
				
				echo '<fieldset id="edd_slg_social_login" class="edd-slg-social-container">';
				if( !empty($login_heading) ) {
					echo '<span><legend>'. $login_heading.'</legend></span>';
				}
				
				$this->render->edd_slg_social_login_inner_buttons( $redirect_url );
				
				echo '</fieldset><!--#edd_slg_social_login-->';
				
				$content .= ob_get_clean();
			}
		}
		return $content;
	}
	
	/**
	 * Adding Hooks
	 *
	 * Adding hooks for calling shortcodes.
	 *
	 * @package Easy Digital Downloads - Social Login
	 * @since 1.1.0
	 */
	public function add_hooks() {
		
		//add shortcode to show all social login buttons
		add_shortcode( 'edd_social_login', array( $this, 'edd_slg_social_login' ) );
		
	}
}
?>