<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Editing_Model_Post_Slug extends ACP_Editing_Model {

	public function get_view_settings() {
		return array(
			'type'        => 'text',
			'placeholder' => $this->column->get_label(),
		);
	}

	public function save( $id, $value ) {
		$this->strategy->update( $id, array( 'post_name' => $value ) );
	}

}
