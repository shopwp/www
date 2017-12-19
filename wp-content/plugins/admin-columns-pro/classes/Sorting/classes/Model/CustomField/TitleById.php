<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Sorting_Model_CustomField_TitleById extends ACP_Sorting_Model_CustomField {

	public function get_sorting_vars() {
		$ids = array();

		foreach ( $this->strategy->get_results() as $id ) {
			// sort by the actual post_title instead of ID
			$string = ac_helper()->array->implode_recursive( ',', $this->column->get_meta_value( $id, $this->column->get_meta_key(), true ) );
			$title_ids = ac_helper()->string->string_to_array_integers( $string );

			// use first title to sort with
			$ids[ $id ] = is_array( $title_ids ) && isset( $title_ids[0] ) ? ac_helper()->post->get_raw_post_title( $title_ids[0] ) : '';
		}

		if ( ! acp_sorting()->show_all_results() ) {
			$ids = array_filter( $ids );
		}

		return array(
			'ids' => $this->sort( $ids ),
		);
	}

}
