<?php 

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Shortocde UI
 *
 * This is the code for the pop up editor, which shows up when an user clicks
 * on the edd social login icon within the WordPress editor.
 *
 * @package Easy Digital Downloads - Social Login
 * @since 1.1.1
 * 
 **/

?>

<div class="edd-slg-popup-content">

	<div class="edd-slg-header">
		<div class="edd-slg-header-title"><?php _e( 'Add A Social Login Shortcode', 'eddslg' );?></div>
		<div class="edd-slg-popup-close"><a href="javascript:void(0);" class="edd-slg-close-button"><img src="<?php echo EDD_SLG_IMG_URL;?>/tb-close.png" alt="<?php _e( 'Close', 'eddslg' );?>" /></a></div>
	</div>
	
	<div class="edd-slg-popup">
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row">
						<label><?php _e( 'Select A Shortcode', 'eddslg' );?></label>		
					</th>
					<td>
						<select id="edd_slg_shortcodes">				
							<option value="edd_social_login"><?php _e( 'Social Login', 'eddslg' );?></option>
						</select>		
					</td>
				</tr>
			</tbody>
		</table>
		
		<div id="edd_slg_login_options" class="edd-slg-shortcodes-options">
		
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row">
							<label for="edd_slg_title"><?php _e( 'Social Login Title:', 'eddslg' );?></label>		
						</th>
						<td>
							<input type="text" id="edd_slg_title" class="regular-text" value="<?php _e( 'Prefer to Login with Social Media', 'eddslg' );?>" /><br/>
							<span class="description"><?php _e( 'Enter a social login title.', 'eddslg' );?></span>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="edd_slg_redirect_url"><?php _e( 'Redirect URL:', 'eddslg' );?></label>		
						</th>
						<td>
							<input type="text" id="edd_slg_redirect_url" class="regular-text" value="" /><br/>
							<span class="description"><?php _e( 'Enter a redirect URL for users after they login with social media. The URL must start with', 'eddslg' ).' http://';?></span>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="edd_slg_show_on_page"><?php _e( 'Show Only on Page / Post:', 'eddslg' );?></label>		
						</th>
						<td>
							<input type="checkbox" id="edd_slg_show_on_page" value="1" /><br />
							<span class="description"><?php _e( 'Check this box if you want to show social login buttons only on inner page of posts and pages.', 'eddslg' );?></span>
						</td>
					</tr>
				</tbody>
			</table>
			
		</div><!--edd_slg_login_options-->
		
		<div id="edd_slg_insert_container" >
			<input type="button" class="button-secondary" id="edd_slg_insert_shortcode" value="<?php _e( 'Insert Shortcode', 'eddslg' ); ?>">
		</div>
		
	</div><!--.edd-slg-popup-->
	
</div><!--.edd-slg-popup-content-->
<div class="edd-slg-popup-overlay"></div>