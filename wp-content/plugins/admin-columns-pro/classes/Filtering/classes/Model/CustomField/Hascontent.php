<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Filtering_Model_CustomField_Hascontent extends ACP_Filtering_Model_CustomField {

	public function get_filtering_data() {
		return array(
			'empty_option' => $this->get_empty_labels( __( 'Content', 'codepress-admin-columns' ) )
		);
	}

}
