<?php
/**
 * Upgrade Functions
 *
 * @package     EDD
 * @subpackage  Admin/Upgrades
 * @copyright   Copyright (c) 2015, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.4
 */


/**
 * Perform automatic database upgrades when necessary
 *
 * @since 2.7
 * @return void
*/
function edd_recurring_do_automatic_upgrades() {

	$did_upgrade = false;
	$version = get_option( 'edd_recurring_version' );

	if ( $version <> EDD_RECURRING_VERSION ) {

		// Trigger DB upgrades
		edd_recurring_install();

		// Let us know that an upgrade has happened
		$did_upgrade = true;

	}

	if ( $did_upgrade ) {

		update_option( 'edd_recurring_version', EDD_RECURRING_VERSION );

	}

}
add_action( 'admin_init', 'edd_recurring_do_automatic_upgrades' );


/**
 * Recurring Payments Upgrade Notices
 *
 * @since 2.4
 *
 */
function edd_show_recurring_upgrade_notices() {

	global $wpdb;

	// Don't show notices on the upgrades page
	if ( isset( $_GET['page'] ) && $_GET['page'] == 'edd-upgrades' ) {
		return;
	}

	$edd_recurring_version = get_option( 'edd_recurring_version' );

	if ( ! edd_has_upgrade_completed( 'upgrade_24_subscriptions' ) ) {

		$has_recurring = $wpdb->get_var( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'edd_period' OR ( meta_key = 'edd_variable_prices' AND meta_value LIKE '%recurring%' AND meta_value LIKE '%yes%' ) AND 1=1 LIMIT 1" );
		$needs_upgrade = ! empty( $has_recurring );

		if( ! $needs_upgrade ) {
			return;
		}

		printf(
			'<div class="updated"><p>' . __( 'Easy Digital Downloads needs to upgrade the subscriptions database, click <a href="%s">here</a> to start the upgrade.', 'edd-recurring' ) . '</p></div>',
			esc_url( admin_url( 'index.php?page=edd-upgrades&edd-upgrade=upgrade_24_subscriptions' ) )
		);
	}

	if ( ! edd_has_upgrade_completed( 'fix_24_stripe_customers' ) ) {

		// 2.4.6 - Check for duplicate stripe customers after #355
		if ( false === edd_recurring_needs_24_stripe_fix() ) {
			edd_set_upgrade_complete( 'fix_24_stripe_customers' );
			return;
		}

		printf(
			'<div class="updated"><p>' . __( 'Easy Digital Downloads needs to upgrade the subscription customer database, click <a href="%s">here</a> to start the upgrade.', 'edd-recurring' ) . '</p></div>',
			esc_url( admin_url( 'index.php?page=edd-upgrades&edd-upgrade=fix_24_stripe_customers' ) )
		);

	}

	if ( ! edd_has_upgrade_completed( 'recurring_27_subscription_meta' ) ) {

		printf(
			'<div class="updated"><p>' . __( 'Easy Digital Downloads needs to upgrade the subscription payments database, click <a href="%s">here</a> to start the upgrade.', 'edd-recurring' ) . '</p></div>',
			esc_url( admin_url( 'index.php?page=edd-upgrades&edd-upgrade=recurring_27_subscription_meta' ) )
		);

	}

	if ( ! edd_has_upgrade_completed( 'recurring_paypalproexpress_logs' ) ) {

		printf(
			'<div class="updated"><p>' . __( 'Easy Digital Downloads needs to upgrade the payment gateway error logs, click <a href="%s">here</a> to start the upgrade.', 'edd-recurring' ) . '</p></div>',
			esc_url( admin_url( 'index.php?page=edd-upgrades&edd-upgrade=recurring_paypalproexpress_logs' ) )
		);

	}

	if ( ! edd_has_upgrade_completed( 'recurring_add_tax_columns_to_subs_table' ) ) {

		printf(
			'<div class="updated"><p>' . __( 'Easy Digital Downloads needs to upgrade subscriptions table, click <a href="%s">here</a> to start the upgrade.', 'edd-recurring' ) . '</p></div>',
			esc_url( admin_url( 'index.php?page=edd-upgrades&edd-upgrade=recurring_add_tax_columns_to_subs_table' ) )
		);

	}

	if ( ! edd_has_upgrade_completed( 'recurring_cancel_subs_if_times_met' ) ) {

		printf(
			'<div class="updated"><p>' . __( 'Easy Digital Downloads wants to check to see if any subscriptions need to be set to complete. Click <a href="%s">here</a> to start.', 'edd-recurring' ) . '</p></div>',
			esc_url( admin_url( 'index.php?page=edd-upgrades&edd-upgrade=recurring_cancel_subs_if_times_met' ) )
		);

	}

	if ( ! edd_has_upgrade_completed( 'recurring_add_price_id_column' ) ) {

		printf(
			'<div class="updated"><p>' . __( 'Easy Digital Downloads needs to upgrade subscriptions table, click <a href="%s">here</a> to start the upgrade.', 'edd-recurring' ) . '</p></div>',
			esc_url( admin_url( 'index.php?page=edd-upgrades&edd-upgrade=recurring_add_price_id_column' ) )
		);

	}

	if ( ! edd_has_upgrade_completed( 'recurring_update_price_id_column' ) ) {

		printf(
			'<div class="updated"><p>' . __( 'Easy Digital Downloads needs to update the subscriptions table, click <a href="%s">here</a> to start the upgrade.', 'edd-recurring' ) . '</p></div>',
			esc_url( admin_url( 'index.php?page=edd-upgrades&edd-upgrade=recurring_update_price_id_column' ) )
		);

	}

}
add_action( 'admin_notices', 'edd_show_recurring_upgrade_notices' );

/**
 * Migrates pre 2.4 subscriptions to new database
 *
 * @since  2.4
 * @return void
 */
function edd_recurring_v24_migrate_subscriptions() {
	global $wpdb;

	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		wp_die( __( 'You do not have permission to do shop upgrades', 'edd-recurring' ), __( 'Error', 'edd-recurring' ), array( 'response' => 403 ) );
	}

	ignore_user_abort( true );
	if ( ! edd_is_func_disabled( 'set_time_limit' ) ) {
		@set_time_limit( 0 );
	}

	$step   = isset( $_GET['step'] ) ? absint( $_GET['step'] ) : 1;
	$number = isset( $_GET['number'] ) ? absint( $_GET['number'] ) : 5;
	$offset = $step == 1 ? 0 : ( $step - 1 ) * $number;

	if ( $step < 2 ) {

		$db = new EDD_Subscriptions_DB;
		@$db->create_table();

		// Check if we have any payments before moving on
		$sql          = "SELECT ID FROM $wpdb->posts WHERE post_type = 'edd_payment' LIMIT 1";
		$has_payments = $wpdb->get_col( $sql );

		if ( empty( $has_payments ) ) {
			// We had no payments, just complete
			update_option( 'edd_recurring_version', preg_replace( '/[^0-9.].*/', '', EDD_RECURRING_VERSION ) );
			edd_set_upgrade_complete( 'upgrade_24_subscriptions' );
			delete_option( 'edd_doing_upgrade' );
			wp_redirect( admin_url() );
			exit;
		}
	}

	$total = isset( $_GET['total'] ) ? absint( $_GET['total'] ) : false;
	if ( empty( $total ) || $total <= 1 ) {
		$total_sql = "SELECT COUNT(ID) as total_payments FROM $wpdb->posts WHERE post_type = 'edd_payment' AND post_status IN ('publish','revoked','cancelled');";
		$results   = $wpdb->get_row( $total_sql, 0 );
		$total     = $results->total_payments;
	}

	$payment_ids = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT ID FROM $wpdb->posts WHERE post_type = 'edd_payment' AND post_status IN ('publish','revoked','cancelled') ORDER BY post_date ASC LIMIT %d,%d;",
			$offset,
			$number
		)
	);

	if ( $payment_ids ) {

		foreach ( $payment_ids as $payment_id ) {

			$cancelled    = false;
			$expiration   = '';
			$recurring_id = '';
			$payment      = edd_get_payment( $payment_id );
			$profile      = edd_recurring_get_legacy_profile_id( $payment );
			$cart_details = $payment->cart_details;
			$download     = reset( $cart_details );
			$download_id  = $download['id'];

			if( is_object( $profile ) ) {

				// Stripe gives us a subscription object containing the sub ID, customer ID, and cancelled status

				$profile_id   = $profile->id;
				$recurring_id = $profile->customer;
				$cancelled    = ! empty( $profile->canceled_at );
				$expiration   = $profile->current_period_end;

			} else {

				$profile_id   = $profile;

			}

			if( empty( $profile_id ) ) {
				// No subscription ID discovered, skip this payment
				continue;
			}

			if ( edd_has_variable_prices( $download_id ) ) {

				$price_id  = edd_get_cart_item_price_id( $download );
				$recurring = edd_recurring()->is_price_recurring( $download_id, $price_id );
				$times     = edd_recurring()->get_times( $price_id, $download_id );
				$period    = edd_recurring()->get_period( $price_id, $download_id );
				$fee       = edd_recurring()->get_signup_fee( $price_id, $download_id );

			} else {

				$recurring = edd_recurring()->is_recurring( $download_id );
				$times     = edd_recurring()->get_times_single( $download_id );
				$period    = edd_recurring()->get_period_single( $download_id );
				$fee       = edd_recurring()->get_signup_fee_single( $download_id );

			}

			if( ! $recurring ) {
				continue;
			}

			if( empty( $payment->user_id ) ) {

				$user = get_user_by( 'email', $payment->email );

				if( $user ) {

					$payment->user_id = $user->ID;

				} else {

					remove_all_actions( 'user_register' );

					$payment->user_id = wp_insert_user( array(
						'user_email' => $payment->email,
						'user_login' => $payment->email,
						'first_name' => $payment->first_name,
						'last_name'  => $payment->last_name,
					) );

				}

				$payment->save();

			}

			$subscriber = new EDD_Recurring_Subscriber( $payment->user_id, true );

			if( ! empty( $recurring_id ) ) {
				$subscriber->set_recurring_customer_id( $recurring_id, 'stripe' );
			}

			if( empty( $expiration ) ) {

				$expiration  = get_user_meta( $payment->user_id, '_edd_recurring_exp', true );

				if( empty( $expiration ) ) {

					// If no user account existed, we don't know the expiration date

					$child_payments = edd_get_payments( array(
						'status'      => 'edd_subscription',
						'number'      => 1,
						'post_parent' => $payment_id,
						'order'       => 'DESC',
						'orderby'     => 'post_date',
						'output'      => 'payments'
					) );

					if( $child_payments ) {

						// Use date of latest renewal payment as base
						$child_payment = reset( $child_payments );
						$base = strtotime( $child_payment->date );

					} else {

						// Use signup date as base if there are no renewals
						$base = strtotime( $payment->date );

					}

					$expiration = strtotime( '+ 1 ' . $period . ' 23:59:59', $base );

				}

			}

			switch( $payment->status ) {

				case 'publish' :

					$status = 'active';
					break;

				case 'cancelled' :
				case 'revoked' :

					$status = 'cancelled';
					break;
			}

			if( ! empty( $cancelled ) ) {
				$status = 'cancelled';
			}

			$args = array(
				'product_id'        => $download_id,
				'period'            => $period,
				'initial_amount'    => $payment->total,
				'recurring_amount'  => $payment->total + $fee,
				'bill_times'        => $times,
				'parent_payment_id' => $payment_id,
				'created'           => $payment->date,
				'gateway'           => $payment->gateway,
				'expiration'        => date( 'Y-m-d H:i:s', $expiration ),
				'profile_id'        => $profile_id,
				'status'            => $status,
			);

			$subscriber->add_subscription( $args );

			edd_update_payment_meta( $payment_id, '_edd_subscription_payment', true );

		}

		$step ++;
		$redirect = add_query_arg( array(
			'page'        => 'edd-upgrades',
			'edd-upgrade' => 'upgrade_24_subscriptions',
			'step'        => $step,
			'number'      => $number,
			'total'       => $total
		), admin_url( 'index.php' ) );

		wp_redirect( $redirect );
		exit;

	} else {

		update_option( 'edd_recurring_version', preg_replace( '/[^0-9.].*/', '', EDD_RECURRING_VERSION ) );
		edd_set_upgrade_complete( 'upgrade_24_subscriptions' );
		delete_option( 'edd_doing_upgrade' );

		wp_redirect( admin_url() );
		exit;

	}

}
add_action( 'edd_upgrade_24_subscriptions', 'edd_recurring_v24_migrate_subscriptions' );

