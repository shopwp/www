<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Filtering_Model_CustomField_Checkmark extends ACP_Filtering_Model_CustomField {

	public function get_filtering_data() {
		$data = array(
			'options' => array(
				'1' => __( 'True', 'codepress-admin-columns' ),
				'0' => __( 'False', 'codepress-admin-columns' ),
			),
		);

		return $data;
	}

	public function get_filtering_vars( $vars ) {
		if ( 1 == $this->get_filter_value() ) {
			$vars['meta_query'][] = array(
				'key'   => $this->column->get_meta_key(),
				'value' => array( '1', 'yes', 'true', 'on' ),
			);
		}

		if ( 0 == $this->get_filter_value() ) {

			$vars['meta_query'][] = array(
				'relation' => 'OR',
				array(
					'key'     => $this->column->get_meta_key(),
					'compare' => 'NOT EXISTS',
				),
				array(
					'key'   => $this->column->get_meta_key(),
					'value' => array( '0', 'no', 'false', 'off', '' ),
				),
			);
		}

		return $this->get_filtering_vars_empty_nonempty( $vars );
	}

}
