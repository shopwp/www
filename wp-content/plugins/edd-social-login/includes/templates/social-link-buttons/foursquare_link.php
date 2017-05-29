<?php
/**
 * Foursquare Button Template
 * 
 * Handles to load foursquare button template
 * 
 * Override this template by copying it to yourtheme/edd-social-login/social-link-buttons/foursquare_link.php
 * 
 * @package Easy Digital Downloads - Social Login
 * @since 1.5.6
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
	
//show foursquare button
?>
<div class="edd-slg-login-wrapper">
	<a title="<?php _e( 'Link your account with Foursquare', 'eddslg');?>" href="javascript:void(0);" class="edd-slg-social-login-foursquare">
		<img src="<?php echo $fslinkimgurl;?>" alt="<?php _e( 'Link Your account with Foursquare', 'eddslg');?>" />
	</a>
</div>