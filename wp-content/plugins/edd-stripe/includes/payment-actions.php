<?php
/**
 * Payment actions.
 *
 * @package EDD_Stripe
 * @since   2.7.0
 */

/**
 * Starts the process of completing a purchase with Stripe.
 *
 * Generates an intent that can require user authorization before proceeding.
 *
 * @link https://stripe.com/docs/payments/intents
 * @since 2.7.0
 *
 * @param array $purchase_data {
 *   Purchase form data.
 *
 * }
 */
function edds_process_purchase_form( $purchase_data ) {

	// Catch a straight to gateway request.
	// Remove the error set by the "gateway mismatch" and allow the redirect.
	if ( isset( $_REQUEST['edd_action'] ) && 'straight_to_gateway' === $_REQUEST['edd_action'] ) {
		foreach ( $purchase_data['downloads'] as $download ) {
			$options = isset( $download['options'] ) ? $download['options'] : array();
			$options['quantity'] = isset( $download['quantity'] ) ? $download['quantity'] : 1;

			edd_add_to_cart( $download['id'], $options );
		}

		edd_unset_error( 'edd-straight-to-gateway-error' );
		edd_send_back_to_checkout();

		return;
	}

	try {
		/**
		 * Allows processing before an Intent is created.
		 *
		 * @since 2.7.0
		 *
		 * @param array $purchase_data Purchase data.
		 */
		do_action( 'edds_pre_process_purchase_form', $purchase_data );

		$payment_method_id     = isset( $_POST['payment_method_id'] ) ? sanitize_text_field( $_POST['payment_method_id'] ) : false;
		$payment_method_exists = isset( $_POST['payment_method_exists'] ) ? 'true' == $_POST['payment_method_exists'] : false;

		if ( ! $payment_method_id ) {
			throw new \Exception( esc_html__( 'Unable to locate Payment Method.', 'edds' ) );
		}

		if ( edds_is_zero_decimal_currency() ) {
			$amount = $purchase_data['price'];
		} else {
			$amount = round( $purchase_data['price'] * 100, 0 );
		}

		// Retrieves or creates a Stripe Customer.
		$customer = edds_checkout_setup_customer( $purchase_data );

		if ( ! $customer ) {
			throw new \Exception( esc_html__( 'Customer creation failed while processing a payment.', 'edds' ) );
		}

		/**
		 * Allows processing before an Intent is created, but 
		 * after a \Stripe\Customer is available.
		 *
		 * @since 2.7.0
		 *
		 * @param array            $purchase_data Purchase data.
		 * @param \Stripe\Customer $customer Stripe Customer object.
		 */
		do_action( 'edds_process_purchase_form_before_intent', $purchase_data, $customer );

		// Flag if this is the first card being attached to the Customer.
		$existing_payment_methods = edd_stripe_get_existing_cards( $purchase_data['user_info']['id'] );
		$is_first_payment_method  = empty( $existing_payment_methods );

		// Update PaymentMethod details if necessary.
		if ( $payment_method_exists && ! empty( $_POST['edd_stripe_update_billing_address'] ) ) {
			$address_info    = $purchase_data['user_info']['address'];
			$billing_address = array();

			foreach ( $address_info as $key => $value ) {
				// Adjusts address data keys to work with PaymentMethods.
				switch( $key ) {
					case 'zip':
						$key = 'postal_code';
						break;
				}

				$billing_address[ $key ] = ! empty( $value ) ? sanitize_text_field( $value ) : '';
			}

			edds_api_request( 'PaymentMethod', 'update', $payment_method_id, array(
				'billing_details' => array(
					'address' => $billing_address,
				),
			) );
		}

		// Shared Intent arguments.
		$intent_args = array(
			'confirm'        => true,
			'payment_method' => $payment_method_id,
			'customer'       => $customer->id,
			'metadata'       => array(
				'email' => $purchase_data['user_info']['email'],
			),
		);

		// Attempt to map existing charge arguments to PaymentIntents.
		if ( has_filter( 'edds_create_charge_args' ) ) {
			/**
			 * @deprecated 2.7.0 In favor of `edds_create_payment_intent_args`.
			 *
			 * @param array $intent_args
			 */
			$old_charge_args = apply_filters_deprecated(
				'edds_create_charge_args',
				array(
					$intent_args,
				),
				'2.7.0',
				'edds_create_payment_intent_args'
			);

			// Grab a few compatible arguments from the old charges filter.
			$compatible_keys = array(
				'amount',
				'currency',
				'customer',
				'description',
				'metadata',
				'application_fee',
			);

			foreach ( $compatible_keys as $compatible_key ) {
				if ( ! isset( $old_charge_args[ $compatible_key ] ) ) {
					continue;
				}

				$value = $old_charge_args[ $compatible_key ];

				switch ( $compatible_key ) {
					case 'application_fee' :
						$intent_args['application_fee_amount'] = $value;
						break;

					default:
						// If a legacy value is an array merge it with the existing values to avoid overriding completely.
						$intent_args[ $compatible_key ] = is_array( $value ) && is_array( $intent_args[ $compatible_key ] )
							? wp_parse_args( $value, $intent_args[ $compatible_key ] )
							: $value;
				}

				edd_debug_log( __( 'Charges are no longer directly created in Stripe. Please read the following for more information: https://easydigitaldownloads.com/development/', 'edd-stripe' ), true );
			}
		}

		// Create a SetupIntent for a non-payment carts.
		if ( edd_get_option( 'stripe_preapprove_only' ) || 0 === $amount ) {
			$intent_args = array_merge(
				array(
					'usage'       => 'off_session',
					'description' => edds_get_payment_description( $purchase_data['cart_details'] ),
				),
				$intent_args
			);

			/**
			 * Filters the arguments used to create a SetupIntent.
			 *
			 * @since 2.7.0
			 *
			 * @param array $intent_args SetupIntent arguments.
			 * @param array $purchase_data {
			 *   Purchase form data.
			 *
			 * }
			 */
			$intent_args = apply_filters( 'edds_create_setup_intent_args', $intent_args, $purchase_data );

			$intent = edds_api_request( 'SetupIntent', 'create', $intent_args );

			// Manually attach PaymentMethod to the Customer.
			if ( ! $payment_method_exists && edd_stripe_existing_cards_enabled() ) {
				$payment_method = edds_api_request( 'PaymentMethod', 'retrieve', $payment_method_id );
				$payment_method->attach( array(
					'customer' => $customer->id,
				) );
			}

		// Create a PaymentIntent for an immediate charge.
		} else {
			$purchase_summary     = edds_get_payment_description( $purchase_data['cart_details'] );
			$statement_descriptor = edds_get_statement_descriptor();

			if ( empty( $statement_descriptor ) ) {
				$statement_descriptor = substr( $purchase_summary, 0, 22 );
			}

			$statement_descriptor = apply_filters( 'edds_statement_descriptor', $statement_descriptor, $purchase_data );
			$statement_descriptor = edds_sanitize_statement_descriptor( $statement_descriptor );

			if ( empty( $statement_descriptor ) ) {
				$statement_descriptor = null;
			}

			$intent_args = array_merge(
				array(
					'amount'               => $amount,
					'currency'             => edd_get_currency(),
					'setup_future_usage'   => 'off_session',
					'confirmation_method'  => 'manual',
					'save_payment_method'  => true,
					'description'          => $purchase_summary,
					'statement_descriptor' => $statement_descriptor,
				),
				$intent_args
			);

			/**
			 * Filters the arguments used to create a SetupIntent.
			 *
			 * @since 2.7.0
			 *
			 * @param array $intent_args SetupIntent arguments.
			 * @param array $purchase_data {
			 *   Purchase form data.
			 *
			 * }
			 */
			$intent_args = apply_filters( 'edds_create_payment_intent_args', $intent_args, $purchase_data );

			$intent = edds_api_request( 'PaymentIntent', 'create', $intent_args );
		}

		// Set the default payment method when attaching the first one.
		if ( $is_first_payment_method ) {
			edds_api_request( 'Customer', 'update', $customer->id, array(
				'invoice_settings' => array(
					'default_payment_method' => $payment_method_id,
				),
			) );
		}

		/**
		 * Allows further processing after an Intent is created.
		 *
		 * @since 2.7.0
		 *
		 * @param array                                     $purchase_data Purchase data.
		 * @param \Stripe\PaymentIntent|\Stripe\SetupIntent $intent Created Stripe Intent.
		 * @param int                                       $payment_id EDD Payment ID.
		 */
		do_action( 'edds_process_purchase_form', $purchase_data, $intent );

		return wp_send_json_success( array(
			'intent' => $intent,
		) );

	// Catch card-specific errors to handle rate limiting.
	} catch ( \Stripe\Error\Card $e ) {
		// Increase the card error count.
		edd_stripe()->rate_limiting->increment_card_error_count();

		// Record error in log.
		edd_record_gateway_error(
			esc_html__( 'Stripe Error', 'edds' ),
			sprintf(
				esc_html__( 'There was an error while processing a Stripe payment. Payment data: %s', ' edds' ), 
				wp_json_encode( $e->getJsonBody()['error'] )
			),
			0
		);

		return wp_send_json_error( array(
			'message' => $e->getMessage(),
		) );

	// Catch any remaining error.
	} catch( \Exception $e ) {

		// Saftey precaution in case the payment form is submitted directly.
		// Redirects back to the Checkout.
		if ( isset( $_POST['edd_email'] ) && ! isset( $_POST['payment_method_id'] ) ) {
			edd_set_error( $e->getCode(), $e->getMessage() );
			edd_send_back_to_checkout( '?payment-mode=' . $purchase_data['gateway'] );
		}

		return wp_send_json_error( array(
			'message' => $e->getMessage(),
		) );
	}
}
add_action( 'edd_gateway_stripe', 'edds_process_purchase_form' );

