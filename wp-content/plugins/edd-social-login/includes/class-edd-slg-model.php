<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Model Class
 * 
 * Handles generic plugin functionality.
 * 
 * @package Easy Digital Downloads - Social Login
 * @since 1.0.0
 */
class EDD_Slg_Model {
	
	public $foursquare;
	
	public function __construct() {
		
		global $edd_slg_social_foursquare;
		
		$this->foursquare = $edd_slg_social_foursquare;
		
	}
	
	/**
	 * Escape Tags & Slashes
	 * 
	 * Handles escapping the slashes and tags
	 * 
	 * @package  Easy Digital Downloads - Social Login
	 * @since 1.0.0
	 */
	public function edd_slg_escape_attr($data){
		return esc_attr(stripslashes($data));
	}
	
	/**
	 * Strip Slashes From Array
	 * 
	 * @package Easy Digital Downloads - Social Login
	 * @since 1.0.0
	 */
	public function edd_slg_escape_slashes_deep($data = array(),$flag=false) {
		
		if($flag != true) {
			$data = $this->edd_slg_nohtml_kses($data);
		}
		$data = stripslashes_deep($data);
		return $data;
	}
	
	/**
	 * Strip Html Tags
	 * 
	 * It will sanitize text input (strip html tags, and escape characters)
	 * 
	 * @package Easy Digital Downloads - Social Login
	 * @since 1.0.0
	 */
	public function edd_slg_nohtml_kses($data = array()) {
		
		if ( is_array($data) ) {
			
			$data = array_map(array($this,'edd_slg_nohtml_kses'), $data);
			
		} elseif ( is_string( $data ) ) {
			
			$data = wp_filter_nohtml_kses($data);
		}
		
		return $data;
	}
	
	/**
	 * Convert Object To Array
	 * 
	 * Converting Object Type Data To Array Type
	 * 
	 * @package Easy Digital Downloads - Social Login
	 * @since 1.0.0
	 */
	public function edd_slg_object_to_array( $result ) {
	    $array = array();
	    foreach( $result as $key => $value ) {	
	        if( is_object( $value ) ) {
				$array[$key]=$this->edd_slg_object_to_array($value);
	        } else {
				$array[$key]=$value;
	        }
	    }
	    return $array;
	}
	
	/**
	 * Create User
	 * 
	 * Function to add connected users to the WordPress users database
	 * and add the role subscriber
	 * 
	 * @package Easy Digital Downloads - Social Login
	 * @since 1.0.0
	 */
	public function edd_slg_add_wp_user( $criteria ) {
		
		global $wp_version, $edd_options;
		
		$usermeta_prefix	= EDD_SLG_USER_META_PREFIX;
		
		$username	= $this->edd_slg_create_username( $criteria );
		
		$name		= $criteria['name'];
		$first_name	= $criteria['first_name'];
		$last_name	= $criteria['last_name'];
		$password	= wp_generate_password(12, false);
		$email		= $criteria['email'];
		$wp_id		= 0;
		
		//create the WordPress user
		if ( version_compare($wp_version, '3.1', '<') ) {
			require_once( ABSPATH . WPINC . '/registration.php' );
		}
		
		//check user id is exist or not
		if ( $this->edd_slg_check_user_exists( $criteria ) == false ) {
			
			$wp_id = wp_create_user( $username, $password, $email );
			
			if( !empty( $wp_id ) ) { //if user is created then update some data
				$role = get_option( 'default_role' );
				$user = new WP_User( $wp_id );
				$user->set_role( $role );
				
				if( isset($edd_options['edd_slg_enable_notification']) && !empty($edd_options['edd_slg_enable_notification']) ) { // check enable email notification from settings
					wp_new_user_notification( $wp_id, null, apply_filters('edd_slg_new_user_notify_to', 'both') );
				}
			}
			
			//Update unique id to usermeta
			update_user_meta( $wp_id, $usermeta_prefix.'unique_id', $criteria['id'] );
			
		} else {
			
			//get user from email or username
			$userdata = $this->edd_slg_get_user_by( $criteria );
			
			if( !empty( $userdata ) ) { //check user is exit or not
				$wp_id = isset( $userdata->ID ) ? $userdata->ID : '';
			}
		}
		return $wp_id;
	}
	
	/**
	 * Get Social Connected Users Count
	 * 
	 * Handles to return connected user counts
	 * 
	 * @package Easy Digital Downloads - Social Login
	 * @since 1.0.0
	 */
	public function edd_slg_social_get_users( $args = array() ) {
		
		$userargs = array();
		$metausr1 = array();
		
		if( isset( $args['network'] ) && !empty( $args['network'] ) ) { //check network is set or not
			$metausr1['key'] = 'edd_slg_social_user_connect_via';
			$metausr1['value'] = $args['network'];
		}
		
		if( !empty($metausr1) ) { //meta query
			$userargs['meta_query'] = array( $metausr1 );
		}
		
		//get users data
		$result = new WP_User_Query($userargs);
		
		if ( isset( $args['getcount'] ) && !empty( $args['getcount'] ) ) { //get count of users
			$users = $result->total_users;
		} else {
			//retrived data is in object format so assign that data to array for listing
			$users = $this->edd_slg_object_to_array($users->results);
		}
		
		return $users;
	}
	
	/**
	 * Create User Name for VK.com / Instagram
	 * 
	 * Function to check type is vk/instagram then create user name based on user id.
	 * 
	 * @package Easy Digital Downloads - Social Login
	 * @since 1.3.0
	 */
	public function edd_slg_create_username( $criteria ) {
		
		global $edd_options;
		
		//Initilize username
		$username	= '';
		
		//Get base of username
		$edd_user_base	= isset( $edd_options['edd_slg_base_reg_username'] ) ? $edd_options['edd_slg_base_reg_username'] : '';
		
		switch( $edd_user_base ) {
			
			case 'realname' :
				
				//Get first name
				$first_name	= isset( $criteria['first_name'] ) ? strtolower( $criteria['first_name'] ) : '';
				//Get last name
				$last_name	= isset( $criteria['last_name'] ) ? strtolower( $criteria['last_name'] ) : '';
				
				//Get username using fname and lname
				$username	= $this->edd_slg_username_by_fname_lname( $first_name, $last_name );
				break;
				
			case 'emailbased' : 
				
				//Get user email
				$user_email	= isset( $criteria['email'] ) ? $criteria['email'] : '';
				
				//Create username using email
				$username	= $this->edd_slg_username_by_email( $user_email );
				break;
				
			default : 
				break;
		}
		
		if( empty( $username ) ) {//If username get empty
			
			//Get username prefix
			$prefix	= EDD_SLG_USER_PREFIX;
			
			if( $criteria['type'] == 'vk' || $criteria['type'] == 'instagram') { // if service is vk.com OR instagram then create username with unique id
				$username	= $prefix . $criteria['id'];
			} else { // else create create username with random string
				$username	= $prefix . wp_rand( 100, 9999999 );
			}
		}
		
		//Apply filter to modify username logic
		$username	= apply_filters( 'edd_slg_social_username', $username, $criteria );
		
		//Assign username to temporary variable
		$temp_user_name	= $username;
		
		//Make sure the name is unique: if we've already got a user with this name, append a number to it.
		$counter	= 1;
		if ( username_exists( $temp_user_name ) ) {//If username is exist
			
			do {
				$username	= $temp_user_name;
				$counter++;
				$username	= $username . $counter;
			} while ( username_exists( $username ) );
		} else {
			
			$username	= $temp_user_name;
		}
		
		return $username;
	}
	
