<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Filtering_Model_Comment_DateGmt extends ACP_Filtering_Model {

	public function filter_by_date_gmt( $comments_clauses ) {
		global $wpdb;
		$comments_clauses['where'] .= ' ' . $wpdb->prepare( "AND {$wpdb->comments}.comment_date_gmt LIKE %s", $this->get_filter_value() . '%' );

		return $comments_clauses;
	}

	public function get_filtering_vars( $vars ) {
		add_filter( 'comments_clauses', array( $this, 'filter_by_date_gmt' ) );

		return $vars;
	}

	public function get_filtering_data() {
		$data = array();
		$data['order'] = false;
		foreach ( $this->strategy->get_values_by_db_field( 'comment_date_gmt' ) as $_value ) {
			$date = substr( $_value, 0, 7 ); // only year and month
			$data['options'][ $date ] = date_i18n( 'F Y', strtotime( $_value ) );
		}
		krsort( $data['options'] );

		return $data;
	}

}
