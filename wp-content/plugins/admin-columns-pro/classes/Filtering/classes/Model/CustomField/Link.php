<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Filtering_Model_CustomField_Link extends ACP_Filtering_Model_CustomField {

	public function get_filtering_data() {
		$options = array();

		foreach ( $this->get_meta_values() as $value ) {
			$options[ $value ] = $value;
		}

		return array(
			'options'      => $options,
			'empty_option' => true,
		);
	}

}
