<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Filtering_Model_Comment_ReplyTo extends ACP_Filtering_Model {

	public function get_filtering_vars( $vars ) {
		$vars['parent'] = $this->get_filter_value();

		return $vars;
	}

	public function get_filtering_data() {
		$data = array();
		foreach ( $this->strategy->get_values_by_db_field( 'comment_parent' ) as $_value ) {
			$data['options'][ $_value ] = get_comment_author( $_value ) . ' (' . $_value . ')';
		}

		return $data;
	}

}
