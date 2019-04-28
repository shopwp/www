<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EDD_Recurring_Gateway {

	public $id;
	public $friendly_name = '';
	public $subscriptions = array();
	public $purchase_data = array();
	public $offsite = false;
	public $email = 0;
	public $customer_id = 0;
	public $user_id = 0;
	public $payment_id = 0;
	public $failed_subscriptions = array();
	public $custom_meta = array();

	/**
	 * Get things started
	 *
	 * @access      public
	 * @since       2.4
	 * @return      void
	 */
	public function __construct() {

		$this->init();

		add_action( 'edd_checkout_error_checks', array( $this, 'checkout_errors' ), 0, 2 );
		add_action( 'edd_gateway_' . $this->id, array( $this, 'process_checkout' ), 0 );
		add_action( 'init', array( $this, 'require_login' ), 9 );
		add_action( 'init', array( $this, 'process_webhooks' ), 9 );
		add_action( 'wp_enqueue_scripts', array( $this, 'scripts' ), 10 );
		add_action( 'edd_cancel_subscription', array( $this, 'process_cancellation' ) );
		add_action( 'edd_reactivate_subscription', array( $this, 'process_reactivation' ) );
		add_filter( 'edd_subscription_can_cancel', array( $this, 'can_cancel' ), 10, 2 );
		add_filter( 'edd_subscription_can_update', array( $this, 'can_update' ), 10, 2 );
		add_filter( 'edd_subscription_can_reactivate', array( $this, 'can_reactivate' ), 10, 2 );
		add_filter( 'edd_subscription_can_retry', array( $this, 'can_retry' ), 10, 2 );
		add_filter( 'edd_recurring_retry_subscription_' . $this->id, array( $this, 'retry' ), 10, 2 );
		add_action( 'edd_recurring_cancel_' . $this->id . '_subscription', array( $this, 'cancel' ), 10, 2 );
		add_action( 'edd_recurring_reactivate_' . $this->id . '_subscription', array( $this, 'reactivate' ), 10, 2 );
		add_action( 'edd_recurring_update_payment_form', array( $this, 'update_payment_method_form' ), 10, 1 );
		add_action( 'edd_recurring_update_subscription_payment_method', array( $this, 'process_payment_method_update' ), 10, 3 );
		add_action( 'edd_recurring_update_' . $this->id . '_subscription', array( $this, 'update_payment_method' ), 10, 2 );
		add_action( 'edd_after_cc_fields', array( $this, 'after_cc_fields' ) );

		add_filter( 'edd_subscription_profile_link_' . $this->id, array( $this, 'link_profile_id' ), 10, 2 );
		add_filter( 'edd_purchase_data_before_gateway' . $this->id, array( $this, 'maybe_skip_manual_on_free' ), 10, 2 );
	}

	/**
	 * Setup gateway ID and possibly load API libraries
	 *
	 * @access      public
	 * @since       2.4
	 * @return      void
	 */
	public function init() {

		$this->id = '';

	}

	/**
	 * Enqueue necessary scripts. Perhaps only enqueue when edd_is_checkout()
	 *
	 * @access      public
	 * @since       2.4
	 * @return      void
	 */
	public function scripts() {
	}

	/**
	 * Validate checkout fields
	 *
	 * @access      public
	 * @since       2.4
	 * @return      void
	 */
	public function validate_fields( $data, $posted ) {

		/*

		if( true ) {
			edd_set_error( 'error_id_here', __( 'Error message here', 'edd-recurring' ) );
		}

		*/

	}

	/**
	 * Creates subscription payment profiles and sets the IDs so they can be stored
	 *
	 * @access      public
	 * @since       2.4
	 * @return      void
	 */
	public function create_payment_profiles() {

		// Gateways loop through each download and creates a payment profile and then sets the profile ID

		foreach ( $this->subscriptions as $key => $subscription ) {
			$this->subscriptions[ $key ]['profile_id'] = '1234';
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

		wp_redirect( edd_get_success_page_uri() );
		exit;
	}

	/**
	 * Processes webhooks from the payment processor
	 *
	 * @access      public
	 * @since       2.4
	 * @return      void
	 */
	public function process_webhooks() {

		// set webhook URL to: home_url( 'index.php?edd-listener=' . $this->id );

		if ( empty( $_GET['edd-listener'] ) || $this->id !== $_GET['edd-listener'] ) {
			return;
		}

		// process webhooks here

	}

	/**
	 * Determines if a subscription can be cancelled through the gateway
	 *
	 * @access      public
	 * @since       2.4
	 * @return      bool
	 */
	public function can_cancel( $ret, $subscription ) {
		return $ret;
	}

	/**
	 * Returns an array of subscription statuses that can be cancelled
	 *
	 * @access      public
	 * @since       2.6.3
	 * @return      array
	 */
	public function get_cancellable_statuses() {
		return apply_filters( 'edd_recurring_cancellable_statuses', array( 'active', 'trialling', 'failing' ) );
	}

	/**
	 * Cancels a subscription
	 *
	 * @access      public
	 * @since       2.4
	 * @return      bool
	 */
	public function cancel( $subscription, $valid ) { }

	/**
	 * Determines if a subscription can be reactivated through the gateway
	 *
	 * @access      public
	 * @since       2.7.10
	 * @return      bool
	 */
	public function can_reactivate( $ret, $subscription ) {
		return $ret;
	}

	/**
	 * Reactivates a cancelled subscription
	 *
	 * @access      public
	 * @since       2.7.10
	 * @return      bool
	 */
	public function reactivate( $subscription, $valid ) {}

	/**
	 * Determines if a subscription can be retried through the gateway
	 *
	 * @access      public
	 * @since       2.7.10
	 * @return      bool
	 */
	public function can_retry( $ret, $subscription ) {
		return $ret;
	}

	/**
	 * Retries a failing subscription
	 *
	 * This method is connected to a filter instead of an action so we can return a nice error message.
	 *
	 * @access      public
	 * @since       2.7.10
	 * @return      bool|WP_Error
	 */
	public function retry( $result, $subscription ) {
		return $result;
	}

	/**
	 * Determines if a subscription can be cancelled through a gateway
	 *
	 * @since  2.4
	 * @param  bool   $ret            Default stting (false)
	 * @param  object $subscription   The subscription
	 * @return bool
	 */
	public function can_update( $ret, $subscription ) {
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
	public function update_payment_method( $subscriber, $subscription ) { }

	/**
	 * Outputs the payment method update form
	 *
	 * @since  2.4
	 * @param  object $subscription The subscription object
	 * @return void
	 */
	public function update_payment_method_form( $subscription ) {

		if ( $subscription->gateway !== $this->id ) {
			return;
		}

		ob_start();
		edd_get_cc_form();
		echo ob_get_clean();

	}

	/**
	 * Get the expiration date with merchant processor
	 *
	 * @since  2.6.6
	 * @param  object $subscription The subscription object
	 * @return string Expiration date in Y-n-d H:i:s format
	 */
	public function get_expiration( $subscription ) {

		// Return existing expiration date by default
		return date( 'Y-n-d H:i:s', $subscription->get_expiration_time() );
	}

	/**
	 * Outputs any information after the Credit Card Fields
	 *
	 * @since  2.4
	 * @return void
	 */
	public function after_cc_fields() {}

	/**
	 * Determines if the gateway allows multiple subscriptions to be purchased at once.

	 * @since 2.8.5
	 * @return bool
	 */
	public function can_purchase_multiple_subs() {
		return true;
	}


	/****************************************************************
	 * Below methods should not be extended except in rare cases
	 ***************************************************************/


	/**
	 * Processes the checkout screen and sends sets up the subscription data for hand-off to the gateway
	 *
	 * @access      public
	 * @since       2.4
	 * @return      void
	 */
	public function process_checkout( $purchase_data ) {

		if ( ! edd_recurring()->is_purchase_recurring( $purchase_data ) ) {
			return; // Not a recurring purchase so bail
		}

		if ( ! wp_verify_nonce( $purchase_data['gateway_nonce'], 'edd-gateway' ) ) {
			wp_die( __( 'Nonce verification has failed', 'edd-recurring' ), __( 'Error', 'edd-recurring' ), array( 'response' => 403 ) );
		}

		if ( $purchase_data['user_info']['id'] < 1 && ! class_exists( 'EDD_Auto_Register' ) ) {
			edd_set_error( 'edd_recurring_logged_in', __( 'You must log in or create an account to purchase a subscription', 'edd-recurring' ) );
		}

		// Never let a user_id be lower than 0 since WP Core absints when doing get_user_meta lookups
		if ( $purchase_data['user_info']['id'] < 1 ) {
			$purchase_data['user_info']['id'] = 0;
		}

		// Initial validation
		do_action( 'edd_recurring_process_checkout', $purchase_data, $this );

		$errors = edd_get_errors();

		if ( $errors ) {

			edd_send_back_to_checkout( '?payment-mode=' . $this->id );

		}

		$this->purchase_data = apply_filters( 'edd_recurring_purchase_data', $purchase_data, $this );
		$this->user_id       = $purchase_data['user_info']['id'];
		$this->email         = $purchase_data['user_info']['email'];

		if ( empty( $this->user_id ) ) {
			$subscriber = new EDD_Recurring_Subscriber( $this->email );
		} else {
			$subscriber = new EDD_Recurring_Subscriber( $this->user_id, true );
		}

		if ( empty( $subscriber->id ) ) {

			$name = '';
			if ( ! empty( $purchase_data['user_info']['first_name'] ) ) {
				$name = $purchase_data['user_info']['first_name'];
			}

			if( ! empty( $purchase_data['user_info']['last_name'] ) ) {
				$name .= ' ' . $purchase_data['user_info']['last_name'];
			}

			$subscriber_data = array(
				'name'        => $name,
				'email'       => $purchase_data['user_info']['email'],
				'user_id'     => $this->user_id,
			);

			$subscriber->create( $subscriber_data );

		}

		$this->customer_id = $subscriber->id;

		foreach ( $this->purchase_data['cart_details'] as $key => $item ) {

			if ( ! isset( $item['item_number']['options'] ) || ! isset( $item['item_number']['options']['recurring'] ) ) {
				continue;
			}

			// Check if one time discounts are enabled in the admin settings, which prevent discounts from being used on renewals
			$recurring_one_time_discounts = edd_get_option( 'recurring_one_time_discounts' ) ? true : false;

			// If there is a trial in the cart for this item, One-Time Discounts have no relevance, and discounts are used no matter what.
			if( ! empty( $item['item_number']['options']['recurring']['trial_period']['unit'] ) && ! empty( $item['item_number']['options']['recurring']['trial_period']['quantity'] ) ) {
				$recurring_one_time_discounts = false;
			}

			$prices_include_tax = edd_prices_include_tax();

			// If we should NOT apply the discount to the renewal
			if( $recurring_one_time_discounts ) {

				// If entered prices do not include tax
				if ( ! $prices_include_tax ) {

					// When prices don't include tax, the $item['subtotal'] is the cost of the item, including quantities, but NOT including discounts or taxes
					// Set the recurring amount to be the full amount, with no discounts
					$recurring_amount = $item['subtotal'] + edd_calculate_tax( $item['subtotal'] );

					// Set the tax to be the full amount as well for recurs. Recalculate it using the amount without discounts, which is the subtotal
					$recurring_tax = edd_calculate_tax( $item['subtotal'] );

				} else {

					// If prices include tax, we can't use the $item['subtotal'] like we do above, because it does not include taxes, and we need it to include taxes.
					// So instead, we use the item_price, which is the entered price of the product, without any discounts, and with taxes included.
					$recurring_amount = $item['item_price'];
					$recurring_tax = edd_calculate_tax( $item['item_price'] );

				}

			} else {

				// The $item['price'] includes all discounts and taxes.
				// Since discounts are allowed on renewals, we don't need to make any changes at all to the price or the tax.
				$recurring_amount = $item['price'];
				$recurring_tax = $item['tax'];

			}

			$fees = $item['item_number']['options']['recurring']['signup_fee'];

			if( ! empty( $item['fees'] ) ) {
				foreach( $item['fees'] as $fee ) {

					// Negative fees are already accounted for on $item['price']
					if( $fee['amount'] <= 0 ) {
						continue;
					}

					$fees += $fee['amount'];
				}

			}

			// Determine tax amount for any fees if it's more than $0
			$fee_tax = $fees > 0 ? edd_calculate_tax( $fees ) : 0;

			$args = array(
				'id'                 => $item['id'],
				'name'               => $item['name'],
				'price_id'           => isset( $item['item_number']['options']['price_id'] ) ? $item['item_number']['options']['price_id'] : false,
				'initial_amount'     => edd_sanitize_amount( $item['price'] + $fees + $fee_tax ),
				'recurring_amount'   => edd_sanitize_amount( $recurring_amount ),
				'initial_tax'        => edd_use_taxes() ? edd_sanitize_amount( $item['tax'] + $fee_tax ) : 0,
				'initial_tax_rate'   => edd_sanitize_amount( $this->purchase_data['tax_rate'] ),
				'recurring_tax'      => edd_use_taxes() ? edd_sanitize_amount( $recurring_tax ) : 0,
				'recurring_tax_rate' => edd_sanitize_amount( $this->purchase_data['tax_rate'] ),
				'signup_fee'         => edd_sanitize_amount( $fees ),
				'period'             => $item['item_number']['options']['recurring']['period'],
				'frequency'          => 1, // Hard-coded to 1 for now but here in case we offer it later. Example: charge every 3 weeks
				'bill_times'         => $item['item_number']['options']['recurring']['times'],
				'profile_id'         => '', // Profile ID for this subscription - This is set by the payment gateway
				'transaction_id'     => '', // Transaction ID for this subscription - This is set by the payment gateway
			);

			$args = apply_filters( 'edd_recurring_subscription_pre_gateway_args', $args, $item );

			if( ! edd_get_option( 'recurring_one_time_trials' ) || ! $subscriber->has_trialed( $item['id'] ) ) {

				// If the item in the cart has a free trial period
				if( ! empty( $item['item_number']['options']['recurring']['trial_period']['unit'] ) && ! empty( $item['item_number']['options']['recurring']['trial_period']['quantity'] ) ) {

					$args['has_trial']         = true;
					$args['trial_unit']        = $item['item_number']['options']['recurring']['trial_period']['unit'];
					$args['trial_quantity']    = $item['item_number']['options']['recurring']['trial_period']['quantity'];
					$args['status']            = 'trialling';
					$args['initial_amount']    = 0;
					$args['initial_tax_rate']  = 0;
					$args['initial_tax']       = 0;
				}

			}

			$this->subscriptions[] = $args;
		}

		// Store this so we can detect if the count changes due to failed subscriptions
		$initial_subscription_count = count( $this->subscriptions );

		do_action( 'edd_recurring_pre_create_payment_profiles', $this );

		if ( ! is_user_logged_in() ) {
			edd_set_error( 'edd_recurring_login', __( 'You must be logged in to purchase a subscription', 'edd-recurring' ) );
			$redirect_query = '?payment-mode=' . $this->id . '&edd-recurring-login=1';

			edd_send_back_to_checkout( $redirect_query );
		}

		// Create subscription payment profiles in the gateway
		$this->create_payment_profiles();

		// See if the gateway reported some subscriptions that failed
		if ( ! empty( $this->failed_subscriptions ) ) {

			// See if any subscriptions failed and remove them if necessary
			foreach ( $this->failed_subscriptions as $failed_sub ) {

				$item_key = $failed_sub['key'];
				// Remove it from the subscriptions array so we don't create an EDD Subscription entry
				unset( $this->subscriptions[ $item_key ] );

				// Remove it from the cart details and downloads so we don't charge the customer and give accees to it
				unset( $this->purchase_data['downloads'][ $item_key ] );
				unset( $this->purchase_data['cart_details'][ $item_key ] );

			}

			// Since we allow subscriptions to be marked as failed, make sure that we at least have one valid subscription
			if ( count( $this->failed_subscriptions ) === $initial_subscription_count ) {
				if ( ! empty( $failed_sub['error'] ) ) {
					edd_set_error( 'recurring-failed-sub-error-' . $item_key, $failed_sub['error'] );
				} else {
					edd_set_error( 'recurring-all-subscriptions-failed', __( 'There was an error processing your order. Please contact support.', 'edd-recurring' ) );
				}
			}

		}

		do_action( 'edd_recurring_post_create_payment_profiles', $this );

		// Look for errors after trying to create payment profiles
		$errors = edd_get_errors();

		if ( $errors ) {
			edd_send_back_to_checkout( '?payment-mode=' . $this->id );
		}

		// Record the subscriptions and finish up
		$this->record_signup();

		// Finish the signup process. Gateways can perform off-site redirects here if necessary
		$this->complete_signup();

		// Look for any last errors
		$errors = edd_get_errors();

		// We shouldn't usually get here, but just in case a new error was recorded, we need to check for it
		if ( $errors ) {

			edd_send_back_to_checkout( '?payment-mode=' . $this->id );

		}

	}

	/**
	 * Records purchased subscriptions in the database and creates an edd_payment record
	 *
	 * @access      public
	 * @since       2.4
	 * @return      void
	 */
	public function record_signup() {


		$payment_data = array(
			'price'        => $this->purchase_data['price'],
			'date'         => $this->purchase_data['date'],
			'user_email'   => $this->purchase_data['user_email'],
			'purchase_key' => $this->purchase_data['purchase_key'],
			'currency'     => edd_get_currency(),
			'downloads'    => $this->purchase_data['downloads'],
			'user_info'    => $this->purchase_data['user_info'],
			'cart_details' => $this->purchase_data['cart_details'],
			'status'       => 'pending',
		);

		foreach( $this->subscriptions as $key => $item ) {

			if ( ! empty( $item['has_trial'] ) ) {
				$payment_data['cart_details'][ $key ]['item_price'] = $item['initial_amount'] - $item['initial_tax'];
				$payment_data['cart_details'][ $key ]['tax']        = $item['initial_tax'];
				$payment_data['cart_details'][ $key ]['price']      = 0;
				$payment_data['cart_details'][ $key ]['discount']   = 0;

			}

		}

		// Record the pending payment
		$this->payment_id = edd_insert_payment( $payment_data );
		$payment = new EDD_Payment( $this->payment_id );

		if ( ! $this->offsite ) {

			// Offsite payments get verified via a webhook so are completed in webhooks()
			$payment->status = 'publish';
			$payment->save();

		}

		// Set subscription_payment
		$payment->update_meta( '_edd_subscription_payment', true );


		/*
		 * We need to delete pending subscription records to prevent duplicates. This ensures no duplicate subscription records are created when a purchase is being recovered. See:
		 * https://github.com/easydigitaldownloads/edd-recurring/issues/707
		 * https://github.com/easydigitaldownloads/edd-recurring/issues/762
		 */
		global $wpdb;
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}edd_subscriptions WHERE parent_payment_id = %d AND status = 'pending';", $this->payment_id ) );

		$subscriber = new EDD_Recurring_Subscriber( $this->customer_id );

		// Now create the subscription record(s)
		foreach ( $this->subscriptions as $subscription ) {

			if( isset( $subscription['status'] ) ) {
				$status  = $subscription['status'];
			} else {
				$status  = $this->offsite ? 'pending' : 'active';
			}

			$trial_period = ! empty( $subscription['has_trial'] ) ? $subscription['trial_quantity'] . ' ' . $subscription['trial_unit'] : '';

			$args = array(
				'product_id'            => $subscription['id'],
				'user_id'               => $this->purchase_data['user_info']['id'],
				'parent_payment_id'     => $this->payment_id,
				'status'                => $status,
				'period'                => $subscription['period'],
				'initial_amount'        => $subscription['initial_amount'],
				'initial_tax_rate'      => $subscription['initial_tax_rate'],
				'initial_tax'           => $subscription['initial_tax'],
				'recurring_amount'      => $subscription['recurring_amount'],
				'recurring_tax_rate'    => $subscription['recurring_tax_rate'],
				'recurring_tax'         => $subscription['recurring_tax'],
				'bill_times'            => $subscription['bill_times'],
				'expiration'            => $subscriber->get_new_expiration( $subscription['id'], $subscription['price_id'], $trial_period ),
				'trial_period'          => $trial_period,
				'profile_id'            => $subscription['profile_id'],
				'transaction_id'        => $subscription['transaction_id'],
			);

			$args = apply_filters( 'edd_recurring_pre_record_signup_args', $args, $this );
			$sub = $subscriber->add_subscription( $args );

			if( ! $this->offsite && $trial_period ) {
				$subscriber->add_meta( 'edd_recurring_trials', $subscription['id'] );
			}

		}

		// Now look if the gateway reported any failed subscriptions and log a payment note
		if ( ! empty( $this->failed_subscriptions ) ) {

			foreach ( $this->failed_subscriptions as $failed_subscription ) {
				$note = sprintf( __( 'Failed creating subscription for %s. Gateway returned: %s', 'edd-recurring' ), $failed_subscription['subscription']['name'], $failed_subscription['error'] );
				$payment->add_note( $note );
			}

			$payment->update_meta( '_edd_recurring_failed_subscriptions', $this->failed_subscriptions );
		}

		if ( ! empty( $this->custom_meta ) ) {
			foreach ( $this->custom_meta as $key => $value ) {
				$payment->update_meta( $key, $value );
			}
		}

	}

	/**
	 * Triggers the validate_fields() method for the gateway during checkout submission
	 *
	 * This should not be extended
	 *
	 * @access      public
	 * @since       2.4
	 * @return      void
	 */
	public function checkout_errors( $data, $posted ) {

		if ( $this->id !== $posted['edd-gateway'] ) {
			return;
		}

		if( ! edd_recurring()->cart_contains_recurring() ) {
			return;
		}

		if ( edd_recurring()->cart_is_mixed() ) {
			edd_set_error( 'edd_recurring_mixed_cart', __( 'Subscriptions and non-subscriptions may not be purchased at the same time. Please purchase each separately.', 'edd-recurring' ) );
		}

		if ( edd_recurring()->cart_is_mixed_with_trials() ) {
			edd_set_error( 'edd_recurring_mixed_trials_cart', __( 'Free trials and non-trials may not be purchased at the same time. Please purchase each separately.', 'edd-recurring' ) );
		}

		$this->validate_fields( $data, $posted );

	}

	/**
	 * Process the update payment form
	 *
	 * @since  2.4
	 * @param  int  $user_id            User ID
	 * @param  int  $subscription_id    Subscription ID
	 * @param  bool $verified           Sanity check that the request to update is coming from a verified source
	 * @return void
	 */
	public function process_payment_method_update( $user_id, $subscription_id, $verified ) {

		if ( 1 !== $verified ) {
			wp_die( __( 'Unable to verify payment update.', 'edd-recurring' ) );
		}

		if ( ! is_user_logged_in() ) {
			wp_die( __( 'You must be logged in to update a payment method.', 'edd-recurring' ) );
		}

		$subscription = new EDD_Subscription( $subscription_id );
		if ( $subscription->gateway !== $this->id ) {
			return;
		}

		if ( empty( $subscription->id ) ) {
			wp_die( __( 'Invalid subscription id.', 'edd-recurring' ) );
		}

		$subscriber   = new EDD_Recurring_Subscriber( $subscription->customer_id );
		if ( empty( $subscriber->id ) ) {
			wp_die( __( 'Invalid subscriber.', 'edd-recurring' ) );
		}

		// Make sure the User doing the udpate is the user the subscription belongs to
		if ( $user_id != $subscriber->user_id ) {
			wp_die( __( 'User ID and Subscriber do not match.', 'edd-recurring' ) );
		}

		// make sure we don't have any left over errors present
		edd_clear_errors();

		do_action( 'edd_recurring_update_' . $subscription->gateway .'_subscription', $subscriber, $subscription );

		$errors = edd_get_errors();

		if ( empty( $errors ) ) {

			$url = add_query_arg( array( 'updated' => true ) );
			wp_redirect( $url );
			die();
		}

		$url = add_query_arg( array( 'action' => 'update', 'subscription_id' => $subscription->id ) );
		wp_redirect( $url );
		die();

	}

	/**
	 * Handles cancellation requests for a subscription
	 *
	 * This should not be extended
	 *
	 * @access      public
	 * @since       2.4
	 * @return      void
	 */
	public function process_cancellation( $data ) {

		if( empty( $data['sub_id'] ) ) {
			return;
		}

		if( ! is_user_logged_in() ) {
			return;
		}

		if( ! wp_verify_nonce( $data['_wpnonce'], 'edd-recurring-cancel' ) ) {
			wp_die( __( 'Nonce verification failed', 'edd-recurring' ), __( 'Error', 'edd-recurring' ), array( 'response' => 403 ) );
		}

		$data['sub_id'] = absint( $data['sub_id'] );
		$subscription   = new EDD_Subscription( $data['sub_id'] );

		try {

			$subscription->cancel();

			if( is_admin() ) {

				wp_redirect( admin_url( 'edit.php?post_type=download&page=edd-subscriptions&edd-message=cancelled&id=' . $subscription->id ) );
				exit;

			} else {

				$redirect = remove_query_arg( array( '_wpnonce', 'edd_action', 'sub_id' ), add_query_arg( array( 'edd-message' => 'cancelled' ) ) );
				$redirect = apply_filters( 'edd_recurring_cancellation_redirect', $redirect, $subscription );
				wp_safe_redirect( $redirect );
				exit;

			}

		} catch ( Exception $e ) {
			wp_die( $e->getMessage(), __( 'Error', 'edd-recurring' ), array( 'response' => 403 ) );
		}

	}

	/**
	 * Process subscription reactivation
	 *
	 * @access      public
	 * @since       2.6
	 * @return      void
	 */
	public function process_reactivation( $data ) {

		if( empty( $data['sub_id'] ) ) {
			return;
		}

		if( ! is_user_logged_in() ) {
			return;
		}

		if( ! wp_verify_nonce( $data['_wpnonce'], 'edd-recurring-reactivate' ) ) {
			wp_die( __( 'Nonce verification failed', 'edd-recurring' ), __( 'Error', 'edd-recurring' ), array( 'response' => 403 ) );
		}


		$data['sub_id'] = absint( $data['sub_id'] );
		$subscription   = new EDD_Subscription( $data['sub_id'] );

		if( ! $subscription->can_reactivate() ) {
			wp_die( __( 'This subscription cannot be reactivated', 'edd-recurring' ), __( 'Error', 'edd-recurring' ), array( 'response' => 403 ) );
		}

		try {

			do_action( 'edd_recurring_reactivate_' . $subscription->gateway . '_subscription', $subscription, true );

			$user = is_user_logged_in() ? wp_get_current_user()->user_login : 'gateway';
			$note = sprintf( __( 'Subscription reactivated by %s', 'edd-recurring' ), $user );
			$subscription->add_note( $note );

			if( is_admin() ) {

				wp_redirect( admin_url( 'edit.php?post_type=download&page=edd-subscriptions&edd-message=reactivated&id=' . $subscription->id ) );
				exit;

			} else {

				$redirect = remove_query_arg( array( '_wpnonce', 'edd_action', 'sub_id' ), add_query_arg( array( 'edd-message' => 'reactivated' ) ) );
				$redirect = apply_filters( 'edd_recurring_reactivation_redirect', $redirect, $subscription );
				wp_safe_redirect( $redirect );
				exit;

			}

		} catch ( Exception $e ) {
			wp_die( $e->getMessage(), __( 'Error', 'edd-recurring' ), array( 'response' => 403 ) );
		}

	}

	/**
	 * Make it so that accounts are required
	 *
	 * @access      public
	 * @since       2.4
	 * @return      void
	 */
	public function require_login() {

		$cart_items    = edd_get_cart_contents();
		$has_recurring = false;

		if ( empty( $cart_items ) ) {
			return;
		}


		// Loops through each item to see if any of them are recurring
		foreach( $cart_items as $item ) {

			if( ! isset( $item['options']['recurring'] ) ) {
				continue;
			}

			$has_recurring = true;

		}

		$auto_register = class_exists( 'EDD_Auto_Register' );

		if( $has_recurring && ! $auto_register ) {

			add_filter( 'edd_no_guest_checkout', '__return_true' );
			add_filter( 'edd_logged_in_only', '__return_true' );

		}

	}

	/**
	 * Retrieve subscription details
	 *
	 * This method should be extended by each gateway in order to call the gateway API to determine the status and expiration of the subscription
	 *
	 * @access      public
	 * @since       2.4
	 * @return      array
	 */
	public function get_subscription_details( EDD_Subscription $subscription ) {

		/*
		 * Return value for valid subscriptions should be an array containing the following keys:
		 *
		 * - status: The status of the subscription (active, cancelled, expired, completed, pending, failing)
		 * - expiration: The expiration / renewal date of the subscription
		 * - error: An instance of WP_Error with error code and message (if any)
		 */

		$ret = array(
			'status'     => '',
			'expiration' => '',
			'error'      => '',
		);

		return $ret;

	}

	public function link_profile_id( $profile_id, $subscription ) {
		return $profile_id;
	}

	/**
	 * Easy Digital Downloads automatically sets the gateway to "manual" if the cart total is 0.
	 *
	 * In order for free trials to work, we need to reset "manual" to the selected gateway to ensure the proper gateway processes the signup.
	 *
	 * @access      public
	 * @since       2.6
	 * @return      array
	 */
	public function maybe_skip_manual_on_free( $purchase_data, $valid_data ) {

		if ( edd_recurring()->is_purchase_recurring( $purchase_data ) ) {



			$purchase_data['gateway'] = $gateway;

		}

		return $purchase_data;
	}

}
