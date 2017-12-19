<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 2.0.0
 */
class ACP_Column_Taxonomy_Excerpt extends AC_Column
	implements ACP_Column_EditingInterface {

	public function __construct() {
		$this->set_type( 'column-excerpt' );
		$this->set_label( __( 'Excerpt', 'codepress-admin-columns' ) );
	}

	public function get_raw_value( $term_id ) {
		return ac_helper()->taxonomy->get_term_field( 'description', $term_id, $this->get_taxonomy() );
	}

	public function editing() {
		return new ACP_Editing_Model_Taxonomy_Description( $this );
	}

	public function register_settings() {
		$this->add_setting( new AC_Settings_Column_WordLimit( $this ) );
	}

}