/**
 * Retrieves an Intent.
 *
 * @since 2.7.0
 */
function edds_get_intent() {
	$intent_id = isset( $_REQUEST['intent_id'] ) ? sanitize_text_field( $_REQUEST['intent_id'] ) : null;
	$intent_type = isset( $_REQUEST['intent_type'] ) ? sanitize_text_field( $_REQUEST['intent_type'] ) : 'payment_intent';

	try {
		if ( 'setup_intent' === $intent_type ) {
			$intent = edds_api_request( 'SetupIntent', 'retrieve', $intent_id );
		} else {
			$intent = edds_api_request( 'PaymentIntent', 'retrieve', $intent_id );
		}

		return wp_send_json_success( array(
			'intent' => $intent,
		) );
	} catch( Exception $e ) {
		return wp_send_json_error( array(
			'message' => esc_html( $e->getMessage() ),
		) );
	}
}
add_action( 'wp_ajax_edds_get_intent', 'edds_get_intent' );
add_action( 'wp_ajax_nopriv_edds_get_intent', 'edds_get_intent' );

/**
 * Confirms a PaymentIntent.
 *
 * @since 2.7.0
 */
function edds_confirm_intent() {
	// Map and merge serialized `form_data` to $_POST so it's accessible to other functions.
	_edds_map_form_data_to_request( $_POST );

	$intent_id   = isset( $_REQUEST['intent_id'] ) ? sanitize_text_field( $_REQUEST['intent_id'] ) : null;
	$intent_type = isset( $_REQUEST['intent_type'] ) ? sanitize_text_field( $_REQUEST['intent_type'] ) : 'payment_intent';

	try {
		// SetupIntent was used if the cart total is $0.
		if ( 'setup_intent' === $intent_type ) {
			$intent = edds_api_request( 'SetupIntent', 'retrieve', $intent_id );
		} else {
			$intent = edds_api_request( 'PaymentIntent', 'retrieve', $intent_id );
			$intent->confirm();
		}

		/**
		 * Allows further processing after an Intent is confirmed.
		 * Runs for all calls to confirm(), regardless of action needed.
		 *
		 * @since 2.7.0
		 *
		 * @param \Stripe\PaymentIntent|\Stripe\SetupIntent $intent Stripe intent.
		 */
		do_action( 'edds_confirm_payment_intent', $intent );

		return wp_send_json_success( array(
			'intent' => $intent,
		) );
	} catch( Exception $e ) {
		return wp_send_json_error( array(
			'message' => esc_html( $e->getMessage() ),
		) );
	}
}
add_action( 'wp_ajax_edds_confirm_intent', 'edds_confirm_intent' );
add_action( 'wp_ajax_nopriv_edds_confirm_intent', 'edds_confirm_intent' );

