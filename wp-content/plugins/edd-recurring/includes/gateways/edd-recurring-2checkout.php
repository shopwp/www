<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $edd_recurring_2co;

class EDD_Recurring_2Checkout extends EDD_Recurring_Gateway {

	private $credentials;

	/**
	 * Setup gateway ID and load API libraries
	 *
	 * @access      public
	 * @since       2.4
	 * @return      void
	 */
	public function init() {

		$this->id            = '2checkout';
		$this->friendly_name = __( '2Checkout', 'edd-recurring' );
		$this->credentials   = $this->get_api_credentials();
		$this->offsite       = true;

		if( ! class_exists( 'Twocheckout' ) && file_exists( WP_PLUGIN_DIR . '/edd-2checkout/sdk/lib/Twocheckout.php' ) ) {
			require_once WP_PLUGIN_DIR . '/edd-2checkout/sdk/lib/Twocheckout.php';
		}

	}

	/**
	 * Validate the checkout fields and show any errors if necessary
	 *
	 * @access      public
	 * @since       2.4
	 * @return      void
	 */
	public function validate_fields( $data, $posted ) {

		if( empty( $this->credentials['tco_secret_word'] ) || empty( $this->credentials['tco_account_number'] ) ) {

			edd_set_error( 'missing_account_number', __( 'You must enter your account number and secret word in settings', 'edd-recurring' ) );

		}

		if( count( edd_get_cart_contents() ) > 1 && ! $this->can_purchase_multiple_subs() ) {

			edd_set_error( 'subscription_invalid', __( 'Only one subscription may be purchased at a time through 2Checkout.', 'edd-recurring') );

		}

	}

	/**
	 * Store pending profile IDs
	 *
	 * @access      public
	 * @since       2.4
	 * @return      void
	 */
	public function create_payment_profiles() {

		foreach( $this->subscriptions as $key => $subscription ) {

			if( ! empty( $subscription['has_trial'] ) ) {
				edd_set_error( 'free_trial_not_supported', __( 'Free trials are not supported by 2Checkout.', 'edd-recurring' ) );
				return;
			}

			$this->subscriptions[ $key ]['profile_id'] = '2checkout-' . $this->purchase_data['purchase_key'] . '-' . $key;

		}

	}

	/**
	 * Finishes the signup process by redirecting to the success page or to an off-site payment page
	 *
	 * @access      public
	 * @since       2.4
	 * @return      void
	 */
	public function complete_signup() {

		Twocheckout::sandbox( edd_is_test_mode() );

		// Get the success url
		$return_url = add_query_arg( array(
			'payment-confirmation' => '2checkout',
			'payment-id'           => $this->payment_id,
		), edd_get_success_page_uri() );

		$args = array(
			'sid'                => $this->credentials['tco_account_number'],
			'merchant_order_id'  => $this->purchase_data['purchase_key'],
			'mode'               => '2CO',
			'currency_code'      => strtoupper( edd_get_currency() ),
			'x_receipt_link_url' => $return_url,
		);

		$i = 0;
		foreach( $this->subscriptions as $key => $subscription ) {

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

			$args['li_' . $i . '_recurrence']  = $frequency . ' ' . $period;
			$args['li_' . $i . '_duration']    = $subscription['bill_times'] > 0 ? $subscription['bill_times'] . ' ' . ucfirst( $subscription['period'] ) : 'Forever';
			$args['li_' . $i . '_type']        = 'product';
			$args['li_' . $i . '_price']       = round( $subscription['recurring_amount'], 2 );
			$args['li_' . $i . '_product_id']  = $subscription['id'];
			$args['li_' . $i . '_name']        = $subscription['name'];
			$args['li_' . $i . '_quantity']    = '1';
			$args['li_' . $i . '_tangible']    = 'N';
			$args['li_' . $i . '_startup_fee'] = round( $subscription['initial_amount'] - $subscription['recurring_amount'], 2 );

			$i++;

		}

		$args = apply_filters( 'edd_recurring_2checkout_redirect_args', $args, $this );

		try {

			edd_empty_cart();

			$charge = Twocheckout_Charge::redirect( $args );

			exit;

		} catch ( Twocheckout_Error $e ) {

			edd_set_error( '2checkout_error', $e->getMessage() );

		}

	}