	/**
	 * Check User Exists
	 * 
	 * Function to check user is exists or not based on either username or email
	 * for VK and 
	 * for Instragram(only by username, because it can't contain email)
	 *
	 * @package Easy Digital Downloads - Social Login
	 * @since 1.0.0
	 */
	public function edd_slg_check_user_exists( $criteria ) {
		
		$prefix = EDD_SLG_USER_PREFIX;
		
		if ( ( $criteria['type'] == 'vk' && empty( $criteria['email'] ) ) || ( $criteria['type'] == 'instagram') ) {
			
			//return username_exists( $prefix.$criteria['id'] );
			return $this->edd_slg_user_meta_exists( $criteria['id'] );
			
		} else {
			
			return email_exists( $criteria['email'] );
			
		}
	}
	
	/**
	 * User exist from meta
	 * 
	 * @package Easy Digital Downloads - Social Login
	 * @since 1.5.0
	 */
	public function edd_slg_user_meta_exists( $criteria_id = '', $only_id = true ) {
		
		//Usermeta prefix
		$user_meta_prefix	= EDD_SLG_USER_META_PREFIX;
		
		$user	= array();
		
		//Get user by meta
		$users	= get_users(
							array(
								'meta_key'		=> $user_meta_prefix . 'unique_id',
								'meta_value'	=> $criteria_id,
								'number'		=> 1,
								'count_total'	=> false
							)
						);
		
		if( !empty( $users ) ) {//If user not empty
			$user	= reset( $users );
		}
		
		return isset( $user->ID ) ? $user->ID : false;
	}
	
	/**
	 * User Data By MetaData
	 * 
	 * @package Easy Digital Downloads - Social Login
	 * @since 1.5.0
	 */
	public function edd_slg_get_user_by_meta( $criteria_id ) {
		
		//Usermeta prefix
		$user_meta_prefix	= EDD_SLG_USER_META_PREFIX;
		
		$user	= array();
		
		//Get user by meta
		$users	= get_users(
							array(
								'meta_key'		=> $user_meta_prefix . 'unique_id',
								'meta_value'	=> $criteria_id,
								'number'		=> 1,
								'count_total'	=> false
							)
						);
		
		if( !empty( $users ) ) {//If user not empty
			$user	= reset( $users );
		}
		
		return $user;
		
	}
	
	/**
	 * Get User by email or username
	 * 
	 * Function to get user by email or username
	 * for VK and 
	 * for Instragram(only by username, because it can't contain email)
	 * 
	 * @package Easy Digital Downloads - Social Login
	 * @since 1.0.0
	 */
	public function edd_slg_get_user_by( $criteria ) {
		
		$prefix = EDD_SLG_USER_PREFIX;
		
		if ( ($criteria['type'] == 'vk' && empty( $criteria['email'] ) ) || ( $criteria['type'] == 'instagram' ) ) {
			
			//return get_user_by('login',$prefix.$criteria['id']);
			return $this->edd_slg_get_user_by_meta( $criteria['id'] );
		} else {
			
			return get_user_by( 'email', $criteria['email'] );
		}
	}
	
	/**
	 * Add plugin section in extension settings
	 *
	 * @package Easy Digital Downloads - Social Login
	 * @since 1.7.2
	 */
	 public function edd_slg_settings_section( $sections ) {
  	 	$sections['eddslg'] = __( 'Social Login', 'eddslg' );
 	 	return $sections;
	 }
	
	/**
	 * Register Settings
	 * 
	 * Handels to add settings in settings page
	 * 
	 * @package Easy Digital Downloads - Social Login
	 * @since 1.0.0
	 */
	  public function edd_slg_settings( $settings ) {
		
		$success_message = '';
		// Display success message when click reset social setting
		if( isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] == 'edd_slg_reset' ) {
			$success_message = '<div class="updated" id="message"><p><strong>' . __( 'Social login settings reset successfully.','eddslg' ) . '</strong></p></div>';
		}
		
		$select_fblanguage = array( 'en_US' => __( 'English', 'eddslg' ), 'af_ZA' => __( 'Afrikaans', 'eddslg' ), 'sq_AL' => __( 'Albanian', 'eddslg' ), 'ar_AR' => __( 'Arabic', 'eddslg' ), 'hy_AM' => __( 'Armenian', 'eddslg' ), 'eu_ES' => __( 'Basque', 'eddslg' ), 'be_BY' => __( 'Belarusian', 'eddslg' ), 'bn_IN' => __( 'Bengali', 'eddslg' ), 'bs_BA' => __( 'Bosanski', 'eddslg' ), 'bg_BG' => __( 'Bulgarian', 'eddslg' ), 'ca_ES' => __( 'Catalan', 'eddslg' ), 'zh_CN' => __( 'Chinese', 'eddslg' ), 'cs_CZ' => __( 'Czech', 'eddslg' ), 'da_DK' => __( 'Danish', 'eddslg' ), 'fy_NL' => __( 'Dutch', 'eddslg' ), 'eo_EO' => __( 'Esperanto', 'eddslg' ), 'et_EE' => __( 'Estonian', 'eddslg' ), 'et_EE' => __( 'Estonian', 'eddslg' ), 'fi_FI' => __( 'Finnish', 'eddslg' ), 'fo_FO' => __( 'Faroese', 'eddslg' ), 'tl_PH' => __( 'Filipino', 'eddslg' ), 'fr_FR' => __( 'French', 'eddslg' ), 'gl_ES' => __( 'Galician', 'eddslg' ), 'ka_GE' => __( 'Georgian', 'eddslg' ), 'de_DE' => __( 'German', 'eddslg' ), 'zh_CN' => __( 'Greek', 'eddslg' ), 'he_IL' => __( 'Hebrew', 'eddslg' ), 'hi_IN' => __( 'Hindi', 'eddslg' ), 'hr_HR' => __( 'Hrvatski', 'eddslg' ), 'hu_HU' => __( 'Hungarian', 'eddslg' ), 'is_IS' => __( 'Icelandic', 'eddslg' ), 'id_ID' => __( 'Indonesian', 'eddslg' ), 'ga_IE' => __( 'Irish', 'eddslg' ), 'it_IT' => __( 'Italian', 'eddslg' ), 'ja_JP' => __( 'Japanese', 'eddslg' ), 'ko_KR' => __( 'Korean', 'eddslg' ), 'ku_TR' => __( 'Kurdish', 'eddslg' ), 'la_VA' => __( 'Latin', 'eddslg' ), 'lv_LV' => __( 'Latvian', 'eddslg' ), 'fb_LT' => __( 'Leet Speak', 'eddslg' ), 'lt_LT' => __( 'Lithuanian', 'eddslg' ), 'mk_MK' => __( 'Macedonian', 'eddslg' ), 'ms_MY' => __( 'Malay', 'eddslg' ), 'ml_IN' => __( 'Malayalam', 'eddslg' ), 'nl_NL' => __( 'Nederlands', 'eddslg' ), 'ne_NP' => __( 'Nepali', 'eddslg' ), 'nb_NO' => __( 'Norwegian', 'eddslg' ), 'ps_AF' => __( 'Pashto', 'eddslg' ), 'fa_IR' => __( 'Persian', 'eddslg' ), 'pl_PL' => __( 'Polish', 'eddslg' ), 'pt_PT' => __( 'Portugese', 'eddslg' ), 'pa_IN' => __( 'Punjabi', 'eddslg' ), 'ro_RO' => __( 'Romanian', 'eddslg' ), 'ru_RU' => __( 'Russian', 'eddslg' ), 'sk_SK' => __( 'Slovak', 'eddslg' ), 'sl_SI' => __( 'Slovenian', 'eddslg' ), 'es_LA' => __( 'Spanish', 'eddslg' ), 'sr_RS' => __( 'Srpski', 'eddslg' ), 'sw_KE' => __( 'Swahili', 'eddslg' ), 'sv_SE' => __( 'Swedish', 'eddslg' ), 'ta_IN' => __( 'Tamil', 'eddslg' ), 'te_IN' => __( 'Telugu', 'eddslg' ), 'th_TH' => __( 'Thai', 'eddslg' ), 'tr_TR' => __( 'Turkish', 'eddslg' ), 'uk_UA' => __( 'Ukrainian', 'eddslg' ), 'vi_VN' => __( 'Vietnamese', 'eddslg' ), 'cy_GB' => __( 'Welsh', 'eddslg' ), 'zh_TW' => __( 'Traditional Chinese Language', 'eddslg' ) );
		
