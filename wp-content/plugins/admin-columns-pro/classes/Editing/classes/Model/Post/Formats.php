<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Editing_Model_Post_Formats extends ACP_Editing_Model {

	public function get_view_settings() {
		return array(
			'type'    => 'select',
			'options' => get_post_format_strings(),
		);
	}

	public function save( $id, $value ) {
		set_post_format( $id, $value );
	}

}
