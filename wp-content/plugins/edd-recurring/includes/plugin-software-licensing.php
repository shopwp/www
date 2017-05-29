<?php

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
		add_filter( 'edd_sl_license_exp_length', array( $this, 'set_license_length_for_trials' ), 10, 4);
		add_filter( 'edd_sl_can_extend_license', array( $this, 'disable_license_extension' ), 10, 2 );
		add_filter( 'edd_recurring_subscription_pre_gateway_args', array( $this, 'add_upgrade_and_renewal_flag' ), 10, 2 );
		add_filter( 'edd_sl_send_scheduled_reminder_for_license', array( $this, 'maybe_suppress_scheduled_reminder_for_license' ), 10, 3 );
		add_filter( 'edd_get_renewals_by_date', array( $this, 'renewals_by_date' ), 10, 4 );
		add_filter( 'edd_subscription_can_renew', array( $this, 'can_renew_subscription' ), 10, 2 );
		add_filter( 'edd_subscription_renew_url', array( $this, 'get_renew_url' ), 10, 2 );
		add_filter( 'edd_recurring_show_stripe_update_payment_method_notice', array( $this, 'maybe_suppress_update_payment_method_notice' ), 10, 2 );

		add_action( 'edd_recurring_post_create_payment_profiles', array( $this, 'handle_subscription_upgrade' ) );
		add_action( 'edd_recurring_post_create_payment_profiles', array( $this, 'handle_manual_license_renewal' ) );
		add_action( 'edd_complete_download_purchase', array( $this, 'handle_non_subscription_upgrade' ), -1, 5 );
		add_action( 'edd_subscription_post_renew', array( $this, 'renew_license_keys' ), 10, 3 );
		add_action( 'edd_recurring_add_subscription_payment', array( $this, 'set_renewal_flag' ), 10, 2 );
		add_action( 'edd_sl_column_purchased', array( $this, 'licenses_table' ), 10 );
		add_action( 'edd_subscription_after_tables', array( $this, 'subscription_details' ), 10 );
		add_action( 'edd_sl_license_key_details', array( $this, 'license_key_details' ) );
		add_action( 'edd_purchase_form_before_submit', array( $this, 'checkout_upgrade_details' ), 9 );
		add_action( 'edd_purchase_form_before_submit', array( $this, 'checkout_license_renewal_details' ), 9 );
		add_action( 'edd_sl_license_metabox_after_license_length', array( $this, 'free_trial_settings_notice' ) );
		add_action( 'edd_recurring_check_expiration', array( $this, 'maybe_sync_license_expiration_on_check_expiration' ), 10, 2 );
	}

	/**
	 * Modifies the recurring amounts in respect to renewal discounts and license upgrades
	 *
	 * @since  2.4
	 * @return array
	 */
	public function set_recurring_amount( $args = array(), $item = array() ) {

		$enabled  = get_post_meta( $args['id'], '_edd_sl_enabled', true );
		$discount = edd_sl_get_renewal_discount_percentage( 0, $item['id'] );

		if ( $enabled ) {

			if ( ! empty( $item['item_number']['options']['is_upgrade'] ) || ! empty( $item['item_number']['options']['is_renewal'] ) ) {

				if( edd_has_variable_prices( $item['id'] ) && 0 !== (int) $item['item_number']['options']['price_id'] ) {

					$price = edd_get_price_option_amount( $args['id'], $args['price_id'] );

				} else {

					$price = edd_get_download_price( $item['id'] );

				}

				if( $discount > 0 ) {

					$args['recurring_amount'] = edd_sanitize_amount( $price - ( $price * ( $discount / 100 ) ) );

				} else {

					$args['recurring_amount'] = edd_sanitize_amount( $price );

				}


			} else {

				if( $discount > 0 ) {

					$renewal_discount = ( $args['recurring_amount'] * ( $discount / 100 ) );

					$args['recurring_amount'] -= $renewal_discount;
					$args['recurring_amount'] = edd_sanitize_amount( $args['recurring_amount'] );

				}

			}

			if( edd_get_option( 'recurring_one_time_discounts' ) ) {
				$args['recurring_tax'] = edd_sanitize_amount( edd_calculate_tax( $args['recurring_amount'] ) );
			} else {
				$args['recurring_tax'] = edd_sanitize_amount( edd_calculate_tax( $args['recurring_amount'] - $item['tax'] ) );
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

		$payments = get_post_meta( $license_id, '_edd_sl_payment_id', false );

		if( count( $payments ) <= 1 && edd_recurring()->has_free_trial( $download_id ) ) {
			// set expiration to trial length
			$trial_period = edd_recurring()->get_trial_period( $download_id );
			$expiration = '+' . $trial_period['quantity'] . ' ' . $trial_period['unit'];
		}

		return $expiration;
	}

	/**
	 * Disables the Extend link in [edd_license_keys] for licenses that are tied to a subscription
	 *
	 * @since  2.4
	 * @return bool
	 */
	public function disable_license_extension( $can_extend, $license_id = 0 ) {

		$sub = $this->get_subscription_of_license( $license_id );

		if( ! empty( $sub ) && $sub->id > 0 ) {
			$can_extend = false;
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

		$args['post_status'][] = 'edd_subscription';

		return $args;
	}

	/**
	 * Determines if a subscription with a license key can be renewed=
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
			$subscription['old_subscription_id'] = $this->get_subscription_of_license( $license_id )->id;

		} elseif( isset( $item['item_number']['options']['is_renewal'] ) && isset( $item['item_number']['options']['license_id'] ) ) {

			$license_id = $item['item_number']['options']['license_id'];
			$sub        = $this->get_subscription_of_license( $license_id );

			if( $sub ) {
				$subscription['is_renewal']          = true;
				$subscription['old_subscription_id'] = $sub->id;
			}

		}

		return $subscription;

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

				$sub = new EDD_Subscription( $subscription['old_subscription_id'] );

				if( ! $sub->can_cancel() && 'manual' !== $sub->gateway ) {
					continue;
				}

				$gateway = edd_recurring()->get_gateway_class( $sub->gateway );

				if( empty( $gateway ) || ! class_exists( $gateway ) ) {
					continue;
				}

				$gateway = new $gateway;

				$recurring = edd_recurring();

				remove_action( 'edd_subscription_cancelled', array( $recurring::$emails, 'send_subscription_cancelled' ), 10 );

				if( $gateway->cancel( $sub, true ) ) {

					$note = sprintf( __( 'Subscription #%d cancelled for license upgrade', 'edd-recurring' ), $sub->id );
					edd_insert_payment_note( $sub->parent_payment_id, $note );

					$sub->cancel();
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

		$gateway = edd_recurring()->get_gateway_class( $sub->gateway );

		if( empty( $gateway ) || ! class_exists( $gateway ) ) {
			return;
		}

		$gateway = new $gateway;

		$recurring = edd_recurring();

		remove_action( 'edd_subscription_cancelled', array( $recurring::$emails, 'send_subscription_cancelled' ), 10 );

		if( $gateway->cancel( $sub, true ) ) {

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

				$gateway = edd_recurring()->get_gateway_class( $sub->gateway );

				if( empty( $gateway ) || ! class_exists( $gateway ) ) {
					continue;
				}

				$gateway = new $gateway;

				$recurring = edd_recurring();

				remove_action( 'edd_subscription_cancelled', array( $recurring::$emails, 'send_subscription_cancelled' ), 10 );

				if( $gateway->cancel( $sub, true ) ) {

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
	public function renew_license_keys( $sub_id, $expiration, $subscription ) {

		// Update the expiration date of the associated license key, if EDD Software Licensing is active

		$license = edd_software_licensing()->get_license_by_purchase( $subscription->parent_payment_id, $subscription->product_id );

		if ( $license ) {

			// Update the expiration dates of the license key
			edd_software_licensing()->renew_license( $license->ID, $subscription->parent_payment_id, $subscription->product_id );

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

			$key = edd_software_licensing()->get_license_key( $license->ID );

			add_post_meta( $payment->ID, '_edd_sl_renewal_key', $key );
			add_post_meta( $license->ID, '_edd_sl_payment_id', $payment->ID );

		}
	}

	/**
	 * Display a link to the subscription details page in Downloads > Licenses
	 *
	 * @since  2.4
	 * @return void
	 */
	public function licenses_table( $license ) {

		$payment_id  = get_post_meta( $license['ID'], '_edd_sl_payment_id', true );
		$download_id = edd_software_licensing()->get_download_id( $license['ID'] );

		$subs = $this->db->get_subscriptions( array( 'product_id' => $download_id, 'parent_payment_id' => $payment_id ) );
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
				printf( __( 'Renews automatically on %s', 'edd-recurring' ), date_i18n( 'F j, Y', strtotime( $sub->expiration ) ) );
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

			$discount = edd_sl_get_renewal_discount_percentage();
			$cost     = edd_currency_filter( edd_sanitize_amount( $price - ( $price * ( $discount / 100 ) ) ) );
			$period   = EDD_Recurring()->get_pretty_subscription_frequency( $item['options']['recurring']['period'] );

			$message  = sprintf(
				__( '%s will now automatically renew %s for %s', 'edd-recurring' ),
				get_the_title( $item['id'] ),
				$period,
				$cost
			);

			echo '<div class="edd-alert edd-alert-warn"><p class="edd_error">' . $message . '</p></div>';

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
			$sub         = $this->get_subscription_of_license( $license_id );

			if( ! $sub || ( $sub->is_active() && 'cancelled' === $sub->status ) ) {
				continue;
			}

			if( edd_has_variable_prices( $item['id'] ) ) {
				$price = edd_get_price_option_amount( $item['id'], $item['options']['price_id'] );
			} else {
				$price = edd_get_download_price( $item['id'] );
			}

			$discount = edd_sl_get_renewal_discount_percentage();
			$cost     = edd_currency_filter( edd_sanitize_amount( $price - ( $price * ( $discount / 100 ) ) ) );
			$period   = EDD_Recurring()->get_pretty_subscription_frequency( $item['options']['recurring']['period'] );

			$message  = sprintf(
				__( 'Your existing subscription to %s will be cancelled and replaced with a new subscription that automatically renews %s for %s.', 'edd-recurring' ),
				get_the_title( $item['id'] ),
				$period,
				$cost
			);

			echo '<div class="edd-alert edd-alert-warn"><p class="edd_error">' . $message . '</p></div>';

		}

	}

	/**
	 * Retrieves the subscription associated with a license key
	 *
	 * If a license key has multiple subscriptions (such as can happen with license upgrades),
	 * the most recently subscription is returned
	 *
	 * @since  2.4
	 * @return void
	 */
	private function get_subscription_of_license( $license_id = 0 ) {

		$payment_ids = get_post_meta( $license_id, '_edd_sl_payment_id' );

		if( ! is_array( $payment_ids ) ) {
			return false;
		}

		$payment_id  = array_pop( $payment_ids );
		$download_id = edd_software_licensing()->get_download_id( $license_id );

		if( $payment_id && $download_id )  {

			$subs = $this->db->get_subscriptions( array(
				'product_id'        => $download_id,
				'parent_payment_id' => $payment_id,
				'status'            => 'active',
				'number'            => 1,
				'order'             => 'DESC'
			) );

			if( $subs ) {

				return array_pop( $subs );

			}

		}

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

}