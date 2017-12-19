<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Sorting_Model_Media_AvailableSizes extends ACP_Sorting_Model_Media_Meta {

	public function get_sorting_vars() {
		$sizes = (array) get_intermediate_image_sizes();

		$meta_values = $this->get_meta_values();

		foreach ( $meta_values as $id => $meta_value ) {
			$meta_values[ $id ] = ! empty( $meta_value ) && ! empty( $meta_value['sizes'] ) && is_array( $meta_value['sizes'] )
				? count( array_intersect( $sizes, array_keys( $meta_value['sizes'] ) ) )
				: '';
		}

		if ( ! acp_sorting()->show_all_results() ) {
			$meta_values = array_filter( $meta_values );
		}

		return array(
			'ids' => $this->sort( $meta_values ),
		);
	}
}
