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

		if ( empty( $_POST['message_type'] ) ) {
			die( '-2' );
		}

		if ( empty( $_POST['vendor_id'] ) ) {
			die( '-3' );
		}

		$purchase_key = sanitize_text_field( $_POST['vendor_order_id'] );
		$payment      = edd_get_payment_by( 'key', $purchase_key );

		if( ! $payment || $payment->ID < 1 ) {
			return;
		}

		$hash  = strtoupper( md5( $_POST['sale_id'] . $this->credentials['tco_account_number'] . $_POST['invoice_id'] . $this->credentials['tco_secret_word'] ) );

		if ( ! hash_equals( $hash, $_POST['md5_hash'] ) ) {
			die('-1');
		}

		$db = new EDD_Subscriptions_DB;

		$i = 1;
		foreach( $payment->cart_details as $key => $item ) {

			$item_id = isset( $_POST[ 'item_id_' . $i ] ) ? absint( $_POST[ 'item_id_' . $i ] ) : 0;
			$sub     = $db->get_subscriptions( array( 'parent_payment_id' => $payment->ID, 'product_id' => $item_id, 'number' => 1 ) );

			if( empty( $item_id ) ) {
				continue;
			}

			if( ! $sub ) {
				continue;
			}

			$sub = reset( $sub );

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

						// Attempt to retrieve the lineitem_id from 2Checkout
						$sale = Twocheckout_Sale::retrieve( array( 'sale_id' => sanitize_text_field( $_POST['sale_id'] ) ) );

						if( $sale && ! empty( $sale['sale']['invoices'][0]['lineitems'] ) ) {

							foreach( $sale['sale']['invoices'][0]['lineitems'] as $line_item ) {

								if( (int) $item_id !== (int) $line_item['vendor_product_id'] ) {
									continue;
								}

								// This is the real ID needed to cancel this particular subscription
								$profile_id = $line_item['lineitem_id'];

							}

						}

					}

					$sub->update( array(
						'profile_id'     => $profile_id,
						'transaction_id' => sanitize_text_field( $_POST[ 'sale_id' ] ),
						'status'         => 'active'
					) );

					die( '1' );

					break;

				case 'RECURRING_INSTALLMENT_SUCCESS' :

					$sub->add_payment( array(
						'amount'         => sanitize_text_field( $_POST[ 'item_list_amount_' . $i ] ),
						'transaction_id' => sanitize_text_field( $_POST[ 'sale_id' ] ),
					) );
					$sub->renew();

					die( '1' );

					break;

				case 'RECURRING_INSTALLMENT_FAILED' :

					$sub->failing();

					do_action( 'edd_recurring_payment_failed', $sub );

					die( '1' );

				case 'RECURRING_STOPPED' :

					$sub->cancel();

					die( '1' );

					break;

				case 'RECURRING_COMPLETE' :

					$sub->complete();

					die( '1' );

					break;

				case 'RECURRING_RESTARTED' :

					$date = date( 'Y-n-d H:i:s', strtotime( $_POST[ 'item_rec_date_next_' . $i ] ) );

					$sub->update( array( 'status' => 'active', 'expiration' => $date ) );

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

}
$edd_recurring_2co = new EDD_Recurring_2Checkout;
