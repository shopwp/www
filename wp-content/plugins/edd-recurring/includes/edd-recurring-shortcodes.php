<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Recurring Shortcodes
 *
 * Adds additional recurring specific shortcodes as well as hooking into existing EDD core shortcodes to add additional subscription functionality
 *
 * @since  2.4
 */
class EDD_Recurring_Shortcodes {

	/**
	 * Get things started
	 */
	function __construct() {

		//Make Recurring template files work
		add_filter( 'edd_template_paths', array( $this, 'add_template_stack' ) );

		add_shortcode( 'edd_subscriptions', array( $this, 'edd_subscriptions' ) );

		// Show recurring details on the [edd_receipt]
		add_action( 'edd_payment_receipt_after_table', array( $this, 'subscription_receipt' ), 1, 2 );

		// Process form submission to update a payment method. This then passes the handling on to EDD_Recurring_Gateway
		add_action( 'edd_recurring_update_payment', array( $this, 'verify_profile_update_setup' ), 10 );

		/*
		 * These are deprecated shortcodes
		 */
		add_shortcode( 'edd_recurring_update', '__return_null' );
		add_shortcode( 'edd_recurring_cancel', '__return_null' );
	}


	/**
	 * Adds our templates dir to the EDD template stack
	 *
	 * @since 2.4
	 *
	 * @param $paths
	 *
	 * @return mixed
	 */
	public function add_template_stack( $paths ) {

		$paths[57] = EDD_RECURRING_PLUGIN_DIR . 'templates/';

		return $paths;

	}


	/**
	 * Subscription Receipt
	 *
	 * @description: Displays the recurring details within the [edd_receipt] shortcode
	 *
	 * @since      2.4
	 *
	 * @return mixed
	 */
	public function subscription_receipt() {

		ob_start();

		edd_get_template_part( 'shortcode', 'subscription-receipt' );

		echo ob_get_clean();

	}


	/**
	 * Displays a profile cancellation link
	 *
	 * @since  1.0
	 * @return string
	 */
	public function cancel_link( $atts, $content = null ) {
		global $user_ID;

		if ( ! is_user_logged_in() ) {
			return false;
		}

		if ( ! EDD_Recurring_Customer::is_customer_active( $user_ID ) ) {
			return false;
		}

		if ( 'cancelled' === EDD_Recurring_Customer::get_customer_status( $user_ID ) ) {
			return false;
		}

		$atts = shortcode_atts( array(
			'text' => ''
		), $atts );

		$cancel_url = 'https://www.paypal.com/cgi-bin/customerprofileweb?cmd=_manage-paylist';
		$link       = '<a href="%s" class="edd-recurring-cancel" target="_blank" title="%s">%s</a>';
		$link       = sprintf(
			$link,
			$cancel_url,
			__( 'Cancel your subscription', 'edd-recurring' ),
			empty( $atts['text'] ) ? __( 'Cancel Subscription', 'edd-recurring' ) : esc_html( $atts['text'] )
		);

		return apply_filters( 'edd_recurring_cancel_link', $link, $user_ID );
	}

	/**
	 * Sets up the process of verifying the saving of the updated payment method
	 *
	 * @since  x.x
	 * @return void
	 */
	public function verify_profile_update_setup() {

		if ( ! is_user_logged_in() ) {
			wp_die( __( 'Invalid User ID' ) );
		}

		$user_id = get_current_user_id();

		$this->verify_profile_update_action( $user_id );

	}


	/**
	 * Verify and fire the hook to update a recurring payment method
	 *
	 * @since  x.x
	 *
	 * @param  int $user_id The User ID to update
	 *
	 * @return void
	 */
	private function verify_profile_update_action( $user_id ) {

		$passed_nonce = isset( $_POST['edd_recurring_update_nonce'] ) ? $_POST['edd_recurring_update_nonce'] : false;

		if ( false === $passed_nonce || ! isset( $_POST['_wp_http_referer'] ) ) {
			wp_die( __( 'Invalid Payment Update', 'edd-recurring' ) );
		}

		$verified = wp_verify_nonce( $passed_nonce, 'update-payment' );

		if ( 1 !== $verified || (int) $user_id !== (int) get_current_user_id() ) {
			wp_die( __( 'Unable to verify payment update. Please try again later.', 'edd-recurring' ) );
		}

		// Check if a subscription_id is passed to use the new update methods
		if ( isset( $_POST['subscription_id'] ) && is_numeric( $_POST['subscription_id'] ) ) {
			do_action( 'edd_recurring_update_subscription_payment_method', $user_id, $_POST['subscription_id'], $verified );
		}

	}


	/**
	 * Subscription History
	 *
	 * Provides users with an historical overview of their purchased subscriptions
	 *
	 * @since      2.4
	 * @since      2.7.14 Modified to call the EDD_Recurring()->subscriptions_view() function.
	 */
	public function edd_subscriptions() {
		return EDD_Recurring()->subscriptions_view();

	}


}
new EDD_Recurring_Shortcodes();
