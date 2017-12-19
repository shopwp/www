<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 2.0.0
 */
class ACP_Column_Taxonomy_ID extends AC_Column {

	public function __construct() {
		$this->set_type( 'column-termid' );
		$this->set_label( __( 'ID', 'codepress-admin-columns' ) );
	}

	public function get_value( $term_id ) {
		return $this->get_raw_value( $term_id );
	}

	public function get_raw_value( $term_id ) {
		return $term_id;
	}

}
