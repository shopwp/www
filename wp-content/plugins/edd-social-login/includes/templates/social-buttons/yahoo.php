<?php
/**
 * Yahoo Button Template
 * 
 * Handles to load yahoo button template
 * 
 * Override this template by copying it to yourtheme/edd-social-login/social-buttons/yahoo.php
 * 
 * @package Easy Digital Downloads - Social Login
 * @since 1.4.1
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
	
//show yahoo button
?>
<div class="edd-slg-login-wrapper">
	<a title="<?php _e( 'Connect with Yahoo', 'eddslg');?>" href="javascript:void(0);" class="edd-slg-social-login-yahoo">
		<img src="<?php echo $yhimgurl;?>" alt="<?php _e( 'Yahoo', 'eddslg');?>" />
	</a>
</div>