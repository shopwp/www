<?php

use EDD\Gateways\PayPal\Exceptions\API_Exception;

/**
 * Integrates EDD Recurring with the Software Licensing extension
 *
 * @since v2.4
 */
class EDD_Recurring_Software_Licensing {

	protected $db;

	/**
	 * Get things started
	 *
	 * @since  2.4
	 * @return void
	 */
	public function __construct() {

		if ( ! function_exists( 'edd_software_licensing' ) ) {
			return;
		}

		$this->db = new EDD_Subscriptions_DB;

		add_filter( 'edd_recurring_subscription_pre_gateway_args', array( $this, 'set_recurring_amount' ), 10, 2 );
		add_filter( 'edd_sl_license_exp_length', array( $this, 'set_license_length_for_trials' ), 10, 4 );
		add_filter( 'edd_sl_can_extend_license', array( $this, 'disable_license_extension' ), 10, 2 );
		add_filter( 'edd_sl_can_renew_license', array( $this, 'disable_license_extension' ), 10, 2 );
		add_filter( 'edd_recurring_subscription_pre_gateway_args', array( $this, 'add_upgrade_and_renewal_flag' ), 10, 2 );
		add_filter( 'edd_sl_send_scheduled_reminder_for_license', array( $this, 'maybe_suppress_scheduled_reminder_for_license' ), 10, 3 );
		add_filter( 'edd_get_renewals_by_date', array( $this, 'renewals_by_date' ), 10, 4 );
		add_filter( 'edd_subscription_can_renew', array( $this, 'can_renew_subscription' ), 10, 2 );
		add_filter( 'edd_subscription_renew_url', array( $this, 'get_renew_url' ), 10, 2 );
		add_filter( 'edd_recurring_show_stripe_update_payment_method_notice', array( $this, 'maybe_suppress_update_payment_method_notice' ), 10, 2 );
		add_filter( 'edd_recurring_create_subscription_args', array( $this, 'handle_subscription_upgrade_billing' ), 10, 7 );
		add_filter( 'edd_recurring_pre_record_signup_args', array( $this, 'handle_subscription_upgrade_expiration' ), 10, 2 );
		add_filter( 'edd_cart_contents', array( $this, 'remove_trial_flags_on_renewals_and_upgrades' ) );
		add_filter( 'edd_sl_get_time_based_pro_rated_upgrade_cost', array( $this, 'reset_upgrade_cost_when_trialling' ), 10, 4 );
		add_filter( 'edd_sl_get_cost_based_pro_rated_upgrade_cost', array( $this, 'reset_upgrade_cost_when_trialling' ), 10, 4 );

		add_action( 'edd_recurring_post_create_payment_profiles', array( $this, 'handle_subscription_upgrade' ) );
		add_action( 'edd_recurring_post_create_payment_profiles', array( $this, 'handle_manual_license_renewal' ) );
		add_action( 'edd_complete_download_purchase', array( $this, 'handle_non_subscription_upgrade' ), -1, 5 );
		add_action( 'edd_subscription_post_renew', array( $this, 'renew_license_keys' ), 10, 4 );
		add_action( 'edd_recurring_add_subscription_payment', array( $this, 'set_renewal_flag' ), 10, 2 );
		add_action( 'edd_sl_column_purchased', array( $this, 'licenses_table' ), 10 );
		add_action( 'edd_subscription_after_tables', array( $this, 'subscription_details' ), 10 );
		add_action( 'edd_sl_license_key_details', array( $this, 'license_key_details' ) );
		add_action( 'edd_purchase_form_before_submit', array( $this, 'checkout_upgrade_details' ), 9 );
		add_action( 'edd_purchase_form_before_submit', array( $this, 'checkout_license_renewal_details' ), 9 );
		add_action( 'edd_sl_license_metabox_after_license_length', array( $this, 'free_trial_settings_notice' ) );
		add_action( 'edd_recurring_check_expiration', array( $this, 'maybe_sync_license_expiration_on_check_expiration' ), 10, 2 );
		if ( function_exists( 'edd_get_order' ) ) {
			add_action( 'edd_refund_order', array( $this, 'rollback_expiration_on_renewal_refund' ), 10, 3 );
		} else {
			add_action( 'edd_post_refund_payment', array( $this, 'rollback_expiration_on_renewal_refund' ) );
		}
		add_action( 'edd_recurring_post_record_signup', array( $this, 'cancel_failed_subscription_during_renewal' ), 10, 3 );

	}

	/**
	 * Modifies the recurring amounts in respect to renewal discounts and license upgrades
	 *
	 * @since  2.4
	 * @param array $args This array contains information about the product. The the edd_recurring_subscription_pre_gateway_args filter in edd-recurring-gateway for a list of keys.
	 * @param array $item The information about this item, as found in the edd_gateway_[ gateway name ] hook.
	 * @return array The modified args for the edd_recurring_subscription_pre_gateway_args filter.
	 */
	public function set_recurring_amount( $args = array(), $item = array() ) {

		$adjust  = false;
		$enabled = get_post_meta( $args['id'], '_edd_sl_enabled', true );

		// Only set up a discount if software licensing is enabled for the product.
		if ( $enabled ) {
			$discount = edd_sl_get_renewal_discount_percentage( 0, $item['id'] );
		} else {
			$discount = 0;
		}

		// This is an upgrade.
		if ( ! empty( $item['item_number']['options']['is_upgrade'] ) || ! empty( $item['item_number']['options']['is_renewal'] ) ) {

			if ( edd_has_variable_prices( $item['id'] ) ) {

				$price = edd_get_price_option_amount( $args['id'], $args['price_id'] );

			} else {

				$price = edd_get_download_price( $item['id'] );

			}

			if ( $discount > 0 ) {

				$args['recurring_amount'] = (float) edd_sanitize_amount( $price - ( $price * ( $discount / 100 ) ) );

			} else {

				$args['recurring_amount'] = (float) edd_sanitize_amount( $price );

			}

			// Set the tax amount according to whether taxes are inclusive or exclusive.
			if ( edd_use_taxes() ) {

				if ( edd_prices_include_tax() ) {

					// If the store is set to bake taxes into the price, bake the taxes into the price.
					$pre_tax               = $args['recurring_amount'] / ( 1 + edd_get_tax_rate() );
					$args['recurring_tax'] = $args['recurring_amount'] - $pre_tax;

				} else {

					// If the store is set to add tax on-top-of the price, add the taxes to the price.
					$args['recurring_tax']    = $args['recurring_amount'] * ( edd_get_tax_rate() );
					$args['recurring_amount'] = $args['recurring_amount'] + $args['recurring_tax'];
				}
			}

			// This is not an upgrade, but rather is a manual renewal, or an original purchase.
		} else {

			if ( $discount > 0 ) {

				$renewal_discount = ( $args['recurring_amount'] * ( $discount / 100 ) );

				$args['recurring_amount'] -= $renewal_discount;
				$args['recurring_amount']  = (float) edd_sanitize_amount( $args['recurring_amount'] );

				/**
				 * The recurring amount has been adjusted so we now need to re-calculate taxes.
				 *
				 * The recurring amount has taxes included in it already, so we work backwards,
				 * just like calculated taxes when prices are inclusive of tax.
				 */
				$pre_tax               = $args['recurring_amount'] / ( 1 + edd_get_tax_rate() );
				$args['recurring_tax'] = $args['recurring_amount'] - $pre_tax;

			}
		}

		return $args;

	}

