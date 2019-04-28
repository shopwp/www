<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $edd_recurring_authorize;

class EDD_Recurring_Authorize extends EDD_Recurring_Gateway {

	private $md5_hash_value;

	private $api_login_id;

	private $transaction_key;

	private $is_sandbox_mode;

	/**
	 * Get Authorize started
	 */
	public function init() {

		$this->id = 'authorize';
		$this->friendly_name = __( 'Authorize.net', 'edd-recurring' );

		// Load Authorize SDK and define its contants
		$this->load_authnetaim_library();
		$this->load_authnetxml_library();
		$this->define_authorize_values();

		add_filter( 'edd_settings_gateways', array( $this, 'settings' ) );
	}

	/**
	 * Loads the Core AuthorizeNet AIM ASK
	 *
	 * @return void
	 */
	public function load_authnetaim_library() {
		require_once EDDA_PLUGIN_DIR . '/includes/anet_php_sdk/AuthorizeNet.php';
	}

	/**
	 * Loads AuthorizeNet PHP sdk
	 *
	 * @return void
	 */
	public function load_authnetxml_library() {
		require_once EDD_RECURRING_PLUGIN_DIR . 'includes/gateways/authorize/AuthnetXML/AuthnetXML.class.php';
	}

	/**
	 * Set API Login ID, Transaction Key and Mode
	 *
	 * @return void
	 */
	public function define_authorize_values() {

		$this->api_login_id    = edd_get_option( 'edda_api_login' );
		$this->transaction_key = edd_get_option( 'edd_transaction_key' );
		$this->is_sandbox_mode = edd_is_test_mode();
		$this->md5_hash_value  = edd_get_option( 'edd_authorize_md5_hash_value' );
	}

	/**
	 * Validates the form data
	 *
	 * @return void
	 */
	public function validate_fields( $data, $posted ) {

		if ( ! class_exists( 'AuthnetXML' ) && ! function_exists( 'edda_process_payment' ) ) {
			edd_set_error( 'edd_recurring_authorize_missing', __( 'Authorize gateway is not activated', 'edd-recurring' ) );
		}

		if ( empty( $this->api_login_id ) || empty( $this->transaction_key ) ) {
			edd_set_error( 'edd_recurring_authorize_settings_missing', __( 'API Login ID or Transaction key is missing', 'edd-recurring' ) );
		}
	}

	/**
	 * Creates subscription payment profiles and sets the IDs so they can be stored
	 *
	 * @return bool true on success and false on failure
	 */
	public function create_payment_profiles() {


		$card_info = $this->purchase_data['card_info'];
		$user_info = $this->purchase_data['user_info'];

		foreach( $this->subscriptions as $key => $subscription ) {

			$response = $this->create_authorize_net_subscription( $subscription, $card_info, $user_info );
			if ( $response->isSuccessful() ) {

				$this->subscriptions[ $key ]['profile_id'] = $response->subscriptionId;

				if( ! empty( $subscription['has_trial'] ) ) {

					$this->subscriptions[ $key ]['status'] = 'trialling';

				}

				$is_success = true;

			} else {

				if( isset( $response->messages->message ) ) {

					edd_set_error( 'edd_recurring_authorize_error', $response->messages->message->code . ': ' . $response->messages->message->text, 'edd-recurring' );

				} else {

					edd_set_error( 'edd_recurring_authorize_error', __( 'Your subscription cannot be created due to an error at the gateway.', 'edd-recurring' ) );

				}

				// TODO: Should log the error
				$is_success = false;

			}

		}

		return $is_success;
	}