/**
 * Retrieve the recurring profile ID for a legacy subscription
 *
 * @since  2.4
 * @return string
 */
function edd_recurring_get_legacy_profile_id( EDD_Payment $payment ) {

	$profile_id = '';

	switch( $payment->gateway ) {

		case 'paypal' :

			foreach ( edd_get_payment_notes( $payment->ID ) as $note ) {
				if ( preg_match( '/^PayPal Subscription ID: ([^\s]+)/', $note->comment_content, $match ) ) {
					$profile_id = $match[1];
					continue;
				}
			}

			break;

		case 'stripe' :

			$charge_id = $payment->transaction_id;

			if( empty( $charge_id ) ) {

				if ( edd_is_test_mode() ) {
					$prefix = 'test_';
				} else {
					$prefix = 'live_';
				}

				$secret_key = edd_get_option( $prefix . 'secret_key', '' );

				\Stripe\Stripe::setApiKey( $secret_key );

				foreach ( edd_get_payment_notes( $payment->ID ) as $note ) {
					if ( preg_match( '/^Stripe Charge ID: ([^\s]+)/', $note->comment_content, $match ) ) {
						$charge_id = $match[1];

						// We need to store the transaction ID if it's not present
						$payment->transaction_id = $charge_id;
						$payment->save();

						continue;
					}
					if ( preg_match( '/^Stripe Customer ID: ([^\s]+)/', $note->comment_content, $match ) ) {
						$customer_id = $match[1];
						continue;
					}
				}

				if( empty( $charge_id ) ) {

					// No charge, let's look for a customer ID
					if( ! empty( $customer_id ) ) {

						try {

							$customer      = \Stripe\Customer::retrieve( $customer_id );
							$subscriptions = $customer->subscriptions->data;
							$subscription  = reset( $subscriptions );

							if( ! empty( $subscription ) ) {
								return $subscription;
							}

							return '';

						} catch ( Exception $e ) {

							return '';

						}

					}

				}

			}

			try {

				$charge     = \Stripe\Charge::retrieve( $charge_id );
				$invoice    = \Stripe\Invoice::retrieve( $charge->invoice );
				$customer   = \Stripe\Customer::retrieve( $invoice->customer );
				$profile_id = ! empty( $invoice->subscription ) ? $invoice->subscription : '';

				if( is_object( $customer ) && true !== $customer->deleted && ! empty( $profile_id ) ) {

					return $customer->subscriptions->retrieve( $profile_id );

				}

			} catch ( Exception $e ) {

				return '';

			}

			break;


	}

	return $profile_id;
}