	/**
	 * Sets the length of a license key for free trials
	 *
	 * @since  2.6
	 * @return string
	 */
	public function set_license_length_for_trials( $expiration, $payment_id, $download_id, $license_id ) {

		if( ! defined( 'EDD_SL_VERSION' ) || version_compare( EDD_SL_VERSION, '3.5', '<' ) ) {
			return $expiration; // We need version 3.5 or later
		}

		$license  = edd_software_licensing()->get_license( $license_id );
		$payments = $license->payment_ids;

		if ( count( $payments ) > 1 || ! edd_recurring()->has_free_trial( $download_id, $license->price_id ) ) {
			return $expiration;
		}

		// Only modify the expiration during initial payments, not renewals.
		if ( did_action( 'edd_subscription_pre_renew' ) ) {
			return $expiration;
		}

		$adjust_license_expiration = ! edd_get_option( 'recurring_one_time_trials' );
		if ( ! $adjust_license_expiration ) {
			/*
			 * We're in here because "one time trials" is being enforced. Now we have to check
			 * if this license is the customer's first trial, and only change the expiration
			 * date it if is.
			 */
			edd_debug_log( sprintf( 'Recurring - Determining trial eligibility for license #%d.', $license_id ) );

			$subscription = $this->get_subscription_of_license( $license_id );

			if ( $subscription instanceof EDD_Subscription ) {
				/*
				 * Our first preference is to use the subscription record as our "record of truth".
				 * If that exists (it may not always exist yet) and is trialling, then we can
				 * assume the license is as well.
				 *
				 * We can't always rely on `edd_recurring()->has_trialed()` (further below), because if we
				 * call that _after_ the subscription record has been created, then that will return
				 * `true`, even though it's talking about this _current_ payment that's being finished up.
				 *
				 * @link https://github.com/easydigitaldownloads/edd-recurring/issues/1479
				 */
				$adjust_license_expiration = ( 'trialling' === $subscription->status );
				edd_debug_log( sprintf( '-- Recurring - Subscription object found for license #%d. Status: %s', $license_id, $subscription->status ) );
			} else {
				// If our customer record exists, use that email, otherwise set it to false so it defaults to the currently logged in customer's email
				$email = ! empty( $license->customer ) && ! empty( $license->customer->email ) ? $license->customer->email : '';

				$adjust_license_expiration = ! edd_recurring()->has_trialed( $download_id, $email );

				edd_debug_log('-- Recurring - No subscription object found.' );
			}
		}

		if ( $adjust_license_expiration ) {
			// set expiration to trial length
			$trial_period = edd_recurring()->get_trial_period( $download_id, $license->price_id );
			$expiration = '+' . $trial_period['quantity'] . ' ' . $trial_period['unit'];

			edd_debug_log( sprintf( '-- Recurring - Adjusting expiration of license #%d to sync with trial period (%s).', $license_id, json_encode( $trial_period ) ) );
		} else {
			edd_debug_log( sprintf( '-- Recurring - Not adjusting expiration of license #%d; ineligible for trial.', $license_id ) );
		}

		return $expiration;
	}

	/**
	 * Disables the Renew/Extend link in [edd_license_keys] for licenses that are tied to an active, trialling, or failing subscription
	 *
	 * @since  2.4
	 * @return bool
	 */
	public function disable_license_extension( $can_extend, $license_id = 0 ) {

		$sub = $this->get_subscription_of_license( $license_id );

		if( ! empty( $sub ) && $sub->id > 0 ) {

			if( 'failing' == $sub->status || 'active' == $sub->status || 'trialling' == $sub->status ) {

				$can_extend = false;
			}

		}

		return $can_extend;

	}

	/**
	 * Disables the license key renewal reminders when a license has an active subscription
	 *
	 * @since  2.4
	 * @return bool
	 */
	public function maybe_suppress_scheduled_reminder_for_license( $send = true, $license_id = 0, $notice_id = 0 ) {

		$sub = $this->get_subscription_of_license( $license_id );

		if( ! empty( $sub ) && 'active' == $sub->status ) {
			$send = false;
		}

		return $send;

	}

	/**
	 * Adds edd_subscription status to the renewals by date query
	 *
	 * @since  2.5
	 * @return array
	 */
	public function renewals_by_date( $args, $day, $month, $year ) {

		$args['status'][] = 'edd_subscription';

		return $args;
	}

