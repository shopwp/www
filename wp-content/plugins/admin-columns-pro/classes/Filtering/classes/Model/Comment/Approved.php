<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Filtering_Model_Comment_Approved extends ACP_Filtering_Model {

	public function filter_by_approved( $comments_clauses ) {
		global $wpdb;

		$comments_clauses['where'] .= ' ' . $wpdb->prepare( "AND {$wpdb->comments}.comment_approved = %s", $this->get_filter_value() );

		return $comments_clauses;
	}

	public function get_filtering_vars( $vars ) {
		add_filter( 'comments_clauses', array( $this, 'filter_by_approved' ) );

		return $vars;
	}

	public function get_filtering_data() {
		return array(
			'options' => array(
				0 => __( 'No' ),
				1 => __( 'Yes' ),
			),
		);
	}

}
