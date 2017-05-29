<?php
/**
 * Yahoo Button Template
 * 
 * Handles to load yahoo button template
 * 
 * Override this template by copying it to yourtheme/edd-social-login/social-buttons/yahoo_link.php
 * 
 * @package Easy Digital Downloads - Social Login
 * @since 1.4.1
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
	
//show yahoo button
?>
<div class="edd-slg-login-wrapper">
	<a title="<?php _e( 'Link your account with Yahoo', 'eddslg');?>" href="javascript:void(0);" class="edd-slg-social-login-yahoo">
		<img src="<?php echo $yhlinkimgurl;?>" alt="<?php _e( 'Link your account with Yahoo', 'eddslg');?>" />
	</a>
</div>