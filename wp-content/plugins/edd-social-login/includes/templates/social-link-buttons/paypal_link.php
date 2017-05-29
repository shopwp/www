<?php
/**
 * Paypal Button Template
 * 
 * Handles to load paypal button template
 * 
 * Override this template by copying it to yourtheme/edd-social-login/social-link-buttons/paypal_link.php
 * 
 * @package Easy Digital Downloads - Social Login
 * @since 1.7.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
	
//show paypal link button
?>
<div class="edd-slg-login-wrapper">
	<a title="<?php _e( 'Link your account with Paypal', 'eddslg');?>" href="javascript:void(0);" class="edd-slg-social-login-paypal">
		<img src="<?php echo $paypalimglinkurl;?>" alt="<?php _e( 'Link your account with Paypal', 'eddslg');?>" />
	</a>
</div>