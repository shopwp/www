<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Editing_Model_Taxonomy_Name extends ACP_Editing_Model {

	public function get_view_settings() {
		return array(
			'type'         => 'text',
			'js'           => array(
				'selector' => 'a.row-title',
			),
			'display_ajax' => false,
		);
	}

	public function get_edit_value( $id ) {
		return ac_helper()->taxonomy->get_term_field( 'name', $id, $this->column->get_taxonomy() );
	}

	public function save( $id, $value ) {
		$this->strategy->update( $id, array( 'name' => $value ) );
	}

}
