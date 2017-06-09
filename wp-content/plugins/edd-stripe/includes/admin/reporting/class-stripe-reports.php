<?php

/**
 * Class EDD_Stripe_Reports
 *
 * @since 2.6
 *
 */
class EDD_Stripe_Reports {


	/**
	 * Get it started
	 */
	public function __construct() {

		//Graph Reports
		add_filter( 'edd_report_views', array( $this, 'add_stripe_card_reports_view' ) );
		add_action( 'edd_reports_view_stripe', array( $this, 'display_stripe_report' ) );

	}

	/**
	 * Adds "Subscriptions Revenue" to the report views
	 *
	 * @param $views
	 *
	 * @return mixed
	 */
	public function add_stripe_card_reports_view( $views ) {
		$views['stripe'] = __( 'Stripe Reports', 'edds' );

		return $views;
	}


	/**
	 * Get Subscription by Date
	 *
	 * @description: Helper function for reports
	 *
	 * @since      2.6
	 *
	 * @param null $day
	 * @param null $month
	 * @param null $year
	 * @param null $hour
	 *
	 * @return array
	 */
	public function get_stripe_payments_by_date( $day = null, $month = null, $year = null, $hour = null, $include_taxes = false ) {

		$args = apply_filters( 'edd_get_stripe_payments_by_date', array(
			'nopaging'    => true,
			'post_type'   => 'edd_payment',
			'post_status' => array( 'publish', 'revoked' ),
			'year'        => $year,
			'monthnum'    => $month,
			'meta_query'  => array(
				array(
					'key'     => '_edd_payment_gateway',
					'value'   => 'stripe',
					'compare' => '=',
				)
			),
		), $day, $month, $year );

		if ( ! empty( $day ) ) {
			$args['day'] = $day;
		}

		if ( ! empty( $hour ) ) {
			$args['hour'] = $hour;
		}

		$payments = get_posts( $args );

		$return             = array();
		$return['new_earnings']      = 0;
		$return['existing_earnings'] = 0;
		$return['new_count']         = 0;
		$return['existing_count']    = 0;

		if ( $payments ) {
			foreach ( $payments as $payment ) {
				$payment = new EDD_Payment( $payment->ID );
				$amount   = $payment->total;
				$existing = $payment->get_meta( '_edds_used_existing_card' );
				$tax      = 0;

				if ( ! $include_taxes ) {
					$tax = $payment->tax;
				}

				if ( ! empty( $existing ) ) {
					$return['existing_earnings'] += ( $amount - $tax );
					$return['existing_count'] += 1;
				} else {
					$return['new_earnings'] += ( $amount - $tax );
					$return['new_count'] += 1;
				}
			}
		}

		return $return;
	}

