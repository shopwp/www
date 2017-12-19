<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Sorting_Model_Post_Taxonomy extends ACP_Sorting_Model {

	public function get_sorting_vars() {
		add_filter( 'posts_clauses', array( $this, 'sorting_clauses_callback' ), 10, 2 );

		return array(
			'suppress_filters' => false,
			'_acp_taxonomy'    => $this->column->get_taxonomy(),
		);
	}

	/**
	 * Setup clauses to sort by taxonomies
	 *
	 * @since 3.4
	 *
	 * @param array    $clauses array
	 * @param WP_Query $query
	 *
	 * @return array
	 */
	public function sorting_clauses_callback( $clauses, $query ) {
		global $wpdb;

		$conditions[] = $wpdb->prepare( 'taxonomy = %s', $query->get( '_acp_taxonomy' ) );
		$conditions[] = ACP()->sorting()->show_all_results() ? ' OR taxonomy IS NULL' : '';

		$clauses['where'] .= vsprintf( ' AND (%s%s)', $conditions );
		$clauses['orderby'] = "{$wpdb->terms}.name " . $query->query_vars['order'];
		$clauses['join'] .= "
            LEFT OUTER JOIN {$wpdb->term_relationships}
                ON {$wpdb->posts}.ID = {$wpdb->term_relationships}.object_id
            LEFT OUTER JOIN {$wpdb->term_taxonomy}
                USING (term_taxonomy_id)
            LEFT OUTER JOIN {$wpdb->terms}
                USING (term_id)
        ";

		// remove this filter
		remove_filter( 'posts_clauses', array( $this, __FUNCTION__ ) );

		return $clauses;
	}

}
