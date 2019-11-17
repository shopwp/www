<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $edd_recurring_stripe;

class EDD_Recurring_Stripe extends EDD_Recurring_Gateway {

	/**
	 * Store \EDD_Payment object once retrieved.
	 *
	 * @since 2.9.0
	 *
	 * @type \EDD_Payment
	 */
	private $payment;

	/**
	 * Store \EDD_Subscriber object once retrieved.
	 *
	 * @since 2.9.0
	 *
	 * @type \EDD_Recurring_Subscriber
	 */
	private $subscriber;

	/**
	 * Registers additionally supported functionalities for specific gateways.
	 *
	 * @since 2.9.0
	 *
	 * @type array
	 */
	public $supports = array();

	/**
	 * Ensures Easy Digital Downloads - Stripe Payment Gateway is active.
	 *
	 * @since unknown
	 */
	public function __construct() {
		if ( ! defined( 'EDDS_PLUGIN_DIR' ) ) {
			return;
		}

		parent::__construct();

		// Ensure Stripe 2.7.0+ is available.
		add_filter( 'edd_enabled_payment_gateways', array( $this, '_require_stripe_270' ), 20 );
		add_action( 'admin_notices', array( $this, '_require_stripe_270_notice' ) );
	}

	/**
	 * Registers gateway and hooks.
	 *
	 * @since unknown
	 */
	public function init() {
		$this->id            = 'stripe';
		$this->friendly_name = __( 'Stripe', 'edd-recurring' );
		$this->supports      = array(
			'mixed_cart',
		);

		// Watch for subscription payment method updates.
		add_action( 'wp_ajax_edd_recurring_update_subscription_payment_method', array( $this, 'update_subscription_payment_method' ) );

		// Auto register before the initial parent payment is created.
		add_action( 'edds_pre_process_purchase_form', array( $this, 'auto_register' ) );

		// Bail early if the \Stripe\Customer currency does not match the stores.
		add_action( 'edds_process_purchase_form_before_intent', array( $this, 'check_customer_currency' ), 10, 2 );

		// Purchase flow:

		// 0. Adjust \Stripe\PaymentIntent behavior for the parent \EDD_payment.
		add_filter( 'edds_create_payment_intent_args', array( $this, 'create_payment_intent_args' ), 10, 2 );

		// 1. Create \EDD_Subscription(s) on initial gateway processing.
		// 2. Create \Stripe\Subscription(s).
		//    Remove any \EDD_Subscription(s) that no longer have a corresponding \Stripe\Subscription.
		add_action( 'edds_payment_created', array( $this, 'process_purchase_form' ), 20, 2 );

		// 3. Capture original \Stripe\PaymentIntent using an amount equal to the number of \Stripe\Subscription(s) created.
		add_action( 'edds_capture_payment_intent', array( $this, 'capture_payment_intent' ) );

		// 4. Transition created \EDD_Subscriptions to their next status.
		add_action( 'edds_payment_complete', array( $this, 'complete_subscriptions' ) );

		add_action( 'edd_pre_refund_payment', array( $this, 'process_refund' ) );
		add_action( 'edd_recurring_stripe_check_txn', array( $this, 'check_transaction_id' ) );
		add_action( 'edd_recurring_setup_subscription', array( $this, 'maybe_check_subscription' ) );
		add_action( 'edd_subscription_completed', array( $this, 'cancel_on_completion' ), 10, 2 );
	}

	/**
	 * Removes Stripe from active gateways if the base gateway < 2.7.0
	 *
	 * @since 2.9.0
	 *
	 * @param array $enabled_gateways Enabled gateways that allow purchasing.
	 * @return array
	 */
	public function _require_stripe_270( $enabled_gateways ) {
		if (
			isset( $enabled_gateways['stripe'] ) &&
			defined( 'EDD_STRIPE_VERSION' ) &&
			! version_compare( EDD_STRIPE_VERSION, '2.6.20', '>' )
		) {
			unset( $enabled_gateways['stripe'] );
		}

		return $enabled_gateways;
	}

	/**
	 * Adds notice if the base gateway < 2.7.0
	 *
	 * @since 2.9.0
	 */
	public function _require_stripe_270_notice() {
		remove_filter( 'edd_enabled_payment_gateways', array( $this, '_require_stripe_270' ), 20 );
		$enabled_gateways = edd_get_enabled_payment_gateways();
		add_filter( 'edd_enabled_payment_gateways', array( $this, '_require_stripe_270' ), 20 );

		if (
			isset( $enabled_gateways['stripe'] ) &&
			defined( 'EDD_STRIPE_VERSION' ) &&
			! version_compare( EDD_STRIPE_VERSION, '2.6.20', '>' )
		) {
			echo '<div class="notice notice-error">';

			echo wpautop( wp_kses(
				sprintf(
					/* translators: %1$s Opening strong tag, do not translate. %2$s Closing strong tag, do not translate. */
					__( '%1$sCredit card payments with Stripe are currently disabled.%2$s', 'edd-recurring' ),
					'<strong>',
					'</strong>'
				)
				. '<br />' .
				sprintf(
					/* translators: %1$s Opening code tag, do not translate. %2$s Closing code tag, do not translate. */
					__( 'To continue accepting recurring credit card payments with Stripe please update the Stripe Payment Gateway extension to version %1$s2.7%2$s.', 'edd-recurring' ),
					'<code>',
					'</code>'
				),
				array(
					'br'     => true,
					'strong' => true,
					'code'   => true,
				)
			) );

			echo '</div>';
		}
	}

	// Override methods that are automatically called in the parent class.
	public function process_checkout( $purchase_data ) {}
	public function complete_signup() {}
	public function create_payment_profiles() {}
	public function record_signup() {}

	/**
	 * Ensure subsequent API requests use the correct information.
	 *
	 * @todo https://github.com/easydigitaldownloads/edd-stripe/issues/391
	 * @since 2.9.0
	 */
	public function setup_stripe_api() {
		if ( edd_is_test_mode() ) {
			$secret_key = trim( edd_get_option( 'test_secret_key' ) );
		} else {
			$secret_key = trim( edd_get_option( 'live_secret_key' ) );
		}

		\Stripe\Stripe::setApiVersion( EDD_STRIPE_API_VERSION );
		\Stripe\Stripe::setApiKey( $secret_key );
		\Stripe\Stripe::setAppInfo( 'Easy Digital Downloads - Stripe', EDD_STRIPE_VERSION, esc_url( site_url() ), EDD_STRIPE_PARTNER_ID );
	}

