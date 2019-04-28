<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * The Subscriptions DB Class
 *
 * @since  2.4
 */

class EDD_Subscriptions_DB extends EDD_DB {

	/**
	 * Get things started
	 *
	 * @access  public
	 * @since   2.4
	 */
	public function __construct() {

		global $wpdb;

		$this->table_name  = $wpdb->prefix . 'edd_subscriptions';
		$this->primary_key = 'id';
		$this->version     = '1.3';

	}

	/**
	 * Get columns and formats
	 *
	 * @access  public
	 * @since   2.4
	 */
	public function get_columns() {
		return array(
			'id'                    => '%d',
			'customer_id'           => '%d',
			'period'                => '%s',
			'initial_amount'        => '%s',
			'initial_tax_rate'      => '%s',
			'initial_tax'           => '%s',
			'recurring_amount'      => '%s',
			'recurring_tax_rate'    => '%s',
			'recurring_tax'         => '%s',
			'bill_times'            => '%d',
			'transaction_id'        => '%s',
			'parent_payment_id'     => '%d',
			'product_id'            => '%d',
			'created'               => '%s',
			'expiration'            => '%s',
			'trial_period'          => '%s',
			'status'                => '%s',
			'notes'                 => '%s',
			'profile_id'            => '%s',
		);
	}

	/**
	 * Get default column values
	 *
	 * @access  public
	 * @since   2.4
	 */
	public function get_column_defaults() {
		return array(
			'customer_id'               => 0,
			'period'                    => '',
			'initial_amount'            => '',
			'initial_tax_rate'          => '',
			'initial_tax'               => '',
			'recurring_amount'          => '',
			'recurring_tax_rate'        => '',
			'recurring_tax'             => '',
			'bill_times'                => 0,
			'transaction_id'            => '',
			'parent_payment_id'         => 0,
			'product_id'                => 0,
			'created'                   => date( 'Y-m-d H:i:s' ),
			'expiration'                => date( 'Y-m-d H:i:s' ),
			'trial_period'              => '',
			'status'                    => '',
			'notes'                     => '',
			'profile_id'                => '',
		);
	}