/**
 * Fixes incorrect stripe customer association from the EDD Recurriong 2.4 upgrade routine.
 *
 * It was discovered that the upgrade routine for 2.4 resulted in a few subscribers getting assigned
 * the same customer ID from Stripe. This resulted in occasional new signups through Stripe to have
 * the charges assigned to the incorrect Stripe customer record. It also caused the credit / debit
 * card entered on checkout during purchase to get added to the wrong customer record in Stripe.
 *
 * See https://github.com/easydigitaldownloads/edd-recurring/issues/355
 *
 * @since  2.4.6
 * @return void
 */
function edd_recurring_fix_24_stripe_customers() {

	global $wpdb;

	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		wp_die( __( 'You do not have permission to do shop upgrades', 'edd-recurring' ), __( 'Error', 'edd-recurring' ), array( 'response' => 403 ) );
	}

	ignore_user_abort( true );
	if ( ! edd_is_func_disabled( 'set_time_limit' ) ) {
		@set_time_limit( 0 );
	}

	$step   = isset( $_GET['step'] ) ? absint( $_GET['step'] ) : 1;
	$number = isset( $_GET['number'] ) ? absint( $_GET['number'] ) : 5;
	$offset = $step == 1 ? 0 : ( $step - 1 ) * $number;
	$total  = isset( $_GET['total'] ) ? absint( $_GET['total'] ) : false;

	$log_only   = isset( $_GET['custom'] ) && 1 == $_GET['custom'] ? true : false;
	$upload_dir = wp_upload_dir();
	$filename   = trailingslashit( $upload_dir['basedir'] ) . 'edd-recurring-246.txt';

	if ( true === $log_only && 1 === $step ) {
		// Blank out the log file if needed
		@file_put_contents( $filename, '' );
		@chmod( $filename, 0664 );
	}

	if ( true === $log_only ) {
		$log_file = fopen( $filename, 'a' );
	}

	if ( empty( $total ) || $total <= 1 ) {
		$total_sql = "SELECT COUNT(user_id) as total_stripe_customers FROM $wpdb->usermeta WHERE meta_key = '_edd_recurring_id' AND meta_value LIKE '%stripe%'";
		$results   = $wpdb->get_row( $total_sql, 0 );
		$total     = $results->total_stripe_customers;

		if ( $log_only ) {
			fwrite( $log_file, 'Found ' . $total . ' Stripe Customers' . "\n");
		}
	}

	// Storage for customer IDs that have been processed.
	$processed = array();

	$sql = $wpdb->prepare(
		"SELECT user_id FROM $wpdb->usermeta WHERE meta_key = '_edd_recurring_id' AND meta_value LIKE '%s' ORDER BY umeta_id ASC LIMIT %d,%d",
		'%' . $wpdb->esc_like( 'stripe' ) . '%',
		$offset,
		$number
	);

	$user_ids = $wpdb->get_col( $sql, 0);

	if ( $user_ids ) {

		foreach ( $user_ids as $user_id ) {
			if ( true === $log_only ) {
				fwrite( $log_file, 'Processing User ID: ' . $user_id . "\n");
			}

			if( in_array( $user_id, $processed ) ) {
				if ( true === $log_only ) {
					fwrite( $log_file, 'User ID ' . $user_id . ' was previously processed' . "\n");
				}
				continue;
			}

			$subscriber = '';
			$stripe_customer_id = '';
			$users_profiles     = get_user_meta( $user_id, '_edd_recurring_id', true );

			// Make sure this recurring ID is for Stripe
			if ( ! is_array( $users_profiles ) || ! isset( $users_profiles['stripe'] ) ) {
				if ( true === $log_only ) {
					fwrite( $log_file, $user_id . ' had no Stripe profiles' . "\n");
				}
				continue;
			}

			$stripe_customer_id = $users_profiles['stripe'];

			// See if there are any other users with this stripe customer ID
			$found_duplicates = $wpdb->get_results( "SELECT user_id FROM $wpdb->usermeta WHERE meta_key = '_edd_recurring_id' AND meta_value LIKE '%$stripe_customer_id%' ORDER BY umeta_id ASC;");

			// There are no users found for this customer ID or only one user found, so no duplicates...just skip them over
			if ( empty( $found_duplicates ) || count( $found_duplicates ) < 2 ) {
				if ( true === $log_only ) {
					fwrite( $log_file, 'User ID ' . $user_id . ' had no duplicate usermeta entires' . "\n");
				}
				continue;
			}

			if ( true === $log_only ) {
				fwrite( $log_file, 'User ID ' . $user_id . ' has ' . count( $found_duplicates ) . ' duplicates' . "\n");
			}

			if ( empty( $stripe_customer_id ) ) {
				continue;
			}

			if ( edd_is_test_mode() ) {
				$prefix = 'test_';
			} else {
				$prefix = 'live_';
			}

			$secret_key = edd_get_option( $prefix . 'secret_key', '' );

			\Stripe\Stripe::setApiKey( $secret_key );

			/*
			 * Find the original customer record, which is correct. For all other user IDs, we need to create a new customer.
			 * After we have created a new customer in Stripe for the user, we will then correct the canonical.
			 *
			 * We identify the canonical as being the oldest row in wp_usermeta
			 */
			$canonical = reset( $found_duplicates );

			if ( true === $log_only ) {
				fwrite( $log_file, 'Canonical User ID for Stripe customer ' . $stripe_customer_id . ' is ' . $canonical->user_id . "\n");
			}

			if( (int) $user_id !== (int) $canonical->user_id ) {

				if ( true === $log_only ) {
					fwrite( $log_file, 'User ID ' . $user_id . ' is not the canonical' . "\n");
				}

				// Remove the recurring customer ID
				$edd_subscriber = new EDD_Recurring_Subscriber( $user_id, true );

				if( ! $edd_subscriber || ! $edd_subscriber->id > 0 ) {
					continue;
				}

				$profiles = $edd_subscriber->get_recurring_customer_ids();

				if( isset( $profiles['stripe'] ) ) {
					unset( $profiles['stripe'] );
				}

				/*
				 * We now need to look over the customer's signup payments to determine if a customer ID already exists.
				 * A customer ID will exist on the payment record for customers that paid through Stripe but then cancelled or had their subscription deleted.
				 * The original 2.4 upgrade routine did not store the customer ID if $customer->subscriptions->data returned an empty array.
				 *
				 * If we do not find a customer ID on the payment, we create a new one.
				 */

				$customer_id   = false;
				$subscriptions = $edd_subscriber->get_subscriptions();
				if( ! empty( $subscriptions ) ) {

					foreach( $subscriptions as $subscription ) {

						foreach ( edd_get_payment_notes( $subscription->parent_payment_id ) as $note ) {
							if ( preg_match( '/^Stripe Customer ID: ([^\s]+)/', $note->comment_content, $match ) ) {
								$customer_id = $match[1];
								continue;
							}

						}

					}

				}


				if ( true === $log_only ) {

					if( ! empty( $customer_id ) ) {

						fwrite( $log_file, 'Customer ID for User ID ' . $user_id . ' found on previous payment record: ' . $customer_id . "\n");

					} else {

						fwrite( $log_file, 'User ID ' . $user_id . ' needs new Stripe customer' . "\n");

					}

				} else {

					if( empty( $customer_id ) ) {

						// This is reset below but we remove it here just in case customer creation in Stripe fails.
						update_user_meta( $user_id, '_edd_recurring_id', $profiles );

						try {

							// Create a customer first so we can retrieve them later for future payments
							$new_customer = \Stripe\Customer::create( array(
									'description' => $edd_subscriber->email,
									'email'       => $edd_subscriber->email,
									'metadata'    => array(
										'edd_customer_id' => $edd_subscriber->id,
									)
								)
							);

							$customer_id = $new_customer->id;

						} catch ( Exception $e ) {

							$new_customer = false;

						}

					}

					// Assign the subscriber the new Stripe ID we just found or created, replacing the old (incorrect) one
					if ( ! empty( $customer_id ) ) {
						$edd_subscriber->set_recurring_customer_id( $customer_id, 'stripe' );
						update_user_meta( $user_id, '_edd_stripe_customer_id_' . $prefix, $customer_id );
					}

				}

				unset( $note );
				unset( $edd_subscriber );
				unset( $subscriptions );
				unset( $subscription );
				unset( $new_customer );
				unset( $customer_id );
				unset( $profiles );

				$processed[] = $user_id;

				if ( true === $log_only ) {
					fwrite( $log_file, 'User ID ' . $user_id . ' processed successfully' . "\n");
				}

				// Skip to the next user. Only the canonical user ID gets its Stripe subscriptions adjusted.
				continue;

			}

			/*
			 * This customer is the canonical customer that was duplicated onto other user accounts.
			 * We now have to remove any incorrect subscription and source profiles from it.
			 * To fix it, we will first query the customer from Stripe and then check if any subscription
			 * on the Stripe customer matches the profile_id we have stored for this subscriber.
			 * If the profile_id does NOT match, this is an incorrectly assigned subscription.
			 *
			 * For incorrectly assigned subscriptions, we need to do the following:
			 * - delete the profile_id for the EDD_Subscription
			 * - cancel the subscription in Stripe to ensure not further bad payments are processed
			 * - delete the source (card) from the customer in Stripe to ensure no future charges can be placed on it
			 *
			 */
			$subscriber = new EDD_Recurring_Subscriber( $canonical->user_id, true );

			if ( true === $log_only ) {
				fwrite( $log_file, 'User ID ' . $user_id . ' is the canonical and requires further processing' . "\n");
			}

			if( ! $subscriber || ! $subscriber->id > 0 ) {
				if ( true === $log_only ) {
					fwrite( $log_file, 'User ID ' . $user_id . ' had no subscriber object' . "\n");
				}
				// Sad, guess there is nothing we can do for this one.
				continue;
			}

			// Now attempt to retrieve the customer from Stripe.

			try {

				$customer = \Stripe\Customer::retrieve( $stripe_customer_id );

				if( $customer->deleted ) {

					if ( true === $log_only ) {
						fwrite( $log_file, 'User ID ' . $user_id . ' had customer record in Stripe deleted' . "\n");
					}
					// This customer was deleted in Stripe, skip over them.
					continue;

				}

			} catch ( Exception $e ) {

				if ( true === $log_only ) {
					fwrite( $log_file, 'User ID ' . $user_id . ' could not have Stripe customer retrieved' . "\n");
				}

				// For one reason or another, we cannot retrieve this customer, skip over them.
				continue;

			}

			// Confirm the customer has subscriptions in Stripe.
			if ( ! empty( $customer->subscriptions->data ) ) {

				if ( true === $log_only ) {
					fwrite( $log_file, 'User ID ' . $user_id . ' has subscriptions in Stripe' . "\n");
				}

				foreach ( $customer->subscriptions->data as $subscription ) {

					$subscription_id = $subscription->id;

					if ( true === $log_only ) {
						fwrite( $log_file, 'Processing subscription ' . $subscription_id . "\n");
					}

					/*
					 * Get the EDD subscription associated with this user ID, and see if this subscription belongs to them.
					 *
					 * If we successfully retrieve the EDD_Subscription object, it is correct.
					 */
					$edd_sub = new EDD_Subscription( $subscription_id, true );

					if ( ( false === $edd_sub || ! $edd_sub->id > 0 ) && (int) $edd_sub->customer_id !== (int) $subscriber->id ) {

						if ( true === $log_only ) {
							fwrite( $log_file, 'Subscription ' . $subscription_id . ' does not belong on Stripe customer ' . $customer->id . "\n");
						}


						/*
						 * This subscription was recorded with the incorrect Stripe customer so we must:
						 * - delete the profile_id in edd_subscriptions
						 * - cancel the subscription in Stripe
						 * - delete the card source in Stripe
						 *
						 * The customer will have to manually renew their subscription in the future
						 * as automatic renewals will not be processed for this subscription.
						 *
						 * A note is added to the EDD customer to make it easier to track down the
						 * history for this customer if necessary for site admins.
						 *
						 */

						// Now retrieve the sub record while ignoring the incorrect customer attachment so we can update it
						$edd_sub = new EDD_Subscription( $subscription_id, true );

						if ( ! empty( $edd_sub->profile_id ) ) {
							if ( true === $log_only ) {
								fwrite( $log_file, 'Found EDD Subscription ID ' . $edd_sub->id . ' / ' . $edd_sub->profile_id . ' and removing it from Stripe customer ' . $customer->id . "\n");
							} else {
								$edd_sub->update( array( 'profile_id' => '' ) );

								// The subscription ID from Stripe does not belong to this user, cancel it
								$customer->subscriptions->retrieve( $subscription_id )->cancel();

								$edd_sub->customer->add_note( sprintf(
									__( 'Customer\'s subscription #%d was improperly attributed to the incorrect Stripe customer record. It was automatically cancelled to prevent incorrect renewal payments.', 'edd-recurring' ),
									$edd_sub->id
								) );
							}
						}

						if( ! empty( $edd_sub->transaction_id ) && false !== strpos( $edd_sub->transaction_id, 'ch_' ) ) {
							if ( true === $log_only ) {
								fwrite( $log_file, 'Identified charge ' . $edd_sub->transaction_id . ' and removing source' . "\n");
							} else {
								try {
									// Now get the charge from that subscription, and remove the source from the customer
									$charge_data = \Stripe\Charge::retrieve( $edd_sub->transaction_id );
									$source_id   = $charge_data->source->id;
									$customer->sources->retrieve( $source_id )->delete();

								} catch( Exception $e ) {
									// Something failed retrieving the charge. Oh well, was the best we could do.
								}
							}
						}

						unset( $edd_sub );
						unset( $subscription_id );
						unset( $source_id );
						unset( $charge_data );

					} // End if ( false === $edd_sub || ! $edd_sub->id > 0 )

				} // End foreach ( $customer->subscriptions->data as $subscription ) {

				unset( $customer );
				unset( $stripe_customer_id );
				unset( $subscriber );

			} // End ! empty( $customer->subscriptions->data )

		} // End foreach ( $user_ids as $user_id )

		$step ++;
		$redirect = add_query_arg( array(
			'page'        => 'edd-upgrades',
			'edd-upgrade' => 'fix_24_stripe_customers',
			'step'        => $step,
			'number'      => $number,
			'custom'      => true === $log_only ? 1 : 0,
			'total'       => $total
		), admin_url( 'index.php' ) );

		if ( true === $log_only ) {
			fwrite( $log_file, 'Step ' . $step . ' completed' . "\n");
		}

		wp_redirect( $redirect );
		exit;

	} else {

		if ( false === $log_only ) {
			update_option( 'edd_recurring_version', EDD_RECURRING_VERSION );
			edd_set_upgrade_complete( 'fix_24_stripe_customers' );
		} else {
			fwrite( $log_file, 'Completed upgarde routine' . "\n");
		}
		delete_option( 'edd_doing_upgrade' );

		wp_redirect( admin_url() );
		exit;

	}

}
add_action( 'edd_fix_24_stripe_customers', 'edd_recurring_fix_24_stripe_customers' );

