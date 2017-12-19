<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Filtering_Model_Post_FeaturedImage extends ACP_Filtering_Model_Meta {

	public function get_filtering_data() {
		$options = array();

		foreach ( $this->get_meta_values() as $media_id ) {
			$options[ $media_id ] = ac_helper()->image->get_file_name( $media_id );
		}

		return array(
			'empty_option' => $this->get_empty_labels(),
			'options'      => $options,
		);
	}

}
