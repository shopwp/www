<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

global $edd_recurring_paypal;

class EDD_Recurring_PayPal extends EDD_Recurring_Gateway {

	/**
	 * Get things started
	 *
	 * @access      public
	 * @since       2.4
	 * @return      void
	 */
	public function init() {

		$this->id = 'paypal';

		$this->offsite = true;

		// Process PayPal subscription sign ups
		add_action( 'edd_paypal_subscr_signup', array( $this, 'process_paypal_subscr_signup' ) );

		// Process PayPal subscription payments
		add_action( 'edd_paypal_subscr_payment', array( $this, 'process_paypal_subscr_payment' ) );

		// Process PayPal subscription cancellations
		add_action( 'edd_paypal_subscr_cancel', array( $this, 'process_paypal_subscr_cancel' ) );

		// Process PayPal subscription end of term notices
		add_action( 'edd_paypal_subscr_eot', array( $this, 'process_paypal_subscr_eot' ) );

		// Process PayPal payment failed
		add_action( 'edd_paypal_subscr_failed', array( $this, 'process_paypal_subscr_failed' ) );

		//Validate PayPal times server side when a download post is saved
		add_action( 'save_post', array( $this, 'validate_paypal_recurring_download' ) );

		add_action( 'edd_paypal_refund_purchase', array( $this, 'cancel_subscriptions_on_refund' ) );
		add_action( 'edd_pre_refund_payment', array( $this, 'refund_renewal_payment' ) );

	}

	/**
	 * Create temporary profile IDs that we can reference during IPN processing
	 *
	 * @access      public
	 * @since       2.4
	 * @return      void
	 */
	public function create_payment_profiles() {

		foreach( $this->subscriptions as $key => $subscription ) {

			// This is a temporary ID used to look it up later during IPN processing
			$this->subscriptions[ $key ]['profile_id'] = 'paypal-' . $this->purchase_data['purchase_key'];

		}
	}

	/**
	 * Initial field validation before ever creating profiles or customers
	 *
	 * @access      public
	 * @since       2.4
	 * @return      void
	 */
	public function validate_fields( $data, $posted ) {

		if( ! edd_get_option( 'paypal_email', false ) ) {

			edd_set_error( 'edd_recurring_paypal_email_missing', __( 'Please enter your PayPal email address.', 'edd-recurring' ) );

		}

		if( count( edd_get_cart_contents() ) > 1 ) {

			edd_set_error( 'subscription_invalid', __( 'Only one subscription may be purchased through PayPal per checkout.', 'edd-recurring') );

		}

	}