	/**
	 * Determines if a subscription with a license key can be renewed
	 *
	 * @since  2.5
	 * @return bool
	 */
	public function can_renew_subscription( $can_renew, EDD_Subscription $subscription ) {

		$license = edd_software_licensing()->get_license_by_purchase( $subscription->parent_payment_id, $subscription->product_id );

		if( ! empty( $license ) && 'expired' == edd_software_licensing()->get_license_status( $license->ID ) && edd_sl_renewals_allowed() ) {
			$can_renew = true;
		}

		return $can_renew;
	}

	/**
	 * Retrieves the renewal URL
	 *
	 * @since  2.5
	 * @return bool
	 */
	public function get_renew_url( $url, EDD_Subscription $subscription ) {

		$license = edd_software_licensing()->get_license_by_purchase( $subscription->parent_payment_id, $subscription->product_id );

		if( ! empty( $license ) ) {
			$url = add_query_arg( array(
				'edd_license_key' => edd_software_licensing()->get_license_key( $license->ID ),
				'download_id'     => $subscription->product_id
			), edd_get_checkout_uri() );
		}

		return $url;
	}

	/**
	 * Prevents the notice about updating payment method from showing when customer only has one subscription and we're processing an upgrade
	 *
	 * @since  2.5
	 * @return bool
	 */
	public function maybe_suppress_update_payment_method_notice( $show_notice, $notice_subs ) {

		if( $show_notice && count( $notice_subs ) < 2 && $this->cart_has_upgrade() ) {

			$show_notice = false;

		}

		return $show_notice;

	}

	/**
	 * Removes the trial flags from cart items when purchasing a renewal or upgrade
	 *
	 * @since  2.7
	 * @return bool
	 */
	public function remove_trial_flags_on_renewals_and_upgrades( $cart_contents ) {

		if( $cart_contents ) {

			foreach( $cart_contents as $key => $item ) {

				if( empty( $item['options']['is_renewal'] ) && empty( $item['options']['is_upgrade'] ) ) {
					continue;
				}

				if( ( isset( $item['options']['recurring'] ) && isset( $item['options']['recurring']['trial_period'] ) ) || isset( $item['options']['is_upgrade'] ) ) {
					unset( $cart_contents[ $key ]['options']['recurring']['trial_period'] );
				}
			}
		}

		return $cart_contents;

	}

	/**
	 * Adds upgrade flag to subscription details during checkout
	 *
	 * Replaced by add_upgrade_and_renewal_flag()
	 *
	 * @since  2.4
	 * @return array
	 */
	public function add_upgrade_flag( $subscription = array(), $item = array() ) {
		$this->add_upgrade_and_renewal_flag( $subscription, $item );
	}

	/**
	 * Adds upgrade and renewal flag to subscription details during checkout
	 *
	 * @since  2.6.3
	 * @return array
	 */
	public function add_upgrade_and_renewal_flag( $subscription = array(), $item = array() ) {

		if( isset( $item['item_number']['options']['is_upgrade'] ) ) {

			$license_id = $item['item_number']['options']['license_id'];

			$subscription['is_upgrade']          = true;

			$sub = $this->get_subscription_of_license( $license_id );
			if ( $sub ) {
				$subscription['old_subscription_id'] = $sub->id;
			}

		} elseif( isset( $item['item_number']['options']['is_renewal'] ) && isset( $item['item_number']['options']['license_id'] ) ) {

			$license_id = $item['item_number']['options']['license_id'];
			$sub        = $this->get_subscription_of_license( $license_id, array(
				'status' => array( 'active', 'trialling', 'failing' )
			) );

			if( $sub ) {
				$subscription['is_renewal']          = true;
				$subscription['old_subscription_id'] = $sub->id;
			}

		}

		return $subscription;

	}

	/**
	 * If a license has an associated subscription and that subscription is currently trialling, the upgrade
	 * cost is modified to be the full amount of the new product.
	 *
	 * @param float $prorated_price The prorated cost to upgrade the license.
	 * @param int   $license_id     ID of the license being upgraded.
	 * @param float $old_price      Price of the license being upgraded.
	 * @param float $new_price      Price of the new license level.
	 *
	 * @since 2.10.1
	 * @return float The prorated cost to upgrade the license.
	 */
	public function reset_upgrade_cost_when_trialling( $prorated_price, $license_id, $old_price, $new_price ) {
		$subscription = $this->get_subscription_of_license( $license_id );

		if ( ! $subscription ) {
			return $prorated_price;
		}

		return 'trialling' === $subscription->get_status() ? $new_price : $prorated_price;
	}

