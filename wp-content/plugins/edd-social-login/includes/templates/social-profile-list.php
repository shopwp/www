<?php
/**
 * Social Profile List Template
 * 
 * Handles to load social media connected list
 * 
 * Override this template by copying it to yourtheme/edd-social-login/edd-slg-social-profile-list.php
 * 
 * @package Easy Digital download - Social Login
 * @since 1.3.0
 */

global $edd_slg_model;

$model = $edd_slg_model;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>



<div class="edd-slg-login-loader">
	<img src="<?php echo EDD_SLG_IMG_URL;?>/social-loader.gif" alt="<?php echo __( 'Social Loader', 'eddslg' );?>"/>
</div>

<?php
if( isset($_SESSION['edd_slg_unlinked_notice']) && !empty($_SESSION['edd_slg_unlinked_notice'])) {
	edd_slg_success_message($_SESSION['edd_slg_unlinked_notice']);
	$_SESSION['edd_slg_unlinked_notice'] = "";
}
if( isset($_SESSION['edd_slg_linked_notice']) && !empty($_SESSION['edd_slg_linked_notice'])) {
	edd_slg_success_message($_SESSION['edd_slg_linked_notice']);
	$_SESSION['edd_slg_linked_notice'] = "";
}
?>
<div class="edd-social-login-profile edd-slg-social-wrap">
	<h2><?php
		echo __( 'My Social Login Accounts', 'eddslg' );
	?></h2><?php

	if ( $linked_profiles ) { ?>
		<p><?php
			echo $connected_link_heading;
			
			if( $can_link ) {?>
				
				<a class="edd-slg-show-link" href="javascript:void(0);"><?php echo $add_more_link; ?></a><?php 
				
			}?>
		</p>
		<div class="table-container">
		<table class="edd-social-login-linked-profiles">
			<thead>
				<tr>
					<th><?php echo __( 'Provider', 'eddslg' ); ?></th>
					<th><?php echo __( 'Account', 'eddslg' ); ?></th>
					<th><?php echo __( 'Last Login', 'eddslg' ); ?></th>
					<th><?php echo __( 'Unlink', 'eddslg' ); ?></th>
				</tr>
			</thead><?php

			foreach ( $linked_profiles as $profile => $value ) {
				

				$provider		= EDD_SLG_IMG_URL . "/" . $profile . "-provider.png";
				$provider_data	= $model->edd_slg_get_user_common_social_data( $value, $profile );
				?>
				
				<tr>
					<!-- Display provider image-->
					<td data-title="<?php __( 'Provider', 'eddslg' ); ?>">
						<img src="<?php echo $provider; ?>" >
					</td>
					<!-- Display account email id image-->
					<td data-title="<?php __( 'Account', 'eddslg' ); ?>"><?php
						echo !empty( $provider_data['email'] ) ? $provider_data['email'] : $provider_data['name'];
					?></td>
					<td><?php
						$login_timestamp	= edd_slg_get_social_last_login_timestamp( $user_id, $profile );
						
						if( !empty( $login_timestamp ) ) {
							printf( __( '%s @ %s', 'eddslg' ), date_i18n( get_option( 'date_format' ), $login_timestamp ), date_i18n( get_option( 'time_format' ), $login_timestamp ) );
						} else {
							echo __( 'Never', 'eddslg' );
						}
					?></td>
					<td><?php
						if( $profile != $primary_social ) {?>
							<!-- Display profile unlink url-->
							<a href="javascript:void(0);" class="button edd-slg-social-unlink-profile" id="<?php echo $profile;?>"><?php
								echo __( 'Unlink', 'eddslg' );
							?></a><?php 
						} else {
							echo '<strong>' . __( 'Primary', 'eddslg' ) . '</strong>';
						}
					?></td>
				</tr><?php
			}?>
			<tfoot>
				<tr>
					<th><?php echo __( 'Provider', 'eddslg' ); ?></th>
					<th><?php echo __( 'Account', 'eddslg' ); ?></th>
					<th><?php echo __( 'Last Login', 'eddslg' ); ?></th>
					<th><?php echo __( 'Unlink', 'eddslg' ); ?></th>
				</tr>
			</tfoot>
		</table></div><?php
	} else {?>

		<p><?php 
			echo $no_social_connected;
			
			if( $can_link ) {?>
				<a class="edd-slg-show-link" href="javascript:void(0);"><?php echo $connect_now_link; ?></a><?php 
			}?>
		</p><?php
	}?>

	<div class="edd-slg-profile-link-container" style="<?php if( $can_link ) { echo 'display:none;'; }?>"><?php
		// display social link buttons
		edd_slg_link_buttons();?>

	</div>
</div>