	/**
	 * Setup PayPal arguments and redirect to PayPal
	 *
	 * @access      public
	 * @since       2.4
	 * @return      void
	 */
	public function complete_signup() {

		// Get the success url
		$return_url = add_query_arg( array(
			'payment-confirmation' => 'paypal',
			'payment-id' => $this->payment_id
		), edd_get_success_page_uri() );

		// Get the PayPal redirect uri
		$paypal_redirect = trailingslashit( edd_get_paypal_redirect() ) . '?';

		// Setup PayPal arguments
		$paypal_args = array(
			'business'      => edd_get_option( 'paypal_email', false ),
			'email'         => $this->purchase_data['user_email'],
			'first_name'    => $this->purchase_data['user_info']['first_name'],
			'last_name'     => $this->purchase_data['user_info']['last_name'],
			'invoice'       => $this->purchase_data['purchase_key'],
			'no_shipping'   => '1',
			'shipping'      => '0',
			'no_note'       => '1',
			'currency_code' => edd_get_currency(),
			'charset'       => get_bloginfo( 'charset' ),
			'custom'        => $this->payment_id,
			'rm'            => '2',
			'return'        => $return_url,
			'cancel_return' => edd_get_failed_transaction_uri( '?payment-id=' . $this->payment_id ),
			'notify_url'    => add_query_arg( 'edd-listener', 'IPN', home_url( 'index.php' ) ),
			'page_style'    => edd_get_paypal_page_style(),
			'cbt'           => get_bloginfo( 'name' ),
			'bn'            => 'EasyDigitalDownloads_SP',
			'sra'           => '1',
			'src'           => '1',
			'cmd'           => '_xclick-subscriptions'
		);

		if ( ! empty( $this->purchase_data['user_info']['address'] ) ) {
			$paypal_args['address1'] = $this->purchase_data['user_info']['address']['line1'];
			$paypal_args['address2'] = $this->purchase_data['user_info']['address']['line2'];
			$paypal_args['city']     = $this->purchase_data['user_info']['address']['city'];
			$paypal_args['country']  = $this->purchase_data['user_info']['address']['country'];
		}

		// Add cart items
		foreach ( $this->subscriptions as $subscription ) {

			$paypal_args['a1'] = round( $subscription['initial_amount'], 2 );

			// Set the recurring amount
			$paypal_args['a3'] = round( $subscription['recurring_amount'], 2 );

			// Set tax amount
			$paypal_args['tax'] = isset( $subscription['tax'] ) ? $subscription['tax'] : 0;

			// Set purchase description
			$paypal_args['item_name'] = $subscription['name'];
			if( ! is_null( $subscription['price_id'] ) ) {
				$paypal_args['item_name'] .= stripslashes_deep( html_entity_decode(  ' - ' . edd_get_price_option_name( $subscription['id'], $subscription['price_id'] ), ENT_COMPAT, 'UTF-8' ) );
			}

			// One period unit (every week, every month, etc)
			$paypal_args['p3'] = $subscription['frequency'];
			$paypal_args['p1'] = $subscription['frequency'];

			// Set the recurring period
			switch( $subscription['period'] ) {
				case 'day' :
					$paypal_args['t3'] = 'D';
					$paypal_args['t1'] = 'D';
					break;
				case 'week' :
					$paypal_args['t3'] = 'W';
					$paypal_args['t1'] = 'W';
					break;
				case 'month' :
					$paypal_args['t3'] = 'M';
					$paypal_args['t1'] = 'M';
					break;
				case 'quarter' :
					$paypal_args['t3'] = 'M';
					$paypal_args['t1'] = 'M';
					$paypal_args['p3'] = '3';
					$paypal_args['p1'] = '3';
					break;
				case 'semi-year' :
					$paypal_args['t3'] = 'M';
					$paypal_args['t1'] = 'M';
					$paypal_args['p3'] = '6';
					$paypal_args['p1'] = '6';
					break;
				case 'year' :
					$paypal_args['t3'] = 'Y';
					$paypal_args['t1'] = 'Y';
					break;
			}

			if( ! empty( $subscription['has_trial'] ) ) {
				$paypal_args['a1'] = 0;
				$paypal_args['p1'] = $subscription['trial_quantity'];

				switch( $subscription['trial_unit'] ) {
					case 'day' :
						$paypal_args['t1'] = 'D';
						break;
					case 'week' :
						$paypal_args['t1'] = 'W';
						break;
					case 'month' :
						$paypal_args['t1'] = 'M';
						break;
					case 'year' :
						$paypal_args['t1'] = 'Y';
						break;
				}

			}

			if( $subscription['bill_times'] > 1 ) {

				if( ! empty( $subscription['has_trial'] ) ) {
					// Free trial counts as one installment, so to bill the proper number of times, we need to add one to it
					$subscription['bill_times'] += 1;
				}

				// Make sure it's not over the max of 52
				$subscription['bill_times'] = $subscription['bill_times'] <= 52 ? absint( $subscription['bill_times'] ) - 1 : 52;

				$paypal_args['srt'] = $subscription['bill_times'];

			}

		}

		$paypal_args = apply_filters( 'edd_recurring_paypal_args', $paypal_args, $this->purchase_data );

		// Build query
		$paypal_redirect .= http_build_query( $paypal_args );

		// Fix for some sites that encode the entities
		$paypal_redirect = str_replace( '&amp;', '&', $paypal_redirect );

		// Get rid of cart contents
		edd_empty_cart();

		// Redirect to PayPal
		wp_redirect( $paypal_redirect ); exit;
	}

	/**
	 * Processes the "signup" IPN notice
	 *
	 * @since  2.4
	 * @return void
	 */
	public function process_paypal_subscr_signup( $ipn_data ) {

		$parent_payment_id = absint( $ipn_data['custom'] );

		if( empty( $parent_payment_id ) ) {
			return;
		}

		if( ! edd_get_payment_by( 'id', $parent_payment_id ) ) {
			return;
		}

		edd_update_payment_status( $parent_payment_id, 'publish' );

		// Record transaction ID
		edd_insert_payment_note( $parent_payment_id, sprintf( __( 'PayPal Subscription ID: %s', 'edd-recurring' ) , $ipn_data['subscr_id'] ) );

		$subscription = $this->get_subscription( $ipn_data );

		if( false === $subscription ) {
			return;
		}

		$status = 'trialling' == $subscription->status ? 'trialling' : 'active';

		// Retrieve pending subscription from database and update it's status to active and set proper profile ID
		$subscription->update( array( 'profile_id' => $ipn_data['subscr_id'], 'status' => $status ) );

	}


