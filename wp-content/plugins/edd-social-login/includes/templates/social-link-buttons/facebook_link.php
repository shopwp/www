<?php
/**
 * Facebook Link Button Template
 * 
 * Handles to load facebook link button template
 * 
 * Override this template by copying it to yourtheme/edd-social-login/social-link-buttons/facebook_link.php
 * 
 * @package Easy Digital Downloads - Social Login
 * @since 1.5.6
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
	
//show facebook button
?>
<div class="edd-slg-login-wrapper">
	<a title="<?php _e( 'Link your account with Facebook', 'eddslg');?>" href="javascript:void(0);" class="edd-slg-social-login-facebook">
		<img src="<?php echo $fblinkimgurl;?>" alt="<?php _e( 'Link your account with Facebook', 'eddslg');?>" />
	</a>
</div>