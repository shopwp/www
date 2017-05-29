<?php

/**
 * Process stripe checkout submission
 *
 * @access      public
 * @since       1.0
 * @return      void
 */
function edds_process_stripe_payment( $purchase_data ) {

	global $edd_options, $edd_stripe_is_buy_now;

	if ( edd_is_test_mode() ) {
		$secret_key = trim( $edd_options['test_secret_key'] );
	} else {
		$secret_key = trim( $edd_options['live_secret_key'] );
	}

	$purchase_summary = '';
	if( is_array( $purchase_data['cart_details'] ) && ! empty( $purchase_data['cart_details'] ) ) {

		foreach( $purchase_data['cart_details'] as $item ) {
			$purchase_summary .= $item['name'];
			$price_id = isset( $item['item_number']['options']['price_id'] ) ? absint( $item['item_number']['options']['price_id'] ) : false;
			if ( false !== $price_id ) {
				$purchase_summary .= ' - ' . edd_get_price_option_name( $item['id'], $item['item_number']['options']['price_id'] );
			}
			$purchase_summary .= ', ';
		}

		$purchase_summary = rtrim( $purchase_summary, ', ' );

	} else {

		$purchase_summary = edd_get_purchase_summary( $purchase_data, false );

	}

	// make sure we don't have any left over errors present
	edd_clear_errors();

	if ( ! isset( $_POST['edd_stripe_token'] ) ) {

		// no Stripe token
		edd_set_error( 'no_token', __( 'Missing Stripe token. Please contact support.', 'edds' ) );
		edd_record_gateway_error( __( 'Missing Stripe Token', 'edds' ), __( 'A Stripe token failed to be generated. Please check Stripe logs for more information', ' edds' ) );

	} else {
		$card_data = $_POST['edd_stripe_token'];
	}

	$errors = edd_get_errors();

	if ( ! $errors ) {

		try {

			\Stripe\Stripe::setApiKey( $secret_key );

			if ( method_exists( '\Stripe\Stripe', 'setAppInfo' ) ) {
				\Stripe\Stripe::setAppInfo( 'Easy Digital Downloads - Stripe', EDD_STRIPE_VERSION, esc_url( site_url() ) );
			}

			// setup the payment details
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

			$customer_exists = false;

			if ( is_user_logged_in() ) {

				// First try and retrieve the customer ID from the logged in user
				$user = get_user_by( 'ID', get_current_user_id() );

				if ( $user ) {

					$customer_id = edds_get_stripe_customer_id( $user->ID );

				}

			}

			if( empty( $customer_id ) ) {

				// No customer ID found, let's look one up based on the email
				$customer_id = edds_get_stripe_customer_id( $purchase_data['user_email'] );

			}

			if ( ! empty( $customer_id ) ) {

				$customer_exists = true;

				try {

					// Retrieve the customer to ensure the customer has not been deleted
					$cu = \Stripe\Customer::retrieve( $customer_id );

					if( isset( $cu->deleted ) && $cu->deleted ) {

						// This customer was deleted
						$customer_exists = false;

					}

				// No customer found
				} catch ( Exception $e ) {

					$customer_exists = false;

				}

			}

			if ( ! $customer_exists ) {

				// Create a customer first so we can retrieve them later for future payments
				$cu = \Stripe\Customer::create( array(
						'description' => $purchase_data['user_email'],
						'email'       => $purchase_data['user_email'],
					)
				);

				$customer_id = is_array( $cu ) ? $cu['id'] : $cu->id;

				$customer_exists = true;

			}


			if ( $customer_exists ) {

				if ( is_array( $card_data ) ) {
					$card_data['object'] = 'card';
				}

				$card    = $cu->sources->create( array( 'source' => $card_data ) );
				$card_id = $card->id;

				// Process a normal one-time charge purchase

				if( ! isset( $edd_options['stripe_preapprove_only'] ) ) {

					if( edds_is_zero_decimal_currency() ) {
						$amount = $purchase_data['price'];
					} else {
						$amount = $purchase_data['price'] * 100;
					}

					$unsupported_characters = array( '<', '>', '"', '\'' );

					$statement_descriptor = apply_filters( 'edds_statement_descriptor', substr( $purchase_summary, 0, 22 ), $purchase_data );

					$statement_descriptor = str_replace( $unsupported_characters, '', $statement_descriptor );

					$args = array(
						'amount'      => $amount,
						'currency'    => edd_get_currency(),
						'customer'    => $customer_id,
						'source'      => $card_id,
						'description' => html_entity_decode( $purchase_summary, ENT_COMPAT, 'UTF-8' ),
						'metadata'    => array(
							'email'   => $purchase_data['user_info']['email']
						),
					);

					if( ! empty( $statement_descriptor ) ) {
						$args[ 'statement_descriptor' ] = $statement_descriptor;
					}

					$charge = \Stripe\Charge::create( apply_filters( 'edds_create_charge_args', $args, $purchase_data ) );
				}

				// record the pending payment
				$payment = edd_insert_payment( $payment_data );

				if ( is_user_logged_in() ) {
					update_user_meta( get_current_user_id(), edd_stripe_get_customer_key(), $customer_id );
				}

			} else {

				edd_record_gateway_error( __( 'Customer Creation Failed', 'edds' ), sprintf( __( 'Customer creation failed while processing a payment. Payment Data: %s', ' edds' ), json_encode( $payment_data ) ) );

			}

			if ( $payment && ( ! empty( $customer_id ) || ! empty( $charge ) ) ) {

				if ( ! empty( $needs_invoiced ) ) {

					try {
						// Create the invoice containing taxes / discounts / fees
						$invoice = \Stripe\Invoice::create( array(
							'customer' => $customer_id, // the customer to apply the fee to
						) );
						$invoice = $invoice->pay();
					} catch ( Exception $e ) {
						// If there is nothing to pay, it just means the invoice item was taken care of with the subscription payment
					}
				}

				if ( isset( $edd_options['stripe_preapprove_only'] ) ) {
					edd_update_payment_status( $payment, 'preapproval' );
					add_post_meta( $payment, '_edds_stripe_customer_id', $customer_id );
				} else {
					edd_update_payment_status( $payment, 'publish' );
				}

				// You should be using Stripe's API here to retrieve the invoice then confirming it's been paid
				if ( ! empty( $charge ) ) {

					edd_insert_payment_note( $payment, 'Stripe Charge ID: ' . $charge->id );

					if( function_exists( 'edd_set_payment_transaction_id' ) ) {

						edd_set_payment_transaction_id( $payment, $charge->id );

					}

				} elseif ( ! empty( $customer_id ) ) {
					edd_insert_payment_note( $payment, 'Stripe Customer ID: ' . $customer_id );
				}

				edd_empty_cart();
				edd_send_to_success_page();

			} else {

				edd_set_error( 'payment_not_recorded', __( 'Your payment could not be recorded, please contact the site administrator.', 'edds' ) );

				// if errors are present, send the user back to the purchase page so they can be corrected
				edd_send_back_to_checkout( '?payment-mode=stripe' );

			}

		 } catch ( \Stripe\Error\Card $e ) {

			$body = $e->getJsonBody();
			$err  = $body['error'];

			if( isset( $err['message'] ) ) {
				edd_set_error( 'payment_error', $err['message'] );
			} else {
				edd_set_error( 'payment_error', __( 'There was an error processing your payment, please ensure you have entered your card number correctly.', 'edds' ) );
			}

			edd_record_gateway_error( __( 'Stripe Error', 'edds' ), sprintf( __( 'There was an error while processing a Stripe payment. Payment data: %s', ' edds' ), json_encode( $err ) ), 0 );

			if( $edd_stripe_is_buy_now ) {
				wp_die( $err['message'], __( 'Card Processing Error', 'edds' ) );
			} else {
				edd_send_back_to_checkout( '?payment-mode=stripe' );
			}

		} catch ( \Stripe\Error\ApiConnection $e ) {

			$body = $e->getJsonBody();
			$err  = $body['error'];

			edd_set_error( 'payment_error', __( 'There was an error processing your payment (Stripe\'s API is down), please try again', 'edds' ) );
			edd_record_gateway_error( __( 'Stripe Error', 'edds' ), sprintf( __( 'There was an error processing your payment (Stripe\'s API was down). Error: %s', 'edds' ), json_encode( $err['message'] ) ), 0 );

			if( $edd_stripe_is_buy_now ) {
				wp_die( $err['message'], __( 'Card Processing Error', 'edds' ) );
			} else {
				edd_send_back_to_checkout( '?payment-mode=stripe' );
			}

		} catch ( \Stripe\Error\InvalidRequest $e ) {

			$body = $e->getJsonBody();
			$err  = $body['error'];

			// Bad Request of some sort. Maybe Christoff was here ;)
			if( isset( $err['message'] ) ) {
				edd_set_error( 'request_error', $err['message'] );
			} else {
				edd_set_error( 'request_error', __( 'The Stripe API request was invalid, please try again', 'edds' ) );
			}

			if( $edd_stripe_is_buy_now ) {
				wp_die( $err['message'], __( 'Card Processing Error', 'edds' ) );
			} else {
				edd_send_back_to_checkout( '?payment-mode=stripe' );
			}

		} catch ( \Stripe\Error\Api $e ) {

			$body = $e->getJsonBody();
			$err  = $body['error'];

			if( isset( $err['message'] ) ) {
				edd_set_error( 'request_error', $err['message'] );
			} else {
				edd_set_error( 'request_error', __( 'The Stripe API request was invalid, please try again', 'edds' ) );
			}
			edd_set_error( 'request_error', sprintf( __( 'The Stripe API request was invalid, please try again. Error: %s', 'edds' ), json_encode( $err['message'] ) ) );

			if( $edd_stripe_is_buy_now ) {
				wp_die( $err['message'], __( 'Card Processing Error', 'edds' ) );
			} else {
				edd_send_back_to_checkout( '?payment-mode=stripe' );
			}

		} catch ( \Stripe\Error\Authentication $e ) {

			$body = $e->getJsonBody();
			$err  = $body['error'];

			// Authentication error. Stripe keys in settings are bad.
			if( isset( $err['message'] ) ) {
				edd_set_error( 'request_error', $err['message'] );
			} else {
				edd_set_error( 'api_error', __( 'The API keys entered in settings are incorrect', 'edds' ) );
			}

			if( $edd_stripe_is_buy_now ) {
				wp_die( $err['message'], __( 'Card Processing Error', 'edds' ) );
			} else {
				edd_send_back_to_checkout( '?payment-mode=stripe' );
			}

		} catch ( Exception $e ) {
			// some sort of other error
			$body = $e->getJsonBody();
			$err  = $body['error'];
			if( isset( $err['message'] ) ) {
				edd_set_error( 'request_error', $err['message'] );
			} else {
				edd_set_error( 'api_error', __( 'Something went wrong.', 'edds' ) );
			}

			if( $edd_stripe_is_buy_now ) {
				wp_die( $err['message'], __( 'Card Processing Error', 'edds' ) );
			} else {
				edd_send_back_to_checkout( '?payment-mode=stripe' );
			}

		}
	} else {
		edd_send_back_to_checkout( '?payment-mode=stripe' );
	}
}
add_action( 'edd_gateway_stripe', 'edds_process_stripe_payment' );

