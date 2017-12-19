<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Filtering_Model_Comment_User extends ACP_Filtering_Model {

	public function get_filtering_vars( $vars ) {
		$vars['user_id'] = $this->get_filter_value();

		return $vars;
	}

	public function get_filtering_data() {
		$data = array();
		foreach ( $this->strategy->get_values_by_db_field( 'user_id' ) as $_value ) {
			$data['options'][ $_value ] = ac_helper()->user->get_display_name( $_value );
		}

		return $data;
	}

}
