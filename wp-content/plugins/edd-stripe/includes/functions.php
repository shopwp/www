<?php

/**
 * Allows preconfigured Stripe API requests to be made.
 *
 * @since 2.7.0
 *
 * @throws \EDD_Stripe_Utils_Exceptions_Stripe_Object_Not_Found When attempting to call an object or method that is not available.
 * @throws \Stripe\Exception
 *
 * @param string $object Name of the Stripe object to request.
 * @param string $method Name of the API operation to perform during the request.
 * @param mixed ...$args Additional arguments to pass to the request.
 * @return \Stripe\StripeObject 
 */
function edds_api_request( $object, $method, $args = null ) {
	$api = new EDD_Stripe_API();

	return call_user_func_array( array( $api, 'request' ), func_get_args() );
}

/**
 * Converts a truthy value (e.g. '1', 'yes') to a bool.
 *
 * @since 2.8.0
 *
 * @param mixed $truthy_value Truthy value.
 * @return bool
 */
function edds_truthy_to_bool( $truthy_value ) {
	$truthy = array(
		'yes',
		1,
		'1',
		'true',
	);

	return is_bool( $truthy_value )
		? $truthy_value
		: in_array( strtolower( $truthy_value ), $truthy, true );
}

/**
 * Retrieve the exsting cards setting.
 * @return bool
 */
function edd_stripe_existing_cards_enabled() {
	$use_existing_cards = edd_get_option( 'stripe_use_existing_cards', false );
	return ! empty( $use_existing_cards );
}

/**
 * Given a user ID, retrieve existing cards within stripe.
 *
 * @since 2.6
 * @param int $user_id
 *
 * @return array
 */
function edd_stripe_get_existing_cards( $user_id = 0 ) {
	if ( empty( $user_id ) ) {
		return array();
	}

	$enabled = edd_stripe_existing_cards_enabled();

	if ( ! $enabled ) {
		return array();
	}

	static $existing_cards;

	if ( ! is_null( $existing_cards ) && array_key_exists( $user_id, $existing_cards ) ) {
		return $existing_cards[ $user_id ];
	}

	// Check if the user has existing cards
	$customer_cards     = array();
	$stripe_customer_id = edds_get_stripe_customer_id( $user_id );

	if ( ! empty( $stripe_customer_id ) ) {
		try {
			$stripe_customer = edds_api_request( 'Customer', 'retrieve', $stripe_customer_id );

			if ( isset( $stripe_customer->deleted ) && $stripe_customer->deleted ) {
				return $customer_cards;
			}

			$payment_methods = edds_api_request( 'PaymentMethod', 'all', array(
				'type'     => 'card',
				'customer' => $stripe_customer->id,
				'limit'    => 100,
			) );

			$cards = edds_api_request( 'Customer', 'allSources', $stripe_customer->id, array(
				'limit' => 100,
			)	);

			$sources = array_merge( $payment_methods->data, $cards->data );

			foreach ( $sources as $source ) {
				if ( ! in_array( $source->object, array( 'payment_method', 'card' ), true ) ) {
					continue;
				}

				$source_data     = new stdClass();
				$source_data->id = $source->id;

				switch( $source->object ) {
					case 'payment_method':
						$source_data->brand           = ucwords( $source->card->brand );
						$source_data->last4           = $source->card->last4;
						$source_data->exp_month       = $source->card->exp_month;
						$source_data->exp_year        = $source->card->exp_year;
						$source_data->fingerprint     = $source->card->fingerprint;
						$source_data->address_line1   = $source->billing_details->address->line1;
						$source_data->address_line2   = $source->billing_details->address->line2;
						$source_data->address_city    = $source->billing_details->address->city;
						$source_data->address_zip     = $source->billing_details->address->postal_code;
						$source_data->address_state   = $source->billing_details->address->state;
						$source_data->address_country = $source->billing_details->address->country;

						$customer_cards[ $source->id ]['default'] = $source->id === $stripe_customer->invoice_settings->default_payment_method;
						break;
					case 'card':
						$source_data->brand           = $source->brand;
						$source_data->last4           = $source->last4;
						$source_data->exp_month       = $source->exp_month;
						$source_data->exp_year        = $source->exp_year;
						$source_data->fingerprint     = $source->fingerprint;
						$source_data->address_line1   = $source->address_line1;
						$source_data->address_line2   = $source->address_line2;
						$source_data->address_city    = $source->address_city;
						$source_data->address_zip     = $source->address_zip;
						$source_data->address_state   = $source->address_state;
						$source_data->address_country = $source->address_country;
						break;
				}

				$customer_cards[ $source->id ]['source']  = $source_data;
			}
		} catch ( Exception $e ) {
			return $customer_cards;
		}
	}

	// Show only the latest version of card for duplicates.
	$fingerprints = array();
	foreach ( $customer_cards as $key => $customer_card ) {
		$fingerprint = $customer_card['source']->fingerprint;
		if ( ! in_array( $fingerprint, $fingerprints ) ) {
			$fingerprints[] = $fingerprint;
		} else {
			unset( $customer_cards[ $key ] );
		}
	}

	// Put default card first.
	usort(
		$customer_cards,
		function( $a, $b ) {
			return ! $a['default'];
		}
	);

	$existing_cards[ $user_id ] = $customer_cards;

	return $existing_cards[ $user_id ];
}