/**
 * Register payment statuses for preapproval
 *
 * @since 1.6
 * @return void
 */
function edds_register_post_statuses() {
	register_post_status( 'preapproval', array(
		'label'                     => _x( 'Preapproved', 'Preapproved payment', 'edds' ),
		'public'                    => true,
		'exclude_from_search'       => false,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		'label_count'               => _n_noop( 'Active <span class="count">(%s)</span>', 'Active <span class="count">(%s)</span>', 'edds' )
	) );
	register_post_status( 'cancelled', array(
		'label'                     => _x( 'Cancelled', 'Cancelled payment', 'edds' ),
		'public'                    => true,
		'exclude_from_search'       => false,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		'label_count'               => _n_noop( 'Active <span class="count">(%s)</span>', 'Active <span class="count">(%s)</span>', 'edds' )
	) );
}
add_action( 'init',  'edds_register_post_statuses', 110 );


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
 * Display the Preapprove column label
 *
 * @since 1.6
 * @return array
 */
function edds_payments_column( $columns ) {

	global $edd_options;

	if ( isset( $edd_options['stripe_preapprove_only'] ) ) {
		$columns['preapproval'] = __( 'Preapproval', 'edds' );
	}
	return $columns;
}
add_filter( 'edd_payments_table_columns', 'edds_payments_column' );


