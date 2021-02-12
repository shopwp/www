<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The Recurring Checkout Class.
 *
 * This handles modification of the frontend checkout.
 *
 * Some methods introduced before 2.6 were moved here from the main EDD_Recurring class.
 *
 * Note: there are many filters and helper methods that modify aspects of checkout as well,
 * but they are not included here due to many of them being used elsewhere and they
 * cannot be moved in order to maintain backwards compatibility.
 *
 * Look in the main EDD_Recurring class if you do not find the method you are looking for here.
 *
 * @since  2.6
 */
class EDD_Recurring_Checkout {

	/**
	 * Come alive!
	 *
	 * @since  2.6
	 * @return void
	 */
	public function __construct() {
		$this->init();
	}

	/**
	 * Start things up by adding actions and filters
	 *
	 * @since  2.6
	 * @return void
	 */
	public function init() {

		// Maybe show subscription terms under purchase link.
		add_action( 'edd_purchase_link_end', array( $this, 'show_single_terms_notice' ), 10, 2 );
		add_action( 'edd_after_price_option', array( $this, 'show_variable_terms_notice' ), 10, 3 );
		add_action( 'edd_after_price_options_list', array( $this, 'show_variable_custom_terms_notice' ), 11, 3 );
		add_action( 'edd_checkout_cart_item_title_after', array( $this, 'show_terms_on_cart_item' ), 10, 1 );

		// Maybe show signup fee under purchase link.
		add_action( 'edd_purchase_link_end', array( $this, 'show_single_signup_fee_notice' ), 10, 2 );
		add_action( 'edd_purchase_link_end', array( $this, 'show_single_custom_signup_fee_notice' ), 10, 2 );
		add_action( 'edd_after_price_option', array( $this, 'show_variable_signup_fee_notice' ), 10, 3 );
		add_action( 'edd_after_price_options_list', array( $this, 'show_multi_custom_signup_fee_notice' ), 11, 3 );

		// Maybe show adjusted total on checkout for free trials.
		add_action( 'edd_purchase_form_before_submit', array( $this, 'maybe_remove_total' ) );
		add_action( 'edd_purchase_form_before_submit', array( $this, 'free_trial_total' ), 999 );

		// Accounts for showing the login form when auto register is enabled, and login forms aren't shown.
		add_action( 'edd_purchase_form_before_register_login', array( $this, 'force_login_fields' ) );

		// Notify a user when a subscription failed to be purchased.
		add_action( 'edd_payment_receipt_before', array( $this, 'display_failed_subscriptions' ), 10, 2 );
		add_action( 'edd_retry_failed_subs', array( $this, 'process_add_failed' ) );

		// Check email entered on checkout for repeat trial purchase attempt.
		add_action( 'wp_ajax_nopriv_edd_recurring_check_repeat_trial', array( $this, 'check_repeat_trial' ) );

	}

	/**
	 * If a purchase fails b/c of not being logged in, show the login form if it doesn't show
	 * Covers a use case of auto-register being enabled, and a user account already existing for the email
	 * address used
	 *
	 * @since  2.4.8
	 * @return void
	 */
	public function force_login_fields() {
		if ( isset( $_GET['edd-recurring-login'] ) && '1' === $_GET['edd-recurring-login'] ) {
			?>
			<div class="edd-alert edd-alert-info">
				<p><?php _e( 'An account was detected for your email. Please log in to continue your purchase.', 'edd-recurring' ); ?></p>
				<p>
					<a href="<?php echo wp_lostpassword_url(); ?>" title="<?php _e( 'Lost Password', 'edd-recurring' ); ?>">
						<?php _e( 'Lost Password?', 'edd-recurring' ); ?>
					</a>
				</p>
			</div>
			<?php
			$show_register_form = edd_get_option( 'show_register_form', 'none' ) ;

			if ( 'both' === $show_register_form || 'login' === $show_register_form ) {
				return;
			}
			do_action( 'edd_purchase_form_login_fields' );
		}
	}

