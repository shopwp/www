<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Filtering_Model_Post_Roles extends ACP_Filtering_Model {

	public function get_filtering_vars( $vars ) {
		$vars['author'] = implode( ',', get_users( array( 'role' => $this->get_filter_value(), 'fields' => 'id' ) ) );

		return $vars;
	}

	public function get_filtering_data( ) {
		$data = array(
			'options' => ac_helper()->user->get_roles()
		);

		return $data;
	}

}