/**
 * Look up the stripe customer id in user meta, and look to recurring if not found yet
 *
 * @since  2.4.4
 * @since  2.6               Added lazy load for moving to customer meta and ability to look up by customer ID.
 * @param  int  $id_or_email The user ID, customer ID or email to look up.
 * @param  bool $by_user_id  If the lookup is by user ID or not.
 *
 * @return string       Stripe customer ID
 */
function edds_get_stripe_customer_id( $id_or_email, $by_user_id = true ) {
	$stripe_customer_id = '';
	$meta_key           = edd_stripe_get_customer_key();

	if ( is_email( $id_or_email ) ) {
		$by_user_id = false;
	}

	$customer = new EDD_Customer( $id_or_email, $by_user_id );
	if ( $customer->id > 0 ) {
		$stripe_customer_id = $customer->get_meta( $meta_key );
	}

	if ( empty( $stripe_customer_id ) ) {
		$user_id = 0;
		if ( ! empty( $customer->user_id ) ) {
			$user_id = $customer->user_id;
		} else if ( $by_user_id && is_numeric( $id_or_email ) ) {
			$user_id = $id_or_email;
		} else if ( is_email( $id_or_email ) ) {
			$user = get_user_by( 'email', $id_or_email );
			if ( $user ) {
				$user_id = $user->ID;
			}
		}

		if ( ! isset( $user ) ) {
			$user = get_user_by( 'id', $user_id );
		}

		if ( $user ) {

			$customer = new EDD_Customer( $user->user_email );

			if ( ! empty( $user_id ) ) {
				$stripe_customer_id = get_user_meta( $user_id, $meta_key, true );

				// Lazy load migrating data over to the customer meta from Stripe issue #113
				$customer->update_meta( $meta_key, $stripe_customer_id );
			}

		}

	}

	if ( empty( $stripe_customer_id ) && class_exists( 'EDD_Recurring_Subscriber' ) ) {

		$subscriber   = new EDD_Recurring_Subscriber( $id_or_email, $by_user_id );

		if ( $subscriber->id > 0 ) {

			$verified = false;

			if ( ( $by_user_id && $id_or_email == $subscriber->user_id ) ) {
				// If the user ID given, matches that of the subscriber
				$verified = true;
			} else {
				// If the email used is the same as the primary email
				if ( $subscriber->email == $id_or_email ) {
					$verified = true;
				}

				// If the email is in the EDD 2.6 Additional Emails
				if ( property_exists( $subscriber, 'emails' ) && in_array( $id_or_email, $subscriber->emails ) ) {
					$verified = true;
				}
			}

			if ( $verified ) {
				$stripe_customer_id = $subscriber->get_recurring_customer_id( 'stripe' );
			}

		}

		if ( ! empty( $stripe_customer_id ) ) {
			$customer->update_meta( $meta_key, $stripe_customer_id );
		}

	}

	return $stripe_customer_id;
}

/**
 * Get the meta key for storing Stripe customer IDs in
 *
 * @access      public
 * @since       1.6.7
 * @return      string
 */
