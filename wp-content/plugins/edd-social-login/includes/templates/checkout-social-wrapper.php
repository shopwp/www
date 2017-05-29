<?php
/**
 * Social Wrapper Template
 * 
 * Handles to load social wrapper template for checkout page
 * 
 * Override this template by copying it to yourtheme/edd-social-login/checkout-social-wrapper.php
 * 
 * Note: When you overwrite template, please dont remove class "edd-slg-social-container", else social buttons wont work.
 * 
 * @package Easy Digital Downloads - Social Login
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

?>
<fieldset id="edd_slg_social_login" class="edd-slg-social-container">
		
	<span><legend><?php echo $login_heading; ?></legend></span>
	
	<?php
		//do action to add social login buttons
		do_action( 'edd_slg_checkout_wrapper_social_login' );
	?>	
</fieldset>