		$edd_slg_settings = array(
				
				array(
					'id'	=> 'edd_slg_settings',
					'name'	=> $success_message . '<strong>' . __( 'Social Login Options', 'eddslg' ) . '</strong>',
					'desc'	=> __( '<input type="button" value="Reset Setting" />Configure Social Login Settings', 'eddslg' ),
					'type'	=> 'header'
				),
				array(
					'id'	=> 'edd_slg_social_reset',
					'name'	=> '<strong>' . __( 'Reset Settings', 'eddslg' ) . '</strong>',
					'desc'	=> '<p class="description">'.__( 'This will reset all the setings of social login.', 'eddslg' ).'</p>',
					'type'	=> 'social_reset',
					'size'	=> 'button',
				),
				
				//General Settings
				array(
					'id'	=> 'edd_slg_general_settings',
					'name'	=> '<strong>' . __( 'General Settings', 'eddslg' ) . '</strong>',
					'desc'	=> __( 'Configure Social Login General Settings', 'eddslg' ),
					'type'	=> 'header'
				),
				array(
					'id'	=> 'edd_slg_login_heading',
					'name'	=> __( 'Social Login Title:', 'eddslg' ),
					'desc'	=> __( 'Enter Social Login Title.', 'eddslg' ),
					'type'	=> 'text',
					'size'	=> 'regular'
				),
				array(
					'id'		=> 'edd_slg_enable_login_page',
					'name'		=> __( 'Display Social Login buttons on Login page:', 'eddslg' ),					
					'type'		=> 'checkbox',
					'desc'	=> '<p class="description">'.__( 'Check this box to add social login buttons on easy digital download login page and default wordpress login page.','eddslg' ).'</p>'
				),
				array(
					'id'		=> 'edd_slg_display_link_thank_you',
					'name'		=> __( 'Display "Link Your Account" button on Thank You page:', 'eddslg' ),
					'type'		=> 'checkbox',
					'desc'	=> '<p class="description">'.__( ' Check this box to allow customers to link their social account on the Thank You page for faster login & checkout next time they purchase.','eddslg' ).'</p>'
				),
				
				array(
					'id'	=> 'edd_slg_enable_notification',
					'name'	=> __( 'Enable Email Notification:', 'eddslg' ),
					'desc'	=> __( 'Check this box, if you want to notify admin and user when user is registered by social media.', 'eddslg' ),
					'type'	=> 'checkbox'
				),
				array(
					'id'	=> 'edd_slg_redirect_url',
					'name'	=> __( 'Redirect URL:', 'eddslg' ),
					'desc'	=> __( 'Enter a redirect URL for users after they login with social media. The URL must start with', 'eddslg' ).' http://',
					'type'	=> 'text',
					'size'	=> 'regular'
				),
				array(
					'id'	=> 'edd_slg_base_reg_username',
					'name'	=> __( 'Autoregistered Usernames:', 'eddslg' ),
					'desc'	=> '',
					'type'	=> 'radio',
					'size'	=> 'regular',
					'std' 	=> '',
					'options'=> array(
						''		=> __( 'Based on unique ID & random number (i.e. edd_slg_123456)', 'eddslg' ),
						'realname'		=> __( 'Based on real name (i.e. john_smith)', 'eddslg' ),
						'emailbased'	=> __( 'Based on email ID (i.e. john.smith@example.com to john_smith_example_com )', 'eddslg' )
					)
				),
				
				//Facebbok Settings
				array(
					'id'	=> 'edd_slg_facebook_settings',
					'name'	=> '<strong>' . __( 'Facebook Settings', 'eddslg' ) . '</strong>',
					'desc'	=> __( 'Configure Social Login Facebook Settings', 'eddslg' ),
					'type'	=> 'header'
				),
				array(
					'id'	=> 'edd_slg_facebook_desc',
					'name'	=> __( 'Facebook Application:', 'eddslg' ),
					'desc'	=> '',
					'type'	=> 'facebook_desc'
				),
				array(
					'id'	=> 'edd_slg_enable_facebook',
					'name'	=> __( 'Enable Facebook:', 'eddslg' ),
					'desc'	=> __( 'Check this box, if you want to enable facebook social login registration.', 'eddslg' ),
					'type'	=> 'checkbox'
				),
				array(
					'id'	=> 'edd_slg_fb_app_id',
					'name'	=> __( 'Facebook App ID/API Key:', 'eddslg' ),
					'desc'	=> __( 'Enter Facebook API Key.', 'eddslg'),
					'type'	=> 'text',
					'size'	=> 'regular'
				),
				array(
					'id'	=> 'edd_slg_fb_app_secret',
					'name'	=> __( 'Facebook App Secret:', 'eddslg' ),
					'desc'	=> __( 'Enter Facebook App Secret.', 'eddslg'),
					'type'	=> 'text',
					'size'	=> 'regular'
				),
				array(
					'id'		=> 'edd_slg_fb_language',
					'name'		=> __( 'Facebook API Locale:', 'eddslg' ),
					'desc'		=> __( 'Select the language for Facebook. With this option, you can explicitly tell which language you want to use for communicating with Facebook.', 'eddslg' ),
					'type'		=> 'select',
					'options'	=> $select_fblanguage
				),
				array(
					'id'	=> 'edd_slg_fb_icon_url',
					'name'	=> __( 'Custom Facebook Icon:', 'eddslg' ),
					'desc'	=> __( 'If you want to use your own Facebook Icon, upload one here.', 'eddslg' ),
					'type'	=> 'upload',
					'size'	=> 'regular'
				),
				array(
					'id'	=> 'edd_slg_fb_link_icon_url',
					'name'	=> __( 'Custom Facebook Link Icon:', 'eddslg' ),
					'desc'	=> __( 'If you want to use your own Facebook Link Icon, upload one here.', 'eddslg' ),
					'type'	=> 'upload',
					'size'  => 'regular'
				),
				array(
					'id'		=> 'edd_slg_enable_fb_avatar',
					'name'		=> __( 'Enable Facebook Avatar:', 'eddslg' ),
					'desc'		=> __( 'Check this box, if you want to use Facebook profile pictures as avatars.', 'eddslg' ),
					'type'		=> 'checkbox'
				),
				//Google+ Settings
				array(
					'id'	=> 'edd_slg_googleplus_settings',
					'name'	=> '<strong>' . __( 'Google+ Settings', 'eddslg' ) . '</strong>',
					'desc'	=> __( 'Configure Social Login Google+ Settings', 'eddslg' ),
					'type'	=> 'header'
				),
				array(
					'id'	=> 'edd_slg_googleplus_desc',
					'name'	=> __( 'Google+ Application:', 'eddslg' ),
					'desc'	=> '',
					'type'	=> 'googleplus_desc'
				),
				array(
					'id'	=> 'edd_slg_enable_googleplus',
					'name'	=> __( 'Enable Google+:', 'eddslg' ),
					'desc'	=> __( 'Check this box, if you want to enable google+ social login registration.', 'eddslg' ),
					'type'	=> 'checkbox'
				),
				array(
					'id'	=> 'edd_slg_gp_client_id',
					'name'	=> __( 'Google+ Client ID:', 'eddslg' ),
					'desc'	=> __( 'Enter Google+ Client ID.', 'eddslg'),
					'type'	=> 'text',
					'size'	=> 'regular'
				),
				array(
					'id'	=> 'edd_slg_gp_client_secret',
					'name'	=> __( 'Google+ Client Secret:', 'eddslg' ),
					'desc'	=> __( 'Enter Google+ Client Secret.', 'eddslg'),
					'type'	=> 'text',
					'size'	=> 'regular'
				),
				array(
					'id'	=> 'edd_slg_gp_redirect_url',
					'name'	=> __( 'Google+ Callback URL:', 'eddslg' ),
					'desc'	=> '',
					'type'	=> 'gp_redirect_url',
					'size'	=> 'regular'
				),
				array(
					'id'	=> 'edd_slg_gp_icon_url',
					'name'	=> __( 'Custom Google+ Icon:', 'eddslg' ),
					'desc'	=> __( 'If you want to use your own Google+ Icon, upload one here.', 'eddslg' ),
					'type'	=> 'upload',
					'size'	=> 'regular'
				),
				array(
					'id'	=> 'edd_slg_gp_link_icon_url',
					'name'	=> __( 'Custom Google+ Link Icon:', 'eddslg' ),
					'desc'	=> __( 'If you want to use your own Google+ Link Icon, upload one here.', 'eddslg' ),
					'type'	=> 'upload',
					'size'	=> 'regular'
				),
				array(
					'id'		=> 'edd_slg_enable_gp_avatar',
					'name'		=> __( 'Enable Google Plus Avatar:', 'eddslg' ),
					'desc'		=> __( 'Check this box, if you want to use Google Plus profile pictures as avatars.', 'eddslg' ),
					'type'		=> 'checkbox'
				),
				
				//LinkedIn Settings
				array(
					'id'	=> 'edd_slg_linkedin_settings',
					'name'	=> '<strong>' . __( 'LinkedIn Settings', 'eddslg' ) . '</strong>',
					'desc'	=> __( 'Configure Social Login LinkedIn Settings', 'eddslg' ),
					'type'	=> 'header'
				),
				array(
					'id'	=> 'edd_slg_linkedin_desc',
					'name'	=> __( 'LinkedIn Application:', 'eddslg' ),
					'desc'	=> '',
					'type'	=> 'linkedin_desc'
				),
				array(
					'id'	=> 'edd_slg_enable_linkedin',
					'name'	=> __( 'Enable LinkedIn:', 'eddslg' ),
					'desc'	=> __( 'Check this box, if you want to enable LinkedIn social login registration.', 'eddslg' ),
					'type'	=> 'checkbox'
				),
				array(
					'id'	=> 'edd_slg_li_app_id',
					'name'	=> __( 'LinkedIn App ID/API Key:', 'eddslg' ),
					'desc'	=> __( 'Enter LinkedIn App ID/API Key.', 'eddslg'),
					'type'	=> 'text',
					'size'	=> 'regular'
				),
				array(
					'id'	=> 'edd_slg_li_app_secret',
					'name'	=> __( 'LinkedIn App Secret:', 'eddslg' ),
					'desc'	=> __( 'Enter LinkedIn App Secret.', 'eddslg'),
					'type'	=> 'text',
					'size'	=> 'regular'
				),
				array(
					'id'	=> 'edd_slg_li_redirect_url',
					'name'	=> __( 'Linkedin Redirect URI:', 'eddslg' ),
					'desc'	=> '',
					'type'	=> 'li_redirect_url',
					'size'	=> 'regular'
				),
				array(
					'id'	=> 'edd_slg_li_icon_url',
					'name'	=> __( 'Custom LinkedIn Icon:', 'eddslg' ),
					'desc'	=> __( 'If you want to use your own LinkedIn Icon, upload one here.', 'eddslg' ),
					'type'	=> 'upload',
					'size'	=> 'regular'
				),
				array(
					'id'	=> 'edd_slg_li_link_icon_url',
					'name'	=> __( 'Custom LinkedIn Link Icon:', 'eddslg' ),
					'desc'	=> __( 'If you want to use your own LinkedIn Link Icon, upload one here.', 'eddslg' ),
					'type'	=> 'upload',
					'size'	=> 'regular'
				),
				array(
					'id'		=> 'edd_slg_enable_li_avatar',
					'name'		=> __( 'Enable LinkedIn Avatar:', 'eddslg' ),
					'desc'		=> __( 'Check this box, if you want to use LinkedIn profile pictures as avatars.', 'eddslg' ),
					'type'		=> 'checkbox'
				),
				
				//twitter Settings
				array(
					'id'	=> 'edd_slg_twitter_settings',
					'name'	=> '<strong>' . __( 'Twitter Settings', 'eddslg' ) . '</strong>',
					'desc'	=> __( 'Configure Social Login Twitter Settings', 'eddslg' ),
					'type'	=> 'header'
				),
				array(
					'id'	=> 'edd_slg_twitter_desc',
					'name'	=> __( 'Twitter Application:', 'eddslg' ),
					'desc'	=> '',
					'type'	=> 'twitter_desc'
				),
				array(
					'id'	=> 'edd_slg_enable_twitter',
					'name'	=> __( 'Enable Twitter:', 'eddslg' ),
					'desc'	=> __( 'Check this box, if you want to enable Twitter social login registration.', 'eddslg' ),
					'type'	=> 'checkbox'
				),
				array(
					'id'	=> 'edd_slg_tw_consumer_key',
					'name'	=> __( 'Twitter API Key:', 'eddslg' ),
					'desc'	=> __( 'Enter Twitter API Key.', 'eddslg'),
					'type'	=> 'text',
					'size'	=> 'regular'
				),
				array(
					'id'	=> 'edd_slg_tw_consumer_secret',
					'name'	=> __( 'Twitter API Secret:', 'eddslg' ),
					'desc'	=> __( 'Enter Twitter API Secret.', 'eddslg'),
					'type'	=> 'text',
					'size'	=> 'regular'
				),
				array(
					'id'	=> 'edd_slg_tw_icon_url',
					'name'	=> __( 'Custom Twitter Icon:', 'eddslg' ),
					'desc'	=> __( 'If you want to use your own Twitter Icon, upload one here.', 'eddslg' ),
					'type'	=> 'upload',
					'size'	=> 'regular'
				),
				array(
					'id'	=> 'edd_slg_tw_link_icon_url',
					'name'	=> __( 'Custom Twitter Link Icon:', 'eddslg' ),
					'desc'	=> __( 'If you want to use your own Twitter Link Icon, upload one here.', 'eddslg' ),
					'type'	=> 'upload',
					'size'	=> 'regular'
				),
				array(
					'id'		=> 'edd_slg_enable_tw_avatar',
					'name'		=> __( 'Enable Twitter Avatar:', 'eddslg' ),
					'desc'		=> __( 'Check this box, if you want to use Twitter profile pictures as avatars.', 'eddslg' ),
					'type'		=> 'checkbox'
				),
				
				//yahoo Settings
				array(
					'id'	=> 'edd_slg_yahoo_settings',
					'name'	=> '<strong>' . __( 'Yahoo Settings', 'eddslg' ) . '</strong>',
					'desc'	=> __( 'Configure Social Login Yahoo Settings', 'eddslg' ),
					'type'	=> 'header'
				),
				array(
					'id'	=> 'edd_slg_yahoo_desc',
					'name'	=> __( 'Yahoo Application:', 'eddslg' ),
					'desc'	=> '',
					'type'	=> 'yahoo_desc'
				),
				array(
					'id'	=> 'edd_slg_enable_yahoo',
					'name'	=> __( 'Enable Yahoo:', 'eddslg' ),
					'desc'	=> __( 'Check this box, if you want to enable Yahoo social login registration.', 'eddslg' ),
					'type'	=> 'checkbox'
				),
				array(
					'id'	=> 'edd_slg_yh_consumer_key',
					'name'	=> __( 'Yahoo Consumer Key:', 'eddslg' ),
					'desc'	=> __( 'Enter Yahoo Consumer Key.', 'eddslg'),
					'type'	=> 'text',
					'size'	=> 'regular'
				),
				array(
					'id'	=> 'edd_slg_yh_consumer_secret',
					'name'	=> __( 'Yahoo Consumer Secret:', 'eddslg' ),
					'desc'	=> __( 'Enter Yahoo Consumer Secret.', 'eddslg'),
					'type'	=> 'text',
					'size'	=> 'regular'
				),
				array(
					'id'	=> 'edd_slg_yh_app_id',
					'name'	=> __( 'Yahoo App Id:', 'eddslg' ),
					'desc'	=> __( 'Enter Yahoo App Id.', 'eddslg'),
					'type'	=> 'text',
					'size'	=> 'regular'
				),
				array(
					'id'	=> 'edd_slg_yh_redirect_url',
					'name'	=> __( 'Yahoo Callback URL:', 'eddslg' ),
					'desc'	=> '',
					'type'	=> 'yh_redirect_url',
					'size'	=> 'regular'
				),
				array(
					'id'	=> 'edd_slg_yh_icon_url',
					'name'	=> __( 'Custom Yahoo Icon:', 'eddslg' ),
					'desc'	=> __( 'If you want to use your own Yahoo Icon, upload one here.', 'eddslg' ),
					'type'	=> 'upload',
					'size'	=> 'regular'
				),
				array(
					'id'	=> 'edd_slg_yh_link_icon_url',
					'name'	=> __( 'Custom Yahoo Link Icon:', 'eddslg' ),
					'desc'	=> __( 'If you want to use your own Yahoo Link Icon, upload one here.', 'eddslg' ),
					'type'	=> 'upload',
					'size'	=> 'regular'
				),
				array(
					'id'		=> 'edd_slg_enable_yh_avatar',
					'name'		=> __( 'Enable Yahoo Avatar:', 'eddslg' ),
					'desc'		=> __( 'Check this box, if you want to use Yahoo profile pictures as avatars.', 'eddslg' ),
					'type'		=> 'checkbox'
				),
				
				//Foursquare Settings
				array(
					'id'	=> 'edd_slg_foursquare_settings',
					'name'	=> '<strong>' . __( 'Foursquare Settings', 'eddslg' ) . '</strong>',
					'desc'	=> __( 'Configure Social Login Foursquare Settings', 'eddslg' ),
					'type'	=> 'header'
				),
				array(
					'id'	=> 'edd_slg_foursquare_desc',
					'name'	=> __( 'Foursquare Application:', 'eddslg' ),
					'desc'	=> '',
					'type'	=> 'foursquare_desc'
				),
				array(
					'id'	=> 'edd_slg_enable_foursquare',
					'name'	=> __( 'Enable Foursquare:', 'eddslg' ),
					'desc'	=> __( 'Check this box, if you want to enable Foursquare social login registration.', 'eddslg' ),
					'type'	=> 'checkbox'
				),
				array(
					'id'	=> 'edd_slg_fs_client_id',
					'name'	=> __( 'Foursquare Client ID:', 'eddslg' ),
					'desc'	=> __( 'Enter Foursquare Client ID.', 'eddslg'),
					'type'	=> 'text',
					'size'	=> 'regular'
				),
				array(
					'id'	=> 'edd_slg_fs_client_secret',
					'name'	=> __( 'Foursquare Client Secret:', 'eddslg' ),
					'desc'	=> __( 'Enter Foursquare Client Secret.', 'eddslg'),
					'type'	=> 'text',
					'size'	=> 'regular'
				),
				array(
					'id'	=> 'edd_slg_fs_icon_url',
					'name'	=> __( 'Custom Foursquare Icon:', 'eddslg' ),
					'desc'	=> __( 'If you want to use your own Foursquare Icon, upload one here.', 'eddslg' ),
					'type'	=> 'upload',
					'size'	=> 'regular'
				),
				array(
					'id'	=> 'edd_slg_fs_link_icon_url',
					'name'	=> __( 'Custom Foursquare Link Icon:', 'eddslg' ),
					'desc'	=> __( 'If you want to use your own Foursquare Link Icon, upload one here.', 'eddslg' ),
					'type'	=> 'upload',
					'size'	=> 'regular'
				),
				array(
					'id'		=> 'edd_slg_enable_fs_avatar',
					'name'		=> __( 'Enable Foursquare Avatar:', 'eddslg' ),
					'desc'		=> __( 'Check this box, if you want to use Foursquare profile pictures as avatars.', 'eddslg' ),
					'type'		=> 'checkbox'
				),
				
				//Windows Live Settings
				array(
					'id'	=> 'edd_slg_windowslive_settings',
					'name'	=> '<strong>' . __( 'Windows Live Settings', 'eddslg' ) . '</strong>',
					'desc'	=> __( 'Configure Social Login Windows Live Settings', 'eddslg' ),
					'type'	=> 'header'
				),
				array(
					'id'	=> 'edd_slg_windowslive_desc',
					'name'	=> __( 'Windows Live Application:', 'eddslg' ),
					'desc'	=> '',
					'type'	=> 'windowslive_desc'
				),
				array(
					'id'	=> 'edd_slg_enable_windowslive',
					'name'	=> __( 'Enable Windows Live:', 'eddslg' ),
					'desc'	=> __( 'Check this box, if you want to enable Windows Live social login registration.', 'eddslg' ),
					'type'	=> 'checkbox'
				),
				array(
					'id'	=> 'edd_slg_wl_client_id',
					'name'	=> __( 'Windows Live Client ID:', 'eddslg' ),
					'desc'	=> __( 'Enter Windows Live Client ID.', 'eddslg'),
					'type'	=> 'text',
					'size'	=> 'regular'
				),
				array(
					'id'	=> 'edd_slg_wl_client_secret',
					'name'	=> __( 'Windows Live Client Secret:', 'eddslg' ),
					'desc'	=> __( 'Enter Windows Live Client Secret.', 'eddslg'),
					'type'	=> 'text',
					'size'	=> 'regular'
				),
				array(
					'id'	=> 'edd_slg_wl_redirect_url',
					'name'	=> __( 'Windows Live Callback URL:', 'eddslg' ),
					'desc'	=> '',
					'type'	=> 'wl_redirect_url',
					'size'	=> 'regular'
				),
				array(
					'id'	=> 'edd_slg_wl_icon_url',
					'name'	=> __( 'Custom Windows Live Icon:', 'eddslg' ),
					'desc'	=> __( 'If you want to use your own Windows Live Icon, upload one here.', 'eddslg' ),
					'type'	=> 'upload',
					'size'	=> 'regular'
				),
				array(
					'id'	=> 'edd_slg_wl_link_icon_url',
					'name'	=> __( 'Custom Windows Live Link Icon:', 'eddslg' ),
					'desc'	=> __( 'If you want to use your own Windows Live Link Icon, upload one here.', 'eddslg' ),
					'type'	=> 'upload',
					'size'	=> 'regular'
				),
				
				//VK Settings
				array(
					'id'	=> 'edd_slg_vk_settings',
					'name'	=> '<strong>' . __( 'VK Settings', 'eddslg' ) . '</strong>',
					'desc'	=> __( 'Configure Social Login VK Settings', 'eddslg' ),
					'type'	=> 'header'
				),
				array(
					'id'	=> 'edd_slg_vk_desc',
					'name'	=> __( 'VK Application:', 'eddslg' ),
					'desc'	=> '',
					'type'	=> 'vk_desc'
				),
				array(
					'id'	=> 'edd_slg_enable_vk',
					'name'	=> __( 'Enable VK:', 'eddslg' ),
					'desc'	=> __( 'Check this box, if you want to enable vk social login registration.', 'eddslg' ),
					'type'	=> 'checkbox'
				),
				array(
					'id'	=> 'edd_slg_vk_app_id',
					'name'	=> __( 'VK Application ID:', 'eddslg' ),
					'desc'	=> __( 'Enter VK Application ID.', 'eddslg'),
					'type'	=> 'text',
					'size'	=> 'regular'
				),
				array(
					'id'	=> 'edd_slg_vk_app_secret',
					'name'	=> __( 'VK Secret Key:', 'eddslg' ),
					'desc'	=> __( 'Enter VK Secret Key.', 'eddslg'),
					'type'	=> 'text',
					'size'	=> 'regular'
				),				
				array(
					'id'	=> 'edd_slg_vk_icon_url',
					'name'	=> __( 'Custom VK Icon:', 'eddslg' ),
					'desc'	=> __( 'If you want to use your own VK Icon, upload one here.', 'eddslg' ),
					'type'	=> 'upload',
					'size'	=> 'regular'
				),
				array(
					'id'	=> 'edd_slg_vk_link_icon_url',
					'name'	=> __( 'Custom VK Link Icon:', 'eddslg' ),
					'desc'	=> __( 'If you want to use your own VK Link Icon, upload one here.', 'eddslg' ),
					'type'	=> 'upload',
					'size'	=> 'regular'
				),
				array(
					'id'		=> 'edd_slg_enable_vk_avatar',
					'name'		=> __( 'Enable VK Avatar:', 'eddslg' ),
					'desc'		=> __( 'Check this box, if you want to use VK profile pictures as avatars.', 'eddslg' ),
					'type'		=> 'checkbox'
				),
				
				//Instagram Settings
				array(
					'id'	=> 'edd_slg_instagram_settings',
					'name'	=> '<strong>' . __( 'Instagram Settings', 'eddslg' ) . '</strong>',
					'desc'	=> __( 'Configure Social Login Instagram Settings', 'eddslg' ),
					'type'	=> 'header'
				),
				array(
					'id'	=> 'edd_slg_instagram_desc',
					'name'	=> __( 'Instagram Application:', 'eddslg' ),
					'desc'	=> '',
					'type'	=> 'instagram_desc'
				),
				array(
					'id'	=> 'edd_slg_enable_instagram',
					'name'	=> __( 'Enable Instagram:', 'eddslg' ),
					'desc'	=> __( 'Check this box, if you want to enable instagram social login registration.', 'eddslg' ),
					'type'	=> 'checkbox'
				),
				array(
					'id'	=> 'edd_slg_inst_app_id',
					'name'	=> __( 'Instagram Client ID:', 'eddslg' ),
					'desc'	=> __( 'Enter Instagram Client ID.', 'eddslg'),
					'type'	=> 'text',
					'size'	=> 'regular'
				),
				array(
					'id'	=> 'edd_slg_inst_app_secret',
					'name'	=> __( 'Instagram Client Secret:', 'eddslg' ),
					'desc'	=> __( 'Enter Instagram Client Secret.', 'eddslg'),
					'type'	=> 'text',
					'size'	=> 'regular'
				),
				array(
					'id'	=> 'edd_slg_inst_redirect_url',
					'name'	=> __( 'Instagram Callback URL:', 'eddslg' ),
					'desc'	=> '',
					'type'	=> 'inst_redirect_url',
					'size'	=> 'regular'
				),				
				array(
					'id'	=> 'edd_slg_inst_icon_url',
					'name'	=> __( 'Custom Instagram Icon:', 'eddslg' ),
					'desc'	=> __( 'If you want to use your own Instagram Icon, upload one here.', 'eddslg' ),
					'type'	=> 'upload',
					'size'	=> 'regular'
				),
				array(
					'id'	=> 'edd_slg_inst_link_icon_url',
					'name'	=> __( 'Custom Instagram Link Icon:', 'eddslg' ),
					'desc'	=> __( 'If you want to use your own Instagram Link Icon, upload one here.', 'eddslg' ),
					'type'	=> 'upload',
					'size'	=> 'regular'
				),
				array(
					'id'		=> 'edd_slg_enable_inst_avatar',
					'name'		=> __( 'Enable Instagram Avatar:', 'eddslg' ),
					'desc'		=> __( 'Check this box, if you want to use Instagram profile pictures as avatars.', 'eddslg' ),
					'type'		=> 'checkbox'
				),
				
				//amazon Settings
				array(
					'id'	=> 'edd_slg_amazon_settings',
					'name'	=> '<strong>' . __( 'Amazon Settings', 'eddslg' ) . '</strong>',
					'desc'	=> __( 'Configure Social Login Amazon Settings', 'eddslg' ),
					'type'	=> 'header'
				),
				array(
					'id'	=> 'edd_slg_amazon_desc',
					'name'	=> __( 'Amazon Application:', 'eddslg' ),
					'desc'	=> '',
					'type'	=> 'amazon_desc'
				),
				array(
					'id'	=> 'edd_slg_enable_amazon',
					'name'	=> __( 'Enable Amazon:', 'eddslg' ),
					'desc'	=> __( 'Check this box, if you want to enable amazon social login registration.', 'eddslg' ),
					'type'	=> 'checkbox'
				),
				array(
					'id'	=> 'edd_slg_amazon_app_id',
					'name'	=> __( 'Amazon Client ID:', 'eddslg' ),
					'desc'	=> __( 'Enter Amazon Client ID.', 'eddslg'),
					'type'	=> 'text',
					'size'	=> 'regular'
				),
				array(
					'id'	=> 'edd_slg_amazon_app_secret',
					'name'	=> __( 'Amazon Client Secret:', 'eddslg' ),
					'desc'	=> __( 'Enter Amazon Client Secret.', 'eddslg'),
					'type'	=> 'text',
					'size'	=> 'regular'
				),
				array(
					'id'	=> 'edd_slg_amazon_redirect_url',
					'name'	=> __( 'Amazon Callback URL:', 'eddslg' ),
					'desc'	=> '',
					'type'	=> 'amazon_redirect_url',
					'size'	=> 'regular'
				),				
				array(
					'id'	=> 'edd_slg_amazon_icon_url',
					'name'	=> __( 'Custom Amazon Icon:', 'eddslg' ),
					'desc'	=> __( 'If you want to use your own Amazon Icon, upload one here.', 'eddslg' ),
					'type'	=> 'upload',
					'size'	=> 'regular'
				),
				array(
					'id'	=> 'edd_slg_amazon_link_icon_url',
					'name'	=> __( 'Custom Amazon Link Icon:', 'eddslg' ),
					'desc'	=> __( 'If you want to use your own Amazon Link Icon, upload one here.', 'eddslg' ),
					'type'	=> 'upload',
					'size'	=> 'regular'
				),
				
				
				//paypal Settings
				array(
					'id'	=> 'edd_slg_paypal_settings',
					'name'	=> '<strong>' . __( 'Paypal Settings', 'eddslg' ) . '</strong>',
					'desc'	=> __( 'Configure Social Login Paypal Settings', 'eddslg' ),
					'type'	=> 'header'
				),
				array(
					'id'	=> 'edd_slg_paypal_desc',
					'name'	=> __( 'Paypal Application:', 'eddslg' ),
					'desc'	=> '',
					'type'	=> 'paypal_desc'
				),
				array(
					'id'	=> 'edd_slg_enable_paypal',
					'name'	=> __( 'Enable Paypal:', 'eddslg' ),
					'desc'	=> __( 'Check this box, if you want to enable paypal social login registration.', 'eddslg' ),
					'type'	=> 'checkbox'
				),
				array(
					'id'	=> 'edd_slg_paypal_app_id',
					'name'	=> __( 'Paypal Client ID:', 'eddslg' ),
					'desc'	=> __( 'Enter Paypal Client ID.', 'eddslg'),
					'type'	=> 'text',
					'size'	=> 'regular'
				),
				array(
					'id'	=> 'edd_slg_paypal_app_secret',
					'name'	=> __( 'Paypal Client Secret:', 'eddslg' ),
					'desc'	=> __( 'Enter Paypal Client Secret.', 'eddslg'),
					'type'	=> 'text',
					'size'	=> 'regular'
				),
				array(
					'id'	=> 'edd_slg_paypal_redirect_url',
					'name'	=> __( 'Paypal Callback URL:', 'eddslg' ),
					'desc'	=> '',
					'type'	=> 'paypal_redirect_url',
					'size'	=> 'regular'
				),				
				array(
					'id'	=> 'edd_slg_paypal_icon_url',
					'name'	=> __( 'Custom Paypal Icon:', 'eddslg' ),
					'desc'	=> __( 'If you want to use your own Paypal Icon, upload one here.', 'eddslg' ),
					'type'	=> 'upload',
					'size'	=> 'regular'
				),
				array(
					'id'	=> 'edd_slg_paypal_link_icon_url',
					'name'	=> __( 'Custom Paypal Link Icon:', 'eddslg' ),
					'desc'	=> __( 'If you want to use your own Paypal Link Icon, upload one here.', 'eddslg' ),
					'type'	=> 'upload',
					'size'	=> 'regular'
				),
				array(
					'id'		=> 'edd_slg_paypal_environment',
					'name'		=> __( 'Environment:', 'eddslg' ),
					'desc'		=> __('Select which environment to process logins under.', 'eddslg'),
					'type'		=> 'select',					
					'options' 	=> array( 'live' => __('Live','eddslg'), 'sandbox' => __('Sandbox','eddslg') )	
				),
		 );
		 
		 
		// If EDD is at version 2.5 or later
	    if ( version_compare( EDD_VERSION, 2.5, '>=' ) ) {
	    	$edd_slg_settings = array( 'eddslg' => $edd_slg_settings );
	    }
		
