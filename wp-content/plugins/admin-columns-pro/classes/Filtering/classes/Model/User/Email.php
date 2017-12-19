<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Filtering_Model_User_Email extends ACP_Filtering_Model {

	/**
	 * @param $query
	 *
	 * @return WP_Query
	 */
	public function filter_by_email( $query ) {
		global $wpdb;

		$query->query_where .= ' ' . $wpdb->prepare( "AND {$wpdb->users}.user_email = %s", $this->get_filter_value() );

		return $query;
	}

	/**
	 * @param array $vars
	 * @param string $value
	 */
	public function get_filtering_vars( $vars ) {
		add_filter( 'pre_user_query', array( $this, 'filter_by_email' ) );

		return $vars;
	}

	/**
	 * @return array
	 */
	public function get_filtering_data() {
		$data = array();

		if ( $values = $this->strategy->get_values_by_db_field( 'user_email' ) ) {
			foreach ( $values as $value ) {
				$data['options'][ $value ] = $value;
			}
		}

		return $data;
	}

}
