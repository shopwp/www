<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Sorting_Model_Comment_Response extends ACP_Sorting_Model {

	public function get_sorting_vars() {
		add_filter( 'comments_clauses', array( $this, 'comments_clauses_callback' ) );

		return array();
	}

	public function comments_clauses_callback( $pieces ) {
		global $wpdb;

		$pieces['join'] .= " INNER JOIN $wpdb->posts p ON p.ID = $wpdb->comments.comment_post_ID ";
		$pieces['orderby'] = " p.post_title " . $this->strategy->get_query_var( 'order' );

		remove_filter( 'comments_clauses', array( $this, __FUNCTION__ ) );

		return $pieces;
	}

}
