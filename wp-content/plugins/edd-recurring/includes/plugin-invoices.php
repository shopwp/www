<?php

/**
 * Integrates EDD Recurring with the EDD Invoices extension
 *
 * @since v2.5.3
 */
class EDD_Recurring_Invoices {


	/**
	 * Get things started
	 *
	 * @since  2.5.3
	 * @return void
	 */
	public function __construct() {

		if ( ! class_exists( 'EDDInvoices' ) ) {
			return;
		}

		add_filter( 'edd_invoices_acceptable_payment_statuses', array( $this, 'add_acceptable_payment_statuses' ), 10, 1 );
	}

	/**
	 * Add the payment statuses created and used by Recurring to the list of acceptable statuses when EDD Invoices is deciding if it should show the "Generate Invoice" option.
	 *
	 * @since  2.5.3
	 * @param  array $acceptable_statuses  The array containing all of the acceptable payment statuses.
	 * @return void
	 */
	public function add_acceptable_payment_statuses( $acceptable_statuses ) {

		$acceptable_statuses[] = 'edd_subscription';
		
		return $acceptable_statuses;
	}

}