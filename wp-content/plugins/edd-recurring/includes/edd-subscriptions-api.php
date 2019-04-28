<?php
/**
 * Subscribers REST API
 *
 * @package     EDD Recurring
 * @subpackage  Subscriber API Class
 * @copyright   Copyright (c) 2015, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * EDD_Subscriptions_API
 *
 * Extends the EDD_API to make the /subscriptions endpoint
 *
 * @class EDD_Subscriptions_API
 * @since 2.4
 * @author Chris K, Pippin, Topher
 */
class EDD_Subscriptions_API extends EDD_API {

	/**
	 * User ID Performing the API Request
	 *
	 * @var int
	 * @access private
	 * @since  2.4
	 */
	public $user_id = 0;
	/**
	 *
	 * @var bool
	 * @access private
	 * @since  1.7
	 */
	public $override = true;

	/**
	 * Adds to the allowed query vars list from EDD Core for API access
	 *
	 * @access public
	 * @since  2.4.3
	 * @author Topher
	 * @param  array $vars Query vars.
	 * @return string[] $vars New query vars
	 */
	public function query_vars( $vars ) {

		$vars[] = 'status';

		return $vars;
	}

	/**
	 * Safely gets the status from the URL
	 *
	 * @access private
	 * @since  2.4.3
	 * @author Topher
	 */
	private function get_status_from_url() {

		// Get the query vars.
		global $wp_query;

		$status = '';

		$allowed = array(
			'active',
			'pending',
			'failing',
			'completed',
			'cancelled',
			'expired',
		);

		// Get status information from the input.
		$input_status = isset( $wp_query->query_vars['status'] ) ? $wp_query->query_vars['status'] : null;

		if ( in_array( $input_status, $allowed ) ) {
			$status = $input_status;
		}

		if ( null !== $input_status && '' === $status ) {
			$error['error'] = sprintf( __( '\'%s\' is not a valid status.', 'edd-recurring' ), $input_status );

			return $error;
		} else {
			return $status;
		}
	}


	/**
	 * Fire up the engines.
	 *
	 * @since 2.4
	 */
	public function __construct() {
		parent::__construct();
		add_filter( 'edd_api_valid_query_modes', array( $this, 'add_valid_subscriptions_query' ) );
		add_filter( 'edd_api_output_data', array( $this, 'add_edd_subscription_endpoint' ), 10, 3 );
	}

	/**
	 * Whitelist 'subscriptions' api endpoint
	 *
	 * @since 2.4
	 *
	 * @param $queries
	 *
	 * @return array
	 */
	public function add_valid_subscriptions_query( $queries ) {
		$queries[] .= 'subscriptions';
		$this->override = false;

		return $queries;
	}

	/**
	 *
	 * Add Subscribers Endpoint
	 *
	 * @description: This method makes available the http://mycoolsite.com/edd-api/subscriptions/ endpoint
	 * @since      2.4
	 *
	 * @param $data
	 * @param $query_mode
	 * @param $api_object
	 *
	 * @return array $subscriptions
	 */
	public function add_edd_subscription_endpoint( $data, $query_mode, $api_object ) {

		// Sanity check: don't mess with other API queries!
		if ( 'subscriptions' !== $query_mode ) {
			return $data;
		}

		// Get query vars.
		global $wp_query;

		// Get the status from input.
		$status = $this->get_status_from_url();

		if ( is_array( $status ) && array_key_exists( 'error', $status ) ) {
			$error = $status;
			return $error;
		}

		// Get the customer information from the input.
		$queried_c = isset( $wp_query->query_vars['customer'] ) ? sanitize_text_field( $wp_query->query_vars['customer'] ) : null;
		$customer  = new EDD_Customer( $queried_c );

		if( ! empty( $queried_c ) && ( ! $customer || ! $customer->id > 0 ) ) {

			$error['error'] = sprintf( __( 'No customer found for %s!', 'edd-recurring' ), $queried_c );

			return $error;

		}

		$count         = 0;
		$response_data = array();
		if ( isset( $wp_query->query_vars['id'] ) &&  is_numeric( $wp_query->query_vars['id'] ) ) {

			$subscriptions = array(
				new EDD_Subscription( $wp_query->query_vars['id'] )
			);

		} else {
			$paged         = $this->get_paged();
			$per_page      = $this->per_page();
			$offset        = $per_page * ( $paged - 1 );
			$db            = new EDD_Subscriptions_DB;
			$subscriptions = $db->get_subscriptions( array(
				'number'      => $per_page,
				'offset'      => $offset,
				'customer_id' => $customer->id,
				'status'      => $status,
			) );
		}

		if ( $subscriptions ) {

			/** @var EDD_Subscription $subscription */
			foreach ( $subscriptions as $subscription ) {

				// Subscription object to array.
				$response_data['subscriptions'][ $count ]['info'] =  $subscription->to_array();


				// Subscription Payments.
				$subscription_payments = $subscription->get_child_payments();
				$response_data['subscriptions'][ $count ]['payments'] = array();

				if ( ! empty( $subscription_payments ) ) :

					foreach ( $subscription_payments as $payment ) {

						array_push( $response_data['subscriptions'][ $count ]['payments'], array(
							'id'     => $payment->ID,
							'amount' => $payment->total,
							'date'   => date_i18n( get_option( 'date_format' ), strtotime( $payment->date ) ),
							'status' => $payment->status_nicename,
						) );

					}

				endif;

				$count ++;

			}

		} elseif( ! empty( $queried_c ) ) {

			$error['error'] = sprintf( __( 'No subscriptions found for %s!', 'edd-recurring' ), $queried_c );

			return $error;

		} else {

			$error['error'] = __( 'No subscriptions found!', 'edd-recurring' );

			return $error;

		}

		return $response_data;

	}
}