	/**
	 * Retrieve all subscriptions for a customer
	 *
	 * @access  public
	 * @since   2.4
	 */
	public function get_subscriptions( $args = array() ) {
		global $wpdb;

		$defaults = array(
			'number'              => 20,
			'offset'              => 0,
			'search'              => '',
			'customer_id'         => 0,
			'orderby'             => 'id',
			'order'               => 'DESC',
			'bill_times'          => null,
			'bill_times_operator' => '='
		);

		$args  = wp_parse_args( $args, $defaults );

		if( $args['number'] < 1 ) {
			$args['number'] = 999999999999;
		}

		$where = ' WHERE 1=1 ';
		$join  = '';

		if( isset( $args['bill_times'] ) ) {

			if ( ! is_numeric( $args['bill_times'] ) ) {
				trigger_error( __( 'The bill_times argument should be a number but was not.', 'edd-recurring' ) );
			} else {
				$where .= " AND t1.bill_times {$args['bill_times_operator']} '{$args['bill_times']}'";
			}

		}

		// specific customers
		if( ! empty( $args['id'] ) ) {

			if( is_array( $args['id'] ) ) {
				$ids = implode( ',', array_map('intval', $args['id'] ) );
			} else {
				$ids = intval( $args['id'] );
			}

			$where .= " AND t1.id IN( {$ids} ) ";

		}

		// Specific products
		if( ! empty( $args['product_id'] ) ) {

			if( is_array( $args['product_id'] ) ) {
				$product_ids = implode( ',', array_map('intval', $args['product_id'] ) );
			} else {
				$product_ids = intval( $args['product_id'] );
			}

			$where .= " AND t1.product_id IN( {$product_ids} ) ";

		}

		// Specific parent payments
		if( ! empty( $args['parent_payment_id'] ) ) {

			if( is_array( $args['parent_payment_id'] ) ) {
				$parent_payment_ids = implode( ',', array_map('intval', $args['parent_payment_id'] ) );
			} else {
				$parent_payment_ids = intval( $args['parent_payment_id'] );
			}

			$where .= " AND t1.parent_payment_id IN( {$parent_payment_ids} ) ";

		}

		// Specific transaction IDs
		if( ! empty( $args['transaction_id'] ) ) {

			if( is_array( $args['transaction_id'] ) ) {
				$transaction_ids = implode( "','", array_map('sanitize_text_field', $args['transaction_id'] ) );
			} else {
				$transaction_ids = sanitize_text_field( $args['transaction_id'] );
			}

			$where .= " AND t1.transaction_id IN ( '{$transaction_ids}' ) ";

		}

		// Subscriptions for specific customers
		if( ! empty( $args['customer_id'] ) ) {

			if( is_array( $args['customer_id'] ) ) {
				$customer_ids = implode( ',', array_map('intval', $args['customer_id'] ) );
			} else {
				$customer_ids = intval( $args['customer_id'] );
			}

			$where .= " AND t1.customer_id IN( {$customer_ids} ) ";

		}

		// Subscriptions for specific profile IDs
		if( ! empty( $args['profile_id'] ) ) {

			if( is_array( $args['profile_id'] ) ) {
				$profile_ids = implode( "','", array_map('sanitize_text_field', $args['profile_id'] ) );
			} else {
				$profile_ids = sanitize_text_field( $args['profile_id'] );
			}

			$where .= " AND t1.profile_id IN( '{$profile_ids}' ) ";

		}

		// Subscriptions for specific statuses
		if( ! empty( $args['status'] ) ) {

			if( is_array( $args['status'] ) ) {
				$statuses = implode( "','", array_map( 'sanitize_text_field', $args['status'] ) );
			} else {
				$statuses = sanitize_text_field( $args['status'] );
			}

			$where .= " AND t1.status IN( '{$statuses}' ) ";

		}

		// Subscriptions created for a specific date or in a date range
		if( ! empty( $args['date'] ) ) {

			if( is_array( $args['date'] ) ) {

				if( ! empty( $args['date']['start'] ) ) {

					$start = date( 'Y-m-d H:i:s', strtotime( $args['date']['start'] ) );

					$where .= " AND t1.created >= '{$start}'";

				}

				if( ! empty( $args['date']['end'] ) ) {

					$end = date( 'Y-m-d H:i:s', strtotime( $args['date']['end'] ) );

					$where .= " AND t1.created <= '{$end}'";

				}

			} else {

				$year  = date( 'Y', strtotime( $args['date'] ) );
				$month = date( 'm', strtotime( $args['date'] ) );
				$day   = date( 'd', strtotime( $args['date'] ) );

				$where .= " AND $year = YEAR ( t1.created ) AND $month = MONTH ( t1.created ) AND $day = DAY ( t1.created )";
			}

		}

		// Subscriptions with a specific expiration date or in an expiration date range
		if( ! empty( $args['expiration'] ) ) {

			if( is_array( $args['expiration'] ) ) {

				if( ! empty( $args['expiration']['start'] ) ) {

					$start = date( 'Y-m-d H:i:s', strtotime( $args['expiration']['start'] ) );

					$where .= " AND t1.expiration >= '{$start}'";

				}

				if( ! empty( $args['expiration']['end'] ) ) {

					$end = date( 'Y-m-d H:i:s', strtotime( $args['expiration']['end'] ) );

					$where .= " AND t1.expiration <= '{$end}'";

				}

			} else {

				$year  = date( 'Y', strtotime( $args['expiration'] ) );
				$month = date( 'm', strtotime( $args['expiration'] ) );
				$day   = date( 'd', strtotime( $args['expiration'] ) );

				$where .= " AND $year = YEAR ( t1.expiration ) AND $month = MONTH ( t1.expiration ) AND $day = DAY ( t1.expiration )";
			}

		}

		if ( ! empty( $args['search'] ) ) {

			if( is_email( $args['search'] ) ) {

				$customer = new EDD_Customer( $args['search'] );
				if( $customer && $customer->id > 0 ) {
					$where .= " AND t1.customer_id = '" . esc_sql( $customer->id ) . "'";
				}

			} else if( false !== strpos( $args['search'], 'txn:' ) ) {

				$args['search'] = trim( str_replace( 'txn:', '', $args['search'] ) );
				$where .= " AND t1.transaction_id = '" . esc_sql( $args['search'] ) . "'";

			} else if ( false !== strpos( $args['search'], 'profile_id:' ) ) {

				$args['search'] = trim( str_replace( 'profile_id:', '', $args['search'] ) );
				$where .= " AND t1.profile_id = '" . esc_sql( $args['search'] ) . "'";

			} else if ( false !== strpos( $args['search'], 'product_id:' ) ) {

				$args['search'] = trim( str_replace( 'product_id:', '', $args['search'] ) );
				$where .= " AND t1.product_id = '" . esc_sql( $args['search'] ) . "'";

			} else if ( false !== strpos( $args['search'], 'customer_id:' ) ) {

				$args[ 'search' ] = trim( str_replace( 'customer_id:', '', $args[ 'search' ] ) );
				$where            .= " AND t1.customer_id = '" . esc_sql( $args[ 'search' ] ) . "'";

			} else if ( false !== strpos( $args['search'], 'id:' ) ) {

				$args['search'] = trim( str_replace( 'id:', '', $args['search'] ) );
				$where .= " AND t1.id = '" . esc_sql( $args['search'] ) . "'";

			} else {

				// See if search matches a product name
				$download = get_page_by_title( trim( $args['search'] ), OBJECT, 'download' );

				if( $download ) {

					$args['search'] = $download->ID;
					$where .= " AND t1.product_id = '" . esc_sql( $args['search'] ) . "'";

				} else {

					$where .= " AND ( t1.parent_payment_id LIKE '%%" . esc_sql( $args['search'] ) . "%%' OR t1.profile_id LIKE '%%" . esc_sql( $args['search'] ) . "%%' OR t1.transaction_id LIKE '%%" . esc_sql( $args['search'] ) . "%%' OR t1.product_id LIKE '%%" . esc_sql( $args['search'] ) . "%%' OR t1.id = '" . esc_sql( $args['search'] ) . "' )";

				}

			}

		}

		// Search by gateway
		if ( ! empty( $args['gateway'] ) ) {
			$gateway = sanitize_text_field( $args['gateway'] );

			if ( version_compare(EDD_VERSION, '3.0.0-beta1', '<') ) {

				// Pre EDD 3.0 join
				$join  .= " LEFT JOIN {$wpdb->prefix}postmeta m1 ON t1.parent_payment_id = m1.post_id ";
				$where .= $wpdb->prepare( " AND m1.meta_key = '_edd_payment_gateway' AND m1.meta_value = '%s'", $gateway );

			} else {

				// Post EDD 3.0 join
				$join  .= " LEFT JOIN {$wpdb->prefix}edd_orders o1 on t1.parent_payment_id = o1.id ";
				$where .= $wpdb->prepare( " AND o1.gateway = '%s' ", $gateway );

			}

		}

		$args['orderby'] = ! array_key_exists( $args['orderby'], $this->get_columns() ) ? 'id' : $args['orderby'];

		if( 'amount' == $args['orderby'] ) {
			$args['orderby'] = 't1.amount+0';
		}

		$cache_key = md5( 'edd_subscriptions_' . serialize( $args ) );

		$subscriptions = wp_cache_get( $cache_key, 'edd_subscriptions' );

		$args['orderby'] = esc_sql( $args['orderby'] );
		$args['order']   = esc_sql( $args['order'] );

		if( $subscriptions === false ) {
			$query = $wpdb->prepare( "SELECT t1.* FROM  $this->table_name t1 $join $where ORDER BY {$args['orderby']} {$args['order']} LIMIT %d,%d;", absint( $args['offset'] ), absint( $args['number'] ) );
			$subscriptions = $wpdb->get_results( $query, OBJECT );

			if( ! empty( $subscriptions ) ) {

				foreach( $subscriptions as $key => $subscription ) {

					$subscription_object = wp_cache_get( $subscription->id, 'edd_subscription_objects' );

					// If we didn't find the subscription in cache, get it.
					if ( false === $subscription_object ) {

						$subscription_object = new EDD_Subscription( $subscription );

						// If we got a valid subscription object, save it in cache for 1 hour.
						if ( ! empty( $subscription->id ) ) {
							wp_cache_set( $subscription->id, $subscription_object, 'edd_subscription_objects', 3600 );
						}
					}

					$subscriptions[ $key ] = $subscription_object;
				}

				wp_cache_set( $cache_key, $subscriptions, 'edd_subscriptions', 3600 );

			}

		}

		return $subscriptions;
	}

