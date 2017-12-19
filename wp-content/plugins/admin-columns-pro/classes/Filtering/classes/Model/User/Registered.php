<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Filtering_Model_User_Registered extends ACP_Filtering_Model {

	public function filter_by_user_registered( $query ) {
		global $wpdb;

		$query->query_where .= ' ' . $wpdb->prepare( "AND {$wpdb->users}.user_registered LIKE %s", $this->get_filter_value() . '%' );

		return $query;
	}

	public function get_filtering_vars( $vars ) {
		add_filter( 'pre_user_query', array( $this, 'filter_by_user_registered' ) );

		return $vars;
	}

	public function get_filtering_data() {
		$data = array(
			'order' => false,
		);

		foreach ( ac_helper()->user->get_ids() as $id ) {
			$registered_date = $this->column->get_raw_value( $id );
			$date = substr( $registered_date, 0, 7 ); // only year and month
			$data['options'][ $date ] = date_i18n( 'F Y', strtotime( get_date_from_gmt( $registered_date ) ) );
		}

		krsort( $data['options'] );

		return $data;
	}

}
