<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Amazon Class
 * 
 * Handles all amazon functions
 * 
 * @package Easy Digital Downloads - Social Login
 * @since 1.7.0
 */
if( !class_exists( 'EDD_Slg_Social_Amazon' ) ) {

	class EDD_Slg_Social_Amazon {

		public $amazon, $requires_ssl;
		
		public function __construct() {
			$this->requires_ssl = true;
		}

		/**
		 * Include Amazon Class
		 * 
		 * Handles to load amazon code
		 * 
		 * @package Easy Digital Downloads - Social Login
	 	 * @since 1.7.0
		 */
		public function edd_slg_get_amazon_auth_url() {

			global $edd_options;
			
			$oauth_url	= 'https://www.amazon.com/ap/oa';
			$url		= '';			
			
			//amazon declaration
			if( !empty( $edd_options['edd_slg_enable_amazon'] ) && !empty( $edd_options['edd_slg_amazon_app_id'] ) && !empty( $edd_options['edd_slg_amazon_app_secret'] ) ) {
				
				$params = array(
					'client_id'		=> EDD_SLG_AMAZON_APP_ID,
					'redirect_uri'	=> EDD_SLG_AMAZON_REDIRECT_URL,
					'response_type'	=> 'code',
					'scope'			=> 'profile postal_code'
				);
				$url= $oauth_url.'?'.http_build_query($params, '', '&');
			}
					
			return apply_filters( 'edd_slg_get_amazon_auth_url', $url );
		}

		/**
		 * Initializes Amazon API
		 * 
		 * @package Easy Digital Downloads - Social Login
		 * @since 1.7.0
		 */
		public function edd_slg_initialize_amazon() {
			
			//check yahoo is enable,consumer key not empty,consumer secrets not empty and app id should not empty
			if ( isset( $_GET['code'] )  && isset( $_GET['eddslg'] ) && $_GET['eddslg'] == 'amazon' ) {

				$code	= $_GET['code'];
				$url	= 'https://api.amazon.com/auth/o2/token';
				$params	= array(
								'code'			=> $code,
								'client_id'		=> EDD_SLG_AMAZON_APP_ID,
								'client_secret'	=> EDD_SLG_AMAZON_APP_SECRET,
								'redirect_uri'	=> EDD_SLG_AMAZON_REDIRECT_URL,
								'grant_type'	=> 'authorization_code'
							);
				
				$query		= http_build_query($params, '', '&');
				
				$wp_http_args	= array(
										'method'      => 'POST',
										'body'        => $query,
										'headers'     => 'Content-type: application/x-www-form-urlencoded',
										'cookies'     => array(),
								);
				
				$response		= wp_remote_request($url, $wp_http_args);
				$responseData	= wp_remote_retrieve_body( $response );
				
				if( is_wp_error( $response ) ) {
					$content = $response->get_error_message();
				} else {
					
					$responseData	= json_decode( $responseData );
					
					if( isset( $responseData->access_token ) && !empty( $responseData->access_token ) ) {
						$token	= $responseData->access_token;
						$_SESSION['edd_slg_amazon_user_cache']	= $this->edd_slg_get_amazon_profile_data( $token );						
					}
				}
			}
		}
		
		/**
		 * Get USer Profile Information
		 * 
		 * Handle to get user profile information
		 * 
		 * @package Easy Digital Downloads - Social Login
		 * @since 1.7.0
		 */
		public function edd_slg_get_amazon_profile_data( $token ) {
			
			$profile_data	= array();
			
			if( isset( $token ) && !empty( $token ) ) { // if access token is not empty
				
				$url	= 'https://api.amazon.com/user/profile';
				$args	= array(
									'headers'	=> array(
									'Authorization' => 'bearer ' . $token
								)
							);
				
				$result			= wp_remote_retrieve_body( wp_remote_get( $url, $args ) );
				$profile_data	= json_decode( $result );
			}
			
			return apply_filters( 'edd_slg_get_amazon_profile_data', $profile_data, $token );
		}
		
		/**
		 * Get USer Profile Information
		 *  
		 * @package Easy Digital Downloads - Social Login
		 * @since 1.7.0
		 */
		public function edd_slg_get_amazon_user_data() {
			
			$user_profile_data	= '';
			$user_profile_data	= isset( $_SESSION['edd_slg_amazon_user_cache'] ) ? $_SESSION['edd_slg_amazon_user_cache'] : array();
			
			return apply_filters( 'edd_slg_get_amazon_user_data', $user_profile_data );
		}
	}
}