/**
 * Capture a PaymentIntent.
 *
 * @since 2.7.0
 */
function edds_capture_intent() {
	// Map and merge serialized `form_data` to $_POST so it's accessible to other functions.
	_edds_map_form_data_to_request( $_POST );

	$intent_id = isset( $_REQUEST['intent_id'] ) ? sanitize_text_field( $_REQUEST['intent_id'] ) : null;

	try {
		$intent = edds_api_request( 'PaymentIntent', 'retrieve', $intent_id );

		/**
		 * Allows processing before a PaymentIntent is captured.
		 *
		 * @since 2.7.0
		 *
		 * @param \Stripe\PaymentIntent $payment_intent Stripe PaymentIntent.
		 */
		do_action( 'edds_capture_payment_intent', $intent );

		// Capture capturable amount if nothing else has captured the intent.
		if ( 'requires_capture' === $intent->status ) {
			$intent->capture( array(
				'amount_to_capture' => $intent->amount_capturable,
			) );
		}

		return wp_send_json_success( array(
			'intent' => $intent,
		) );
	} catch( Exception $e ) {
		return wp_send_json_error( array(
			'message' => esc_html( $e->getMessage() ),
		) );
	}
}
add_action( 'wp_ajax_edds_capture_intent', 'edds_capture_intent' );
add_action( 'wp_ajax_nopriv_edds_capture_intent', 'edds_capture_intent' );

