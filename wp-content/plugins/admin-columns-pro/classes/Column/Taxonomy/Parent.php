<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Column_Taxonomy_Parent extends AC_Column
	implements ACP_Column_EditingInterface {

	public function __construct() {
		$this->set_type( 'column-term_parent' );
		$this->set_label( __( 'Parent', 'codepress-admin-columns' ) );
	}

	public function get_raw_value( $term_id ) {
		$term = get_term( $term_id, $this->get_taxonomy() );

		return $term->parent;
	}

	public function editing() {
		return new ACP_Editing_Model_Taxonomy_Parent( $this );
	}

	public function is_valid() {
		return is_taxonomy_hierarchical( $this->get_taxonomy() );
	}

	public function register_settings() {
		$this->add_setting( new AC_Settings_Column_Term( $this ) );
	}

}
