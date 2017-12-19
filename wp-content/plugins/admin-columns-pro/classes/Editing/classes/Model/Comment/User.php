<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Editing_Model_Comment_User extends ACP_Editing_Model {

	public function get_view_settings() {
		return array(
			'type'               => 'select2_dropdown',
			'ajax_populate'      => true,
			'store_single_value' => true,
		);
	}

	public function get_edit_value( $id ) {
		$user = get_userdata( $this->column->get_raw_value( $id ) );

		if ( ! $user ) {
			return false;
		}

		return array(
			$user->ID => ac_helper()->user->get_display_name( $user ),
		);
	}

	public function get_ajax_options( $request ) {
		return acp_editing_helper()->get_users_list( array(
			'search' => $request['search'],
			'paged'  => $request['paged'],
		) );
	}

	public function save( $id, $value ) {
		$this->strategy->update( $id, array( 'user_id' => $value ) );
	}

}
