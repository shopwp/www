<?php
/**
 * Googleplus Button Template
 * 
 * Handles to load googleplus button template
 * 
 * Override this template by copying it to yourtheme/edd-social-login/social-link-buttons/googleplus_link.php
 * 
 * @package Easy Digital Downloads - Social Login
 * @since 1.5.6
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
	
//show googleplus button
?>
<div class="edd-slg-login-wrapper">
	<a title="<?php _e( 'Link your account with Google+', 'eddslg');?>" href="javascript:void(0);" class="edd-slg-social-login-googleplus">
		<img src="<?php echo $gplinkimgurl;?>" alt="<?php _e( 'Link your account with Google+', 'eddslg');?>" />
	</a>
</div>