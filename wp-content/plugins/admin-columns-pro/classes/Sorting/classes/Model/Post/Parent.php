<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Sorting_Model_Post_Parent extends ACP_Sorting_Model {

	public function get_sorting_vars() {
		add_filter( 'posts_clauses', array( $this, 'sorting_clauses_callback' ) );

		return array(
			'suppress_filters' => false,
		);
	}

	/**
	 * Setup clauses to sort by parent
	 *
	 * @since 4.0
	 *
	 * @param array $clauses array
	 * @param WP_Query $query
	 *
	 * @return array
	 */
	public function sorting_clauses_callback( $clauses ) {
		global $wpdb;

		$order = $this->get_order();
		$join_type = acp_sorting()->show_all_results() ? 'LEFT' : 'INNER';

		$clauses['join'] .= "$join_type JOIN $wpdb->posts AS pp ON $wpdb->posts.post_parent = pp.ID";
		$clauses['orderby'] = "pp.post_title $order, $wpdb->posts.ID $order";

		// run once
		remove_filter( 'posts_clauses', array( $this, __FUNCTION__ ) );

		return $clauses;
	}

}
