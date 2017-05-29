<?php

/**
 * Class EDD_Recurring_Reports
 *
 * @since 2.4
 *
 */
class EDD_Recurring_Reports {


	/**
	 * Get it started
	 */
	public function __construct() {

		//Graph Reports
		add_filter( 'edd_report_views', array( $this, 'add_subscriptions_reports_view' ) );
		add_action( 'edd_reports_view_subscriptions', array( $this, 'display_subscriptions_report' ) );

		//Payments' subscription status column
		add_filter( 'edd_payments_table_column', array( $this, 'status_column' ), 800, 3 );

	}

	/**
	 * Adds "Subscriptions Revenue" to the report views
	 *
	 * @param $views
	 *
	 * @return mixed
	 */
	public function add_subscriptions_reports_view( $views ) {
		$views['subscriptions'] = __( 'Subscription Renewals', 'edd-recurring' );

		return $views;
	}


	/**
	 * Get Subscription by Date
	 *
	 * @description: Helper function for reports
	 *
	 * @since      2.4
	 *
	 * @param null $day
	 * @param null $month
	 * @param null $year
	 * @param null $hour
	 *
	 * @return array
	 */
	public function get_subscriptions_by_date( $day = null, $month = null, $year = null, $hour = null, $include_taxes = false ) {

		$args = apply_filters( 'edd_get_subscriptions_by_date', array(
			'nopaging'    => true,
			'post_type'   => 'edd_payment',
			'post_status' => array( 'edd_subscription' ),
			'year'        => $year,
			'monthnum'    => $month,
			'fields'      => 'ids'
		), $day, $month, $year );

		if ( ! empty( $day ) ) {
			$args['day'] = $day;
		}

		if ( ! empty( $hour ) ) {
			$args['hour'] = $hour;
		}

		$subscriptions = get_posts( $args );

		$return             = array();
		$return['earnings'] = 0;
		$return['count']    = count( $subscriptions );
		if ( $subscriptions ) {
			foreach ( $subscriptions as $renewal ) {
				
				$amount    = edd_get_payment_amount( $renewal );
				$total_tax = 0;
				if ( ! $include_taxes ) {
					$total_tax = $wpdb->get_var( "SELECT SUM(meta_value) FROM $wpdb->postmeta WHERE meta_key = '_edd_payment_tax' AND post_id IN ({$sales})" );
				}

				$return['earnings'] += ( $amount - $total_tax );

			}
		}

		return $return;
	}


