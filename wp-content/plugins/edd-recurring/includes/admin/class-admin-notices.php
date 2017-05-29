<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EDD_Recurring_Admin_Notices {

	public function __construct() {
		$this->init();
	}

	public function init() {
		add_action( 'admin_notices', array( $this, 'notices' ) );
	}

	public function notices() {

		if( ! edd_is_admin_page( 'edd-subscriptions' ) ) {
			return;
		}

		if( empty( $_GET['edd-message'] ) ) {
			return;
		}

		$type    = 'updated';
		$message = '';

		switch( strtolower( $_GET['edd-message'] ) ) {

			case 'updated' :

				$message = __( 'Subscription updated successfully', 'edd-recurring' );

				break;

			case 'deleted' :

				$message = __( 'Subscription deleted successfully', 'edd-recurring' );

				break;

			case 'cancelled' :

				$message = __( 'Subscription cancelled successfully', 'edd-recurring' );

				break;

			case 'renewal-added' :

				$message = __( 'Renewal payment recorded successfully', 'edd-recurring' );

				break;

			case 'renewal-not-added' :

				$message = __( 'Renewal payment could not be recorded', 'edd-recurring' );
				$type    = 'error';

				break;


		}

		if ( ! empty( $message ) ) {
			echo '<div class="' . esc_attr( $type ) . '"><p>' . $message . '</p></div>';
		}

	}

}
$edd_recurring_admin_notices = new EDD_Recurring_Admin_Notices;