	/**
	 * If multiple subscriptions are in the cart and one fails, notifiy the customer about it but process the rest
	 *
	 * @since  2.4.14
	 * @param  WP_Post $payment      The WP_Post object of the payment.
	 * @param  array   $receipt_args Array of arguments of the payment receipt.
	 * @return void
	 */
	public function display_failed_subscriptions( $payment, $receipt_args ) {
		$payment              = edd_get_payment( $payment->ID );
		$failed_subscriptions = $payment->get_meta( '_edd_recurring_failed_subscriptions', true );

		if ( empty( $failed_subscriptions ) ) {
			return;
		}
		$subscription_names = wp_list_pluck( $failed_subscriptions, 'subscription' );
		$subscription_names = implode( ', ', wp_list_pluck( $subscription_names, 'name' ) );

		$error_messages = array();
		$link_data      = array( 'download_ids' => array(), 'price_ids' => array() );

		foreach ( $failed_subscriptions as $key => $subscription ) {
			$error_hash = md5( $subscription['error'] );

			if ( ! isset( $error_messages[ $error_hash ] ) ) {
				$error_messages[ $error_hash ]['message']       = $subscription['error'];
				$error_messages[ $error_hash ]['subscriptions'] = array();

			}

			$error_messages[ $error_hash ]['subscriptions'][] = $subscription;

			$link_data['download_ids'][] = $subscription['subscription']['id'];
			$link_data['price_ids'][]    = ! empty( $subscription['subscription']['price_id'] ) ? $subscription['subscription']['price_id'] : 0;
		}
		?>
		<div class="eddr-failed-subscription-notice">
			<div class="edd-alert edd-alert-warn">
				<p>
					<strong><?php _e( 'Notice', 'edd-recurring' ); ?>:</strong> <?php _e( 'Your purchase is completed, but we encountered an issue while processing payments for the following items', 'edd-recurring' ); ?>:
				</p>
				<p class="edd-recurring-failed-list">
					<?php foreach ( $failed_subscriptions as $key => $subscription ) : ?>
						<span>&mdash;&nbsp;<strong><?php echo $subscription['subscription']['name']; ?></strong>: <?php echo $subscription['error']; ?></span>
					<?php endforeach; ?>
				</p>
				<p>
					<?php _e( 'The above items were removed from the purchase and you were not charged for them. You can attempt to repurchase them at your convenience. All other items were purchased successfully.', 'edd-recurring' ); ?>
				</p>
				<p>
					<form id="edd-recurring-add-failed" class="edd-form" method="post">
						<?php foreach ( $failed_subscriptions as $key => $subscription ) : ?>
							<input type="hidden" name="failed-subs[<?php echo $key; ?>][id]" value="<?php echo $subscription['subscription']['id']; ?>" />
							<?php if ( is_numeric( $subscription['subscription']['price_id'] ) ) : ?>
								<input type="hidden" name="failed-subs[<?php echo $key; ?>][price_id]" value="<?php echo $subscription['subscription']['price_id']; ?>" />
							<?php endif; ?>
						<?php endforeach; ?>
						<input type="submit" class="button" name="edd_recurring_add_failed" value="<?php _e( 'Try Again', 'edd-recurring' ); ?>"/>
						<input type="hidden" name="edd_action" value="retry_failed_subs"/>
						<?php wp_nonce_field( 'edd_retry_failed_subs_nonce', 'edd_retry_failed_subs' ); ?>
					</form>
				</p>
			</div>
		</div>
		<?php
	}