/**
 * Update a PaymentIntent.
 *
 * @since 2.7.0
 */
function edds_update_intent() {
	// Map and merge serialized `form_data` to $_POST so it's accessible to other functions.
	_edds_map_form_data_to_request( $_POST );

	$intent_id = isset( $_REQUEST['intent_id'] ) ? sanitize_text_field( $_REQUEST['intent_id'] ) : null;

	try {
		/**
		 * Allows processing before a PaymentIntent is updated.
		 *
		 * @since 2.7.0
		 *
		 * @param string $intent_id Stripe PaymentIntent ID.
		 */
		do_action( 'edds_update_payment_intent', $intent_id );

		$intent_args           = array();
		$intent_args_whitelist = array(
			'payment_method',
		);

		foreach ( $intent_args_whitelist as $intent_arg ) {
			if ( isset( $_POST[ $intent_arg ] ) ) {
				$intent_args[ $intent_arg ] = sanitize_text_field( $_POST[ $intent_arg ] );
			}
		}

		$intent = edds_api_request( 'PaymentIntent', 'update', $intent_id, $intent_args );

		return wp_send_json_success( array(
			'intent' => $intent,
		) );
	} catch( Exception $e ) {
		return wp_send_json_error( array(
			'message' => esc_html( $e->getMessage() ),
		) );
	}
}
add_action( 'wp_ajax_edds_update_intent', 'edds_update_intent' );
add_action( 'wp_ajax_nopriv_edds_update_intent', 'edds_update_intent' );

/**
 * Create an \EDD_Payment.
 *
 * @since 2.7.0
 */
