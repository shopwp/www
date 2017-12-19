<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Filtering_Model_Comment_Type extends ACP_Filtering_Model {

	public function get_filtering_vars( $vars ) {
		$vars['type'] = $this->get_filter_value();

		return $vars;
	}

	public function get_filtering_data() {
		$data = array();
		foreach ( $this->strategy->get_values_by_db_field( 'comment_type' ) as $_value ) {
			$data['options'][ $_value ] = $_value;
		}

		return $data;
	}

}
