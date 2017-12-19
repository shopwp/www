<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Editing_Model_Post_Title extends ACP_Editing_Model {

	public function get_edit_value( $id ) {
		return get_post_field( 'post_title', $id );
	}

	public function get_view_settings() {
		return array(
			'type'         => 'text',
			'js'           => array(
				'selector' => 'a.row-title',
			),
			'display_ajax' => false,
		);
	}

	public function save( $id, $value ) {
		$this->strategy->update( $id, array( 'post_title' => $value ) );
	}

}