	/**
	 * Run Auto Register early to avoid creating additional unused records.
	 *
	 * This is also hooked in to `edd_recurring_pre_create_payment_profiles` by the
	 * plugin integration `EDD_Recurring_Auto_Register` but will not do anything
	 * the second time it is run.
	 *
	 * @since 2.9.0
	 * @throws \Exception If the email address being used at checkout already has a user, throw an exception.
	 */
	public function auto_register() {
		if ( ! function_exists( 'edd_auto_register' ) ) {
			return;
		}

		$purchase_data = edd_get_purchase_session();

		if ( ! edd_recurring()->is_purchase_recurring( $purchase_data ) ) {
			return;
		}

		// Attempt to auto log in if the account is new.
		add_filter( 'edd_auto_register_login_user', '__return_true' );

		edd_auto_register()->create_user( $purchase_data );

		// If auto register found an existing account it cannot be logged in.
		if ( ! is_user_logged_in() ) {
			/* translators: %1$s Email address of an existing account used during checkout. */
			throw new \Exception( sprintf( __( 'A customer account for %1$s already exists. Please log in to complete your purchase.', 'edd-recurring' ), esc_html( $purchase_data['user_email'] ) ) );
		}
	}

	/**
	 * Check the customer currency prior to allowing checkout.
	 *
	 * If a customer has previously purchased a subscription, any future subscriptions must be made in the same currency.
	 *
	 * @since 2.9.0
	 * @throws \Exception If the Stripe customer currency does not match the currency attempting to checkout, throw an Exception.
	 *
	 * @param array            $purchase_data Purchase data.
	 * @param \Stripe\Customer $customer Stripe Customer object.
	 */
	public function check_customer_currency( $purchase_data, $customer ) {
		if ( ! edd_recurring()->is_purchase_recurring( $purchase_data ) ) {
			return;
		}

		// First purchase, \Stripe\Customer has not taken an action that assigns
		// a currency, so any currency purchase can be made.
		if ( ! $customer->currency ) {
			return;
		}

		$store_currency    = strtolower( edd_get_currency() );
		$customer_currency = strtolower( $customer->currency );

		if ( $customer_currency !== $store_currency ) {
			throw new \Exception(
				sprintf(
					/* translators: %1$s Customer currency. */
					__( 'Unable to complete your purchase. Your order must be completed in %1$s.', 'edd-recurring' ),
					strtoupper( $customer->currency )
				)
			);
		}
	}

	/**
	 * Sets the PaymentIntent capture method to manual.
	 *
	 * Creating \Stripe\Subscriptions can fail individually.
	 * Capturing after all attempts have been made ensures we only charge
	 * for fulfilled items.
	 *
	 * @since 2.9.0
	 *
	 * @param array $payment_intent_args PaymentIntent creation arguments.
	 * @param array $purchase_data       Cart purchase data.
	 * @return array
	 */
	public function create_payment_intent_args( $payment_intent_args, $purchase_data ) {
		if ( edd_recurring()->is_purchase_recurring( $purchase_data ) ) {
			$payment_intent_args['capture_method'] = 'manual';
		}

		return $payment_intent_args;
	}

	/**
	 * Handles creating EDD_Subscription and \Stripe\Subscription records
	 * on checkout form submission.
	 *
	 * @since 2.9.0
	 *
	 * @param array                                     $purchase_data Purchase data.
	 * @param \EDD_Payment                              $payment EDD Payment.
	 * @param \Stripe\PaymentIntent|\Stripe\SetupIntent $intent Created Stripe Intent.
	 */
	public function process_purchase_form( $payment, $intent ) {
		$purchase_data = edd_get_purchase_session();

		if ( ! edd_recurring()->is_purchase_recurring( $purchase_data ) ) {
			return;
		}

		// Store for direct access later.
		$this->payment       = $payment;
		$this->payment_id    = $payment->ID;
		$this->purchase_data = $purchase_data;

		$this->purchase_data = apply_filters( 'edd_recurring_purchase_data', $purchase_data, $this );
		$this->user_id       = $this->purchase_data['user_info']['id'];
		$this->email         = $this->purchase_data['user_info']['email'];

		// Never let a user_id be lower than 0 since WP Core absints when doing get_user_meta lookups
		if ( $this->purchase_data['user_info']['id'] < 1 ) {
			$this->purchase_data['user_info']['id'] = 0;
		}

		do_action( 'edd_recurring_process_checkout', $this->purchase_data, $this );

		$errors = edd_get_errors();

		// Throw an exception with the latest error (for backwards compat with `edd_recurring_process_checkout`).
		if ( $errors ) {
			throw new \Exception( current( edd_get_errors() ) );
		}

		// Use cart purchase data to find EDD_Customer and EDD_Recurring_Subscriber.
		$this->setup_customer_subscriber();

		// Map cart purchase data to gateway object (this).
		$this->build_subscriptions();

		// Use mapped data to create EDD_Subscription records.
		$this->create_edd_subscriptions();

		// Save any custom meta added via hooks.
		$this->payment->update_meta( '_edd_subscription_payment', true );

		if ( ! empty( $this->custom_meta ) ) {
			foreach ( $this->custom_meta as $key => $value ) {
				$this->payment->update_meta( $key, $value );
			}
		}

		// Use mapped data to create \Stripe\Subscription records.
		$this->create_stripe_subscriptions( $intent );

		// There is a bug in EDD core that causes adjusting tax amounts on
		// individual line items to improperly recalculate total taxes.
		//
		// Line item amounts are adjusted when a Subscription has a free trial
		// so the total amount captured is accurate.
		//
		// Set the value directly instead.
		//
		// @link https://github.com/easydigitaldownloads/easy-digital-downloads/issues/7385
		if ( edd_recurring()->cart_has_free_trial() ) {
			$this->payment->tax = 0;
			$this->payment->total = 0;
		}

		// Save any changes to parent \EDD_Payment.
		$this->payment->save();
	}