	/**
	 * Count the total number of subscriptions in the database
	 *
	 * @access  public
	 * @since   2.4
	 */
	public function count( $args = array() ) {

		global $wpdb;

		$defaults = array(
			'bill_times'          => null,
			'bill_times_operator' => '='
		);

		$args = wp_parse_args( $args, $defaults );

		$where = ' WHERE 1=1 ';
		$join  = '';

		if( isset( $args['bill_times'] ) ) {

			if ( ! is_numeric( $args['bill_times'] ) ) {
				trigger_error( __( 'The bill_times argument should be a number but was not.', 'edd-recurring' ) );
			} else {
				$where .= " AND t1.bill_times {$args['bill_times_operator']} '{$args['bill_times']}'";
			}

		}

		// specific customers
		if( ! empty( $args['id'] ) ) {

			if( is_array( $args['id'] ) ) {
				$ids = implode( ',', array_map('intval', $args['id'] ) );
			} else {
				$ids = intval( $args['id'] );
			}

			$where .= " AND t1.id IN( {$ids} ) ";

		}

		// Specific products
		if( ! empty( $args['product_id'] ) ) {

			if( is_array( $args['product_id'] ) ) {
				$product_ids = implode( ',', array_map('intval', $args['product_id'] ) );
			} else {
				$product_ids = intval( $args['product_id'] );
			}

			$where .= " AND t1.product_id IN( {$product_ids} ) ";

		}

		// Specific parent payments
		if( ! empty( $args['parent_payment_id'] ) ) {

			if( is_array( $args['parent_payment_id'] ) ) {
				$parent_payment_ids = implode( ',', array_map('intval', $args['parent_payment_id'] ) );
			} else {
				$parent_payment_ids = intval( $args['parent_payment_id'] );
			}

			$where .= " AND t1.parent_payment_id IN( {$parent_payment_ids} ) ";

		}

		// Subscriptoins for specific customers
		if( ! empty( $args['customer_id'] ) ) {

			if( is_array( $args['customer_id'] ) ) {
				$customer_ids = implode( ',', array_map('intval', $args['customer_id'] ) );
			} else {
				$customer_ids = intval( $args['customer_id'] );
			}

			$where .= " AND t1.customer_id IN( {$customer_ids} ) ";

		}

		// Subscriptions for specific profile IDs
		if( ! empty( $args['profile_id'] ) ) {

			if( is_array( $args['profile_id'] ) ) {
				$profile_ids = implode( ',', array_map('intval', $args['profile_id'] ) );
			} else {
				$profile_ids = intval( $args['profile_id'] );
			}

			$where .= " AND t1.profile_id IN( {$profile_ids} ) ";

		}

		// Specific transaction IDs
		if( ! empty( $args['transaction_id'] ) ) {

			if( is_array( $args['transaction_id'] ) ) {
				$transaction_ids = "'" . implode( "','", array_map('sanitize_text_field', $args['transaction_id'] ) ) . "'";
				$where .= " AND t1.transaction_id IN( {$transaction_ids} ) ";
			} else {
				$transaction_id = sanitize_text_field( $args['transaction_id'] );
				$where  .= " AND t1.transaction_id = '{$transaction_id}' ";
			}

		}

		// Subscriptions for specific statuses
		if( ! empty( $args['status'] ) ) {

			if( is_array( $args['status'] ) ) {
				$statuses = "'" . implode( "','", $args['status'] ) . "'";
				$where  .= " AND t1.status IN( {$statuses} ) ";
			} else {
				$statuses = $args['status'];
				$where  .= " AND t1.status = '{$statuses}' ";
			}



		}

		// Subscriptions created for a specific date or in a date range
		if( ! empty( $args['date'] ) ) {

			if( is_array( $args['date'] ) ) {

				if( ! empty( $args['date']['start'] ) ) {

					$start = date( 'Y-m-d H:i:s', strtotime( $args['date']['start'] ) );

					$where .= " AND t1.created >= '{$start}'";

				}

				if( ! empty( $args['date']['end'] ) ) {

					$end = date( 'Y-m-d H:i:s', strtotime( $args['date']['end'] ) );

					$where .= " AND t1.created <= '{$end}'";

				}

			} else {

				$year  = date( 'Y', strtotime( $args['date'] ) );
				$month = date( 'm', strtotime( $args['date'] ) );
				$day   = date( 'd', strtotime( $args['date'] ) );

				$where .= " AND $year = YEAR ( t1.created ) AND $month = MONTH ( t1.created ) AND $day = DAY ( t1.created )";
			}

		}

		// Subscriptions with a specific expiration date or in an expiration date range
		if( ! empty( $args['expiration'] ) ) {

			if( is_array( $args['expiration'] ) ) {

				if( ! empty( $args['expiration']['start'] ) ) {

					$start = date( 'Y-m-d H:i:s', strtotime( $args['expiration']['start'] ) );

					$where .= " AND t1.expiration >= '{$start}'";

				}

				if( ! empty( $args['expiration']['end'] ) ) {

					$end = date( 'Y-m-d H:i:s', strtotime( $args['expiration']['end'] ) );

					$where .= " AND t1.expiration <= '{$end}'";

				}

			} else {

				$year  = date( 'Y', strtotime( $args['expiration'] ) );
				$month = date( 'm', strtotime( $args['expiration'] ) );
				$day   = date( 'd', strtotime( $args['expiration'] ) );

				$where .= " AND $year = YEAR ( t1.expiration ) AND $month = MONTH ( t1.expiration ) AND $day = DAY ( t1.expiration )";
			}

		}

		if ( ! empty( $args['search'] ) ) {

			if( is_email( $args['search'] ) ) {

				$customer = new EDD_Customer( $args['search'] );
				if( $customer && $customer->id > 0 ) {
					$where .= " AND t1.customer_id = '" . esc_sql( $customer->id ) . "'";
				}

			} else if ( false !== strpos( $args['search'], 'txn:' ) ) {

				$args['search'] = trim( str_replace( 'txn:', '', $args['search'] ) );
				$where .= " AND t1.transaction_id = '" . esc_sql( $args['search'] ) . "'";

			} else if ( false !== strpos( $args['search'], 'profile_id:' ) ) {

				$args['search'] = trim( str_replace( 'profile_id:', '', $args['search'] ) );
				$where .= " AND t1.profile_id = '" . esc_sql( $args['search'] ) . "'";

			} else if ( false !== strpos( $args['search'], 'product_id:' ) ) {

				$args['search'] = trim( str_replace( 'product_id:', '', $args['search'] ) );
				$where .= " AND t1.product_id = '" . esc_sql( $args['search'] ) . "'";

			} else if ( false !== strpos( $args['search'], 'customer_id:' ) ) {

				$args[ 'search' ] = trim( str_replace( 'customer_id:', '', $args[ 'search' ] ) );
				$where .= " AND t1.customer_id = '" . esc_sql( $args[ 'search' ] ) . "'";

			} else if ( false !== strpos( $args['search'], 'id:' ) ) {

				$args['search'] = trim( str_replace( 'id:', '', $args['search'] ) );
				$where .= " AND t1.id = '" . esc_sql( $args['search'] ) . "'";

			} else {

				// See if search matches a product name
				$download = get_page_by_title( trim( $args['search'] ), OBJECT, 'download' );

				if( $download ) {

					$args['search'] = $download->ID;
					$where .= " AND t1.product_id = '" . esc_sql( $args['search'] ) . "'";

				} else {

					$where .= " AND ( t1.parent_payment_id LIKE '%%" . esc_sql( $args['search'] ) . "%%' OR t1.profile_id LIKE '%%" . esc_sql( $args['search'] ) . "%%' OR t1.transaction_id LIKE '%%" . esc_sql( $args['search'] ) . "%%' OR t1.product_id LIKE '%%" . esc_sql( $args['search'] ) . "%%' OR t1.id = '" . esc_sql( $args['search'] ) . "' )";

				}

			}

		}

		// Search by gateway
		if ( ! empty( $args['gateway'] ) ) {
			$gateway = sanitize_text_field( $args['gateway'] );

			if ( version_compare(EDD_VERSION, '3.0.0-beta1', '<') ) {

				// Pre EDD 3.0 join
				$join  .= " LEFT JOIN {$wpdb->prefix}postmeta m1 ON t1.parent_payment_id = m1.post_id ";
				$where .= $wpdb->prepare( " AND m1.meta_key = '_edd_payment_gateway' AND m1.meta_value = '%s'", $gateway );

			} else {

				// Post EDD 3.0 join
				$join  .= " LEFT JOIN {$wpdb->prefix}edd_orders o1 on t1.parent_payment_id = o1.id ";
				$where .= $wpdb->prepare( " AND o1.gateway = '%s' ", $gateway );

			}

		}

		$cache_key = md5( 'edd_subscriptions_count' . serialize( $args ) );

		$count = wp_cache_get( $cache_key, 'edd_subscriptions' );

		if( $count === false ) {

			$sql   = "SELECT COUNT($this->primary_key) FROM " . $this->table_name . " t1" . "{$join}" . "{$where}";
			$count = $wpdb->get_var( $sql );

			wp_cache_set( $cache_key, $count, 'edd_subscriptions', 3600 );

		}

		return absint( $count );

	}

