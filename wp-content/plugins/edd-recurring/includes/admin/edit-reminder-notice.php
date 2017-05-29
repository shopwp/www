<?php
/**
 * Edit Reminder Notice
 *
 * @package     EDD Recurring
 * @copyright   Copyright (c) 2014, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.4
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! isset( $_GET['notice'] ) || ! is_numeric( $_GET['notice'] ) ) {
	//wp_die( __( 'Something went wrong.', 'edd-recurring' ), __( 'Error', 'edd-recurring' ) );
}

$notices  = new EDD_Recurring_Reminders();
$notice_id = absint( $_GET['notice'] );
$notice    = $notices->get_notice( $notice_id );
?>
<div class="wrap">
	<h1><?php _e( 'Edit Reminder Notice', 'edd-recurring' ); ?> -
		<a href="<?php echo admin_url( 'edit.php?post_type=download&page=edd-settings&tab=extensions&section=recurring' ); ?>" class="add-new-h2"><?php _e( 'Go Back', 'edd-recurring' ); ?></a>
	</h1>

	<form id="edd-edit-reminder-notice" action="" method="post">
		<table class="form-table">
			<tbody>
			<tr>
				<th scope="row" valign="top">
					<label for="edd-notice-type"><?php _e( 'Notice Type', 'edd-recurring' ); ?></label>
				</th>
				<td>
					<select name="type" id="edd-notice-type">
						<?php foreach ( $notices->get_notice_types() as $type => $label ) : ?>
							<option value="<?php echo esc_attr( $type ); ?>"<?php selected( $type, $notice['type'] ); ?>><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select>
					
					<p class="description"><?php _e( 'Is this a renewal notice or an expiration notice?', 'edd-recurring' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row" valign="top">
					<label for="edd-notice-subject"><?php _e( 'Email Subject', 'edd-recurring' ); ?></label>
				</th>
				<td>
					<input name="subject" id="edd-notice-subject" class="edd-notice-subject" type="text" value="<?php echo esc_attr( stripslashes( $notice['subject'] ) ); ?>" />

					<p class="description"><?php _e( 'The subject line of the reminder notice email', 'edd-recurring' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row" valign="top">
					<label for="edd-notice-period"><?php _e( 'Email Period', 'edd-recurring' ); ?></label>
				</th>
				<td>
					<select name="period" id="edd-notice-period">
						<?php foreach ( $notices->get_notice_periods() as $period => $label ) : ?>
							<option value="<?php echo esc_attr( $period ); ?>"<?php selected( $period, $notice['send_period'] ); ?>><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select>

					<p class="description"><?php _e( 'When should this email be sent?', 'edd-recurring' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row" valign="top">
					<label for="edd-notice-message"><?php _e( 'Email Message', 'edd-recurring' ); ?></label>
				</th>
				<td>
					<?php wp_editor( wpautop( wp_kses_post( wptexturize( $notice['message'] ) ) ), 'message', array( 'textarea_name' => 'message' ) ); ?>
					<p class="description"><?php _e( 'The email message to be sent with the reminder notice. The following template tags can be used in the message:', 'edd-recurring' ); ?></p>
					<ul>
						<li>{name} <?php _e( 'The customer\'s name', 'edd-recurring' ); ?></li>
						<li>{subscription_name} <?php _e( 'The name of the product the subscription belongs to', 'edd-recurring' ); ?></li>
						<li>{expiration} <?php _e( 'The expiration date for the subscription', 'edd-recurring' ); ?></li>
						<li>{amount} <?php _e( 'The recurring amount of the subscription', 'edd-recurring' ); ?></li>
					</ul>
				</td>
			</tr>

			</tbody>
		</table>
		<p class="submit">
			<input type="hidden" name="edd-action" value="recurring_edit_reminder_notice" />
			<input type="hidden" name="notice-id" value="<?php echo esc_attr( $notice_id ); ?>" />
			<input type="hidden" name="edd-recurring-reminder-notice-nonce" value="<?php echo wp_create_nonce( 'edd_recurring_reminder_nonce' ); ?>" />
			<input type="submit" value="<?php _e( 'Update Reminder Notice', 'edd-recurring' ); ?>" class="button-primary" />
		</p>
	</form>
</div>
