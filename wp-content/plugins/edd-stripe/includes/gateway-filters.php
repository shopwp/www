<?php

/**
 * Removes Stripe from active gateways if Recurring version is < 2.9.
 *
 * @since 2.7.0
 *
 * @param array $enabled_gateways Enabled gateways that allow purchasing.
 * @return array
 */
function edds_require_recurring_290( $enabled_gateways ) {
	if ( 
		isset( $enabled_gateways['stripe'] ) &&
		defined( 'EDD_RECURRING_VERSION' ) &&
		! version_compare( EDD_RECURRING_VERSION, '2.9.99', '>' )
	) {
		unset( $enabled_gateways['stripe'] );
	}

	return $enabled_gateways;
}
add_filter( 'edd_enabled_payment_gateways', 'edds_require_recurring_290', 20 );

/**
 * Register our new payment status labels for EDD
 *
 * @since 1.6
 * @return array
 */
function edds_payment_status_labels( $statuses ) {
	$statuses['preapproval']         = __( 'Preapproved', 'edds' );
	$statuses['preapproval_pending'] = __( 'Preapproval Pending', 'edds' );
	$statuses['cancelled']           = __( 'Cancelled', 'edds' );
	return $statuses;
}
add_filter( 'edd_payment_statuses', 'edds_payment_status_labels' );

/**
 * Injects the Stripe token and customer email into the pre-gateway data
 *
 * @since 2.0
 *
 * @param array $purchase_data
 * @return array
 */
function edd_stripe_straight_to_gateway_data( $purchase_data ) {

	$gateways = edd_get_enabled_payment_gateways();

	if ( isset( $gateways['stripe'] ) ) {
		$_REQUEST['edd-gateway']  = 'stripe';
		$purchase_data['gateway'] = 'stripe';
	}

	return $purchase_data;
}
add_filter( 'edd_straight_to_gateway_purchase_data', 'edd_stripe_straight_to_gateway_data' );

/**
 * Process the POST Data for the Credit Card Form, if a token wasn't supplied
 *
 * @since  2.2
 * @return array The credit card data from the $_POST
 */
function edds_process_post_data( $purchase_data ) {
	if ( ! isset( $purchase_data['gateway'] ) || 'stripe' !== $purchase_data['gateway'] ) {
		return;
	}

	if ( isset( $_POST['edd_stripe_existing_card'] ) && 'new' !== $_POST['edd_stripe_existing_card'] ) {
		return;
	}

	// Require a name for new cards.
	if ( ! isset( $_POST['card_name'] ) || strlen( trim( $_POST['card_name'] ) ) === 0 ) {
		edd_set_error( 'no_card_name', __( 'Please enter a name for the credit card.', 'edds' ) );
	}
}
add_action( 'edd_checkout_error_checks', 'edds_process_post_data' );

/**
 * Retrieves the locale used for Checkout modal window
 *
 * @since  2.5
 * @return string The locale to use
 */
function edds_get_stripe_checkout_locale() {
	return apply_filters( 'edd_stripe_checkout_locale', 'auto' );
}

/**
 * Sets the $_COOKIE global when a logged in cookie is available.
 *
 * We need the global to be immediately available so calls to wp_create_nonce()
 * within the same session will use the newly available data.
 *
 * @since 2.8.0
 *
 * @link https://wordpress.stackexchange.com/a/184055
 *
 * @param string $logged_in_cookie The logged-in cookie value.
 */
function edds_set_logged_in_cookie_global( $logged_in_cookie ) {
	$_COOKIE[ LOGGED_IN_COOKIE ] = $logged_in_cookie;
}
