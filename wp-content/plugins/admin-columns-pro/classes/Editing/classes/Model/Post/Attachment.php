<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Editing_Model_Post_Attachment extends ACP_Editing_Model {

	public function get_view_settings() {
		return array(
			'type'         => 'media',
			'attachment'   => array(
				'disable_select_current' => true,
			),
			'multiple'     => true,
			'store_values' => true,
		);
	}

	/**
	 * @param int          $id
	 * @param string|array $value
	 */
	public function save( $id, $value ) {

		$attachment_ids = get_posts( array(
			'post_type'      => 'attachment',
			'post_parent'    => $id,
			'posts_per_page' => -1,
			'fields'         => 'ids',
		) );

		// Detach
		if ( $attachment_ids ) {
			foreach ( $attachment_ids as $attachment_id ) {
				wp_update_post( array( 'ID' => $attachment_id, 'post_parent' => '' ) );
			}
		}

		// Attach
		if ( ! empty( $value ) ) {
			foreach ( $value as $attachment_id ) {
				wp_update_post( array( 'ID' => $attachment_id, 'post_parent' => $id ) );
			}
		}
	}

}
