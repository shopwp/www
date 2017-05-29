<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Social File
 *
 * Handles load all social related files
 *
 * @package Easy Digital Downloads - Social Login
 * @since 1.0.0
 */

global $edd_slg_social_facebook, $edd_slg_social_google,$edd_slg_social_linkedin,$edd_slg_social_twitter,
	$edd_slg_social_yahoo,$edd_slg_social_foursquare,$edd_slg_social_vk,$edd_slg_social_windowslive,$edd_slg_social_instagram,
	$edd_slg_social_amazon, $edd_slg_social_paypal;

//Social Media Facebook Class for social login
require_once( EDD_SLG_SOCIAL_LIB_DIR .'/facebook.php');
$edd_slg_social_facebook = new EDD_Slg_Social_Facebook();
	
//Social Media Google Class for social login
require_once( EDD_SLG_SOCIAL_LIB_DIR .'/google.php');
$edd_slg_social_google = new EDD_Slg_Social_Google();

//Social Media LinkedIn Class for social login
require_once( EDD_SLG_SOCIAL_LIB_DIR .'/linkedin.php');
$edd_slg_social_linkedin = new EDD_Slg_Social_LinkedIn();

//Social Media Twitter Class for social login
require_once( EDD_SLG_SOCIAL_LIB_DIR .'/twitter.php');
$edd_slg_social_twitter = new EDD_Slg_Social_Twitter();

//Social Media Yahoo Class for social login
require_once( EDD_SLG_SOCIAL_LIB_DIR .'/yahoo.php');
$edd_slg_social_yahoo = new EDD_Slg_Social_Yahoo();

//Social Media Foursquare Class for social login
require_once( EDD_SLG_SOCIAL_LIB_DIR .'/foursquare.php');
$edd_slg_social_foursquare = new EDD_Slg_Social_Foursquare();

//Social Media Windows Live Class for social login
require_once( EDD_SLG_SOCIAL_LIB_DIR .'/windowslive.php');
$edd_slg_social_windowslive = new EDD_Slg_Social_Windowslive();

//Social Media VK Class for social login
require_once( EDD_SLG_SOCIAL_LIB_DIR .'/vk.php');
$edd_slg_social_vk = new EDD_Slg_Social_VK();

//Social Media Instagram Class for social login
require_once( EDD_SLG_SOCIAL_LIB_DIR .'/instagram.php');
$edd_slg_social_instagram = new EDD_Slg_Social_Instagram();

//Social Media Amazon Class for social login
require_once( EDD_SLG_SOCIAL_LIB_DIR .'/amazon.php');
$edd_slg_social_amazon = new EDD_Slg_Social_Amazon();

//Social Media PayPal Class for social login
require_once( EDD_SLG_SOCIAL_LIB_DIR .'/paypal.php');
$edd_slg_social_paypal = new EDD_Slg_Social_Paypal();