	/**
	 * Display the signup fee notice under the purchase link
	 *
	 * @since  2.4
	 * @param  int   $download_id The download ID beign displayed.
	 * @param  array $args      Array of arguements for the purcahse link.
	 * @return void
	 */
	public function show_single_signup_fee_notice( $download_id, $args ) {
		if ( ! edd_recurring()->is_recurring( $download_id ) ) {
			return;
		}

		$show_notice = edd_get_option( 'recurring_show_signup_fee_notice', false );
		if ( false === $show_notice ) {
			return;
		}

		$download = new EDD_Download( $download_id );

		if ( $download->has_variable_prices() ) {

			$prices               = $download->get_prices();
			$variable_signup_fees = array();
			foreach ( $prices as $price_id => $price ) {
				$variable_signup_fees[ $price_id ] = edd_recurring()->get_signup_fee( $price_id, $download_id );
			}

			$high_fee = max( $variable_signup_fees );
			$low_fee  = min( $variable_signup_fees );

			// Only show the base notice if there is one signup fee, otherwise show on each variable price.
			if ( $high_fee !== $low_fee ) {
				return;
			}

			$signup_fee = $low_fee;

		} else {

			$signup_fee = edd_recurring()->get_signup_fee_single( $download_id );

		}

		if ( empty( $signup_fee ) ) {
			return;
		}

		ob_start();
		$formatted_price = edd_currency_filter( edd_format_amount( $signup_fee, edd_currency_decimal_filter() ) );
		$text = edd_get_option( 'recurring_signup_fee_label', __( 'signup fee', 'edd-recurring' ) );
		?>
		<p class="eddr-notice eddr-signup-fee-notice">
			<em><?php printf( __( 'With %s %s', 'edd-recurring' ), $formatted_price, $text ); ?></em>
		</p>
		<?php

		echo apply_filters( 'edd_recurring_single_signup_notice', ob_get_clean(), $download, $args );
	}

	/**
	 * Display the sign up fee notice under the purchase link for Custom Prices
	 *
	 * @since  2.5
	 * @param  int   $download_id The download ID being displayed.
	 * @param  array $args      Array of arguments for the purchase link.
	 * @return void
	 */
	public function show_single_custom_signup_fee_notice( $download_id, $args ) {

		if ( ! defined( 'EDD_CUSTOM_PRICES' ) ) {
			return;
		}

		$show_notice = edd_get_option( 'recurring_show_signup_fee_notice', false );
		if ( false === $show_notice ) {
			return;
		}

		if ( ! edd_recurring()->is_custom_recurring( $download_id ) ) {
			return;
		}

		$signup_fee = edd_recurring()->get_custom_signup_fee( $download_id );

		if ( empty( $signup_fee ) ) {
			return;
		}

		ob_start();
		$formatted_price = edd_currency_filter( edd_format_amount( $signup_fee, edd_currency_decimal_filter() ) );
		$text = edd_get_option( 'recurring_signup_fee_label', __( 'signup fee', 'edd-recurring' ) );
		?>
		<p class="eddr-notice eddr-signup-fee-notice eddr-custom-signup-fee-notice" style="display:none">
			<em><?php printf( __( 'With %s %s', 'edd-recurring' ), $formatted_price, $text ); ?></em>
		</p>
		<?php

		echo apply_filters( 'edd_recurring_custom_single_signup_notice', ob_get_clean(), $download_id, $args );
	}

	/**
	 * Show the signup fees by vraible prices
	 *
	 * @since  2.4
	 * @param  int    $price_id    The price ID key.
	 * @param  string $price       The Price.
	 * @param  int    $download_id The download ID.
	 * @return void
	 */
	public function show_variable_signup_fee_notice( $price_id, $price, $download_id ) {
		if ( ! edd_recurring()->is_price_recurring( $download_id, $price_id ) ) {
			return;
		}

		$show_notice = edd_get_option( 'recurring_show_signup_fee_notice', false );
		if ( false === $show_notice ) {
			return;
		}

		$signup_fee = edd_recurring()->get_signup_fee( $price_id, $download_id );
		if ( empty( $signup_fee ) ) {
			return;
		}

		ob_start();
		$formatted_price = edd_currency_filter( edd_format_amount( $signup_fee, edd_currency_decimal_filter() ) );
		$text = edd_get_option( 'recurring_signup_fee_label', __( 'signup fee', 'edd-recurring' ) );
		?>
		<p class="eddr-notice eddr-signup-fee-notice variable-prices">
			<em><?php printf( __( 'With %s %s', 'edd-recurring' ), $formatted_price, $text ); ?></em>
		</p>
		<?php

		echo apply_filters( 'edd_recurring_multi_signup_notice', ob_get_clean(), $download_id, $price_id );
	}