	/**
	 * Show subscriptions report
	 *
	 * @access      public
	 * @since       2.4
	 * @return      void
	 */
	public function display_subscriptions_report() {

		if ( ! current_user_can( 'view_shop_reports' ) ) {
			wp_die( __( 'You do not have permission to view this data', 'edd-recurring' ), __( 'Error', 'edd-recurring' ), array( 'response' => 401 ) );
		}

		// Retrieve the queried dates
		$dates = edd_get_report_dates();

		// Determine graph options
		switch ( $dates['range'] ) :
			case 'today' :
			case 'yesterday' :
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

		$earnings_totals      = 0.00; // Total earnings for time period shown
		$subscriptions_totals = 0;    // Total sales for time period shown

		//@TODO: Should taxes ever be included?

		$include_taxes      = empty( $_GET['exclude_taxes'] ) ? true : false;
		$earnings_data      = array();
		$subscription_count = array();

		if ( $dates['range'] == 'today' || $dates['range'] == 'yesterday' ) {
			// Hour by hour
			$hour  = 1;
			$month = $dates['m_start'];
			while ( $hour <= 23 ) :

				$subscriptions = $this->get_subscriptions_by_date( $dates['day'], $month, $dates['year'], $hour, $include_taxes );

				$earnings_totals += $subscriptions['earnings'];
				$subscriptions_totals += $subscriptions['count'];

				$date                 = mktime( $hour, 0, 0, $month, $dates['day'], $dates['year'] ) * 1000;
				$subscription_count[] = array( $date, $subscriptions['count'] );
				$earnings_data[]      = array( $date, $subscriptions['earnings'] );

				$hour ++;
			endwhile;

		} elseif ( $dates['range'] == 'this_week' || $dates['range'] == 'last_week' ) {

			// Day by day
			$day     = $dates['day'];
			$day_end = $dates['day_end'];
			$month   = $dates['m_start'];
			while ( $day <= $day_end ) :

				$subscriptions = $this->get_subscriptions_by_date( $day, $month, $dates['year'], null, $include_taxes );

				$earnings_totals += $subscriptions['earnings'];
				$subscriptions_totals += $subscriptions['count'];

				$date                 = mktime( 0, 0, 0, $month, $day, $dates['year'] ) * 1000;
				$subscription_count[] = array( $date, $subscriptions['count'] );
				$earnings_data[]      = array( $date, $subscriptions['earnings'] );
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

					if ( $day_by_day ) :

						if ( $i == $month_end ) {

							$num_of_days = $dates['day_end'];

						} else {

							$num_of_days = cal_days_in_month( CAL_GREGORIAN, $i, $y );

						}

						$d = $dates['day'];

						while ( $d <= $num_of_days ) :

							$subscriptions = $this->get_subscriptions_by_date( $d, $i, $y, null, $include_taxes );

							$earnings_totals += $subscriptions['earnings'];
							$subscriptions_totals += $subscriptions['count'];

							$date                 = mktime( 0, 0, 0, $i, $d, $y ) * 1000;
							$subscription_count[] = array( $date, $subscriptions['count'] );
							$earnings_data[]      = array( $date, $subscriptions['earnings'] );
							$d ++;

						endwhile;

					else :

						$subscriptions = $this->get_subscriptions_by_date( null, $i, $y, null, $include_taxes );

						$earnings_totals += $subscriptions['earnings'];
						$subscriptions_totals += $subscriptions['count'];

						if ( $i == $month_end && $last_year ) {

							$num_of_days = cal_days_in_month( CAL_GREGORIAN, $i, $y );

						} else {

							$num_of_days = 1;

						}

						$date                 = mktime( 0, 0, 0, $i, $num_of_days, $y ) * 1000;
						$subscription_count[] = array( $date, $subscriptions['count'] );
						$earnings_data[]      = array( $date, $subscriptions['earnings'] );

					endif;

					$i ++;

				endwhile;

				$y ++;
			endwhile;

		}

		$data = array(
			__( 'Renewals', 'edd-recurring' ) => $subscription_count,
			__( 'Earnings', 'edd-recurring' )      => $earnings_data
		);

		ob_start();
		?>
		<div class="tablenav top">
			<div class="alignleft actions"><?php edd_report_views(); ?></div>
		</div>
		<?php do_action( 'edd_subscription_reports_graph_before' ); ?>
		<div class="metabox-holder">
			<div class="postbox">
				<h3><span><?php _e( 'Subscription Renewals', 'edd-recurring' ); ?></span></h3>

				<div class="inside">
					<?php
					edd_reports_graph_controls();
					$graph = new EDD_Graph( $data );
					$graph->set( 'x_mode', 'time' );
					$graph->set( 'multiple_y_axes', true );
					$graph->display();
					?>

					<p class="edd_graph_totals">
						<strong>
							<?php
							_e( 'Total earnings for period shown: ', 'easy-digital-downloads' );
							echo edd_currency_filter( edd_format_amount( $earnings_totals ) );
							?>
						</strong>
						<?php if ( ! $include_taxes ) : ?>
							<sup>&dagger;</sup>
						<?php endif; ?>
					</p>

					<p class="edd_graph_totals">
						<strong><?php _e( 'Total subscription renewals for period shown: ', 'edd-recurring' );
							echo edd_format_amount( $subscriptions_totals, false ); ?></strong></p>

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


	/**
	 * Show subscription payment statuses in Payment History
	 *
	 * @since  2.2
	 * @return void
	 */
	public function status_column( $value, $payment_id, $column_name ) {

		if ( 'status' == $column_name && 'edd_subscription' == get_post_status( $payment_id ) ) {
			$value = __( 'Renewal Payment', 'edd-recurring' );
		}

		return $value;
	}

}
new EDD_Recurring_Reports();