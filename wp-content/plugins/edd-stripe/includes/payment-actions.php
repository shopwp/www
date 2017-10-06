<?php

/**
 * Process stripe checkout submission
 *
 * @access      public
 * @since       1.0
 * @return      void
 */
function edds_process_stripe_payment( $purchase_data ) {

	global $edd_stripe_is_buy_now;

	if ( edd_is_test_mode() ) {
		$secret_key = trim( edd_get_option( 'test_secret_key' ) );
	} else {
		$secret_key = trim( edd_get_option( 'live_secret_key' ) );
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

	if ( ! isset( $_POST['edd_stripe_token'] ) && ! isset( $_POST['edd_stripe_existing_card'] ) ) {

		// no Stripe token
		edd_set_error( 'no_token', __( 'Missing Stripe token. Please contact support.', 'edds' ) );
		edd_record_gateway_error( __( 'Missing Stripe Token', 'edds' ), __( 'A Stripe token failed to be generated. Please check Stripe logs for more information', ' edds' ) );

	} else {
		$card_data = isset( $_POST['edd_stripe_token'] ) ? $_POST['edd_stripe_token'] : $_POST['edd_stripe_existing_card'];
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

				$customer_id = edds_get_stripe_customer_id( get_current_user_id() );

			}

			if ( empty( $customer_id ) ) {

				// No customer ID found, let's look one up based on the email
				$customer_id = edds_get_stripe_customer_id( $purchase_data['user_email'], false );

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

			$existing_card = false;
			$preapprove_only = edd_get_option( 'stripe_preapprove_only' );

			if ( $customer_exists ) {

				if ( is_array( $card_data ) ) {
					$card_data[ 'object' ] = 'card';
				} else {
					if ( false !== strpos( $card_data, 'tok_' ) ) {

						// We were given a token, so we need to create a new source
						$card    = $cu->sources->create( array( 'source' => $card_data ) );
						$card_id = $card->id;

					} else {

						// We were given a card ID, so we don't need to tokenize it.
						$card_id       = $card_data;
						$existing_card = true;

						// User updated card billing details, so let's update the card as well.`
						if ( ! empty( $_POST['edd_stripe_update_billing_address'] ) ) {
							$card = $cu->sources->retrieve( $card_id );
							$address_info = $payment_data['user_info']['address'];
							foreach ( $address_info as $key => $value ) {
								switch( $key ) {
									case 'line1':
										$card->address_line1 = $value;
										break;

									case 'line2':
										$card->address_line2 = $value;
										break;

									case 'city':
										$card->address_city = $value;
										break;

									case 'state':
										$card->address_state = $value;
										break;

									case 'country':
										$card->address_country = $value;
										break;

									case 'zip':
										$card->address_zip = $value;
										break;
								}
							}

							$card->save();
						}
					}
				}

				// Process a normal one-time charge purchase
				if( ! $preapprove_only ) {

					if( edds_is_zero_decimal_currency() ) {

						$amount = $purchase_data['price'];

					} else {

						// Round to the nearest integer, see GitHub issue #270
						$amount = round( $purchase_data['price'] * 100, 0 );

					}

					$statement_descriptor = edd_get_option( 'stripe_statement_descriptor', '' );
					if ( empty( $statement_descriptor ) ) {
						$statement_descriptor = substr( $purchase_summary, 0, 22 );
					}
					$statement_descriptor = apply_filters( 'edds_statement_descriptor', $statement_descriptor, $purchase_data );

					$unsupported_characters = array( '<', '>', '"', '\'' );
					$statement_descriptor   = str_replace( $unsupported_characters, '', $statement_descriptor );

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

				$edd_customer = new EDD_Customer( $purchase_data['user_email'] );
				if ( $edd_customer->id > 0 ) {
					$edd_customer->update_meta( edd_stripe_get_customer_key(), $customer_id );
				}

			} else {

				edd_record_gateway_error( __( 'Customer Creation Failed', 'edds' ), sprintf( __( 'Customer creation failed while processing a payment. Payment Data: %s', ' edds' ), json_encode( $payment_data ) ) );

			}

			if ( $payment && ( ! empty( $customer_id ) || ! empty( $charge ) ) ) {

				$payment = new EDD_Payment( $payment );

				if ( $preapprove_only ) {
					$payment->status = 'preapproval';
					$payment->update_meta( '_edds_stripe_customer_id', $customer_id );
				} else {
					$payment->status = 'publish';
				}

				if ( $existing_card ) {
					$payment->update_meta( '_edds_used_existing_card', true );
				}

				// You should be using Stripe's API here to retrieve the invoice then confirming it's been paid
				if ( ! empty( $charge ) ) {

					$payment->add_note( 'Stripe Charge ID: ' . $charge->id );
					$payment->transaction_id = $charge->id;

				} elseif ( ! empty( $customer_id ) ) {

					$payment->add_note( 'Stripe Customer ID: ' . $customer_id );

				}

				$payment->save();

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

			// Check if an error message exists, if not use an empty string.
			$message = $e->getMessage();

			if ( empty( $message ) ) {
				$message = __( 'Something went wrong.', 'edds' );
			}

			edd_set_error( 'request_error', $message );

			if( $edd_stripe_is_buy_now ) {
				wp_die( $message, __( 'Card Processing Error', 'edds' ) );
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
 * Charge a preapproved payment
 *
 * @since 1.6
 * @return bool
 */
function edds_charge_preapproved( $payment_id = 0 ) {

	if( empty( $payment_id ) )
		return false;

	$customer_id = get_post_meta( $payment_id, '_edds_stripe_customer_id', true );

	if( empty( $customer_id ) || empty( $payment_id ) ) {
		return;
	}

	if ( 'preapproval' !== get_post_status( $payment_id ) ) {
		return;
	}

	$secret_key = edd_is_test_mode() ? trim( edd_get_option( 'test_secret_key' ) ) : trim( edd_get_option( 'live_secret_key' ) );

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

		$statement_descriptor = edd_get_option( 'stripe_statement_descriptor', '' );
		if ( empty( $statement_descriptor ) ) {
			$statement_descriptor = substr( $purchase_summary, 0, 22 );
		}

		$statement_descriptor   = apply_filters( 'edds_preapproved_statement_descriptor', $statement_descriptor, $payment_id );

		$unsupported_characters = array( '<', '>', '"', '\'' );
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
 * Listen for Stripe events, primarily recurring payments
 *
 * @access      public
 * @since       1.5
 * @return      void
 */

function edds_stripe_event_listener() {

	if ( isset( $_GET['edd-listener'] ) && $_GET['edd-listener'] == 'stripe' ) {

		$secret_key = edd_is_test_mode() ? trim( edd_get_option( 'test_secret_key' ) ) : trim( edd_get_option( 'live_secret_key' ) );

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

				case 'review.opened' :

					$is_live = ! edd_is_test_mode();
					$review  = $event->data->object;

					// Make sure the modes match
					if ( $is_live == $review->livemode ) {
						$payment_id = edd_get_purchase_id_by_transaction_id( $review->charge );
						if ( $payment_id ) {
							$payment    = new EDD_Payment( $payment_id );
							$payment->add_note( sprintf( __( 'Stripe Radar review opened with a reason of %s.', 'edds' ), $review->reason ) );

							do_action( 'edd_stripe_review_opened', $review, $payment_id );
						}
					}

					break;

				case 'review.closed' :

					$is_live = ! edd_is_test_mode();
					$review  = $event->data->object;

					// Make sure the modes match
					if ( $is_live == $review->livemode ) {
						$payment_id = edd_get_purchase_id_by_transaction_id( $review->charge );
						if ( $payment_id ) {
							$payment    = new EDD_Payment( $payment_id );
							$payment->add_note( sprintf( __( 'Stripe Radar review closed with a reason of %s.', 'edds' ), $review->reason ) );

							do_action( 'edd_stripe_review_closed', $review, $payment_id );
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
 * Process refund in Stripe
 *
 * @access      public
 * @since       1.8
 * @return      void
 */
function edd_stripe_process_refund( $payment_id, $new_status, $old_status ) {

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

	$secret_key = edd_is_test_mode() ? trim( edd_get_option( 'test_secret_key' ) ) : trim( edd_get_option( 'live_secret_key' ) );

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