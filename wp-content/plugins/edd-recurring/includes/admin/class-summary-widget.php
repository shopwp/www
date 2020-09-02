<?php

class EDD_Recurring_Summary_Widget {

	/**
	 * Get things started
	 *
	 * @since  2.4.15
	 * @return void
	 */
	public function __construct() {
		$this->init();
	}

	/**
	 * Add our actions
	 *
	 * @since  2.4.15
	 * @return void
	 */
	public function init() {
		add_action( 'edd_sales_summary_widget_after_stats', array( $this, 'widget' ) );
	}

	/**
	 * Display the widget
	 *
	 * @since  2.4.15
	 * @return void
	 */
	public function widget() {
?>
		<div class="table table_left table_current_month">
			<table>
				<thead>
					<tr>
						<td colspan="2"><?php _e( 'Subscriptions Created', 'edd-recurring' ) ?></td>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td class="first t"><?php _e( 'This Year', 'edd-recurring' ); ?></td>
						<td class="b"><?php echo $this->get_subscription_count( 'year' ); ?></td>
					</tr>
					<tr>
						<td class="first t"><?php _e( 'This Month', 'edd-recurring' ); ?></td>
						<td class="b"><?php echo $this->get_subscription_count( 'month' ); ?></td>
					</tr>
					<tr>
						<td class="first t"><?php _e( 'Total', 'edd-recurring' ); ?></td>
						<td class="b"><?php echo $this->get_subscription_count(); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<div class="table table_right table_totals">
			<table>
				<thead>
					<tr>
						<td colspan="2"><?php _e( 'Estimated Recurring Revenue', 'edd-recurring' ) ?></td>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td class="t"><?php _e( 'Next 365 Days', 'edd-recurring' ); ?></td>
						<td class="last b"><?php echo $this->get_estimated_revenue( 365 ); ?></td>
					</tr>
					<tr>
						<td class="t"><?php _e( 'Next 30 Days', 'edd-recurring' ); ?></td>
						<td class="last b"><?php echo $this->get_estimated_revenue( 30 ); ?></td>
					</tr>
					<tr>
						<td class="t"><?php _e( 'Total', 'edd-recurring' ); ?></td>
						<td class="last b"><?php echo $this->get_estimated_revenue(); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<div style="clear: both"></div>
<?php
	}

	/**
	 * Retrieve subscription count for given period
	 *
	 * @since  2.4.15
	 * @return int
	 */
	public function get_subscription_count( $period = '' ) {

		$db    = new EDD_Subscriptions_DB;
		$start = '';
		$end   = '';

		if( ! empty( $period ) ) {

			switch( $period ) {

				case 'year' :

					$start = 'January 1, ' . date( 'Y' );
					$end   = 'December 31, ' . date( 'Y' );

					break;

				case 'month' :

					$month = date( 'n' );
					$year  = date( 'Y' );
					$day   = cal_days_in_month( CAL_GREGORIAN, $month, $year );

					$start = $year . '-' . $month . '-01 00:00:00';
					$end   = $year . '-' . $month . '-' . $day . ' 23:59:59';

					break;
			}

		}

		$args = array(
			'status' => 'active',
			'date'   => array(
				'start' => $start,
				'end'   => $end
			)
		);

		return edd_format_amount( $db->count( $args ), false );
	}

	/**
	 * Retrieve estimated revenue for the number of days given
	 *
	 * @since  2.4.15
	 *
	 * @param  int $days Number of days (0 to 365)
	 *
	 * @return float
	 */
	public function get_estimated_revenue( $days = 0 ) {

		global $wpdb;

		// Cast days to int
		$days = absint( $days );

		// How long to cache values for
		$expiration = HOUR_IN_SECONDS;

		// "Total"
		if ( empty( $days ) ) {

			// Get the transient
			$key    = 'edd_recurring_estimated_revenue';
			$amount = get_transient( $key );

			// No transient
			if ( false === $amount ) {

				// SQL
				$query = "SELECT SUM(recurring_amount)
						  FROM {$wpdb->prefix}edd_subscriptions
						  WHERE
							  ( expiration >= %s )
							  AND status IN( 'active', 'trialling' )";

				// Boundary
				$now      = date( 'Y-m-d 00:00:00', strtotime( 'now' ) );

				// Query the database
				$prepared = $wpdb->prepare( $query, $now );
				$amount   = $wpdb->get_var( $prepared );

				// Cache
				set_transient( $key, $amount, $expiration );
			}

		// "Next X Days"
		} else {

			// Get the transient
			$key    = 'edd_recurring_estimated_revenue_' . $days;
			$amount = get_transient( $key );

			// No transient
			if ( false === $amount ) {

				// Calculate the ratio based on number of $days
				$yid   = 365; // Year in days
				$ratio = ( $yid / $days );

				// Array of period => interval
				$sum_cases = array(
					'day'       => 365,
					'week'      => 52,
					'month'     => 12,
					'quarter'   => 4,
					'semi-year' => 2,
					'year'      => 1,
				);

				// Default sums array
				$sums = array();

				// Loop through sums and combine into array of SQL
				foreach ( $sum_cases as $period => $interval ) {

					// Adjust the SQL according to the days ratio, rounded up
					$math   = ceil( $interval / $ratio );

					// Add SUM case to array
					$sums[] = "SUM( CASE WHEN period='{$period}' THEN recurring_amount * {$math} END ) AS '{$period}'";
				}

				// Combine SUM() clauses into usable SQL
				$sum_sql = join( ",\n ", $sums );

				// SQL
				$query   = "SELECT {$sum_sql}
							 FROM {$wpdb->prefix}edd_subscriptions
							 WHERE
							 ( expiration >= %s AND expiration <= %s )
							 AND status IN( 'active', 'trialling' )";

				// Boundaries (all day today, all day final day)
				$now  = date( 'Y-m-d 00:00:00', strtotime( 'now' ) );
				$date = date( 'Y-m-d 23:59:59', strtotime( "+ {$days} days" ) );

				// Query the database
				$prepared = $wpdb->prepare( $query, $now, $date );
				$amounts  = $wpdb->get_results( $prepared, ARRAY_A );

				// Sum the values
				$amount   = array_sum( reset( $amounts ) );

				// Cache
				set_transient( $key, $amount, $expiration );

			}
		}

		return edd_currency_filter( edd_format_amount( edd_sanitize_amount( $amount ) ) );
	}
}
$widget = new EDD_Recurring_Summary_Widget;
unset( $widget );