function edds_create_payment() {
	// Map and merge serialized `form_data` to $_POST so it's accessible to other functions.
	_edds_map_form_data_to_request( $_POST );

	// Simulate being in an `edd_process_purchase_form()` request.
	_edds_fake_process_purchase_step();

	$intent = isset( $_REQUEST['intent'] ) ? $_REQUEST['intent'] : array();

	$purchase_data = edd_get_purchase_session();

	$payment_data = array(
		'price'        => $purchase_data['price'],
		'date'         => $purchase_data['date'],
		'user_email'   => $purchase_data['user_email'],
		'purchase_key' => $purchase_data['purchase_key'],
		'currency'     => edd_get_currency(),
		'downloads'    => $purchase_data['downloads'],
		'cart_details' => $purchase_data['cart_details'],
		'user_info'    => $purchase_data['user_info'],
		'status'       => 'pending',
		'gateway'      => 'stripe'
	);

	// Record the pending payment.
	$payment_id  = edd_insert_payment( $payment_data );

	try {
		if ( ! isset( $intent['id'] ) ) {
			throw new \Exception( esc_html__( 'Unable to create payment.', 'edds' ) );
		}

		// Retrieve created payment.
		$payment = edd_get_payment( $payment_id );

		// Retrieve the relevant Intent.
		if ( 'setup_intent' === $intent['object'] ) {
			$intent = edds_api_request( 'SetupIntent', 'update', $intent['id'], array(
				'metadata' => array(
					'edd_payment_id' => $payment_id,
				),
			) );

			$payment->add_note( 'Stripe SetupIntent ID: ' . $intent->id );
			$payment->update_meta( '_edds_stripe_setup_intent_id', $intent->id );
		} else {
			$intent = edds_api_request( 'PaymentIntent', 'update', $intent['id'], array(
				'metadata' => array(
					'edd_payment_id' => $payment_id,
				),
		  ) );

			$payment->add_note( 'Stripe PaymentIntent ID: ' . $intent->id );
			$payment->update_meta( '_edds_stripe_payment_intent_id', $intent->id );
		}

		// Use Intent ID for temporary transaction ID.
		// It will be updated when a charge is available.
		$payment->transaction_id = $intent->id;

		// Retrieves or creates a Stripe Customer.
		$payment->update_meta( '_edds_stripe_customer_id', $intent->customer );
		$payment->add_note( 'Stripe Customer ID: ' . $intent->customer );

		// Attach the \Stripe\Customer ID to the \EDD_Customer meta if one exists.
		$edd_customer = new EDD_Customer( $purchase_data['user_email'] );

		if ( $edd_customer->id > 0 ) {
			$edd_customer->update_meta( edd_stripe_get_customer_key(), $intent->customer );
		}

		if ( $payment->save() ) {
			/**
			 * Allows further processing after a payment is created.
			 *
			 * @since 2.7.0
			 *
			 * @param \EDD_Payment                              $payment EDD Payment.
			 * @param \Stripe\PaymentIntent|\Stripe\SetupIntent $intent Created Stripe Intent.
			 */
			do_action( 'edds_payment_created', $payment, $intent );

			return wp_send_json_success( array(
				'intent'  => $intent,
				'payment' => $payment,
			) );
		} else {
			throw new \Exception( esc_html__( 'Unable to create payment.', 'edds' ) );
		}
	} catch( \Exception $e ) {
		// Record error in log.
		edd_record_gateway_error(
			esc_html__( 'Stripe Error', 'edds' ),
			esc_html__( 'There was an error while completing payment made wiwth Stripe.', ' edds' ) . ' ' . $e->getMessage(),
			0
		);

		return wp_send_json_error( array(
			'message' => esc_html( $e->getMessage() ),
		) );
	}
}
add_action( 'wp_ajax_edds_create_payment', 'edds_create_payment' );
add_action( 'wp_ajax_nopriv_edds_create_payment', 'edds_create_payment' );

/**
 * Completes an \EDD_Payment (via AJAX)
 *
 * @since 2.7.0
 */