	/**
	 * When upgrading a license, set a trial period so that we avoid having a license that expires prior to the subscription,
	 * and renew the subscription at the next expiration.
	 *
	 * @since 2.7.1
	 *
	 * @param array                      $args          Arguments used to create the subscription.
	 * @param array                      $downloads     All downloads for this order.
	 * @param string                     $gateway       Gateway slug.
	 * @param int                        $download_id   ID of the download for this subscription.
	 * @param int|false                  $price_id      Price ID for the download.
	 * @param array                      $subscription  All subscription data used for creating the subscription.
	 * @param EDD_Recurring_Gateway|null $gateway_class Gateway object.
	 *
	 * @return array
	 * @throws Exception
	 */
	public function handle_subscription_upgrade_billing( $args, $downloads, $gateway, $download_id, $price_id, $subscription = array(), $gateway_class = null ) {
		$downloads = ! is_array( $downloads ) ? array() : $downloads;

		foreach ( $downloads as $download ) {

			// Account for the fact that PayPal Express deals with post-payment creation, which means we have item_number in play.
			$options = isset( $download['item_number']['options'] ) ? $download['item_number']['options'] : $download['options'];

			if ( ! isset( $options['is_upgrade'] ) ) {
				continue;
			}

			if ( (int) $download['id'] !== (int) $download_id ) {
				continue;
			}

			// Determine if there is no price_id needed to be checked.
			if ( isset( $options['price_id'] ) && is_numeric( $options['price_id'] ) ) {
				if ( $price_id != $options['price_id'] ) {
					continue;
				}
			}

			$license_id = isset( $options['license_id'] ) ? $options['license_id'] : false;
			if ( empty( $license_id ) ) {
				continue;
			}

			$license = edd_software_licensing()->get_license( $license_id );
			if ( false === $license ) {
				continue;
			}

			/*
			 * If the license never expires, then exit now.
			 * This logic to sync up with the license expiration date is not necessary if there is no expiration date.
			 * @link https://github.com/easydigitaldownloads/edd-recurring/issues/1311
			 */
			if ( empty( $license->expiration ) ) {
				continue;
			}

			// Due to the order of payment -> subscriptions -> license modification, we need to get all the data for the new expiration
			// directly from the upgrade paths and new download, instead of the existing license, as it has not changed yet.
			$upgrade_paths = edd_sl_get_license_upgrades( $license->ID );

			edd_debug_log( sprintf( 'Recurring - License Upgrade paths: %s', print_r( $upgrade_paths, true ) ) );

			$upgrade_path  = ! empty( $upgrade_paths[ $options['upgrade_id'] ] ) ? $upgrade_paths[ $options['upgrade_id'] ] : false;
			if ( empty( $upgrade_path ) ) {
				continue;
			}

			edd_debug_log( sprintf( 'Recurring - Found upgrade path: %s', print_r( $upgrade_path, true ) ) );
			$upgraded_download = new EDD_SL_Download( $upgrade_path['download_id'] );

			if ( $upgraded_download->has_variable_prices() ) {
				$price_id             = $upgrade_path['price_id'];
				$download_is_lifetime = $upgraded_download->is_price_lifetime( $upgrade_path['price_id'] );
			} else {
				$price_id             = false;
				$download_is_lifetime = $upgraded_download->is_lifetime();
			}

			if ( $download_is_lifetime ) {
				continue;
			}

			$exp_unit   = $upgraded_download->get_expiration_unit( $price_id );
			$exp_length = $upgraded_download->get_expiration_length( $price_id );

			if( empty( $exp_unit ) ) {
				$exp_unit = 'years';
			}

			if( empty( $exp_length ) ) {
				$exp_length = '1';
			}

			// Get the start time of the current period
			$previous_start_time = strtotime( '-' . $license->license_length(), $license->expiration );

			// Sync the trial expiration to when the license expires, using the current period's start date plus the length of the upgrade.
			$license_expiration = strtotime( '+' . $exp_length . ' ' . $exp_unit, $previous_start_time );

			edd_debug_log( sprintf( 'Recurring - License Expiration after Upgrade: %s', print_r( $license_expiration, true ) ) );

			if ( ! empty( $license_expiration ) ) {

				switch ( $gateway ) {

					case 'stripe':
						// Instead of using billing_cycle_anchor to offset the start time of the next subscription, use a free trial.
						unset( $args['billing_cycle_anchor'] );
						$args['trial_end']      = $license_expiration;
						$args['needs_one_time'] = true;
						$args['license_id']     = $license_id;
						break;

					case 'paypal_commerce' :
						try {
							if ( empty( $gateway_class->paypal_product_id ) ) {
								throw new \Exception( 'Missing PayPal product ID.' );
							}

							$plan_args = \EDD_Recurring\Gateways\PayPal\_create_plan_args_for_sl_upgrade(
								new DateTime( date( 'Y-m-d H:i:s' , $license_expiration ) ),
								$gateway_class->paypal_product_id,
								$subscription
							);

							$api      = new \EDD\Gateways\PayPal\API();
							$response = $api->make_request( 'v1/billing/plans', $plan_args );

							if ( 201 !== $api->last_response_code ) {
								throw new API_Exception( sprintf(
									'Unexpected HTTP response code: %d; Response: %s',
									$api->last_response_code,
									json_encode( $response )
								) );
							}

							if ( empty( $response->id ) ) {
								throw new API_Exception( sprintf( 'Missing plan ID from PayPal response. Response: %s', json_encode( $response ) ) );
							}

							$args['plan_id'] = $response->id;
						} catch ( \Exception $e ) {
							edd_debug_log( sprintf(
								'PayPal - Exception while syncing subscription to license key. Message: %s',
								$e->getMessage()
							), true );
						}
						break;

					case 'paypalpro':
					case 'paypalexpress':
						$args['PROFILESTARTDATE'] = date( 'Y-m-d\Tg:i:s', $license_expiration );
						break;

					case 'paypal':
						$current_date = new DateTime( 'now' );
						$expiration   = new DateTime( date( 'Y-m-d' , $license_expiration ) );
						$date_diff    = $current_date->diff( $expiration );

						$args['t1'] = 'D';
						$args['p1'] = $date_diff->days;

						/*
						 * PayPal has a maximum of 90 days for trial periods.
						 * If the trial period, likely due to a Software Licensing upgrade, is greater than 90 days,
						 * we need to split it into two trial periods.
						 *
						 * See https://github.com/easydigitaldownloads/edd-recurring/issues/769
						 */
						if( $date_diff->days > 90 && 'D' === $args['t1'] ) {

							// Setup the default period times
							$first_period  = $date_diff->days;
							$second_period = 0;
							$unit          = 'D';

							if ( ( $date_diff->days - 90 ) <= 90 ) {

								// t1 = D, t2 = D
								$unit = 'D';

								$second_period = $date_diff->days - 90;
								$first_period  = 90;

							} elseif ( $date_diff->days / 7 <= 52 ) {

								// t1 = D, t2 = W
								$unit = 'W';

								$total_weeks   = $date_diff->days / 7;
								$second_period = (int) floor( $total_weeks );
								$first_period  = (int) absint( round( ( 7 * ( $total_weeks - $second_period ) ) ) );

							} elseif ( $date_diff->days / 7 > 52 ) {

								// t1 = D, tw = M
								$unit = 'M';

								$first_period    = $date_diff->d;
								$second_period   = $date_diff->m;

							}

							// Let's reduce things to be a bit more 'human readable
							switch( $unit ) {
								case 'W':

									if ( 52 === $second_period ) {
										$unit          = 'Y';
										$second_period = 1;
									} elseif ( 4 === $second_period ) {
										$unit          = 'M';
										$second_period = 1;
									}

									break;

								case 'M':
									if ( 12 === $second_period ) {
										$unit          = 'Y';
										$second_period = 1;
									}
									break;
							}


							/**
							 * If we have left over days after doing the math to determine if we're over limits,
							 * we create 2 trials, if they have no left over days, we simply set the initial trial.
							 *
							 * This covers upgrading a subscription on the same day.
							 */
							if ( ! empty( $first_period ) ) {
								$args['p1'] = $first_period;
								$args['t1'] = 'D';
								$args['a2'] = 0;
								$args['p2'] = absint( $second_period );
								$args['t2'] = $unit;
							} else {
								$args['p1'] = absint( $second_period );
								$args['t1'] = $unit;
							}
						}

						break;

					case 'authorize':
						$args['subscription']['paymentSchedule']['startDate'] = date( 'Y-m-d', $license_expiration );
						break;

				}

				break;
			}
		}

		return $args;

	}

