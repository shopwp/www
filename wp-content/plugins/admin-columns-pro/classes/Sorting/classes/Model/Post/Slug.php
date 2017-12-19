<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Sorting_Model_Post_Slug extends ACP_Sorting_Model {

	public function get_sorting_vars() {
		add_filter( 'posts_fields', array( $this, 'posts_fields_callback' ) );

		$args = array(
			'suppress_filters' => false,
			'fields'           => array(),
		);

		$ids = array();

		foreach ( $this->strategy->get_results( $args ) as $post ) {
			$ids[ $post->ID ] = $post->post_name;

			wp_cache_delete( $post->ID, 'posts' );
		}

		return array(
			'ids' => $this->sort( $ids ),
		);
	}

	/**
	 * Only return fields required for sorting
	 *
	 * @global wpdb $wpdb
	 * @return string
	 */
	public function posts_fields_callback() {
		global $wpdb;

		remove_filter( 'posts_fields', array( $this, __FUNCTION__ ) );

		return "$wpdb->posts.ID, $wpdb->posts.post_name";
	}

}