	/**
	 * Processes the recurring payments as they come in
	 *
	 * @since  2.4
	 * @return void
	 */
	public function process_paypal_subscr_payment( $ipn_data ) {

		$subscription = $this->get_subscription( $ipn_data );

		if( false === $subscription ) {
			return;
		}

		$transaction_id = edd_get_payment_transaction_id( $subscription->parent_payment_id );
		$signup_date    = strtotime( $subscription->created );
		$today          = date( 'Y-n-d', $signup_date ) == date( 'Y-n-d', strtotime( $ipn_data['payment_date'] ) );

		// Look to see if payment is same day as signup and we have set the transaction ID on the parent payment yet
		if( $today && ( ! $transaction_id || $transaction_id == $subscription->parent_payment_id ) ) {

			// Verify the amount paid
			$initial_amount = round( $subscription->initial_amount, 2 );
			$paid_amount    = round( $ipn_data['mc_gross'], 2 );

			if( $paid_amount < $initial_amount ) {

				$payment = new EDD_Payment( $subscription->parent_payment_id );
				$payment->status = 'failed';
				$payment->add_note( __( 'Payment failed due to invalid amount in PayPal IPN.', 'edd-recurring' ) );
				$payment->save();

				edd_record_gateway_error( __( 'IPN Error', 'edd-recurring' ), sprintf( __( 'Invalid payment amount in IPN response. IPN data: %s', 'edd-recurring' ), json_encode( $ipn_data ) ), $payment->ID );

				return;

			}

			$subscription->set_transaction_id( $ipn_data['txn_id'] );

			// This is the very first payment
			edd_set_payment_transaction_id( $subscription->parent_payment_id, $ipn_data['txn_id'] );
			return;

		}

		if( edd_get_purchase_id_by_transaction_id( $ipn_data['txn_id'] ) ) {
			return; // Payment alreay recorded
		}

		$currency_code = strtolower( $ipn_data['mc_currency'] );

		// verify details
		if( $currency_code != strtolower( edd_get_currency() ) ) {
			// the currency code is invalid
			edd_record_gateway_error( __( 'IPN Error', 'edd-recurring' ), sprintf( __( 'Invalid currency in IPN response. IPN data: ', 'edd-recurring' ), json_encode( $ipn_data ) ) );
			return;
		}

		$args = array(
			'amount'         => $ipn_data['mc_gross'],
			'transaction_id' => $ipn_data['txn_id']
		);

		$subscription->add_payment( $args );
		$subscription->renew();

	}

	/**
	 * Processes the "cancel" IPN notice
	 *
	 * @since  2.4
	 * @return void
	 */
	public function process_paypal_subscr_cancel( $ipn_data ) {

		$subscription = $this->get_subscription( $ipn_data );

		if( false === $subscription ) {
			return;
		}

		$subscription->cancel();

	}

	/**
	 * Processes the "cancel" IPN notice
	 *
	 * @since  2.4
	 * @return void
	 */
	public function process_paypal_subscr_eot( $ipn_data ) {

		$subscription = $this->get_subscription( $ipn_data );

		if( false === $subscription ) {
			return;
		}

		$subscription->complete();

	}

	/**
	 * Processes the payment failed IPN notice
	 *
	 * @since  2.4
	 * @return void
	 */
	public function process_paypal_subscr_failed( $ipn_data ) {

		$subscription = $this->get_subscription( $ipn_data );

		if( false === $subscription ) {
			return;
		}

		$subscription->failing();

		do_action( 'edd_recurring_payment_failed', $subscription );

	}