function edds_complete_payment() {
	// Map and merge serialized `form_data` to $_POST so it's accessible to other functions.
	_edds_map_form_data_to_request( $_POST );

	$intent = isset( $_REQUEST['intent'] ) ? $_REQUEST['intent'] : array();

	try {
		if ( ! isset( $intent['id'] ) ) {
			throw new \Exception( esc_html__( 'Unable to complete payment.', 'edds' ) );
		}

		$payment = edd_get_payment( $intent['metadata']['edd_payment_id'] );

		if ( ! $payment ) {
			throw new \Exception( esc_html__( 'Unable to complete payment.', 'edds' ) );
		}

		if ( 'setup_intent' !== $intent['object'] ) {
			$charge_id = sanitize_text_field( current( $intent['charges']['data'] )['id'] );

			$payment->add_note( 'Stripe Charge ID: ' . $charge_id );
			$payment->transaction_id = sanitize_text_field( $charge_id );
		}

		// Mark payment as Preapproved.
		if ( edd_get_option( 'stripe_preapprove_only' ) ) {
			$payment->status = 'preapproval';

		// Complete payment and transition the Transaction ID to the actual Charge ID.
		} else {
			$payment->status = 'publish';
		}

		if ( $payment->save() ) {
			/**
			 * Allows further processing after a payment is completed.
			 *
			 * Sends back just the Intent ID to avoid needing always retrieve
			 * the intent in this step, which has been transformed via JSON,
			 * and is no longer a \Stripe\PaymentIntent
			 *
			 * @since 2.7.0
			 *
			 * @param \EDD_Payment $payment   EDD Payment.
			 * @param string       $intent_id Stripe Intent ID.
			 */
			do_action( 'edds_payment_complete', $payment, $intent['id'] );

			// Empty cart.
			edd_empty_cart();

			return wp_send_json_success( array(
				'payment' => $payment,
				'intent'  => $intent,
			) );
		} else {
			throw new \Exception( esc_html__( 'Unable to complete payment.', 'edds' ) );
		}
	} catch( \Exception $e ) {
		// Record error in log.
		edd_record_gateway_error(
			esc_html__( 'Stripe Error', 'edds' ),
			esc_html__( 'There was an error while completing payment made wiwth Stripe.', ' edds' ) . ' ' . $e->getMessage(),
			0
		);

		return wp_send_json_error( array(
			'message' => esc_html( $e->getMessage() ),
		) );
	}
}
add_action( 'wp_ajax_edds_complete_payment', 'edds_complete_payment' );
add_action( 'wp_ajax_nopriv_edds_complete_payment', 'edds_complete_payment' );

/**
 * Completes a Payment authorization.
 *
 * @since 2.7.0
 */
function edds_complete_payment_authorization() {
	$intent_id = isset( $_REQUEST['intent_id'] ) ? sanitize_text_field( $_REQUEST['intent_id'] ) : null;

	try {
		$intent         = edds_api_request( 'PaymentIntent', 'retrieve', $intent_id );
		$edd_payment_id = $intent->metadata->edd_payment_id ? $intent->metadata->edd_payment_id : false;

		if ( ! $edd_payment_id ) {
			throw new \Exception( esc_html__( 'Unable to complete payment.', 'edds' ) );
		}

		$payment   = edd_get_payment( $edd_payment_id );
		$charge_id = current( $intent->charges->data )->id;

		$payment->add_note( 'Stripe Charge ID: ' . $charge_id );
		$payment->transaction_id = $charge_id;
		$payment->status = 'publish';
		
		if ( $payment->save() ) {

			/**
			 * Allows further processing after a payment authorization is completed.
			 *
			 * @since 2.7.0
			 *
			 * @param \Stripe\PaymentIntent $intent Created Stripe Intent.
			 * @param EDD_Payment           $payment EDD Payment.
			 */
			do_action( 'edds_payment_authorization_complete', $intent, $payment );

			return wp_send_json_success( array(
				'intent'  => $intent,
				'payment' => $payment,
			) );
		} else {
			throw new \Exception( esc_html__( 'Unable to complete payment.', 'edds' ) );
		}
	} catch( \Exception $e ) {
		return wp_send_json_error( array(
			'message' => esc_html( $e->getMessage() ),
		) );
	}
}
add_action( 'wp_ajax_edds_complete_payment_authorization', 'edds_complete_payment_authorization' );
add_action( 'wp_ajax_nopriv_edds_complete_payment_authorization', 'edds_complete_payment_authorization' );

/**
 * Sets up a \Stripe\Customer object based on the current purchase data.
 *
 * @param array $purchase_data {
 *
 * }
 * @return \Stripe\Customer
 */
