<?php

/**
 * Register our new payment status labels for EDD
 *
 * @since 1.6
 * @return array
 */
function edds_payment_status_labels( $statuses ) {
	$statuses['preapproval'] = __( 'Preapproved', 'edds' );
	$statuses['cancelled']   = __( 'Cancelled', 'edds' );
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

	if( isset( $_REQUEST['edd_stripe_token'] ) || isset( $_REQUEST['edd_stripe_existing_card'] ) ) {

		global $edd_stripe_is_buy_now;

		$edd_stripe_is_buy_now = true;

		$purchase_data['gateway'] = 'stripe';
		$_REQUEST['edd-gateway']  = 'stripe';

		if( isset( $_REQUEST['edd_email'] ) ) {
			$purchase_data['user_info']['email'] = $_REQUEST['edd_email'];
			$purchase_data['user_email'] = $_REQUEST['edd_email'];
		}

	}
	return $purchase_data;
}
add_filter( 'edd_straight_to_gateway_purchase_data', 'edd_stripe_straight_to_gateway_data' );

/**
 * Sets the text of the Purchase button when Stripe Checkout is enabled
 *
 * @since  2.5
 * @return $text Value of the checkout submit button
 */
function edds_filter_purchase_button_text( $text, $key, $default ) {

	if( 'stripe' == edd_get_chosen_gateway() && edd_get_option( 'stripe_checkout' ) ) {
		$text = edd_get_option( 'stripe_checkout_button_text' );
	}

	return $text;

}
add_filter( 'edd_get_option_checkout_label', 'edds_filter_purchase_button_text', 10, 3 );

/**
 * Process the POST Data for the Credit Card Form, if a token wasn't supplied
 *
 * @since  2.2
 * @return array The credit card data from the $_POST
 */
function edds_process_post_data( $purchase_data ) {
	if ( ! isset( $_POST['card_name'] ) || strlen( trim( $_POST['card_name'] ) ) == 0 )
		edd_set_error( 'no_card_name', __( 'Please enter a name for the credit card.', 'edds' ) );

	if ( ! isset( $_POST['card_number'] ) || strlen( trim( $_POST['card_number'] ) ) == 0 )
		edd_set_error( 'no_card_number', __( 'Please enter a credit card number.', 'edds' ) );

	if ( ! isset( $_POST['card_cvc'] ) || strlen( trim( $_POST['card_cvc'] ) ) == 0 )
		edd_set_error( 'no_card_cvc', __( 'Please enter a CVC/CVV for the credit card.', 'edds' ) );

	if ( ! isset( $_POST['card_exp_month'] ) || strlen( trim( $_POST['card_exp_month'] ) ) == 0 )
		edd_set_error( 'no_card_exp_month', __( 'Please enter a expiration month.', 'edds' ) );

	if ( ! isset( $_POST['card_exp_year'] ) || strlen( trim( $_POST['card_exp_year'] ) ) == 0 )
		edd_set_error( 'no_card_exp_year', __( 'Please enter a expiration year.', 'edds' ) );

	$card_data = array(
		'number'          => $purchase_data['card_info']['card_number'],
		'name'            => $purchase_data['card_info']['card_name'],
		'exp_month'       => $purchase_data['card_info']['card_exp_month'],
		'exp_year'        => $purchase_data['card_info']['card_exp_year'],
		'cvc'             => $purchase_data['card_info']['card_cvc'],
		'address_line1'   => $purchase_data['card_info']['card_address'],
		'address_line2'   => $purchase_data['card_info']['card_address_2'],
		'address_city'    => $purchase_data['card_info']['card_city'],
		'address_zip'     => $purchase_data['card_info']['card_zip'],
		'address_state'   => $purchase_data['card_info']['card_state'],
		'address_country' => $purchase_data['card_info']['card_country']
	);

	return $card_data;
}

/**
 * Retrieves the locale used for Checkout modal window
 *
 * @since  2.5
 * @return string The locale to use
 */
function edds_get_stripe_checkout_locale() {
	return apply_filters( 'edd_stripe_checkout_locale', 'auto' );
}