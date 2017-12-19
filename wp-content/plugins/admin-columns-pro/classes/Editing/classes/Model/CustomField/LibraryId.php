<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Editing_Model_CustomField_LibraryId extends ACP_Editing_Model_CustomField {

	public function get_view_settings() {
		return array(
			'type'         => 'attachment',
			'clear_button' => true,
		);
	}

}