	/**
	 * Sets up EDD_Customer (ID only) and EDD_Recurring_Subscriber based on purchase data.
	 *
	 * @todo This is not gateway-specific and can be moved up.
	 *
	 * @since 2.9.0
	 */
	public function setup_customer_subscriber() {
		if ( empty( $this->user_id ) ) {
			$subscriber = new EDD_Recurring_Subscriber( $this->email );
		} else {
			$subscriber = new EDD_Recurring_Subscriber( $this->user_id, true );
		}

		if ( empty( $subscriber->id ) ) {
			$name = '';

			if ( ! empty( $this->purchase_data['user_info']['first_name'] ) ) {
				$name = $this->purchase_data['user_info']['first_name'];
			}

			if ( ! empty( $this->purchase_data['user_info']['last_name'] ) ) {
				$name .= ' ' . $this->purchase_data['user_info']['last_name'];
			}

			$subscriber_data = array(
				'name'        => $name,
				'email'       => $this->purchase_data['user_info']['email'],
				'user_id'     => $this->user_id,
			);

			$subscriber->create( $subscriber_data );
		}

		$this->subscriber  = $subscriber;
		$this->customer_id = $subscriber->id;
	}

	/**
	 * Maps/normalizes cart data to a list of subscription data.
	 *
	 * @todo This is not gateway-specific and can be moved up.
	 *
	 * @since 2.9.0
	 */
	public function build_subscriptions() {
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

			if ( ! empty( $item['fees'] ) ) {
				foreach ( $item['fees'] as $fee ) {

					// Negative fees are already accounted for on $item['price']
					if ( $fee['amount'] <= 0 ) {
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
				'transaction_id'     => $this->payment->transaction_id, // No charges are created for the Subscription initially, so use the parent payment's transaction ID.
			);

			$args = apply_filters( 'edd_recurring_subscription_pre_gateway_args', $args, $item );

			if ( ! edd_get_option( 'recurring_one_time_trials' ) || ! $this->subscriber->has_trialed( $item['id'] ) ) {

				// If the item in the cart has a free trial period
				if ( ! empty( $item['item_number']['options']['recurring']['trial_period']['unit'] ) && ! empty( $item['item_number']['options']['recurring']['trial_period']['quantity'] ) ) {

					$args['has_trial']         = true;
					$args['trial_unit']        = $item['item_number']['options']['recurring']['trial_period']['unit'];
					$args['trial_quantity']    = $item['item_number']['options']['recurring']['trial_period']['quantity'];
					$args['status']            = 'trialling';
					$args['initial_amount']    = 0;
					$args['initial_tax_rate']  = 0;
					$args['initial_tax']       = 0;
				}

			}

			$this->subscriptions[ $key ] = $args;
		}
	}

	/**
	 * Creates EDD_Subscription records.
	 *
	 * @todo This is not gateway-specific and can be moved up.
	 */
	public function create_edd_subscriptions() {
		/*
		 * We need to delete pending subscription records to prevent duplicates. This ensures no duplicate subscription records are created when a purchase is being recovered. See:
		 * https://github.com/easydigitaldownloads/edd-recurring/issues/707
		 * https://github.com/easydigitaldownloads/edd-recurring/issues/762
		 */
		global $wpdb;

		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}edd_subscriptions WHERE parent_payment_id = %d AND status = 'pending';", $this->payment_id ) );

		// Now create the subscription record(s)
		foreach ( $this->subscriptions as $key => $subscription ) {

			if( isset( $subscription['status'] ) ) {
				$status = $subscription['status'];
			} else {
				$status = 'pending';
			}

			$trial_period = ! empty( $subscription['has_trial'] ) ? $subscription['trial_quantity'] . ' ' . $subscription['trial_unit'] : '';

			$args = array(
				'product_id'            => $subscription['id'],
				'price_id'              => isset( $subscription['price_id'] ) ? $subscription['price_id'] : null,
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
				'expiration'            => $this->subscriber->get_new_expiration( $subscription['id'], $subscription['price_id'], $trial_period ),
				'trial_period'          => $trial_period,
				'profile_id'            => $subscription['profile_id'],
				'transaction_id'        => $subscription['transaction_id'],
			);

			$args = apply_filters( 'edd_recurring_pre_record_signup_args', $args, $this );

			$sub = $this->subscriber->add_subscription( $args );

			if( ! $this->offsite && $trial_period ) {
				$this->subscriber->add_meta( 'edd_recurring_trials', $subscription['id'] );
			}

			// Track newly created \EDD_Subscription in the gateway object.
			$this->subscriptions[ $key ]['edd_subscription'] = $sub;
		}
	}

