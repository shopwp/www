<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Editing_Model_User_Url extends ACP_Editing_Model {

	public function get_edit_value( $id ) {
		return ac_helper()->user->get_user_field( 'user_url', $id );
	}

	public function get_view_settings() {
		return array(
			'type'        => 'text',
			'placeholder' => $this->column->get_label(),
		);
	}

	public function save( $id, $value ) {
		$this->strategy->update( $id, array( 'user_url' => $value ) );
	}

}
