<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;


/**
 * The Cecurring Customer Class
 *
 * DEPRECATED in 2.4. Use EDD_Recurring_Subscriber instead
 *
 * Includes methods for setting users as customers, setting their status, expiration, etc.
 *
 * @since  1.0
 */

class EDD_Recurring_Customer {

	/**
	 * Get us started
	 *
	 * @since  1.0
	 * @return void
	 */

	function __construct() { }

	/**
	 * Set a user as a subscriber
	 *
	 * @since  1.0
	 * @param  $user_id INT The ID of the user we're setting as a subscriber
	 * @return void
	 */

	static public function set_as_subscriber( $user_id = 0 ) {

		$backtrace = debug_backtrace();

		_edd_deprecated_function( __FUNCTION__, '2.4', 'EDD_Recurring_Subscriber', $backtrace );

		$user                   = new WP_User( $user_id );
		$subscriber_role_exists = (bool) $GLOBALS['wp_roles']->is_role( 'edd_subscriber' );
		if ( $subscriber_role_exists ) {
			$user->add_role( 'edd_subscriber' );
		}

		do_action( 'edd_recurring_set_as_subscriber', $user_id );

	}

	/**
	 * Store a recurring customer ID
	 *
	 * @since  1.0
	 * @param  $user_id      INT The ID of the user we're setting as a subscriber
	 * @param  $recurring_id INT The recurring profile ID to set
	 * @return bool
	 */

	static public function set_customer_id( $user_id = 0, $recurring_id = '' ) {

		$backtrace = debug_backtrace();

		_edd_deprecated_function( __FUNCTION__, '2.4', 'EDD_Recurring_Subscriber', $backtrace );

		$id = apply_filters( 'edd_recurring_set_customer_id', $recurring_id, $user_id );

		return update_user_meta( $user_id, '_edd_recurring_id', $recurring_id );

	}


	/**
	 * Get a recurring customer ID
	 *
	 * @since  1.0
	 * @param  $user_id      INT The ID of the user we're getting an ID for
	 * @return str
	 */

	static public function get_customer_id( $user_id = 0 ) {

		$backtrace = debug_backtrace();

		_edd_deprecated_function( __FUNCTION__, '2.4', 'EDD_Recurring_Subscriber', $backtrace );

		return get_user_meta( $user_id, '_edd_recurring_id', true );

	}


	/**
	 * Get a user ID from the recurring customer ID
	 *
	 * @since  1.0.1
	 * @param  $recurring_id  STR The recurring ID of the user we're getting an ID for
	 * @return int
	 */
	static public function get_user_id_by_customer_id( $recurring_id = '' ) {
		global $wpdb;

		$backtrace = debug_backtrace();

		_edd_deprecated_function( __FUNCTION__, '2.4', 'EDD_Recurring_Subscriber', $backtrace );

		$user_id = $wpdb->get_var( $wpdb->prepare( "SELECT user_id FROM $wpdb->usermeta WHERE meta_key = '_edd_recurring_id' AND meta_value = '%s' LIMIT 1", $recurring_id ) );
		return $user_id;

	}


	/**
	 * Stores the parent payment ID for a customer
	 *
	 * @since  1.0.1
	 * @param  $user_id     INT The user ID to set a parent payment for
	 * @param  $payment_id  INT The Payment ID to set
	 * @return int
	 */
	static public function set_customer_payment_id( $user_id = 0, $payment_id = 0 ) {

		$backtrace = debug_backtrace();

		_edd_deprecated_function( __FUNCTION__, '2.4', 'EDD_Recurring_Subscriber', $backtrace );

		do_action( 'edd_recurring_set_customer_payment_id', $user_id, $payment_id );
		update_user_meta( $user_id, '_edd_recurring_user_parent_payment_id', $payment_id );
	}


