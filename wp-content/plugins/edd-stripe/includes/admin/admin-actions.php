<?php

/**
 * Trigger preapproved payment charge
 *
 * @since 1.6
 * @return void
 */
function edds_process_preapproved_charge() {

	if( empty( $_GET['nonce'] ) )
		return;

	if( ! wp_verify_nonce( $_GET['nonce'], 'edds-process-preapproval' ) )
		return;

	$payment_id  = absint( $_GET['payment_id'] );
	$charge      = edds_charge_preapproved( $payment_id );

	if ( $charge ) {
		wp_redirect( esc_url_raw( add_query_arg( array( 'edd-message' => 'preapproval-charged' ), admin_url( 'edit.php?post_type=download&page=edd-payment-history' ) ) ) ); exit;
	} else {
		wp_redirect( esc_url_raw( add_query_arg( array( 'edd-message' => 'preapproval-failed' ), admin_url( 'edit.php?post_type=download&page=edd-payment-history' ) ) ) ); exit;
	}

}
add_action( 'edd_charge_stripe_preapproval', 'edds_process_preapproved_charge' );


/**
 * Cancel a preapproved payment
 *
 * @since 1.6
 * @return void
 */
function edds_process_preapproved_cancel() {
	global $edd_options;

	if( empty( $_GET['nonce'] ) )
		return;

	if( ! wp_verify_nonce( $_GET['nonce'], 'edds-process-preapproval' ) )
		return;

	$payment_id  = absint( $_GET['payment_id'] );
	$customer_id = get_post_meta( $payment_id, '_edds_stripe_customer_id', true );

	if( empty( $customer_id ) || empty( $payment_id ) ) {
		return;
	}

	if ( 'preapproval' !== get_post_status( $payment_id ) ) {
		return;
	}

	edd_insert_payment_note( $payment_id, __( 'Preapproval cancelled', 'edds' ) );
	edd_update_payment_status( $payment_id, 'cancelled' );
	delete_post_meta( $payment_id, '_edds_stripe_customer_id' );

	wp_redirect( esc_url_raw( add_query_arg( array( 'edd-message' => 'preapproval-cancelled' ), admin_url( 'edit.php?post_type=download&page=edd-payment-history' ) ) ) ); exit;
}
add_action( 'edd_cancel_stripe_preapproval', 'edds_process_preapproved_cancel' );

/**
 * Admin Messages
 *
 * @since 1.6
 * @return void
 */
function edds_admin_messages() {

	if ( isset( $_GET['edd-message'] ) && 'preapproval-charged' == $_GET['edd-message'] ) {
		 add_settings_error( 'edds-notices', 'edds-preapproval-charged', __( 'The preapproved payment was successfully charged.', 'edds' ), 'updated' );
	}
	if ( isset( $_GET['edd-message'] ) && 'preapproval-failed' == $_GET['edd-message'] ) {
		 add_settings_error( 'edds-notices', 'edds-preapproval-charged', __( 'The preapproved payment failed to be charged. View order details for further details.', 'edds' ), 'error' );
	}
	if ( isset( $_GET['edd-message'] ) && 'preapproval-cancelled' == $_GET['edd-message'] ) {
		 add_settings_error( 'edds-notices', 'edds-preapproval-cancelled', __( 'The preapproved payment was successfully cancelled.', 'edds' ), 'updated' );
	}

	settings_errors( 'edds-notices' );
}
add_action( 'admin_notices', 'edds_admin_messages' );

/**
 * Add payment meta item to payments that used an existing card
 *
 * @since 2.6
 * @param $payment_id
 * @return void
 */
function edds_show_existing_card_meta( $payment_id ) {
	$payment = new EDD_Payment( $payment_id );
	$existing_card = $payment->get_meta( '_edds_used_existing_card' );
	if ( ! empty( $existing_card ) ) {
		?>
		<div class="edd-order-stripe-existing-card edd-admin-box-inside">
			<p>
				<span class="label"><?php _e( 'Used Existing Card:', 'edds' ); ?></span>&nbsp;
				<span><?php _e( 'Yes', 'edds' ); ?></span>
			</p>
		</div>
		<?php
	}
}
add_action( 'edd_view_order_details_payment_meta_after', 'edds_show_existing_card_meta', 10, 1 );