	/**
	 * Create the table
	 *
	 * @access  public
	 * @since   2.4
	 */
	public function create_table() {

		global $wpdb;

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$sql = "CREATE TABLE " . $this->table_name . " (
		id bigint(20) NOT NULL AUTO_INCREMENT,
		customer_id bigint(20) NOT NULL,
		period varchar(20) NOT NULL,
		initial_amount mediumtext NOT NULL,
		initial_tax_rate mediumtext NOT NULL,
		initial_tax mediumtext NOT NULL,
		recurring_amount mediumtext NOT NULL,
		recurring_tax_rate mediumtext NOT NULL,
		recurring_tax mediumtext NOT NULL,
		bill_times bigint(20) NOT NULL,
		transaction_id varchar(60) NOT NULL,
		parent_payment_id bigint(20) NOT NULL,
		product_id bigint(20) NOT NULL,
		created datetime NOT NULL,
		expiration datetime NOT NULL,
		trial_period varchar(20) NOT NULL,
		status varchar(20) NOT NULL,
		profile_id varchar(60) NOT NULL,
		notes longtext NOT NULL,
		PRIMARY KEY  (id),
		KEY profile_id (profile_id),
		KEY customer (customer_id),
		KEY transaction (transaction_id),
		KEY customer_and_status ( customer_id, status)
		) CHARACTER SET utf8 COLLATE utf8_general_ci;";

		dbDelta( $sql );

		update_option( $this->table_name . '_db_version', $this->version );
	}

	/**
	 * Convert object to array
	 *
	 * @since 2.7.4
	 *
	 * @return array
	 */
	public function to_array(){
		$array = array();
		foreach( get_object_vars( $this )as $prop => $var ){
			$array[ $prop ] = $var;
		}

		return $array;
	}
}
