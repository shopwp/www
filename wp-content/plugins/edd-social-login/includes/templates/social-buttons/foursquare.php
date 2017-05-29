<?php
/**
 * Foursquare Button Template
 * 
 * Handles to load foursquare button template
 * 
 * Override this template by copying it to yourtheme/edd-social-login/social-buttons/foursquare.php
 * 
 * @package Easy Digital Downloads - Social Login
 * @since 1.4.1
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
	
//show foursquare button
?>
<div class="edd-slg-login-wrapper">
	<a title="<?php _e( 'Connect with Foursquare', 'eddslg');?>" href="javascript:void(0);" class="edd-slg-social-login-foursquare">
		<img src="<?php echo $fsimgurl;?>" alt="<?php _e( 'Foursquare', 'eddslg');?>" />
	</a>
</div>