<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Foursquare Class
 *
 * Handles all Foursquare functions 
 *
 * @package Easy Digital Downloads - Social Login
 * @since 1.0.0
 */
if( !class_exists( 'EDD_Slg_Social_Foursquare' ) ) {
	
	class EDD_Slg_Social_Foursquare{

		var $foursquare;
		
		public function __construct(){
			
		}
		/**
		 * Load Foursquare Class
		 * 
		 * Handles to load foursquare social api class
		 * 
		 * @package Easy Digital Downloads - Social Login
		 * @since 1.0.0
		 */
		public function edd_slg_load_foursquare() {
			
			global $edd_options;
			
			//foursquare declaration
			if( !empty( $edd_options['edd_slg_enable_foursquare'] ) 
				&& !empty( $edd_options['edd_slg_fs_client_id'] ) && !empty( $edd_options['edd_slg_fs_client_secret'] ) ) {
			
				if( !class_exists( 'socialmedia_oauth_connect' ) ) {
					require_once ( EDD_SLG_SOCIAL_LIB_DIR . '/foursquare/socialmedia_oauth_connect.php' );
				}
				
				// Foursquare Object
				$this->foursquare = new socialmedia_oauth_connect();
						
				$this->foursquare->provider		= "Foursquare";
				$this->foursquare->client_id 	= EDD_SLG_FS_CLIENT_ID;
				$this->foursquare->client_secret= EDD_SLG_FS_CLIENT_SECRET;
				$this->foursquare->scope		= "";
				$this->foursquare->redirect_uri	= EDD_SLG_FS_REDIRECT_URL;
				$this->foursquare->Initialize();
				
				return true;
				
			} else {
				
				return false;
			}
			
		}
		/**
		 * Initializes Foursquare API
		 * 
		 * @package Easy Digital Downloads - Social Login
		 * @since 1.0.0
		 */
		function edd_slg_initialize_foursquare() {
			
			global $edd_options;
			
			//check code is set and wpsocial is set and it is foursuqare then execute code
			if ( isset( $_GET['code'] ) && !empty($_GET['code'] ) 
				&& isset( $_GET['eddslg'] ) && $_GET['eddslg'] == 'foursquare' ) {
			
				//load foursquare class	
			    $foursquare = $this->edd_slg_load_foursquare();
			
				//check foursquare class is loaded or not
				if( !$foursquare ) return false;
					
				$this->foursquare->code = $_GET['code'];
				$user_profile_data = json_decode( $this->foursquare->getUserProfile() );
				
				if( isset( $user_profile_data->response ) && isset( $user_profile_data->response->user ) && !empty( $user_profile_data->response->user ) ) {
				
					EDD()->session->set( 'edd_slg_foursquare_user_cache', $user_profile_data->response->user );
				}
			}
		}
		/**
		 * Get auth url for foursquare
		 *
		 * @param Easy Digital Downloads - Social Login
		 * @since 1.0.0
		 */	
		public function edd_slg_get_foursquare_auth_url () {
			
			//load foursquare class
			$foursquare = $this->edd_slg_load_foursquare();
			
			$url = '';
			
			//check foursquare class is loaded or not
			if( !$foursquare ) return false;
			
				$url = $this->foursquare->Authorize();
				
			return $url;
		}	
		
		/**
		 * Get Foursquare user's Data
		 * 
		 * @param Easy Digital Downloads - Social Login
		 * @since 1.0.0
		 */		
		public function edd_slg_get_foursquare_user_data() {
		
			$user_profile_data = '';
			
			$user_profile_data = EDD()->session->get( 'edd_slg_foursquare_user_cache' );
			
			return $user_profile_data;
		}
		
		/**
		 * User Image
		 *
		 * Getting the the profile image of the connected Foursquare user.
		 *
		 * @package Easy Digital Downloads - Social Login
		 * @since 1.5.1
		 */
		public function edd_slg_get_foursquare_profile_picture( $args = array(), $user ) {
			
			// Taking some defaults
			$url = '';
			
			if( isset( $args['size'] ) && !empty( $args['size'] ) ) {
				$size = $args['size'];
			} else {
				$size = '64';
			}
			
			// If user is empty then get from the session
			if( empty($user) ) {
				$user = $this->edd_slg_get_foursquare_user_data();
			}
			
			$size = apply_filters( 'edd_slg_foursquare_profile_picture_size', $size, $user );
			
			if(!empty($user->photo->prefix) && !empty($user->photo->suffix)) {
				$url = $user->photo->prefix . $size . 'x' . $size . $user->photo->suffix;
			}
			
			return $url;
		}
		
	}
}
?>