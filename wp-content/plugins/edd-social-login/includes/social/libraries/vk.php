<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * VK Class
 *
 * Handles all vk functions 
 *
 * @package Easy Digital Downloads - Social Login
 * @since 1.3.0
 */
if( !class_exists( 'EDD_Slg_Social_VK' ) ) {
	
	class EDD_Slg_Social_VK {
		
		var $vk;
		
		public function __construct() {
			
		}
		
		/**
		 * Include VK Class
		 * 
		 * Handles to load vk class
		 * 
		 * @package Easy Digital Downloads - Social Login
	 	 * @since 1.3.0
		 */
		public function edd_slg_load_vk() {
			
			global $edd_options;
			
			//vk declaration
			if( !empty( $edd_options['edd_slg_enable_vk'] ) && !empty( $edd_options['edd_slg_vk_app_secret'] ) 
				&& !empty( $edd_options['edd_slg_vk_app_id'] ) ) {
			
				// loads the class
				require_once ( EDD_SLG_SOCIAL_LIB_DIR . '/vk/classes/VkPhpSdk.php' ); 
				require_once ( EDD_SLG_SOCIAL_LIB_DIR . '/vk/classes/Oauth2Proxy.php' ); 
								
				
			   /* if( is_ssl() ) { // Check page is secure
			     	$edd_domain_url = str_replace( 'http://', 'https://', $edd_domain_url );
			    }*/
			    
			    // VK Object
			    $this->vk = new Oauth2Proxy(
								    EDD_SLG_VK_APP_ID, // app id
								    EDD_SLG_VK_APP_SECRET, // app secret
								    'https://oauth.vk.com/access_token', // access token url
								    'https://oauth.vk.com/authorize', // dialog uri
								    'code', // response type
								    EDD_SLG_VK_REDIRECT_URL, // redirect url
									'offline,notify,friends,photos,audio,video,email' // scope
							);
				
				return true;	
			
			} else {
				
				return false;
			}
			
		}
		
		/**
		 * Initializes VK API
		 * 
		 * @package Easy Digital Downloads - Social Login
		 * @since 1.3.0
		 */
		public function edd_slg_initialize_vk() {			
									
			//vk.com authentcation code
			if( isset( $_REQUEST['state'] ) && !empty( $_REQUEST['state'] )  && $_REQUEST['state'] == $_SESSION['vkPhpSdkstate'] 
				&& isset( $_GET['eddslg'] ) && $_GET['eddslg'] == 'vk' ) { //  
													
				//load vk class
				$vk = $this->edd_slg_load_vk();		
				
				//check vk class is loaded or not
				if( !$vk ) return false;
				
				// Authentication URL
				$vk_auth_url	= $this->vk->_accessTokenUrl.'?client_id='.$this->vk->_clientId
									.'&client_secret='.$this->vk->_clientSecret.'&code='.$_REQUEST['code']
									.'&redirect_uri='.$this->vk->_redirectUri;
				
				$auth_json = $this->edd_slg_get_data_from_url( $vk_auth_url );//json_decode( $this->vk->_authJson );
				$auth_json = $this->vk->object_to_array( $auth_json );
				
				if( !empty( $auth_json ) && !empty( $auth_json['access_token'] ) ) {
					
					$vkPhpSdk = new VkPhpSdk();
					
					$vkPhpSdk->setAccessToken( $auth_json['access_token'] );
					$vkPhpSdk->setUserId( $auth_json['user_id'] );
				
					// API call - get profile
					$user_profile_data	= $vkPhpSdk->api( 'getProfiles', array(
																'uids' => $vkPhpSdk->getUserId(),
																'fields' => 'uid, first_name, last_name, nickname, screen_name, photo_big, email',
															)
														);
					
					$user_profile_data	= isset( $user_profile_data['response'][0] ) ? $user_profile_data['response'][0] : array();
					
					$user_data_session = EDD()->session->get( 'edd_slg_vk_user_cache' );							
					
					//Add email field to array if found email address field
					if(isset($user_data_session['email']) && !empty( $user_data_session['email'] ) ) {
						
						$user_profile_data['email']	= $auth_json['email'];
					}
					
					$auth_json	= array_merge( $auth_json, $user_profile_data );					
					
					//$_SESSION['edd_slg_vk_user_cache'] = $auth_json;
					EDD()->session->set( 'edd_slg_vk_user_cache' , $auth_json );
					
				}					
			}			
		}
		
		/**
		 * Get Data From URL
		 * 
		 * Handels to return data from url 
		 * via calling CURL
		 * 
		 * @package Easy Digital Downloads - Social Login
		 * @since 1.0.0
		 */
		public function edd_slg_get_data_from_url( $url, $data = array(), $post = false ) {
			
			/*$ch = curl_init();
			
			// Set the cURL URL
			curl_setopt( $ch, CURLOPT_URL, $url );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, TRUE );
			
			//IF NEED TO POST SOME FIELD && $data SHOULD NOT BE EMPTY
			if( $post == TRUE && !empty( $data ) ) {
				curl_setopt( $ch, CURLOPT_POST, TRUE );
				curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
			}
			$result = curl_exec($ch);
			// Close the cURL connection
			curl_close( $ch );*/
			
			$result	= wp_remote_retrieve_body( wp_remote_get( $url ) );
			
			$this->vk->_authJson	= $result;
			
			// Decode the JSON request and remove the access token from it
			$data = json_decode( $result );
			
			return $data;
			
		}
		
		/**
		 * Get auth url for vk
		 *
		 * @param Easy Digital Downloads - Social Login
		 * @since 1.3.0
		 */	
		public function edd_slg_get_vk_auth_url () {
			
			//load vk class
			$vk = $this->edd_slg_load_vk();
			
			//check vk is loaded or not
			if( !$vk ) return false;
			
			if( $this->vk ) {
				
				return $this->vk->authorize();
			}
		}
		 
		/**
		 * Get VK user's Data
		 * 
		 * @param Easy Digital Downloads - Social Login
		 * @since 1.3.0
		 */		
		public function edd_slg_get_vk_user_data() {
					
			$user_data = '';
			
			$user_data = EDD()->session->get( 'edd_slg_vk_user_cache' );
			
			return $user_data;
		}
	}
}
?>