	/**
	 * Retrieve the subscription this IPN notice is for
	 *
	 * @since  2.4
	 * @return EDD_Subscription|false
	 */
	public function get_subscription( $ipn_data = array() ) {

		$parent_payment_id = absint( $ipn_data['custom'] );

		if( empty( $parent_payment_id ) ) {
			return false;
		}

		$payment = edd_get_payment_by( 'id', $parent_payment_id );

		if( ! $payment ) {
			return false;
		}

		$subscription = new EDD_Subscription( $ipn_data['subscr_id'], true );

		if( ! $subscription || $subscription->id < 1 ) {

			$subs_db      = new EDD_Subscriptions_DB;
			$subs         = $subs_db->get_subscriptions( array( 'parent_payment_id' => $parent_payment_id, 'number' => 1 ) );
			$subscription = reset( $subs );

			if( $subscription && $subscription->id > 0 ) {

				// Update the profile ID so it is set for future renewals
				$subscription->update( array( 'profile_id' => sanitize_text_field( $ipn_data['subscr_id'] ) ) );

			} else {

				// No subscription found with a matching payment ID, bail
				return false;

			}

		}

		return $subscription;

	}

	/**
	 * Validate PayPal Recurring Download
	 * @description: Additional server side validation for PayPal Standard recurring
	 *
	 * @param int $post_id
	 *
	 * @return mixed
	 */
	public function validate_paypal_recurring_download( $post_id = 0 ) {
		global $post;
		//Sanity Checks
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
		if ( ! current_user_can( 'edit_products', $post_id ) ) {
			return $post_id;
		}
		if ( ! edd_is_gateway_active('paypal') ) {
			return $post_id;
		}

		$message = __( 'PayPal Standard requires recurring times to be set to 0 for indefinite subscriptions or a minimum value of 2 and a maximum value of 52 for limited subscriptions.', 'edd-recurring' );

		if ( edd_has_variable_prices( $post_id ) ) {
			$prices = edd_get_variable_prices( $post_id );
			foreach ( $prices as $price_id => $price ) {
				if ( EDD_Recurring()->is_price_recurring( $post_id, $price_id ) ) {
					$time = EDD_Recurring()->get_times( $price_id, $post_id );
					//PayPal download allow times of "1" or above "52"
					//https://developer.paypal.com/docs/classic/paypal-payments-standard/integration-guide/Appx_websitestandard_htmlvariables/
					if ( $time == 1  || $time >= 53 ) {
						wp_die( $message, __( 'Error', 'edd-recurring' ), array( 'response' => 400 ) );
					}
				}
			}
		} else {
			if ( EDD_Recurring()->is_recurring( $post_id ) ) {
				$time = EDD_Recurring()->get_times_single( $post_id );
				if ( $time == 1  || $time >= 53 ) {
					wp_die( $message, __( 'Error', 'edd-recurring' ), array( 'response' => 400 ) );
				}
			}
		}
		return $post_id;
	}

	/**
	 * Refund charges and cancel subscription when refunding via View Order Details
	 *
	 * @access      public
	 * @since       2.5
	 * @return      void
	 */
	public function cancel_subscriptions_on_refund( EDD_Payment $payment ) {

		$statuses = array( 'edd_subscription', 'publish', 'revoked' );

		if ( ! in_array( $payment->old_status, $statuses ) ) {
			return;
		}

		if ( 'paypal' !== $payment->gateway ) {
			return;
		}

		switch( $payment->old_status ) {

			case 'edd_subscription' :
			case 'publish' :
			case 'revoked' :

				// Cancel all associated subscriptions

				$db   = new EDD_Subscriptions_DB;
				$subs = $db->get_subscriptions( array( 'parent_payment_id' => $payment->ID, 'number' => 100 ) );

				if( empty( $subs ) ) {

					return;

				}

				foreach( $subs as $subscription ) {

					if ( 'cancelled' !== $subscription->status ) {

						// Cancel subscription
						$this->cancel( $subscription, true );
						$subscription->cancel();
						$payment->add_note( sprintf( __( 'Subscription %d cancelled.', 'edd-recurring' ), $subscription->id ) );

					}

				}

				// End publish/revoked case
				break;

		} // End switch

	}

	/**
	 * Refund a renewal payment
	 *
	 * @access      public
	 * @since       2.5
	 * @return      void
	 */
	public function refund_renewal_payment( EDD_Payment $payment ) {

		if ( 'paypal' !== $payment->gateway ) {
			return;
		}

		if( 'edd_subscription' !== $payment->old_status ) {
			return;
		}

		if( ! current_user_can( 'edit_shop_payments', $payment->ID ) ) {
			return;
		}

		if( empty( $_POST['edd-paypal-refund'] ) ) {
			return;
		}

		$processed = $payment->get_meta( '_edd_paypal_refunded', true );

		// If the payment has already been refunded in the past, return early.
		if ( $processed ) {
			return;
		}

		// Process the refund in PayPal.
		edd_refund_paypal_purchase( $payment );

	}