	/**
	 * When upgrading a license, set the subscription renewal to the license expiration.
	 *
	 * @since 2.7.1
	 * @param $args
	 * @param $recurring_gateway_data
	 *
	 * @return array
	 */
	public function handle_subscription_upgrade_expiration( $args, $recurring_gateway_data ) {
		$download_id = $args['product_id'];

		foreach ( $recurring_gateway_data->purchase_data['downloads'] as $download ) {
			if ( (int) $download['id'] !== (int) $download_id ) {
				continue;
			}

			if ( ! isset( $download['options']['is_upgrade'] ) ) {
				continue;
			}

			$license_id = isset( $download['options']['license_id'] ) ? $download['options']['license_id'] : false;
			$license    = edd_software_licensing()->get_license( $license_id );
			if ( ! $license instanceof EDD_SL_License ) {
				continue;
			}

			$download = new EDD_SL_Download( $download_id );
			$price_id = isset( $args['price_id'] ) && is_numeric( $args['price_id'] ) ? $args['price_id'] : false;

			// If price is lifetime, keep going
			if ( $download->has_variable_prices() && is_numeric( $price_id ) && $download->is_price_lifetime( $price_id ) ) {
				continue;
			} else if ( $download->is_lifetime() ) {
				continue;
			}

			// Determine if the license length is changing.
			$old_length = $license->license_length();
			$new_length = edd_sl_get_product_license_length( $download_id, $price_id );

			$lengths = array(
				'old' => 'lifetime' !== $old_length ? strtotime( $old_length ) : 'lifetime',
				'new' => 'lifetime' !== $new_length ? strtotime( $new_length ) : 'lifetime',
			);

			// If these are the same, use the expiration date of the license.
			if ( $lengths['old'] === $lengths['new'] ) {
				$args['expiration'] = date( 'Y-m-d H:i:s', $license->expiration );
			} else {
				// Calculate a new expiration date using the new length.
				$exp_unit   = $download->get_expiration_unit( $price_id );
				$exp_length = $download->get_expiration_length( $price_id );

				// For now, upgrade expiration dates are relative to original payment date.
				// @link https://github.com/easydigitaldownloads/EDD-Software-Licensing/issues/1862
				$old_payment     = new EDD_Payment( $license->payment_id );
				$purchase_date   = ! empty( $old_payment->date ) ? strtotime( $old_payment->date ) : time();

				$args['expiration'] = date( 'Y-m-d H:i:s', strtotime( '+' . $exp_length . ' ' . $exp_unit, $purchase_date ) );
			}
		}

		return $args;
	}

	/**
	 * Handles the upgrade process for a license key with a subscription
	 *
	 * When upgrading a license key that has a subscription, the original subscription is cancelled
	 * and then a new subscription record is created
	 *
	 * @since  2.4
	 * @return void
	 */
	public function handle_subscription_upgrade( EDD_Recurring_Gateway $gateway_data ) {

		foreach( $gateway_data->subscriptions as $subscription ) {

			if( ! empty( $subscription['is_upgrade'] ) && ! empty( $subscription['old_subscription_id'] ) ) {

				$old_sub = new EDD_Subscription( $subscription['old_subscription_id'] );

				if( ! $old_sub->can_cancel() && 'manual' !== $old_sub->gateway ) {
					continue;
				}

				$gateway = edd_recurring()->get_gateway( $old_sub->gateway );

				if( empty( $gateway ) ) {
					continue;
				}

				$recurring = edd_recurring();

				remove_action( 'edd_subscription_cancelled', array( $recurring::$emails, 'send_subscription_cancelled' ), 10 );

				if ( $gateway->cancel_immediately( $old_sub ) ) {

					$note = sprintf( __( 'Subscription #%d cancelled for license upgrade', 'edd-recurring' ), $old_sub->id );
					edd_insert_payment_note( $old_sub->parent_payment_id, $note );
					$old_sub->add_note( __( 'Subscription cancelled for license upgrade', 'edd-recurring' ) );

					$old_sub->cancel();
				}
			}

		}

	}

	/**
	 * Handles the upgrade process for a license key with a subscription
	 *
	 * When upgrading a license key that has a subscription and upgrading to a product without a subscription,
	 * the original subscription is cancelled
	 *
	 * @since  2.4
	 * @return void
	 */
	public function handle_non_subscription_upgrade( $download_id = 0, $payment_id = 0, $type = 'default', $cart_item = array(), $cart_index = 0 ) {

		// Bail if this is not an upgrade item
		if( empty( $cart_item['item_number']['options']['is_upgrade'] ) ) {
			return;
		}

		// Bail if this was a subscription purchase
		if( edd_get_payment_meta( $payment_id, '_edd_subscription_payment', true ) ) {
			return;
		}

		$license_id   = $cart_item['item_number']['options']['license_id'];
		$subscription = $this->get_subscription_of_license( $license_id );

		if( empty( $subscription->id ) ) {
			return;
		}

		$sub = new EDD_Subscription( $subscription->id );

		if( ! $sub->can_cancel() && 'manual' !== $sub->gateway ) {
			return;
		}

		$gateway = edd_recurring()->get_gateway( $sub->gateway );

		if( empty( $gateway ) ) {
			return;
		}

		$recurring = edd_recurring();

		remove_action( 'edd_subscription_cancelled', array( $recurring::$emails, 'send_subscription_cancelled' ), 10 );

		if ( $gateway->cancel_immediately( $sub ) ) {

			$note = sprintf( __( 'Subscription #%d cancelled for license upgrade', 'edd-recurring' ), $sub->id );
			edd_insert_payment_note( $sub->parent_payment_id, $note );

			$sub->cancel();
		}

	}