	/**
	 * Get the parent payment ID for a customer
	 *
	 * @since  1.0.1
	 * @param  $user_id     INT The user ID to get a parent payment for
	 * @return int
	 */
	static public function get_customer_payment_id( $user_id = 0 ) {

		$backtrace = debug_backtrace();

		_edd_deprecated_function( __FUNCTION__, '2.4', 'EDD_Recurring_Subscriber', $backtrace );

		// Retrieve from user meta for pre 2.4 data
		$payment_id = get_user_meta( $user_id, '_edd_recurring_user_parent_payment_id', true );

		// Now look to see if we have updated data
		$customer = new EDD_Recurring_Subscriber( $user_id, true );

		if( $customer->id > 0 ) {
			$subs = $customer->get_subscriptions( 0, array( 'completed', 'active' ) );
			if( $subs ) {
				foreach( $subs as $sub ) {
					$payment_id = $sub->parent_payment_id;
					break;
				}

			}
		}

		return $payment_id;
	}


	/**
	 * Set a status for a customer
	 *
	 * @since  1.0
	 * @param  $user_id      INT The ID of the user we're setting a status for
	 * @param  $status       STRING The status to set
	 * @return bool
	 */

	static public function set_customer_status( $user_id = 0, $status = 'active' ) {

		$backtrace = debug_backtrace();

		_edd_deprecated_function( __FUNCTION__, '2.4', 'EDD_Recurring_Subscriber', $backtrace );

		$status = apply_filters( 'edd_recurring_set_customer_status', $status, $user_id );
		$ret    = update_user_meta( $user_id, '_edd_recurring_status', $status );

		// Update new subscription record
		$customer = new EDD_Recurring_Subscriber( $user_id, true );

		if( $customer->id > 0 ) {
			$subs = $customer->get_subscriptions( 0 );
			if( $subs ) {
				foreach( $subs as $sub ) {
					$sub->update( array( 'status' => $status ) );
					break;
				}

			}
		}

		do_action( 'edd_recurring_set_user_status', $user_id, $status );

		return $ret;
	}


	/**
	 * Get customer status
	 *
	 * @since  1.0
	 * @param  $user_id      INT The ID of the user we're getting a status for
	 * @return bool
	 */

	static public function get_customer_status( $user_id = 0 ) {

		$backtrace = debug_backtrace();

		_edd_deprecated_function( __FUNCTION__, '2.4', 'EDD_Recurring_Subscriber', $backtrace );

		// Check if we have an active subscription and force to active if we do
		$customer = new EDD_Recurring_Subscriber( $user_id, true );

		return $customer->has_active_subscription() ? 'active' : 'expired';

	}


	/**
	 * Check if a customer is active
	 *
	 * @since  1.0
	 * @param  $user_id      INT The ID of the user we're checking
	 * @return bool
	 */

	static public function is_customer_active( $user_id = 0 ) {

		$backtrace = debug_backtrace();

		_edd_deprecated_function( __FUNCTION__, '2.4', 'EDD_Recurring_Subscriber', $backtrace );

		if( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}

		$status = self::get_customer_status( $user_id );

		// Check if expired and set to expired if so
		if( self::is_customer_expired( $user_id ) ) {
			$status = 'expired';
			self::set_customer_status( $user_id, $status );
		}

		$active = $status == 'active' || $status == 'cancelled' ? true : false;

		return apply_filters( 'edd_recurring_is_user_active', $active, $user_id, $status );

	}


	/**
	 * Set an expiration date
	 *
	 * @since  1.0
	 * @param  $user_id      INT The ID of the user we're setting an expiration for
	 * @param  $expiration   INT The expiration timestamp
	 * @return bool
	 */

