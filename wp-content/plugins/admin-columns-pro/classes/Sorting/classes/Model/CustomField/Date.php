<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Sorting_Model_CustomField_Date extends ACP_Sorting_Model_Meta {

	public function __construct( AC_Column_CustomField $column ) {
		parent::__construct( $column );

		$this->set_data_type( 'numeric' );
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
			$timestamp = ac_helper()->date->strtotime( maybe_unserialize( $result->meta_value ) );

			if ( $timestamp ) {
				$values[ $result->id ] = $timestamp;
			}
		}

		return array(
			'ids' => $this->sort( $values ),
		);
	}

}
