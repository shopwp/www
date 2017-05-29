<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Twitter Class
 *
 * Handles all twitter functions 
 *
 * @package Easy Digital Downloads - Social Login
 * @since 1.0.0
 */
if( !class_exists( 'EDD_Slg_Social_Twitter' ) ) {
	
	class EDD_Slg_Social_Twitter {
		
		var $twitter;
		
		public function __construct(){
			
		}
		
		/**
		 * Include Twitter Class
		 * 
		 * Handles to load twitter class
		 * 
		 * @package Easy Digital Downloads - Social Login
	 	 * @since 1.0.0
		 */
		public function edd_slg_load_twitter() {
			
			global $edd_options;
			
			//twitter declaration
			if( !empty( $edd_options['edd_slg_enable_twitter'] )
				 && !empty( $edd_options['edd_slg_tw_consumer_key'] ) && !empty( $edd_options['edd_slg_tw_consumer_secret'] ) ) {
			
				if( !class_exists( 'TwitterOAuth' ) ) { // loads the Twitter class
					require_once ( EDD_SLG_SOCIAL_LIB_DIR . '/twitter/twitteroauth.php' ); 
				}
				
				// Twitter Object
				$this->twitter = new TwitterOAuth( EDD_SLG_TW_CONSUMER_KEY, EDD_SLG_TW_CONSUMER_SECRET );
				
				return true;
				
			} else {
	 		
				return false;
			}	
			
		}
		
		/**
		 * Initializes Twitter API
		 * 
		 * @package Easy Digital Downloads - Social Login
		 * @since 1.0.0
		 */
		function edd_slg_initialize_twitter() {
			
			//when user is going to logged in in twitter and verified successfully session will create
			if ( isset( $_REQUEST['oauth_verifier'] ) && isset( $_REQUEST['oauth_token'] ) ) {
			
				//load twitter class
				$twitter = $this->edd_slg_load_twitter();
			
				//check twitter class is loaded or not
				if( !$twitter ) return false;
				
				$oauth_token = EDD()->session->get( 'edd_slg_twt_oauth_token' );
				$oauth_token_secret = EDD()->session->get( 'edd_slg_twt_oauth_token_secret' );
				
				if( isset( $oauth_token ) && $oauth_token == $_REQUEST['oauth_token'] ) {
						
					$this->twitter = new TwitterOAuth( EDD_SLG_TW_CONSUMER_KEY, EDD_SLG_TW_CONSUMER_SECRET, $oauth_token, $oauth_token_secret );
					
					// Request access tokens from twitter
					$edd_slg_tw_access_token = $this->twitter->getAccessToken($_REQUEST['oauth_verifier']);
					
					//session create for access token & secrets		
					EDD()->session->set( 'edd_slg_twt_oauth_token', $edd_slg_tw_access_token['oauth_token'] );
					EDD()->session->set( 'edd_slg_twt_oauth_token_secret', $edd_slg_tw_access_token['oauth_token_secret'] );
					
					//session for verifier
					$verifier['oauth_verifier'] = $_REQUEST['oauth_verifier'];
					//EDD()->session->set( 'edd_slg_twt_user_cache', $verifier );
					
					$_SESSION[ 'edd_slg_twt_user_cache' ] = $verifier;
					
					//getting user data from twitter
					$response = $this->twitter->get('account/verify_credentials');
					
					//if user data get successfully
					if ( $response->id_str ) {
						
						$data['user'] = $response;
						
						//all data will assign to a session
						EDD()->session->set( 'edd_slg_twt_user_cache', $data );	
						
					}
				}
			}
		}
		
		/**
		 * Get auth url for twitter
		 *
		 * @param Easy Digital Downloads - Social Login
		 * @since 1.0.0
		 */	
		public function edd_slg_get_twitter_auth_url () {
			
			// Save temporary credentials to session.
			// Get temporary credentials.
			global $post;
			
			//load twitter class
			$twitter = $this->edd_slg_load_twitter();
			
			//check twitter class is loaded or not
			if( !$twitter ) return false;
			
			$request_token = $this->twitter->getRequestToken( edd_slg_get_current_page_url()  ); // get_permalink( $post->ID )
		
			// If last connection failed don't display authorization link. 
			switch( $this->twitter->http_code ) { //
				
			  case 200:
			  	
						//$edd_slg_twt_oauth_token = EDD()->session->get( 'edd_slg_twt_oauth_token' );
						
						//if( empty( $edd_slg_twt_oauth_token ) ) {
						
					    	// Build authorize URL and redirect user to Twitter. 
					    	// Save temporary credentials to session.
					    	EDD()->session->set( 'edd_slg_twt_oauth_token', $request_token['oauth_token'] );
					    	EDD()->session->set( 'edd_slg_twt_oauth_token_secret', $request_token['oauth_token_secret'] );
						//}
						
				    	$token = $request_token['oauth_token'];
						$url = $this->twitter->getAuthorizeURL( $token );
						
				    	break;
			  default:
					    // Show notification if something went wrong.
					    $url = '';
			}		
			return $url;
		}	
		
		/**
		 * Get Twitter user's Data
		 * 
		 * @param Easy Digital Downloads - Social Login
		 * @since 1.0.0
		 */		
		public function edd_slg_get_twitter_user_data() {
		
			$user_profile_data = '';
			
			$user_cache = EDD()->session->get( 'edd_slg_twt_user_cache' );
			
			$user_profile_data = $user_cache['user'];
			
			return $user_profile_data;
		}
		
	}
	
}
?>