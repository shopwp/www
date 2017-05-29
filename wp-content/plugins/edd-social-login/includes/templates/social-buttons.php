<?php
/**
 * Social Button Template
 * 
 * Handles to load social button template
 * 
 * 
 * @package Easy Digital Downloads - Social Login
 * @since 1.4.1
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
	
?>
<div class="edd-slg-social-wrap">
	<?php 
		//do action to add social login buttons
		do_action( 'edd_slg_checkout_social_login' );
	?>
	<div class="edd-slg-clear"></div>
</div><!--.edd-slg-social-wrap-->

<div class="edd-slg-login-error"></div><!--edd-slg-login-error-->

<div class="edd-slg-login-loader">
	<img src="<?php echo EDD_SLG_IMG_URL;?>/social-loader.gif" alt="<?php _e( 'Social Loader', 'eddslg');?>"/>
</div><!--.edd-slg-login-loader-->

<input type="hidden" class="edd-slg-redirect-url" id="edd_slg_redirect_url" value="<?php echo $login_redirect_url;?>" />
