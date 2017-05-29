<?php

/**
 * Integrates EDD Recurring with the Auto Register extension
 *
 * @since v2.4
 */
class EDD_Recurring_Auto_Register {


	/**
	 * Get things started
	 *
	 * @since  2.4
	 * @return void
	 */
	public function __construct() {

		if ( ! function_exists( 'edd_auto_register' ) ) {
			return;
		}

		add_action( 'edd_recurring_pre_create_payment_profiles', array( $this, 'auto_register' ), 10, 1 );
	}

	/**
	 * Run the auto-register plugin function prior to creating payment profiles
	 *
	 * @since  2.4
	 * @param  EDD_Recurring_Gateway $gateway_data  Gateway Object
	 * @return void
	 */
	public function auto_register( EDD_Recurring_Gateway $gateway_data ) {

		// While processign a recurring payment, we need to run before the 'edd_purchase' hook
		// This allows us to log the user in prior to auto register's default action
		add_filter( 'edd_auto_register_login_user', '__return_true' );

		edd_auto_register()->create_user( $gateway_data->purchase_data );

	}

}
