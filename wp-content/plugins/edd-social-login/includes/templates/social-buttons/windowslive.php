<?php
/**
 * Windows Live Button Template
 * 
 * Handles to load windows live button template
 * 
 * Override this template by copying it to yourtheme/edd-social-login/social-buttons/window.php
 * 
 * @package Easy Digital Downloads - Social Login
 * @since 1.4.1
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
	
//show windows live button
?>
<div class="edd-slg-login-wrapper">
	<a title="<?php _e( 'Connect with Windows Live', 'eddslg');?>" href="javascript:void(0);" class="edd-slg-social-login-windowslive">
		<img src="<?php echo $wlimgurl;?>" alt="<?php _e( 'Windows Live', 'eddslg');?>" />
	</a>
</div>