	/**
	 * Creates a new Automatted Recurring Billing (ARB) subscription
	 *
	 * @param  array  $subscription
	 * @param  array  $card_info
	 * @param  array  $user_info
	 * @return AuthnetXML
	 */
	public function create_authorize_net_subscription( $subscription, $card_info, $user_info ) {

		// Since Authorize.net doesnt' verify the initial charge first, we have to do an authorization
		// on the initial amount.
		$is_authorized = $this->authorize_initial_charge( $card_info, $subscription['initial_amount'] );
		if ( false === $is_authorized['success'] ) {
			edd_set_error( $is_authorized['msg_id'], $is_authorized['message'] );
			edd_send_back_to_checkout('?payment-mode=' . $this->id );
		}

		$args = $this->generate_create_subscription_request_args( $subscription, $card_info, $user_info );

		// Use AuthnetXML library to create a new subscription request
		$authnet_xml = new AuthnetXML( $this->api_login_id, $this->transaction_key, $this->is_sandbox_mode );
		$authnet_xml->ARBCreateSubscriptionRequest( $args );

		return $authnet_xml;
	}

	/**
	 * Autorize the amount we need for the initial charge with Authorize.net before making our subscriptions.
	 *
	 * @param  array $card_info The card info supplied at checkout.
	 * @return array            If the auth was successful, and any error messages that are returned.
	 */
	public function authorize_initial_charge( $card_info, $amount ) {

		global $edd_options;


		$transaction = new AuthorizeNetAIM( edd_get_option( 'edda_api_login' ), edd_get_option( 'edd_transaction_key' ) );
		if(edd_is_test_mode()) {
			$transaction->setSandbox(true);
		} else {
			$transaction->setSandbox(false);
		}

		$transaction->address     = $card_info['card_address'] . ' ' . $card_info['card_address_2'];
		$transaction->city        = $card_info['card_city'];
		$transaction->country     = $card_info['card_country'];
		$transaction->state       = $card_info['card_state'];
		$transaction->zip         = $card_info['card_zip'];

		$transaction->amount      = $amount;
		$transaction->card_num    = strip_tags( trim( $card_info['card_number'] ) );
		$transaction->exp_date    = strip_tags( trim( $card_info['card_exp_month'] ) ) . '/' . strip_tags( trim( $card_info['card_exp_year'] ) );
		$transaction->recurring_billing = true;

		try {

			$response = $transaction->authorizeOnly();

			if ( $response->approved ) {

				return array( 'success' => true );

			} else {


				if( isset( $response->response_reason_text ) ) {
					$error = $response->response_reason_text;
				} elseif( isset( $response->error_message ) ) {
					$error = $response->error_message;
				} else {
					$error = '';
				}

				$msg_id = '';

				if( strpos( strtolower( $error ), 'the credit card number is invalid' ) !== false ) {
					$msg_id  =  'invalid_card';
					$message = __( 'Your card number is invalid', 'edda' );
				} elseif( strpos( strtolower( $error ), 'this transaction has been declined' ) !== false ) {
					$msg_id  =  'invalid_card';
					$message = __( 'Your card has been declined', 'edda' );
				} elseif( isset( $response->response_reason_text ) ) {
					$msg_id  =  'api_error';
					$message = $response->response_reason_text;
				} elseif( isset( $response->error_message ) ) {
					$msg_id  =  'api_error';
					$message = $response->error_message;
				} else {
					$msg_id  =  'api_error';
					$message = sprintf( __( 'An error occurred. Error data: %s', 'edda' ), print_r( $response, true ) );
				}

				return array( 'success' => false, 'msg_id' => $msg_id, 'message' => $message );

			}

		} catch ( AuthorizeNetException $e ) {

			return array( 'success' => false, 'msg_id' => 'request_error', 'message' => $e->getMessage() );

		}


	}

