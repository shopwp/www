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

			case 'subscription-note-added' :

				$message = __( 'Subscription note added successfully', 'edd-recurring' );

				break;

			case 'subscription-note-not-added' :

				$message = __( 'Subscription note could not be added', 'edd-recurring' );
				$type    = 'error';
				break;

			case 'renewal-added' :

				$message = __( 'Renewal payment recorded successfully', 'edd-recurring' );

				break;

			case 'renewal-not-added' :

				$message = __( 'Renewal payment could not be recorded', 'edd-recurring' );
				$type    = 'error';

				break;

			case 'retry-success' :

				$message = __( 'Retry succeeded! The subscription has been renewed successfully.', 'edd-recurring' );

				break;

			case 'retry-failed' :

				$message = sprintf( __( 'Retry failed. %s', 'edd-recurring' ), sanitize_text_field( urldecode( $_GET['error-message'] ) ) );
				$type    = 'error';

				break;


		}

		if ( ! empty( $message ) ) {
			echo '<div class="' . esc_attr( $type ) . '"><p>' . $message . '</p></div>';
		}

	}

}
$edd_recurring_admin_notices = new EDD_Recurring_Admin_Notices;
