<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Sorting_Model_Post_EstimateReadingTime extends ACP_Sorting_Model_Value {

	public function __construct( $column ) {
		parent::__construct( $column );

		$this->set_data_type( 'numeric' );
	}

	public function get_sorting_vars() {
		$ids = array();

		foreach ( $this->strategy->get_results() as $id ) {
			$ids[ $id ] = ac_helper()->string->word_count( $this->column->get_raw_value( $id ) );
		}

		return array(
			'ids' => $this->sort( $ids ),
		);
	}

}