	/**
	 * Handles the processing of cancelling existing subscription when manually renewing a license key
	 *
	 * When renewing a license key that has a subscription, the original subscription is cancelled
	 * and then a new subscription record is created
	 *
	 * @since  2.6.3
	 * @return void
	 */
	public function handle_manual_license_renewal( EDD_Recurring_Gateway $gateway_data ) {

		foreach( $gateway_data->subscriptions as $subscription ) {

			if( ! empty( $subscription['is_renewal'] ) && ! empty( $subscription['old_subscription_id'] ) ) {

				$sub = new EDD_Subscription( $subscription['old_subscription_id'] );

				if( ! $sub->can_cancel() && 'manual' !== $sub->gateway ) {
					continue;
				}

				$gateway = edd_recurring()->get_gateway( $sub->gateway );

				if( empty( $gateway ) ) {
					continue;
				}

				$recurring = edd_recurring();

				remove_action( 'edd_subscription_cancelled', array( $recurring::$emails, 'send_subscription_cancelled' ), 10 );

				if ( $gateway->cancel_immediately( $sub ) ) {

					$note = sprintf( __( 'Subscription #%d cancelled for manual license renewal', 'edd-recurring' ), $sub->id );
					edd_insert_payment_note( $sub->parent_payment_id, $note );

					$sub->cancel();
				}
			}

		}

	}

	/**
	 * Renew the license key for a subscription when a renewal payment is processed
	 *
	 * @since  2.4
	 * @return void
	 */
	public function renew_license_keys( $sub_id, $expiration, $subscription, $payment_id ) {

		// Update the expiration date of the associated license key, if EDD Software Licensing is active

		$license = apply_filters( 'edd_recurring_sl_renewing_license',
			edd_software_licensing()->get_license_by_purchase( $subscription->parent_payment_id, $subscription->product_id ),
			$subscription
		);

		if ( $license ) {

			// Update the expiration dates of the license key
			$payment_id = ! empty( $payment_id ) ? (int) $payment_id : $subscription->parent_payment_id;
			edd_software_licensing()->renew_license( $license->ID, $payment_id, $subscription->product_id );

			$log_id = wp_insert_post(
				array(
					'post_title'   => sprintf( __( 'LOG - License %d Renewed via Subscription', 'edd_sl' ), $license->ID ),
					'post_name'    => 'log-license-renewed-' . $license->ID . '-' . md5( time() ),
					'post_type'    => 'edd_license_log',
					'post_content' => $subscription->id,
					'post_status'  => 'publish'
				 )
			);

			add_post_meta( $log_id, '_edd_sl_log_license_id', $license->ID );

		}
	}

	/**
	 * Sets the "Was Renewal" flag on renewal payments that have a license key
	 *
	 * @since  2.5
	 * @return void
	 */
	public function set_renewal_flag( $payment, $subscription ) {

		$license = edd_software_licensing()->get_license_by_purchase( $subscription->parent_payment_id, $subscription->product_id );

		if ( $license ) {

			$payment->update_meta( '_edd_sl_is_renewal', 1 );
			$payment->add_meta( '_edd_sl_renewal_key', $license->key );

		}
	}

	/**
	 * Display a link to the subscription details page in Downloads > Licenses
	 *
	 * @since  2.4
	 * @return void
	 */
	public function licenses_table( $license ) {
		$edd_license = edd_software_licensing()->get_license( $license['ID'] );

		$subs = $this->db->get_subscriptions( array( 'product_id' => $edd_license->download_id, 'parent_payment_id' => $edd_license->payment_id ) );
		if( $subs ) {
			foreach( $subs as $sub ) {

				if( 'cancelled' == $sub->status ) {
					continue;
				}

				echo '<br/>';
				echo '<a href="' . esc_url( admin_url( 'edit.php?post_type=download&page=edd-subscriptions&id=' ) . $sub->id ) . '">' . __( 'View Subscription', 'edd-recurring' ) . '</a>';
			}
		}

	}

	/**
	 * Display the associated license key on the subscription details screen
	 *
	 * @since  2.4
	 * @return void
	 */
	public function subscription_details( EDD_Subscription $subscription ) {

		$license = edd_software_licensing()->get_license_by_purchase( $subscription->parent_payment_id, $subscription->product_id );
		if( $license ) : ?>
			<h3><?php _e( 'License Key:', 'edd-recurring' ); ?></h3>
			<table class="wp-list-table widefat striped payments">
				<thead>
				<tr>
					<th><?php _e( 'License', 'edd-recurring' ); ?></th>
					<th><?php _e( 'Status', 'edd-recurring' ); ?></th>
					<th><?php _e( 'Actions', 'edd-recurring' ); ?></th>
				</tr>
				</thead>
				<tbody>
					<?php $license_key = edd_software_licensing()->get_license_key( $license->ID ); ?>
					<tr>
						<td><?php echo $license_key; ?></td>
						<td><?php echo edd_software_licensing()->get_license_status( $license->ID ); ?></td>
						<td>
							<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=download&page=edd-licenses&s=' ) . $license_key ); ?>"><?php _e( 'View License', 'edd-recurring' ); ?></a>
						</td>
					</tr>
				</tbody>
			</table>
		<?php endif;

	}

