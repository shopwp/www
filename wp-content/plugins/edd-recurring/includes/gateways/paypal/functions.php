<?php
/**
 * Retrieve PayPal API credentials
 *
 * @access      public
 * @since       2.4
 */
function edd_recurring_get_paypal_api_credentials() {

	$mode = 'live';

	if ( edd_is_test_mode() ) {
		$mode = 'test';
	}

	// Retrieve credentials from core
	$creds = array(
		'username'  => edd_get_option( 'paypal_' . $mode . '_api_username' ),
		'password'  => edd_get_option( 'paypal_' . $mode . '_api_password' ),
		'signature' => edd_get_option( 'paypal_' . $mode . '_api_signature' )
	);

	// Loop over credentials and make sure they were found. If empty, check for credentials from PayPal Pro / Express gateway
	foreach( $creds as $key => $cred ) {

		if( empty( $cred ) ) {
			$creds[ $key ] = edd_get_option( $mode . '_paypal_api_' . $key );
		}
	}

	return apply_filters( 'edd_recurring_get_paypal_api_credentials', $creds );
}