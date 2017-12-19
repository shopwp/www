<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Editing_Model_Post_FeaturedImage extends ACP_Editing_Model {

	public function get_view_settings() {
		return array(
			'type'         => 'media',
			'attachment'   => array(
				'library' => array(
					'type' => 'image',
				),
			),
			'clear_button' => true,
		);
	}

	public function save( $id, $value ) {
		if ( $value ) {
			set_post_thumbnail( $id, $value );
		}
		else {
			delete_post_thumbnail( $id );
		}
	}

}
