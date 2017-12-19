<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Editing_Model_CustomField_Checkmark extends ACP_Editing_Model_CustomField {

	public function get_view_settings() {
		return array(
			'type'    => 'togglable',
			'options' => array( '0', '1' ),
		);
	}

}
