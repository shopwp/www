<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $edd_recurring_manual;

class EDD_Recurring_Manual_Payments extends EDD_Recurring_Gateway {

	public function init() {

		$this->id = 'manual';
		$this->friendly_name = __( 'Manual', 'edd-recurring' );

	}

	public function create_payment_profiles() {

		foreach( $this->subscriptions as $key => $subscription ) {
			$this->subscriptions[ $key ]['profile_id'] = md5( $this->purchase_data['purchase_key'] . $subscription['id'] );
		}

	}

	/**
	 * Determines if the subscription can be cancelled
	 *
	 * @access      public
	 * @since       2.7
	 * @return      bool
	 */
	public function can_cancel( $ret, $subscription ) {
		if( $subscription->gateway === 'manual' && in_array( $subscription->status, $this->get_cancellable_statuses() ) ) {
			return true;
		}
		return $ret;
	}

	/**
	 * Cancels a subscription.
	 *
	 * This does not actually cancel anything since there is no payment profile to cancel.
	 * It is purely for testing / demonstration purposes.
	 *
	 * @access      public
	 * @since       2.4
	 * @return      bool
	 */
	public function cancel( $subscription, $valid ) {

		if( empty( $valid ) ) {
			return false;
		}

		return true;

	}

}
$edd_recurring_manual = new EDD_Recurring_Manual_Payments;
