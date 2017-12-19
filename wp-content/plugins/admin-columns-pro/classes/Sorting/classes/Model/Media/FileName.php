<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Sorting_Model_Media_FileName extends ACP_Sorting_Model_Meta {

	public function get_sorting_vars() {
		$ids = $this->strategy->get_results( parent::get_sorting_vars() );

		$query = new AC_Meta_QueryColumn( $this->column );
		$query->select( 'id, meta_value' )
		      ->where_in( $ids );

		if ( acp_sorting()->show_all_results() ) {
			$query->left_join();
		} else {
			$query->where( 'meta_value', '!=', '' );
		}

		$values = array();

		foreach ( $query->get() as $value ) {
			$values[ $value->id ] = strtolower( basename( $value->meta_value ) );
		}

		return array(
			'ids' => $this->sort( $values ),
		);
	}

}
