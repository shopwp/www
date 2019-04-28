<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * The Recurring Subscriber Class
 *
 * Includes methods for setting users as customers, setting their status, expiration, etc.
 *
 * @since  2.4
 */
class EDD_Recurring_Subscriber extends EDD_Customer {

	private $subs_db;

	/**
	 * Get us started
	 *
	 * @since  2.4
	 * @return void
	 */
	function __construct( $_id_or_email = false, $by_user_id = false ) {
		parent::__construct( $_id_or_email, $by_user_id );
		$this->subs_db = new EDD_Subscriptions_DB;
	}

	/**
	 * Determine if the customer has an active subscription for the given product
	 *
	 * @since  2.4
	 * @return void
	 */
	public function has_active_product_subscription( $product_id = 0 ) {

		$ret  = false;
		$subs = $this->get_subscriptions( $product_id );

		if ( $subs ) {

			foreach ( $subs as $sub ) {

				if ( $sub->is_active() ) {
					$ret = true;
					break;
				}

			}

		}

		return apply_filters( 'edd_recurring_has_active_product_subscription', $ret, $product_id, $this );
	}

	/**
	 * Has Product Subscription
	 *
	 * @since 2.4
	 * @param int $product_id
	 * @return mixed|void
	 */
	public function has_product_subscription( $product_id = 0 ) {

		$ret  = false;
		$subs = $this->get_subscriptions( $product_id );
		$ret  = ! empty( $subs );

		return apply_filters( 'edd_recurring_has_product_subscription', $ret, $product_id, $this );
	}

	/**
	 * Has Active Subscription
	 *
	 * @since 2.4
	 * @return mixed|void
	 */
	public function has_active_subscription() {

		$ret  = false;
		$subs = $this->get_subscriptions();
		if ( $subs ) {
			foreach ( $subs as $sub ) {

				if ( $sub->is_active() || ( ! $sub->is_expired() && 'cancelled' === $this->status ) ) {
					$ret = true;
				}

			}
		}

		return apply_filters( 'edd_recurring_has_active_subscription', $ret, $this );
	}

	/**
	 * Has trialed
	 *
	 * Determines if the subscriber has used a free trial.
	 * Optionally checks if a trial for a specific product has been used.
	 *
	 * @since 2.6
	 * @return bool
	 */
	public function has_trialed( $product_id = 0 ) {

		$ret    = false;
		$trials = (array) $this->get_meta( 'edd_recurring_trials', false );

		if( ! empty( $product_id ) ) {
			$ret = in_array( $product_id, $trials );
		} else {
			$ret = ! empty( $trials );
		}

		return apply_filters( 'edd_recurring_has_trialed', $ret, $product_id, $this );
	}

	/**
	 * Adds a subscription to a user / customer
	 *
	 * @since 2.4
	 * @param array $args
	 * @return object EDD_Subscription
	 */
	public function add_subscription( $args = array() ) {

		$args = wp_parse_args( $args, $this->subs_db->get_column_defaults() );

		if ( empty( $args['product_id'] ) ) {
			return false;
		}

		if ( ! empty( $this->user_id ) ) {

			$this->set_as_subscriber(  );

		}

		$args['customer_id'] = $this->id;

		$subscription = new EDD_Subscription();

		return $subscription->create( $args );

	}

	/**
	 * Add Payment
	 *
	 * @since 2.4
	 * @param array $args
	 * @return bool
	 */
	public function add_payment( $args = array() ) {

		$args = wp_parse_args( $args, array(
			'subscription_id' => 0,
			'amount'          => '0.00',
			'transaction_id'  => '',
		) );

		if ( empty( $args['subscription_id'] ) ) {
			return false;
		}

		$subscription = new EDD_Subscription( $args['subscription_id'] );

		if ( empty( $subscription ) ) {
			return false;
		}

		unset( $args['subscription_id'] );

		return $subscription->add_payment( $args );

	}

	/**
	 * Retrieves a subscription based on subscription ID
	 *
	 * @param int $subscription_id
	 * @since 2.4
	 * @return object EDD_Subscription
	 */
	public function get_subscription( $subscription_id = 0 ) {

		$sub = new EDD_Subscription( $subscription_id );

		if( (int) $sub->customer_id !== (int) $this->id ) {
			return false;
		}

		return $sub;
	}

