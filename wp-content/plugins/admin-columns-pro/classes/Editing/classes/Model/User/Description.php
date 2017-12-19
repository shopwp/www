<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Editing_Model_User_Description extends ACP_Editing_Model {

	public function get_view_settings() {
		return array(
			'type' => 'textarea',
		);
	}

	public function save( $id, $value ) {
		update_user_meta( $id, 'description', $value );
	}

}
