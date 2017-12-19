<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Editing_Strategy_Comment extends ACP_Editing_Strategy {

	public function get_rows() {
		global $wp_list_table;

		return $this->get_editable_rows( $wp_list_table->items );
	}

	/**
	 * @since 4.0
	 * @param int|WP_Comment $comment
	 * @return int|false
	 */
	public function user_has_write_permission( $comment ) {
		if ( ! is_a( $comment, 'WP_Comment' ) ) {
			$comment = get_comment( $comment );
		}

		if ( ! $comment ) {
			return false;
		}

		if ( ! current_user_can( 'edit_comment', $comment->comment_ID ) ) {
			return false;
		}

		return $comment->comment_ID;
	}

	/**
	 * @since 4.0
	 */
	public function update( $id, $args ) {
		$args['comment_ID'] = $id;

		return wp_update_comment( $args );
	}

}
