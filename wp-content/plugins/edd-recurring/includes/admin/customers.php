<?php

/**
 * Display the customer's subscriptions on the customer card
 *
 * @since  2.4
 * @param  object $customer The Customer object
 * @return void
 */
function edd_recurring_customer_subscriptions_list( $customer ) {

	$subscriber    = new EDD_Recurring_Subscriber( $customer->id );
	$subscriptions = $subscriber->get_subscriptions();

	if( ! $subscriptions ) {
		return;
	}
?>
	<h3><?php _e( 'Subscriptions', 'edd-recurring' ); ?></h3>
	<table class="wp-list-table widefat striped downloads">
		<thead>
			<tr>
				<th><?php echo edd_get_label_singular(); ?></th>
				<th><?php _e( 'Amount', 'edd-recurring' ); ?></th>
				<th><?php _e( 'Status', 'edd-recurring' ); ?></th>
				<th><?php _e( 'Actions', 'edd-recurring' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach( $subscriptions as $subscription ) : ?>
				<tr>
					<td><a href="<?php echo esc_url( admin_url( 'post.php?action=edit&post=' . $subscription->product_id ) ); ?>"><?php echo get_the_title( $subscription->product_id ); ?></a></td>
					<td><?php printf( _x( '%s every %s', 'Example: $10 every month', 'edd-recurring' ), edd_currency_filter( edd_sanitize_amount( $subscription->recurring_amount ), edd_get_payment_currency_code( $subscription->parent_payment_id ) ), $subscription->period ); ?></td>
					<td><?php echo $subscription->get_status_label(); ?></td>
					<td><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=download&page=edd-subscriptions&id=' . $subscription->id ) ); ?>"><?php _e( 'View Details', 'edd-recurring' ); ?></a>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
<?php
}
add_action( 'edd_customer_after_tables', 'edd_recurring_customer_subscriptions_list' );

/**
 * Display a customer's recurring profile IDs on the customer card if they have them
 *
 * @since  2.4.2
 * @param  object $customer Customer Ojbect
 * @return void
 */
function edd_recurring_customer_profile_ids( $customer ) {
	$subscriber = new EDD_Recurring_Subscriber( $customer->id );
	$profiles   = $subscriber->get_recurring_customer_ids();

	if ( ! is_array( $profiles ) || empty( $profiles ) ) {
		return;
	}
	?>
	<h3><?php _e( 'Recurring Profiles', 'edd-recurring' ); ?></h3>
	<table class="wp-list-table widefat striped downloads">
		<thead>
			<tr>
				<th><?php _e( 'Gateway', 'edd-recurring' ); ?></th>
				<th style="width: 150px;"><?php _e( 'Profile ID', 'edd-recurring' ); ?></th>
			</tr>
		</thead>
		<tbody>
		<?php foreach ( $profiles as $gateway => $profile ) : ?>
			<?php
			$gateway_class = EDD_Recurring()->get_gateway_class( $gateway );
			if ( false === $gateway_class ) {
				continue;
			}

			$gateway = new $gateway_class();
			?>
			<tr>
				<td><?php echo $gateway->friendly_name; ?></td>
				<td><?php echo $profile; ?></td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
	<?php
}
add_action( 'edd_customer_after_tables', 'edd_recurring_customer_profile_ids' );

/**
 * Allow the customer recount tool to include edd_subscription payment status.
 *
 * @since  2.4.5
 * @param  array $payment_statuses Array of post statuses.
 * @return array                   Array of post statuses with edd_subscription included.
 */
function edd_recurring_customer_recount_status( $payment_statuses ) {

	$payment_statuses[] = 'edd_subscription';

	return $payment_statuses;

}
add_filter( 'edd_recount_customer_payment_statuses', 'edd_recurring_customer_recount_status', 10, 1 );

/**
 * Allow the customer recount tool to process a subscription payment.
 *
 * @since  2.4.5
 * @param  bool   $ret      Base status for if the payment should be processed.
 * @param  object $payment  WP_Post object of the payment being checked.
 * @return bool             If it's an edd_subscription, return true, otherwise return the supplied return.
 */
function edd_recurring_should_process_payment( $ret, $payment ) {

	if ( 'edd_subscription' === $payment->post_status ) {
		$ret = true;
	}

	return $ret;
}
add_filter( 'edd_customer_recount_should_process_payment', 'edd_recurring_should_process_payment', 10, 2 );

/**
 * Find any customers with subscription customer IDs
 *
 * @since  2.4
 * @param  array $items Current items to remove from the reset
 * @return array        The items with any subscription customer entires
 */
function edd_recurring_reset_delete_sub_customer_ids( $items ) {

	global $wpdb;

	$sql      = "SELECT umeta_id FROM $wpdb->usermeta WHERE meta_key = '_edd_recurring_id'";
	$meta_ids = $wpdb->get_col( $sql );

	foreach ( $meta_ids as $id ) {
		$items[] = array(
			'id'   => (int) $id,
			'type' => 'edd_subscriber_id',
		);
	}

	return $items;
}
add_filter( 'edd_reset_store_items', 'edd_recurring_reset_delete_sub_customer_ids', 10, 1 );

/**
 * Isolate any subscriber Customer IDs to remove from the db on reset
 *
 * @since  2.4
 * @param  stirng $type The type of item to remove from the initial findings
 * @param  array  $item The item to remove
 * @return string       The determine item type
 */
function edd_recurring_reset_recurring_customer_ids( $type, $item ) {

	if ( 'edd_subscriber_id' === $item['type'] ) {
		$type = $item['type'];
	}

	return $type;

}
add_filter( 'edd_reset_item_type', 'edd_recurring_reset_recurring_customer_ids', 10, 2 );

/**
 * Add an SQL item to the reset process for the usermeta with the given umeta_ids
 *
 * @since  2.4
 * @param  array  $sql An Array of SQL statements to run
 * @param  string $ids The IDs to remove for the given item type
 * @return array       Returns the array of SQL statements with statements added
 */
function edd_recurring_reset_customer_queries( $sql, $ids ) {

	global $wpdb;
	$sql[] = "DELETE FROM $wpdb->usermeta WHERE umeta_id IN ($ids)";

	return $sql;

}
add_filter( 'edd_reset_add_queries_edd_subscriber_id', 'edd_recurring_reset_customer_queries', 10, 2 );

/**
 * Cancels subscriptions and deletes them when a customer is deleted
 *
 * @since  2.5
 * @param  int  $customer_id ID of the customer being deleted
 * @param  bool $confirm     Whether site admin has confirmed they wish to delete the customer
 * @param  bool $remove_data Whether associated data should be deleted
 * @return void
 */
function edd_recurring_delete_customer_and_subscriptions( $customer_id, $confirm, $remove_data ) {

	if( empty( $customer_id ) || ! $customer_id > 0 ) {
		return;
	}

	$subscriber       = new EDD_Recurring_Subscriber( $customer_id );
	$subscriptions    = $subscriber->get_subscriptions();
	$subscriptions_db = new EDD_Subscriptions_DB;

	if( ! is_array( $subscriptions ) ) {
		return;
	}

	foreach( $subscriptions as $sub ) {

		if( $sub->can_cancel() ) {

			// Attempt to cancel the subscription in the gateway
			$gateway = edd_recurring()->get_gateway_class( $sub->gateway );

			if( $gateway ) {

				$gateway_obj = new $gateway;
				$gateway_obj->cancel( $sub, true );

			}

		}

		if( $remove_data ) {

			// Delete the subscription from the database
			$subscriptions_db->delete( $sub->id );

		}

	}

}
add_action( 'edd_pre_delete_customer', 'edd_recurring_delete_customer_and_subscriptions', 10, 3 );
