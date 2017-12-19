<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_ThirdParty_RelatedPosts_Sorting extends ACP_Sorting_Model {

	public function get_sorting_vars() {
		$post_ids = array();
		foreach ( $this->get_strategy()->get_results() as $post_id ) {
			$related = $this->column->get_raw_value( $post_id );
			$post_ids[ $post_id ] = $related ? count( $related ) : false;
		}

		return array(
			'ids' => $this->sort( $post_ids )
		);
	}

}
