<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Filtering_Model_CustomField_Image extends ACP_Filtering_Model_CustomField {

	public function get_filtering_data() {
		$data = array(
			'empty_option' => true,
		);

		$values = $this->get_meta_values();

		foreach ( $values as $value ) {
			$data['options'][ $value ] = basename( wp_get_attachment_url( $value ) );
		}

		return $data;
	}

	public function register_settings() {
		$this->column->add_setting( new ACP_Filtering_Settings( $this->column ) );
	}

}