/**
 * Determines if we need to fix incorrect Stripe customers from the 2.4 upgrade routine
 *
 * @since  2.4.6
 * @return void
 */
function edd_recurring_needs_24_stripe_fix() {

	global $wpdb;

	// 2.4.6 - Check for duplicate stripe customers after #355
	$needs_stripe_fix = false;
	$has_stripe_subs  = $wpdb->get_col( "SELECT meta_value FROM $wpdb->usermeta WHERE meta_key = '_edd_recurring_id' AND meta_value LIKE '%stripe%'" );

	if ( $has_stripe_subs ) {

		// See if we have at least 2 subscritpions in stripe, if not, set the upgrade routine as completed
		$found_stripe_customers = array();
		foreach ( $has_stripe_subs as $sub ) {
			$ids = maybe_unserialize( $sub );
			if ( isset( $ids['stripe'] ) ) {
				$found_stripe_customers[] = $ids['stripe'];
			}
		}

		// If the number of found stripe customers is greater than the unique count...we have duplicates
		$unique_customers = array_unique( $found_stripe_customers );
		$needs_stripe_fix = count( $unique_customers ) < count( $found_stripe_customers );

	}

	return $needs_stripe_fix;
}

/**
 * Adds subscription_id meta data to renewal payments (including refunded renewals) so they can be queried for reports
 *
 * See https://github.com/easydigitaldownloads/edd-recurring/issues/626
 *
 * @since  2.7
 * @return void
 */
