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
		! version_compare( EDD_RECURRING_VERSION, '2.8.8', '>' )
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
 * Sets the stripe-checkout parameter if the direct parameter is present in the [purchase_link] short code
 *
 * @since  2.0
 * @return array
 */
function edd_stripe_purchase_link_shortcode_atts( $out, $pairs, $atts ) {

	if( ! empty( $out['direct'] ) ) {

		$out['stripe-checkout'] = true;
		$out['direct'] = true;

	} else {

		foreach( $atts as $key => $value ) {
			if( false !== strpos( $value, 'stripe-checkout' ) ) {
				$out['stripe-checkout'] = true;
				$out['direct'] = true;
			}
		}

	}

	return $out;
}
add_filter( 'shortcode_atts_purchase_link', 'edd_stripe_purchase_link_shortcode_atts', 10, 3 );

/**
 * Sets the stripe-checkout parameter if the direct parameter is present in edd_get_purchase_link()
 *
 * @since  2.0
 * @return array
 */
function edd_stripe_purchase_link_atts( $args ) {

	if( ! empty( $args['direct'] ) && edd_is_gateway_active( 'stripe' ) ) {

		$args['stripe-checkout'] = true;
		$args['direct'] = true;
	}

	return $args;
}
add_filter( 'edd_purchase_link_args', 'edd_stripe_purchase_link_atts', 10 );

/**
 * Injects the Stripe token and customer email into the pre-gateway data
 *
 * @since  2.0
 * @return array
 */
function edd_stripe_straight_to_gateway_data( $purchase_data ) {
	$purchase_data['gateway'] = 'stripe';
	$_REQUEST['edd-gateway']  = 'stripe';

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