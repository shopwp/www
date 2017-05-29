<?php
/**
 * Twitter Button Template
 * 
 * Handles to load twitter button template
 * 
 * Override this template by copying it to yourtheme/edd-social-login/social-link-buttons/twitter_link.php
 * 
 * @package Easy Digital Downloads - Social Login
 * @since 1.5.6
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
	
//show twitter button
?>
<div class="edd-slg-login-wrapper">
	<a title="<?php _e( 'Link your account with Twitter', 'eddslg');?>" href="javascript:void(0);" class="edd-slg-social-login-twitter">
		<img src="<?php echo $twlinkimgurl;?>" alt="<?php _e( 'Link your account with  Twitter', 'eddslg');?>" />
	</a>
</div>