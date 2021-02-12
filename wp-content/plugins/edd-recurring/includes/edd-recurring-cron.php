<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * The Recurring Reminders Class
 *
 * @since  2.4
 */
class EDD_Recurring_Cron {

	protected $db;

	/**
	 *
	 * @since  2.4
	 */
	public function __construct() {
		$this->init();
	}

	/**
	 * Set up our actions and properties
	 *
	 * @since  2.4
	 */
	public function init() {

		$this->db = new EDD_Subscriptions_DB;

		// Renewal reminders are added to cron in edd-recurring-reminders.php

		add_action( 'edd_recurring_daily_scheduled_events', array( $this, 'check_for_expired_subscriptions' ), 20 );
		add_action( 'edd_recurring_daily_scheduled_events', array( $this, 'check_for_abandoned_subscriptions' ), 20 );
	}

	/**
	 * Check for expired subscriptions once per day and mark them as expired
	 *
	 * @since  2.4
	 */
	public function check_for_expired_subscriptions() {

		$args = array(
			'status'     => 'active',
			'number'     => 999999,
			'expiration' => array(
				'start'  => date( 'Y-n-d 00:00:00', strtotime( '-1 day', current_time( 'timestamp' ) ) ),
				'end'    => date( 'Y-n-d 23:59:59', strtotime( '-1 day', current_time( 'timestamp' ) ) )
			)

		);

		$subs = $this->db->get_subscriptions( $args );

		if( ! empty( $subs ) ) {

			foreach( $subs as $sub ) {

				/*
				 * In the future we can query the merchant processor to confirm the subscription is actually expired
				 *
				 * See https://github.com/easydigitaldownloads/edd-recurring/issues/101
				 * See https://github.com/easydigitaldownloads/edd-recurring/issues/614
				 */

				$sub->expire( true );

			}

		}

	}

	/**
	 * Deletes pending subscription records
	 *
	 * @since 2.5
	 * @return void
	*/
	public function check_for_abandoned_subscriptions() {

		$db = new EDD_Subscriptions_DB;

		$args = array(
			'status'  => 'pending',
			'number'  => 1000,
			'date'    => array(
				'end' => '-1 week'
			),
		);

		$subscriptions = $db->get_subscriptions( $args );

		if( $subscriptions ) {

			foreach( $subscriptions as $subscription ) {

				$payment = new EDD_Payment( $subscription->parent_payment_id );
				if ( $payment ) {
					$payment->delete_meta( '_edd_subscription_payment' );
				}
				$db->delete( $subscription->id );

			}

		}

	}

}


// This is intentionally outside of the class. EDD_Recurring_Cron is loaded too late to register new scheduled events
if ( ! wp_next_scheduled( 'edd_recurring_daily_scheduled_events' ) ) {
	wp_schedule_event( current_time( 'timestamp', true ), 'daily', 'edd_recurring_daily_scheduled_events' );
}