/**
 * Display the payment status filters
 *
 * @since 1.6
 * @return array
 */
function edds_payment_status_filters( $views ) {
	$payment_count        = wp_count_posts( 'edd_payment' );
	$preapproval_count    = '&nbsp;<span class="count">(' . $payment_count->preapproval . ')</span>';
	$cancelled_count      = '&nbsp;<span class="count">(' . $payment_count->cancelled . ')</span>';
	$current              = isset( $_GET['status'] ) ? $_GET['status'] : '';
	$views['preapproval'] = sprintf( '<a href="%s"%s>%s</a>', esc_url( add_query_arg( 'status', 'preapproval', admin_url( 'edit.php?post_type=download&page=edd-payment-history' ) ) ), $current === 'preapproval' ? ' class="current"' : '', __( 'Preapproval Pending', 'edds' ) . $preapproval_count );
	$views['cancelled']   = sprintf( '<a href="%s"%s>%s</a>', esc_url( add_query_arg( 'status', 'cancelled', admin_url( 'edit.php?post_type=download&page=edd-payment-history' ) ) ), $current === 'cancelled' ? ' class="current"' : '', __( 'Cancelled', 'edds' ) . $cancelled_count );

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
	if ( $column_name == 'preapproval' ) {
		$status      = get_post_status( $payment_id );
		$customer_id = get_post_meta( $payment_id, '_edds_stripe_customer_id', true );

		if( ! $customer_id )
			return $value;

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

		if ( 'preapproval' === $status ) {
			$value = '<a href="' . esc_url( add_query_arg( $preapproval_args, admin_url( 'edit.php?post_type=download&page=edd-payment-history' ) ) ) . '" class="button-secondary button">' . __( 'Process Payment', 'edds' ) . '</a>&nbsp;';
			$value .= '<a href="' . esc_url( add_query_arg( $cancel_args, admin_url( 'edit.php?post_type=download&page=edd-payment-history' ) ) ) . '" class="button-secondary button">' . __( 'Cancel Preapproval', 'edds' ) . '</a>';
		}
	}
	return $value;
}
add_filter( 'edd_payments_table_column', 'edds_payments_column_data', 10, 3 );


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
 * Charge a preapproved payment
 *
 * @since 1.6
 * @return bool
 */
