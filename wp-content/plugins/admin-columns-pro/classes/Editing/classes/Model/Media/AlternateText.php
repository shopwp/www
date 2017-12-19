<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Editing_Model_Media_AlternateText extends ACP_Editing_Model {

	public function get_edit_value( $id ) {
		if ( ! wp_attachment_is_image( $id ) ) {
			return null;
		}

		return parent::get_edit_value( $id );
	}

	public function save( $id, $value ) {
		update_metadata( 'post', $id, '_wp_attachment_image_alt', $value );
	}

}
