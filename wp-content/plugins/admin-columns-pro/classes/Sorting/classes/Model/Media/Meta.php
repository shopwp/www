<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class ACP_Sorting_Model_Media_Meta extends ACP_Sorting_Model_Meta {

	protected function get_meta_values() {
		$ids = $this->strategy->get_results( parent::get_sorting_vars() );

		$query = new AC_Meta_Query( $this->column );
		$query->select( 'id, meta_value' )
		      ->where_in( $ids )
		      ->order_by( 'meta_value', $this->get_order() );

		if ( acp_sorting()->show_all_results() ) {
			$query->left_join();
		}

		$values = array();

		foreach ( $query->get() as $result ) {
			if ( $this->column->is_serialized() ) {
				$result->meta_value = unserialize( $result->meta_value );
			}

			$values[ $result->id ] = $result->meta_value;
		}

		return $values;
	}

	/**
	 * Return meta value based on values _wp_attachment_metadata
	 *
	 * @param array $meta
	 * @param string $key
	 *
	 * @return false|string
	 */
	protected function get_meta_value( $meta, $key ) {
		if ( empty( $meta ) || ! is_array( $meta ) || ! isset( $meta_value[ $key ] ) ) {
			return false;
		}

		return $meta_value[ $key ];
	}

}
