<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Editing_Model_Post_Parent extends ACP_Editing_Model {

	public function get_view_settings() {
		return array(
			'type'               => 'select2_dropdown',
			'ajax_populate'      => true,
			'multiple'           => false,
			'clear_button'       => true,
			'store_single_value' => true,
		);
	}

	public function get_ajax_options( $request ) {
		return acp_editing_helper()->get_posts_list( array(
			's'         => $request['search'],
			'post_type' => $this->column->get_post_type(),
			'paged'     => $request['paged'],
		) );
	}

	public function get_edit_value( $id ) {
		$post = get_post( parent::get_edit_value( $id ) );

		if ( ! $post ) {
			return false;
		}

		return array(
			$post->ID => $post->post_title,
		);
	}

	public function save( $id, $value ) {
		$this->strategy->update( $id, array( 'post_parent' => $value ) );
	}

}
