<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Column for plugin WordPress Related Posts
 *
 * Plugin: https://wordpress.org/plugins/wordpress-23-related-posts-plugin/
 * Plugin Author: Barry Kooij
 *
 * Class ACP_Column_Post_RelatedPosts
 */
class ACP_ThirdParty_RelatedPosts_Column extends AC_Column
	implements ACP_Column_SortingInterface, ACP_Column_EditingInterface {

	public function __construct() {
		$this->set_type( 'column-related-posts' );
		$this->set_label( __( 'Related Posts', 'codepress-admin-columns' ) );
		$this->set_group( 'related-posts' );
	}

	public function sorting() {
		return new ACP_ThirdParty_RelatedPosts_Sorting( $this );
	}

	public function editing() {
		return new ACP_ThirdParty_RelatedPosts_Editing( $this );
	}

	// Display
	public function get_value( $post_id ) {
		$titles = array();

		if ( $related_post_ids = $this->get_related_ids( $post_id ) ) {
			foreach ( $related_post_ids as $id ) {
				$post = get_post( $id );

				if ( $post->post_title ) {
					$titles[] = ac_helper()->html->link( get_edit_post_link( $post ), $post->post_title );
				}
			}
		}

		return implode( ' | ', $titles );
	}

	public function get_raw_value( $post_id ) {
		return $this->get_related_ids( $post_id );
	}

	public function is_valid() {
		if ( ! class_exists( 'RP4WP_Post_Type_Manager' ) ) {
			return false;
		}

		$pt_manager = new RP4WP_Post_Type_Manager();

		return $pt_manager->is_post_type_installed( $this->get_post_type() );
	}

	// Helper
	private function get_related_ids( $post_id ) {
		if ( ! class_exists( 'RP4WP_Post_Link_Manager' ) ) {
			return false;
		}

		$pl_manager = new RP4WP_Post_Link_Manager();
		$related_posts = $pl_manager->get_children( $post_id, array( 'posts_per_page' => -1 ) );

		if ( empty( $related_posts ) ) {
			return false;
		}

		return array_values( array_unique( array_filter( (array) wp_list_pluck( $related_posts, 'ID' ) ) ) );
	}

}