function edd_recurring_27_add_subscription_id_meta() {

	global $wpdb;

	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		wp_die( __( 'You do not have permission to do shop upgrades', 'edd-recurring' ), __( 'Error', 'edd-recurring' ), array( 'response' => 403 ) );
	}

	ignore_user_abort( true );
	if ( ! edd_is_func_disabled( 'set_time_limit' ) ) {
		@set_time_limit( 0 );
	}

	$step   = isset( $_GET['step'] ) ? absint( $_GET['step'] ) : 1;
	$number = isset( $_GET['number'] ) ? absint( $_GET['number'] ) : 5;
	$offset = $step == 1 ? 0 : ( $step - 1 ) * $number;
	$total  = isset( $_GET['total'] ) ? absint( $_GET['total'] ) : false;

	if ( empty( $total ) || $total <= 1 ) {
		$total_sql = "SELECT COUNT(ID) as total_payments FROM $wpdb->posts WHERE post_type = 'edd_payment' AND post_status IN ('edd_subscription','refunded') AND post_parent > 0;";
		$results   = $wpdb->get_row( $total_sql, 0 );
		$total     = $results->total_payments;
	}

	$payments = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT ID,post_parent FROM $wpdb->posts WHERE post_type = 'edd_payment' AND post_status IN ('edd_subscription','refunded') AND post_parent > 0 ORDER BY post_date ASC LIMIT %d,%d;",
			$offset,
			$number
		)
	);

	if( $payments ) {

		foreach( $payments as $payment ) {

			$subscription_id = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM " . $wpdb->prefix . "edd_subscriptions WHERE parent_payment_id = %d LIMIT 0,1", $payment->post_parent ) );

			if( $subscription_id ) {

				edd_update_payment_meta( $payment->ID, 'subscription_id', $subscription_id );

			}

		}

		$step ++;
		$redirect = add_query_arg( array(
			'page'        => 'edd-upgrades',
			'edd-upgrade' => 'recurring_27_subscription_meta',
			'step'        => $step,
			'number'      => $number,
			'total'       => $total
		), admin_url( 'index.php' ) );

		wp_redirect( $redirect );
		exit;

	} else {

		update_option( 'edd_recurring_version', EDD_RECURRING_VERSION );
		edd_set_upgrade_complete( 'recurring_27_subscription_meta' );

		delete_option( 'edd_doing_upgrade' );

		wp_redirect( admin_url() );
		exit;

	}

}
add_action( 'edd_recurring_27_subscription_meta', 'edd_recurring_27_add_subscription_id_meta' );

