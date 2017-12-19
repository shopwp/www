<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Editing_Model_Comment_Approved extends ACP_Editing_Model {

	public function get_view_settings() {
		return array(
			'type'    => 'togglable',
			'options' => array( 0, 1 ),
		);
	}

	public function save( $id, $value ) {
		$this->strategy->update( $id, array( 'comment_approved' => $value ) );
	}

}