function edds_charge_preapproved( $payment_id = 0 ) {

	global $edd_options;

	if( empty( $payment_id ) )
		return false;

	$customer_id = get_post_meta( $payment_id, '_edds_stripe_customer_id', true );

	if( empty( $customer_id ) || empty( $payment_id ) ) {
		return;
	}

	if ( 'preapproval' !== get_post_status( $payment_id ) ) {
		return;
	}

	$secret_key = edd_is_test_mode() ? trim( $edd_options['test_secret_key'] ) : trim( $edd_options['live_secret_key'] );

	\Stripe\Stripe::setApiKey( $secret_key );

	if ( method_exists( '\Stripe\Stripe', 'setAppInfo' ) ) {
		\Stripe\Stripe::setAppInfo( 'Easy Digital Downloads - Stripe', EDD_STRIPE_VERSION, esc_url( site_url() ) );
	}

	if( edds_is_zero_decimal_currency() ) {
		$amount = edd_get_payment_amount( $payment_id );
	} else {
		$amount = edd_get_payment_amount( $payment_id ) * 100;
	}

	$purchase_summary = '';
	$cart_details     = edd_get_payment_meta_cart_details( $payment_id );
	if( is_array( $cart_details ) ) {

		foreach( $cart_details as $item ) {
			$purchase_summary .= $item['name'];
			$price_id = isset( $item['item_number']['options']['price_id'] ) ? absint( $item['item_number']['options']['price_id'] ) : false;
			if ( false !== $price_id ) {
				$purchase_summary .= ' - ' . edd_get_price_option_name( $item['id'], $item['item_number']['options']['price_id'] );
			}
			$purchase_summary .= ', ';
		}

		$purchase_summary = rtrim( $purchase_summary, ', ' );

	}

	try {

		$unsupported_characters = array( '<', '>', '"', '\'' );
		$statement_descriptor   = apply_filters( 'edds_preapproved_statement_descriptor', substr( $purchase_summary, 0, 22 ), $payment_id );
		$statement_descriptor   = str_replace( $unsupported_characters, '', $statement_descriptor );

		$charge = \Stripe\Charge::create( array(
				"amount"                => $amount,
				"currency"              => edd_get_currency(),
				"customer"              => $customer_id,
				"description"           => sprintf( __( 'Preapproved charge for purchase %s from %s', 'edds' ), edd_get_payment_key( $payment_id ), home_url() ),
				'statement_descriptor'  => $statement_descriptor,
				'metadata'              => array(
					'email'             => edd_get_payment_user_email( $payment_id )
				)
			)
		);

	} catch ( \Stripe\Error\Card $e ) {

		$body = $e->getJsonBody();
		$err  = $body['error'];

		$error_message = isset( $err['message'] ) ? $err['message'] : __( 'There was an error processing this charge', 'edds' );

	} catch ( \Stripe\Error\ApiConnection $e ) {

		$body = $e->getJsonBody();
		$err  = $body['error'];

		$error_message = isset( $err['message'] ) ? $err['message'] : __( 'There was an error processing this charge', 'edds' );

	} catch ( \Stripe\Error\InvalidRequest $e ) {

		$body = $e->getJsonBody();
		$err  = $body['error'];

		$error_message = isset( $err['message'] ) ? $err['message'] : __( 'There was an error processing this charge', 'edds' );

	} catch ( \Stripe\Error\Api $e ) {

		$body = $e->getJsonBody();
		$err  = $body['error'];

		$error_message = isset( $err['message'] ) ? $err['message'] : __( 'There was an error processing this charge', 'edds' );
	} catch ( \Stripe\Error\Authentication $e ) {

		$body = $e->getJsonBody();
		$err  = $body['error'];

		$error_message = isset( $err['message'] ) ? $err['message'] : __( 'There was an error processing this charge', 'edds' );

	} catch ( Exception $e ) {

		// some sort of other error
		$body = $e->getJsonBody();
		$err  = $body['error'];

		$error_message = isset( $err['message'] ) ? $err['message'] : __( 'There was an error processing this charge', 'edds' );

	}

	if ( ! empty( $charge ) ) {

		edd_insert_payment_note( $payment_id, 'Stripe Charge ID: ' . $charge->id );
		edd_update_payment_status( $payment_id, 'publish' );
		delete_post_meta( $payment_id, '_edds_stripe_customer_id' );
		return true;

	} else {

		edd_insert_payment_note( $payment_id, $error_message );

		return false;
	}
}


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
 * Listen for Stripe events, primarily recurring payments
 *
 * @access      public
 * @since       1.5
 * @return      void
 */

function edds_stripe_event_listener() {

	if ( isset( $_GET['edd-listener'] ) && $_GET['edd-listener'] == 'stripe' ) {

		global $edd_options;

		$secret_key = edd_is_test_mode() ? trim( $edd_options['test_secret_key'] ) : trim( $edd_options['live_secret_key'] );

		\Stripe\Stripe::setApiKey( $secret_key );

		if ( method_exists( '\Stripe\Stripe', 'setAppInfo' ) ) {
			\Stripe\Stripe::setAppInfo( 'Easy Digital Downloads - Stripe', EDD_STRIPE_VERSION, esc_url( site_url() ) );
		}

		// retrieve the request's body and parse it as JSON
		$body = @file_get_contents( 'php://input' );
		$event = json_decode( $body );
		if( isset( $event->id ) ) {

			status_header( 200 );

			try {

				$event = \Stripe\Event::retrieve( $event->id );

			} catch ( Exception $e ) {

				return; // No event found for this account

			}


			$invoice = $event->data->object;
			switch ( $event->type ) :

				case 'charge.succeeded' :

					$charge     = $event->data->object;
					$payment_id = edd_get_purchase_id_by_transaction_id( $charge->id );
					$payment    = new EDD_Payment( $payment_id );

					if( $payment && $payment->ID > 0 ) {

						$payment->address = array(
							'line1'   => $charge->source->address_line1,
							'line2'   => $charge->source->address_line2,
							'state'   => $charge->source->address_state,
							'city'    => $charge->source->address_city,
							'zip'     => $charge->source->address_zip,
							'country' => $charge->source->address_country,
						);

						$payment->save();

					}

					break;

				case 'charge.refunded' :

					global $wpdb;

					$charge = $event->data->object;

					if( $charge->refunded ) {

						$payment_id = edd_get_purchase_id_by_transaction_id( $charge->id );

						if( $payment_id ) {

							edd_update_payment_status( $payment_id, 'refunded' );
							edd_insert_payment_note( $payment_id, __( 'Charge refunded in Stripe.', ' edds' ) );

						}

					}

					break;

			endswitch;

			do_action( 'edds_stripe_event_' . $event->type, $event );

			die( '1' ); // Completed successfully

		} else {
			status_header( 500 );
			die( '-1' ); // Failed
		}
		die( '-2' ); // Failed
	}
}
add_action( 'init', 'edds_stripe_event_listener' );