	/**
	 * Creates \Stripe\Subscription records.
	 *
	 * @since 2.9.0
	 *
	 * @param \Stripe\PaymentIntent Stripe PaymentIntent, used to retrieve the parent \EDD_Payment
	 */
	public function create_stripe_subscriptions( $intent ) {
		/** This action is documented in incldues/gateways/edd-recurring-gateway.php */
		do_action( 'edd_recurring_pre_create_payment_profiles', $this );

		// Retrieve the \Stripe\Customer used to create the \Stripe\PaymentIntent.
		//
		// Could use ID directly to avoid another API request, however
		// the full object is needed for the `edd_recurring_create_stripe_subscription_args`
		// filter below.
		$customer = $this->get_customer( $intent->customer );

		// Sync the gateway's recurring customer ID with the subscriber.
		$this->subscriber->set_recurring_customer_id( $customer->id, $this->id );

		// Ensure that one-time purchases through Stripe use the same customer ID.
		if ( function_exists( 'edd_stripe_get_customer_key' ) ) {
			update_user_meta( $this->user_id, edd_stripe_get_customer_key(), $customer->id );
			$this->subscriber->update_meta( edd_stripe_get_customer_key(), $customer->id );
		}

		// Ensure we use the correct API information.
		$this->setup_stripe_api();

		foreach( $this->subscriptions as $key => $subscription ) {
			try {
				$plan_id = $this->get_plan_id( $subscription );

				$args = array(
					'customer'               => $customer->id,
					'default_payment_method' => $intent->payment_method,
					'off_session'            => true,
					'items'                  => array(
						array(
							'plan'     => $plan_id,
							'quantity' => 1,
						)
					),
					'metadata'               => array(
						'payment_key' => $this->purchase_data['purchase_key'],
						'download'    => $subscription['name'],
						'download_id' => $subscription['id'],
						'price_id'    => $subscription['price_id'],
						'caller'      => __CLASS__ . '|' . __METHOD__ . '|' . __LINE__ . '|' . EDD_RECURRING_VERSION,
					)
				);

				if ( ! empty( $subscription['has_trial'] ) ) {
					$args['trial_end'] = strtotime( '+' . $subscription['trial_quantity'] . ' ' . $subscription['trial_unit'] );
				} else {

					if ( 'quarter' === $subscription['period'] ) {
						$args['trial_end'] = strtotime( '+ 3 months' );
					} else if ( 'semi-year' === $subscription['period'] ) {
						$args['trial_end'] = strtotime( '+ 6 months' );
					} else {
						$args['trial_end'] = strtotime( '+ 1 ' . $subscription['period'] );
					}

				}

				/**
				 * Filters the arguments used to create all Recurring subscriptions.
				 *
				 * @since unknown
				 *
				 * @param array  $args       Arguments used to create the gateway-specific Subscription record.
				 * @param array  $downloads  Cart downloads.
				 * @param string $id         Gateway ID.
				 * @param string $product_id Download ID.
				 * @param string $price_id   Download price ID.
				 */
				$args = apply_filters(
					'edd_recurring_create_subscription_args',
					$args,
					$this->purchase_data['downloads'],
					$this->id,
					$subscription['id'],
					$subscription['price_id']
				);

				/**
				 * Filters the arguments used to create \Stripe\Subscription records.
				 *
				 * @since unknown
				 *
				 * @param array  $args      Arguments used to create the \Stripe\Subscription.
				 * @param array  $downloads Cart downloads.
				 * @param string $id        Gateway ID.
				 * @param \Stripe\Customer  Stripe customer.
				 */
				$args = apply_filters(
					'edd_recurring_create_stripe_subscription_args',
					$args,
					$this->purchase_data,
					$customer
				);

				// Avoid sending unnecessary parameters to Stripe.
				if ( ! empty( $args['needs_one_time'] ) ) {
					unset( $args['needs_one_time'] );
					unset( $args['license_id'] );
				}

				$stripe_subscription = \Stripe\Subscription::create( $args );

				// Set profile ID.
				$subscription['edd_subscription']->update( array(
					'profile_id' => $stripe_subscription->id,
				) );

				wp_schedule_single_event( strtotime( '+2 minutes' ), 'edd_recurring_stripe_check_txn', array( $stripe_subscription->id ) );

				// Update parent \EDD_Payment downloads that have a trial.
				if ( ! empty( $subscription['has_trial'] ) ) {
					$this->payment->modify_cart_item( $key, array(
						'item_price' => 0,
						// Tax amount needs to be the same to avoid a bug in EDD core.
						// If the amount is less it will accidentally increase the value.
						//
						// @link https://github.com/easydigitaldownloads/easy-digital-downloads/issues/7385
						'tax'        => $subscription['initial_tax'],
						'price'      => 0,
						'discount'   => 0,
					) );
				}

			// Note any Subscription failures.
			} catch( \Exception $e ) {
				$this->failed_subscriptions[] = array(
					'key'          => $key,
					'error'        => $e->getMessage(),
					'subscription' => $subscription,
				);
			}
		}

		// Clean up subscriptions.
		foreach ( $this->failed_subscriptions as $failed_subscription ) {
			// Remove an EDD record to match other gateways that create a
			// record after talking to the gateway.
			$failed_subscription['subscription']['edd_subscription']->delete();

			$this->payment->add_note( sprintf( __( 'Failed creating subscription for %s. Gateway returned: %s', 'edd-recurring' ), $failed_subscription['subscription']['name'], $failed_subscription['error'] ) );

			$this->payment->remove_download( $failed_subscription['subscription']['id'], array(
				'price_id' => $failed_subscription['subscription']['price_id'],
			) );
		}

		$this->payment->update_meta( '_edd_recurring_failed_subscriptions', $this->failed_subscriptions );

		/** This action is documented in incldues/gateways/edd-recurring-gateway.php */
		do_action( 'edd_recurring_post_create_payment_profiles', $this );
	}

	/**
	 * Adjusts the capture amount for the \Stripe\PaymentIntent and captures.
	 *
	 * The parent \EDD_Payment record's current total is used to
	 * determine the amount that is captured.
	 *
	 * @since 2.9.0
	 *
	 * @param \Stripe\PaymentIntent $intent PaymentIntent to capture.
	 */
	public function capture_payment_intent( $intent ) {
		$payment_id = $intent->metadata->edd_payment_id;
		$payment    = edd_get_payment( $payment_id );

		if ( edds_is_zero_decimal_currency() ) {
			$amount = $payment->total;
		} else {
			$amount = round( $payment->total * 100, 0 );
		}

		// Capture amount must be positive (and over $0.50).
		// No Subscriptions were left on the Parent Payment Record.
		//
		// The cart is also manually cleared here to avoid confusion.
		if ( 0 === intval( $amount ) ) {
			$intent->cancel( array(
				'cancellation_reason' => 'abandoned',
			) );

			$payment->add_note( esc_html__( 'PaymentIntent cancelled because there is nothing to collect.', 'edd-recurring' ) );

			edd_empty_cart();
			return;
		}

		return $intent->capture( array(
			'amount_to_capture' => $amount,
		) );
	}