	/**
	 * Retrieves a subscription based on the profile ID
	 *
	 * @since 2.4
	 * @param string $profile
	 * @return object EDD_Subscription
	 */
	public function get_subscription_by_profile_id( $profile_id = '' ) {

		if( empty( $profile_id ) ) {
			return false;
		}

		$sub = new EDD_Subscription( $profile_id, true );

		if( (int) $sub->customer_id !== (int) $this->id ) {
			return false;
		}

		return $sub;

	}

	/**
	 * Retrieves an array of subscriptions for a the customer
	 *
	 * Optional product ID and status(es) can be supplied
	 *
	 * @param int $product_id
	 * @param array $statuses
	 * @since 2.4
	 * @return array
	 */
	public function get_subscriptions( $product_id = 0, $statuses = array() ) {
		if ( ! $this->id > 0 ) {
			return array();
		}

		$args = array(
			'customer_id' => $this->id,
			'number'      => - 1
		);

		if ( ! empty( $statuses ) ) {
			$args['status'] = $statuses;
		}

		if ( ! empty( $product_id ) ) {
			$args['product_id'] = $product_id;
		}

		return $this->subs_db->get_subscriptions( $args );
	}

	/**
	 * Set a user as a subscriber
	 *
	 * @since  1.0
	 * @param  $user_id INT The ID of the user we're setting as a subscriber
	 * @return void
	 */
	public function set_as_subscriber() {

		$user = new WP_User( $this->user_id );

		if ( $user ) {
			$user->add_role( 'edd_subscriber' );
			do_action( 'edd_recurring_set_as_subscriber', $this->user_id );
		}

	}

	/**
	 * Calculate a new expiration date
	 */
	public function get_new_expiration( $download_id = 0, $price_id = null, $trial_period = '' ) {

		if( ! empty( $trial_period ) ) {

			$period = '+ ' . $trial_period;

		} else {

			if ( edd_has_variable_prices( $download_id ) ) {

				$period = edd_recurring()->get_period( $price_id, $download_id );

			} else {

				$period = edd_recurring()->get_period_single( $download_id );

			}

			switch( $period ) {

				case 'quarter' :

					$period = '+ 3 months';

					break;

				case 'semi-year' :

					$period = '+ 6 months';

					break;

				default :

					$period = '+ 1 ' . $period;

					break;

			}

		}

		return date( 'Y-m-d H:i:s', strtotime( $period . ' 23:59:59', current_time( 'timestamp' ) ) );

	}

	/**
	 * Get a recurring customer ID
	 *
	 * @since  2.4
	 * @param  string $gateway The gateway to retrieve the customer ID for
	 * @return str
	 */
	public function get_recurring_customer_id( $gateway = false ) {

		$recurring_ids = $this->get_recurring_customer_ids();

		if ( is_array( $recurring_ids )  ) {
			if ( false === $gateway || ! array_key_exists( $gateway, $recurring_ids ) ) {
				$gateway = reset( $recurring_ids );
			}

			$customer_id = $recurring_ids[ $gateway ];
		} else {
			$customer_id = empty( $recurring_ids ) ? false : $recurring_ids;
		}

		return apply_filters( 'edd_recurring_get_customer_id', $customer_id, $this );

	}

	/**
	 * Store a recurring customer ID
	 *
	 * @since  2.4
	 * @param  int    $recurring_id The recurring profile ID to set
	 * @param  string $gateway      The Gateway to set the ID for
	 * @return bool
	 */
	public function set_recurring_customer_id( $recurring_id = '', $gateway = false ) {

		if ( false === $gateway ) {
			// We require a gateway identifier to be included, if it's not, return false
			return false;
		}

		$recurring_id  = apply_filters( 'edd_recurring_set_customer_id', $recurring_id, $this->user_id );
		$recurring_ids = $this->get_recurring_customer_ids();

		if( ! is_array( $recurring_ids ) ) {

			$existing      = $recurring_ids;
			$recurring_ids = array();

			// If the first three characters match, we know the existing ID belongs to this gateway
			if( substr( $recurring_id, 0, 3 ) === substr( $existing, 0, 3 ) ) {

				$recurring_ids[ $gateway ] = $existing;

			}

		}

		$recurring_ids[ $gateway ] = $recurring_id;

		return update_user_meta( $this->user_id, '_edd_recurring_id', $recurring_ids );

	}

	/**
	 * Retrieve the recurring customer IDs for the user
	 *
	 * @since  2.4
	 * @return mixed The profile IDs
	 */
	public function get_recurring_customer_ids() {
		$ids = get_user_meta( $this->user_id, '_edd_recurring_id', true );
		return apply_filters( 'edd_recurring_customer_ids', $ids, $this );
	}


}
