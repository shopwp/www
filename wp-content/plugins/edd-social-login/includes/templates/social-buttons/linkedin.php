<?php
/**
 * Linkedin Button Template
 * 
 * Handles to load linkedin button template
 * 
 * Override this template by copying it to yourtheme/edd-social-login/social-buttons/linkedin.php
 * 
 * @package Easy Digital Downloads - Social Login
 * @since 1.4.1
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
	
//show linkedin button
?>
<div class="edd-slg-login-wrapper">
	<a title="<?php _e( 'Connect with LinkedIn', 'eddslg');?>" href="javascript:void(0);" class="edd-slg-social-login-linkedin">
		<img src="<?php echo $liimgurl;?>" alt="<?php _e( 'LinkedIn', 'eddslg');?>" />
	</a>
</div>