	/**
	 * Transitions \EDD_Subscription records to their next status when
	 * the parent \EDD_Payment record is transitioned.
	 *
	 * @since 2.9.0
	 *
	 * @param \EDD_Payment $parent_payment Parent payment.
	 */
	public function complete_subscriptions( $parent_payment ) {
		$purchase_data = edd_get_purchase_session();

		if ( ! edd_recurring()->is_purchase_recurring( $purchase_data ) ) {
			return;
		}

		$subscription_db = new EDD_Subscriptions_DB;
		$subscriptions   = $subscription_db->get_subscriptions( array(
			'parent_payment_id' => $parent_payment->ID,
		) );

		foreach ( $subscriptions as $subscription ) {
			$subscription->update( array(
				'status' => empty( $subscription->trial_period ) ? 'active' : 'trialling',
			) );
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

			// Ensure we use the correct API information.
			$this->setup_stripe_api();

			status_header( 200 );

			try {

				$event = \Stripe\Event::retrieve( $event_json->id );

			} catch ( Exception $e ) {

				die( 'Invalid event ID' );

			}

			// Create the object with a different data key based on the type of event sent.
			$data         = $event->data->object;
			$subscription = null;
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

			do_action( 'edd_pre_recurring_stripe_event', $event->type, $event, $subscription );
			do_action( 'edd_pre_recurring_stripe_event_' . $event->type, $event, $subscription );

			switch ( $event->type ) :

				case 'invoice.payment_failed' :

					$subscription->failing();
					$subscription->add_note( sprintf( __( 'Failing invoice URL: %s', 'edd-recurring' ), $event->data->object->hosted_invoice_url ) );

					do_action( 'edd_recurring_payment_failed', $subscription );
					do_action( 'edd_recurring_stripe_event_' . $event->type, $event );

					break;

				case 'invoice.payment_succeeded' :

					$subscription_id = ! empty( $data->subscription ) ? $event->data->object->subscription : false;

					// See if the trial is still in place before allowing a 0 transaction.
					// https://github.com/easydigitaldownloads/edd-recurring/issues/611
					$stripe_sub = ! empty( $event->data->object->subscription )
						? \Stripe\Subscription::retrieve( $event->data->object->subscription )
						: false;

					if ( 0 === (int) $data->total && ( $stripe_sub && current_time( 'timestamp' ) < $stripe_sub->trial_end ) ) {
						die( 'EDD Recurring: Initial Trial Invoice' );
					}

					$args = array(
						'amount'         => $data->total / 100,
						'transaction_id' => $data->charge,
					);

					if ( ! empty( $data->tax ) ) {
						$args['tax'] = $data->tax / 100;
					}

					// This is a renewal charge
					$payment_id = $subscription->add_payment( $args );

					if ( empty( $stripe_sub->metadata->reactivated ) || empty( $stripe_sub->metadata->reactivation_processed ) ) {

						if ( ! empty( $payment_id ) ) {

							// Renew the subscription but only if this is not a reactivation and we got a renewal payment ID.
							$subscription->renew( $payment_id );

						}

					} elseif ( ! empty( $stripe_sub->metadata->reactivated ) ) {

						// Set a flag so we know that this reactivation has been processed.
						\Stripe\Subscription::update( $stripe_sub->id, array(
							'metadata' => array(
								'reactivation_processed' => true,
							),
						) );
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

					if ( ! edds_is_zero_decimal_currency() ) {
						$new_amount /= 100;
					}

					$old_amount = edd_sanitize_amount( $old_amount );
					$new_amount = edd_sanitize_amount( $new_amount );

					if ( $new_amount !== $old_amount ) {
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

					// This is an uncaptured PaymentIntent, not a true refund.
					if ( ! $data->captured ) {
						return;
					}

					$charge = $data->charge;

					// Get the charge from the PaymentIntent if not available directly.
					if ( ! $charge ) {
						$payment_intent = $data->payment_intent;

						if ( $payment_intent ) {
							$payment_intent = \Stripe\PaymentIntent::retrieve( $payment_intent );
							$charge         = current( $payment_intent->charges->data )->id;
						}
					}

					$payment_id = edd_get_purchase_id_by_transaction_id( $charge );

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
	 * Retrieve the customer object from Stripe.
	 *
	 * @since 2.4
	 * @since 2.9.0 All payments go through the base Stripe gateway ensuring a
	 *              customer record is associated with each user.
	 *
	 * @param string $customer_id Optional \Stripe\Customer ID. If not supplied the current user record will be used.
	 * @return null|\Stripe\Customer Null if a saved customer ID reference cannot be found.
	 */
	public function get_customer( $customer_id = null ) {
		$customer = null;

		if ( ! $customer_id ) {
			$customer_id = edds_get_stripe_customer_id( get_current_user_id() );
		}

		if ( ! empty( $customer_id ) ) {
			// Ensure we use the correct API information.
			$this->setup_stripe_api();

			try {
				$customer = \Stripe\Customer::retrieve( $customer_id );
			} catch( \Exception $e ) {
				$customer = null;
			}
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
			// Ensure we use the correct API information.
			$this->setup_stripe_api();

			// Update the customer to ensure their card data is up to date
			$customer     = \Stripe\Customer::retrieve( $customer_id );
			$stripe_sub   = \Stripe\Subscription::retrieve( $subscription_id );

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
			// Ensure we use the correct API information.
			$this->setup_stripe_api();

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
	public function get_plan_args( $subscription = array(), $name, $plan_id = '' ) {
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

		// Ensure we use the correct API information.
		$this->setup_stripe_api();

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
			// Ensure we use the correct API information.
			$this->setup_stripe_api();

			$at_period_end = $subscription->status == 'failing' ? false : true;

			if ( $at_period_end ) {
				$sub = \Stripe\Subscription::update( $subscription->profile_id, array(
					'cancel_at_period_end' => true,
				) );
			} else {
				$sub = \Stripe\Subscription::retrieve( $subscription->profile_id );
				$sub->cancel();
			}

			// We must now loop through and cancel all unpaid invoice to ensure that additional payment attempts are not made
			$invoices = \Stripe\Invoice::all( array( 'subscription' => $subscription->profile_id ) );

			if ( $invoices ) {

				foreach ( $invoices->data as $invoice ) {

					// Skip paid invoices.
					if ( $invoice->paid ) {
						continue;
					}

					$invoice->voidInvoice();
				}

			}

		} catch( Exception $e ) {
			return false;
		}

		return true;

	}

	/**
	 * Determines if a subscription can be reactivated through the gateway.
	 *
	 * @since 2.6
	 *
	 * @param bool $ret                       True if the Subscription can be reactivated.
	 * @param \EDD_Subscription $subscription Subscription to determine reactivation status of.
	 *
	 * @return bool
	 */
	public function can_reactivate( $ret, $subscription ) {
		if ( $subscription->gateway !== 'stripe' || empty( $subscription->profile_id ) || 'cancelled' !== $subscription->status ) {
			return $ret;
		}

		$payment = edd_get_payment( $subscription->get_original_payment_id() );
		$status  = $payment->status;

		// Can't reactivate with a refunded or revoked original payment.
		if ( 'publish' !== $status && 'revoked' !== $status ) {
			return false;
		}

		// Can't reactivate a Subscription that was automatically cancelled as part of a
		// Software Licensing upgrade.
		$was_upgraded = $payment->get_meta( '_edd_sl_upgraded_to_payment_id' );

		if ( ! empty( $was_upgraded ) ) {
			return false;
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
		if( $subscription->gateway === 'stripe' && ! empty( $subscription->profile_id ) && 'failing' === $subscription->status ) {
			return true;
		}
		return $ret;
	}

	/**
	 * Reactivates a subscription.
	 *
	 * @access      public
	 * @since       2.6
	 *
	 * @param EDD_Subscription $subscription The EDD_Subscription object.
	 * @param boolean          $valid        A verification call that this call came from a valid source.
	 *
	 * @return boolean
	 */
	public function reactivate( $subscription, $valid ) {

		if ( empty( $valid ) ) {
			return false;
		}

		try {
			// Ensure we use the correct API information.
			$this->setup_stripe_api();

			$sub = \Stripe\Subscription::retrieve( $subscription->profile_id );

			// This Subscription was cancelled in Stripe, so we have to create a new subscription.
			if ( empty( $sub->cancel_at_period_end ) || in_array( $sub->status, array( 'canceled', 'incomplete', 'incomplete_expired' ), true ) ) {
				$args = array(
					'customer'               => $sub->customer,
					'items'                  => array(
						array(
							'plan'     => $sub->plan->id,
							'quantity' => $sub->quantity,
						)
					),
					'tax_percent'            => $sub->tax_percent,
					'default_payment_method' => $sub->default_payment_method,
					'default_source'         => $sub->default_source,
					'off_session'            => true,
					'metadata'               => array_merge(
						array(
							'reactivated' => true,
							'old_sub_id'  => $subscription->profile_id,
							'caller'      => __CLASS__ . '|' . __METHOD__ . '|' . __LINE__ . '|' . EDD_RECURRING_VERSION,
						),
						$sub->metadata->__toArray()
					),
				);

				// If the expiration date is in the future, we need to reactivate without charge.
				if ( current_time( 'timestamp' ) < $subscription->get_expiration_time() ) {
					if ( ! empty( $sub->current_period_end ) ) {
						$args['trial_end'] = $sub->current_period_end;
					} else {
						$args['trial_end'] = strtotime( $subscription->get_expiration() );
					}
				}

				$stripe_sub = \Stripe\Subscription::create( $args );

				// Subscription could not be fully reactivated.
				if ( 'incomplete' === $stripe_sub->status ) {
					$subscription->add_note( esc_html__( 'Subscription reactivation requires payment by customer and will be cancelled in 24 hours if no action is taken.', 'edd-recurring' ) );
				}

				$subscription->update(
					array(
						'status'     => 'incomplete' === $stripe_sub->status ? 'failing' : 'active',
						'profile_id' => $stripe_sub->id,
						'expiration' => date( 'Y-n-d H:i:s', $stripe_sub->current_period_end ),
					)
				);


			} else { // This Subscription is still active in Stripe, remove cancellation notice.
				\Stripe\Subscription::update(
					$sub->id,
					array(
						'cancel_at_period_end' => false,
					)
				);

				$subscription->update(
					array(
						'status'     => 'active',
						'expiration' => date( 'Y-n-d H:i:s', $sub->current_period_end ),
					)
				);
			}

		} catch ( Exception $e ) {
			wp_die( esc_html( $e->getMessage() ), esc_html( __( 'Error', 'edd-recurring' ) ), array( 'response' => 403 ) );
		}

		return true;
	}

	/**
	 * Retries a failing Subscription's latest invoice.
	 *
	 * This method is connected to a filter instead of an action so we can return a nice error message.
	 *
	 * @todo This uses a different amount of paid invoices than the Stripe Account settings may require.
	 *
	 * @access      public
	 * @since       2.8
	 *
	 * @param bool             $result       If the result was successful.
	 * @param EDD_Subscription $subscription The EDD_Subscription object to retry.
	 *
	 * @return      bool|WP_Error
	 */
	public function retry( $result, $subscription ) {
		if ( ! $this->can_retry( false, $subscription ) ) {
			return $result;
		}

		$subscriber  = new EDD_Recurring_Subscriber( $subscription->customer_id );
		$customer_id = $subscriber->get_recurring_customer_id( 'stripe' );

		if ( empty( $customer_id ) ) {
			return $result;
		}

		$void_past_due_invoices = true;

		/** This filter is documented in includes/gateways/edd-recurring-stripe.php */
		$void_past_due_invoices = apply_filters(
			'edd_recurring_stripe_void_past_due_invoices',
			$void_past_due_invoices,
			$subscription
		);

		try {
			// Ensure we use the correct API information.
			$this->setup_stripe_api();

			// Manual retries are limited to 7 days, so it's unlikely there will
			// be more invoices than that.
			$invoices = \Stripe\Invoice::all(
				array(
					'subscription' => $subscription->profile_id,
					'limit'        => 7,
					'status'       => 'open',
					'customer'     => $customer_id,
				)
			);

			if ( empty( $invoices->data ) ) {
				return $result;
			}

			$has_paid_invoice = false;

			foreach ( $invoices->data as $invoice ) {
				/* @var \Stripe\Invoice $invoice */

				// We have found an invoice and paid it, void the rest.
				if ( true === $has_paid_invoice && true === $void_past_due_invoices ) {
					$invoice->voidInvoice();
				} else {
					$paid_invoice = $invoice->pay(
						array(
							'off_session' => true,
						)
					);

					if ( 'paid' === $paid_invoice->status ) {
						$has_paid_invoice = true;
						$payment_intent   = \Stripe\PaymentIntent::retrieve( $paid_invoice->payment_intent );
						$charges          = $payment_intent->charges->data;

						if ( ! empty( $charges ) ) {
							$charge     = current( $charges );
							$payment_id = $subscription->add_payment(
								array(
									'transaction_id' => $charge->id,
									'amount'         => $paid_invoice->total / 100,
									'gateway'        => 'stripe',
								)
							);

							$subscription->renew( $payment_id );
						}
					}
				}
			}

			$result = $has_paid_invoice;
		} catch ( Exception $e ) {
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
			// Ensure we use the correct API information.
			$this->setup_stripe_api();

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
	 * Refund charges for renewals when refunding via View Order Details.
	 *
	 * @access      public
	 * @since       2.4.11
	 * @param       EDD_Payment $payment The EDD_Payment object that is being refunded.
	 * @return      void
	 */
	public function process_refund( EDD_Payment $payment ) {
		if ( empty( $_POST['edd_refund_in_stripe'] ) ) {
			return;
		}

		$statuses = array( 'edd_subscription' );

		if ( ! in_array( $payment->old_status, $statuses ) ) {
			return;
		}

		if ( 'stripe' !== $payment->gateway ) {
			return;
		}

		// Ensure we use the correct API information.
		$this->setup_stripe_api();

		switch( $payment->old_status ) {

			// Renewal.
			case 'edd_subscription' :

				// No valid charge ID.
				if ( empty( $payment->transaction_id ) || $payment->transaction_id == $payment->ID ) {
					return;
				}

				try {
					$ch = \Stripe\Charge::retrieve( $payment->transaction_id );
					$ch->refund();

					$payment->add_note( sprintf( __( 'Charge %s refunded in Stripe.', 'edd-recurring' ), $payment->transaction_id ) );
				} catch ( \Stripe\Error\Base $e ) {
					$body = $e->getJsonBody();
					$err  = $body['error'];

					if ( isset( $err['message'] ) ) {
						$error = $err['message'];
					} else {
						$error = __( 'Something went wrong while refunding the Charge in Stripe.', 'edd-recurring' );
					}

					wp_die( $error, __( 'Error', 'edd-recurring' ) , array( 'response' => 400 ) );
				}

				break;
		}

	}

	/**
	 * Outputs the payment method update form
	 *
	 * @since  2.4
	 * @param  EDD_Subscription object $subscription The subscription object.
	 * @return void
	 */
	public function update_payment_method_form( $subscription ) {
		if ( $subscription->gateway !== $this->id ) {
			return;
		}

		edd_stripe_js( true );

		wp_enqueue_script(
			'edd-frontend-recurring-stripe',
			EDD_RECURRING_PLUGIN_URL . 'assets/js/edd-frontend-recurring-stripe.js',
			array( 'jquery', 'edd-stripe-js' ),
			EDD_RECURRING_VERSION
		);

		wp_localize_script(
			'edd-frontend-recurring-stripe',
			'eddRecurringStripe',
			array(
				'i18n' => array(
					'loading' => esc_html__( 'Please Wait…', 'edd-recurring' ),
				),
			)
		);

		try {
			// Ensure we use the correct API information.
			$this->setup_stripe_api();

			$stripe_subscription = \Stripe\Subscription::retrieve(
				array(
					'id' => $subscription->profile_id,
				)
			);

			// Find the latest open (unpaid) invoice.
			// Scheduled invoices have no PaymentIntent, which Stripe can return in `latest_invoice`
			// on the Subscription object.
			//
			// When the form is submitted any previously stacked Past due invoices will be voided.
			//
			// @link https://github.com/easydigitaldownloads/edd-recurring/issues/1177.
			$latest_open_invoice = \Stripe\Invoice::all(
				array(
					'subscription' => $stripe_subscription->id,
					'limit'        => 1,
					'status'       => 'open',
					'customer'     => $stripe_subscription->customer,
				)
			);

			if ( ! empty( $latest_open_invoice->data ) ) {
				$invoice = current( $latest_open_invoice->data );

				if ( $invoice->payment_intent ) {
					$payment_intent = \Stripe\PaymentIntent::retrieve( $invoice->payment_intent );

					if ( 'succeeded' !== $payment_intent->status ) {
						echo '<input type="hidden" name="edd_recurring_stripe_payment_intent" value="' . esc_attr( $payment_intent->id ) . '" />';
					}
				}
			}

			echo '<input type="hidden" name="edd_recurring_stripe_profile_id" value="' . esc_attr( $stripe_subscription->id ) . '" />';
			echo '<input type="hidden" name="edd_recurring_stripe_default_payment_method" value="' . esc_attr( $stripe_subscription->default_payment_method ) . '" />';

			edds_credit_card_form();
		} catch ( \Exception $e ) {
			echo esc_html( $e->getMessage() );
		}
	}

	/**
	 * Updates a Subscription's default payment method.
	 */
	public function update_subscription_payment_method() {
		$subscription_id       = isset( $_POST['subscription_id'] ) ? sanitize_text_field( $_POST['subscription_id'] ) : false;
		$payment_method_id     = isset( $_POST['payment_method_id'] ) ? sanitize_text_field( $_POST['payment_method_id'] ) : false;
		$payment_method_exists = isset( $_POST['payment_method_exists'] ) ? 'true' == $_POST['payment_method_exists'] : false;
		$nonce                 = isset( $_POST['nonce'] ) ? sanitize_text_field( $_POST['nonce'] ) : false;

		if ( ! $nonce || ! wp_verify_nonce( $nonce, 'update-payment' ) ) {
			return wp_send_json_error( array(
				'message' => esc_html__( 'Invalid request. Please try again', 'edd-recurring' ),
			) );
		}

		if ( ! $subscription_id || ! $payment_method_id ) {
			return wp_send_json_error( array(
				'message' => esc_html__( 'Unable to locate Subscription. Please try again', 'edd-recurring' ),
			) );
		}

		try {
			// Ensure we use the correct API information.
			$this->setup_stripe_api();

			$customer       = $this->get_customer();
			$payment_method = \Stripe\PaymentMethod::retrieve( $payment_method_id );

			// Attach method if it's new.
			if ( ! $payment_method_exists ) {
				$payment_method->attach( array(
					'customer' => $customer->id,
				) );

			// Update an existing method's address.
			} else {
				$address_info    = isset( $_POST['billing_address'] ) ? $_POST['billing_address'] : array();
				$billing_address = array();

				foreach ( $address_info as $key => $value ) {
					$billing_address[ $key ] = ! empty( $value ) ? sanitize_text_field( $value ) : null;
				}

				\Stripe\PaymentMethod::update( $payment_method_id, array(
					'billing_details' => array(
						'address' => $billing_address,
					),
				) );
			}

			// Set the Subscription's default payment method.
			$subscription = \Stripe\Subscription::update( $subscription_id, array(
				'default_payment_method' => $payment_method_id,
			) );

			return wp_send_json_success( array(
				'message'      => esc_html__( 'Payment method updated.', 'edd-recurring' ),
				'subscription' => $subscription,
			) );
		} catch( \Exception $e ) {
			return wp_send_json_error( array(
				'message' => $e->getMessage(),
			) );
		}
	}

	/**
	 * Processes the update payment form.
	 *
	 * Handling of the latest open invoice with an attached PaymentIntent is done
	 * on the client. In order to avoid a loop of paying for multiple "Past due" invoices
	 * that haven't affected the the \EDD_Subscription status, void them.
	 *
	 * @link https://github.com/easydigitaldownloads/edd-recurring/issues/1177
	 *
	 * @since 2.9.0
	 *
	 * @param EDD_Recurring_Subscriber $subscriber   EDD_Recurring_Subscriber.
	 * @param EDD_Subscription         $subscription EDD_Subscription.
	 */
	public function update_payment_method( $subscriber, $subscription ) {
		$void_past_due_invoices = true;

		/**
		 * Filters if stacked past due invoices should be voided when updating
		 * a Subscription's payment method.
		 *
		 * @since 2.9.0
		 *
		 * @param bool $void_past_due_invoices Void stacked past due invoices. Defaults true.
		 * @param int  $subscriber EDD_Recurring_Subscriber
		 */
		$void_past_due_invoices = apply_filters(
			'edd_recurring_stripe_void_past_due_invoices',
			$void_past_due_invoices,
			$subscription
		);

		if ( true !== $void_past_due_invoices ) {
			return;
		}

		if ( empty( $subscription->profile_id ) ) {
			return;
		}

		$customer_id = $subscriber->get_recurring_customer_id( 'stripe' );

		if ( empty( $customer_id ) ) {
			return;
		}

		try {
			// Ensure we use the correct API information.
			$this->setup_stripe_api();

			// Manual retries are limited to 7 days, so it's unlikely there will
			// be more invoices than that.
			$invoices = \Stripe\Invoice::all(
				array(
					'subscription' => $subscription->profile_id,
					'limit'        => 7,
					'status'       => 'open',
					'customer'     => $customer_id,
				)
			);

			if ( empty( $invoices->data ) ) {
				return;
			}

			foreach ( $invoices->data as $invoice ) {
				/* @var \Stripe\Invoice $invoice */
				$invoice->voidInvoice();
			}
		} catch ( \Exception $e ) {
			wp_die( esc_html( $e->getMessage() ) );
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
		if ( empty( $profile_id ) ) {
			return false;
		}

		$subscription = new EDD_Subscription( $profile_id, true );

		if ( ! $subscription || ! $subscription->id > 0 ) {
			return false;
		}

		// Already transformed a PaymentIntent to Charge ID.
		if ( 'ch_' === substr( $subscription->transaction_id, 0, 3 ) ) {
			return false;
		}

		// Ensure we use the correct API information.
		$this->setup_stripe_api();

		// A parent EDD_Payment's PaymentIntent was used temporarily.
		// Try to find a charge from the Intent.
		if ( 'pi_' === substr( $subscription->transaction_id, 0, 3 ) ) {

			try {
				$payment_intent = \Stripe\PaymentIntent::retrieve( $subscription->transaction_id );

				if ( ! empty( $payment_intent->charges->data ) ) {
					$charge_id = current( $payment_intent->charges->data )->id;

					$subscription->update( array(
						'transaction_id' => $charge_id,
					) );

					return $subscription;
				}
			} catch( \Exception $e ) {
				return false;
			}

		// Try to find it through any existing invoices.
		} else {

			$subscriber  = new EDD_Recurring_Subscriber( $subscription->customer_id );
			$customer_id = $subscriber->get_recurring_customer_id( 'stripe' );

			if ( empty( $customer_id ) ) {
				return false;
			}

			try {
				$customer = \Stripe\Customer::retrieve( $customer_id );
				$invoices = \Stripe\Invoice::all( array(
					'customer' => $customer_id,
					'limit' => 20,
				) );

				if ( empty( $invoices->data ) ) {
					return false;
				}

				foreach ( $invoices->data as $invoice ) {
					if ( empty( $invoice->subscription ) ) {
						continue;
					}

					if ( $profile_id != $invoice->subscription ) {
						continue;
					}

					if ( empty( $invoice->charge ) ) {
						continue;
					}

					$subscription->update( array(
						'transaction_id' => $invoice->charge,
					) );

					$subscription->transaction_id = $invoice->charge;

					return $subscription;

					break;
				}

			} catch( \Exception $e ) {
				return false;
			}
		}

		return false;
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
		if ( ! $subscription || ! $subscription->id > 0 ) {
			return;
		}

		if ( 'stripe' !== $subscription->gateway ) {
			return;
		}

		if ( empty( $subscription->profile_id ) ) {
			return;
		}

		// Already transformed a PaymentIntent to Charge ID.
		if ( 'ch_' === substr( $subscription->transaction_id, 0, 3 ) ) {
			return;
		}

		// Make sure we don't cause an infinite loop
		remove_action( 'edd_recurring_setup_subscription', array( $this, 'maybe_check_subscription' ), 10 );

		if ( false !== $this->check_transaction_id( $subscription->profile_id ) ) {
			// Remove the scheduled event for this subscription if it hasn't already run
			wp_clear_scheduled_hook( 'edd_recurring_stripe_check_txn', array( $subscription->profile_id ) );
		}

		add_action( 'edd_recurring_setup_subscription', array( $this, 'maybe_check_subscription' ) );
	}

	/**
	 * Verify that the user has acknowledged to updating their payment form as a default for all subscriptions
	 *
	 * @since 2.4
	 * @since 2.9.0 No longer used, always returns value sent.
	 *
	 * @param bool  $is_valid  If the data passed so far was valid from EDD Core
	 * @param array $post_data The array of $_POST sent by the form
	 *
	 * @return bool
	 */
	public function confirm_default_payment_method_change( $is_valid, $post_data ) {
		return $is_valid;
	}

}
$edd_recurring_stripe = new EDD_Recurring_Stripe;
