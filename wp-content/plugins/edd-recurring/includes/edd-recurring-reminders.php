<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * The Recurring Reminders Class
 *
 * @since  2.4
 */
class EDD_Recurring_Reminders {

	public function __construct() {
		add_action( 'edd_recurring_daily_scheduled_events', array( $this, 'scheduled_reminders' ) );
	}

	/**
	* Returns if renewals are enabled
	*
	* @return array Array of defined reminders.
	*/
	public function reminders_enabled() {
		$types = $this->get_notice_types();
		$ret = array();
		foreach ( $types as $type => $label ) {
			$ret[ $type ] = edd_get_option( 'recurring_send_' . $type . '_reminders', false );
		}
		return apply_filters( 'edd_recurring_send_reminders', $ret );
	}

	/**
	* Retrieve reminder notices periods
	*
	* @since 2.4
	* @return array reminder notice periods
	*/
	public function get_notice_periods() {
		$periods = array(
			'today'    => __( 'The day of the renewal/expiration', 'edd-recurring' ),
			'+1day'    => __( 'One day before renewal/expiration', 'edd-recurring' ),
			'+2days'   => __( 'Two days before renewal/expiration', 'edd-recurring' ),
			'+3days'   => __( 'Three days before renewal/expiration', 'edd-recurring' ),
			'+1week'   => __( 'One week before renewal/expiration', 'edd-recurring' ),
			'+2weeks'  => __( 'Two weeks before renewal/expiration', 'edd-recurring' ),
			'+1month'  => __( 'One month before renewal/expiration', 'edd-recurring' ),
			'+2months' => __( 'Two months before renewal/expiration', 'edd-recurring' ),
			'+3months' => __( 'Three months before renewal/expiration', 'edd-recurring' ),
			'-1day'    => __( 'One day after expiration', 'edd-recurring' ),
			'-2days'   => __( 'Two days after expiration', 'edd-recurring' ),
			'-3days'   => __( 'Three days after expiration', 'edd-recurring' ),
			'-1week'   => __( 'One week after expiration', 'edd-recurring' ),
			'-2weeks'  => __( 'Two weeks after expiration', 'edd-recurring' ),
			'-1month'  => __( 'One month after expiration', 'edd-recurring' ),
			'-2months' => __( 'Two months after expiration', 'edd-recurring' ),
			'-3months' => __( 'Three months after expiration', 'edd-recurring' )
		);
		return apply_filters( 'edd_recurring_get_reminder_notice_periods', $periods );
	}

	/**
	* Retrieve the reminder label for a notice
	*
	* @since 2.4
	* @return String
	*/
	public function get_notice_period_label( $notice_id = 0 ) {

		$notice  = $this->get_notice( $notice_id );
		$periods = $this->get_notice_periods();
		$label   = $periods[ $notice['send_period'] ];

		return apply_filters( 'edd_recurring_get_reminder_notice_period_label', $label, $notice_id );
	}

	/**
	* Retrieve reminder notices types
	*
	* @since 2.4
	* @return array reminder notice types
	*/
	public function get_notice_types() {
		$types = array(
			'renewal'    => __( 'Renewal', 'edd-recurring' ),
			'expiration' => __( 'Expiration', 'edd-recurring' ),
		);
		return apply_filters( 'edd_recurring_get_reminder_notice_types', $types );
	}

	/**
	* Retrieve the reminder type label for a notice
	*
	* @since 2.4
	* @return String
	*/
	public function get_notice_type_label( $notice_id = 0 ) {

		$notice  = $this->get_notice( $notice_id );
		$types = $this->get_notice_types();
		$label   = $types[ $notice['type'] ];

		return apply_filters( 'edd_recurring_get_reminder_notice_type_label', $label, $notice_id );
	}

	/**
	* Retrieve a reminder notice
	*
	* @since 2.4
	* @return array Reminder notice details
	*/
	public function get_notice( $notice_id = 0 ) {

		$notices  = $this->get_notices();

		$defaults = array(
			'subject'      => __( 'Your Subscription is About to Renew', 'edd-recurring' ),
			'send_period'  => '+1month',
			'message'      => 'Hello {name},

			Your subscription for {subscription_name} will renew on {expiration}.',
			'type'		   => 'renewal',
		);

		$notice   = isset( $notices[ $notice_id ] ) ? $notices[ $notice_id ] : $notices[0];

		$notice   = wp_parse_args( $notice, $defaults );

		return apply_filters( 'edd_recurring_reminder_notice', $notice, $notice_id );

	}