	/**
	 * Processes webhooks from the payment processor
	 *
	 * @access      public
	 * @since       2.4
	 * @return      void
	 */
	public function process_webhooks() {

		if( empty( $_GET['edd-listener'] ) || '2COINS' !== $_GET['edd-listener'] ) {
			return;
		}

		$this->log( 'EDD Recurring 2Checkout INS - processing started. Input data: ' . var_export( $_POST, true ) );

		if( empty( $_POST['sale_id'] ) || empty( $_POST['sale_id'] ) || empty( $_POST['sale_id'] ) ) {

			$this->log( 'EDD Recurring 2Checkout INS - processing stopped due to missing required parameters. Input data: ' . var_export( $_POST, true ) );
			die( '0' );
		}

		if ( empty( $_POST['message_type'] ) ) {
			die( '-2' );
		}

		if ( empty( $_POST['vendor_id'] ) ) {
			die( '-3' );
		}


		$purchase_key = sanitize_text_field( $_POST['vendor_order_id'] );
		$payment      = edd_get_payment_by( 'key', $purchase_key );

		if( ! $payment || $payment->ID < 1 ) {

			$this->log( 'EDD Recurring 2Checkout INS - Payment not found, processing stopped' );
			return;
		}

		$this->log( 'EDD Recurring 2Checkout INS - Payment ' . $payment->ID . ' found' );

		$hash  = strtoupper( md5( $_POST['sale_id'] . $this->credentials['tco_account_number'] . $_POST['invoice_id'] . $this->credentials['tco_secret_word'] ) );

		$this->log( 'EDD Recurring 2Checkout INS - Calculated hash: ' . $hash );

		if ( ! hash_equals( $hash, $_POST['md5_hash'] ) ) {

			$this->log( 'EDD Recurring 2Checkout INS - Invalid hash. Expected: ' . $hash . '. Provided: ' . $_POST['md5_hash'] );

			die('-1');
		}

		$db = new EDD_Subscriptions_DB;

		$i = 1;
		foreach( $payment->cart_details as $key => $item ) {

			$item_id = isset( $_POST[ 'item_id_' . $i ] ) ? absint( $_POST[ 'item_id_' . $i ] ) : 0;
			$sub     = $db->get_subscriptions( array( 'parent_payment_id' => $payment->ID, 'product_id' => $item_id, 'number' => 1 ) );

			if( empty( $item_id ) ) {
				$this->log( 'EDD Recurring 2Checkout INS - Processing stopped due to missing item ID' );
				continue;
			}

			if( ! $sub ) {
				$this->log( 'EDD Recurring 2Checkout INS - Processing stopped due to subscription not being found' );
				continue;
			}

			$sub = reset( $sub );

			$this->log( 'EDD Recurring 2Checkout INS - Preparing to process INS type ' . $_POST['message_type'] );

			switch( strtoupper( $_POST['message_type'] ) ) {

				case 'ORDER_CREATED' :

					// The default profile ID we will use
					$profile_id = sanitize_text_field( $_POST['sale_id'] ) . '-' . $i;

					// Check if this is the first payment or a renewal
					$payment->status = 'complete';
					$payment->transaction_id = sanitize_text_field( $_POST[ 'sale_id' ] );
					$payment->save();

					if( defined( 'TWOCHECKOUT_ADMIN_USER' ) && defined( 'TWOCHECKOUT_ADMIN_PASSWORD' ) ) {

						Twocheckout::privateKey( $this->credentials['tco_private_key'] );
						Twocheckout::sellerId( $this->credentials['tco_account_number'] );
						Twocheckout::sandbox( edd_is_test_mode() );
						Twocheckout::username( TWOCHECKOUT_ADMIN_USER );
						Twocheckout::password( TWOCHECKOUT_ADMIN_PASSWORD );

						// Attempt to retrieve the line item_id from 2Checkout
						$sale = Twocheckout_Sale::retrieve( array( 'sale_id' => sanitize_text_field( $_POST['sale_id'] ) ) );

						if( $sale && ! empty( $sale['sale']['invoices'][0]['lineitems'] ) ) {

							$this->log( 'EDD Recurring 2Checkout INS - ORDER_CREATED sale ' . sanitize_text_field( $_POST['sale_id'] ) . ' retrieved' );

							foreach( $sale['sale']['invoices'][0]['lineitems'] as $line_item ) {

								if( (int) $item_id !== (int) $line_item['vendor_product_id'] ) {
									continue;
								}

								$this->log( 'EDD Recurring 2Checkout INS - ORDER_CREATED profile ID found: ' . $line_item['lineitem_id'] );

								// This is the real ID needed to cancel this particular subscription
								$profile_id = $line_item['lineitem_id'];

							}

						}

					}

					$sub->update( array(
						'profile_id'     => $profile_id,
						'transaction_id' => sanitize_text_field( $_POST[ 'invoice_id' ] ),
						'status'         => 'active'
					) );

					$this->log( 'EDD Recurring 2Checkout INS - ORDER_CREATED subscription ' . $sub->id . ' updated' );

					die( '1' );

					break;

				case 'RECURRING_INSTALLMENT_SUCCESS' :

					$payment_id = $sub->add_payment( array(
						'amount'         => sanitize_text_field( $_POST[ 'item_list_amount_' . $i ] ),
						'transaction_id' => sanitize_text_field( $_POST[ 'invoice_id' ] ),
					) );

					if ( ! empty( $payment_id ) ) {
						$sub->renew( $payment_id );
						$this->log( 'EDD Recurring 2Checkout INS - RECURRING_INSTALLMENT_SUCCESS subscription' . $sub->id . ' renewed' );
					}

					die( '1' );

					break;

				case 'FRAUD_STATUS_CHANGED':

					$fraud_status = sanitize_text_field( $_POST['fraud_status'] );

					$this->log( 'EDD Recurring 2Checkout INS - FRAUD_STATUS_CHANGED processing status of ' . $fraud_status );

					switch( $fraud_status ) {

						case 'fail':
							$sub->cancel();

							$payment_id      = $sub->parent_payment_id;
							$initial_payment = new EDD_Payment( $payment_id );

							$initial_payment->update_status( 'revoked' );
							$initial_payment->add_note( __( '2Checkout fraud review failed.', 'edd-recurring' ) );

							break;

						case 'wait':
							$args = array(
								'status' => 'pending'
							);

							$sub->update( $args );

							$payment_id      = $sub->parent_payment_id;
							$initial_payment = new EDD_Payment( $payment_id );

							$initial_payment->update_status( 'pending' );
							$initial_payment->add_note( __( '2Checkout fraud review in progress.', 'edd-recurring' ) );
							break;

						case 'pass':
							$args = array(
								'status' => 'active'
							);

							$sub->update( $args );

							$payment_id      = $sub->parent_payment_id;
							$initial_payment = new EDD_Payment( $payment_id );

							$initial_payment->update_status( 'complete' );
							$initial_payment->add_note( __( '2Checkout fraud review passed.', 'edd-recurring' ) );
							break;

					}

					die( '1' );
					break;

				case 'INVOICE_STATUS_CHANGED' :

					$status = strtolower( sanitize_text_field( $_POST['invoice_status'] ) );

					$this->log( 'EDD Recurring 2Checkout INS - INVOICE_STATUS_CHANGED processing status of ' . $status );

					switch( $status ) {

						case 'deposited' :
							$args = array(
								'status' => 'active',
							);

							$sub->update( $args );

							$payment_id      = $sub->parent_payment_id;
							$initial_payment = new EDD_Payment( $payment_id );

							$initial_payment->update_status( 'complete' );
							$initial_payment->add_note( __( '2Checkout Invoice status set to deposited.', 'edd-recurring' ) );
							break;

					}

					die( '2' );
					break;

				case 'RECURRING_INSTALLMENT_FAILED' :

					$sub->failing();

					$this->log( 'EDD Recurring 2Checkout INS - RECURRING_INSTALLMENT_FAILED for ' . $sub->id );

					do_action( 'edd_recurring_payment_failed', $sub );

					die( '1' );

				case 'RECURRING_STOPPED' :

					$sub->cancel();

					$this->log( 'EDD Recurring 2Checkout INS - RECURRING_STOPPED for ' . $sub->id );

					die( '1' );

					break;

				case 'RECURRING_COMPLETE' :

					$sub->complete();

					$this->log( 'EDD Recurring 2Checkout INS - RECURRING_COMPLETE for ' . $sub->id );

					die( '1' );

					break;

				case 'RECURRING_RESTARTED' :

					$date = date( 'Y-n-d H:i:s', strtotime( $_POST[ 'item_rec_date_next_' . $i ] ) );

					$sub->update( array( 'status' => 'active', 'expiration' => $date ) );

					$this->log( 'EDD Recurring 2Checkout INS - RECURRING_RESTARTED for ' . $sub->id );

					die( '1' );

					break;

				case 'REFUND_ISSUED' :

					$payment_id      = $sub->parent_payment_id;
					$initial_payment = new EDD_Payment( $payment_id );
					$cart_count      = count( $initial_payment->cart_details );

					// Look for the new refund line item
					if( isset( $_POST['item_list_amount_' . $cart_count + 1 ] ) && $_POST['item_list_amount_' . $cart_count + 1 ] < $payment->total ) {

						$refunded = edd_sanitize_amount( $_POST['item_list_amount_' . $cart_count + 1 ] );
						$payment->add_note( sprintf( __( 'Partial refund for %s processed in 2Checkout' ), edd_currency_filter( $refunded ) ) );

					} else {

						$sub->cancel();

						$initial_payment->update_status( 'refunded' );
						$initial_payment->add_note( __( 'Payment refunded in 2Checkout.', 'edd-recurring' ) );

					}

					$this->log( 'EDD Recurring 2Checkout INS - REFUND_ISSUED for ' . $sub->id );

					die( '1' );

					break;

			}

			$i++;

		}

	}

