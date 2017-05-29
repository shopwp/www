<?php
/**
 * Template Hooks
 * 
 * Handles to add all hooks of template
 * 
 * @package Easy Digital Downloads - Social Login
 * @since 1.4.1
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $edd_slg_render;

$user_id = get_current_user_id();
		
$render = $edd_slg_render;
		
//add action to load social button facebook, twitter, googleplus, linkedin

add_action( 'edd_slg_checkout_wrapper_social_login', array( $render, 'edd_slg_checkout_wrapper_social_login_content'), 10 );

//check is there any social media is enable or not
if( edd_slg_check_social_enable() ){
	
	$edd_social_order = get_option( 'edd_social_order' );

	if( !empty( $edd_social_order ) ) {
		$priority = 5;
		foreach ( $edd_social_order as $social ) {
			if( !empty( $social ) ) {				
				add_action( 'edd_slg_checkout_social_login', array( $render, 'edd_slg_login_'.$social ), $priority );
				add_action( 'edd_slg_checkout_social_login_link', array( $render, 'edd_slg_login_link_'.$social ), $priority );
				$priority += 5;			
			}
		}
	}
}

?>