/**
 * Removes pre-existing logs for the PayPal Pro & Express payment gateway error logs
 *
 * @since  2.7.14
 * @return void
 */
function edd_recurring_paypalproexpress_logs() {

	global $wpdb;

	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		wp_die( __( 'You do not have permission to do shop upgrades', 'edd-recurring' ), __( 'Error', 'edd-recurring' ), array( 'response' => 403 ) );
	}

	ignore_user_abort( true );
	if ( ! edd_is_func_disabled( 'set_time_limit' ) ) {
		@set_time_limit( 0 );
	}

	$step   = isset( $_GET['step'] )   ? absint( $_GET['step'] ) : 1;
	$number = isset( $_GET['number'] ) ? absint( $_GET['number'] ) : 25;
	$total  = isset( $_GET['total'] ) ? absint( $_GET['total'] ) : false;

	if ( empty( $total ) || $total <= 1 ) {
		$total_sql = "SELECT COUNT(ID) as total_error_logs FROM $wpdb->posts WHERE post_title = 'PayPal Express Error' AND post_type = 'edd_log'";
		$results   = $wpdb->get_row( $total_sql, 0 );
		$total     = $results->total_error_logs;

		// We had no errors, so just mark the upgrade as complete.
		if ( empty( $total ) ) {
			update_option( 'edd_recurring_version', preg_replace( '/[^0-9.].*/', '', EDD_RECURRING_VERSION ) );
			edd_set_upgrade_complete( 'recurring_paypalproexpress_logs' );

			delete_option( 'edd_doing_upgrade' );

			wp_redirect( admin_url() );
			exit;
		}
	}

	$logs = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT ID FROM $wpdb->posts WHERE post_title = 'PayPal Express Error' AND post_type = 'edd_log' ORDER BY post_date ASC LIMIT %d;",
			$number
		)
	);

	if( $logs ) {

		foreach( $logs as $log_id ) {
			wp_delete_post( (int)$log_id->ID, true );
		}

		$step ++;
		$redirect = add_query_arg( array(
			'page'        => 'edd-upgrades',
			'edd-upgrade' => 'recurring_paypalproexpress_logs',
			'step'        => $step,
			'number'      => $number,
			'total'       => $total
		), admin_url( 'index.php' ) );

		wp_redirect( $redirect );
		exit;

	} else {

		update_option( 'edd_recurring_version', EDD_RECURRING_VERSION );
		edd_set_upgrade_complete( 'recurring_paypalproexpress_logs' );

		delete_option( 'edd_doing_upgrade' );

		wp_redirect( admin_url() );
		exit;

	}

}
add_action( 'edd_recurring_paypalproexpress_logs', 'edd_recurring_paypalproexpress_logs' );

/**
 * Adds columns to the EDD_Subscriptions table for initial_tax and recurring_tax
 *
 * @since  2.7.17
 * @return void
 */
function edd_recurring_add_tax_columns_to_subs_table() {

	global $wpdb;

	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		wp_die( __( 'You do not have permission to do shop upgrades', 'edd-recurring' ), __( 'Error', 'edd-recurring' ), array( 'response' => 403 ) );
	}

	ignore_user_abort( true );
	if ( ! edd_is_func_disabled( 'set_time_limit' ) ) {
		@set_time_limit( 0 );
	}

	$step   = isset( $_GET['step'] )   ? absint( $_GET['step'] ) : 1;
	$number = isset( $_GET['number'] ) ? absint( $_GET['number'] ) : 10;
	$total  = isset( $_GET['total'] ) ? absint( $_GET['total'] ) : false;
	$db    = new EDD_Subscriptions_DB;

	if( ! edd_use_taxes() ) {
		$total = 1; // Little hack to skip upgrading subscription records for stores that do not use taxes.
	}

	if ( empty( $total ) || $total <= 1 ) {

		$total = $db->count();

		// We had no errors, so just mark the upgrade as complete.
		if ( empty( $total ) ) {
			update_option( 'edd_recurring_version', EDD_RECURRING_VERSION );
			edd_set_upgrade_complete( 'recurring_add_tax_columns_to_subs_table' );

			delete_option( 'edd_doing_upgrade' );

			wp_redirect( admin_url() );
			exit;
		}
	}

	$subs = $db->get_subscriptions( array(
		'number' => $number,
		'offset' => 10 * ( $step - 1 ),
	) );

	if( $subs ) {

		foreach( $subs as $subscription ) {
			$payment = edd_get_payment( $subscription->parent_payment_id );
			$args    = array();
			$subscription->update( array(
				'initial_tax'        => $payment->tax,
				'recurring_tax'      => ( (float) $subscription->recurring_amount - (float) $payment->tax ) * (float) $payment->tax_rate,
				'initial_tax_rate'   => $payment->tax_rate,
				'recurring_tax_rate' => $payment->tax_rate,
			) );
		}

		$step ++;
		$redirect = add_query_arg( array(
			'page'        => 'edd-upgrades',
			'edd-upgrade' => 'recurring_add_tax_columns_to_subs_table',
			'step'        => $step,
			'number'      => $number,
			'total'       => $total
		), admin_url( 'index.php' ) );

		wp_redirect( $redirect );
		exit;

	} else {

		update_option( 'edd_recurring_version', EDD_RECURRING_VERSION );
		edd_set_upgrade_complete( 'recurring_add_tax_columns_to_subs_table' );

		delete_option( 'edd_doing_upgrade' );

		wp_redirect( admin_url() );
		exit;

	}

}
add_action( 'edd_recurring_add_tax_columns_to_subs_table', 'edd_recurring_add_tax_columns_to_subs_table' );