	/**
	 * Determines if the subscription can be cancelled
	 *
	 * @access      public
	 * @since       2.4
	 * @return      bool
	 */
	public function can_cancel( $ret, $subscription ) {

		if( $subscription->gateway === '2checkout' && ! empty( $subscription->profile_id ) && in_array( $subscription->status, $this->get_cancellable_statuses() ) ) {

			if( false === strpos( $subscription->profile_id, '-' ) && defined( 'TWOCHECKOUT_ADMIN_USER' ) && defined( 'TWOCHECKOUT_ADMIN_PASSWORD' ) ) {
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

		Twocheckout::privateKey( $this->credentials['tco_private_key'] );
		Twocheckout::sellerId( $this->credentials['tco_account_number'] );
		Twocheckout::sandbox( edd_is_test_mode() );
		Twocheckout::username( TWOCHECKOUT_ADMIN_USER );
		Twocheckout::password( TWOCHECKOUT_ADMIN_PASSWORD );

		$cancelled = Twocheckout_Sale::stop( array( 'lineitem_id' => $subscription->profile_id ) );

		if( $cancelled['response_code'] == 'OK' ) {
			return true;
		}

		return false;

	}

	/**
	 * Retrieve the API credentials
	 *
	 * @since 2.4
	 * @return array
	 */
	public function get_api_credentials() {

		$data = array(
			'tco_secret_word'    => edd_get_option( 'tco_secret_word' ),
			'tco_account_number' => edd_get_option( 'tco_account_number' ),
			'tco_private_key'    => edd_get_option( 'tco_private_api_key' ),
			'tco_public_key'     => edd_get_option( 'tco_publishable_api_key' ),
		);

		return $data;
	}

	/**
	 * Determines if 2Checkout allows multiple subscriptions to be purchased at once.
	 *
	 * 2Checkout does not allow multiple subscriptions to be purchased at the same time.
	 *
	 * @since 2.8.5
	 * @return bool
	 */
	public function can_purchase_multiple_subs() {
		return false;
	}

	/**
	 * Link the recurring profile in 2Checkout.
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
			$base_url = 'live' === $payment->mode ? 'https://2checkout.com/' : 'https://sandbox.2checkout.com/sandbox/';
			$url      = '<a href="%s" target="_blank">' . $profile_id . '</a>';
			$link     = esc_url( $base_url . 'sales/detail?sale_id=' . $payment->transaction_id );

			$profile_id = sprintf( $html, $link );
		}

		return $profile_id;

	}

	/**
	 * Logs a message to EDD's debug log
	 *
	 * @since 2.7.22
	 * @param string $message The message to log
	 *
	 * @return void
	 */
	private function log( $message ) {
		if( function_exists( 'edd_debug_log' ) ) {
			edd_debug_log( $message );
		}
	}

}
$edd_recurring_2co = new EDD_Recurring_2Checkout;
