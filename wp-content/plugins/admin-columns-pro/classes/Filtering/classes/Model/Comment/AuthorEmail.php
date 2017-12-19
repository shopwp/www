<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Filtering_Model_Comment_AuthorEmail extends ACP_Filtering_Model {

	public function get_filtering_vars( $vars ) {
		$vars['author_email'] = $this->get_filter_value();

		return $vars;
	}

	public function get_filtering_data() {
		$data = array();
		foreach ( $this->strategy->get_values_by_db_field( 'comment_author_email' ) as $_value ) {
			$data['options'][ $_value ] = $_value;
		}

		return $data;
	}

}
