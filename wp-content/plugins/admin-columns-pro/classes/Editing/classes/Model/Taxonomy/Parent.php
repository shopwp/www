<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Editing_Model_Taxonomy_Parent extends ACP_Editing_Model {

	public function get_edit_value( $id ) {
		$term = $this->get_term( $id );

		if ( ! $term || 0 === $term->parent ) {
			return false;
		}

		$parent = $this->get_term( $term->parent );

		if ( ! $parent ) {
			return false;
		}

		return array(
			$parent->term_id => $parent->name,
		);
	}

	public function get_ajax_options( $request ) {

		// no pagination
		if ( 1 !== $request['paged'] ) {
			return array();
		}

		$args = array(
			'number'       => '', // show all
			'hide_empty'   => false,
			'search'       => $request['search'],
			'fields'       => 'id=>name',
			'exclude_tree' => $request['object_id'],
		);

		return get_terms( $this->column->get_taxonomy(), $args );
	}

	public function get_view_settings() {
		return array(
			'type'          => 'select2_dropdown',
			'ajax_populate' => true,
			'multiple'      => false,
			'clear_button'  => true,
		);
	}

	public function save( $id, $value ) {
		$this->strategy->update( $id, array( 'parent' => $value ) );
	}

	private function get_term( $id ) {
		return get_term_by( 'id', $id, $this->column->get_taxonomy() );
	}

}