/**
 * Adds columns to the EDD_Subscriptions table for initial_tax and recurring_tax
 *
 * @since  2.7.17
 * @return void
 */
function edd_recurring_cancel_subs_if_times_met() {

	global $wpdb;

	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		wp_die( __( 'You do not have permission to do shop upgrades', 'edd-recurring' ), __( 'Error', 'edd-recurring' ), array( 'response' => 403 ) );
	}

	ignore_user_abort( true );
	if ( ! edd_is_func_disabled( 'set_time_limit' ) ) {
		@set_time_limit( 0 );
	}

	$step   = isset( $_GET['step'] )   ? absint( $_GET['step'] ) : 1;
	$number = isset( $_GET['number'] ) ? absint( $_GET['number'] ) : 10;
	$total  = isset( $_GET['total'] ) ? absint( $_GET['total'] ) : false;
	$db    = new EDD_Subscriptions_DB;

	$subs = $db->get_subscriptions( array(
		'number'              => $number,
		'offset'              => 10 * ( $step - 1 ),
		'bill_times'          => 0,
		'bill_times_operator' => '>'
	) );

	if( $subs ) {

		foreach( $subs as $subscription ) {

			$times_billed = $subscription->get_times_billed();

			// Complete subscription if applicable
			if ( $subscription->bill_times > 0 && $times_billed >= $subscription->bill_times ) {
				$subscription->complete();
				$subscription->status = 'completed';
			}

		}

		$step ++;
		$redirect = add_query_arg( array(
			'page'        => 'edd-upgrades',
			'edd-upgrade' => 'recurring_cancel_subs_if_times_met',
			'step'        => $step,
			'number'      => $number,
			'total'       => $total
		), admin_url( 'index.php' ) );

		wp_redirect( $redirect );
		exit;

	} else {

		update_option( 'edd_recurring_version', EDD_RECURRING_VERSION );
		edd_set_upgrade_complete( 'recurring_cancel_subs_if_times_met' );

		delete_option( 'edd_doing_upgrade' );

		wp_redirect( admin_url() );
		exit;

	}

}
add_action( 'edd_recurring_cancel_subs_if_times_met', 'edd_recurring_cancel_subs_if_times_met' );

/**
 * Manages the addition of the `price_id` column in the database.
 *
 * @since 2.9.0
 */
function edd_upgrade_render_recurring_add_price_id_column() {
	wp_enqueue_script( 'jquery' );

	$migration_complete = edd_has_upgrade_completed( 'recurring_add_price_id_column' );

	if ( $migration_complete ) : ?>
		<div id="edd-sl-migration-complete" class="notice notice-success">
			<p>
				<?php _e( '<strong>Migration complete:</strong> You have already completed the subscription Price ID upgrade.', 'edd-recurring' ); ?>
			</p>
		</div>
		<?php return; ?>
	<?php endif; ?>

	<div id="edd-migration-ready" class="notice notice-success" style="display: none;">
		<p>
			<?php _e( '<strong>Database Upgrade Complete:</strong> All database upgrades have been completed.', 'edd-recurring' ); ?>
			<br /><br />
			<?php _e( 'You may now leave this page.', 'edd-recurring' ); ?>
		</p>
	</div>

	<div id="edd-migration-nav-warn" class="notice notice-info">
		<p>
			<?php _e( '<strong>Important:</strong> Please leave this screen open and do not navigate away until the process completes.', 'edd-recurring' ); ?>
		</p>
	</div>

	<style>
		.dashicons.dashicons-yes { display: none; color: rgb(0, 128, 0); vertical-align: middle; }
	</style>
	<script>
		jQuery( function($) {
			$(document).ready(function () {
				$(document).on("DOMNodeInserted", function (e) {
					var element = e.target;

					if (element.id === 'edd-batch-success') {
						element = $(element);

						element.parent().prev().find('.edd-migration.allowed').hide();
						element.parent().prev().find('.edd-migration.unavailable').show();
						var element_wrapper = element.parents().eq(4);
						element_wrapper.find('.dashicons.dashicons-yes').show();

						var next_step_wrapper = element_wrapper.next();
						if (next_step_wrapper.find('.postbox').length) {
							next_step_wrapper.find('.edd-migration.allowed').show();
							next_step_wrapper.find('.edd-migration.unavailable').hide();

							if (auto_start_next_step) {
								next_step_wrapper.find('.edd-export-form').submit();
							}
						} else {
							$('#edd-migration-nav-warn').hide();
							$('#edd-migration-ready').slideDown();
						}

					}
				});
			});
		});
	</script>

	<div class="metabox-holder">
		<div class="postbox">
			<h2 class="hndle">
				<span><?php _e( 'Update subscription records', 'edd-recurring' ); ?></span>
				<span class="dashicons dashicons-yes"></span>
			</h2>
			<div class="inside update-subscription-records-control">
				<p>
					<?php _e( 'This update will add the price ID of any variably priced subscription to the subscription record in the database.', 'edd-recurring' ); ?>
				</p>
				<form method="post" id="edd-add-price-id-column-subs-form" class="edd-export-form edd-import-export-form">
			<span class="step-instructions-wrapper">

				<?php wp_nonce_field( 'edd_ajax_export', 'edd_ajax_export' ); ?>

				<?php if ( ! $migration_complete ) : ?>
					<span class="edd-migration allowed">
						<input type="submit" id="add-price-ids-submit" value="<?php _e( 'Update Subscriptions', 'edd-recurring' ); ?>" class="button-primary"/>
					</span>
				<?php else: ?>
					<input type="submit" disabled="disabled" id="migrate-logs-submit" value="<?php _e( 'Update Subscriptions', 'edd-recurring' ); ?>" class="button-secondary"/>
					&mdash; <?php _e( 'Subscription records have already been updated.', 'edd-recurring' ); ?>
				<?php endif; ?>

				<input type="hidden" name="edd-export-class" value="EDD_Recurring_Add_Subscription_Price_IDs" />
				<span class="spinner"></span>

			</span>
				</form>
			</div><!-- .inside -->
		</div><!-- .postbox -->
	</div>

	<?php
}

