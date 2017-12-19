<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Filtering_Model_Comment_Author extends ACP_Filtering_Model {

	public function filter_by_author( $comments_clauses ) {
		global $wpdb;

		$comments_clauses['where'] .= ' ' . $wpdb->prepare( "AND {$wpdb->comments}.comment_author = %s", $this->get_filter_value() );

		return $comments_clauses;
	}

	public function get_filtering_vars( $vars ) {
		add_filter( 'comments_clauses', array( $this, 'filter_by_author' ) );

		return $vars;
	}

	public function get_filtering_data() {
		$data = array();

		foreach ( $this->strategy->get_values_by_db_field( 'comment_author' ) as $_value ) {
			$data['options'][ $_value ] = $_value;
		}

		return $data;
	}

}
