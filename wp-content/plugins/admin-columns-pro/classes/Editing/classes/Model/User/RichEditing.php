<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Editing_Model_User_RichEditing extends ACP_Editing_Model {

	public function get_view_settings() {
		return array(
			'type'    => 'togglable',
			'options' => array( 'true', 'false' ),
		);
	}

	public function save( $id, $value ) {
		update_user_meta( $id, 'rich_editing', $value );
	}

}
