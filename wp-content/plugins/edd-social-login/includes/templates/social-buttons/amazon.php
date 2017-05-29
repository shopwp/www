<?php
/**
 * Amazon Button Template
 * 
 * Handles to load amazon button template
 * 
 * Override this template by copying it to yourtheme/edd-social-login/social-buttons/amazon.php
 * 
 * @package Easy Digital Downloads - Social Login
 * @since 1.7.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
	
//show amazon button
?>
<div class="edd-slg-login-wrapper">
	<a title="<?php _e( 'Connect with Amazon', 'eddslg');?>" href="javascript:void(0);" class="edd-slg-social-login-amazon">
		<img src="<?php echo $amazonimgurl;?>" alt="<?php _e( 'Amazon', 'eddslg');?>" />
	</a>
</div>
