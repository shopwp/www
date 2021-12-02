<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $edd_recurring_2co_onsite;

class EDD_Recurring_2Checkout_Onsite extends EDD_Recurring_2Checkout {

	private $credentials;

	/**
	 * Setup gateway ID and load API libraries
	 *
	 * @access      public
	 * @since       2.4
	 * @return      void
	 */
	public function init() {

		$this->id          = '2checkout_onsite';
		$this->friendly_name = __( '2Checkout OnSite', 'edd-recurring' );
		$this->credentials = $this->get_api_credentials();
		$this->offsite     = false;

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

			edd_set_error( 'missing_api_keys', __( 'You must enter your account number and secret word in settings', 'edd-recurring' ) );

		}

		if( empty( $this->credentials['tco_private_key'] ) || empty( $this->credentials['tco_public_key'] ) ) {

			edd_set_error( 'missing_api_keys', __( 'You must enter your Private and Publishable API key in settings', 'edd-recurring' ) );

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

		if( empty( $_POST['token'] ) ) {
			edd_set_error( 'missing_card_token', __( 'Missing 2Checkout token, please try again or contact support if the issue persists.', 'edd-recurring' ) );
			return;
		}

		if( ! empty( $subscription['has_trial'] ) ) {
			edd_set_error( 'free_trial_not_supported', __( 'Free trials are not supported by 2Checkout.', 'edd-recurring' ) );
			return;
		}

		$verify_ssl = ! edd_is_test_mode();
		Twocheckout::privateKey( $this->credentials['tco_private_key'] );
		Twocheckout::sellerId( $this->credentials['tco_account_number'] );
		Twocheckout::sandbox( edd_is_test_mode() );
		Twocheckout::verifySSL( $verify_ssl );

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

			$line_items = array( array(
				"recurrence"  => $frequency . ' ' . $period,
				"type"        => 'product',
				"price"       => round( $subscription['recurring_amount'], 2 ),
				"productId"   => $subscription['id'],
				"name"        => $subscription['name'],
				"quantity"    => '1',
				"tangible"    => 'N',
				"startupFee"  => round( $subscription['initial_amount'] - $subscription['recurring_amount'], 2 )
			) );

			$this->subscriptions[ $key ]['profile_id'] = '2checkout-' . $this->purchase_data['purchase_key'] . '-' . $key;

		}

		try {

			$charge = Twocheckout_Charge::auth( array(
				'merchantOrderId' => $this->purchase_data['purchase_key'],
				'token'           => $this->purchase_data['post_data']['token'],
				'currency'        => strtoupper( edd_get_currency() ),
				'billingAddr'     => array(
					'name'        => sanitize_text_field( $this->purchase_data['card_info']['card_name'] ),
					'addrLine1'   => sanitize_text_field( $this->purchase_data['card_info']['card_address'] ),
					'city'        => sanitize_text_field( $this->purchase_data['card_info']['card_city'] ),
					'state'       => sanitize_text_field( $this->purchase_data['card_info']['card_state'] ),
					'zipCode'     => sanitize_text_field( $this->purchase_data['card_info']['card_zip'] ),
					'country'     => sanitize_text_field( $this->purchase_data['card_info']['card_country'] ),
					'email'       => $this->purchase_data['user_email'],
				),
				"lineItems"       => $line_items,
			));

			if( $charge['response']['responseCode'] == 'APPROVED' ) {

				$this->purchase_data['transaction_id'] = $charge['response']['orderNumber'];

			}

		} catch ( Twocheckout_Error $e ) {

			edd_set_error( '2checkout_error', $e->getMessage() );

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

		edd_set_payment_transaction_id( $this->payment_id, $this->purchase_data['transaction_id'] );

		if( defined( 'TWOCHECKOUT_ADMIN_USER' ) && defined( 'TWOCHECKOUT_ADMIN_PASSWORD' ) ) {

			$verify_ssl = ! edd_is_test_mode();
			Twocheckout::privateKey( $this->credentials['tco_private_key'] );
			Twocheckout::sellerId( $this->credentials['tco_account_number'] );
			Twocheckout::sandbox( edd_is_test_mode() );
			Twocheckout::username( TWOCHECKOUT_ADMIN_USER );
			Twocheckout::password( TWOCHECKOUT_ADMIN_PASSWORD );
			Twocheckout::verifySSL( $verify_ssl );

			// Attempt to retrieve the lineitem_id from 2Checkout
			$sale = Twocheckout_Sale::retrieve( array( 'sale_id' => $this->purchase_data['transaction_id'] ) );

			if( $sale && ! empty( $sale['sale']['invoices'][0]['lineitems'] ) ) {

				foreach( $sale['sale']['invoices'][0]['lineitems'] as $line_item ) {

					foreach( $this->subscriptions as $key => $subscription ) {

						if( (int) $subscription['id'] !== (int) $line_item['vendor_product_id'] ) {
							continue;
						}

						// Retrieve and set the real ID needed to cancel this particular subscription
						$sub = new EDD_Subscription( '2checkout-' . $this->purchase_data['purchase_key'] . '-' . $key, true );
						$sub->update( array(
							'profile_id'     => $line_item['lineitem_id'],
							'transaction_id' => $this->purchase_data['transaction_id']
						) );
					}

				}

			}

		}

		wp_redirect( edd_get_success_page_uri() );
		exit;

	}

	/**
	 * Determines if the subscription can be cancelled
	 *
	 * @access      public
	 * @since       2.4
	 * @return      bool
	 */
	public function can_cancel( $ret, $subscription ) {

		if( $subscription->gateway === '2checkout_onsite' && ! empty( $subscription->profile_id ) && in_array( $subscription->status, $this->get_cancellable_statuses() ) ) {

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
	 * @return      bool
	 */
	public function cancel( $subscription, $valid ) {

		if( empty( $valid ) ) {
			return false;
		}

		$verify_ssl = ! edd_is_test_mode();
		Twocheckout::privateKey( $this->credentials['tco_private_key'] );
		Twocheckout::sellerId( $this->credentials['tco_account_number'] );
		Twocheckout::sandbox( edd_is_test_mode() );
		Twocheckout::username( TWOCHECKOUT_ADMIN_USER );
		Twocheckout::password( TWOCHECKOUT_ADMIN_PASSWORD );
		Twocheckout::verifySSL( $verify_ssl );

		$cancelled = Twocheckout_Sale::stop( array( 'lineitem_id' => $subscription->profile_id ) );

		if( $cancelled['response_code'] == 'OK' ) {
			return true;
		}

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

			$payment  = edd_get_payment( $subscription->parent_payment_id );
			$base_url = 'live' === $payment->mode ? 'https://2checkout.com/' : 'https://sandbox.2checkout.com/sandbox/';
			$url      = '<a href="%s" target="_blank">' . $profile_id . '</a>';
			$link     = esc_url( $base_url . 'sales/detail?sale_id=' . $payment->transaction_id );

			$profile_id = sprintf( $html, $link );
		}

		return $profile_id;

	}

}
$edd_recurring_2co_onsite = new EDD_Recurring_2Checkout_Onsite;
