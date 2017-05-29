<?php
/**
 * Instagram Button Template
 * 
 * Handles to load instagram button template
 * 
 * Override this template by copying it to yourtheme/edd-social-login/social-buttons/instagram_link.php
 * 
 * @package Easy Digital Downloads - Social Login
 * @since 1.5.6
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
	
//show instagram button
?>
<div class="edd-slg-login-wrapper">
	<a title="<?php _e( 'Link your account with Instagram', 'eddslg');?>" href="javascript:void(0);" class="edd-slg-social-login-instagram">
		<img src="<?php echo $instlinkimgurl;?>" alt="<?php _e( 'Link your account with Instagram', 'eddslg');?>" />
	</a>
</div>