/**
* Register our settings section
*
* @return array
*/
function edds_settings_section( $sections ) {
	$sections['edd-stripe'] = __( 'Stripe', 'edds' );

	return $sections;
}
add_filter( 'edd_settings_sections_gateways', 'edds_settings_section' );

/**
 * Register the gateway settings
 *
 * @access      public
 * @since       1.0
 * @return      array
 */

function edds_add_settings( $settings ) {

	$stripe_settings = array(
		array(
			'id'   => 'stripe_settings',
			'name'  => '<strong>' . __( 'Stripe Settings', 'edds' ) . '</strong>',
			'desc'  => __( 'Configure the Stripe settings', 'edds' ),
			'type'  => 'header'
		),
		array(
			'id'   => 'test_secret_key',
			'name'  => __( 'Test Secret Key', 'edds' ),
			'desc'  => __( 'Enter your test secret key, found in your Stripe Account Settings', 'edds' ),
			'type'  => 'text',
			'size'  => 'regular'
		),
		array(
			'id'   => 'test_publishable_key',
			'name'  => __( 'Test Publishable Key', 'edds' ),
			'desc'  => __( 'Enter your test publishable key, found in your Stripe Account Settings', 'edds' ),
			'type'  => 'text',
			'size'  => 'regular'
		),
		array(
			'id'   => 'live_secret_key',
			'name'  => __( 'Live Secret Key', 'edds' ),
			'desc'  => __( 'Enter your live secret key, found in your Stripe Account Settings', 'edds' ),
			'type'  => 'text',
			'size'  => 'regular'
		),
		array(
			'id'   => 'live_publishable_key',
			'name'  => __( 'Live Publishable Key', 'edds' ),
			'desc'  => __( 'Enter your live publishable key, found in your Stripe Account Settings', 'edds' ),
			'type'  => 'text',
			'size'  => 'regular'
		),
		array(
			'id'    => 'stripe_webhook_description',
			'type'  => 'descriptive_text',
			'name'  => __( 'Webhooks', 'edds' ),
			'desc'  =>
				'<p>' . sprintf( __( 'In order for Stripe to function completely, you must configure your Stripe webhooks. Visit your <a href="%s" target="_blank">account dashboard</a> to configure them. Please add a webhook endpoint for the URL below.', 'edds' ), 'https://dashboard.stripe.com/account/webhooks' ) . '</p>' .
				'<p><strong>' . sprintf( __( 'Webhook URL: %s', 'edds' ), home_url( 'index.php?edd-listener=stripe' ) ) . '</strong></p>' .
				'<p>' . sprintf( __( 'See our <a href="%s">documentation</a> for more information.', 'edds' ), 'http://docs.easydigitaldownloads.com/article/405-setup-documentation-for-stripe-payment-gateway' ) . '</p>'
		),
		array(
			'id'    => 'stripe_billing_fields',
			'name'  => __( 'Billing Address Display', 'edds' ),
			'desc'  => __( 'Select how you would like to display the billing address fields on the checkout form. <p><strong>Notes</strong>:</p><p>If taxes are enabled, this option cannot be changed from "Full address".</p><p>This setting does <em>not</em> apply to Stripe Checkout options below.</p><p>If set to "No address fields", you <strong>must</strong> disable "zip code verification" in your Stripe account.</p>', 'edds' ),
			'type'  => 'select',
			'options' => array(
				'full'        => __( 'Full address', 'edds' ),
				'zip_country' => __( 'Zip / Postal Code and Country only', 'edds' ),
				'none'        => __( 'No address fields', 'edds' )
			),
			'std'   => 'full'
		),
		array(
			'id'   => 'stripe_preapprove_only',
			'name'  => __( 'Preapprove Only?', 'edds' ),
			'desc'  => __( 'Check this if you would like to preapprove payments but not charge until a later date.', 'edds' ),
			'type'  => 'checkbox'
		),
		array(
			'id'    => 'stripe_checkout_settings',
			'name'  => __( 'Stripe Checkout Options', 'edds' ),
			'type'  => 'header'
		),
		array(
			'id'    => 'stripe_checkout',
			'name'  => __( 'Enable Stripe Checkout', 'edds' ),
			'desc'  => __( 'Check this if you would like to enable the <a href="https://stripe.com/checkout">Stripe Checkout</a> modal window on the main checkout screen.', 'edds' ),
			'type'  => 'checkbox'
		),
		array(
			'id'    => 'stripe_alipay',
			'name'  => __( 'Accept Alipay in Stripe Checkout', 'edds' ),
			'desc'  => __( 'Check this box if you would like to permit your customers to pay with an Alipay account.', 'edds' ),
			'type'  => 'checkbox'
		),/*
		array(
			'id'   => 'stripe_bitcoin',
			'name'  => __( 'Accept Bitcoin in Stripe Checkout', 'edds' ),
			'desc'  => __( 'Check this box if you would like to permit your customers to pay with bitcoin. Supported by Buy Now buttons only.', 'edds' ),
			'type'  => 'checkbox'
		),*/
		array(
			'id'    => 'stripe_checkout_button_text',
			'name'  => __( 'Complete Purchase Text', 'edds' ),
			'desc'  => __( 'Enter the text shown on the checkout\'s submit button. This is the button that opens the Stripe Checkout modal window.', 'edds' ),
			'type'  => 'text',
			'std'   => __( 'Next', 'edds' )
		),
		array(
			'id'    => 'stripe_checkout_image',
			'name'  => __( 'Checkout Logo', 'edds' ),
			'desc'  => __( 'Upload an image to be shown on the Stripe Checkout modal window. Recommended minimum size is 128x128px. Leave blank to disable the image.', 'edds' ),
			'type'  => 'upload'
		),
		array(
			'id'    => 'stripe_checkout_billing',
			'name'  => __( 'Enable Billing Address', 'edds' ),
			'desc'  => __( 'Check this box to instruct Stripe to collect a billing address in the Checkout modal window.', 'edds' ),
			'type'  => 'checkbox',
			'std'   => 0,
		),
		array(
			'id'    => 'stripe_checkout_zip_code',
			'name'  => __( 'Enable Zip / Postal Code', 'edds' ),
			'desc'  => __( 'Check this box to instruct Stripe to collect a zip / postal code in the Checkout modal window.', 'edds' ),
			'type'  => 'checkbox',
			'std'   => 0,
		),
		array(
			'id'    => 'stripe_checkout_remember',
			'name'  => __( 'Enable Remember Me', 'edds' ),
			'desc'  => __( 'Check this box to enable the Remember Me option in the Stripe Checkout modal window.', 'edds' ),
			'type'  => 'checkbox',
			'std'   => 0,
		),
	);

	if ( version_compare( EDD_VERSION, 2.5, '>=' ) ) {
		$stripe_settings = array( 'edd-stripe' => $stripe_settings );
	}

	return array_merge( $settings, $stripe_settings );
}
add_filter( 'edd_settings_gateways', 'edds_add_settings' );