	/**
	* Retrieve reminder notice periods
	*
	* @since 2.4
	* @return array Reminder notices defined in settings
	*/
	public function get_notices( $type = 'all' ) {
		$notices = get_option( 'edd_recurring_reminder_notices', array() );

		if( empty( $notices ) ) {

			$message = 'Hello {name},

	Your subscription for {subscription_name} will renew on {expiration}.';

			$notices[0] = array(
				'send_period' => '+1month',
				'subject'     => __( 'Your Subscription is About to Renew', 'edd-recurring' ),
				'message'     => $message,
				'type'		  => 'renewal'
			);

			$message = 'Hello {name},

	Your subscription for {subscription_name} will expire on {expiration}.';

			$notices[1] = array(
				'send_period' => '+1month',
				'subject'     => __( 'Your Subscription is About to Expire', 'edd-recurring' ),
				'message'     => $message,
				'type'	  	  => 'expiration'
			);
		}

		if ( $type != 'all' ) {

			$notices_hold = array();

			foreach ( $notices as $key => $notice ) {

				if ( $notice['type'] == $type ) {
					$notices_hold[ $key ] = $notice;

				}

			}

			$notices = $notices_hold;

		}

		return apply_filters( 'edd_recurring_get_reminder_notices', $notices, $type );
	}

	/**
	* Send reminder emails
	*
	* @since 2.4
	* @return void
	*/
	public function scheduled_reminders() {

		$edd_recurring_emails = new EDD_Recurring_Emails;

		$reminders_enabled = $this->reminders_enabled();

		edd_debug_log( 'Running EDD_Recurring_Reminders::scheduled_reminders.', true );

		foreach ( $reminders_enabled as $type => $enabled ) {

			if ( ! $enabled ) {
				continue;
			}

			$notices = $this->get_notices( $type );

			edd_debug_log( 'Beginning reminder processing. Found ' . count( $notices ) . ' reminder templates.', true );

			foreach( $notices as $notice_id => $notice ) {

				edd_debug_log( 'Processing ' . $notice['send_period'] . ' reminder template.', true );

				$subscriptions = $this->get_reminder_subscriptions( $notice['send_period'], $type );

				edd_debug_log( 'Found ' . count( $subscriptions ) . ' subscriptions to send reminders for.', true );

				if( ! $subscriptions ) {
					continue;
				}

				$processed_subscriptions = 0;

				foreach( $subscriptions as $subscription ) {

					// Ensure the subscription should renew based on payments made and bill times
					if ( $type == 'renewal' && $subscription->bill_times != 0 && $subscription->get_total_payments() >= $subscription->bill_times ) {
						edd_debug_log( 'Ignored renewal notice for subscription ID ' . $subscription->id . ' due being billing times being complete.', true );
						continue;
					}

					// Ensure an expiration notice isn't sent to an auto-renew subscription
					if ( $type == 'expiration' && $subscription->get_status() == 'active' && ( $subscription->get_total_payments() < $subscription->bill_times || $subscription->bill_times == 0 ) ) {
						edd_debug_log( 'Ignored expiration notice for subscription ID ' . $subscription->id . ' due to subscription being active.', true );
						continue;
					}

					// Ensure an expiration notice isn't sent to a still-trialling subscription
					if ( $type == 'expiration' && $subscription->get_status() == 'trialling' ) {
						edd_debug_log( 'Ignored expiration notice for subscription ID ' . $subscription->id . ' due subscription still trialling.', true );
						continue;
					}

					$sent_time = get_user_meta( $subscription->customer->user_id, sanitize_key( '_edd_recurring_reminder_sent_' . $subscription->id . '_' . $notice_id . '_' . $subscription->get_total_payments() ), true );

					if ( $sent_time ) {
						edd_debug_log( 'Skipping renewal reminder for subscription ID ' . $subscription->id . ' and reminder ' . $notice['send_period'] . '. Previously sent on ' . date_i18n( get_option( 'date_format' ), $sent_time ), true );
						continue;
					}

					edd_debug_log( 'Renewal reminder not previously sent for subscription ID ' . $subscription->id . ' for reminder ' . $notice['send_period'], true );

					$edd_recurring_emails->send_reminder( $subscription->id, $notice_id );
					$processed_subscriptions++;

				}

				edd_debug_log( 'Finished processing ' . $processed_subscriptions . ' for ' . $notice['send_period'] . ' reminder template.', true );

			}
		}

		edd_debug_log( 'Finished EDD_Recurring_Reminders::scheduled_reminders.', true );

	}