	/**
	 * Show subscriptions report
	 *
	 * @access      public
	 * @since       2.6
	 * @return      void
	 */
	public function display_stripe_report() {

		if ( ! current_user_can( 'view_shop_reports' ) ) {
			wp_die( __( 'You do not have permission to view this data', 'edds' ), __( 'Error', 'edds' ), array( 'response' => 401 ) );
		}

		// Retrieve the queried dates
		$dates = edd_get_report_dates();

		// Determine graph options
		switch ( $dates['range'] ) :
			case 'today' :
			case 'yesterday' :
			case 'last_30_days' :
				$day_by_day = true;
				break;
			case 'last_year' :
			case 'this_year' :
			case 'last_quarter' :
			case 'this_quarter' :
				$day_by_day = false;
				break;
			case 'other' :
				if ( $dates['m_end'] - $dates['m_start'] >= 2 || $dates['year_end'] > $dates['year'] && ( $dates['m_start'] != '12' && $dates['m_end'] != '1' ) ) {
					$day_by_day = false;
				} else {
					$day_by_day = true;
				}
				break;
			default:
				$day_by_day = true;
				break;
		endswitch;

		$new_earnings      = 0.00; // Total earnings for payments with new cards
		$existing_earnings = 0.00; // Total earnings for payments using an existing card
		$new_cards         = 0;    // Total payments using a new card
		$existing_cards    = 0;    // Total payments using an existing card


		$include_taxes      = empty( $_GET['exclude_taxes'] ) ? true : false;
		$new_cards_earnings      = array();
		$existing_cards_earnings = array();
		$new_cards_counts        = array();
		$existing_cards_counts   = array();

		if ( $dates['range'] == 'today' || $dates['range'] == 'yesterday' ) {
			// Hour by hour
			$hour  = 1;
			$month = $dates['m_start'];
			while ( $hour <= 23 ) :

				$payments = $this->get_stripe_payments_by_date( $dates['day'], $month, $dates['year'], $hour, $include_taxes );

				$new_earnings      += $payments['new_earnings'];
				$existing_earnings += $payments['existing_earnings'];
				$new_cards         += $payments['new_count'];
				$existing_cards    += $payments['existing_count'];

				$date = mktime( $hour, 0, 0, $month, $dates['day'], $dates['year'] ) * 1000;
				$new_cards_earnings[]      = array( $date, $payments['new_earnings'] );
				$existing_cards_earnings[] = array( $date, $payments['existing_earnings'] );
				$new_cards_counts[]        = array( $date, $payments['new_count'] );
				$existing_cards_counts[]   = array( $date, $payments['existing_count'] );

				$hour ++;
			endwhile;

		} elseif ( $dates['range'] == 'this_week' || $dates['range'] == 'last_week' ) {

			// Day by day
			$day     = $dates['day'];
			$day_end = $dates['day_end'];
			$month   = $dates['m_start'];
			while ( $day <= $day_end ) :

				$payments = $this->get_stripe_payments_by_date( $day, $month, $dates['year'], null, $include_taxes );

				$new_earnings      += $payments['new_earnings'];
				$existing_earnings += $payments['existing_earnings'];
				$new_cards         += $payments['new_count'];
				$existing_cards    += $payments['existing_count'];

				$date = mktime( 0, 0, 0, $month, $day, $dates['year'] ) * 1000;
				$new_cards_earnings[]      = array( $date, $payments['new_earnings'] );
				$existing_cards_earnings[] = array( $date, $payments['existing_earnings'] );
				$new_cards_counts[]        = array( $date, $payments['new_count'] );
				$existing_cards_counts[]   = array( $date, $payments['existing_count'] );
				$day ++;
			endwhile;

		} else {

			$y = $dates['year'];

			while ( $y <= $dates['year_end'] ) :

				$last_year = false;

				if ( $dates['year'] == $dates['year_end'] ) {
					$month_start = $dates['m_start'];
					$month_end   = $dates['m_end'];
					$last_year   = true;
				} elseif ( $y == $dates['year'] ) {
					$month_start = $dates['m_start'];
					$month_end   = 12;
				} elseif ( $y == $dates['year_end'] ) {
					$month_start = 1;
					$month_end   = $dates['m_end'];
				} else {
					$month_start = 1;
					$month_end   = 12;
				}

				$i = $month_start;
				while ( $i <= $month_end ) :
					$d = $dates['day'];

					if ( $day_by_day ) :

						if ( $i == $month_end ) {

							$num_of_days = $dates['day_end'];
							if ( $month_start < $month_end ) {
								$d = 1;
							}

						} else {

							$num_of_days = cal_days_in_month( CAL_GREGORIAN, $i, $y );

						}

						while ( $d <= $num_of_days ) :

							$payments = $this->get_stripe_payments_by_date( $d, $i, $y, null, $include_taxes );

							$new_earnings      += $payments['new_earnings'];
							$existing_earnings += $payments['existing_earnings'];
							$new_cards         += $payments['new_count'];
							$existing_cards    += $payments['existing_count'];

							$date = mktime( 0, 0, 0, $i, $d, $y ) * 1000;
							$new_cards_earnings[]      = array( $date, $payments['new_earnings'] );
							$existing_cards_earnings[] = array( $date, $payments['existing_earnings'] );
							$new_cards_counts[]        = array( $date, $payments['new_count'] );
							$existing_cards_counts[]   = array( $date, $payments['existing_count'] );
							$d ++;

						endwhile;

					else :

						$payments = $this->get_stripe_payments_by_date( null, $i, $y, null, $include_taxes );

						$new_earnings      += $payments['new_earnings'];
						$existing_earnings += $payments['existing_earnings'];
						$new_cards         += $payments['new_count'];
						$existing_cards    += $payments['existing_count'];

						if ( $i == $month_end && $last_year ) {

							$num_of_days = cal_days_in_month( CAL_GREGORIAN, $i, $y );

						} else {

							$num_of_days = 1;

						}

						$date = mktime( 0, 0, 0, $i, $num_of_days, $y ) * 1000;
						$new_cards_earnings[]      = array( $date, $payments['new_earnings'] );
						$existing_cards_earnings[] = array( $date, $payments['existing_earnings'] );
						$new_cards_counts[]        = array( $date, $payments['new_count'] );
						$existing_cards_counts[]   = array( $date, $payments['existing_count'] );

					endif;

					$i ++;

				endwhile;

				$y ++;
			endwhile;

		}

		$data = array(
			__( 'New Card Earnings', 'edds' )                => $new_cards_earnings,
			__( 'Existing Card Earnings', 'edd-recurring' )  => $existing_cards_earnings,
			__( 'New Card Purchases', 'edd-recurring' )      => $new_cards_counts,
			__( 'Existing Card Purchases', 'edd-recurring' ) => $existing_cards_counts,
		);

		$new_earnings_max      = max( wp_list_pluck( $new_cards_earnings, 1 ) );
		$existing_earnings_max = max( wp_list_pluck( $existing_cards_earnings, 1 ) );
		$earnings_max          = max( $new_earnings_max, $existing_earnings_max );

		$new_count_max      = max( wp_list_pluck( $new_cards_counts, 1 ) );
		$existing_count_max = max( wp_list_pluck( $existing_cards_counts, 1 ) );
		$sales_max          = max( $new_count_max, $existing_count_max ) + 1;

		ob_start();
		?>
		<div class="tablenav top">
			<div class="alignleft actions"><?php edd_report_views(); ?></div>
		</div>
		<?php do_action( 'edd_subscription_reports_graph_before' ); ?>
		<div class="metabox-holder">
			<div class="postbox">
				<h3><span><?php _e( 'Stripe Card Types Report', 'edds' ); ?></span></h3>

				<div class="inside">
					<?php
					edd_reports_graph_controls();
					$graph = new EDD_Graph( $data );
					$graph->set( 'x_mode', 'time' );
					$graph->set( 'multiple_y_axes', true );
					$additional_options = array(
						0 => array(
							'max' => $earnings_max,
						),
						1 => array(
							'max' => $earnings_max,
							'show' => false,
						),
						2 => array(
							'max' => $sales_max,
						),
						3 => array(
							'max' => $sales_max,
							'show' => false,
						),
					);
					$graph->set( 'additional_options', 'yaxes: ' . json_encode( $additional_options ) );
					$graph->display();
					?>

					<p class="edd_graph_totals">
						<strong>
							<?php
							_e( 'Gross earnings for period shown: ', 'edds' );
							echo edd_currency_filter( edd_format_amount( $new_earnings  + $existing_earnings) );
							?>
						</strong>
					</p>

					<p class="edd_graph_totals">
						<strong>
							<?php
							_e( 'New card earnings: ', 'edds' );
							echo edd_currency_filter( edd_format_amount( $new_earnings ) );
							?>
						</strong>
					</p>

					<p class="edd_graph_totals">
						<strong>
							<?php
							_e( 'Existing card earnings: ', 'edds' );
							echo edd_currency_filter( edd_format_amount( $existing_earnings ) );
							?>
						</strong>
					</p>

					<p class="edd_graph_totals">
						<strong><?php _e( 'Total sales for period shown: ', 'edds' ); echo edd_format_amount( $new_cards + $existing_cards, false ); ?></strong>
					</p>

					<p class="edd_graph_totals">
						<strong>
							<?php
							_e( 'Total payments with new cards: ', 'edds' );
							echo edd_format_amount( $new_cards, false );
							?>
						</strong>
					</p>

					<p class="edd_graph_totals">
						<strong>
							<?php
							_e( 'Total payments with existing cards: ', 'edds' );
							echo edd_format_amount( $existing_cards, false );
							?>
						</strong>
					</p>

					<?php do_action( 'edd_subscription_reports_graph_additional_stats' ); ?>

					<p class="edd_graph_notes">
						<?php if ( false === $include_taxes ) : ?>
							<em><sup>&dagger;</sup> <?php _e( 'Excludes sales tax.', 'easy-digital-downloads' ); ?></em>
						<?php endif; ?>
					</p>


				</div>
			</div>
		</div>
		<?php do_action( 'edd_subscription_reports_graph_after' ); ?>

		<?php
		// get output buffer contents and end our own buffer
		$output = ob_get_contents();
		ob_end_clean();

		echo $output;

	}

}
new EDD_Stripe_Reports();