	static public function set_customer_expiration( $user_id = 0, $expiration = 0 ) {

		$backtrace = debug_backtrace();

		_edd_deprecated_function( __FUNCTION__, '2.4', 'EDD_Recurring_Subscriber', $backtrace );

		$date = apply_filters( 'edd_recurring_set_customer_expiration', $expiration, $user_id );
		$ret  = update_user_meta( $user_id, '_edd_recurring_exp', $date );

		// Update new subscription record
		$customer = new EDD_Recurring_Subscriber( $user_id, true );

		if( $customer->id > 0 ) {
			$subs = $customer->get_subscriptions( 0 );
			if( $subs ) {
				foreach( $subs as $sub ) {
					$sub->update( array( 'expiration' => date( 'Y-m-d H:i:s', $date ) ) );
					break;
				}

			}
		}

		do_action( 'edd_recurring_set_user_expiration', $user_id, $expiration );

		return $ret;

	}


	/**
	 * Get an expiration date
	 *
	 * @since  1.0
	 * @param  $user_id      INT The ID of the user we're getting an expiration for
	 * @return int
	 */

	static public function get_customer_expiration( $user_id = 0 ) {

		$backtrace = debug_backtrace();

		_edd_deprecated_function( __FUNCTION__, '2.4', 'EDD_Recurring_Subscriber', $backtrace );

		$date = get_user_meta( $user_id, '_edd_recurring_exp', true );

		// Now look to see if we have updated data
		$customer = new EDD_Recurring_Subscriber( $user_id, true );

		if( $customer->id > 0 ) {
			$subs = $customer->get_subscriptions( 0 );
			if( $subs ) {
				foreach( $subs as $sub ) {
					$date = $sub->get_expiration_time();
					break;
				}

			}
		}

		return $date;

	}


	/**
	 * Check if expired
	 *
	 * @since  1.0
	 * @param  $user_id      INT The ID of the user we're checking
	 * @return bool
	 */

	static public function is_customer_expired( $user_id = 0 ) {

		$backtrace = debug_backtrace();

		_edd_deprecated_function( __FUNCTION__, '2.4', 'EDD_Recurring_Subscriber', $backtrace );

		if( empty( $user_id ) )
			$user_id = get_current_user_id();

		$expiration = self::get_customer_expiration( $user_id );

		return time() > $expiration ? true : false;

	}


	/**
	 * Calculate a new expiration date
	 *
	 * @since  1.0
	 * @param  $_customer_or_user_id      INT depending on EDD Version, this is a customer or User ID
	 * @param  $payment_id   INT The original payment ID
	 * @return int
	 */

	static public function calc_user_expiration( $_customer_or_user_id = 0, $payment_id = 0 ) {

		$backtrace = debug_backtrace();

		_edd_deprecated_function( __FUNCTION__, '2.4', 'EDD_Recurring_Subscriber', $backtrace );

		$edd_version = get_option( 'edd_version' );

		if ( version_compare( $edd_version, '2.3', '<' ) ) {
			$user_id = $_customer_or_user_id;
		} else {
			$user_id = self::get_user_id_from_customer_id( $_customer_or_user_id );
		}

		// Retrieve the items purchased from the original payment
		$downloads  = edd_get_payment_meta_downloads( $payment_id );
		$download   = $downloads[0]; // We only care about the first (and only) item
		$period     = $download['options']['recurring']['period'];
		$expiration = strtotime( '+ 1 ' . $period . ' 23:59:59' );

		return apply_filters( 'edd_recurring_calc_expiration', $expiration, $user_id, $payment_id, $period );
	}

	/**
	 * Given a customer ID, transpose to the user ID
	 *
	 * @since  2.2.14
	 * @param  int $customer_id The customer ID to lookup
	 * @return int              The User ID assocaited with that customer ID
	 */
	static public function get_user_id_from_customer_id( $customer_id ) {

		$backtrace = debug_backtrace();

		_edd_deprecated_function( __FUNCTION__, '2.4', 'EDD_Recurring_Subscriber', $backtrace );

		$user_id = 0;

		if ( empty( $customer_id ) || ! is_numeric( $customer_id ) ) {
			return $user_id;
		}

		$customer = new EDD_Customer( $customer_id );

		return $customer->user_id;
	}

}
