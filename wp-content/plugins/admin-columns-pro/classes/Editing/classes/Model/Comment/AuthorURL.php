<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Editing_Model_Comment_AuthorURL extends ACP_Editing_Model {

	public function get_view_settings() {
		return array(
			'type' => 'url',
		);
	}

	public function save( $id, $value ) {
		$this->strategy->update( $id, array( 'comment_author_url' => $value ) );
	}

}
