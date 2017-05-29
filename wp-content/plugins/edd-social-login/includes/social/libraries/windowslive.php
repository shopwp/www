<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Windows Live Class
 *
 * Handles all Windows Live functions
 *
 * @package Easy Digital Downloads - Social Login
 * @since 1.0.0
 */

if( !class_exists( 'EDD_Slg_Social_Windowslive' ) ) {
	
	class EDD_Slg_Social_Windowslive {

		var $windowslive;
		var $windowslive_client_id;
		var $windowslive_client_secret;
		var $windowslive_redirect_uri;
		
		public function __construct() {
			
		}
		/**
		 * Initialize some user data
		 * 
		 * Handles to initialize some user
		 * data
		 * 
		 * @package Easy Digital Downloads - Social Login
		 * @since 1.0.0
		 */
		public function edd_slg_initialize_windowslive() {
			
			global $edd_options;
			
			//check facebook is enable and application id and application secret is not empty			
			if( !empty( $edd_options['edd_slg_enable_windowslive'] ) 
				&& !empty( $edd_options['edd_slg_wl_client_id'] ) && !empty($edd_options['edd_slg_wl_client_secret'] ) ) {
					
				//check $_GET['code'] is set and not empty and 
				//$_GET['eddslg'] is set and equals to windowslive
				if( isset( $_GET['code'] ) && !empty( $_GET['code'] ) 
					&& isset( $_GET['eddslg'] ) && $_GET['eddslg'] == 'windowslive' ) {
				
					$access_token_url = 'https://login.live.com/oauth20_token.srf';
		    	
					$postdata = 'code='.$_REQUEST['code'].'&client_id='.EDD_SLG_WL_CLIENT_ID.'&client_secret='.EDD_SLG_WL_CLIENT_SECRET.
								'&redirect_uri='.EDD_SLG_WL_REDIRECT_URL.'&grant_type=authorization_code';
								
					$data = $this->edd_slg_get_data_from_url( $access_token_url , $postdata, true );
					
					if( !empty( $data->access_token ) ) { 
						
						// Set the session access token
						EDD()->session->set( 'edd_slg_windowslive_access_token', $data->access_token );
						
						$accessurl = 'https://apis.live.net/v5.0/me?access_token=' . $data->access_token;
						
						//get user data from access token
						$userdata = $this->edd_slg_get_data_from_url( $accessurl );
						
						// Set the session access token
						EDD()->session->set( 'edd_slg_windowslive_user_cache', $userdata );
					}
				}
			}
		}
		
		/**
		 * Get Auth Url
		 * 
		 * Handles to Get authentication url
		 * from windows live
		 * 
		 * @package Easy Digital Downloads - Social Login
	 	 * @since 1.0.0
		 */
		public function edd_slg_get_wl_auth_url() {
			
			$wlauthurl = add_query_arg( array(	
												'client_id'		=>	EDD_SLG_WL_CLIENT_ID,
												'scope'			=>	'wl.basic+wl.emails',
												'response_type'	=>	'code',
												'redirect_uri'	=>	EDD_SLG_WL_REDIRECT_URL
											),
										'https://login.live.com/oauth20_authorize.srf' );
			return $wlauthurl;
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
			
			$ch = curl_init();
			
			// Set the cURL URL
			curl_setopt($ch, CURLOPT_URL, $url );
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			
			//IF NEED TO POST SOME FIELD && $data SHOULD NOT BE EMPTY
			if( $post == TRUE && !empty( $data ) ) {
				
				curl_setopt( $ch, CURLOPT_POST, TRUE );
				curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
				
			}
			
			$data = curl_exec($ch);
			
			// Close the cURL connection
			curl_close($ch);
			
			/*if( $post == TRUE && !empty( $data ) ) {
				
				$data	= wp_remote_retrieve_body( wp_remote_post( $url, array( 'body' => $data ) ) );
			} else {
				
				$data	= wp_remote_retrieve_body( wp_remote_get( $url ) );
			}*/
			
			// Decode the JSON request and remove the access token from it
			$data = json_decode( $data );
			
			return $data;
			
		}
		
		/**
		 * Get User Data
		 * 
		 * Handles to Get Windows Live User Data
		 * from access token
		 * 
		 * @package Easy Digital Downloads - Social Login
	 	 * @since 1.0.0
		 */
		public function edd_slg_get_windowslive_user_data() {
			
			$user_profile_data = '';
			
			$user_profile_data = EDD()->session->get( 'edd_slg_windowslive_user_cache' );
			
			return $user_profile_data;
			
		}
		
	}
	
}