	/**
	 * Show the signup fees for Custom Prices
	 *
	 * @since  2.5
	 * @param  int    $download_id The download ID.
	 * @param  array  $prices      The array of price IDs for the download.
	 * @param  string $type        If the inputs are checkboxes (multi-select) or radio (single price).
	 * @return void
	 */
	public function show_multi_custom_signup_fee_notice( $download_id, $prices, $type ) {

		$show_notice = edd_get_option( 'recurring_show_signup_fee_notice', false );
		if ( false === $show_notice ) {
			return;
		}

		if ( ! edd_recurring()->is_custom_recurring( $download_id ) ) {
			return;
		}

		$signup_fee = edd_recurring()->get_custom_signup_fee( $download_id );
		if ( empty( $signup_fee ) ) {
			return;
		}

		ob_start();
		$formatted_price = edd_currency_filter( edd_format_amount( $signup_fee, edd_currency_decimal_filter() ) );
		$text = edd_get_option( 'recurring_signup_fee_label', __( 'signup fee', 'edd-recurring' ) );
		?>
		<p class="eddr-notice eddr-signup-fee-notice variable-prices eddr-custom-signup-fee-notice" style="display:none">
			<em><?php printf( __( 'With %s %s', 'edd-recurring' ), $formatted_price, $text ); ?></em>
		</p>
		<?php

		echo apply_filters( 'edd_recurring_multi_custom_signup_notice', ob_get_clean(), $download_id, $prices, $type );
	}

	/**
	 * Display the sign up fee notice under the purchase link
	 *
	 * @since  2.4
	 * @param  int   $download_id The download ID being displayed.
	 * @param  array $args      Array of arguments for the purchase link.
	 * @return void
	 */
	public function show_single_terms_notice( $download_id, $args ) {
		$is_recurring     = edd_recurring()->is_recurring( $download_id );
		$custom_recurring = defined( 'EDD_CUSTOM_PRICES' ) && edd_recurring()->is_custom_recurring( $download_id );
		if ( ! $is_recurring && ! $custom_recurring ) {
			return;
		}

		$show_notice = edd_get_option( 'recurring_show_terms_notice', false );
		if ( false === $show_notice ) {
			return;
		}

		if ( edd_has_variable_prices( $download_id ) ) {
			return;
		}

		ob_start();
		if ( $is_recurring ) :
			$args = array(
				'period' => edd_recurring()->get_period_single( $download_id ),
				'times'  => edd_recurring()->get_times_single( $download_id ),
			);
			if ( edd_recurring()->has_free_trial( $download_id ) && ( ! edd_get_option( 'recurring_one_time_trials' ) || ! edd_recurring()->has_trialed( $download_id ) ) ) {
				$trial                = edd_recurring()->get_trial_period( $download_id );
				$args['trial_period'] = $trial['unit'];
				$args['trial_unit']   = $trial['quantity'];
			}

			?>
			<p class="eddr-notice eddr-terms-notice">
				<em>
					<?php
					echo esc_html( $this->get_recurring_price_text( $args ) );
					?>
				</em>
			</p>
			<?php
		endif;

		if ( $custom_recurring ) :
			$custom_args = array(
				'period' => edd_recurring()->get_custom_period( $download_id ),
				'times'  => edd_recurring()->get_custom_times( $download_id ),
			);
			if ( ! empty( $args['trial_period'] ) ) {
				$custom_args['trial_period'] = $args['trial_period'];
				$custom_args['trial_unit']   = $args['trial_unit'];
			}
			?>
			<p class="eddr-notice eddr-terms-notice eddr-custom-terms-notice" style="display:none">
				<em>
				<?php
					echo esc_html( $this->get_recurring_price_text( $custom_args ) );
				?>
				</em>
			</p>
			<?php
		endif;

		echo apply_filters( 'edd_recurring_single_terms_notice', ob_get_clean(), $download_id, $args );
	}