	/**
	 * Generates args for making a ARB create subscription request
	 *
	 * @param  array $subscription
	 * @param  array $card_info
	 * @param  array $user_info
	 * @return array
	 */
	public function generate_create_subscription_request_args( $subscription, $card_info, $user_info ) {

		// Set date to same timezone as Authorize's servers (Mountain Time) to prevent conflicts
		date_default_timezone_set( 'America/Denver' );
		$today = date( 'Y-m-d' );

		// Calculate totalOccurrences. TODO: confirm if empty or zero
		$total_occurrences = ( $subscription['bill_times'] == 0 ) ? 9999	 : $subscription['bill_times'];
		$card_details      = $this->generate_card_info( $card_info );

		$args = array(
			'subscription' 	=> array(
				'name'            => $this->generate_subscription_name( $subscription['id'], $subscription['name'], $subscription['price_id'] ),
				'paymentSchedule' => array(
					'interval'         => $this->get_interval( $subscription['period'] ),
					'startDate'        => $today,
					'totalOccurrences' => $total_occurrences,
					'trialOccurrences' => empty( $subscription['has_trial'] ) ? 1 : $subscription['trial_quantity'],
				),
				'amount'      => $subscription['recurring_amount'],
				'trialAmount' => $subscription['initial_amount'],
				'payment'     => array(
					'creditCard' => $card_details,
				),
				'billTo'      => array(
					'firstName' => $user_info['first_name'],
					'lastName'  => $user_info['last_name'],
					'zip'       => $card_info['card_zip'],
				),
			),
		);

		$args = apply_filters( 'edd_recurring_create_subscription_args', $args, $this->purchase_data['downloads'], $this->id, $subscription['id'], $subscription['price_id'] );

		return $args;
	}

	/**
	 * Given the $card_info array, generate the card info array for use with the API
	 *
	 * @since   2.4
	 * @param   array $card_info The Card Info from the checkout form
	 * @return  array            Formatted card info for the Authorize.net API
	 */
	private function generate_card_info( $card_info = array() ) {

		$card_details = array(
			'cardNumber'     => $card_info['card_number'],
			'expirationDate' => $card_info['card_exp_year'] . '-' . $card_info['card_exp_month'],
			'cardCode'       => $card_info['card_cvc'],
		);

		return $card_details;

	}

	/**
	 * Generates subscription name
	 *
	 * @param  integer $download_id
	 * @param  string  $form_title
	 * @param  integer $price_id
	 * @return string
	 */
	public function generate_subscription_name( $download_id, $form_title = '', $price_id = 0 ) {

		if ( ! empty ( $form_title ) ) {
			$subscription_name = $form_title;
		} else {
			$subscription_name = get_post_field( 'post_title', $download_id );
		}

		if ( 0 !== $price_id ) {
			$subscription_name .= ' - ' . edd_get_price_option_name( $download_id, $price_id );
		}

		return $subscription_name;
	}

	/**
	 * Gets interval length and interval unit for Authorize.net based on Give subscription period
	 *
	 * @param  string $subscription_period
	 * @return array
	 */
	public function get_interval( $subscription_period ) {

		$length = '1';
		$unit   = 'days';

		switch( $subscription_period ) {

			case 'day':
				$unit   = 'days';
				break;
			case 'week':
				$length = '7';
				$unit   = 'days';
				break;
			case 'month':
				$length = '1';
				$unit   = 'months';
				break;
			case 'quarter':
				$length = '3';
				$unit   = 'months';
				break;
			case 'semi-year':
				$length = '6';
				$unit   = 'months';
				break;
			case 'year':
				$length = '12';
				$unit   = 'months';
				break;
		}

		return compact( 'length', 'unit' );
	}

	/**
	 * Determines if the subscription can be cancelled
	 *
	 * @param  bool               $ret
	 * @param  Give_Subscription  $subscription
	 * @return bool
	 */
	public function can_cancel( $ret, $subscription ) {
		if( $subscription->gateway === 'authorize' && ! empty( $subscription->profile_id ) && in_array( $subscription->status, $this->get_cancellable_statuses() ) ) {
			$ret = true;
		}
		return $ret;
	}


	/**
	 * Cancels a subscription
	 *
	 * @param  Give_Subscription   $subscription
	 * @param  bool                $valid
	 * @return bool
	 */
	public function cancel( $subscription, $valid ) {

		if ( empty ( $valid ) ) {
			return false;
		}

		$response = $this->cancel_authorize_net_subscription( $subscription->profile_id );

		return $response;
	}

	/**
	 * Cancel a ARB subscription based for a given subscription id
	 *
	 * @param  string      $anet_subscription_id
	 * @return AuthnetXML
	 */
	public function cancel_authorize_net_subscription( $anet_subscription_id ) {

		// Use AuthnetXML library to create a new subscription request
		$authnet_xml = new AuthnetXML( $this->api_login_id, $this->transaction_key, $this->is_sandbox_mode );
		$authnet_xml->ARBCancelSubscriptionRequest( array( 'subscriptionId' => $anet_subscription_id ) );

		return $authnet_xml->isSuccessful();
	}

