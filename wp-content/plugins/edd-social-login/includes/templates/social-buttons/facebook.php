<?php
/**
 * Facebook Button Template
 * 
 * Handles to load facebook button template
 * 
 * Override this template by copying it to yourtheme/edd-social-login/social-buttons/facebook.php
 * 
 * @package Easy Digital Downloads - Social Login
 * @since 1.4.1
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
	
//show facebook button
?>
<div class="edd-slg-login-wrapper">
	<a title="<?php _e( 'Connect with Facebook', 'eddslg');?>" href="javascript:void(0);" class="edd-slg-social-login-facebook">
		<img src="<?php echo $fbimgurl;?>" alt="<?php _e( 'Facebook', 'eddslg');?>" />
	</a>
</div>