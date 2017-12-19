<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Editing_Strategy_Post extends ACP_Editing_Strategy {

	/**
	 * @return int[]
	 */
	public function get_rows() {
		global $wp_query;

		return $this->get_editable_rows( $wp_query->posts );
	}

	/**
	 * @param int|WP_Post $post
	 *
	 * @return bool|int
	 */
	public function user_has_write_permission( $post ) {
		if ( ! is_a( $post, 'WP_Post' ) ) {
			$post = get_post( $post );
		}

		if ( ! $post ) {
			return false;
		}

		if ( ! current_user_can( 'edit_post', $post->ID ) ) {
			return false;
		}

		return $post->ID;
	}

	/**
	 * @since 4.0
	 */
	public function update( $id, $args ) {
		$args['ID'] = $id;

		return wp_update_post( $args );
	}

}