/**
 * Force full billing address display when taxes are enabled
 *
 * @access      public
 * @since       2.5
 * @return      string
 */
function edd_stripe_sanitize_stripe_billing_fields_save( $value, $key ) {

	if( 'stripe_billing_fields' == $key && edd_use_taxes() ) {

		$value = 'full';

	}

	return $value;

}
add_filter( 'edd_settings_sanitize_select', 'edd_stripe_sanitize_stripe_billing_fields_save', 10, 2 );

/**
 * Load our javascript
 *
 * @access      public
 * @since       1.0
 * @param bool  $override Allows registering stripe.js on pages other than is_checkout()
 * @return      void
 */
function edd_stripe_js( $override = false ) {
	if ( function_exists( 'edd_is_checkout' ) ) {
		global $edd_options;

		$publishable_key = NULL;

		if ( edd_is_test_mode() ) {
			$publishable_key = edd_get_option( 'test_publishable_key', '' );
		} else {
			$publishable_key = edd_get_option( 'live_publishable_key', '' );
		}

		wp_enqueue_script( 'stripe-js', 'https://js.stripe.com/v2/', array( 'jquery' ) );

		if ( edd_is_checkout() || $override ) {

			wp_enqueue_script( 'stripe-checkout', 'https://checkout.stripe.com/checkout.js', array( 'jquery' ) );
			wp_enqueue_script( 'edd-stripe-js', EDDSTRIPE_PLUGIN_URL . 'edd-stripe.js', array( 'jquery', 'stripe-js' ), EDD_STRIPE_VERSION );

			$stripe_vars = apply_filters( 'edd_stripe_js_vars', array(
				'publishable_key'  => trim( $publishable_key ),
				'is_ajaxed'        => edd_is_ajax_enabled() ? 'true' : 'false',
				'currency'         => edd_get_currency(),
				'locale'           => edds_get_stripe_checkout_locale(),
				'is_zero_decimal'  => edds_is_zero_decimal_currency() ? 'true' : 'false',
				'checkout'         => edd_get_option( 'stripe_checkout' ) ? 'true' : 'false',
				'store_name'       => get_bloginfo( 'name' ),
				'alipay'           => edd_get_option( 'stripe_alipay' ) ? 'true' : 'false',
				'submit_text'      => edd_get_option( 'stripe_checkout_button_text', __( 'Next', 'edds' ) ),
				'image'            => edd_get_option( 'stripe_checkout_image' ),
				'zipcode'          => edd_get_option( 'stripe_checkout_zip_code', false ) ? 'true' : 'false',
				'billing_address'  => edd_get_option( 'stripe_checkout_billing', false ) ? 'true' : 'false',
				'remember_me'      => edd_get_option( 'stripe_checkout_remember', false ) ? 'true' : 'false',
				'no_key_error'     => __( 'Stripe publishable key missing. Please enter your publishable key in Settings.', 'edds' )
			) );

			wp_localize_script( 'edd-stripe-js', 'edd_stripe_vars', $stripe_vars );

		}
	}
}
add_action( 'wp_enqueue_scripts', 'edd_stripe_js', 100 );

/**
 * Load our admin javascript
 *
 * @access      public
 * @since       1.8
 * @return      void
 */
