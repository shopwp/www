<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Settings_Column_NetworkSite_CommentCount extends AC_Settings_Column_CommentCount {

	/**
	 * @param int $blog_id
	 * @param int $original_value
	 *
	 * @return string
	 */
	public function format( $blog_id, $original_value ) {
		$status = $this->get_comment_status();

		switch_to_blog( $blog_id );
		$count = (object) get_comment_count();

		restore_current_blog();

		if ( empty( $count->$status ) ) {
			return $this->column->get_empty_char();
		}

		return ac_helper()->html->link( add_query_arg( array( 'comment_status' => $this->get_comment_status() ), get_admin_url( $blog_id, 'edit-comments.php' ) ), $count->$status );
	}

}
