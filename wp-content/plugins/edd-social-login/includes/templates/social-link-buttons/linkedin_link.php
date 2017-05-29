<?php
/**
 * Linkedin Button Template
 * 
 * Handles to load linkedin button template
 * 
 * Override this template by copying it to yourtheme/edd-social-login/social-link-buttons/linkedin_link.php
 * 
 * @package Easy Digital Downloads - Social Login
 * @since 1.5.6
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
	
//show linkedin button
?>
<div class="edd-slg-login-wrapper">
	<a title="<?php _e( 'Link your account with LinkedIn', 'eddslg');?>" href="javascript:void(0);" class="edd-slg-social-login-linkedin">
		<img src="<?php echo $lilinkimgurl;?>" alt="<?php _e( 'Link your account with LinkedIn', 'eddslg');?>" />
	</a>
</div>