	/**
	* Retrieve reminder notice periods
	*
	* @since 2.4
	* @return array Subscribers whose subscriptions are renewing or expiring within the defined period
	*/
	public function get_reminder_subscriptions( $period = '+1month', $type = false ) {

		if ( ! $type ) {
			return false;
		}

		$args = array();

		switch ( $type ) {
			case "renewal":
				// Doesn't make sense to give someone a notice of an autorenewal if it has already expired
				if ( stristr( $period, '-' ) === true ) {
					return false;
				}

				$args['renewal'] = array(
					'number'     => 99999,
					'status'     => 'active',
					'expiration' => array(
						'start' => $period . ' midnight',
						'end'   => date( 'Y-m-d H:i:s', strtotime( $period . ' midnight' ) + ( DAY_IN_SECONDS - 1 ) ),
					),
				);
				break;

			case "expiration":
				// If we are looking at expired subscriptions then we need to swap our start and end period checks
				if ( stristr( $period, '-' ) === true ) {

					$start = date( 'Y-m-d H:i:s', strtotime( $period . ' midnight' ) + ( DAY_IN_SECONDS - 1 ) );
					$end   = $period . ' midnight';

				} else {

					$start = $period . ' midnight';
					$end   = date( 'Y-m-d H:i:s', strtotime( $period . ' midnight' ) + ( DAY_IN_SECONDS - 1 ) );

				}

				$args[ 'expiration' ] = array(
					'number'        => 99999,
					'expiration'    => array(
						'start'     => $start,
						'end'       => $end
					),
				);
				break;
		}

		$args = apply_filters( 'edd_recurring_reminder_subscription_args', $args );

		$subs_db = new EDD_Subscriptions_DB();
		$subscriptions = $subs_db->get_subscriptions( $args[ $type ] );

		if ( ! empty( $subscriptions ) ) {
			return $subscriptions;
		}

		return false;
	}

	/**
	* Setup and send test email for a reminder
	*
	* @since 2.4
	* @return void
	*/
	function send_test_notice( $notice_id = 0 ) {
		global $edd_options;

		$edd_recurring_emails = new EDD_Recurring_Emails;

		$notice = $this->get_notice( $notice_id );

		$email_to   = function_exists( 'edd_get_admin_notice_emails' ) ? edd_get_admin_notice_emails() : get_bloginfo( 'admin_email' );
		$message    = ! empty( $notice['message'] ) ? $notice['message'] : __( "**THIS IS A DEFAULT TEST MESSAGE - Notice message was not retrieved.**\n\nHello {name},\n\nYour subscription for {subscription_name} will renew or expire on {expiration}.", 'edd-recurring');
        $message 	= $this->filter_test_notice( $message );
		$subject    = ! empty( $notice['subject'] ) ? $notice['subject'] : __( 'Default Subject Message - Your Subscription is About to Renew or Expire', 'edd-recurring' );
        $subject	= $this->filter_test_notice( $subject );

        if( class_exists( 'EDD_Emails' ) ) {

            EDD()->emails->send( $email_to, $subject, $message );

        } else {

            $from_name  = get_bloginfo( 'name' );
            $from_email = get_bloginfo( 'admin_email' );
            $headers    = "From: " . stripslashes_deep( html_entity_decode( $from_name, ENT_COMPAT, 'UTF-8' ) ) . " <$from_email>\r\n";
            $headers   .= "Reply-To: ". $from_email . "\r\n";

            wp_mail( $email_to, $subject, $message, $headers );

        }
	}

	/**
	* Filter fields for test email for a reminder
	*
	* @since 2.4
	* @return void
	*/
	function filter_test_notice( $text = null ) {
		$text = str_replace( '{name}', 'NAME GOES HERE', $text );
        $text = str_replace( '{subscription_name}', 'SUBSCRIPTION NAME', $text );
        $text = str_replace( '{expiration}', date('F j, Y', strtotime( 'today' ) ), $text );

		return $text;
	}

}
