<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $edd_recurring_stripe;

class EDD_Recurring_Stripe extends EDD_Recurring_Gateway {

	private $secret_key;
	private $public_key;

	public function __construct() {
		parent::__construct();

		add_action( 'edd_checkout_error_checks', array( $this, 'confirm_default_payment_method_change' ), 10, 2 );
	}

	public function init() {

		$this->id = 'stripe';
		$this->friendly_name = __( 'Stripe', 'edd-recurring' );

		if ( ! defined( 'EDDS_PLUGIN_DIR' ) ) {
			return;
		}

		if ( edd_is_test_mode() ) {
			$prefix = 'test_';
		} else {
			$prefix = 'live_';
		}

		$this->secret_key = edd_get_option( $prefix . 'secret_key', '' );
		$this->public_key = edd_get_option( $prefix . 'publishable_key', '' );

		if( class_exists( '\Stripe\Stripe' ) ) {

			\Stripe\Stripe::setApiKey( $this->secret_key );

		}

		if ( defined( 'EDD_STRIPE_API_VER' ) ) {
			\Stripe\Stripe::setApiVersion( EDD_STRIPE_API_VER );
		} else {
			\Stripe\Stripe::setApiVersion( '2018-02-28' );
		}

		add_action( 'edd_pre_refund_payment', array( $this, 'process_refund' ) );
		add_action( 'edd_recurring_stripe_check_txn', array( $this, 'check_transaction_id' ) );
		add_action( 'edd_recurring_setup_subscription', array( $this, 'maybe_check_subscription' ) );
		add_action( 'edd_subscription_completed', array( $this, 'cancel_on_completion' ), 10, 2 );

	}

	/**
	 * Initial field validation before ever creating profiles or customers
	 *
	 * @access      public
	 * @since       2.4
	 * @return      void
	 */
	public function validate_fields( $data, $posted ) {

		if( ! class_exists( '\Stripe\Stripe' ) ) {

			edd_set_error( 'edd_recurring_stripe_missing', __( 'The Stripe payment gateway does not appear to be activated.', 'edd-recurring' ) );

		}

		if( empty( $this->public_key ) ) {

			edd_set_error( 'edd_recurring_stripe_public_missing', __( 'The Stripe publishable key must be entered in settings.', 'edd-recurring' ) );

		}

		if( empty( $this->secret_key ) ) {

			edd_set_error( 'edd_recurring_stripe_public_missing', __( 'The Stripe secret key must be entered in settings.', 'edd-recurring' ) );

		}

	}