function edd_stripe_admin_js( $payment_id  = 0 ) {

	if( 'stripe' !== edd_get_payment_gateway( $payment_id ) ) {
		return;
	}
?>
	<script type="text/javascript">
		jQuery(document).ready(function($) {
			$('select[name=edd-payment-status]').change(function() {

				if( 'refunded' == $(this).val() ) {

					// Localize refund label
					var edd_stripe_refund_charge_label = "<?php echo esc_js( __( 'Refund Charge in Stripe', 'edds' ) ); ?>";

					$(this).parent().parent().append( '<input type="checkbox" id="edd_refund_in_stripe" name="edd_refund_in_stripe" value="1" style="margin-top: 0;" />' );
					$(this).parent().parent().append( '<label for="edd_refund_in_stripe">' + edd_stripe_refund_charge_label + '</label>' );

				} else {

					$('#edd_refund_in_stripe').remove();
					$('label[for="edd_refund_in_stripe"]').remove();

				}

			});
		});
	</script>
<?php

}
add_action( 'edd_view_order_details_before', 'edd_stripe_admin_js', 100 );

/**
 * Process refund in Stripe
 *
 * @access      public
 * @since       1.8
 * @return      void
 */
function edd_stripe_process_refund( $payment_id, $new_status, $old_status ) {

	global $edd_options;

	if( empty( $_POST['edd_refund_in_stripe'] ) ) {
		return;
	}

	$should_process_refund = 'publish' != $old_status && 'revoked' != $old_status ? false : true;
	$should_process_refund = apply_filters( 'edds_should_process_refund', $should_process_refund, $payment_id, $new_status, $old_status );

	if( false === $should_process_refund ) {
		return;
	}

	if( 'refunded' != $new_status ) {
		return;
	}

	$charge_id = edd_get_payment_transaction_id( $payment_id );

	if( empty( $charge_id ) || $charge_id == $payment_id ) {

		$notes = edd_get_payment_notes( $payment_id );
		foreach ( $notes as $note ) {
			if ( preg_match( '/^Stripe Charge ID: ([^\s]+)/', $note->comment_content, $match ) ) {
				$charge_id = $match[1];
				break;
			}
		}

	}

	// Bail if no charge ID was found
	if( empty( $charge_id ) ) {
		return;
	}

	$secret_key = edd_is_test_mode() ? trim( $edd_options['test_secret_key'] ) : trim( $edd_options['live_secret_key'] );

	\Stripe\Stripe::setApiKey( $secret_key );

	if ( method_exists( '\Stripe\Stripe', 'setAppInfo' ) ) {
		\Stripe\Stripe::setAppInfo( 'Easy Digital Downloads - Stripe', EDD_STRIPE_VERSION, esc_url( site_url() ) );
	}

	$ch = \Stripe\Charge::retrieve( $charge_id );

	try {

		$ch->refund();

		edd_insert_payment_note( $payment_id, __( 'Charge refunded in Stripe', 'edds' ) );

	} catch ( Exception $e ) {

		// some sort of other error
		$body = $e->getJsonBody();
		$err  = $body['error'];

		if( isset( $err['message'] ) ) {
			$error = $err['message'];
		} else {
			$error = __( 'Something went wrong while refunding the Charge in Stripe.', 'edds' );
		}

		wp_die( $error, __( 'Error', 'edds' ) , array( 'response' => 400 ) );

	}

	do_action( 'edds_payment_refunded', $payment_id );

}
add_action( 'edd_update_payment_status', 'edd_stripe_process_refund', 200, 3 );

/**
 * Look up the stripe customer id in user meta, and look to recurring if not found yet
 *
 * @since  2.4.4
 * @param  int $user_id The user ID or email to look up
 * @return string       Stripe customer ID
 */
function edds_get_stripe_customer_id( $user_id_or_email ) {
	$user_id     = 0;
	$customer_id = '';

	if( is_email( $user_id_or_email ) ) {

		$customer = new EDD_Customer( $user_id_or_email );
		if( $customer->id > 0 && ! empty( $customer->user_id ) ) {
			$user_id = $customer->user_id;
		}

	} else {

		$user_id = $user_id_or_email;

	}

	if ( ! empty( $user_id ) ) {
		$customer_id = get_user_meta( $user_id, edd_stripe_get_customer_key(), true );
	}

	if ( empty( $customer_id ) && class_exists( 'EDD_Recurring_Subscriber' ) ) {

		$by_user_id   = is_int( $user_id_or_email ) ? true : false;
		$subscriber   = new EDD_Recurring_Subscriber( $user_id_or_email, $by_user_id );

		if ( $subscriber->id > 0 ) {

			$verified = false;

			if ( ( $by_user_id && $user_id_or_email == $subscriber->user_id ) ) {
				// If the user ID given, matches that of the subscriber
				$verified = true;
			} else {
				// If the email used is the same as the priamry email
				if ( $subscriber->email == $user_id_or_email ) {
					$verified = true;
				}

				// If the email is in the EDD 2.6 Additional Emails
				if ( property_exists( $subscriber, 'emails' ) && in_array( $user_id_or_email, $subscriber->emails ) ) {
					$verified = true;
				}
			}

			if ( $verified ) {
				$customer_id = $subscriber->get_recurring_customer_id( 'stripe' );
			}

		}

		if ( ! empty( $customer_id ) ) {
			update_user_meta( $subscriber->user_id, edd_stripe_get_customer_key(), $customer_id );
		}

	}

	return $customer_id;
}

