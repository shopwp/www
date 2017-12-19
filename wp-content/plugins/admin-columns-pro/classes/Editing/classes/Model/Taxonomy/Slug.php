<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Editing_Model_Taxonomy_Slug extends ACP_Editing_Model {

	public function get_edit_value( $id ) {
		return ac_helper()->taxonomy->get_term_field( 'slug', $id, $this->column->get_taxonomy() );
	}

	public function save( $id, $value ) {
		$this->strategy->update( $id, array( 'slug' => $value ) );
	}

}
