<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_ThirdParty_RelatedPosts_Editing extends ACP_Editing_Model {

	public function get_ajax_options( $request ) {
		if ( ! class_exists( 'RP4WP_Post_Type_Manager', false ) ) {
			return array();
		}

		$pt_manager = new RP4WP_Post_Type_Manager();
		$post_types = $pt_manager->get_installed_post_type( $this->column->get_post_type() );

		return acp_editing_helper()->get_posts_list( array( 's' => $request['search'], 'post_type' => $post_types, 'paged' => $request['paged'] ) );
	}

	public function get_view_settings() {
		$settings = array(
			'type'            => 'select2_dropdown',
			'ajax_populate'   => true,
			'multiple'        => true,
			'formatted_value' => 'post',
		);

		return $settings;
	}

	public function save( $id, $values ) {
		if ( ! class_exists( 'RP4WP_Post_Link_Manager' ) ) {
			return new WP_Error( 'related-posts-error', 'Class RP4WP_Post_Link_Manager not found.' );
		}

		// remove any false booleans
		$values = array_filter( array_map( 'intval', (array) $values ) );

		$post_link_manager = new RP4WP_Post_Link_Manager();
		$current_related_ids = (array) $this->column->get_raw_value( $id );

		if ( $removed_ids = array_diff( $current_related_ids, $values ) ) {
			foreach ( $removed_ids as $removed_id ) {
				$post_link_manager->delete( $removed_id );
			}
		}
		if ( $added_ids = array_diff( $values, $current_related_ids ) ) {
			foreach ( $added_ids as $added_id ) {
				$post_link_manager->add( $id, $added_id, get_post_type( $id ), false, true );
			}
		}

		return true;
	}

}
