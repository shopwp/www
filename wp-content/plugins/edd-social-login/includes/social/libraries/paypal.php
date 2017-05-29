<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Paypal Class
 * 
 * Handles all paypal functions
 * 
 * @package Easy Digital Downloads - Social Login
 * @since 1.7.0
 */
if( !class_exists( 'EDD_Slg_Social_Paypal' ) ) {
	
	class EDD_Slg_Social_Paypal {
		
		public $api_endpoint, $auth_endpoint, $paypalenvironment;
		
		// live authentication endpoint
		const LIVE_AUTH_ENDPOINT = 'https://www.paypal.com/webapps/auth/protocol/openidconnect/v1/authorize';
		
		// sandbox authentication endpoint
		const SANDBOX_AUTH_ENDPOINT = 'https://www.sandbox.paypal.com/webapps/auth/protocol/openidconnect/v1/authorize';
		
		// live API endpoint
		const LIVE_API_ENDPOINT = 'https://api.paypal.com/v1/identity/openidconnect';
		
		// sandbox API endpoint
		const SANDBOX_API_ENDPOINT = 'https://api.sandbox.paypal.com/v1/identity/openidconnect';
		
		public function __construct() {
			
			$this->paypalenvironment= EDD_SLG_PAYPAL_ENVIRONMENT;
			
			$this->api_endpoint		= ( 'live' == $this->paypalenvironment ) ? self::LIVE_API_ENDPOINT : self::SANDBOX_API_ENDPOINT;
			$this->auth_endpoint	= ( 'live' == $this->paypalenvironment ) ? self::LIVE_AUTH_ENDPOINT : self::SANDBOX_AUTH_ENDPOINT;	
			
		}
		
		/**
		 * Get Paypal Authentication URL
		 * 
		 * Handles to get paypal authentication URL
		 * 
		 * @package Easy Digital Downloads - Social Login
	 	 * @since 1.7.0
		 */
		public function edd_slg_get_paypal_auth_url() {
			
			global $edd_options;
			
			$url	= '';
			
			//paypal declaration
			if( !empty( $edd_options['edd_slg_enable_paypal'] ) && !empty( $edd_options['edd_slg_paypal_app_id'] ) && !empty( $edd_options['edd_slg_paypal_app_secret'] ) ) {
				
				$params = array(
					'client_id'		=> EDD_SLG_PAYPAL_APP_ID,
					'redirect_uri'	=> EDD_SLG_PAYPAL_REDIRECT_URL,
					'response_type'	=> 'code',
					'scope'			=> 'openid profile email'
				);
				
				$url	= $this->auth_endpoint . '?' . http_build_query( $params, '', '&' );
			}
			
			return apply_filters( 'edd_slg_get_paypal_auth_url', $url );
		}
		
		/**
		 * Initializes Paypal API
		 * 
		 * @package Easy Digital Downloads - Social Login
		 * @since 1.7.0
		 */
		public function edd_slg_initialize_paypal() {
			
			if ( isset( $_GET['code'] )  && isset( $_GET['eddslg'] ) && $_GET['eddslg'] == 'paypal' ) {
				
				$response = array();
				
				$code	= $_GET['code'];
				
				$params	= array(
								'code'			=> $code,
								'client_id'		=> EDD_SLG_PAYPAL_APP_ID,
								'client_secret'	=> EDD_SLG_PAYPAL_APP_SECRET,
								'redirect_uri'	=> EDD_SLG_PAYPAL_REDIRECT_URL,
								'grant_type'	=> 'authorization_code'
							);
				
				$query			= "{$this->api_endpoint}/tokenservice".'?'.http_build_query($params,'','&');
				
				$response 		= apply_filters( 'woo_slg_social_paypal_response', $response, $query, $args='' );
				
				if( empty( $response ) ) {
					$response		= wp_remote_request( $query );				
				}
				if( is_wp_error( $response ) ) {
					
					$content = $response->get_error_message();
				} else {
					
					$responseData	= json_decode( $response );					
					if( isset( $responseData->access_token ) && !empty( $responseData->access_token ) ) {
						$token	= $responseData->access_token;
						$_SESSION['edd_slg_paypal_user_cache']	= $this->edd_slg_get_paypal_profile_data( $token );
					}
				}
			}
		}
		
		/**
		 * Get User Profile Information
		 * 
		 * Handle to get user profile information
		 * 
		 * @package Easy Digital Downloads - Social Login
		 * @since 1.7.0
		 */
		public function edd_slg_get_paypal_profile_data( $token ) {
			
			$profile_data	= $result = array();
			
			if( isset( $token ) && !empty( $token ) ) { // if access token is not empty
				
				$url	= "{$this->api_endpoint}/userinfo".'?'.http_build_query(array( 'schema' => 'openid' ),'','&');
				
				$args	= array('headers'	=> array(
										'Authorization' => 'Bearer ' . $token
									)
								);
				$result = apply_filters( 'edd_slg_social_paypal_response', $result, $url, $token );
				
				if( empty( $result) ) {
					$result	= wp_remote_request( $url, $args );
				}
				
				if( is_wp_error( $result ) ) {
					$content = $result->get_error_message();
				} else {
					$profile_data	= json_decode( $result );
				}
			}
			
			return apply_filters( 'edd_slg_get_paypal_profile_data', $profile_data );
		}
		
		/**
		 * Get User Profile Information
		 * 
		 * @package Easy Digital Downloads - Social Login
		 * @since 1.7.0
		 */
		public function edd_slg_get_paypal_user_data() {
			
			$user_profile_data	= '';
			$user_profile_data	= isset( $_SESSION['edd_slg_paypal_user_cache'] ) ? $_SESSION['edd_slg_paypal_user_cache'] : array();
			return apply_filters( 'edd_slg_get_paypal_user_data', $user_profile_data );
		}
	}
}