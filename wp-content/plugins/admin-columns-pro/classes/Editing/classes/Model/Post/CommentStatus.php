<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Editing_Model_Post_CommentStatus extends ACP_Editing_Model {

	public function get_view_settings() {
		return array(
			'type'    => 'togglable',
			'options' => array( 'closed', 'open' ),
		);
	}

	public function save( $id, $value ) {
		$this->strategy->update( $id, array( 'comment_status' => $value ) );
	}

}
