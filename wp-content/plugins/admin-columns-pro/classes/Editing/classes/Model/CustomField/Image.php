<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Editing_Model_CustomField_Image extends ACP_Editing_Model_CustomField {

	public function get_view_settings() {
		$data = parent::get_view_settings();
		$data['type'] = 'media';
		$data['attachment']['library']['type'] = 'image';

		return $data;
	}
}
