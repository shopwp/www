<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class ACP_Sorting_Strategy_Post extends ACP_Sorting_Strategy {

	/**
	 * @var WP_Query $wp_query
	 */
	private $wp_query;

	/**
	 * @param WP_Query $wp_query
	 */
	private function set_wp_query( WP_Query $wp_query ) {
		$this->wp_query = $wp_query;
	}

	/**
	 * @return WP_Query
	 */
	public function get_wp_query() {
		return $this->wp_query;
	}

	public function get_results( array $args = array() ) {
		return $this->get_posts( $args );
	}

	public function get_order() {
		return $this->wp_query->get( 'order' );
	}

	/**
	 * Get post ID's
	 *
	 * @since 1.0.7
	 *
	 * @param array $args
	 *
	 * @return array Array of post ID's
	 */
	protected function get_posts( array $args = array() ) {
		$query_vars = $this->wp_query ? $this->wp_query->query_vars : array();

		if ( ! isset( $query_vars['post_status'] ) ) {
			$query_vars['post_status'] = array( 'any' );
		}

		if ( isset( $query_vars['orderby'] ) ) {
			$query_vars['orderby'] = false;
		}

		$query_vars['post_status'] = apply_filters( 'acp/sorting/post_status', $query_vars['post_status'], $this );
		$query_vars['no_found_rows'] = 1;
		$query_vars['fields'] = 'ids';
		$query_vars['posts_per_page'] = -1;
		$query_vars['order'] = 'ASC';
		$query_vars['posts_per_archive_page'] = '';

		return get_posts( array_merge( $query_vars, $args ) );
	}

	/**
	 * Handle the sorting request on the post-type listing screens
	 *
	 * @since 1.0
	 *
	 * @param WP_Query $query
	 */
	public function handle_sorting_request( WP_Query $query ) {
		if ( ! $query->is_main_query() || ! $query->get( 'orderby' ) ) {
			return;
		}

		$post_type = $this->column->get_post_type();

		// check screen conditions
		if ( $query->get( 'post_type' ) !== $post_type ) {
			return;
		}

		// set pagination vars
		if ( ! is_post_type_hierarchical( $post_type ) ) {
			$per_page = (int) get_user_option( 'edit_' . $post_type . '_per_page' );

			if ( ! $per_page ) {
				$per_page = 20;
			}

			$query->set( 'posts_per_archive_page', $per_page );
			$query->set( 'posts_per_page', $per_page );
		}

		$this->set_wp_query( $query );

		foreach ( $this->model->get_sorting_vars() as $key => $value ) {
			if ( $this->is_universal_id( $key ) ) {
				$key = 'post__in';
			}

			if ( 'meta_query' === $key ) {
				$value = $this->add_meta_query( $value, $query->get( 'meta_query' ) );
			}

			$query->set( $key, $value );
		}

		// pre-sorting done with an array
		$post__in = $query->get( 'post__in' );

		if ( ! empty( $post__in ) ) {
			$query->set( 'orderby', 'post__in' );
		}
	}

}
