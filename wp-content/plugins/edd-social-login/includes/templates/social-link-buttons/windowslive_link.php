<?php
/**
 * Windows Live Button Template
 * 
 * Handles to load windows live button template
 * 
 * Override this template by copying it to yourtheme/edd-social-login/social-link-buttons/windowslive_link.php
 * 
 * @package Easy Digital Downloads - Social Login
 * @since 1.5.6
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
	
//show windows live button
?>
<div class="edd-slg-login-wrapper">
	<a title="<?php _e( 'Link your account with Live', 'eddslg');?>" href="javascript:void(0);" class="edd-slg-social-login-windowslive">
		<img src="<?php echo $wllinkimgurl;?>" alt="<?php _e( 'Link your account with Live', 'eddslg');?>" />
	</a>
</div>