	/**
	 * Setup customers and plans in Stripe for the signup
	 *
	 * @access      public
	 * @since       2.4
	 * @return      void
	 */
	public function create_payment_profiles() {

		if ( ! isset( $_POST['edd_stripe_token'] ) && ! isset( $_POST['edd_stripe_existing_card'] ) ) {

			edd_set_error( 'edd_recurring_stripe_token_missing', __( 'Missing secure Stripe token, please try again.', 'edd-recurring' ) );
			return;

		}

		/**
		 * @var string $stripe_connect_account_id The Stripe account ID received from Stripe Connect.
		 * Used when creating charges and subscriptions.
		 */
		$stripe_connect_account_id = edd_get_option( 'stripe_connect_account_id' );

		$customer = $this->get_customer();

		if( empty( $customer ) ) {

			edd_set_error( 'edd_recurring_stripe_customer_error', __( 'The customer account in Stripe could not be created, please try again.', 'edd-recurring' ) );
			return;

		}

		$setup_currency = false;
		$currency = edd_get_currency();

		if ( empty( $customer->currency ) || $customer->currency != strtolower( $currency ) ) {

			try {

				\Stripe\InvoiceItem::create(array(
					'customer'    => $customer->id,
					'amount'      => 0,
					'currency'    => $currency,
					'description' => 'Setting Customer Currency',
					'metadata'    => array(
						'caller' => __CLASS__ . '|' . __METHOD__ . '|' . __LINE__ . '|' . EDD_RECURRING_VERSION,
					)
				));


				$temp_invoice = \Stripe\Invoice::create(array(
					'customer' => $customer->id,
					'metadata'    => array(
						'caller' => __CLASS__ . '|' . __METHOD__ . '|' . __LINE__ . '|' . EDD_RECURRING_VERSION,
					)
				));

				$setup_currency = true;

			} catch ( Exception $e ) {

				/*
				 * Currency code already set on customer. $customer->currency is the only one that may be used for this customer.
				 *
				 * Currency code was already set on the customer. Let's see if the customer has any invoices.
				 * If there are no invoices, we can delete the customer and make a new one.
				 */

				$invoices = \Stripe\Invoice::all( array( 'limit' => 1, 'customer' => $customer->id ) );

				if( empty( $invoices->data ) ) {

					// Delete the customer
					$customer->delete();

					// Get / create a new customer
					$customer = $this->get_customer();

				} else {

					edd_set_error(
						'recurring_stripe_currency_mismatch',
						sprintf( __( 'Your order must be completed in %s. Please select %s and retry your purchase.', 'edd-recurring' ), strtoupper( $customer->currency ), strtoupper( $customer->currency ) )
					);
					return;
				}

			}
		}

		$existing_card = false;

		try {
			if ( isset( $_POST['edd_stripe_token'] ) ) {
				$card = $customer->sources->create( array( "source" => $_POST['edd_stripe_token'] ) );
				$customer->default_source = $card->id;
			} elseif ( isset( $_POST['edd_stripe_existing_card'] ) ) {
				$customer->default_source = $_POST['edd_stripe_existing_card'];
				$existing_card = true;
			}

			$customer->save();

		} catch ( Exception $e ) {

			edd_set_error( 'edd_recurring_stripe_error', $e->getMessage() );
			return;

		}

		foreach( $this->subscriptions as $key => $subscription ) {

			$plan_id = $this->get_plan_id( $subscription );

			if( empty( $plan_id ) ) {

				edd_set_error( 'edd_recurring_stripe_plan_error', __( 'The subscription plan in Stripe could not be created, please try again.', 'edd-recurring' ) );

				break;

			}

			/*
			 * If we have a signup fee or the recurring amount is different than the initial amount, we need to add it to the customer's
			 * balance so that the subscription invoice amount is adjusted properly.
			 *
			 * Example: if the subscription is $10 per month and the signup fee is $5, this will add $5 to the account balance.
			 *
			 * When account balances are negative, Stripe discounts that amount on the next invoice.
			 * When account balances are positive, Stripe adds that amount to the next invoice.
			 */

			$save_balance = false;
			$balance_adjustment = 0;

			if( $subscription['initial_amount'] > $subscription['recurring_amount'] ) {
				$save_balance        = true;
				$amount              = $subscription['initial_amount'] - $subscription['recurring_amount'];
				$balance_adjustment += round( ( $amount * 100 ), 0 ); // Add additional amount to initial payment (in cents)
			}

			if( $subscription['initial_amount'] < $subscription['recurring_amount'] ) {
				$save_balance        = true;
				$amount              = $subscription['recurring_amount'] - $subscription['initial_amount'];
				$balance_adjustment -= round( ( $amount * 100 ), 0 ); // Add a discount to initial payment (in cents)
			}

			$args = array(
				'plan'        => $plan_id,
				'metadata'    => array(
					'payment_key' => $this->purchase_data['purchase_key'],
					'download'    => $subscription['name'],
					'download_id' => $subscription['id'],
					'price_id'    => $subscription['price_id'],
					'caller'      => __CLASS__ . '|' . __METHOD__ . '|' . __LINE__ . '|' . EDD_RECURRING_VERSION,
				)
			);

			if( ! empty( $subscription['has_trial'] ) ) {
				$args['trial_end'] = strtotime( $subscription['trial_quantity'] . ' ' . $subscription['trial_unit'], current_time( 'timestamp' ) );
			}

			$args = apply_filters( 'edd_recurring_create_subscription_args', $args, $this->purchase_data['downloads'], $this->id, $subscription['id'], $subscription['price_id'] );

			$args = apply_filters( 'edd_recurring_create_stripe_subscription_args', $args, $this->purchase_data, $customer );

			if( ! empty( $args['needs_one_time'] ) ) {

				$create_one_time = true;
				$license_id      = $args['license_id'];
				unset( $args['needs_one_time'] );
				unset( $args['license_id'] );

			}

			if ( ! empty( $create_one_time ) ) {
				$save_balance = false;
			}

			if( ! empty( $save_balance ) ) {

				// Do not modify the customer account balance until we know we should.
				$customer->account_balance = round( $customer->account_balance + $balance_adjustment );

				$balance_changed = true;

				$customer->save();

				if ( ! empty( $setup_currency ) && isset( $temp_invoice ) ) {

					$invoice = \Stripe\Invoice::retrieve( $temp_invoice->id );
					$invoice->closed = true;
					$invoice->save();
					unset( $temp_invoice, $invoice, $setup_currency );

				}

			}

			try {

				$sub_options = array();

				if( ! empty( $stripe_connect_account_id ) ) {
					$sub_options['stripe_account'] = $stripe_connect_account_id;
				}

				$stripe_sub = $customer->subscriptions->create( $args, $sub_options );

				$this->subscriptions[ $key ]['profile_id'] = $stripe_sub->id;

				wp_schedule_single_event( strtotime( '+2 minutes' ), 'edd_recurring_stripe_check_txn', array( $stripe_sub->id ) );

				$customer->save();

				if( ! empty( $create_one_time ) ) {

					/*
					 * This is a one-time charge created only when upgrading a license key in Software Licensing
					 *
					 * The needs_one_time flag is set via the edd_recurring_create_subscription_args through the handle_subscription_upgrade_billing() method
					 * in includes/plugin-software-licensing.php
					 *
					 * See https://github.com/easydigitaldownloads/edd-recurring/issues/497
					 */

					$charge_args = array(
						'amount'   => edds_is_zero_decimal_currency() ? $subscription['initial_amount'] : round( $subscription['initial_amount'] * 100, 0 ),
						'customer' => $customer,
						'currency' => edd_get_currency(),
						'metadata' => array(
							'subscription_id' => $stripe_sub->id,
							'license_id'      => $license_id,
							'caller'          => __CLASS__ . '|' . __METHOD__ . '|' . __LINE__ . '|' . EDD_RECURRING_VERSION,
						)
					);

					$charge_options = array();

					if( ! empty( $stripe_connect_account_id ) ) {
						$charge_options['stripe_account'] = $stripe_connect_account_id;
					}

					$charge = \Stripe\Charge::create( $charge_args, $charge_options );

					$stripe_sub->metadata['upgrade_charge_id'] = $charge->id; // Store the charge ID for easier reference

					$stripe_sub->save();

					$this->subscriptions[ $key ]['transaction_id'] = $charge->id;

				}

				if ( ! empty( $save_balance ) ) {
					// Now reset the balance
					$customer->account_balance -= $balance_adjustment;
					$customer->save();
				}

			} catch ( Exception $e ) {

				// Charging the customer failed so we need to undo the balance adjustment
				if( ! empty( $balance_changed ) ) {
					$customer->account_balance -= $balance_adjustment;
					$customer->save();
				}

				$this->failed_subscriptions[] = array(
					'key'          => $key,
					'subscription' => $subscription,
					'error'        => $e->getMessage(),
				);

			}

		} // End Subscription loop

		if ( $existing_card ) {
			$this->custom_meta['_edds_used_existing_card'] = true;
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

		// set webhook URL to: home_url( 'index.php?edd-listener=' . $this->id );

		if( empty( $_GET['edd-listener'] ) || $this->id !== $_GET['edd-listener'] ) {
			return;
		}

		// retrieve the request's body and parse it as JSON
		$body       = @file_get_contents( 'php://input' );
		$event_json = json_decode( $body );

		if ( isset( $event_json->id ) ) {

			status_header( 200 );

			try {

				$event = \Stripe\Event::retrieve( $event_json->id );

			} catch ( Exception $e ) {

				die( 'Invalid event ID' );

			}


			// Create the object with a different data key based on the type of event sent.
			$data = $event->data->object;
			switch ( $event->type ) {
				case 'invoice.payment_failed' :
				case 'invoice.payment_succeeded' :
					if ( empty( $data->subscription ) ) {
						return;
					}

					$subscription = new EDD_Subscription( $data->subscription, true );
					if ( 'invoice.payment_succeeded' == $event->type ) {
						if ( ! $subscription || $subscription->id < 1 ) {
							$subscription = $this->backfill_subscription( $data->customer, $data->subscription );
							if ( ! $subscription || $subscription->id < 1 ) {
								return;
							}
						}
					}
				break;
				case 'customer.subscription.created' :
				case 'customer.subscription.deleted' :
				case 'customer.subscription.updated' :
					if ( empty( $data->id ) ) {
						return;
					}

					$subscription = new EDD_Subscription( $data->id, true );
				break;
			}

			switch ( $event->type ) :

				case 'invoice.payment_failed' :

					$subscription->failing();

					do_action( 'edd_recurring_payment_failed', $subscription );
					do_action( 'edd_recurring_stripe_event_' . $event->type, $event );

					break;

				case 'invoice.payment_succeeded' :

					$args = array();
					$args['amount']         = $data->total / 100;
					if ( ! empty( $data->tax ) ) {
						$args['tax'] = $data->tax / 100;
					}
					$args['transaction_id'] = $data->charge;
					$subscription_id        = ! empty( $event->data->object->subscription ) ? $event->data->object->subscription : false;
					$invoices               = \Stripe\Invoice::all( array( 'limit' => 2, 'subscription' => $subscription_id ) );

					// Look to see how many invoices we have for the subscription associated with this invoice, if 1, it's the first invoice.
					if( count( $invoices->data ) === 1 && $subscription->get_total_payments() <= 1 ) {

						// This is the first signup payment
						$subscription->set_transaction_id( $args['transaction_id'] );

					} else {

						// See if the trial is still in place before allowing a 0 transaction.
						// https://github.com/easydigitaldownloads/edd-recurring/issues/611
						$stripe_sub = ! empty( $event->data->object->subscription ) ? \Stripe\Subscription::retrieve( $event->data->object->subscription ) : false;

						if ( 0 === (int) $data->total && $stripe_sub && current_time( 'timestamp' ) < $stripe_sub->trial_end ) {
							die( 'EDD Recurring: Initial Trial Invoice' );
						}

						// This is a renewal charge
						$payment_id = $subscription->add_payment( $args );

						if( empty( $stripe_sub->metadata->reactivated ) || empty( $stripe_sub->metadata->reactivation_processed ) ) {

							if ( ! empty( $payment_id ) ) {

								// Renew the subscription but only if this is not a reactivation and we got a renewal payment ID.
								$subscription->renew( $payment_id );

							}

						} elseif( ! empty( $stripe_sub->metadata->reactivated ) ) {

							// Set a flag so we know that this reactivation has been processed.
							$stripe_sub->metadata->reactivation_processed = true;
							$stripe_sub->save();
						}

					}

					do_action( 'edd_recurring_stripe_event_' . $event->type, $event );

					die( 'EDD Recurring: ' . $event->type );

					break;

				case 'customer.subscription.created' :

					do_action( 'edd_recurring_stripe_event_' . $event->type, $event );

					die( 'EDD Recurring: ' . $event->type );

					break;

				case 'customer.subscription.updated' :

					if( ! empty( $data->subscription->cancel_at_period_end ) ) {
						// This is a subscription that has been cancelled but not deleted until period end
						$subscription->cancel();
					}

					$old_amount = $subscription->recurring_amount;
					$new_amount = $data->plan->amount;

					if( ! edds_is_zero_decimal_currency() ) {
						$new_amount /= 100;
					}

					$old_amount = edd_sanitize_amount( $old_amount );
					$new_amount = edd_sanitize_amount( $new_amount );

					if( $new_amount !== $old_amount ) {
						$subscription->update( array( 'recurring_amount' => $new_amount ) );
						$subscription->add_note( sprintf( __( 'Recurring amount changed from %s to %s in Stripe.', ' edd-recurring' ), $old_amount, $new_amount ) );

					}

					do_action( 'edd_recurring_stripe_event_' . $event->type, $event );

					die( 'EDD Recurring: ' . $event->type );

					break;


				case 'customer.subscription.deleted' :

					if( 'completed' !== $subscription->status ) {

						$subscription->cancel();

						do_action( 'edd_recurring_stripe_event_' . $event->type, $event );

					}

					die( 'EDD Recurring: ' . $event->type );

					break;

				case 'charge.refunded' :

					$payment_id = edd_get_purchase_id_by_transaction_id( $data->id );

					if( $payment_id ) {

						// If we have a payment that matches this charge ID, it should be a renewal payment
						$payment = new EDD_Payment( $payment_id );

						if( 'edd_subscription' !== $payment->status ) {

							/*
							 * This is a one-time charge and not associated with a subscription.
							 * Bail and allow the main Stripe gateway to take over
							 */
							return;
						}


					} else {

						$db            = new EDD_Subscriptions_DB();
						$subscriptions = $db->get_subscriptions( array( 'number' => 1, 'transaction_id' => $data->id ) );
						if( $subscriptions ) {
							$subscription = reset( $subscriptions );
							$payment_id   = $subscription->get_original_payment_id();
							$payment      = new EDD_Payment( $payment_id );
						}

					}


					if ( ! empty( $payment->ID ) ) {

						echo "Payment ID found: $payment->ID\n";

						$refund_amount = edds_is_zero_decimal_currency() ? $data->amount_refunded : $data->amount_refunded / 100;

						if( $refund_amount >= $payment->total ) {
							$payment->status = 'refunded';
							$payment->save();
							$payment->add_note( sprintf( __( 'Charge %s refunded in Stripe.', ' edd-recurring' ), $data->id ) );
						} else {
							$payment->add_note( sprintf( __( 'Charge %s partially refunded in Stripe.', ' edd-recurring' ), $data->id ) );
						}

					} else {

						echo "No payment ID found\n";

					}

					do_action( 'edd_recurring_stripe_event_' . $event->type, $event );

					die( 'EDD Recurring: ' . $event->type );

					break;

			endswitch;

		}

	}

	/**
	 * Retrieve the customer object from Stripe
	 *
	 * @access      public
	 * @since       2.4
	 * @return      string
	 */
	public function get_customer() {

		$subscriber  = new EDD_Recurring_Subscriber( $this->customer_id );
		$customer_id = $subscriber->get_recurring_customer_id( $this->id );

		if( empty( $customer_id ) && function_exists( 'edds_get_stripe_customer_id' ) ) {

			// Look for a customer ID from the stand alone Stripe gateway
			$customer_id = edds_get_stripe_customer_id( $subscriber->email );
		}

		if ( empty( $customer_id ) ) {

			$customer = $this->create_stripe_customer();

			if ( $customer ) {

				$set_id      = true;
				$customer_id = $customer->id;

			} else {

				return false;

			}

		}

		try {

			// Update the customer to ensure their card data is up to date
			$customer = \Stripe\Customer::retrieve( $customer_id );

			if ( isset( $customer->deleted ) && $customer->deleted ) {

				// This customer was deleted so make a new one

				$set_id   = true;
				$customer = $this->create_stripe_customer();

			}

			// No customer found
		} catch ( Exception $e ) {

			// Try one more time to create the customer
			$customer = $this->create_stripe_customer();

		}

		if( $customer ) {

			$subscriber->set_recurring_customer_id( $customer->id, $this->id );

			// Ensure that one-time purchases through Stripe use the same customer ID
			if( function_exists( 'edd_stripe_get_customer_key' ) ) {
				update_user_meta( $this->user_id, edd_stripe_get_customer_key(), $customer->id );
				$subscriber->update_meta( edd_stripe_get_customer_key(), $customer->id );
			}

		}

		return $customer;

	}

	/**
	 * Creates a customer in Stripe and returns the customer object
	 *
	 * @access      private
	 * @since       2.4
	 * @return      string
	 */
	private function create_stripe_customer() {

		try {

			// Create a customer first so we can retrieve them later for future payments
			$customer = \Stripe\Customer::create( array(
					'description' => $this->purchase_data['user_email'],
					'email'       => $this->purchase_data['user_email'],
					'metadata'    => array(
						'edd_customer_id' => $this->customer_id,
						'caller'          => __CLASS__ . '|' . __METHOD__ . '|' . __LINE__ . '|' . EDD_RECURRING_VERSION,
					)
				)
			);

		} catch ( Exception $e ) {

			$customer = false;

		}


		return $customer;

	}

	/**
	 * Backfills missing subscription data.
	 *
	 * This runs when a renewal payment is processed in Stripe for a subscription that is
	 * missing the profile_id field. This happens occassionally with subscriptions created
	 * pre Recurring Payments 2.4
	 *
	 * @access      public
	 * @since       2.4
	 * @return      object EDD_Subscription
	 */
	public function backfill_subscription( $customer_id = '', $subscription_id = '' ) {

		$subscription = false;

		try {

			// Update the customer to ensure their card data is up to date
			$customer     = \Stripe\Customer::retrieve( $customer_id );
			$stripe_sub   = $customer->subscriptions->retrieve( $subscription_id );
			if ( ! empty( $stripe_sub->plan->product ) ) {
				$product   = \Stripe\Product::retrieve( $stripe_sub->plan->product );
				$plan_name = $product->name;
			} else {
				$plan_name = $stripe_sub->plan->name;
			}

			// Look up payment by email
			$payments = edd_get_payments( array(
				's'        => $customer->email,
				'status'   => 'publish',
				'number'   => 100,
				'output'   => 'payments'
			) );

			//echo '<pre>';print_r( $payments );echo '</pre>';

			if( $payments ) {

				foreach( $payments as $payment ) {

					if( ! is_array( $payment->cart_details ) ) {

						continue;

					}

					if( ! edd_get_payment_meta( $payment->ID, '_edd_subscription_payment', true ) ) {

						continue;

					}

					foreach( $payment->cart_details as $download ) {

						$slug = get_post_field( 'post_name', $download['id'] );

						if( $slug != $plan_name ) {
							continue;
						}

						// We have found a matching subscription, let's look up the sub record and fix it
						$subs_db = new EDD_Subscriptions_DB;
						$subs    = $subs_db->get_subscriptions( array( 'parent_payment_id' => $payment->ID ) );
						$sub     = reset( $subs );

						if( $sub && $sub->id > 0 ) {

							$sub->update( array( 'profile_id' => $subscription_id ) );

							$subscription = $sub;

							break;

						}

					}

				}

			}

			// No customer found
		} catch ( Exception $e ) {

		}

		return $subscription;

	}

	/**
	 * Retrieve the plan ID for an item in the cart
	 *
	 * @access      public
	 * @since       2.4
	 * @return      string
	 */
	public function get_plan_id( $subscription = array() ) {

		$name = get_post_field( 'post_name', $subscription['id'] );

		if( isset( $subscription['price_id'] ) && false !== $subscription['price_id'] ) {

			$name .= ' - ' . edd_get_price_option_name( $subscription['id'], $subscription['price_id'] );

		}

		$plan_id = $name . '_' . $subscription['recurring_amount'] . '_' . $subscription['period'];
		$plan_id = sanitize_key( $plan_id );

		try {

			$plan     = \Stripe\Plan::retrieve( $plan_id );
			$currency = strtolower( edd_get_currency() );

			if( $plan->currency != $currency ) {

				$plan_id = $plan_id . '_' . $currency;
				$args    = $this->get_plan_args( $subscription, $name, $plan_id );

				try {

					$plan    = \Stripe\Plan::retrieve( $plan_id );
					$plan_id = is_array( $plan ) ? $plan['id'] : $plan->id;

				} catch ( Exception $e ) {

					$plan_id = $this->create_stripe_plan( $args );

				}

			}

		} catch ( Exception $e ) {

			$args = $this->get_plan_args( $subscription, $name, $plan_id );
			$plan_id = $this->create_stripe_plan( $args );

		}

		return $plan_id;

	}

	/**
	 * Build the argument array for creating a plan in Stripe
	 *
	 * @since 2.7
	 * @param array  $subscription
	 * @param string $name
	 * @param string $plan_id
	 *
	 * @return array
	 */
	public function get_plan_args( $subscription = array(), $name = '', $plan_id = '' ) {
		$statement_descriptor   = $name;
		$unsupported_characters = array( '<', '>', '"', '\'' );
		$statement_descriptor   = apply_filters( 'edd_recurring_stripe_statement_descriptor', substr( $statement_descriptor, 0, 22 ), $subscription );
		$statement_descriptor   = str_replace( $unsupported_characters, '', $statement_descriptor );

		switch( $subscription['period'] ) {

			case 'quarter' :

				$frequency = 3;
				$period    = 'month';
				break;

			case 'semi-year' :

				$frequency = 6;
				$period    = 'month';
				break;

			default :

				$frequency = 1;
				$period    = $subscription['period'];
				break;

		}

		$amount = round( $subscription['recurring_amount'], edd_currency_decimal_filter() );

		/**
		 * Stripe requires the amount to be in a number of 'cents' in the currency, so we have to pad
		 * the amount by multiplying it by a power of 10 that's equal to the number of decimal places.
		 *
		 * For Example
		 * 200 = 2.00 * 100
		 * 31  = 3.1 * 10
		 * 41234 = 4.1234 * 1000
		 */
		if ( edd_currency_decimal_filter() > 0 ) {
			$amount = $amount * ( pow( 10, edd_currency_decimal_filter() ) );
		}

		$args = array(
			'amount'               => $amount,
			'interval'             => $period,
			'interval_count'       => $frequency,
			'currency'             => edd_get_currency(),
			'name'                 => $name,
			'id'                   => $plan_id,
			'statement_descriptor' => $statement_descriptor,
			'metadata' => array (
				'caller' => __CLASS__ . '|' . __METHOD__ . '|' . __LINE__ . '|' . EDD_RECURRING_VERSION,
			)
		);

		/**
		 * Stripe plan arguments.
		 * Allows filtering of the arguments that are sent to Stripe when creating the plan.
		 *
		 * @since 2.4
		 *
		 * @param array $args {
		 *     The plan arguments that will be sent to Stripe.
		 *     int    $amount         The amount that will be charged for the plan.
		 *     string $interval       The period at which the plan will renew at.
		 *     int    $interval_count The frequency at which the plan will renew.
		 *     string $name           The human readable name for the plan.
		 *     string $currency       The currency for the plan.
		 *     string $id             The string identifier for the plan in Stripe.
		 *     string $statement_descriptor The value that will show on a customer's financial institution for charges of this plan.
		 * }
		 * @param array $subscription
		 */
		return apply_filters( 'edd_recurring_create_stripe_plan_args', $args, $subscription );
	}

	/**
	 * Creates a plan in Stripe and returns the plan ID
	 *
	 * @access      public
	 * @since       2.4
	 * @return      string
	 */
	private function create_stripe_plan( $args = array() ) {

		if( ! defined( 'EDD_STRIPE_API_VER' ) || ( defined( 'EDD_STRIPE_API_VER' ) && strtotime( EDD_STRIPE_API_VER ) >= 1517788800 ) ) {

			/*
			 * If we're using API version 2018-02-05 or greater, create a product
			 *
			 * See https://github.com/easydigitaldownloads/edd-recurring/issues/925
			 */

			try {

				$id = md5( serialize( $args ) );

				$product = \Stripe\Product::retrieve( $id );

			} catch ( Exception $e ) {

				// No product found, create one

				$product = \Stripe\Product::create( array(
					'id'   => $id,
					'name' => $args['name'],
					'type' => 'service',
					'statement_descriptor' => $args['statement_descriptor'],
					'metadata' => array(
						'caller'      => __CLASS__ . '|' . __METHOD__ . '|' . __LINE__ . '|' . EDD_RECURRING_VERSION,
					)
				) );

			}

		}

		try {

			if( ! empty( $product ) ) {

				$args['product'] = $product;

				if( isset( $args['name'] ) ) {

					unset( $args['name'] );

				}

				if( isset( $args['statement_descriptor'] ) ) {

					unset( $args['statement_descriptor'] );

				}

			}

			$plan    = \Stripe\Plan::create( $args );
			$plan_id = is_array( $plan ) ? $plan['id'] : $plan->id;

		} catch ( Exception $e ) {

			$plan_id = false;

		}


		return $plan_id;

	}

	/**
	 * Determines if the subscription can be cancelled
	 *
	 * @access      public
	 * @since       2.4
	 * @return      bool
	 */
	public function can_cancel( $ret, $subscription ) {
		if( $subscription->gateway === 'stripe' && ! empty( $subscription->profile_id ) && in_array( $subscription->status, $this->get_cancellable_statuses() ) ) {
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

		try {

			$at_period_end = $subscription->status == 'failing' ? false : true;

			$sub = \Stripe\Subscription::retrieve( $subscription->profile_id );
			$sub->cancel( array( 'at_period_end' => $at_period_end ) );

			// We must now loop through and cancel all unpaid invoice to ensure that additional payment attempts are not made
			$invoices = \Stripe\Invoice::all( array( 'subscription' => $subscription->profile_id ) );

			if( $invoices ) {

				foreach( $invoices->data as $invoice ) {

					// Skip paid and closed invoices
					if( $invoice->paid || $invoice->closed ) {
						continue;
					}

					$invoice->closed = true;
					$invoice->save();

				}

			}

		} catch( Exception $e ) {
			return false;
		}

		return true;

	}

	/**
	 * Determines if a subscription can be reactivated through the gateway
	 *
	 * @access      public
	 * @since       2.6
	 * @return      bool
	 */
	public function can_reactivate( $ret, $subscription ) {
		if( $subscription->gateway === 'stripe' && ! empty( $subscription->profile_id ) && 'cancelled' == $subscription->status ) {

			$payment = edd_get_payment( $subscription->get_original_payment_id() );
			$status  = $payment->status;

			if( 'publish' !== $status && 'revoked' !== $status ) {
				return false;
			}

			$was_upgraded = $payment->get_meta( '_edd_sl_upgraded_to_payment_id' );
			if ( ! empty( $was_upgraded ) ) {
				return false;
			}

			return true;

		}

		return $ret;

	}

	/**
	 * Determines if the subscription can be retried when failing
	 *
	 * @access      public
	 * @since       2.8
	 * @return      bool
	 */
	public function can_retry( $ret, $subscription ) {
		if( $subscription->gateway === 'stripe' && ! empty( $subscription->profile_id ) && 'failing' === $subscription->status ) {
			return true;
		}
		return $ret;
	}

	/**
	 * Reactivates a subscription
	 *
	 * @access      public
	 * @since       2.6
	 * @return      string
	 */
	public function reactivate( $subscription, $valid ) {

		if( empty( $valid ) ) {
			return false;
		}

		try {

			$sub = \Stripe\Subscription::retrieve( $subscription->profile_id );

			if( empty( $sub->cancel_at_period_end ) || 'canceled' === $sub->status ) {

				$subscriber = new EDD_Recurring_Subscriber( $subscription->customer->id );

				$c = \Stripe\Customer::retrieve( $subscriber->get_recurring_customer_id( $this->id ) );

				// This sub was 100% cancelled so we have to create a new subscription instead

				$args = array(
					'plan'        => $sub->plan->id,
					'tax_percent' => $sub->tax_percent,
					'metadata'    => array(
						'reactivated' => true,
						'payment_key' => $sub->metadata->payment_key,
						'download'    => $sub->metadata->download,
						'download_id' => $sub->metadata->download_id,
						'price_id'    => $sub->metadata->download_id,
						'old_sub_id'  => $subscription->profile_id,
						'caller'      => __CLASS__ . '|' . __METHOD__ . '|' . __LINE__ . '|' . EDD_RECURRING_VERSION,
					)
				);

				// If the expiration date is in the future, we need to reactivate without charge

				if( current_time( 'timestamp' ) < $subscription->get_expiration_time() ) {

					if( ! empty( $sub->current_period_end ) ) {

						$args['trial_end'] = $sub->current_period_end;

					} else {

						$args['trial_end'] = strtotime( $subscription->get_expiration() );

					}

				}

				$stripe_sub = $c->subscriptions->create( $args );

				$subscription->update( array(
					'status'     => 'active',
					'profile_id' => $stripe_sub->id,
					'expiration' => date( 'Y-n-d H:i:s', $stripe_sub->current_period_end )
				) );

			} else {

				$sub->plan = $sub->plan->id;
				$sub->cancel_at_period_end = false;
				$sub->save();

				$subscription->update( array(
					'status'     => 'active',
					'expiration' => date( 'Y-n-d H:i:s', $sub->current_period_end )
				) );

			}

		} catch( Exception $e ) {
			wp_die( $e->getMessage(), __( 'Error', 'edd-recurring' ), array( 'response' => 403 ) );
		}

		return true;
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

		try {

			$invoices = \Stripe\Invoice::all( array( 'limit' => 3, 'subscription' => $subscription->profile_id ) );

			if( $invoices ) {

				foreach( $invoices->data as $invoice ) {

					if( $invoice->paid ) {
						continue;
					}

					$payment = $invoice->pay();

					if( $payment && $payment->paid ) {
						$payment_id = $subscription->add_payment( array(
							'transaction_id' => $payment->charge,
							'amount'         => $payment->total / 100,
							'gateway'        => 'stripe'
						) );

						$subscription->renew( $payment_id );
					}

					$result = true;

				}

			}

		} catch( Exception $e ) {

			$result = new WP_Error( 'edd_recurring_stripe_error', $e->getMessage() );

		}

		return $result;
	}

	/**
	 * Get the expiration date with Stripe
	 *
	 * @since  2.6.6
	 * @param  object $subscription The subscription object
	 * @return string Expiration date or WP_Error if something went wrong
	 */
	public function get_expiration( $subscription ) {

		try {

			$subscription = \Stripe\Subscription::retrieve( $subscription->profile_id );

		} catch( Exception $e ) {

			return new WP_Error( 'edd_recurring_stripe_error', $e->getMessage() );

		}

		return date( 'Y-n-d H:i:s', $subscription->current_period_end );
	}

	/**
	 * Determines if the subscription can be updated
	 *
	 * @access      public
	 * @since       2.4
	 * @return      bool
	 */
	public function can_update( $ret, $subscription ) {
		if( $subscription->gateway === 'stripe' && ! empty( $subscription->profile_id ) && ( 'active' === $subscription->status || 'failing' === $subscription->status || 'trialling' === $subscription->status ) ) {
			return true;
		}
		return $ret;
	}

	/**
	 * Refund charges and cancel subscription when refunding via View Order Details
	 *
	 * @access      public
	 * @since       2.4.11
	 * @return      void
	 */
	public function process_refund( EDD_Payment $payment ) {

		if( empty( $_POST['edd_refund_in_stripe'] ) ) {
			return;
		}
		$statuses = array( 'edd_subscription', 'publish', 'revoked' );

		if ( ! in_array( $payment->old_status, $statuses ) ) {
			return;
		}

		if( 'stripe' !== $payment->gateway ) {
			return;
		}

		switch( $payment->old_status ) {

			case 'edd_subscription' :

				// Refund renewal payment
				if( empty( $payment->transaction_id ) || $payment->transaction_id == $payment->ID ) {

					// No valid charge ID
					return;
				}

				$ch = \Stripe\Charge::retrieve( $payment->transaction_id );

				try {

					$ch->refund();

					$payment->add_note( sprintf( __( 'Charge %s refunded in Stripe.', 'edd-recurring' ), $payment->transaction_id ) );

				} catch ( Exception $e ) {

					// some sort of other error
					$body = $e->getJsonBody();
					$err  = $body['error'];

					if( isset( $err['message'] ) ) {
						$error = $err['message'];
					} else {
						$error = __( 'Something went wrong while refunding the Charge in Stripe.', 'edd-recurring' );
					}

					wp_die( $error, __( 'Error', 'edd-recurring' ) , array( 'response' => 400 ) );

				}

				break;

			case 'publish' :
			case 'revoked' :

				// Refund initial subscription purchase

				$db   = new EDD_Subscriptions_DB;
				$subs = $db->get_subscriptions( array( 'parent_payment_id' => $payment->ID, 'number' => 100 ) );

				if( empty( $subs ) ) {

					return;

				}

				foreach( $subs as $subscription ) {

					// Refund charge
					$ch = \Stripe\Charge::retrieve( $subscription->transaction_id );

					try {

						$ch->refund();

						$payment->add_note( sprintf( __( 'Charge %s refunded in Stripe.', 'edd-recurring' ), $subscription->transaction_id ) );

					} catch ( Exception $e ) {

						// some sort of other error
						$body = $e->getJsonBody();
						$err  = $body['error'];

						if( isset( $err['message'] ) ) {
							$error = $err['message'];
						} else {
							$error = __( 'Something went wrong while refunding the Charge in Stripe.', 'edd-recurring' );
						}

						$payment->add_note( sprintf( __( 'Charge %s could not be refunded in Stripe. Error: %s', 'edd-recurring' ), $subscription->transaction_id, $error ) );

					}

					// Cancel subscription
					$this->cancel( $subscription, true );
					$subscription->cancel();
					$payment->add_note( sprintf( __( 'Subscription %d cancelled.', 'edd-recurring' ), $subscription->id ) );

				}

				break;

		}

	}

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

		// edds_credit_card_form() only shows when Stripe Checkout is enabled so we fake it
		add_filter( 'edd_get_option_stripe_checkout', '__return_false' );

		edds_credit_card_form();

	}

	/**
	 * Outputs any information after the Credit Card Fields
	 *
	 * @since  2.4
	 * @return void
	 */
	public function after_cc_fields() {

		if ( ! is_user_logged_in() ) {
			return;
		}

		// If we're on the checkout and there are no subscriptions in the cart, return
		$cart_contains_recurring = EDD_Recurring()->cart_contains_recurring();
		$is_checkout             = ( isset( $_POST['action'] ) && 'edd_load_gateway' === $_POST['action'] ) || edd_is_checkout();

		if ( $is_checkout && ! $cart_contains_recurring ) {
			return;
		}

		if( $is_checkout && $this->id !== edd_get_chosen_gateway() ) {
			return;
		}

		ob_start();
		$subscriber    = new EDD_Recurring_Subscriber( get_current_user_id(), true );
		$subscriptions = $subscriber->get_subscriptions();

		if ( ! empty( $subscriptions ) ) {
			$notice_subs = array();
			foreach ( $subscriptions as $sub ) {

				if ( 'active' === $sub->status && $sub->gateway === $this->id ) {
					if ( ! $is_checkout && ( $sub->id === $_GET['subscription_id'] ) ) {
						continue;
					}

					$notice_download = new EDD_Download( $sub->product_id );
					$notice_subs[]   = $notice_download->post_title;
				}

			}
			$sub_count   = count( $notice_subs );
			$show_notice = $sub_count > 0 ? true: false;

			if ( apply_filters( 'edd_recurring_show_stripe_update_payment_method_notice', $show_notice, $notice_subs ) ) {
				$notice_subs = implode( ', ', $notice_subs );
				?>
				<div class="edd-alert edd-alert-warn">
					<p>
						<input type="hidden" name="edds_has_other_subs" value="1" />
						<input type="checkbox" id="edds-confirm-update-default" name="edds_confirm_update_source" value="1" />
						<label for="edds-confirm-update-default">
							<?php
							if ( $is_checkout ) {
								printf( _n( 'I acknowledge that by purchasing this subscription, my current subscription will also be updated to use this payment method for renewals: %s',
									'I acknowledge that by purchasing this subscription, my current subscriptions will also be updated to use this payment method for renewals: %s',
									$sub_count,
									'edd-recurring' ),
									$notice_subs );
							} else {
								printf( _n( 'I acknowledge that by updating this subscription, the following subscription will also be updated to use this payment method for renewals: %s',
									'I acknowledge that by updating this subscription, the following subscriptions will also be updated to use this payment method for renewals: %s',
									$sub_count,
									'edd-recurring' ),
									$notice_subs );
							}
							?>
						</label>
					</p>
				</div>
				<?php
			}
		}
		echo ob_get_clean();
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

		if ( ! isset( $_POST['edd_stripe_token'] ) && ! isset( $_POST['edd_stripe_existing_card'] ) ) {
			edd_set_error( 'edd_recurring_missing_stripe_token', __( 'Please complete all required fields.', 'edd-recurring' ) );
		}

		if ( isset( $_POST['edds_has_other_subs'] ) && empty( $_POST['edds_confirm_update_source'] ) ) {
			edd_set_error( 'edds-confirm-sub-update', __( 'Please confirm your acknowledgement of the current subscription payment method changes', 'edd-recurring' ) );
		}

		$errors = edd_get_errors();

		if ( empty( $errors ) ) {

			$customer_id = $subscriber->get_recurring_customer_id( $this->id );

			if( empty( $customer_id ) ) {

				// We were unable to retrieve the customer ID from meta so let's pull it from the API
				try {

					$sub = \Stripe\Subscription::retrieve( $subscription->profile_id );
					$customer_id = $sub->customer;
					$subscriber->set_recurring_customer_id( $customer_id, 'stripe' );

				} catch( Exception $e ) {

					edd_set_error( 'edd_recurring_stripe_error', $e->getMessage() );
					return;
				}
			}

			$cu = \Stripe\Customer::retrieve( $customer_id );

			// No errors in stripe, continue on through processing
			try {

				if ( isset( $_POST['edd_stripe_token'] ) ) {
					$card = $cu->sources->create( array(
						'source'   => $_POST['edd_stripe_token'],
						'metadata' => array(
							'caller' => __CLASS__ . '|' . __METHOD__ . '|' . __LINE__ . '|' . EDD_RECURRING_VERSION,
						)
					) );
					$cu->default_source = $card->id;
				} elseif ( isset( $_POST['edd_stripe_existing_card'] ) ) {
					$cu->default_source = $_POST[ 'edd_stripe_existing_card' ];
				}

				$cu->save();


				// Reattempt unpaid charges if this subscription is failing
				$subscription->retry();

			} catch ( \Stripe\Error\Card $e ) {

				$body = $e->getJsonBody();
				$err  = $body['error'];

				if( isset( $err['message'] ) ) {
					edd_set_error( 'payment_error', $err['message'] );
				} else {
					edd_set_error( 'payment_error', __( 'There was an error processing your payment, please ensure you have entered your card number correctly.', 'edd-recurring' ) );
				}

			} catch ( \Stripe\Error\ApiConnection $e ) {

				$body = $e->getJsonBody();
				$err  = $body['error'];

				edd_set_error( 'payment_error', __( 'There was an error processing your payment (Stripe\'s API is down), please try again', 'edd-recurring' ) );

			} catch ( \Stripe\Error\InvalidRequest $e ) {

				$body = $e->getJsonBody();
				$err  = $body['error'];

				// Bad Request of some sort. Maybe Christoff was here ;)
				if( isset( $err['message'] ) ) {
					edd_set_error( 'request_error', $err['message'] );
				} else {
					edd_set_error( 'request_error', __( 'The Stripe API request was invalid, please try again', 'edd-recurring' ) );
				}

			} catch ( \Stripe\Error\Api $e ) {

				$body = $e->getJsonBody();
				$err  = $body['error'];

				if( isset( $err['message'] ) ) {
					edd_set_error( 'request_error', $err['message'] );
				} else {
					edd_set_error( 'request_error', __( 'The Stripe API request was invalid, please try again', 'edd-recurring' ) );
				}

			} catch ( \Stripe\Error\Authentication $e ) {

				$body = $e->getJsonBody();
				$err  = $body['error'];

				// Authentication error. Stripe keys in settings are bad.
				if( isset( $err['message'] ) ) {
					edd_set_error( 'request_error', $err['message'] );
				} else {
					edd_set_error( 'api_error', __( 'The API keys entered in settings are incorrect', 'edd-recurring' ) );
				}

			} catch ( Exception $e ) {
				edd_set_error( 'update_error', __( 'There was an error with this payment method. Please try with another card.', 'edd-recurring' ) );
			}

		}

	}

	/**
	 * Verify that the user has acknowledged to updating their payment form as a default for all subscriptions
	 * ** Stripe Specific **
	 *
	 * @since  2.4
	 * @param  bool   $is_valid  If the data passed so far was valid from EDD Core
	 * @param  array  $post_data The array of $_POST sent by the form
	 * @return void
	 */
	public function confirm_default_payment_method_change( $is_valid, $post_data ) {

		if ( isset( $post_data['edds_has_other_subs'] ) && empty( $post_data['edds_confirm_update_source'] ) ) {
			edd_set_error( 'edds-confirm-sub-update', __( 'Please confirm your acknowledgement of the current subscription payment method changes', 'edd-recurring' ) );
		}

	}

	/**
	 * Cancels subscription in Stripe when marked as completed
	 *
	 * @access      public
	 * @since       2.4.15
	 * @return      bool
	 */
	public function cancel_on_completion( $subscription_id, $subscription ) {

		if ( $subscription->gateway !== $this->id ) {
			return;
		}

		return $this->cancel( $subscription, true );

	}

	/**
	 * Link the recurring profile in Stripe.
	 *
	 * @since  2.4.4
	 * @param  string $profile_id   The recurring profile id
	 * @param  object $subscription The Subscription object
	 * @return string               The link to return or just the profile id
	 */
	public function link_profile_id( $profile_id, $subscription ) {

		if( ! empty( $profile_id ) ) {
			$payment    = new EDD_Payment( $subscription->parent_payment_id );
			$html       = '<a href="%s" target="_blank">' . $profile_id . '</a>';
			$base_url   = 'test' === $payment->mode ? 'https://dashboard.stripe.com/test/' : 'https://dashboard.stripe.com/';
			$link       = esc_url( $base_url . 'subscriptions/' . $profile_id );
			$profile_id = sprintf( $html, $link );
		}

		return $profile_id;

	}

	/**
	 * Looks up the transaction ID for a subscription record by the profile ID.
	 *
	 * @since  2.4.11
	 * @param  string $profile_id The recurring profile id
	 * @return object|false EDD_Subsciption object or false if no updates are made
	 */
	public function check_transaction_id( $profile_id = '' ) {

		if( empty( $profile_id ) ) {
			return false;
		}

		$subscription = new EDD_Subscription( $profile_id, true );

		if( ! $subscription || ! $subscription->id > 0 ) {
			return false;
		}

		if( ! empty( $subscription->transaction_id ) ) {
			return false;
		}

		// Subscription is missing its transaction ID, let's call the Stripe API to retrieve it

		$subscriber  = new EDD_Recurring_Subscriber( $subscription->customer_id );
		$customer_id = $subscriber->get_recurring_customer_id( 'stripe' );

		if( empty( $customer_id ) ) {
			return false;
		}

		try {

			$customer   = \Stripe\Customer::retrieve( $customer_id );
			$invoices   = \Stripe\Invoice::all( array( 'customer' => $customer_id, 'limit' => 20 ) );

			if( ! empty( $invoices->data ) ) {

				foreach( $invoices->data as $invoice ) {

					if( empty( $invoice->subscription ) ) {
						continue;
					}

					if( $profile_id != $invoice->subscription ) {
						continue;
					}

					if( empty( $invoice->charge ) ) {
						continue;
					}

					$subscription->update( array( 'transaction_id' => $invoice->charge ) );
					$subscription->transaction_id = $invoice->charge;
					break;

				}

			}

		} catch( Exception $e ) {
			return false;
		}

		return $subscription;

	}

	/**
	 * Determines if the subscription data needs checked against Stripe's database.
	 *
	 * Right now this only checks if the transaction ID is missing and retrieves it. In the future this could also check status, expiration date, etc.
	 *
	 * @since  2.4.11
	 * @param  object $subscription The EDD_Subscription object
	 * @return void
	 */
	public function maybe_check_subscription( EDD_Subscription $subscription ) {

		if( ! $subscription || ! $subscription->id > 0 ) {
			return;
		}

		if( ! empty( $subscription->transaction_id ) ) {
			return;
		}

		if( 'stripe' !== $subscription->gateway ) {
			return;
		}

		if( empty( $subscription->profile_id ) ) {
			return;
		}

		if( ! empty( $subscription->transaction_id ) ) {
			return;
		}

		// Make sure we don't cause an infinite loop
		remove_action( 'edd_recurring_setup_subscription', array( $this, 'maybe_check_subscription' ), 10 );

		if( $this->check_transaction_id( $subscription->profile_id ) ) {

			// Remove the scheduled event for this subscription if it hasn't already run
			wp_clear_scheduled_hook( 'edd_recurring_stripe_check_txn', array( $subscription->profile_id ) );

		}

		add_action( 'edd_recurring_setup_subscription', array( $this, 'maybe_check_subscription' ) );

	}

}
$edd_recurring_stripe = new EDD_Recurring_Stripe;