	/**
	 * Determines if the subscription can be updated
	 *
	 * @access      public
	 * @since       2.4
	 * @return      bool
	 */
	public function can_update( $ret, $subscription ) {
		if( $subscription->gateway === 'authorize' && ! empty( $subscription->profile_id ) && ( 'active' === $subscription->status || 'failing' === $subscription->status || 'trialling' === $subscription->status ) ) {
			return true;
		}
		return $ret;
	}

	/**
	 * Process the update payment form
	 *
	 * @since  2.4
	 * @param  int  $subscriber    EDD_Recurring_Subscriber
	 * @param  int  $subscription  EDD_Subscription
	 * @return void
	 */
	public function update_payment_method( $subscriber, $subscription ) {

		$card_number    = isset( $_POST['card_number'] ) && is_numeric( $_POST['card_number'] )       ? $_POST['card_number']    : '';
		$card_exp_month = isset( $_POST['card_exp_month'] ) && is_numeric( $_POST['card_exp_month'] ) ? $_POST['card_exp_month'] : '';
		$card_exp_year  = isset( $_POST['card_exp_year'] ) && is_numeric( $_POST['card_exp_year'] )   ? $_POST['card_exp_year']  : '';
		$card_cvc       = isset( $_POST['card_cvc'] ) && is_numeric( $_POST['card_cvc'] )             ? $_POST['card_cvc']       : '';

		$card_zip       = isset( $_POST['card_zip'] ) ? sanitize_text_field( $_POST['card_zip'] ) : '';

		$card_info = array(
			'card_number'    => $card_number,
			'card_exp_month' => $card_exp_month,
			'card_exp_year'  => $card_exp_year,
			'card_cvc'       => $card_cvc,
		);

		$card_details = $this->generate_card_info( $card_info );
		$values       = array_search( '', $card_details );

		if ( ! empty( $values ) ) {
			edd_set_error( 'edd_recurring_authnet', __( 'Please enter all required fields.', 'edd-recurring' ) );
		}

		$errors = edd_get_errors();

		if ( ! $errors ) {
			// No errors in Authorize.net, continue on through processing
			try {

				$authnet_xml = new AuthnetXML( $this->api_login_id, $this->transaction_key, $this->is_sandbox_mode );
				$args = array(
					'subscriptionId' => $subscription->profile_id,
					'subscription'   => array(
						'payment'     => array(
							'creditCard' => $card_details,
						),
						'billTo'     => array(
							'zip' => $card_zip,
						),
					),
				);

				$authnet_xml->ARBUpdateSubscriptionRequest( $args );

				if ( ! $authnet_xml->isSuccessful() ) {

					if( isset( $authnet_xml->messages->message ) ) {

						edd_set_error( 'edd_recurring_authorize_error', $authnet_xml->messages->message->code . ': ' . $authnet_xml->messages->message->text, 'edd-recurring' );

					} else {

						edd_set_error( 'edd_recurring_authorize_error', __( 'There was an error updating your payment method.', 'edd-recurring' ) );

					}

				}
				//var_dump( $authnet_xml ); exit;


			} catch ( Exception $e ) {

				edd_set_error( 'edd_recurring_authnet', $e );

			}
		}

	}