	/**
	 * Show the sign up fees by variable prices.
	 *
	 * @since  2.4
	 * @param  int    $price_id    The price ID key.
	 * @param  string $price       The Price.
	 * @param  int    $download_id The download ID.
	 * @return void
	 */
	public function show_variable_terms_notice( $price_id, $price, $download_id ) {
		if ( ! edd_recurring()->is_price_recurring( $download_id, $price_id ) ) {
			return;
		}

		$show_notice = edd_get_option( 'recurring_show_terms_notice', false );
		if ( false === $show_notice ) {
			return;
		}

		$period        = edd_recurring()->get_period( $price_id, $download_id );
		$period_single = edd_recurring()->get_pretty_singular_subscription_frequency( $period );
		$times         = edd_recurring()->get_times( $price_id, $download_id );

		$args = array(
			'period' => edd_recurring()->get_period( $price_id, $download_id ),
			'times'  => edd_recurring()->get_times( $price_id, $download_id ),
		);

		if ( ! empty( $price['trial-quantity'] ) && ! empty( $price['trial-unit'] ) && ( ! edd_get_option( 'recurring_one_time_trials' ) || ! edd_recurring()->has_trialed( $download_id ) ) ) {
			$args['trial_period'] = $price['trial-unit'];
			$args['trial_unit']   = $price['trial-quantity'];
		} elseif ( edd_recurring()->has_free_trial( $download_id, $price_id ) && ( ! edd_get_option( 'recurring_one_time_trials' ) || ! edd_recurring()->has_trialed( $download_id ) ) ) {
			$args['trial_period'] = edd_recurring()->get_trial_period( $download_id, $price_id );
			$args['trial_unit']   = $trial['quantity'];

		}

		ob_start();
		?>
		<p class="eddr-notice eddr-terms-notice variable-prices">
			<em>
				<?php
					echo esc_html( $this->get_recurring_price_text( $args ) );
				?>
			</em>
		</p>
		<?php
		echo apply_filters( 'edd_recurring_multi_terms_notice', ob_get_clean(), $download_id, $price_id );
	}

	/**
	 * Show the subscription terms for variable prices.
	 *
	 * @since  2.5
	 * @param  int    $download_id The download ID.
	 * @param  array  $prices      Variable prices.
	 * @param  string $type        Product type.
	 * @return void
	 */
	public function show_variable_custom_terms_notice( $download_id, $prices, $type ) {

		$show_notice = edd_get_option( 'recurring_show_terms_notice', false );
		if ( false === $show_notice ) {
			return;
		}

		if ( ! defined( 'EDD_CUSTOM_PRICES' ) ) {

			return;

		}

		if ( ! edd_recurring()->is_custom_recurring( $download_id ) ) {

			return;

		}

		$args = array(
			'period' => edd_recurring()->get_custom_period( $download_id ),
			'times'  => edd_recurring()->get_custom_times( $download_id ),
		);

		if ( edd_recurring()->has_free_trial( $download_id ) && ( ! edd_get_option( 'recurring_one_time_trials' ) || ! edd_recurring()->has_trialed( $download_id ) ) ) {
			$trial                = edd_recurring()->get_trial_period( $download_id );
			$args['trial_period'] = $trial['unit'];
			$args['trial_unit']   = $args['trial_period']['quantity'];
		}

		ob_start();
		?>

		<p class="eddr-notice eddr-terms-notice eddr-custom-terms-notice" style="display:none">
			<em>
				<?php
					echo esc_html( $this->get_recurring_price_text( $args ) );
				?>
			</em>
		</p>
		<?php

		echo apply_filters( 'edd_recurring_custom_terms_notice', ob_get_clean(), $download_id, $prices, $type );
	}