function edds_checkout_setup_customer( $purchase_data ) {
	$customer = null;

	if ( is_user_logged_in() ) {
		$customer_id = edds_get_stripe_customer_id( get_current_user_id() );
	}

	if ( empty( $customer_id ) ) {
		// No customer ID found, let's look one up based on the email
		$customer_id = edds_get_stripe_customer_id( $purchase_data['user_email'], false );
	}

	try {
		if ( ! empty( $customer_id ) ) {
			$customer = edds_api_request( 'Customer', 'retrieve', $customer_id );

			// If the Customer was deleted in Stripe, unset $customer_id so we can create one below.
			if ( isset( $customer->deleted ) && $customer->deleted ) {
				$customer_id = '';
			}
		}
	} catch( \Exception $e ) {
		$customer_id = '';
	}

	if ( empty( $customer_id ) ) {
		$customer_args = array(
			'description' => $purchase_data['user_email'],
			'email'       => $purchase_data['user_email'],
		);

		/**
		 * Filters the arguments used to create a Customer in Stripe.
		 *
		 * @since unknown
		 *
		 * @param array $customer_args Arguments used to create the Customer, based on purchase form data.
		 * @param array $purchase_data {
		 *
		 * }
		 */
		$customer_args = apply_filters( 'edds_create_customer_args', $customer_args, $purchase_data );

		$customer = edds_api_request( 'Customer', 'create', $customer_args );
	}

	return $customer;
}

/**
 * Generates a description based on the cart details.
 *
 * @param array $cart_details {
 *
 * }
 * @return string
 */
function edds_get_payment_description( $cart_details ) {
	$purchase_summary = '';

	if( is_array( $cart_details ) && ! empty( $cart_details ) ) {
		foreach( $cart_details as $item ) {
			$purchase_summary .= $item['name'];
			$price_id          = isset( $item['item_number']['options']['price_id'] ) 
				? absint( $item['item_number']['options']['price_id'] ) 
				: false;

			if ( false !== $price_id ) {
				$purchase_summary .= ' - ' . edd_get_price_option_name( $item['id'], $item['item_number']['options']['price_id'] );
			}

			$purchase_summary .= ', ';
		}

		$purchase_summary = rtrim( $purchase_summary, ', ' );
	} else {
		$purchase_summary = edd_get_purchase_summary( $purchase_data, false );
	}

	return html_entity_decode( $purchase_summary, ENT_COMPAT, 'UTF-8' );
}

/**
 * Charge a preapproved payment
 *
 * @since 1.6
 * @return bool
 */
