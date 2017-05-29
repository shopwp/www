<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Instagram Class
 *
 * Handles all instagram functions 
 *
 * @package Easy Digital Downloads - Social Login
 * @since 1.5.0
 */
if( !class_exists( 'EDD_Slg_Social_Instagram' ) ) {
	
	class EDD_Slg_Social_Instagram {
		
		var $instagram;
		
		public function __construct() {
			
		}
		
		/**
		 * Include instagram Class
		 * 
		 * Handles to load instagram class
		 * 
		 * @package Easy Digital Downloads - Social Login
	 	 * @since 1.5.0
		 */
		public function edd_slg_load_instagram() {
			
			global $edd_options;
			
			//instagram declaration
			if( !empty( $edd_options['edd_slg_enable_instagram'] ) && !empty( $edd_options['edd_slg_inst_app_secret'] ) 
				&& !empty( $edd_options['edd_slg_inst_app_id'] ) ) {
			
				if( !class_exists( 'Instagram' ) ) { // loads the Instagram class
					
		 			require_once ( EDD_SLG_SOCIAL_LIB_DIR . '/instagram/instagram.php' );
				}
				
				// initialize class
				$this->instagram = new Instagram(array(
				  'apiKey'      => EDD_SLG_INST_APP_ID,
				  'apiSecret'   => EDD_SLG_INST_APP_SECRET,
				  'apiCallback' => EDD_SLG_INST_REDIRECT_URL
				));
				
				return true;
				
			} else {
				
				return false;
			}
			
		}
		
		/**
		 * Initializes Instagram API
		 * 
		 * @package Easy Digital Downloads - Social Login
		 * @since 1.5.0
		 */
		public function edd_slg_initialize_instagram() {			
									
			//check instagram is enable,consumer key not empty,consumer secrets not empty and app id should not empty
			if ( isset( $_GET['code'] ) && !empty($_GET['code']) && isset( $_GET['eddslg'] ) && $_GET['eddslg'] == 'instagram' ) {
				
				//load instagram class
				$instagram = $this->edd_slg_load_instagram();
				
				//check instagram class is loaded or not
				if( !$instagram ) return false;
				
				// receive OAuth token object
				$data = $this->instagram->getOAuthToken($_GET['code']);
				
				if( isset( $data->user ) && !empty( $data->user ) ) {
										
					EDD()->session->set( 'edd_slg_instagram_user_cache', $data->user );
				}
			}		
		}
						
		/**
		 * Get auth url for instagram
		 *
		 * @param Easy Digital Downloads - Social Login
		 * @since 1.5.0
		 */	
		public function edd_slg_get_instagram_auth_url () {
			
			//load instagram class
			$instagram = $this->edd_slg_load_instagram();
			
			//check instagram is loaded or not
			if( !$instagram ) return false;
			
			$url = $this->instagram->getLoginUrl();
			return $url;
		}
		 
		/**
		 * Get Instagram user's Data
		 * 
		 * @param Easy Digital Downloads - Social Login
		 * @since 1.5.0
		 */		
		public function edd_slg_get_instagram_user_data() {
					
			$user_data = '';
			
			$user_data = EDD()->session->get( 'edd_slg_instagram_user_cache' );
			
			return $user_data;
		}
	}
}
?>