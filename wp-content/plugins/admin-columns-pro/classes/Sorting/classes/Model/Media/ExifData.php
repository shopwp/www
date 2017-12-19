<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Sorting_Model_Media_ExifData extends ACP_Sorting_Model_Media_Meta {

	public function get_sorting_vars() {
		$exif_key = false;

		if ( $setting = $this->column->get_setting( 'exif_data' ) ) {
			$exif_key = $setting->get_value();
		}

		$meta_values = $this->get_meta_values();

		foreach ( $meta_values as $id => $meta_value ) {
			$meta_values[ $id ] = ! empty( $meta_value ) && isset( $meta_value['image_meta'][ $exif_key ] ) ? $meta_value['image_meta'][ $exif_key ] : '';
		}

		if ( ! acp_sorting()->show_all_results() ) {
			$meta_values = array_filter( $meta_values );
		}

		return array(
			'ids' => $this->sort( $meta_values ),
		);
	}

}
