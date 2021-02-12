<?php

/**
 * Given a Payment ID, extract the transaction ID from Stripe
 *
 * @param  string $payment_id       Payment ID
 * @return string                   Transaction ID
 */
function edds_get_payment_transaction_id( $payment_id ) {

	$txn_id = '';
	$notes  = edd_get_payment_notes( $payment_id );

	foreach ( $notes as $note ) {
		if ( preg_match( '/^Stripe Charge ID: ([^\s]+)/', $note->comment_content, $match ) ) {
			$txn_id = $match[1];
			continue;
		}
	}

	return apply_filters( 'edds_set_payment_transaction_id', $txn_id, $payment_id );
}
add_filter( 'edd_get_payment_transaction_id-stripe', 'edds_get_payment_transaction_id', 10, 1 );

/**
 * Given a transaction ID, generate a link to the Stripe transaction ID details
 *
 * @since  1.9.1
 * @param  string $transaction_id The Transaction ID
 * @param  int    $payment_id     The payment ID for this transaction
 * @return string                 A link to the Stripe transaction details
 */
function edd_stripe_link_transaction_id( $transaction_id, $payment_id ) {

	$test = edd_get_payment_meta( $payment_id, '_edd_payment_mode' ) === 'test' ? 'test/' : '';
	$url  = '<a href="https://dashboard.stripe.com/' . $test . 'payments/' . $transaction_id . '" target="_blank">' . $transaction_id . '</a>';

	return apply_filters( 'edd_stripe_link_payment_details_transaction_id', $url );

}
add_filter( 'edd_payment_details_transaction_id-stripe', 'edd_stripe_link_transaction_id', 10, 2 );


/**
 * Display the payment status filters
 *
 * @since 1.6
 * @return array
 */
function edds_payment_status_filters( $views ) {
	$payment_count             = wp_count_posts( 'edd_payment' );
	$preapproval_count         = '&nbsp;<span class="count">(' . $payment_count->preapproval . ')</span>';
	$preapproval_pending_count = '&nbsp;<span class="count">(' . $payment_count->preapproval_pending . ')</span>';
	$cancelled_count           = '&nbsp;<span class="count">(' . $payment_count->cancelled . ')</span>';
	$current                   = isset( $_GET['status'] ) ? $_GET['status'] : '';

	$views['preapproval']         = sprintf( '<a href="%s"%s>%s</a>', esc_url( add_query_arg( 'status', 'preapproval', admin_url( 'edit.php?post_type=download&page=edd-payment-history' ) ) ), $current === 'preapproval' ? ' class="current"' : '', __( 'Preapproved', 'edds' ) . $preapproval_count );
	$views['pending_preapproval'] = sprintf( '<a href="%s"%s>%s</a>', esc_url( add_query_arg( 'status', 'preapproval_pending', admin_url( 'edit.php?post_type=download&page=edd-payment-history' ) ) ), $current === 'preapproval_pending' ? ' class="current"' : '', __( 'Preapproval Pending', 'edds' ) . $preapproval_pending_count );
	$views['cancelled']           = sprintf( '<a href="%s"%s>%s</a>', esc_url( add_query_arg( 'status', 'cancelled', admin_url( 'edit.php?post_type=download&page=edd-payment-history' ) ) ), $current === 'cancelled' ? ' class="current"' : '', __( 'Cancelled', 'edds' ) . $cancelled_count );

	return $views;
}
add_filter( 'edd_payments_table_views', 'edds_payment_status_filters' );

/**
 * Show the Process / Cancel buttons for preapproved payments
 *
 * @since 1.6
 * @return string
 */
function edds_payments_column_data( $value, $payment_id, $column_name ) {
	if ( $column_name == 'status' ) {
		$payment = edd_get_payment( $payment_id );

		if ( empty( $payment ) ) {
			return $value;
		}

		$status      = $payment->status;
		$customer_id = $payment->get_meta( '_edds_stripe_customer_id', true );

		if ( empty( $customer_id ) ) {
			return $value;
		}

		$nonce = wp_create_nonce( 'edds-process-preapproval' );

		$preapproval_args     = array(
			'payment_id'      => $payment_id,
			'nonce'           => $nonce,
			'edd-action'      => 'charge_stripe_preapproval'
		);

		$cancel_args          = array(
			'preapproval_key' => $customer_id,
			'payment_id'      => $payment_id,
			'nonce'           => $nonce,
			'edd-action'      => 'cancel_stripe_preapproval'
		);

		$actions = array();

		$value .= '<p class="row-actions">';

		if ( in_array( $status, array( 'preapproval', 'preapproval_pending' ), true ) ) {
			$actions[] = '<a href="' . esc_url( add_query_arg( $preapproval_args, admin_url( 'edit.php?post_type=download&page=edd-payment-history' ) ) ) . '">' . __( 'Process', 'edds' ) . '</a>';

			if ( 'cancelled' !== $status ) {
				$actions[] = '<span class="delete"><a href="' . esc_url( add_query_arg( $cancel_args, admin_url( 'edit.php?post_type=download&page=edd-payment-history' ) ) ) . '">' . __( 'Cancel', 'edds' ) . '</a></span>';
			}
		}

		$value .= implode( ' | ', $actions );

		$value .= '</p>';
	}
	return $value;
}
add_filter( 'edd_payments_table_column', 'edds_payments_column_data', 20, 3 );