	/**
	 * Display renewal date in [license_keys] for any key that is renewing automatically
	 *
	 * @since  2.4
	 * @return void
	 */
	public function license_key_details( $license_id = 0 ) {

		$sub = $this->get_subscription_of_license( $license_id );

		if( $sub ) {

			echo '<div class="edd-recurring-license-renewal">';
				printf( __( 'Renews automatically on %s', 'edd-recurring' ), date_i18n( get_option( 'date_format' ), strtotime( $sub->expiration ) ) );
			echo '</div>';

		}

	}

	/**
	 * Display the new subscription details on checkout when upgrading a license
	 *
	 * @since  2.4
	 * @return void
	 */
	public function checkout_upgrade_details() {

		$items = edd_get_cart_contents();

		if( ! is_array( $items ) ) {
			return;
		}

		foreach( $items as $item ) {

			if( empty( $item['options']['is_upgrade'] ) ) {

				continue;

			}

			$sub = $this->get_subscription_of_license( $item['options']['license_id'] );

			if( ! $sub ) {

				continue;

			}

			if( empty( $item['options']['recurring'] ) ) {

				continue;

			}

			if( edd_has_variable_prices( $item['id'] ) ) {
				$price = edd_get_price_option_amount( $item['id'], $item['options']['price_id'] );
			} else {
				$price = edd_get_download_price( $item['id'] );
			}

			$enabled = get_post_meta( $item['id'], '_edd_sl_enabled', true );

			// Only set up a discount if software licensing is enabled for the product.
			if ( $enabled ) {
				$discount = edd_sl_get_renewal_discount_percentage( 0, $item['id'] );
			} else {
				$discount = 0;
			}

			$cost     = (float) edd_sanitize_amount( $price - ( $price * ( $discount / 100 ) ) );
			$period   = EDD_Recurring()->get_pretty_subscription_frequency( $item['options']['recurring']['period'] );

			// If the store is set to add tax on-top-of the price, add the taxes to the price.
			if ( edd_use_taxes() && ! edd_prices_include_tax() ) {
				$tax = $cost * ( edd_get_tax_rate() );
				$cost = $cost + $tax;
			}

			$cost = edd_currency_filter( edd_sanitize_amount( $cost ) );

			$message  = sprintf(
				__( '%s will now automatically renew %s for %s', 'edd-recurring' ),
				get_the_title( $item['id'] ),
				$period,
				$cost
			);

			echo '<div id="edd-recurring-sl-auto-renew" class="edd-alert edd-alert-warn"><p class="edd_error">' . $message . '</p></div>';

		}

	}

	/**
	 * Display the new subscription details on checkout when manually renewing a license
	 *
	 * @since  2.6.3
	 * @return void
	 */
	public function checkout_license_renewal_details() {

		if( ! edd_sl_renewals_allowed() ) {
			return;
		}

		if( ! EDD()->session->get( 'edd_is_renewal' ) ) {
			return;
		}

		$cart_items = edd_get_cart_contents();
		$renewals   = edd_sl_get_renewal_keys();

		foreach ( $cart_items as $key => $item ) {

			if( ! isset( $renewals[ $key ] ) || empty( $item['options']['is_renewal'] ) ) {
				continue;
			}

			if( empty( $item['options']['recurring'] ) ) {
				continue;
			}

			$license_key = $renewals[ $key ];
			$license_id  = edd_software_licensing()->get_license_by_key( $license_key );
			$sub         = $this->get_subscription_of_license( $license_id, array(
				'status' => array( 'active', 'trialling', 'failing' )
			) );

			if( ! $sub || ( $sub->is_active() && 'cancelled' === $sub->status ) ) {
				continue;
			}

			if( edd_has_variable_prices( $item['id'] ) ) {
				$price = edd_get_price_option_amount( $item['id'], $item['options']['price_id'] );
			} else {
				$price = edd_get_download_price( $item['id'] );
			}

			$enabled = get_post_meta( $item['id'], '_edd_sl_enabled', true );

			// Only set up a discount if software licensing is enabled for the product.
			if ( $enabled ) {
				$discount = edd_sl_get_renewal_discount_percentage();
			} else {
				$discount = 0;
			}

			$cost     = (float) edd_sanitize_amount( $price - ( $price * ( $discount / 100 ) ) );
			$period   = EDD_Recurring()->get_pretty_subscription_frequency( $item['options']['recurring']['period'] );

			// If the store is set to add tax on-top-of the price, add the taxes to the price.
			if ( edd_use_taxes() && ! edd_prices_include_tax() ) {
				$tax = $cost * ( edd_get_tax_rate() );
				$cost = $cost + $tax;
			}

			$cost = edd_currency_filter( edd_sanitize_amount( $cost ) );

			$message  = sprintf(
				__( 'Your existing subscription to %s will be cancelled and replaced with a new subscription that automatically renews %s for %s.', 'edd-recurring' ),
				get_the_title( $item['id'] ),
				$period,
				$cost
			);

			echo '<div id="edd-recurring-sl-cancel-replace" class="edd-alert edd-alert-warn"><p class="edd_error">' . $message . '</p></div>';

		}

	}

	/**
	 * Retrieves the subscription associated with a license key
	 *
	 * If a license key has multiple subscriptions (such as can happen with license upgrades),
	 * the most recently subscription is returned
	 *
	 * @param int   $license_id ID of the license key.
	 * @param array $sub_args   Subscription query arguments to override the defaults.
	 *
	 * @since  2.4
	 * @return EDD_Subscription|boolean
	 */
	private function get_subscription_of_license( $license_id = 0, $sub_args = array() ) {

		$license     = edd_software_licensing()->get_license( $license_id );
		$payment_ids = $license->payment_ids;

		if( ! is_array( $payment_ids ) ) {
			return false;
		}

		// Sort the payment IDs so we're starting with the oldest, and working towards the newest.
		sort( $payment_ids );

		$sub_args = wp_parse_args( $sub_args, array(
			'product_id' => $license->download_id,
			'status'     => array( 'active', 'trialling' ),
			'number'     => 1,
			'order'      => 'DESC'
		) );

		if( $license->download_id )  {

			/**
			 * Loop through payment IDs until we find one with a subscription for this download ID.
			 *
			 * This accounts for stores who enable Recurring Payments after they already sold licenses, as the initial
			 * payment ID for the license will not have a subscription, but it's possible a manual renewal would initiate
			 * the subscription creation.
			 */
			foreach ( $payment_ids as $payment_id ) {
				$sub_args['parent_payment_id'] = $payment_id;

				$subs = $this->db->get_subscriptions( $sub_args );

				if( $subs ) {

					// If we found a subscription, return it.
					return array_pop( $subs );

				}

			}

		}

		// If no subscriptions are found for the combination of payment IDs and download ID, return false.
		return false;

	}

