<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Editing_Model_Comment_Comment extends ACP_Editing_Model {

	public function get_edit_value( $id ) {
		$comment = get_comment( $id );

		return $comment->comment_content;
	}

	public function get_view_settings() {
		return array(
			'type' => 'textarea',
		);
	}

	public function save( $id, $value ) {
		$this->strategy->update( $id, array( 'comment_content' => $value ) );
	}

}