function edds_charge_preapproved( $payment_id = 0 ) {
	$retval = false;

	if ( empty( $payment_id ) ) {
		return $retval;
	}

	$payment     = edd_get_payment( $payment_id );
	$customer_id = $payment->get_meta( '_edds_stripe_customer_id' );

	if ( empty( $customer_id ) ) {
		return $retval;
	}

	if ( ! in_array( $payment->status, array( 'preapproval', 'preapproval_pending' ), true ) ) {
		return $retval;
	}

	$setup_intent_id = $payment->get_meta( '_edds_stripe_setup_intent_id' );

	try {
		if ( edds_is_zero_decimal_currency() ) {
			$amount = edd_get_payment_amount( $payment->ID );
		} else {
			$amount = edd_get_payment_amount( $payment->ID ) * 100;
		}

		$cart_details         = edd_get_payment_meta_cart_details( $payment->ID );
		$purchase_summary     = edds_get_payment_description( $cart_details );
		$statement_descriptor = edds_get_statement_descriptor();

		if ( empty( $statement_descriptor ) ) {
			$statement_descriptor = substr( $purchase_summary, 0, 22 );
		}

		$statement_descriptor = apply_filters( 'edds_preapproved_statement_descriptor', $statement_descriptor, $payment->ID );
		$statement_descriptor = edds_sanitize_statement_descriptor( $statement_descriptor );

		if ( empty( $statement_descriptor ) ) {
			$statement_descriptor = null;
		}

		// Create a PaymentIntent using SetupIntent data.
		if ( ! empty( $setup_intent_id ) ) {
			$setup_intent = edds_api_request( 'SetupIntent', 'retrieve', $setup_intent_id );
			$intent_args  = array(
				'amount'               => $amount,
				'currency'             => edd_get_currency(),
				'payment_method'       => $setup_intent->payment_method,
				'customer'             => $setup_intent->customer,
				'off_session'          => true,
				'confirm'              => true,
				'description'          => $purchase_summary,
				'metadata'             => $setup_intent->metadata->__toArray(),
				'statement_descriptor' => $statement_descriptor,
			);
		// Process a legacy preapproval. Uses the Customer's default source.
		} else {
			$customer    = \Stripe\Customer::retrieve( $customer_id );
			$intent_args = array(
				'amount'               => $amount,
				'currency'             => edd_get_currency(),
				'payment_method'       => $customer->default_source,
				'customer'             => $customer->id,
				'off_session'          => true,
				'confirm'              => true,
				'description'          => $purchase_summary,
				'metadata'             => array(
					'email'          => edd_get_payment_user_email( $payment->ID ),
					'edd_payment_id' => $payment->ID,
				),
				'statement_descriptor' => $statement_descriptor,
			);
		}

		/** This filter is documented in includes/payment-actions.php */
		$intent_args = apply_filters( 'edds_create_payment_intent_args', $intent_args, array() );

		$payment_intent = edds_api_request( 'PaymentIntent', 'create', $intent_args );

		if ( 'succeeded' === $payment_intent->status ) {
			$charge_id = current( $payment_intent->charges->data )->id;

			$payment->status = 'publish';
			$payment->add_note( 'Stripe Charge ID: ' . $charge_id );
			$payment->add_note( 'Stripe PaymentIntent ID: ' . $payment_intent->id );
			$payment->add_meta( '_edds_stripe_payment_intent_id', $payment_intent->id );
			$payment->transaction_id = $charge_id;

			$retval = $payment->save();
		}
	} catch( \Stripe\Error\Base $e ) {
		$error = $e->getJsonBody()['error'];

		$payment->status = 'preapproval_pending';
		$payment->add_note( esc_html( $e->getMessage() ) );
		$payment->add_note( 'Stripe PaymentIntent ID: ' . $error['payment_intent']['id'] );
		$payment->add_meta( '_edds_stripe_payment_intent_id', $error['payment_intent']['id'] );
		$payment->save();

		/**
		 * Allows further processing when a Preapproved payment needs further action.
		 *
		 * @since 2.7.0
		 *
		 * @param int $payment_id ID of the payment.
		 */
		do_action( 'edds_preapproved_payment_needs_action', $payment_id );
	} catch( \Exception $e ) {
		$payment->add_note( esc_html( $e->getMessage() ) );
	}

	return $retval;
}

/**
 * Process refund in Stripe
 *
 * @access      public
 * @since       1.8
 * @return      void
 */
function edd_stripe_process_refund( $payment_id, $new_status, $old_status ) {
	if ( empty( $_POST['edd_refund_in_stripe'] ) ) {
		return;
	}

	$should_process_refund = 'publish' != $old_status && 'revoked' != $old_status ? false : true;
	$should_process_refund = apply_filters( 'edds_should_process_refund', $should_process_refund, $payment_id, $new_status, $old_status );

	if ( false === $should_process_refund ) {
		return;
	}

	if ( 'refunded' != $new_status ) {
		return;
	}

	$charge_id = edd_get_payment_transaction_id( $payment_id );

	if ( empty( $charge_id ) || $charge_id == $payment_id ) {
		$notes = edd_get_payment_notes( $payment_id );

		foreach ( $notes as $note ) {
			if ( preg_match( '/^Stripe Charge ID: ([^\s]+)/', $note->comment_content, $match ) ) {
				$charge_id = $match[1];
				break;
			}
		}
	}

	// Bail if no charge ID was found.
	if ( empty( $charge_id ) ) {
		return;
	}

	try {
		$args = apply_filters( 'edds_create_refund_args', array(
			'charge' => $charge_id,
		) );

		$sec_args = apply_filters( 'edds_create_refund_secondary_args', array() );

		$refund = edds_api_request( 'Refund', 'create', $args, $sec_args );

		edd_insert_payment_note( $payment_id, sprintf( __( 'Charge refunded in Stripe. Refund ID %s', 'edds' ), $refund->id ) );

	} catch ( Exception $e ) {
		wp_die( $e->getMessage(), __( 'Error', 'edds' ) , array( 'response' => 400 ) );
	}

	do_action( 'edds_payment_refunded', $payment_id );
}
add_action( 'edd_update_payment_status', 'edd_stripe_process_refund', 200, 3 );