	/**
	 * Determines if the cart contains an upgrade
	 *
	 * @since  2.5
	 * @return bool
	 */
	public function cart_has_upgrade() {

		$has_upgrade = false;
		$items       = edd_get_cart_contents();

		if(  is_array( $items ) ) {

			foreach( $items as $item ) {

				if( ! empty( $item['options']['is_upgrade'] ) ) {

					$has_upgrade = true;
					break;
				}

			}

		}

		return $has_upgrade;

	}

	/**
	 * Displays a notice about license key lengths being synced with free trials
	 *
	 * @since  2.6
	 * @return void
	 */
	public function free_trial_settings_notice( $download_id = 0 ) {

		$display = edd_recurring()->has_free_trial( $download_id ) ? '' : ' style="display:none;"';

		echo '<p id="edd-sl-free-trial-length-notice"' . $display . '>' . __( 'Note: license keys will remain valid for the duration of the free trial period. Once the free trial is over, the settings defined here determine the license lengths.', 'edd-recurring' ) . '</p>';

	}

	/**
	 * Updates the expiration date on a license key when the renewal date of a subscription is checked
	 * and synced with a merchant processor
	 *
	 * See https://github.com/easydigitaldownloads/edd-recurring/issues/614
	 *
	 * @since  2.6.6
	 * @return void
	 */
	public function maybe_sync_license_expiration_on_check_expiration( EDD_Subscription $subscription, $expiration ) {

		$license = edd_software_licensing()->get_license_by_purchase( $subscription->parent_payment_id, $subscription->product_id );

		if ( is_a( $license, 'EDD_SL_License' ) ) {

			// If the license expires today, we need to update it with the new date found for the subscription
			if( strtotime( date( 'Y-n-d', $license->expiration ) ) <= strtotime( $expiration ) ) {

				$expiration_date = date( 'Y-n-d 23:59:59', strtotime( $expiration ) );

				// Convert back into timestamp.
				$expiration_date = strtotime( $expiration_date, current_time( 'timestamp' ) );

				$license->expiration = $expiration_date;


			}

		}

	}

	/**
	 * Rolls a license expiration date back when refunding a renewal payment
	 *
	 * See https://github.com/easydigitaldownloads/edd-recurring/issues/559
	 *
	 * @since  2.7
	 * @param \EDD_Payment|int $payment      The original order ID in EDD 3.0; an EDD_Payment object in 2.x.
	 * @param int              $refund_id    The refund order ID (EDD 3.0).
	 * @param bool             $all_refunded Whether the entire order was refunded (EDD 3.0).
	 * @return void
	 */
	public function rollback_expiration_on_renewal_refund( $payment, $refund_id = null, $all_refunded = true ) {

		if ( function_exists( 'edd_get_order_meta' ) ) {
			$is_renewal      = edd_get_order_meta( $payment, '_edd_sl_is_renewal', true );
			$subscription_id = edd_get_order_meta( $payment, 'subscription_id', true );

			// Do not do anything if the original order is not a renewal or if the entire order was not refunded.
			if ( ! $is_renewal || ! $subscription_id || ! $all_refunded ) {
				return;
			}
		}
		if ( $payment instanceof EDD_Payment ) {
			$is_renewal      = edd_get_payment_meta( $payment->ID, '_edd_sl_is_renewal', true );
			$subscription_id = edd_get_payment_meta( $payment->ID, 'subscription_id', true );
			if ( ! $is_renewal || ! $subscription_id ) {
				return;
			}
		}
		$id = empty( $payment->ID ) ? $payment : $payment->ID;
		if ( ! is_numeric( $id ) ) {
			return;
		}

		$licenses = edd_software_licensing()->get_licenses_of_purchase( $id );
		if ( ! $licenses ) {
			return;
		}

		foreach ( $licenses as $license ) {

			if ( ! is_a( $license, 'EDD_SL_License' ) ) {
				continue;
			}

			$license->expiration = strtotime( '-' . $license->license_length(), $license->expiration );
		}
	}

	/**
	 * If this is a renewal and the old subscription is `failing`, cancel it.
	 *
	 * @link https://github.com/easydigitaldownloads/edd-recurring/issues/1288
	 *
	 * @param EDD_Subscription|false $subscription Newly created subscription object.
	 * @param array                  $sub_args     Gateway subscription arguments.
	 * @param EDD_Recurring_Gateway  $gateway      Gateway object.
	 *
	 * @since 2.10.2
	 * @return void
	 */
	public function cancel_failed_subscription_during_renewal( $subscription, $sub_args, $gateway ) {
		// Bail if this isn't a renewal.
		if ( empty( $sub_args['is_renewal'] ) || empty( $sub_args['old_subscription_id'] ) ) {
			return;
		}

		$old_sub = new EDD_Subscription( $sub_args['old_subscription_id'] );
		if ( 'failing' === $old_sub->status && $old_sub->can_cancel() ) {
			$recurring = edd_recurring();
			remove_action( 'edd_subscription_cancelled', array( $recurring::$emails, 'send_subscription_cancelled' ), 10 );

			$old_sub->cancel();

			if ( $gateway->cancel_immediately( $old_sub ) ) {
				$old_sub->add_note( sprintf( __( 'Subscription cancelled due to new subscription #%d created while renewing.', 'edd_sl' ), $subscription->id ) );
			}
		}
	}

}
