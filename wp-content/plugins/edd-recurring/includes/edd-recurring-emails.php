<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * The Recurring Emails Class
 *
 * @since  2.4
 */
class EDD_Recurring_Emails {

	public $subscription;
	
	public function __construct() {
		$this->init();
	}

	public function init() {

		if( edd_get_option( 'enable_payment_received_email' ) ) {
			add_action( 'edd_recurring_add_subscription_payment', array( $this, 'send_payment_received' ), 10, 2 );
		}

		if( edd_get_option( 'enable_payment_failed_email' ) ) {
			add_action( 'edd_recurring_payment_failed', array( $this, 'send_payment_failed' ), 10 );
		}

		if( edd_get_option( 'enable_subscription_cancelled_email' ) ) {
			add_action( 'edd_subscription_cancelled', array( $this, 'send_subscription_cancelled' ), 10, 3 );
		}
	}

	public function send_payment_received( EDD_Payment $payment, EDD_Subscription $subscription ) {

		$this->subscription = $subscription;

		$email_to = $subscription->customer->email;
		$subject  = apply_filters( 'edd_recurring_payment_received_subject', edd_get_option( 'payment_received_subject' ) );
		$message  = apply_filters( 'edd_recurring_payment_received_message', edd_get_option( 'payment_received_message' ) );
		$message  = $this->payment_received_template_tags( $message, $payment->total );

		EDD()->emails->send( $email_to, $subject, $message );

	}

	public function send_payment_failed( EDD_Subscription $subscription ) {

		$this->subscription = $subscription;

		$email_to = $subscription->customer->email;
		$subject  = apply_filters( 'edd_recurring_payment_failed_subject', edd_get_option( 'payment_failed_subject' ) );
		$message  = apply_filters( 'edd_recurring_payment_failed_message', edd_get_option( 'payment_failed_message' ) );
		$message  = $this->payment_received_template_tags( $message, $subscription->recurring_amount );

		EDD()->emails->send( $email_to, $subject, $message );

	}

	public function send_subscription_cancelled( $subscription_id = 0, EDD_Subscription $subscription ) {

		$this->subscription = $subscription;

		$email_to = $subscription->customer->email;
		$subject  = apply_filters( 'edd_recurring_subscription_cancelled_subject', edd_get_option( 'subscription_cancelled_subject' ) );
		$message  = apply_filters( 'edd_recurring_subscription_cancelled_message', edd_get_option( 'subscription_cancelled_message' ) );
		$message  = $this->filter_reminder_template_tags( $message, $subscription_id );

		EDD()->emails->send( $email_to, $subject, $message );

	}

	public function send_reminder( $subscription_id = 0, $notice_id = 0 ) {

		if( empty( $subscription_id ) ) {
			return;
		}
		
		$this->subscription = new EDD_Subscription( $subscription_id );
		
		if( empty( $this->subscription ) ) {
			return;
		}
		
		$notices = new EDD_Recurring_Reminders();
		$send    = true;
		$user    = get_user_by( 'id', $this->subscription->customer->user_id );
		$send    = apply_filters( 'edd_recurring_send_reminder', $send, $subscription_id, $notice_id );

		if( ! $user || ! in_array( 'edd_subscriber', $user->roles, true ) || ! $send || ! empty( $user->post_parent ) ) {
			return;
		}

		$email_to   = $this->subscription->customer->email;
		$notice     = $notices->get_notice( $notice_id );
		$message    = ! empty( $notice['message'] ) ? $notice['message'] : __( "Hello {name},\n\nYour subscription for {subscription_name} will renew or expire on {expiration}.", 'edd-recurring');
		$message    = $this->filter_reminder_template_tags( $message, $subscription_id );

		$subject    = ! empty( $notice['subject'] ) ? $notice['subject'] : __( 'Your Subscription is About to Renew or Expire', 'edd-recurring' );
		$subject    = $this->filter_reminder_template_tags( $subject, $subscription_id );

		EDD()->emails->send( $email_to, $subject, $message );

		$log_id = wp_insert_post(
			array(
				'post_title'   => __( 'LOG - Subscription Reminder Notice Sent', 'edd-recurring' ),
				'post_name'    => 'log-subscription-reminder-notice-' . $subscription_id . '_sent-' . $this->subscription->customer_id . '-' . md5( time() ),
				'post_type'    => 'edd_subscription_log',
				'post_status'  => 'publish'
			 )
		);

		add_post_meta( $log_id, '_edd_recurring_log_customer_id', $this->subscription->customer_id );
		add_post_meta( $log_id, '_edd_recurring_log_subscription_id', $subscription_id );
		add_post_meta( $log_id, '_edd_recurring_reminder_notice_id', (int) $notice_id );

		if ( isset( $notice[ 'type' ] ) ) {
			add_post_meta( $log_id, '_edd_recurring_reminder_notice_type', $notice[ 'type' ] );
		}
		
		wp_set_object_terms( $log_id, 'subscription_reminder_notice', 'edd_log_type', false );

		// Prevents reminder notices from being sent more than once
		add_user_meta( $this->subscription->customer->user_id, sanitize_key( '_edd_recurring_reminder_sent_' . $subscription_id . '_' . $notice_id ), time() );

	}

	public function filter_reminder_template_tags( $text = '', $subscription_id = 0 ) {

		$download      = get_post( $this->subscription->product_id );
		$customer_name = $this->subscription->customer->name;
		$expiration    = strtotime( $this->subscription->expiration );

		$text = str_replace( '{name}', $customer_name,  $text );
		$text = str_replace( '{subscription_name}', $download->post_title,   $text );
		$text = str_replace( '{expiration}', date_i18n( 'F j, Y', $expiration ), $text );
		$text = str_replace( '{amount}', edd_currency_filter( edd_format_amount( $this->subscription->recurring_amount ) ), $text );

		return $text;
	}

	public function payment_received_template_tags( $text = '', $amount = '' ) {

		$customer_name = $this->subscription->customer->name;
		$download      = get_post( $this->subscription->product_id );
		$expiration    = strtotime( $this->subscription->expiration );

		$text = str_replace( '{name}', $customer_name, $text );
		$text = str_replace( '{subscription_name}', $download->post_title, $text );
		$text = str_replace( '{expiration}', date_i18n( 'F j, Y', $expiration ), $text );
		$text = str_replace( '{amount}', edd_currency_filter( edd_format_amount( $amount ) ), $text );

		return $text;
	}


}