/**
 * Get the meta key for storing Stripe customer IDs in
 *
 * @access      public
 * @since       1.6.7
 * @return      void
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
 * Outputs javascript for the Stripe Checkout modal
 *
 * @since  2.0
 * @return void
 */
function edd_stripe_purchase_link_output( $download_id = 0, $args = array() ) {
	global $printed_stripe_purchase_link;

	// Stop our output from being triggered if someone is looking at the content for meta tags, like Jetpack
	if ( doing_action( 'wp_head' ) ) {
		return;
	}

	if ( ! empty( $printed_stripe_purchase_link[ $download_id ] ) ) {
		return;
	}

	if( ! isset( $args['stripe-checkout'] ) ) {
		return;
	}

	if( ! edd_is_gateway_active( 'stripe' ) ) {
		return;
	}

	edd_stripe_js( true );

	if ( edd_is_test_mode() ) {
		$publishable_key = trim( edd_get_option( 'test_publishable_key' ) );
	} else {
		$publishable_key = trim( edd_get_option( 'live_publishable_key' ) );
	}

	$download = get_post( $download_id );

	$email = '';
	if( is_user_logged_in() ) {
		$current_user = wp_get_current_user();
		$email        = $current_user->user_email;
	}
?>
	<script src="https://checkout.stripe.com/checkout.js"></script>
	<script>
		jQuery(document).ready(function($) {

			var edd_global_vars;
			var edd_scripts;
			var form;

			$('#edd_purchase_<?php echo $download_id; ?> .edd-add-to-cart,.edd_purchase_<?php echo $download_id; ?> .edd-add-to-cart').click(function(e) {

				form = $(this).parents('.edd_download_purchase_form');

				e.preventDefault();

				var label = form.find('.edd-add-to-cart-label').text();

				if( form.find( '.edd_price_options' ).length || form.find( '.edd_price_option_<?php echo $download_id; ?>' ).length ) {

					var custom_price = false;
					var price_id;
					var prices = [];
					var amount = 0;

					<?php foreach( edd_get_variable_prices( $download_id ) as $price_id => $price ) : ?>
						prices[<?php echo $price_id; ?>] = <?php echo $price['amount']*100; ?>;
					<?php endforeach; ?>

					if( form.find( '.edd_price_option_<?php echo $download_id; ?>' ).length > 1 ) {

						if( form.find('.edd_price_options input:checked').hasClass( 'edd_cp_radio' ) ) {

							custom_price = true;
							amount = form.find( '.edd_cp_price' ).val() * 100;

						} else {
							price_id = form.find('.edd_price_options input:checked').val();
						}

					} else {

						price_id = form.find('.edd_price_option_<?php echo $download_id; ?>').val();

					}

					if( ! custom_price ) {

						amount = prices[ price_id ];

					}

				} else if( form.find( '.edd_cp_price' ).length && form.find( '.edd_cp_price' ).val() ) {
					amount = form.find( '.edd_cp_price' ).val() * 100;

				} else {
					amount = <?php echo edd_get_download_price( $download_id ) * 100; ?>;
				}

				StripeCheckout.configure({
					key: '<?php echo $publishable_key; ?>',
					locale: '<?php echo edds_get_stripe_checkout_locale(); ?>',
					//image: '/square-image.png',
					token: function(token) {
						// insert the token into the form so it gets submitted to the server
						form.append("<input type='hidden' name='edd_stripe_token' value='" + token.id + "' />");
						form.append("<input type='hidden' name='edd_email' value='" + token.email + "' />");
						// submit
						form.get(0).submit();
					},
					opened: function() {

					},
					closed: function() {
						form.find('.edd-add-to-cart').removeAttr( 'data-edd-loading' );
						form.find('.edd-add-to-cart-label').text( label ).show();
					}
				}).open({
					name: '<?php echo esc_js( get_bloginfo( "name" ) ); ?>',
					image: '<?php echo esc_url( edd_get_option( "stripe_checkout_image" ) ); ?>',
					description: '<?php echo esc_js( $download->post_title ); ?>',
					alipay: <?php echo edd_get_option( 'stripe_alipay' ) ? 'true' : 'false'; ?>,
					amount: Math.round( amount ),
					zipCode: <?php echo edd_get_option( 'stripe_checkout_zip_code' ) ? 'true' : 'false'; ?>,
					allowRememberMe: <?php echo edd_get_option( 'stripe_checkout_remember' ) ? 'true' : 'false'; ?>,
					billingAddress: <?php echo edd_get_option( 'stripe_checkout_billing' ) ? 'true' : 'false'; ?>,
					email: '<?php echo esc_js( $email ); ?>',
					currency: '<?php echo edd_get_currency(); ?>'
				});

				return false;

			});

		});
	</script>
<?php
	$printed_stripe_purchase_link[ $download_id ] = true;
}
add_action( 'edd_purchase_link_end', 'edd_stripe_purchase_link_output', 99999, 2 );

/**
 * Injects the Stripe token and customer email into the pre-gateway data
 *
 * @since  2.0
 * @return array
 */
function edd_stripe_straight_to_gateway_data( $purchase_data ) {

	if( isset( $_REQUEST['edd_stripe_token'] ) ) {

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