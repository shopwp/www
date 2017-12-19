<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Filtering_Model_Media_Author extends ACP_Filtering_Model {

	public function get_filtering_vars( $vars ) {
		$vars['author'] = $this->get_filter_value();

		return $vars;
	}

	public function get_filtering_data() {
		$data = array();
		if ( $values = $this->strategy->get_values_by_db_field( 'post_author' ) ) {
			foreach ( $values as $value ) {
				$user = get_user_by( 'id', $value );
				$data['options'][ $value ] = $user->display_name;
			}
		}

		return $data;
	}

}
