<?php
/**
 * PayPal Websites Payments Pro Recurring Gateway
 *
 * Relevant Links (PayPal makes it tough to find them)
 *
 * CreateRecurringPaymentsProfile API Operation (NVP) - https://developer.paypal.com/docs/classic/api/merchant/CreateRecurringPaymentsProfile_API_Operation_NVP/
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $edd_recurring_paypal_wp_pro;

class EDD_Recurring_PayPal_Website_Payments_Pro extends EDD_Recurring_Gateway {

	private   $api_endpoint;
	protected $username;
	protected $password;
	protected $signature;

	/**
	 * Get things rollin'
	 *
	 * @since 2.4
	 */
	public function init() {

		$this->id = 'paypalpro';
		$this->friendly_name = __( 'PayPal Pro', 'edd-recurring' );

		if ( edd_is_test_mode() ) {
			$this->api_endpoint = 'https://api-3t.sandbox.paypal.com/nvp';
		} else {
			$this->api_endpoint = 'https://api-3t.paypal.com/nvp';
		}

		$creds = edd_recurring_get_paypal_api_credentials();

		$this->username  = $creds['username'];
		$this->password  = $creds['password'];
		$this->signature = $creds['signature'];

		add_action( 'edd_pre_refund_payment', array( $this, 'process_refund' ) );

	}

	/**
	 * Validate Fields
	 *
	 * @description: Validate additional fields during checkout submission
	 *
	 * @since      2.4
	 *
	 * @param $data
	 * @param $posted
	 */
	public function validate_fields( $data, $posted ) {

		if ( empty( $this->username ) || empty( $this->password ) || empty( $this->signature ) ) {
			edd_set_error( 'edd_recurring_no_paypal_api', __( 'It appears that you have not configured PayPal API access. Please configure it in EDD &rarr; Settings', 'edd_recurring' ) );
		}

		if ( count( edd_get_cart_contents() ) > 1 && ! $this->can_purchase_multiple_subs() ) {
			edd_set_error( 'subscription_invalid', __( 'Only one subscription may be purchased through this payment method per checkout.', 'edd-recurring' ) );
		}

	}


	/**
	 * Create payment profiles
	 *
	 * @since 2.4
	 */
	public function create_payment_profiles() {

		$free_trial   = false;
		$item_titles  = array();
		$item_total   = 0;
		$tax          = 0;

		foreach( $this->subscriptions as $subscription ) {

			$item_titles[] = html_entity_decode( $subscription['name'], ENT_COMPAT, 'UTF-8' );
			$item_total   += $subscription['initial_amount'] - $subscription['initial_tax'];
			$tax          += $subscription['initial_tax'];

			if( ! empty( $subscription['has_trial'] ) ) {
				$free_trial = true;
			}

		}

		if( ! $free_trial ) {

			$description = implode( ', ', $item_titles );

			$payment_args = array(
				'USER'               => $this->username,
				'PWD'                => $this->password,
				'SIGNATURE'          => $this->signature,
				'VERSION'            => '124',
				// DoDirectPayment Request Fields
				'METHOD'             => 'DoDirectPayment',
				'PAYMENTACTION'      => 'Sale',
				'IPADDRESS'          => edd_get_ip(),
				'SOFTDESCRIPTOR'     => $description,
				'SOFTDESCRIPTORCITY' => get_bloginfo( 'admin_email' ),
				// Credit Card Details Fields
				'CREDITCARDTYPE'     => '',
				'ACCT'               => sanitize_text_field( $this->purchase_data['card_info']['card_number'] ),
				'EXPDATE'            => sanitize_text_field( $this->purchase_data['card_info']['card_exp_month'] . $this->purchase_data['card_info']['card_exp_year'] ), // needs to be in the format 062019
				'CVV2'               => sanitize_text_field( $this->purchase_data['card_info']['card_cvc'] ),
				// Payer Information Fields
				'EMAIL'              => sanitize_email( $this->purchase_data['user_info']['email'] ),
				'FIRSTNAME'          => sanitize_text_field( $this->purchase_data['user_info']['first_name'] ),
				'LASTNAME'           => sanitize_text_field( $this->purchase_data['user_info']['last_name'] ),
				// Address Fields
				'STREET'             => sanitize_text_field( $this->purchase_data['card_info']['card_address'] ),
				'STREET2'            => sanitize_text_field( $this->purchase_data['card_info']['card_address_2'] ),
				'CITY'               => sanitize_text_field( $this->purchase_data['card_info']['card_city'] ),
				'STATE'              => sanitize_text_field( $this->purchase_data['card_info']['card_state'] ),
				'COUNTRYCODE'        => sanitize_text_field( $this->purchase_data['card_info']['card_country'] ),
				'ZIP'                => sanitize_text_field( $this->purchase_data['card_info']['card_zip'] ),
				// Payment Details Fields
				'AMT'                => round( $this->purchase_data['price'], 2 ),
				'CURRENCYCODE'       => strtoupper( edd_get_currency() ),
				'ITEMAMT'            => round( $item_total, 2 ),
				'SHIPPINGAMT'        => 0,
				'TAXAMT'             => round( $tax, 2 ),
				'BUTTONSOURCE'       => 'EasyDigitalDownloads_SP',
				'NOTIFYURL'          => add_query_arg( 'edd-listener', 'paypalpro', home_url( 'index.php' ) ),
			);

			$payment_request = wp_remote_post( $this->api_endpoint, array(
				'timeout'     => 60,
				'sslverify'   => false,
				'body'        => $payment_args,
				'httpversion' => '1.1',
			) );

			$payment_body = wp_remote_retrieve_body( $payment_request );
			$code         = wp_remote_retrieve_response_code( $payment_request );
			$message      = wp_remote_retrieve_response_message( $payment_request );

		}

		if ( ! $free_trial && is_wp_error( $payment_request ) ) {

			$error = '<p>' . __( 'An unidentified error occurred.', 'edd-recurring' ) . '</p>';
			$error .= '<p>' . $payment_request->get_error_message() . '</p>';

			wp_die( $error, __( 'Error', 'edd-recurring' ), array( 'response' => '401' ) );

		} elseif ( $free_trial || ( 200 == $code && 'OK' == $message ) ) {

			if( ! $free_trial && is_string( $payment_body ) ) {
				wp_parse_str( $payment_body, $payment_body );
			}

			if( ! $free_trial && 'failure' === strtolower( $payment_body['ACK'] ) ) {

				$payment_args['ACCT'] = str_pad( substr( $payment_args['ACCT'], -4 ), strlen( $payment_args['ACCT'] ), '*', STR_PAD_LEFT );
				$payment_args['CVV2'] = preg_replace( '/[0-9]+/', '*', $payment_args['CVV2'] );

				edd_record_gateway_error( __( 'PayPal Pro Error', 'edd-recurring' ), sprintf( __( 'Error processing payment: %s', 'edd-recurring' ), json_encode( $payment_body ) . json_encode( $payment_args ) ) );

				edd_set_error( $payment_body['L_ERRORCODE0'], $payment_body['L_LONGMESSAGE0'] );

				//Send back to checkout
				edd_send_back_to_checkout( '?payment-mode=' . $this->id );

			} else {

				foreach ( $this->subscriptions as $key => $subscription ) {

					switch( $subscription['period'] ) {

						case 'quarter' :

							$frequency = 3;
							$period    = 'Month';
							break;

						case 'semi-year' :

							$frequency = 6;
							$period    = 'Month';
							break;

						default :

							$frequency = 1;
							$period    = ucwords( $subscription['period'] );
							break;
					}

					if( $free_trial ) {

						// Set start date to the end of the free trial
						$profile_start = date( 'Y-m-d\Tg:i:s', strtotime( '+' . $subscription['trial_quantity'] . ' ' . ucwords( $subscription['trial_unit'] ), current_time( 'timestamp' ) ) );

					} else {

						// Set start date to the first renewal date. Initial period is covered by the initial payment processed above
						$profile_start = date( 'Y-m-d\Tg:i:s', strtotime( '+' . $frequency . ' ' . $period, current_time( 'timestamp' ) ) );

					}

					$args = array(
						'USER'                => $this->username,
						'PWD'                 => $this->password,
						'SIGNATURE'           => $this->signature,
						'VERSION'             => '124',
						// Credit Card Details Fields
						'CREDITCARDTYPE'      => '',
						'ACCT'                => sanitize_text_field( $this->purchase_data['card_info']['card_number'] ),
						'EXPDATE'             => sanitize_text_field( $this->purchase_data['card_info']['card_exp_month'] . $this->purchase_data['card_info']['card_exp_year'] ), // needs to be in the format 062019
						'CVV2'                => sanitize_text_field( $this->purchase_data['card_info']['card_cvc'] ),
						'ZIP'                 => sanitize_text_field( $this->purchase_data['card_info']['card_zip'] ),
						'METHOD'              => 'CreateRecurringPaymentsProfile',
						'PROFILESTARTDATE'    => $profile_start,
						'BILLINGPERIOD'       => $period,
						'BILLINGFREQUENCY'    => $frequency,
						'AMT'                 => round( $subscription['recurring_amount'], 2 ),
						'TOTALBILLINGCYCLES'  => $subscription['bill_times'] > 1 ? $subscription['bill_times'] - 1 : $subscription['bill_times'],
						'CURRENCYCODE'        => strtoupper( edd_get_currency() ),
						'FAILEDINITAMTACTION' => 'CancelOnFailure',
						'L_BILLINGTYPE0'      => 'RecurringPayments',
						'DESC'                => wp_specialchars_decode( get_the_title( $subscription['id'] ), ENT_QUOTES ),
						'EMAIL'               => sanitize_email( $this->purchase_data['user_info']['email'] ),
						'FIRSTNAME'           => sanitize_text_field( $this->purchase_data['user_info']['first_name'] ),
						'LASTNAME'            => sanitize_text_field( $this->purchase_data['user_info']['last_name'] ),
					);

					$args = apply_filters( 'edd_recurring_create_subscription_args', $args, $this->purchase_data['downloads'], $this->id, $subscription['id'], $subscription['price_id'] );

					$request = wp_remote_post( $this->api_endpoint, array(
						'timeout'     => 45,
						'sslverify'   => false,
						'body'        => $args,
						'httpversion' => '1.1',
					) );

					$body     = wp_remote_retrieve_body( $request );
					$code     = wp_remote_retrieve_response_code( $request );
					$message  = wp_remote_retrieve_response_message( $request );

					if ( is_wp_error( $request ) ) {

						$error = '<p>' . __( 'An unidentified error occurred.', 'edd_recurring' ) . '</p>';
						$error .= '<p>' . $request->get_error_message() . '</p>';
						wp_die( $error, __( 'Error', 'edd_recurring' ), array( 'response' => '401' ) );

					} elseif ( 200 == $code && 'OK' == $message ) {

						if( is_string( $body ) ) {
							wp_parse_str( $body, $body );
						}

						if ( 'failure' === strtolower( $body['ACK'] ) ) {

							$error = '<p>' . __( 'PayPal subscription creation failed.', 'edd_recurring' ) . '</p>';
							$error .= '<p>' . __( 'Error message:', 'edd_recurring' ) . ' ' . $body['L_LONGMESSAGE0'] . '</p>';
							$error .= '<p>' . __( 'Error code:', 'edd_recurring' ) . ' ' . $body['L_ERRORCODE0'] . '</p>';
							edd_record_gateway_error( $error, __( 'Error', 'edd_recurring' ), array( 'response' => '401' ) );
							edd_set_error( $body['L_ERRORCODE0'], $body['L_LONGMESSAGE0'] );
							// get rid of the pending purchase
							//Send back to checkout
							edd_send_back_to_checkout( '?payment-mode=' . $this->id );

						} else {

							// Set subscription profile ID for this subscription
							$this->subscriptions[ $key ]['profile_id'] = $body['PROFILEID'];

							if( $free_trial ) {

								$this->subscriptions[ $key ]['status'] = 'trialling';

							} else if ( 'PendingProfile' === $body['PROFILESTATUS'] && ! edd_is_test_mode() ) {

								$this->subscriptions[ $key ]['status'] = 'pending';

							}

							$txn_id = ! empty( $payment_body['TRANSACTIONID'] ) ? $payment_body['TRANSACTIONID'] : '';

							$this->subscriptions[ $key ]['transaction_id'] = $txn_id;
						}

					} else {

						// Catch any other errors

						edd_set_error( 'edd_recurring_paypal_pro_generic_error', __( 'Something has gone wrong, please try again', 'edd_recurring' ) );

						//Send back to checkout
						edd_send_back_to_checkout( '?payment-mode=' . $this->id );
					}
				} // endforeach

				if( ! empty( $payment_body['TRANSACTIONID'] ) ) {
					$this->payment_transaction_id = sanitize_text_field( $payment_body['TRANSACTIONID'] );
				}

			}

		}

	}

	/**
	 * Process the payment completion
	 *
	 * @since  2.4.3
	 * @return void
	 */
	public function complete_signup() {

		if( ! empty( $this->payment_transaction_id ) ) {
			$payment                 = edd_get_payment( $this->payment_id );
			$payment->transaction_id = $this->payment_transaction_id;
			$payment->save();
		}


		// Look for any last errors
		$errors = edd_get_errors();

		// We shouldn't usually get here, but just in case a new error was recorded, we need to check for it
		if ( empty( $errors ) ) {
			wp_redirect( edd_get_success_page_uri() ); exit;
		}
	}

	/**
	 * Process webhooks
	 *
	 * @since 2.4
	 */
	public function process_webhooks() {

		if ( empty( $_GET['edd-listener'] ) || ( $this->id !== $_GET['edd-listener'] && 'eppe' !== $_GET['edd-listener'] ) ) {
			return;
		}

		nocache_headers();

		$verified = false;

		// Set initial post data to empty string
		$post_data = '';

		// Fallback just in case post_max_size is lower than needed
		if ( ini_get( 'allow_url_fopen' ) ) {
			$post_data = file_get_contents( 'php://input' );
		} else {
			// If allow_url_fopen is not enabled, then make sure that post_max_size is large enough
			ini_set( 'post_max_size', '12M' );
		}

		// Start the encoded data collection with notification command
		$encoded_data = 'cmd=_notify-validate';

		// Get current arg separator
		$arg_separator = edd_get_php_arg_separator_output();

		// Verify there is a post_data
		if ( $post_data || strlen( $post_data ) > 0 ) {

			// Append the data
			$encoded_data .= $arg_separator.$post_data;

		} else {

			// Check if POST is empty
			if ( empty( $_POST ) ) {

				// Nothing to do
				return;

			} else {

				// Loop through each POST
				foreach ( $_POST as $key => $value ) {

					// Encode the value and append the data
					$encoded_data .= $arg_separator."$key=" . urlencode( $value );

				}

			}

		}

		// Convert collected post data to an array
		parse_str( $encoded_data, $encoded_data_array );

		if ( ! edd_get_option( 'disable_paypal_verification' ) && ! edd_is_test_mode() ) {

			// Validate the IPN
			$remote_post_vars      = array(
				'method'           => 'POST',
				'timeout'          => 45,
				'redirection'      => 5,
				'httpversion'      => '1.1',
				'blocking'         => true,
				'headers'          => array(
					'host'         => 'www.paypal.com',
					'connection'   => 'close',
					'content-type' => 'application/x-www-form-urlencoded',
					'post'         => '/cgi-bin/webscr HTTP/1.1',

				),
				'body'             => $encoded_data_array
			);

			// Get response
			$api_response = wp_remote_post( edd_get_paypal_redirect(), $remote_post_vars );
			$body         = wp_remote_retrieve_body( $api_response );

			if ( is_wp_error( $api_response ) ) {
				edd_record_gateway_error( __( 'IPN Error', 'edd-recurring' ), sprintf( __( 'Invalid PayPal Pro IPN verification response. IPN data: %s', 'edd-recurring' ), json_encode( $api_response ) ) );
				status_header( 401 );
				return; // Something went wrong
			}

			if ( $body !== 'VERIFIED' ) {
				status_header( 401 );
				edd_record_gateway_error( __( 'IPN Error', 'edd-recurring' ), sprintf( __( 'Invalid PayPal Pro IPN verification response. IPN data: %s', 'edd-recurring' ), json_encode( $api_response ) ) );
				return; // Response not okay
			}

			// We've verified that the IPN Check passed, we can proceed with processing the IPN data sent to us.
			$verified = true;

		}

		/**
		 * The processIpn() method returned true if the IPN was "VERIFIED" and false if it was "INVALID".
		 */
		if ( ( $verified || edd_get_option( 'disable_paypal_verification' ) ) || isset( $_POST['verification_override'] ) || edd_is_test_mode() ) {

			status_header( 200 );

			$posted          = apply_filters( 'edd_recurring_ipn_post', $_POST ); // allow $_POST to be modified

			if( ! isset( $posted['recurring_payment_id'] ) ) {
				return; // This is not related to Recurring Payments
			}

			$amount        = isset( $posted['mc_gross'] ) ? number_format( (float) $posted['mc_gross'], 2 ) : 0.00;
			$currency_code = isset( $posted['mc_currency'] ) ? $posted['mc_currency'] : false;
			$subscription  = new EDD_Subscription( $posted['recurring_payment_id'], true );

			$parent_payment = edd_get_payment( $subscription->parent_payment_id );
			if ( $parent_payment->gateway !== $this->id ) {
				return;
			}

			if( empty( $subscription->id ) || $subscription->id < 1 )  {
				die( 'No subscription found' );
			}

			// Subscriptions
			switch ( $posted['txn_type'] ) :

				case "recurring_payment_profile_created" :

					if( 'trialling' !== $subscription->status ) {
						$subscription->update( array( 'status' => 'active' ) );
					}

					if( ! empty( $posted['initial_payment_txn_id'] ) ) {
						edd_set_payment_transaction_id( $subscription->parent_payment_id, $posted['initial_payment_txn_id'] );
					}

					die( 'subscription marked as active' );

					break;

				case "recurring_payment" :

					$sub_currency = edd_get_payment_currency_code( $subscription->parent_payment_id );

					// verify details
					if( ! empty( $sub_currency ) && strtolower( $currency_code ) !== strtolower( $sub_currency ) ) {

						// the currency code is invalid
						// @TODO: Does this need a parent_id for better error organization?
						edd_record_gateway_error( __( 'Invalid Currency Code', 'edd-recurring' ), sprintf( __( 'The currency code in an IPN request did not match the site currency code. Payment data: %s', 'edd-recurring' ), json_encode( $payment_data ) ) );

						die( 'invalid currency code' );

					}

					// Bail if this is the very first payment
					if( date( 'Y-n-d', strtotime( $subscription->created ) ) == date( 'Y-n-d', strtotime( $posted['payment_date'] ) ) ) {

						edd_set_payment_transaction_id( $subscription->parent_payment_id, $posted['txn_id'] );

						return;
					}

					// when a user makes a recurring payment
					$payment_id = $subscription->add_payment( array(
						'amount'         => $amount,
						'transaction_id' => $posted['txn_id']
					) );

					if ( ! empty( $payment_id ) ) {
						$subscription->renew( $payment_id );
					}

					die( 'Subscription payment successful' );

					break;

				case "recurring_payment_profile_cancel" :
				case "recurring_payment_suspended" :
				case "recurring_payment_suspended_due_to_max_failed_payment" :

					$subscription->cancel();

					die( 'Subscription cancelled' );

					break;

				case "recurring_payment_failed" :

					$subscription->failing();
					do_action( 'edd_recurring_payment_failed', $subscription );

					break;

				case "recurring_payment_expired" :

					$subscription->complete();

					die( 'Subscription completed' );
					break;

				default :

					die( 'Paypal Pro Endpoint' );
					break;

			endswitch;

		} else {

			status_header( 400 );
			die( 'invalid IPN' );

		}

	}

	/**
	 * Refund charges when refunding via View Order Details
	 *
	 * @access      public
	 * @since       2.4.11
	 * @return      void
	 */
	public function process_refund( EDD_Payment $payment ) {

		if( empty( $_POST['edd-paypal-refund'] ) ) {
			return;
		}

		$statuses = array( 'edd_subscription', 'publish', 'revoked' );

		if ( ! in_array( $payment->old_status, $statuses ) ) {
			return;
		}

		if ( 'paypalpro' !== $payment->gateway ) {
			return;
		}

		switch( $payment->old_status ) {

			case 'edd_subscription' :

				// Possibly add subscription cancellation here too

				break;

			case 'publish' :
			case 'revoked' :

				// Cancel all associated subscriptions

				$db   = new EDD_Subscriptions_DB;
				$subs = $db->get_subscriptions( array( 'parent_payment_id' => $payment->ID, 'number' => 100 ) );

				if( empty( $subs ) ) {

					return;

				}

				$success = false;

				$args = array(
					'USER'          => $this->username,
					'PWD'           => $this->password,
					'SIGNATURE'     => $this->signature,
					'VERSION'       => '124',
					'METHOD'        => 'RefundTransaction',
					'TRANSACTIONID' => $payment->transaction_id,
					'REFUNDTYPE'    => 'Full'
				);

				$error_msg = '';
				$request   = wp_remote_post( $this->api_endpoint, array( 'body' => $args, 'timeout' => 15, 'httpversion' => '1.1' ) );
				$body      = wp_remote_retrieve_body( $request );
				$code      = wp_remote_retrieve_response_code( $request );
				$message   = wp_remote_retrieve_response_message( $request );

				if ( is_wp_error( $request ) ) {

					$success   = false;
					$error_msg = $request->get_error_message();

				} else {

					if( is_string( $body ) ) {
						wp_parse_str( $body, $body );
					}

					if( empty( $code ) || 200 !== (int) $code ) {
						$success = false;
					}

					if( empty( $message ) || 'OK' !== $message ) {
						$success = false;
					}

					if( isset( $body['ACK'] ) && 'success' === strtolower( $body['ACK'] ) ) {
						$success = true;
					} else {
						$success = false;
						if( isset( $body['L_LONGMESSAGE0'] ) ) {
							$error_msg = $body['L_LONGMESSAGE0'];
							$payment->add_note( sprintf( __( 'PayPal Pro refund failed: %s', 'edd-recurring' ), $error_msg ) );
						}
					}

				}

				if( $success ) {

					// Prevents the PayPal Pro one-time gateway from trying to process the refundl
					$payment->update_meta( '_edd_paypalpro_refunded', true );
					$payment->add_note( sprintf( __( 'PayPal Pro Refund Transaction ID: %s', 'edd-recurring' ), $body['REFUNDTRANSACTIONID'] ) );

				}

				// End publish/revoked case
				break;

		} // End switch

	}

	/**
	 * Determines if the subscription can be cancelled
	 *
	 * @access      public
	 * @since       2.4
	 * @return      bool
	 */
	public function can_cancel( $ret, $subscription ) {
		if( $subscription->gateway === 'paypalpro' && ! empty( $subscription->profile_id )  && in_array( $subscription->status, $this->get_cancellable_statuses() ) ) {
			return true;
		}
		return $ret;
	}

	/**
	 * Cancels a subscription
	 *
	 * @access      public
	 * @since       2.4
	 * @return      string
	 */
	public function cancel( $subscription, $valid ) {

		if( empty( $valid ) ) {
			return false;
		}

		$customer = new EDD_Recurring_Subscriber( $subscription->customer_id );

		$args = array(
			'USER'      => $this->username,
			'PWD'       => $this->password,
			'SIGNATURE' => $this->signature,
			'VERSION'   => '124',
			'METHOD'    => 'ManageRecurringPaymentsProfileStatus',
			'PROFILEID' => $subscription->profile_id,
			'ACTION'    => 'Cancel'
		);

		$error_msg = '';
		$request   = wp_remote_post( $this->api_endpoint, array( 'body' => $args, 'timeout' => 30, 'httpversion' => '1.1', ) );
		$body      = wp_remote_retrieve_body( $request );
		$code      = wp_remote_retrieve_response_code( $request );
		$message   = wp_remote_retrieve_response_message( $request );

		if ( is_wp_error( $request ) ) {

			$success   = false;
			$error_msg = $request->get_error_message();

		} else {

			if( is_string( $body ) ) {
				wp_parse_str( $body, $body );
			}

			if( empty( $code ) || 200 !== (int) $code ) {
				$success = false;
			}

			if( empty( $message ) || 'OK' !== $message ) {
				$success = false;
			}

			if( isset( $body['ACK'] ) && 'success' === strtolower( $body['ACK'] ) ) {
				$success = true;
			} else {
				$success = false;
				if( isset( $body['L_LONGMESSAGE0'] ) ) {
					$error_msg = $body['L_LONGMESSAGE0'];
				}
			}

			/*
			 * Sometimes a subscription has already been cancelled in PayPal and PayPal returns an error indicating it's not active
			 * Let's catch those cases and consider the cancellation successful
			 */
			$cancelled_codes = array( 11556, 11557, 11531 );
			if( in_array( $body['L_ERRORCODE0'], $cancelled_codes ) ) {
				$success = true;
			}

		}

		if( empty( $success ) ) {
			wp_die( sprintf( __( 'There was a problem cancelling the subscription, please contact customer support. Error: %s', 'edd-recurring' ), $error_msg ), array( 'response' => 400 ) );
		}

		return true;

	}

	/**
	 * Determines if the subscription can be retried when failing
	 *
	 * @access      public
	 * @since       2.8
	 * @return      bool
	 */
	public function can_retry( $ret, $subscription ) {
		if( $subscription->gateway === 'paypalpro' && ! empty( $subscription->profile_id ) && 'failing' === $subscription->status ) {
			return true;
		}
		return $ret;
	}

	/**
	 * Retries a failing subscription
	 *
	 * This method is connected to a filter instead of an action so we can return a nice error message.
	 *
	 * @access      public
	 * @since       2.8
	 * @return      bool|WP_Error
	 */
	public function retry( $result, $subscription ) {

		if( ! $this->can_retry( false, $subscription ) ) {
			return $result;
		}

		$args = array(
			'USER'      => $this->username,
			'PWD'       => $this->password,
			'SIGNATURE' => $this->signature,
			'VERSION'   => '124',
			'METHOD'    => 'BillOutstandingAmount',
			'PROFILEID' => $subscription->profile_id,
			'NOTE'      => __( 'Retry initiated from EDD Recurring', 'edd-recurring' )
		);

		$error_msg = '';
		$request   = wp_remote_post( $this->api_endpoint, array( 'body' => $args, 'timeout' => 30, 'httpversion' => '1.1' ) );
		$body      = wp_remote_retrieve_body( $request );
		$code      = wp_remote_retrieve_response_code( $request );
		$message   = wp_remote_retrieve_response_message( $request );


		if ( is_wp_error( $request ) ) {

			$success   = false;
			$error_msg = $request->get_error_message();

		} else {

			if( is_string( $body ) ) {
				wp_parse_str( $body, $body );
			}

			if( empty( $code ) || 200 !== (int) $code ) {
				$success = false;
			}

			if( empty( $message ) || 'OK' !== $message ) {
				$success = false;
			}

			if( isset( $body['ACK'] ) && 'success' === strtolower( $body['ACK'] ) ) {
				$success = true;
			} else {
				$success = false;
				if( isset( $body['L_LONGMESSAGE0'] ) ) {
					$error_msg = $body['L_LONGMESSAGE0'];
				}
			}

		}

		if( empty( $success ) ) {
			$result = new WP_Error( 'edd_recurring_paypalpro_error', $error_msg );
		} else {
			$result = true;
		}

		return $result;
	}

	/**
	 * Determines if PayPal Pro allows multiple subscriptions to be purchased at once.
	 *
	 * PayPal Pro has deprecated this entirely as of November 1, 2019.
	 *
	 * @see https://github.com/easydigitaldownloads/edd-recurring/issues/1231
	 * @see https://github.com/easydigitaldownloads/edd-recurring/issues/1092
	 * @since 2.9.3
	 * @return bool
	 */
	public function can_purchase_multiple_subs() {
		return false;
	}

	/**
	 * Get the expiration date with PayPal
	 *
	 * @since  2.6.6
	 * @param  object $subscription The subscription object
	 * @return string Expiration date or WP_Error if something went wrong
	 */
	public function get_expiration( $subscription ) {

		$details = $this->get_subscription_details( $subscription );

		if( ! empty( $details['error'] ) ) {
			return $details['error'];
		}

		return date( 'Y-n-d H:i:s', strtotime( $details['expiration'], current_time( 'timestamp' ) ) );
	}

	/**
	 * Retrieves subscription details (status and expiration)
	 *
	 * @access      public
	 * @since       2.4
	 * @return      array
	 */
	public function get_subscription_details( EDD_Subscription $subscription ) {

		$ret = array(
			'status'     => '',
			'expiration' => '',
			'error'      => '',
		);

		if( ! $subscription->id > 0 ) {

			$ret['error'] = new WP_Error( 'invalid_subscription', __( 'Invalid subscription object supplied', 'edd-recurring' ) );

		} else {

			if( ! empty( $subscription->profile_id ) ) {

				$args = array(
					'USER'      => $this->username,
					'PWD'       => $this->password,
					'SIGNATURE' => $this->signature,
					'VERSION'   => '124',
					'METHOD'    => 'GetRecurringPaymentsProfileDetails',
					'PROFILEID' => $subscription->profile_id,
				);

				$error_msg = '';
				$request   = wp_remote_post( $this->api_endpoint, array( 'body' => $args, 'timeout' => 30, 'httpversion' => '1.1' ) );
				$body      = wp_remote_retrieve_body( $request );
				$code      = wp_remote_retrieve_response_code( $request );
				$message   = wp_remote_retrieve_response_message( $request );

				if ( is_wp_error( $request ) ) {

					$ret['error'] = $request;

				} else {

					if( is_string( $body ) ) {
						wp_parse_str( $body, $body );
					}

					if( empty( $code ) || 200 !== (int) $code ) {
						$ret['error'] = new WP_Error( 'paypal_api_error', sprintf( __( 'Non 200 response code. Response code was: %s', 'edd-recurring' ), $code ) );
					}

					if( empty( $message ) || 'OK' !== $message ) {
						$ret['error'] = new WP_Error( 'paypal_api_error', sprintf( __( 'Response message not okay. Response message was: %s', 'edd-recurring' ), $message ) );
					}

					if( isset( $body['ACK'] ) && 'failure' === strtolower( $body['ACK'] ) ) {
						$ret['error'] = new WP_Error( 'paypal_api_error', $body['L_ERRORCODE0'] . ': '. $body['L_LONGMESSAGE0'] );
					}

					if( empty( $ret['error'] ) ) {

						// All good, let's grab the details of the subscription
						$ret['status']     = strtolower( $body['STATUS'] );
						$ret['expiration'] = date( 'Y-n-d H:i:s', strtotime( $body['NEXTBILLINGDATE'] ) );

					}

				}

			} else {

				$ret['error'] = new WP_Error( 'missing_profile_id', __( 'No profile_id set on subscription object', 'edd-recurring' ) );

			}

		}

		return $ret;
	}

	/**
	 * Determines if the subscription can be updated
	 *
	 * @access      public
	 * @since       2.4
	 * @return      bool
	 */
	public function can_update( $ret, $subscription ) {
		if( $subscription->gateway === 'paypalpro' && ! empty( $subscription->profile_id ) && ( 'active' === $subscription->status || 'failing' === $subscription->status || 'trialling' === $subscription->status ) ) {
			return true;
		}
		return $ret;
	}

	/**
	 * Process the update payment form
	 *
	 * @since  2.4
	 * @param  int  $subscriber    EDD_Recurring_Subscriber
	 * @param  int  $subscription  EDD_Subscription
	 * @return void
	 */
	public function update_payment_method( $subscriber, $subscription ) {

		$card_number    = isset( $_POST['card_number'] ) && is_numeric( $_POST['card_number'] )       ? $_POST['card_number']    : '';
		$card_exp_month = isset( $_POST['card_exp_month'] ) && is_numeric( $_POST['card_exp_month'] ) ? $_POST['card_exp_month'] : '';
		$card_exp_year  = isset( $_POST['card_exp_year'] ) && is_numeric( $_POST['card_exp_year'] )   ? $_POST['card_exp_year']  : '';
		$card_cvc       = isset( $_POST['card_cvc'] ) && is_numeric( $_POST['card_cvc'] )             ? $_POST['card_cvc']       : '';

		$card_zip       = isset( $_POST['card_zip'] ) ? sanitize_text_field( $_POST['card_zip'] ) : '' ;

		if ( empty( $card_number ) || empty( $card_exp_month ) || empty( $card_exp_year ) || empty( $card_cvc ) || empty( $card_zip ) ) {
			edd_set_error( 'edd_recurring_paypalpro', __( 'Please enter all required fields.', 'edd-recurring' ) );
		}

		$errors = edd_get_errors();
		if ( empty( $errors ) ) {
			$args = array(
				'USER'                => $this->username,
				'PWD'                 => $this->password,
				'SIGNATURE'           => $this->signature,
				'VERSION'             => '124',
				'METHOD'              => 'UpdateRecurringPaymentsProfile',
				'PROFILEID'           => $subscription->profile_id,
				'ACCT'                => $card_number,
				'EXPDATE'             => $card_exp_month . $card_exp_year,
				// needs to be in the format 062019
				'CVV2'                => $card_cvc,
				'ZIP'                 => $card_zip,
				'BUTTONSOURCE'        => 'EasyDigitalDownloads_SP',
			);

			$request = wp_remote_post( $this->api_endpoint, array(
				'timeout'     => 45,
				'sslverify'   => false,
				'body'        => $args,
				'httpversion' => '1.1',
			) );

			$body      = wp_remote_retrieve_body( $request );
			$code      = wp_remote_retrieve_response_code( $request );
			$message   = wp_remote_retrieve_response_message( $request );

			if ( is_wp_error( $request ) ) {

				$error = '<p>' . __( 'An unidentified error occurred.', 'edd_recurring' ) . '</p>';
				$error .= '<p>' . $request->get_error_message() . '</p>';

				edd_set_error( 'recurring_generic_paypalpro_error', $error );

			} elseif ( 200 == $code && 'OK' == $message ) {

				if( is_string( $body ) ) {
					wp_parse_str( $body, $body );
				}

				if ( 'failure' === strtolower( $body['ACK'] ) ) {

					$error = '<p>' . __( 'PayPal subscription creation failed.', 'edd_recurring' ) . '</p>';
					$error .= '<p>' . __( 'Error message:', 'edd_recurring' ) . ' ' . $body['L_LONGMESSAGE0'] . '</p>';
					$error .= '<p>' . __( 'Error code:', 'edd_recurring' ) . ' ' . $body['L_ERRORCODE0'] . '</p>';

					edd_record_gateway_error( $error, __( 'Error', 'edd_recurring' ), array( 'response' => '401' ) );

					edd_set_error( $body['L_ERRORCODE0'], $body['L_LONGMESSAGE0'] );

				} else {

					// Request was successful, but verify the profile ID that came back matches
					if ( $subscription->profile_id !== $body['PROFILEID'] ) {
						edd_set_error( 'edd_recurring_profile_mismatch', __( 'Error updating subscription', 'edd-recurring' ) );
					}

				}

				// Reattempt unpaid charges if this subscription is failing
				$subscription->retry();

			} else {

				edd_set_error( 'edd_recurring_paypal_pro_generic_error', __( 'Something has gone wrong, please try again', 'edd_recurring' ) );

			}

		}

	}

	/**
	 * Link the recurring profile in PayPal.
	 *
	 * @since  2.4.4
	 * @param  string $profile_id   The recurring profile id
	 * @param  object $subscription The Subscription object
	 * @return string               The link to return or just the profile id
	 */
	public function link_profile_id( $profile_id, $subscription ) {

		if( ! empty( $profile_id ) ) {
			$html     = '<a href="%s" target="_blank">' . $profile_id . '</a>';

			$payment  = edd_get_payment( $subscription->parent_payment_id );
			$base_url = 'live' === $payment->mode ? 'https://www.paypal.com' : 'https://www.sandbox.paypal.com';
			$link     = esc_url( $base_url . '/cgi-bin/webscr?cmd=_profile-recurring-payments&encrypted_profile_id=' . $profile_id );

			$profile_id = sprintf( $html, $link );
		}

		return $profile_id;

	}

}
$edd_recurring_paypal_wp_pro = new EDD_Recurring_PayPal_Website_Payments_Pro();
