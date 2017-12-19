<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Sorting_Model_User_CommentCount extends ACP_Sorting_Model {

	public function get_sorting_vars() {
		add_action( 'pre_user_query', array( $this, 'pre_user_query_callback' ) );

		return array(
			'ids' => $this->strategy->get_results(),
		);
	}

	public function pre_user_query_callback( WP_User_Query $query ) {
		global $wpdb;

		$sub_query = "
					LEFT JOIN (
						SELECT user_id, COUNT(user_id) AS comment_count
						FROM {$wpdb->comments}
						WHERE user_id <> 0
						GROUP BY user_id
					) AS comments
					ON {$wpdb->users}.ID = comments.user_id
					";

		$query->query_from .= $sub_query;
		$query->query_orderby = "ORDER BY comment_count " . $query->query_vars['order'];

		if ( ! acp_sorting()->show_all_results() ) {
			$query->query_where .= " AND comment_count IS NOT NULL";
		}

		remove_action( 'pre_user_query', array( $this, __FUNCTION__ ) );
	}
}