		return array_merge( $settings, $edd_slg_settings );
		
	}
	
	/**
	 * Get User profile pic
	 *
	 * Function to get user profile pic from user meta type its social type 
	 *
	 * @package Easy Digital Downloads - Social Login
	 * @since 1.4
	 */
	public function edd_slg_get_user_profile_pic( $user_id = false ) {
		
		global $edd_options;
		
		// Taking some defaults
		$profile_pic_url = '';
		
		// If user id is passed then take otherwise take current user
		$user_id = !empty( $user_id ) ? $user_id : '';
		
		if( $user_id ) {
			
			// Getting some user details
			$edd_slg_social_type 	= get_user_meta( $user_id, 'edd_slg_social_user_connect_via', true );
			$edd_slg_data 			= get_user_meta( $user_id, 'edd_slg_social_data', true );
			
			if( !empty($edd_slg_social_type) && !empty($edd_slg_data) ) {
				
				// If facebook avatar is enable
				if( !empty($edd_options['edd_slg_enable_fb_avatar']) && $edd_options['edd_slg_enable_fb_avatar'] == "1" ) {
					
					// If user is from facebook
					if( $edd_slg_social_type == 'facebook' ) {
						$profile_pic_url = !empty($edd_slg_data['picture']) ? $edd_slg_data['picture'] : '';
					}
				}
				
				// If twitter avatar is enable
				if( !empty($edd_options['edd_slg_enable_tw_avatar']) && $edd_options['edd_slg_enable_tw_avatar'] == "1" ) {
					
					// If user is from twitter
					if( $edd_slg_social_type == 'twitter' ) {
						$profile_pic_url = !empty($edd_slg_data->profile_image_url_https) ? $edd_slg_data->profile_image_url_https : '';
					}
				}
				
				// If google plus avatar is enable
				if( !empty($edd_options['edd_slg_enable_gp_avatar']) && $edd_options['edd_slg_enable_gp_avatar'] == "1" ) {
					
					// If user is from googleplus
					if( $edd_slg_social_type == 'googleplus' ) {
						//$profile_pic_url = !empty($edd_slg_data->picture ) ? $edd_slg_data->picture : '';
						if( isset($edd_slg_data->picture) &&  !empty($edd_slg_data->picture) ) {
							$profile_pic_url =  $edd_slg_data->picture;
						} elseif ( $edd_slg_data['image']['url'] && !empty( $edd_slg_data['image']['url'] ) ) { // Added for backward compitibility
							$profile_pic_url =  $edd_slg_data['image']['url'];
						}
					}
				}
				
				// If linked in avatar is enable
				if( !empty($edd_options['edd_slg_enable_li_avatar']) && $edd_options['edd_slg_enable_li_avatar'] == "1" ) {
					
					// If user is from linkedin
					if( $edd_slg_social_type == 'linkedin' ) {
						
						$profile_pic_url = '';
						
						if( !empty($edd_slg_data['picture-url']) ) {
							$profile_pic_url = $edd_slg_data['picture-url']; // Added for backward compitibility
						} elseif ( !empty( $edd_slg_data['pictureUrl'] ) ) {
							$profile_pic_url = $edd_slg_data['pictureUrl'];
						}
					}
				}
				
				// If yahoo avatar is enable
				if( !empty($edd_options['edd_slg_enable_yh_avatar']) && $edd_options['edd_slg_enable_yh_avatar'] == "1" ) {
					
					// If user is from yahoo
					if( $edd_slg_social_type == 'yahoo' ) {
						$profile_pic_url = !empty($edd_slg_data->image->imageUrl) ? $edd_slg_data->image->imageUrl : '';
					}
				}
				
				// If foursquer avatar is enable
				if( !empty($edd_options['edd_slg_enable_fs_avatar']) && $edd_options['edd_slg_enable_fs_avatar'] == "1" ) {
					
					// If user is from foursquare
					if( $edd_slg_social_type == 'foursquare' ) {
						
						$profile_pic_url = $this->foursquare->edd_slg_get_foursquare_profile_picture( array('size' => '64'), $edd_slg_data );
						
					}
				}
				
				// If vk avatar is enable
				if( !empty($edd_options['edd_slg_enable_vk_avatar']) && $edd_options['edd_slg_enable_vk_avatar'] == "1" ) {
					
					// If user is from vk
					if( $edd_slg_social_type == 'vk' ) {
						$profile_pic_url = !empty($edd_slg_data['photo_big']) ? $edd_slg_data['photo_big'] : '';
					}
				}
				
				// If instagram avatar is enable
				if( !empty($edd_options['edd_slg_enable_inst_avatar']) && $edd_options['edd_slg_enable_inst_avatar'] == "1" ) {
					
					// If user is from vk
					if( $edd_slg_social_type == 'instagram' ) {						
						$profile_pic_url = !empty($edd_slg_data->profile_picture) ? $edd_slg_data->profile_picture : '';
					}
				}
				
			}
		}
		
		return $profile_pic_url;
	}
	
	/**
	 * Username Using Fname And Lname
	 * 
	 * Handle to create username using api firstname and lastname
	 * 
	 * @package Easy Digital Downloads - Social Login
	 * @since 1.3.4
	 */
	public function edd_slg_username_by_fname_lname( $first_name = '', $last_name = '' ) {
		
		//Initilize username
		$username	= '';
		
		if( !empty( $first_name ) ) {//If firstname is not empty
			$username	.= $first_name;
		}
		if( !empty( $last_name ) ) {//If lastname is not empty
			$username	.= '_' . $last_name;
		}
		
		return $username;
	}
	
	/**
	 * Username Using Email
	 * 
	 * Handle to create username using social email address
	 * 
	 * @package Easy Digital Downloads - Social Login
	 * @since 1.3.4
	 */
	public function edd_slg_username_by_email( $user_email = '' ) {
		
		//Initilize username
		$username	= '';
		
		$username	= str_replace( '@', '_', $user_email );
		$username	= str_replace( '.', '_', $username );
		
		return $username;
	}
	
	
	/**
	 * Common Social Data Convertion
	 * 
	 * @package Easy Digital Download - Social Login
	 * @since 1.5.6
	 */
	public function edd_slg_get_user_common_social_data( $social_data = array(), $social_type = '' ) {
		
		$common_social_data	= array();
		
		if( !empty( $social_type ) ) { // If social type is not empty
			
			switch ( $social_type ) {
				
				case 'facebook' :
					$common_social_data['first_name']	= $social_data['first_name'];
					$common_social_data['last_name']	= $social_data['last_name'];
					$common_social_data['name']			= $social_data['name'];
					$common_social_data['email']		= $social_data['email'];
					$common_social_data['type']			= $social_type;
					$common_social_data['all']			= $social_data;
					$common_social_data['link']			= $social_data['link'];
					$common_social_data['id']			= $social_data['id'];
					break;
					
				case 'googleplus':		
				
							
					$firstname = isset( $social_data->given_name )  ? $social_data->given_name : ( isset( $social_data['name']['givenName'] ) ? $social_data['name']['givenName'] : '' );
					$lastname  = isset( $social_data->family_name ) ? $social_data->family_name : ( isset( $social_data['name']['family_name'] ) ? $social_data['name']['givenName'] : '' );
					$name 	   = isset( $social_data->name )        ? $social_data->name : ( isset( $social_data['displayName'] ) ? $social_data['displayName'] : '' );
					$email 	   = isset( $social_data->email )       ? $social_data->email : ( isset( $social_data['email'] ) ? $social_data['email'] : '' );
					$id 	   = isset( $social_data->id ) 			? $social_data->id : ( isset( $social_data['id'] ) ? $social_data['id'] : '' );
					//$link    = isset( $social_data->link ) 		? $social_data->link : ( isset( $social_data['url'] ) ? $social_data['url'] : '' );
					
					$common_social_data['first_name']	= $firstname;
					$common_social_data['last_name']	= $lastname;
					$common_social_data['name']			= $name;
					$common_social_data['email']		= $email ;
					$common_social_data['type']			= $social_type;
					$common_social_data['all']			= $social_data;
					$common_social_data['link']			= '';
					$common_social_data['id']			= $id;
					$common_social_data['image']['url'] = $id;					
					break;
					
				case 'linkedin' :
					$common_social_data['first_name']	= $social_data['firstName'];
					$common_social_data['last_name']	= $social_data['lastName'];
					$common_social_data['name']			= $social_data['firstName'].' '.$social_data['lastName'];
					$common_social_data['email']		= $social_data['emailAddress'];
					$common_social_data['type']			= $social_type;
					$common_social_data['all']			= $social_data;
					$common_social_data['link']			= $social_data['publicProfileUrl'];
					$common_social_data['id']			= $social_data['id'];
					break;
					
				case 'yahoo' :
					$common_social_data['first_name']	= $social_data->givenName;
					$common_social_data['last_name']	= $social_data->familyName;
					$common_social_data['name']			= $social_data->givenName.' '.$social_data->familyName;
					$common_social_data['email']		= $social_data->yh_primary_email;
					$common_social_data['type']			= $social_type;
					$common_social_data['all']			= $social_data;
					$common_social_data['link']			= $social_data->profileUrl;
					$common_social_data['id']			= $social_data->guid;
					break;
					
				case 'foursquare' :
					$common_social_data['first_name']	= $social_data->firstName;
					$common_social_data['last_name']	= $social_data->lastName;
					$common_social_data['name']			= $social_data->firstName.' '.$social_data->lastName;
					$common_social_data['email']		= $social_data->contact->email;
					$common_social_data['type']			= $social_type;
					$common_social_data['all']			= $social_data;
					$common_social_data['link']			= 'https://foursquare.com/user/' . $social_data->id;
					$common_social_data['id']			= $social_data->id;
					break;
					
				case 'windowslive' :
					$common_social_data['first_name']	= $social_data->first_name;
					$common_social_data['last_name']	= $social_data->last_name;
					$common_social_data['name']			= $social_data->name;
					$common_social_data['email']		= $social_data->wlemail;
					$common_social_data['type']			= $social_type;
					$common_social_data['all']			= $social_data;
					$common_social_data['link']			= $social_data->link;
					$common_social_data['id']			= $social_data->id;
					break;
					
				case 'vk' :
					$common_social_data['first_name']	= $social_data['first_name'];
					$common_social_data['last_name']	= $social_data['last_name'];
					$common_social_data['name']			= $social_data['first_name'] . ' ' . $social_data['last_name'];
					$common_social_data['email']		= isset($social_data['email']) ? $social_data['email'] : '';  
					$common_social_data['type']			= $social_type;
					$common_social_data['all']			= $social_data;
					$common_social_data['link']			= EDD_SLG_VK_LINK . '/' . $social_data['screen_name'];
					$common_social_data['id']			= $social_data['uid'];
					break;
					
				case 'instagram' :
					$common_social_data['first_name']	= $social_data->first_name;
					$common_social_data['last_name']	= $social_data->last_name;
					$common_social_data['name']			= $social_data->username;
					$common_social_data['email']		= '';
					$common_social_data['type']			= $social_type;
					$common_social_data['all']			= $social_data;
					$common_social_data['link']			= $social_data->profile_picture;
					$common_social_data['id']			= $social_data->id;
					break;
					
				case 'twitter' :
					$common_social_data['first_name']	= $social_data->name;
					$common_social_data['last_name']	= '';
					$common_social_data['name']			= $social_data->screen_name; //display name of user
					$common_social_data['type']			= 'twitter';
					$common_social_data['all']			= $social_data;
					$common_social_data['link']			= 'https://twitter.com/' . $social_data->screen_name;
					$common_social_data['id']			= $social_data->id;
					break;
					
				case 'amazon':
					$common_social_data['name']			= $social_data->name; //display name of user
					$common_social_data['id']			= $social_data->user_id;
					$common_social_data['email']		= $social_data->email;
					$common_social_data['all']			= $social_data;	
					$common_social_data['type']			= $social_type;					
				
				case 'paypal' :
					$common_social_data['first_name']	= isset( $social_data->given_name ) ? $social_data->given_name : '';
					$common_social_data['last_name']	= isset( $social_data->family_name ) ? $social_data->family_name : '';
					$common_social_data['email']		= $social_data->email;
					$common_social_data['name']			= isset( $social_data->name ) ? $social_data->name : ''; //display name of user
					$common_social_data['type']			= $social_type;
					$common_social_data['all']			= $social_data;
					$common_social_data['id']			= $social_data->user_id;	
			}
		}
		
		return apply_filters( 'edd_slg_get_user_common_social_data', $common_social_data, $social_type );
	}
}