<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Sorting_Model_CustomField_Count extends ACP_Sorting_Model_Meta {

	public function get_sorting_vars() {
		$ids = $this->strategy->get_results( parent::get_sorting_vars() );

		$query = new AC_Meta_QueryColumn( $this->column );
		$query->select( 'id' )->count( 'meta_key' )
		      ->where_in( $ids )
		      ->group_by( 'id' )
		      ->order_by( 'count, id', $this->get_order() );

		if ( acp_sorting()->show_all_results() ) {
			$query->left_join();
		}

		$values = array();

		foreach ( $query->get() as $result ) {
			$values[] = $result->id;
		}

		return array(
			'ids' => $values,
		);
	}

}
