<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @property AC_Column_CustomField $column
 */
class ACP_Sorting_Model_CustomField extends ACP_Sorting_Model_Meta {

	public function __construct( AC_Column_CustomField $column ) {
		parent::__construct( $column );
	}

	public function get_sorting_vars() {
		$ids = $this->strategy->get_results( parent::get_sorting_vars() );

		$query = new AC_Meta_QueryColumn( $this->column );
		$query->select( 'id, meta_value' )
		      ->where_in( $ids );

		if ( acp_sorting()->show_all_results() ) {
			$query->left_join();
		}

		$values = array();

		foreach ( $query->get() as $result ) {
			$values[ $result->id ] = maybe_unserialize( $result->meta_value );
		}

		return array(
			'ids' => $this->sort( $values ),
		);
	}

}
