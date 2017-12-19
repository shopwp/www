<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Filtering_Model_User_RichEditing extends ACP_Filtering_Model {

	public function get_filtering_vars( $vars ) {
		$vars['meta_query'][] = array(
			array(
				'key'   => 'rich_editing',
				'value' => '1' === $this->get_filter_value() ? 'true' : 'false',
			),
		);

		return $vars;
	}

	public function get_filtering_data() {
		$data = array(
			'options' => array(
				0 => __( 'No' ),
				1 => __( 'Yes' ),
			),
		);

		return $data;
	}

}
