<?php
/**
 * paypal Button Template
 * 
 * Handles to load paypal button template
 * 
 * Override this template by copying it to yourtheme/edd-social-login/social-buttons/paypal.php
 * 
 * @package Easy Digital Downloads - Social Login
 * @since 1.7.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
	
//show paypal button
?>
<div class="edd-slg-login-wrapper">
	<a title="<?php _e( 'Connect with Paypal', 'eddslg');?>" href="javascript:void(0);" class="edd-slg-social-login-paypal">
		<img src="<?php echo $paypalimgurl;?>" alt="<?php _e( 'Paypal', 'eddslg');?>" />
	</a>
</div>

	 