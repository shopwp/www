<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Filtering_Model_User_Role extends ACP_Filtering_Model {

	public function get_filtering_vars( $vars ) {
		$vars['role'] = $this->get_filter_value();

		return $vars;
	}

	public function get_filtering_data() {
		$data = array();

		$roles = new WP_Roles();
		foreach ( ac_helper()->user->get_ids() as $id ) {
			$u = get_userdata( $id );
			if ( ! empty( $u->roles[0] ) ) {
				$data['options'][ $u->roles[0] ] = $roles->roles[ $u->roles[0] ]['name'];
			}
		}

		return $data;
	}

}
