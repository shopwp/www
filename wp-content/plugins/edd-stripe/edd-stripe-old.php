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

	if ( ! class_exists( 'Stripe' ) )
		require_once EDDS_PLUGIN_DIR . '/Stripe-old/Stripe.php';

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

	if ( !$errors ) {

		try {

			Stripe::setApiKey( $secret_key );

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

				$user = get_user_by( 'email', $purchase_data['user_email'] );

				if ( $user ) {

					$customer_id = get_user_meta( $user->ID, edd_stripe_get_customer_key(), true );

					if ( $customer_id ) {

						$customer_exists = true;

						try {

							// Update the customer to ensure their card data is up to date
							$cu = Stripe_Customer::retrieve( $customer_id );

							if( isset( $cu->deleted ) && $cu->deleted ) {

								// This customer was deleted
								$customer_exists = false;

							} else {

								$cu->card = $card_data;
								$cu->save();

							}

						// No customer found
						} catch ( Exception $e ) {


							$customer_exists = false;

						}

					}

				}

			}

			if ( ! $customer_exists ) {

				// Create a customer first so we can retrieve them later for future payments
				$customer = Stripe_Customer::create( array(
						'description' => $purchase_data['user_email'],
						'email'       => $purchase_data['user_email'],
						'card'        => $card_data
					)
				);

				$customer_id = is_array( $customer ) ? $customer['id'] : $customer->id;

				if ( is_user_logged_in() ) {
					update_user_meta( $user->ID, edd_stripe_get_customer_key(), $customer_id );
				}
			}

			if ( edds_is_recurring_purchase( $purchase_data ) && ( ! empty( $customer ) || $customer_exists ) ) {

				// Process a recurring subscription purchase
				$cu = Stripe_Customer::retrieve( $customer_id );

				/**********************************************************
				* Fees, and discounts have to be handled differently
				* with recurring subscriptions, so each is added as an
				* invoice item and then charged as one time items
				**********************************************************/

				$invoice_items  = array();
				$needs_invoiced = false;

				if ( ! empty( $purchase_data['fees'] ) ) {

					foreach ( $purchase_data['fees'] as $fee ) {

						if( edds_is_zero_decimal_currency() ) {
							$fee_amount = $fee['amount'];
						} else {
							$fee_amount = $fee['amount'] * 100;
						}

						$invoice = Stripe_InvoiceItem::create( array(
								'customer'    => $customer_id,
								'amount'      => $fee_amount,
								'currency'    => edd_get_currency(),
								'description' => $fee['label']
							)
						);

						if( ! empty( $invoice->id ) ) {
							$invoice_items[] = $invoice->id;
						}

					}
					$needs_invoiced = true;
				}

				if ( $purchase_data['discount'] > 0 ) {

					if( edds_is_zero_decimal_currency() ) {
						$discount_amount = $purchase_data['discount'];
					} else {
						$discount_amount = $purchase_data['discount'] * 100;
					}

					$invoice = Stripe_InvoiceItem::create( array(
							'customer'    => $customer_id,
							'amount'      => $discount_amount * -1,
							'currency'    => edd_get_currency(),
							'description' => $purchase_data['user_info']['discount']
						)
					);

					if( ! empty( $invoice->id ) ) {
						$invoice_items[] = $invoice->id;
					}

					$needs_invoiced = true;
				}

				try {

					$plan_id = edds_get_plan_id( $purchase_data );

					// record the pending payment
					$payment = edd_insert_payment( $payment_data );

					// Add support for Auto-Register by alwyas relying on the logged in user
					$user_id = get_current_user_id();

					set_transient( '_edd_recurring_payment_' . $payment, '1', DAY_IN_SECONDS );

					// Store the parent payment ID in the user meta
					EDD_Recurring_Customer::set_customer_payment_id( $user_id, $payment );

					// Calculate the percentage of the price that is tax
					$tax_percentage = 0;
					if ( $purchase_data['tax'] > 0 && ! edd_prices_include_tax() ) {
						$tax_percentage = edd_get_tax_rate();
						if( $tax_percentage < 1 ) {
							$tax_percentage *= 100;
						}
					}

					// Update the customer's subscription in Stripe
					$plan_data = array( 'plan' => $plan_id, 'tax_percent' => round( $tax_percentage, 2 ) );

					$plan_data = apply_filters( 'edd_recurring_stripe_subscription_details', $plan_data, $payment, $user_id );
					$create	   = apply_filters( 'edd_recurring_stripe_create_subscription', false );
					if ( $create ) {
						$customer_response = $cu->subscriptions->create( $plan_data );
					} else {
						$customer_response = $cu->updateSubscription( $plan_data );
					}

					// Set user as subscriber
					EDD_Recurring_Customer::set_as_subscriber( $user_id );

					// store the customer recurring ID
					EDD_Recurring_Customer::set_customer_id( $user_id, $customer_id );

					// Set the customer status
					EDD_Recurring_Customer::set_customer_status( $user_id, 'active' );

					// Calculate the customer's new expiration date
					$new_expiration = EDD_Recurring_Customer::calc_user_expiration( $user_id, $payment );

					// Set the customer's new expiration date
					EDD_Recurring_Customer::set_customer_expiration( $user_id, $new_expiration );

				} catch ( Stripe_CardError $e ) {

					$body = $e->getJsonBody();
					$err  = $body['error'];

					if( isset( $err['message'] ) ) {
						edd_set_error( 'payment_error', $err['message'] );
					} else {
						edd_set_error( 'payment_error', __( 'There was an error processing your payment, please ensure you have entered your card number correctly.', 'edds' ) );
					}

					edd_record_gateway_error( __( 'Stripe Error', 'edds' ), sprintf( __( 'There was an error while processing a Stripe payment. Payment data: %s', ' edds' ), json_encode( $err ) ), 0 );

				} catch ( Stripe_ApiConnectionError $e ) {

					$body = $e->getJsonBody();
					$err  = $body['error'];

					edd_set_error( 'payment_error', __( 'There was an error processing your payment (Stripe\'s API is down), please try again', 'edds' ) );
					edd_record_gateway_error( __( 'Stripe Error', 'edds' ), sprintf( __( 'There was an error processing your payment (Stripe\'s API was down). Error: %s', 'edds' ), json_encode( $err['message'] ) ), 0 );

				} catch ( Stripe_InvalidRequestError $e ) {

					$body = $e->getJsonBody();
					$err  = $body['error'];

					// Bad Request of some sort. Maybe Christoff was here ;)
					if( isset( $err['message'] ) ) {
						edd_set_error( 'request_error', $err['message'] );
					} else {
						edd_set_error( 'request_error', sprintf( __( 'The Stripe API request was invalid, please try again. Error: %s', 'edds' ), json_encode( $err['message'] ) ) );
					}

				} catch ( Stripe_ApiError $e ) {

					$body = $e->getJsonBody();
					$err  = $body['error'];

					if( isset( $err['message'] ) ) {
						edd_set_error( 'request_error', $err['message'] );
					} else {
						edd_set_error( 'request_error', __( 'The Stripe API request was invalid, please try again', 'edds' ) );
					}
					edd_record_gateway_error( __( 'Stripe Error', 'edds' ), sprintf( __( 'There was an error with Stripe\'s API: ', 'edds' ), json_encode( $err['message'] ) ), 0 );

				} catch ( Stripe_AuthenticationError $e ) {

					$body = $e->getJsonBody();
					$err  = $body['error'];

					// Authentication error. Stripe keys in settings are bad.
					if( isset( $err['message'] ) ) {
						edd_set_error( 'request_error', $err['message'] );
					} else {
						edd_set_error( 'api_error', __( 'The API keys entered in settings are incorrect', 'edds' ) );
					}

				} catch ( Stripe_Error $e ) {

					$body = $e->getJsonBody();
					$err  = $body['error'];

					// generic stripe error
					if( isset( $err['message'] ) ) {
						edd_set_error( 'request_error', $err['message'] );
					} else {
						edd_set_error( 'api_error', __( 'Something went wrong.', 'edds' ) );
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

				}

				if( ! empty( $err ) ) {

					// Delete any invoice items we created for fees, taxes, and other
					foreach( $invoice_items as $invoice ) {
						$ii = Stripe_InvoiceItem::retrieve( $invoice );
						$ii->delete();
					}

					edd_send_back_to_checkout( '?payment-mode=stripe' );
				}

			} elseif ( ! empty( $customer ) || $customer_exists ) {

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
						"amount"      => $amount,
						"currency"    => edd_get_currency(),
						"customer"    => $customer_id,
						"description" => html_entity_decode( $purchase_summary, ENT_COMPAT, 'UTF-8' ),
						'metadata'    => array(
							'email'   => $purchase_data['user_info']['email']
						)
					);

					if( ! empty( $statement_descriptor ) ) {
						$args[ 'statement_descriptor' ] = $statement_descriptor;
					}

					$charge = Stripe_Charge::create( apply_filters( 'edds_create_charge_args', $args, $purchase_data ) );
				}

				// record the pending payment
				$payment = edd_insert_payment( $payment_data );

			} else {

				edd_record_gateway_error( __( 'Customer Creation Failed', 'edds' ), sprintf( __( 'Customer creation failed while processing a payment. Payment Data: %s', ' edds' ), json_encode( $payment_data ) ), $payment );

			}

			if ( $payment && ( ! empty( $customer_id ) || ! empty( $charge ) ) ) {

				if ( ! empty( $needs_invoiced ) ) {

					try {
						// Create the invoice containing taxes / discounts / fees
						$invoice = Stripe_Invoice::create( array(
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
		} catch ( Stripe_CardError $e ) {

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

		} catch ( Stripe_ApiConnectionError $e ) {

			$body = $e->getJsonBody();
			$err  = $body['error'];

			edd_set_error( 'payment_error', __( 'There was an error processing your payment (Stripe\'s API is down), please try again', 'edds' ) );
			edd_record_gateway_error( __( 'Stripe Error', 'edds' ), sprintf( __( 'There was an error processing your payment (Stripe\'s API was down). Error: %s', 'edds' ), json_encode( $err['message'] ) ), 0 );

			if( $edd_stripe_is_buy_now ) {
				wp_die( $err['message'], __( 'Card Processing Error', 'edds' ) );
			} else {
				edd_send_back_to_checkout( '?payment-mode=stripe' );
			}

		} catch ( Stripe_InvalidRequestError $e ) {

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

		}
		catch ( Stripe_ApiError $e ) {

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

		} catch ( Stripe_AuthenticationError $e ) {

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

		} catch ( Stripe_Error $e ) {

			$body = $e->getJsonBody();
			$err  = $body['error'];

			// generic stripe error
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
 * Create recurring payment plans when downloads are saved
 *
 * This is in order to support the Recurring Payments module
 *
 * @access      public
 * @since       1.5
 * @return      int
 */

function edds_create_recurring_plans( $post_id = 0 ) {
	global $edd_options, $post;

	if ( ! class_exists( 'EDD_Recurring' ) ) {
		return $post_id;
	}

	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) || isset( $_REQUEST['bulk_edit'] ) ) {
		return $post_id;
	}

	if ( isset( $post->post_type ) && $post->post_type == 'revision' ) {
		return $post_id;
	}

	if ( ! isset( $post->post_type ) || $post->post_type != 'download' ) {
		return $post_id;
	}

	if ( $post->post_status == 'draft' ) {
		return $post_id;
	}

	if ( ! current_user_can( 'edit_products', $post_id ) ) {
		return $post_id;
	}

	if ( ! class_exists( 'Stripe' ) )
		require_once EDDS_PLUGIN_DIR . '/Stripe-old/Stripe.php';

	$secret_key = edd_is_test_mode() ? trim( $edd_options['test_secret_key'] ) : trim( $edd_options['live_secret_key'] );

	$plans = array();

	try {

		Stripe::setApiKey( $secret_key );

		if ( edd_has_variable_prices( $post_id ) ) {

			$prices = edd_get_variable_prices( $post_id );
			foreach ( $prices as $price_id => $price ) {

				if ( EDD_Recurring()->is_price_recurring( $post_id, $price_id ) ) {

					$period = EDD_Recurring()->get_period( $price_id, $post_id );

					if ( EDD_Recurring()->get_times( $price_id, $post_id ) > 0 )
						wp_die( __( 'Stripe requires that the Times option be set to 0.', 'edds' ), __( 'Error', 'edds' ), array( 'response' => 400 ) );

					$plans[] = array(
						'name'   => $price['name'],
						'price'  => $price['amount'],
						'period' => $period
					);

				}
			}

		} else {

			if ( EDD_Recurring()->is_recurring( $post_id ) ) {

				$period = EDD_Recurring()->get_period_single( $post_id );

				if ( EDD_Recurring()->get_times_single( $post_id ) > 0 )
					wp_die( __( 'Stripe requires that the Times option be set to 0.', 'edds' ), __( 'Error', 'edds' ), array( 'response' => 400 ) );

				$plans[] = array(
					'name'   => get_post_field( 'post_name', $post_id ),
					'price'  => edd_get_download_price( $post_id ),
					'period' => $period
				);
			}
		}

		// Get all plans so we know which ones already exist
		$all_plans = array();
		$more      = true;
		$params    = array( 'limit' => 100 ); // Plan request params

		while ( $more ) {

			if ( ! empty( $all_plans ) ) {
				$params['starting_after'] = end( $all_plans );
			}

			$response  = Stripe_Plan::all( $params );
			$all_plans = array_merge( $all_plans, wp_list_pluck( $response->data, "id" ) );
			$more      = absint( $response->has_more );

		}

		foreach ( $plans as $plan ) {

			// Create the plan ID
			$plan_id = $plan['name'] . '_' . $plan['price'] . '_' . $plan['period'];
			$plan_id = sanitize_key( $plan_id );
			$plan_id = apply_filters( 'edd_recurring_plan_id', $plan_id, $plan );

			if ( in_array( $plan_id, $all_plans ) )
				continue;

			if( edds_is_zero_decimal_currency() ) {
				$amount = $plan['price'];
			} else {
				$amount = $plan['price'] * 100;
			}

			$plan_args = array(
				"amount"   => $amount,
				"interval" => $plan['period'],
				"name"     => $plan['name'],
				"currency" => edd_get_currency(),
				"id"       => $plan_id
			);

			$plan_args = apply_filters( 'edd_recurring_plan_details', $plan_args, $plan_id );

			Stripe_Plan::create( $plan_args );
		}
	} catch( Exception $e ) {
		wp_die( __( 'There was an error creating a payment plan with Stripe.', 'edds' ), __( 'Error', 'edds' ), array( 'response' => 400 ) );
	}
}
add_action( 'save_post', 'edds_create_recurring_plans', 999 );


/**
 * Detect if the current purchase is for a recurring product
 *
 * @access      public
 * @since       1.5
 * @return      bool
 */

function edds_is_recurring_purchase( $purchase_data ) {

	if ( ! class_exists( 'EDD_Recurring' ) )
		return false;

	if ( EDD_Recurring()->is_purchase_recurring( $purchase_data ) )
		return true;

	return false;
}


/**
 * Retrieve the plan ID from the purchased items
 *
 * @access      public
 * @since       1.5
 * @return      string|bool
 */

function edds_get_plan_id( $purchase_data ) {
	foreach ( $purchase_data['downloads'] as $download ) {

		if ( edd_has_variable_prices( $download['id'] ) ) {

			$prices = edd_get_variable_prices( $download['id'] );

			$price_name   = edd_get_price_option_name( $download['id'], $download['options']['price_id'] );
			$price_amount = $prices[ $download['options']['price_id'] ]['amount'];

		} else {

			$price_name   = get_post_field( 'post_name', $download['id'] );
			$price_amount = edd_get_download_price( $download['id'] );

		}

		$period = $download['options']['recurring']['period'];

		$plan_id = $price_name . '_' . $price_amount . '_' . $period;
		return sanitize_key( $plan_id );
	}
	return false;
}


/**
 * Fiter the Recurring Payments cancellation link
 *
 * @access      public
 * @since       1.5
 * @return      string
 */

function edds_recurring_cancel_link( $link = '', $user_id = 0 ) {

	$customer_id = EDD_Recurring_Customer::get_customer_id( $user_id );

	// Only modify Stripe customer's cancellation links
	if ( strpos( $customer_id, 'cus_' ) === false )
		return $link;

	$cancel_url = wp_nonce_url( add_query_arg( array( 'edd_action' => 'cancel_recurring_stripe_customer', 'customer_id' => $customer_id, 'user_id' => $user_id ) ), 'edd_stripe_cancel' );
	$link       = '<a href="%s" class="edd-recurring-cancel" title="%s">%s</a>';
	$link       = sprintf(
		$link,
		$cancel_url,
		__( 'Cancel your subscription', 'edd-recurring' ),
		empty( $atts['text'] ) ? __( 'Cancel Subscription', 'edd-recurring' ) : esc_html( $atts['text'] )
	);

	$link .= '<script type="text/javascript">jQuery(document).ready(function($) {$(".edd-recurring-cancel").on("click", function() { if(confirm("' . __( "Do you really want to cancel your subscription? You will retain access for the length of time you have paid for.", "edds" ) . '")) {return true;}return false;});});</script>';

	return $link;

}
add_filter( 'edd_recurring_cancel_link', 'edds_recurring_cancel_link', 10, 2 );


/**
 * Process a recurring payments cancellation
 *
 * @access      public
 * @since       1.5
 * @return      void
 */

function edds_cancel_subscription( $data ) {
	if ( wp_verify_nonce( $data['_wpnonce'], 'edd_stripe_cancel' ) ) {

		global $edd_options;

		$secret_key = edd_is_test_mode() ? trim( $edd_options['test_secret_key'] ) : trim( $edd_options['live_secret_key'] );

		if ( ! class_exists( 'Stripe' ) )
			require_once EDDS_PLUGIN_DIR . '/Stripe-old/Stripe.php';

		Stripe::setApiKey( $secret_key );

		try {

			$cu = Stripe_Customer::retrieve( urldecode( $data['customer_id'] ) );
			$cu->cancelSubscription( array( 'at_period_end' => true ) );

			EDD_Recurring_Customer::set_customer_status( $data['user_id'], 'cancelled' );

			wp_redirect(
				esc_url_raw( add_query_arg(
					'subscription',
					'cancelled',
					remove_query_arg( array( 'edd_action', 'customer_id', 'user_id', '_wpnonce' ) )
				) )
			);
			exit;

		} catch( Exception $e ) {
			wp_die( '<pre>' . $e . '</pre>', __( 'Error', 'edds' ), array( 'response' => 400 ) );
		}

	}
}
add_action( 'edd_cancel_recurring_stripe_customer', 'edds_cancel_subscription' );

/**
 * Return wether to show the CC Update form or not
 *
 * @since  2.2
 * @param  int $user_id The User ID profile editor being shown
 * @return bool         If the CC update form should show.
 */
function edds_show_update_payment_form( $show_form, $user_id ) {
	$customer_id = EDD_Recurring_Customer::get_customer_id( $user_id );

	// If the User isn't a stripe customer, return false
	if ( strpos( $customer_id, 'cus_' ) === false ) {
		return false;
	}

	return true;
}
add_filter( 'edd_recurring_customer_can_update_card', 'edds_show_update_payment_form', 10, 2 );

/**
 * Sets the Recurring credit card form processor to Stripe
 *
 * @since  2.2
 * @param  string $gateway The default gateway, ''
 * @param  int $user_id    The User ID of the profile being updated
 * @return string          The gateway for stripe
 */
function edds_recurring_update_gateway( $gateway, $user_id ) {
	$customer_id = EDD_Recurring_Customer::get_customer_id( $user_id );

	// If the User isn't a stripe customer, return false
	if ( strpos( $customer_id, 'cus_' ) === false ) {
		return $gateway;
	}

	return 'stripe';
}
add_filter( 'edd_recurring_update_gateway', 'edds_recurring_update_gateway', 10, 2 );

/**
 * Overrides the profile editor credit card form with the Stripe specific form
 *
 * @since  2.2
 * @param  string $html    HTML of the default form
 * @param  int $user_id    The User ID of the profile being edited
 * @return string          The new HTML for the credit card form
 */
function edds_update_payment_form_override( $html, $user_id ) {
	$customer_id = EDD_Recurring_Customer::get_customer_id( $user_id );

	// If the User isn't a stripe customer, return false
	if ( strpos( $customer_id, 'cus_' ) === false ) {
		return $html;
	}

	return edds_credit_card_form( false );
}
add_filter( 'edd_recurring_update_form_html', 'edds_update_payment_form_override', 10, 2 );

/**
 * Processes the strip form when the profile is updated
 *
 * @since  2.2
 * @param  int $user_id  The User ID of the profile being updated
 * @param  array $userdata Array of user data
 * @return void
 */
function edds_process_profile_update( $user_id, $verified ) {

	if ( 1 !== $verified ) {
		wp_die( __( 'Unable to verify payment update.', 'edds' ) );
	}

	$customer_id = EDD_Recurring_Customer::get_customer_id( $user_id );

	// If the User isn't a stripe customer, return
	if ( strpos( $customer_id, 'cus_' ) === false ) {
		return;
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

	if ( $errors ) {
		return;
	}

	// No errors in stripe, continue on through processing

	global $edd_options;

	if ( ! class_exists( 'Stripe' ) )
		require_once EDDS_PLUGIN_DIR . '/Stripe-old/Stripe.php';

	if ( edd_is_test_mode() ) {
		$secret_key = trim( edd_get_option( 'test_secret_key', false ) );
	} else {
		$secret_key = trim( edd_get_option( 'live_secret_key', false ) );
	}

	if ( ! isset( $secret_key ) ) {
		return;
	}

	Stripe::setApiKey( $secret_key );

	$customer_exists = false;

	if ( is_user_logged_in() ) {

		if ( ! empty( $user_id ) ) {

			$customer_id = get_user_meta( $user_id, edd_stripe_get_customer_key(), true );

			if ( $customer_id ) {

				$customer_exists = true;

				try {

					// Update the customer to ensure their card data is up to date
					$cu = Stripe_Customer::retrieve( $customer_id );

					if( isset( $cu->deleted ) && $cu->deleted ) {

						// This customer was deleted
						$customer_exists = false;

					}

				// No customer found
				} catch ( Exception $e ) {

					$customer_exists = false;

				}

			}

		}

		if ( $customer_exists ) {

			$recurring_purcahse_id = get_user_meta( $user_id, '_edd_recurring_user_parent_payment_id', true );
			$downloads['downloads'] = edd_get_payment_meta_downloads( $recurring_purcahse_id );
			$plan_id = edds_get_plan_id( $downloads );

			try {
				$customer_response = $cu->updateSubscription( array( 'plan' => $plan_id, 'card' => $card_data ) );
			} catch ( Stripe_CardError $e ) {

				$body = $e->getJsonBody();
				$err  = $body['error'];

				if( isset( $err['message'] ) ) {
					edd_set_error( 'payment_error', $err['message'] );
				} else {
					edd_set_error( 'payment_error', __( 'There was an error processing your payment, please ensure you have entered your card number correctly.', 'edds' ) );
				}

			} catch ( Stripe_ApiConnectionError $e ) {

				$body = $e->getJsonBody();
				$err  = $body['error'];

				edd_set_error( 'payment_error', __( 'There was an error processing your payment (Stripe\'s API is down), please try again', 'edds' ) );

			} catch ( Stripe_InvalidRequestError $e ) {

				$body = $e->getJsonBody();
				$err  = $body['error'];

				// Bad Request of some sort. Maybe Christoff was here ;)
				if( isset( $err['message'] ) ) {
					edd_set_error( 'request_error', $err['message'] );
				} else {
					edd_set_error( 'request_error', __( 'The Stripe API request was invalid, please try again', 'edds' ) );
				}

			}
			catch ( Stripe_ApiError $e ) {

				$body = $e->getJsonBody();
				$err  = $body['error'];

				if( isset( $err['message'] ) ) {
					edd_set_error( 'request_error', $err['message'] );
				} else {
					edd_set_error( 'request_error', __( 'The Stripe API request was invalid, please try again', 'edds' ) );
				}

			} catch ( Stripe_AuthenticationError $e ) {

				$body = $e->getJsonBody();
				$err  = $body['error'];

				// Authentication error. Stripe keys in settings are bad.
				if( isset( $err['message'] ) ) {
					edd_set_error( 'request_error', $err['message'] );
				} else {
					edd_set_error( 'api_error', __( 'The API keys entered in settings are incorrect', 'edds' ) );
				}

			} catch ( Stripe_Error $e ) {

				$body = $e->getJsonBody();
				$err  = $body['error'];

				// generic stripe error
				if( isset( $err['message'] ) ) {
					edd_set_error( 'request_error', $err['message'] );
				} else {
					edd_set_error( 'api_error', __( 'Something went wrong.', 'edds' ) );
				}

			} catch ( Exception $e ) {
				edd_set_error( 'update_error', __( 'There was an error with this payment method. Please try with another card.', 'edds' ) );
			}


			$errors = edd_get_errors();

			// Only do our redirect if it's the standalone form
			if ( ! did_action( 'edd_pre_update_user_profile' ) && empty( $errors ) ) {
				$url = esc_url( add_query_arg( array( 'updated' => true ), edd_get_current_page_url() ) );
				wp_redirect( $url );
				exit;
			}

		}

	}

}
add_action( 'edd_recurring_process_profile_card_update', 'edds_process_profile_update', 10, 2 );

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

	if( empty( $customer_id ) || empty( $payment_id ) )
		return;

	if ( 'preapproval' !== get_post_status( $payment_id ) )
		return;

	if ( ! class_exists( 'Stripe' ) )
		require_once EDDS_PLUGIN_DIR . '/Stripe-old/Stripe.php';

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

	if( empty( $customer_id ) || empty( $payment_id ) )
		return;

	if ( 'preapproval' !== get_post_status( $payment_id ) )
		return;

	if ( ! class_exists( 'Stripe' ) )
		require_once EDDS_PLUGIN_DIR . '/Stripe-old/Stripe.php';

	$secret_key = edd_is_test_mode() ? trim( $edd_options['test_secret_key'] ) : trim( $edd_options['live_secret_key'] );

	Stripe::setApiKey( $secret_key );

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

		$charge = Stripe_Charge::create( array(
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

	} catch ( Stripe_CardError $e ) {

		$body = $e->getJsonBody();
		$err  = $body['error'];

		$error_message = isset( $err['message'] ) ? $err['message'] : __( 'There was an error processing this charge', 'edds' );

	} catch ( Stripe_ApiConnectionError $e ) {

		$body = $e->getJsonBody();
		$err  = $body['error'];

		$error_message = isset( $err['message'] ) ? $err['message'] : __( 'There was an error processing this charge', 'edds' );

	} catch ( Stripe_InvalidRequestError $e ) {

		$body = $e->getJsonBody();
		$err  = $body['error'];

		$error_message = isset( $err['message'] ) ? $err['message'] : __( 'There was an error processing this charge', 'edds' );

	} catch ( Stripe_ApiError $e ) {

		$body = $e->getJsonBody();
		$err  = $body['error'];

		$error_message = isset( $err['message'] ) ? $err['message'] : __( 'There was an error processing this charge', 'edds' );
	} catch ( Stripe_AuthenticationError $e ) {

		$body = $e->getJsonBody();
		$err  = $body['error'];

		$error_message = isset( $err['message'] ) ? $err['message'] : __( 'There was an error processing this charge', 'edds' );

	} catch ( Stripe_Error $e ) {

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

		if ( ! class_exists( 'Stripe' ) ) {
			require_once EDDS_PLUGIN_DIR . '/Stripe-old/Stripe.php';
		}

		$secret_key = edd_is_test_mode() ? trim( $edd_options['test_secret_key'] ) : trim( $edd_options['live_secret_key'] );

		Stripe::setApiKey( $secret_key );

		// retrieve the request's body and parse it as JSON
		$body = @file_get_contents( 'php://input' );
		$event_json = json_decode( $body );

		// for extra security, retrieve from the Stripe API
		$event_id = $event_json->id;

		if ( isset( $event_json->id ) ) {

			status_header( 200 );

			$event = Stripe_Event::retrieve( $event_json->id );

			$invoice = $event->data->object;
			switch ( $event->type ) :

				case 'invoice.payment_succeeded' :

					if ( ! class_exists( 'EDD_Recurring' ) ) {
						break;
					}

					// Process a subscription payment

					// retrieve the customer who made this payment (only for subscriptions)
					$user_id = EDD_Recurring_Customer::get_user_id_by_customer_id( $invoice->customer );

					// retrieve the customer ID from WP database
					$customer_id = EDD_Recurring_Customer::get_customer_id( $user_id );

					// check to confirm this is a stripe subscriber
					if ( $user_id && $customer_id ) {

						$cu = Stripe_Customer::retrieve( $customer_id );

						// Get all subscriptions of this customer
						$plans            = $cu->subscriptions->data;
						$subscriptions    = wp_list_pluck( $plans, 'plan' );
						$subscription_ids = ! empty( $subscriptions ) ? wp_list_pluck( $subscriptions, 'id' ) : array();
						$plan_data 		  = $invoice->lines->data;
						$invoice_plan	  = wp_list_pluck( $plan_data, 'plan' );
						$invoice_plan 	  = array_pop( $invoice_plan );
						$plan_id		  = $invoice_plan->id;

						// Make sure this charge is for the user's subscription
						if ( ! empty( $subscription_ids ) && ! in_array( $plan_id, $subscription_ids ) ) {
							die('-3');
						}

						// Retrieve the original payment details
						$parent_payment_id = EDD_Recurring_Customer::get_customer_payment_id( $user_id );

						if( false !== get_transient( '_edd_recurring_payment_' . $parent_payment_id ) ) {
							// Store the charge for the payment.
							$charge = isset( $invoice->charge ) ? $invoice->charge : false;
							if ( $charge ) {
								edd_insert_payment_note( $parent_payment_id, 'Stripe Charge ID: ' . $charge );

								if ( function_exists( 'edd_set_payment_transaction_id' ) ) {
									edd_set_payment_transaction_id( $parent_payment_id, $charge );
								}
							}
							die('2'); // This is the initial payment
						}

						try {

							// Store the payment
							EDD_Recurring()->record_subscription_payment( $parent_payment_id, $invoice->total / 100, $invoice->charge );

							// Set the customer's status to active
							EDD_Recurring_Customer::set_customer_status( $user_id, 'active' );

							// Calculate the customer's new expiration date
							$new_expiration = EDD_Recurring_Customer::calc_user_expiration( $user_id, $parent_payment_id );

							// Set the customer's new expiration date
							EDD_Recurring_Customer::set_customer_expiration( $user_id, $new_expiration );

						} catch ( Exception $e ) {
							die('3'); // Something not as expected
						}

					} else {
						die('-4'); // The user ID or customer ID could not be retrieved.
					}

				break;

				case 'customer.subscription.deleted' :

					if ( ! class_exists( 'EDD_Recurring' ) ) {
						break;
					}

					// Process a cancellation

					// retrieve the customer who made this payment (only for subscriptions)
					$user_id = apply_filters( 'edd_recurring_subscription_deleted_user_id', EDD_Recurring_Customer::get_user_id_by_customer_id( $invoice->customer ), $invoice->customer );

					$parent_payment_id = apply_filters( 'edd_recurring_subscription_deleted_payment_id', EDD_Recurring_Customer::get_customer_payment_id( $user_id ), $user_id );

					// Set the customer's status to active
					EDD_Recurring_Customer::set_customer_status( $user_id, 'cancelled' );

					edd_update_payment_status( $parent_payment_id, 'cancelled' );

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
		),/*
		array(
			'id'   => 'stripe_bitcoin',
			'name'  => __( 'Accept Bitcoin in Stripe Checkout', 'edds' ),
			'desc'  => __( 'Check this box if you would like to permit your customers to pay with bitcoin. Supported by Buy Now buttons only.', 'edds' ),
			'type'  => 'checkbox'
		),*/
		array(
			'id'   => 'stripe_alipay',
			'name'  => __( 'Accept Alipay in Stripe Checkout', 'edds' ),
			'desc'  => __( 'Check this box if you would like to permit your customers to pay with an Alipay account. Supported by Buy Now buttons only.', 'edds' ),
			'type'  => 'checkbox'
		),
		array(
			'id'   => 'stripe_preapprove_only',
			'name'  => __( 'Preapprove Only?', 'edds' ),
			'desc'  => __( 'Check this if you would like to preapprove payments but not charge until a later date.', 'edds' ),
			'type'  => 'checkbox'
		)
	);

	return array_merge( $settings, $stripe_settings );
}
add_filter( 'edd_settings_gateways', 'edds_add_settings' );


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

		if ( ( edd_is_checkout() || $override ) && edd_is_gateway_active( 'stripe' ) ) {

			wp_enqueue_script( 'stripe-js', 'https://js.stripe.com/v2/', array( 'jquery' ) );
			wp_enqueue_script( 'edd-stripe-js', EDDSTRIPE_PLUGIN_URL . 'edd-stripe.js', array( 'jquery', 'stripe-js' ), EDD_STRIPE_VERSION );

			$stripe_vars = array(
				'publishable_key' => trim( $publishable_key ),
				'is_ajaxed'       => edd_is_ajax_enabled() ? 'true' : 'false',
				'no_key_error'    => __( 'Stripe publishable key missing. Please enter your publishable key in Settings.', 'edds' )
			);

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

					$(this).parent().parent().append( '<input type="checkbox" id="edd_refund_in_stripe" name="edd_refund_in_stripe" value="1"/>' );
					$(this).parent().parent().append( '<label for="edd_refund_in_stripe">Refund Charge in Stripe</label>' );

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

	$charge_id = false;

	$notes = edd_get_payment_notes( $payment_id );
	foreach ( $notes as $note ) {
		if ( preg_match( '/^Stripe Charge ID: ([^\s]+)/', $note->comment_content, $match ) ) {
			$charge_id = $match[1];
			break;
		}
	}

	// Bail if no charge ID was found
	if( empty( $charge_id ) ) {
		return;
	}

	if ( ! class_exists( 'Stripe' ) ) {
		require_once EDDS_PLUGIN_DIR . '/Stripe-old/Stripe.php';
	}

	$secret_key = edd_is_test_mode() ? trim( $edd_options['test_secret_key'] ) : trim( $edd_options['live_secret_key'] );

	Stripe::setApiKey( $secret_key );

	$ch = Stripe_Charge::retrieve( $charge_id );


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

	if( ! empty( $args['direct'] ) ) {

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
	if (  doing_action( 'wp_head' ) ) {
		return;
	}

	if ( ! empty( $printed_stripe_purchase_link[ $download_id ] ) ) {
		return;
	}

	if( ! isset( $args['stripe-checkout'] ) ) {
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
					amount = <?php echo edd_get_download_price( $download_id ) * 100; ?>
				}

				StripeCheckout.configure({
					key: '<?php echo $publishable_key; ?>',
					locale: 'auto',
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
					description: '<?php echo esc_js( $download->post_title ); ?>',
					alipay: <?php echo edd_get_option( 'stripe_alipay' ) ? 'true' : 'false'; ?>,
					amount: amount,
					zipCode: true,
					email: '<?php echo esc_js( $email ); ?>',
					currency: '<?php echo edd_get_currency(); ?>'
				})

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
