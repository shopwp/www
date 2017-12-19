<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Sorting_Model_Value extends ACP_Sorting_Model {

	public function get_sorting_vars() {
		$ids = array();

		foreach ( $this->strategy->get_results() as $id ) {
			$ids[ $id ] = strip_tags( $this->column->get_value( $id ) );
		}

		return array(
			'ids' => $this->sort( $ids ),
		);
	}

}
