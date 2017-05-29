<?php
/**
 * Googleplus Button Template
 * 
 * Handles to load googleplus button template
 * 
 * Override this template by copying it to yourtheme/edd-social-login/social-buttons/googleplus.php
 * 
 * @package Easy Digital Downloads - Social Login
 * @since 1.4.1
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
	
//show googleplus button
?>
<div class="edd-slg-login-wrapper">
	<a title="<?php _e( 'Connect with Google+', 'eddslg');?>" href="javascript:void(0);" class="edd-slg-social-login-googleplus">
		<img src="<?php echo $gpimgurl;?>" alt="<?php _e( 'Google+', 'eddslg');?>" />
	</a>
</div>