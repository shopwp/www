<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Sorting_Model_Post_LastModifiedAuthor extends ACP_Sorting_Model {

	public function get_sorting_vars() {
		$ids = array();

		$setting = $this->column->get_setting( 'user' );

		foreach ( $this->strategy->get_results() as $id ) {
			$ids[ $id ] = $setting->get_user_name( $this->column->get_raw_value( $id ) );
		}

		return array(
			'ids' => $this->sort( $ids ),
		);
	}

}
