<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Editing_Model_Taxonomy_Description extends ACP_Editing_Model {

	public function get_edit_value( $id ) {
		return ac_helper()->taxonomy->get_term_field( 'description', $id, $this->column->get_taxonomy() );
	}

	public function get_view_settings() {
		return array(
			'type' => 'textarea',
		);
	}

	public function save( $id, $value ) {
		$this->strategy->update( $id, array( 'description' => $value ) );
	}

}
