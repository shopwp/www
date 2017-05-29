<?php
/**
 * vk Button Template
 * 
 * Handles to load vk button template
 * 
 * Override this template by copying it to yourtheme/edd-social-login/social-buttons/vk.php
 * 
 * @package Easy Digital Downloads - Social Login
 * @since 1.4.1
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
	
//show vk button
?>
<div class="edd-slg-login-wrapper">
	<a title="<?php _e( 'Connect with VK.com', 'eddslg');?>" href="javascript:void(0);" class="edd-slg-social-login-vk">
		<img src="<?php echo $vkimgurl;?>" alt="<?php _e( 'vk', 'eddslg');?>" />
	</a>
</div>