function edd_stripe_get_customer_key() {

	$key = '_edd_stripe_customer_id';
	if( edd_is_test_mode() ) {
		$key .= '_test';
	}
	return $key;
}

/**
 * Determines if the shop is using a zero-decimal currency
 *
 * @access      public
 * @since       1.8.4
 * @return      bool
 */
function edds_is_zero_decimal_currency() {

	$ret      = false;
	$currency = edd_get_currency();

	switch( $currency ) {

		case 'BIF' :
		case 'CLP' :
		case 'DJF' :
		case 'GNF' :
		case 'JPY' :
		case 'KMF' :
		case 'KRW' :
		case 'MGA' :
		case 'PYG' :
		case 'RWF' :
		case 'VND' :
		case 'VUV' :
		case 'XAF' :
		case 'XOF' :
		case 'XPF' :

			$ret = true;
			break;

	}

	return $ret;
}

/**
 * Retrieves a sanitized statement descriptor.
 *
 * @since 2.6.19
 *
 * @return string $statement_descriptor Sanitized statement descriptor.
 */
function edds_get_statement_descriptor() {
	$statement_descriptor = edd_get_option( 'stripe_statement_descriptor', '' );
	$statement_descriptor = edds_sanitize_statement_descriptor( $statement_descriptor );

	return $statement_descriptor;
}

/**
 * Retrieves a list of unsupported characters for Stripe statement descriptors.
 *
 * @since 2.6.19
 *
 * @return array $unsupported_characters List of unsupported characters.
 */
function edds_get_statement_descriptor_unsupported_characters() {
	$unsupported_characters = array(
		'<',
		'>',
		'"',
		'\'',
		'\\',
		'*',
	);

	/**
	 * Filters the list of unsupported characters for Stripe statement descriptors.
	 *
	 * @since 2.6.19
	 *
	 * @param array $unsupported_characters List of unsupported characters.
	 */
	$unsupported_characters = apply_filters( 'edds_get_statement_descriptor_unsupported_characters', $unsupported_characters  );

	return $unsupported_characters;
}

/**
 * Sanitizes a string to be used for a statement descriptor.
 *
 * @since 2.6.19
 *
 * @link https://stripe.com/docs/connect/statement-descriptors#requirements
 *
 * @param string $statement_descriptor Statement descriptor to sanitize.
 * @return string $statement_descriptor Sanitized statement descriptor.
 */
function edds_sanitize_statement_descriptor( $statement_descriptor ) {
	$unsupported_characters = edds_get_statement_descriptor_unsupported_characters();

	$statement_descriptor = trim( str_replace( $unsupported_characters, '', $statement_descriptor ) );
	$statement_descriptor = substr( $statement_descriptor, 0, 22 );

	return $statement_descriptor;
}

/**
 * Retrieves a given registry instance by name.
 *
 * @since 2.6.19
 *
 * @param string $name Registry name.
 * @return null|EDD_Stripe_Registry Null if the registry doesn't exist, otherwise the object instance.
 */
function edds_get_registry( $name ) {
	switch( $name ) {
		case 'admin-notices':
			$registry = EDD_Stripe_Admin_Notices_Registry::instance();
			break;
		default:
			$registry = null;
			break;
	}

	return $registry;
}

/**
 * Attempts to verify a nonce from various payment forms when the origin
 * of the action isn't explicitly known.
 *
 * This could be coming from the Checkout, Payment Authorization,
 * or Update Payment Method (Recurring) form.
 *
 * @since 2.8.0
 *
 * @return bool
 */
function edds_verify_payment_form_nonce() {
	// Checkout.
	$nonce = isset( $_POST['edd-process-checkout-nonce'] )
		? sanitize_text_field( $_POST['edd-process-checkout-nonce'] )
		: '';

	if ( ! empty( $nonce ) ) {
		return wp_verify_nonce( $nonce, 'edd-process-checkout' );
	}

	// Update Payment Method.
	$nonce = isset( $_POST['edd_recurring_update_nonce'] )
		? sanitize_text_field( $_POST['edd_recurring_update_nonce'] )
		: '';

	if ( ! empty( $nonce ) ) {
		return wp_verify_nonce( $nonce, 'update-payment' );
	}

	return false;
}