	/**
	 * Processes webhooks from the payment processor
	 *
	 * @access      public
	 * @since       2.4
	 * @return      void
	 */
	public function process_webhooks() {

		if ( empty( $_GET['edd-listener'] ) || $this->id !== $_GET['edd-listener'] || ! $this->is_silent_post_valid( $_POST ) ) {
			return;
		}

		$anet_subscription_id = intval( $_POST['x_subscription_id'] );

		if ( $anet_subscription_id ) {

			$response_code = intval( $_POST['x_response_code'] );
			$reason_code   = intval( $_POST['x_response_reason_code'] );

			$subscription = new EDD_Subscription( $anet_subscription_id, true );

			if ( empty( $subscription ) ) {
				return;
			}

			if ( 1 == $response_code ) {

				// Approved
				$renewal_amount = sanitize_text_field( $_POST['x_amount'] );
				$transaction_id = sanitize_text_field( $_POST['x_trans_id'] );

				$this->process_approved_transaction( $subscription, $renewal_amount, $transaction_id );
				do_action( 'edd_recurring_authorizenet_silent_post_payment', $subscription );

			} elseif ( 2 == $response_code ) {

				// Declined
				$subscription->failing();
				do_action( 'edd_recurring_payment_failed', $subscription );
				do_action( 'edd_recurring_authorizenet_silent_post_error', $subscription );

			} elseif ( 3 == $response_code || 8 == $reason_code ) {

				// An expired card
				$subscription->failing();
				do_action( 'edd_recurring_payment_failed', $subscription );
				do_action( 'edd_recurring_authorizenet_silent_post_error', $subscription );

			} else {

				// Other Error
				do_action( 'edd_recurring_authorizenet_silent_post_error', $subscription );

			}
		}
	}

	/**
	 * Determines if the silent post is valid by verifying the MD5 Hash
	 *
	 * @access  public
	 * @since   2.4
	 * @param   array $request The Request array containing data for the silent post
	 * @return  bool
	 */
	public function is_silent_post_valid( $request ) {

		$auth_md5 = isset( $request['x_MD5_Hash'] ) ? $request['x_MD5_Hash'] : '';

		//Sanity check to ensure we have an MD5 Hash from the silent POST
		if( empty( $auth_md5 ) ) {
			return false;
		}

		$str           = $this->md5_hash_value . $request['x_trans_id'] . $request['x_amount'];
		$generated_md5 = strtoupper( md5( $str ) );

		return hash_equals( $generated_md5, $auth_md5 );
	}

	/**
	 * Process approved transaction
	 *
	 * @param  string $subscription
	 * @param  string $amount
	 * @param  string $transaction_id
	 * @return bool|EDD_Subscription
	 */
	public function process_approved_transaction( EDD_Subscription $subscription, $amount, $transaction_id ) {

		if ( empty( $subscription ) ) {
			return false;
		}

		$payment_id = $subscription->add_payment( compact( 'amount', 'transaction_id' ) );

		if ( ! empty( $payment_id ) ) {
			$subscription->renew( $payment_id );
		}

		return $subscription;
	}

	/**
	 * Add Hash Key setting
	 *
	 * @param  array $settings
	 * @since  2.4
	 * @return array
	 */
	public function settings( $settings ) {

		if( isset( $settings['authorize'] ) ) {
			$settings['authorize'][] = array(
				'id'   => 'edd_authorize_md5_hash_value',
				'name' => __( 'MD5-Hash', 'edda'),
				'desc' => __( 'If you are accepting recurring donations with Authorize.net then you will need to configure a "Silent Post URL" - this field allows you to confirm that is properly configured and the communication between your website and Authorize.net is intact.', 'edd-authorize' ),
				'type' => 'text'
			);
		}

		return $settings;
	}

	/**
	 * Link the recurring profile in Authorize.net.
	 *
	 * @since  2.4.4
	 * @param  string $profile_id   The recurring profile id
	 * @param  object $subscription The Subscription object
	 * @return string               The link to return or just the profile id
	 */
	public function link_profile_id( $profile_id, $subscription ) {

		if( ! empty( $profile_id ) ) {
			$html     = '<a href="%s" target="_blank">' . $profile_id . '</a>';

			$payment  = new EDD_Payment( $subscription->parent_payment_id );
			$base_url = 'live' === $payment->mode ? 'https://authorize.net/' : 'https://sandbox.authorize.net/';
			$link     = esc_url( $base_url . 'ui/themes/sandbox/ARB/SubscriptionDetail.aspx?SubscrID=' . $profile_id );

			$profile_id = sprintf( $html, $link );
		}

		return $profile_id;

	}

}
$edd_recurring_authorize = new EDD_Recurring_Authorize;