/**
 * Manages the addition of the `price_id` column in the database.
 *
 * @since 2.9.3
 */
function edd_upgrade_render_recurring_update_price_id_column() {
	wp_enqueue_script( 'jquery' );

	$migration_complete = edd_has_upgrade_completed( 'recurring_update_price_id_column' );

	if ( $migration_complete ) : ?>
		<div id="edd-sl-migration-complete" class="notice notice-success">
			<p>
				<?php _e( '<strong>Migration complete:</strong> You have already completed the subscription Price ID upgrade.', 'edd-recurring' ); ?>
			</p>
		</div>
		<?php return; ?>
	<?php endif; ?>

	<div id="edd-migration-ready" class="notice notice-success" style="display: none;">
		<p>
			<?php _e( '<strong>Database Upgrade Complete:</strong> All database upgrades have been completed.', 'edd-recurring' ); ?>
			<br /><br />
			<?php _e( 'You may now leave this page.', 'edd-recurring' ); ?>
		</p>
	</div>

	<div id="edd-migration-nav-warn" class="notice notice-info">
		<p>
			<?php _e( '<strong>Important:</strong> Please leave this screen open and do not navigate away until the process completes.', 'edd-recurring' ); ?>
		</p>
	</div>

	<style>
		.dashicons.dashicons-yes { display: none; color: rgb(0, 128, 0); vertical-align: middle; }
	</style>
	<script>
		jQuery( function($) {
			$(document).ready(function () {
				$(document).on("DOMNodeInserted", function (e) {
					var element = e.target;

					if (element.id === 'edd-batch-success') {
						element = $(element);

						element.parent().prev().find('.edd-migration.allowed').hide();
						element.parent().prev().find('.edd-migration.unavailable').show();
						var element_wrapper = element.parents().eq(4);
						element_wrapper.find('.dashicons.dashicons-yes').show();

						var next_step_wrapper = element_wrapper.next();
						if (next_step_wrapper.find('.postbox').length) {
							next_step_wrapper.find('.edd-migration.allowed').show();
							next_step_wrapper.find('.edd-migration.unavailable').hide();

							if (auto_start_next_step) {
								next_step_wrapper.find('.edd-export-form').submit();
							}
						} else {
							$('#edd-migration-nav-warn').hide();
							$('#edd-migration-ready').slideDown();
						}

					}
				});
			});
		});
	</script>

	<div class="metabox-holder">
		<div class="postbox">
			<h2 class="hndle">
				<span><?php _e( 'Update subscription records', 'edd-recurring' ); ?></span>
				<span class="dashicons dashicons-yes"></span>
			</h2>
			<div class="inside update-subscription-records-control">
				<p>
					<?php _e( 'This update will update the price ID of any variably priced subscription to the subscription record in the database.', 'edd-recurring' ); ?>
				</p>
				<form method="post" id="edd-add-price-id-column-subs-form" class="edd-export-form edd-import-export-form">
			<span class="step-instructions-wrapper">

				<?php wp_nonce_field( 'edd_ajax_export', 'edd_ajax_export' ); ?>

				<?php if ( ! $migration_complete ) : ?>
					<span class="edd-migration allowed">
						<input type="submit" id="update-price-ids-submit" value="<?php _e( 'Update Subscriptions', 'edd-recurring' ); ?>" class="button-primary"/>
					</span>
				<?php else: ?>
					<input type="submit" disabled="disabled" id="migrate-logs-submit" value="<?php _e( 'Update Subscriptions', 'edd-recurring' ); ?>" class="button-secondary"/>
					&mdash; <?php _e( 'Subscription records have already been updated.', 'edd-recurring' ); ?>
				<?php endif; ?>

				<input type="hidden" name="edd-export-class" value="EDD_Recurring_Update_Subscription_Price_IDs" />
				<span class="spinner"></span>

			</span>
				</form>
			</div><!-- .inside -->
		</div><!-- .postbox -->
	</div>

	<?php
}


/**
 * Registers the upgrade routine to add the `price_id` column to the database.
 *
 * @since 2.9.0
 */
function edd_recurring_register_batch_subscription_price_id_column() {
	add_action( 'edd_batch_export_class_include', 'edd_include_subscription_price_id_batch_processor', 10, 1 );
}
add_action( 'edd_register_batch_exporter', 'edd_recurring_register_batch_subscription_price_id_column', 10 );

/**
 * Includes the files to run the `price_id` column addition routine.
 *
 * @since 2.9.0
 *
 * @param string $class Batch processor class name.
 */
function edd_include_subscription_price_id_batch_processor( $class ) {

	if ( 'EDD_Recurring_Add_Subscription_Price_IDs' === $class ) {
		require_once EDD_RECURRING_PLUGIN_DIR . 'includes/admin/upgrades/class-add-subscription-price-ids.php';
	}

}

/**
 * Registers the upgrade routine to update the `price_id` column to the database.
 *
 * @since 2.9.3
 */
function edd_recurring_register_batch_subscription_price_id_column_update() {
	add_action( 'edd_batch_export_class_include', 'edd_include_subscription_price_id_update_batch_processor', 10, 1 );
}
add_action( 'edd_register_batch_exporter', 'edd_recurring_register_batch_subscription_price_id_column_update', 10 );

/**
 * Includes the files to run the `price_id` column update routine.
 *
 * @since 2.9.3
 *
 * @param string $class Batch processor class name.
 */
function edd_include_subscription_price_id_update_batch_processor( $class ) {

	if ( 'EDD_Recurring_Update_Subscription_Price_IDs' === $class ) {
		require_once EDD_RECURRING_PLUGIN_DIR . 'includes/admin/upgrades/class-update-subscription-price-ids.php';
	}

}
