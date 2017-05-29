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
	 * @return float
	 */
	public function get_estimated_revenue( $days = 0 ) {

		global $wpdb;

		if( empty( $days ) ) {

			$amount = get_transient( 'edd_recurring_estimated_revenue' );

			if( false === $amount ) {

				$now    = date( 'Y-n-d H:i:s', strtotime( 'now' ) );
				$amount = $wpdb->get_var( "SELECT sum(recurring_amount) FROM {$wpdb->prefix}edd_subscriptions WHERE expiration >= '$now' AND 1=1 AND status IN( 'active', 'trialling' );" );

				set_transient( 'edd_recurring_estimated_revenue', $amount, HOUR_IN_SECONDS );

			}

		} else {

			$amount = get_transient( 'edd_recurring_estimated_revenue_' . $days );

			if( false === $amount ) {

				$date   = date( 'Y-n-d H:i:s', strtotime( '+' . absint( $days ) . ' days' ) );
				$now    = date( 'Y-n-d H:i:s', strtotime( 'now' ) );
				$amount = $wpdb->get_var( "SELECT sum(recurring_amount) FROM {$wpdb->prefix}edd_subscriptions WHERE expiration >= '$now' AND expiration <= '$date' AND status IN( 'active', 'trialling' );" );

				set_transient( 'edd_recurring_estimated_revenue_' . $days, $amount, HOUR_IN_SECONDS );

			}

		}

		return edd_currency_filter( edd_format_amount( edd_sanitize_amount( $amount ) ) );
	}
}
$widget = new EDD_Recurring_Summary_Widget;
unset( $widget );