	/**
	 * Determines if the subscription can be cancelled
	 *
	 * @access      public
	 * @since       2.4
	 * @return      bool
	 */
	public function can_cancel( $ret, $subscription ) {

		if( $subscription->gateway === 'paypal' && ! empty( $subscription->profile_id ) && false !== strpos( $subscription->profile_id, 'I-' ) && in_array( $subscription->status, $this->get_cancellable_statuses() ) ) {
			$creds = edd_recurring_get_paypal_api_credentials();
			if( ! empty( $creds['username'] ) && ! empty( $creds['password'] ) && ! empty( $creds['signature'] ) ) {
				return true;
			}
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

		// Parts needed from the PayPal Express API for the cancellation
		if ( edd_is_test_mode() ) {
			$api_endpoint = 'https://api-3t.sandbox.paypal.com/nvp';
		} else {
			$api_endpoint = 'https://api-3t.paypal.com/nvp';
		}

		$creds = edd_recurring_get_paypal_api_credentials();
		if( empty( $creds['username'] ) || empty( $creds['password'] ) || empty( $creds['signature'] ) ) {
			return false;
		}
		// End of PayPal API needs

		$args = array(
			'USER'      => $creds['username'],
			'PWD'       => $creds['password'],
			'SIGNATURE' => $creds['signature'],
			'VERSION'   => '124',
			'METHOD'    => 'ManageRecurringPaymentsProfileStatus',
			'PROFILEID' => $subscription->profile_id,
			'ACTION'    => 'Cancel'
		);

		$error_msg = '';
		$request   = wp_remote_post( $api_endpoint, array( 'body' => $args, 'httpversion' => '1.1', 'timeout' => 30 ) );

		if ( is_wp_error( $request ) ) {

			$success   = false;
			$error_msg = $request->get_error_message();

		} else {

			$body    = wp_remote_retrieve_body( $request );
			$code    = wp_remote_retrieve_response_code( $request );
			$message = wp_remote_retrieve_response_message( $request );

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
			
			edd_insert_payment_note( $subscription->parent_payment_id, $error_msg );

			return false;
		}

		return true;

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

		return $details['expiration'];
	}

	/**
	 * Retrieves subscription details (status and expiration)
	 *
	 * @access      public
	 * @since       2.6.6
	 * @return      array
	 */
	public function get_subscription_details( EDD_Subscription $subscription ) {

		$ret = array(
			'status'     => '',
			'expiration' => '',
			'error'      => '',
		);

		// Parts needed from the PayPal Express API for the cancellation
		if ( edd_is_test_mode() ) {
			$api_endpoint = 'https://api-3t.sandbox.paypal.com/nvp';
		} else {
			$api_endpoint = 'https://api-3t.paypal.com/nvp';
		}

		$creds = edd_recurring_get_paypal_api_credentials();
		if( empty( $creds['username'] ) || empty( $creds['password'] ) || empty( $creds['password'] ) ) {
			$ret['error'] = new WP_Error( 'missing_api_credentials', __( 'Missing PayPal API credentials', 'edd-recurring' ) );
		}

		if( ! $subscription->id > 0 ) {

			$ret['error'] = new WP_Error( 'invalid_subscription', __( 'Invalid subscription object supplied', 'edd-recurring' ) );

		} else {

			if( ! empty( $subscription->profile_id ) ) {

				$args = array(
					'USER'      => $creds['username'],
					'PWD'       => $creds['password'],
					'SIGNATURE' => $creds['password'],
					'VERSION'   => '124',
					'METHOD'    => 'GetRecurringPaymentsProfileDetails',
					'PROFILEID' => $subscription->profile_id,
				);

				$error_msg = '';
				$request   = wp_remote_post( $api_endpoint, array( 'body' => $args, 'timeout' => 30, 'httpversion' => '1.1' ) );
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

			$payment  = new EDD_Payment( $subscription->parent_payment_id );
			$base_url = 'live' === $payment->mode ? 'https://www.paypal.com' : 'https://www.sandbox.paypal.com';
			$link     = esc_url( $base_url . '/cgi-bin/webscr?cmd=_profile-recurring-payments&encrypted_profile_id=' . $profile_id );

			$profile_id = sprintf( $html, $link );
		}

		return $profile_id;

	}

}
$edd_recurring_paypal = new EDD_Recurring_PayPal;
