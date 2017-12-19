<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Editing_Model_CustomField_UserById extends ACP_Editing_Model_CustomField {

	public function get_edit_value( $id ) {
		$ids = $this->column->get_raw_value( $id );

		if ( empty( $ids ) ) {
			return false;
		}

		$value = array();

		foreach ( (array) $ids as $id ) {
			$value[ $id ] = ac_helper()->user->get_display_name( $id );
		}

		return $value;
	}

	public function get_view_settings() {
		return array(
			'type'          => 'select2_dropdown',
			'ajax_populate' => true,
		);
	}

	public function get_ajax_options( $request ) {
		return acp_editing_helper()->get_users_list( array( 'search' => $request['search'], 'paged' => $request['paged'] ) );
	}

}