	/**
	 * Disclose the subscription terms on the cart item.
	 *
	 * @since  2.4
	 * @param  array $item The cart item.
	 * @return void
	 */
	public function show_terms_on_cart_item( $item ) {

		$show_terms_on_checkout = apply_filters( 'edd_recurring_show_terms_on_cart_item', true, $item );

		if ( false === $show_terms_on_checkout ) {
			return;
		}

		$download_id = absint( $item['id'] );

		if ( empty( $item['options']['recurring'] ) ) {
			return;
		}

		$args = array(
			'period' => $item['options']['recurring']['period'],
			'times'  => $item['options']['recurring']['times'],
		);
		if ( ! empty( $item['options']['recurring']['trial_period']['unit'] ) && ! empty( $item['options']['recurring']['trial_period']['quantity'] ) && ( ! edd_get_option( 'recurring_one_time_trials' ) || ! edd_recurring()->has_trialed( $download_id ) ) ) {
			$args['trial_period'] = $item['options']['recurring']['trial_period']['unit'];
			$args['trial_unit']   = $item['options']['recurring']['trial_period']['quantity'];
		}

		ob_start();
		?>
		<p class="eddr-notice eddr-cart-item-notice">
			<em>
				<?php
				echo esc_html( $this->get_recurring_price_text( $args ) );
				?>
			</em>
		</p>
		<?php

		echo apply_filters( 'edd_recurring_cart_item_notice', ob_get_clean(), $item );
	}

	/**
	 * Gets the recurring price text for notices.
	 *
	 * @since 2.10
	 * @param array $details
	 * @return string
	 */
	private function get_recurring_price_text( $details ) {
		$details = wp_parse_args(
			$details,
			array(
				'period'       => false,
				'times'        => false,
				'trial_period' => false,
				'trial_unit'   => false,
			)
		);

		if ( empty( $details['times'] ) ) {
			/* translators: the billing period */
			$output = sprintf( __( 'Billed once per %1$s until cancelled', 'edd-recurring' ), $this->get_frequency_label( $details['period'] ) );
			if ( $details['trial_period'] && $details['trial_unit'] ) {
				$output = sprintf(
					/* translators: 1. the billing period 2. the number of trial units 3. the trial period unit (week, month) */
					__( 'Billed once per %1$s until cancelled, after a %2$s %3$s free trial', 'edd-recurring' ),
					$this->get_frequency_label( $details['period'] ),
					$details['trial_unit'],
					$this->get_frequency_label( $details['trial_period'] )
				);
			}
		} else {
			$output = sprintf(
				/* translators: 1. the billing period 2. the number of times it will be billed */
				_n(
					'Billed once per %1$s, %2$s time',
					'Billed once per %1$s, %2$s times',
					$details['times'],
					'edd-recurring'
				),
				$this->get_frequency_label( $details['period'] ),
				$details['times']
			);
			if ( $details['trial_period'] && $details['trial_unit'] ) {
				$output = sprintf(
					/* translators: 1. the billing period 2. the number of times the subscription will be billed 3. the number of trial units 4. the trial period unit (week, month) */
					_n(
						'Billed once per %1$s, %2$s time, after a %3$s %4$s free trial',
						'Billed once per %1$s, %2$s times, after a %3$s %4$s free trial',
						$details['times'],
						'edd-recurring'
					),
					$this->get_frequency_label( $details['period'] ),
					$details['times'],
					$details['trial_unit'],
					$this->get_frequency_label( $details['trial_period'] )
				);
			}
		}

		return $output;
	}

