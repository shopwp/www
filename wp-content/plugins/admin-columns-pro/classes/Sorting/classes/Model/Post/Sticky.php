<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Sorting_Model_Post_Sticky extends ACP_Sorting_Model {

	public function __construct( AC_Column $column ) {
		parent::__construct( $column );

		$this->set_data_type( 'numeric' );
	}

	public function get_sorting_vars() {
		$sticky_ids = (array) get_option( 'sticky_posts' );
		$ids = $this->strategy->get_results();
		$matched = array();

		foreach ( $sticky_ids as $k => $sticky_id ) {
			$index = array_search( $sticky_id, $ids );

			if ( false !== $index ) {
				unset( $ids[ $index ] );
				$ids[] = $matched[] = $sticky_id;
			}
		}

		if ( ! acp_sorting()->show_all_results() ) {
			$ids = $matched;
		}

		if ( $this->get_order() === 'ASC' ) {
			$ids = array_reverse( $ids );
		}

		return array(
			'ids' => $ids,
		);
	}

}
