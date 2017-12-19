<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Editing_Model_CustomField_Numeric extends ACP_Editing_Model_CustomField {

	/**
	 * @return array
	 */
	public function get_view_settings() {
		return array(
			'type' => 'number',
		);
	}

}