	/**
	 * Gets the frequency labels.
	 *
	 * @since 2.10
	 * @param string $period
	 * @param integer $count
	 * @return string
	 */
	private function get_frequency_label( $period, $count = 1 ) {
		$frequency = '';
		// Format period details
		switch ( $period ) {
			case 'day':
				$frequency = _nx( 'day', 'days', $count, 'subscription term', 'edd-recurring' );
				break;
			case 'week':
				$frequency = _nx( 'week', 'weeks', $count, 'subscription term', 'edd-recurring' );
				break;
			case 'month':
				$frequency = _nx( 'month', 'months', $count, 'subscription term', 'edd-recurring' );
				break;
			case 'quarter':
				$frequency = _x( 'quarter', 'subscription term', 'edd-recurring' );
				break;
			case 'semi-year':
				$frequency = _x( 'six months', 'subscription term', 'edd-recurring' );
				break;
			case 'year':
				$frequency = _nx( 'year', 'years', $count, 'subscription term', 'edd-recurring' );
				break;
			default:
				$frequency = $period;
				break;
		}

		return $frequency;
	}

	/**
	 * Remove default total display when cart contains a free trial.
	 *
	 * @since  2.6
	 * @return void
	 */
	public function maybe_remove_total() {

		if ( ! edd_recurring()->cart_has_free_trial() ) {
			return;
		}

		remove_action( 'edd_purchase_form_before_submit', 'edd_checkout_final_total', 999 );
	}

	/**
	 * Display a new total amount and note for free trials.
	 *
	 * @since  2.6
	 * @return void
	 */
	public function free_trial_total() {

		if ( ! edd_recurring()->cart_has_free_trial() ) {
			return;
		}

?>
		<p id="edd_final_total_wrap">
			<strong><?php _e( 'Total Due Today:', 'edd-recurring' ); ?></strong>
			<span class="edd_recurring_trial_total"><?php echo edd_currency_filter( edd_format_amount( 0.00 ) ); ?></span>
			<span class="edd_recurring_trial_total_sep">&ndash;</span>
			<span class="edd_recurring_trial_total_note"><?php _e( 'Your account will be automatically charged when the free trial is completed.', 'edd-recurring' ); ?></span>
		</p>
<?php
	}

	/**
	 * Listen for the action to add failed subscriptions to the cart again.
	 *
	 * @since  2.4.14
	 * @return void
	 */
	public function process_add_failed() {
		if ( empty( $_POST['edd_recurring_add_failed'] ) ) {
			return;
		}
		if ( ! is_user_logged_in() ) {
			return;
		}
		if ( ! wp_verify_nonce( $_POST['edd_retry_failed_subs'], 'edd_retry_failed_subs_nonce' ) ) {
			wp_die( __( 'Nonce verification failed', 'edd-recurring' ), __( 'Error', 'edd-recurring' ), array( 'response' => 403 ) );
		}

		$failed_subs = $_POST['failed-subs'];
		if ( ! is_array( $failed_subs ) ) {
			return;
		}

		foreach ( $failed_subs as $key => $sub ) {
			$options = array();

			if ( isset( $sub['price_id'] ) && is_numeric( $sub['price_id'] ) ) {
				$options['price_id'] = $sub['price_id'];
			}

			edd_add_to_cart( absint( $sub['id'] ), $options );
		}

		wp_redirect( edd_get_checkout_uri() ); exit;
	}

	public function check_repeat_trial() {

		if ( empty( $_POST['email'] ) || empty( $_POST['downloads'] ) ) {
			return;
		}

		$email        = sanitize_text_field( $_POST['email'] );

		// Normalize and sanitize the download IDs.
		$download_ids = ! is_array( $_POST['downloads'] ) ? array( $_POST['downloads'] ) : $_POST['downloads'];
		$download_ids = array_map( 'absint', $download_ids );

		$message      = '';

		if ( ! empty( $download_ids ) ) {
			foreach( $download_ids as $download_id ) {

				if ( edd_recurring()->has_trialed( $download_id, $email ) ) {

					if ( ! empty( $message ) ) {
						$message .= '<br/>';
					}

					$message .= sprintf(
						__( 'You have already used the free trial for <strong>%s</strong>. Please log into your account to complete the purchase. You will be charged immediately.', 'edd-recurring' ),
						get_the_title( $download_id )
					);
				}

			}
		}

		$return = array(
			'message' => $message,
		);

		wp_send_json( $return );

	}

}
