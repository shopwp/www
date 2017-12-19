<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class ACP_ThirdParty_RelatedPosts_Addon {

	public function __construct() {
		add_action( 'ac/column_types', array( $this, 'set_columns' ) );
		add_action( 'ac/column_groups', array( $this, 'set_groups' ) );
	}

	/**
	 * @param AC_ListScreen $list_screen
	 */
	public function set_columns( $list_screen ) {
		if ( ! function_exists( 'RP4WP' ) ) {
			return;
		}

		$list_screen->register_column_type( new ACP_ThirdParty_RelatedPosts_Column );
	}

	/**
	 * @param AC_Groups $groups
	 */
	public function set_groups( $groups ) {
		$groups->register_group( 'related-posts', __( 'Related Posts', 'codepress-admin-columns' ), 25 );
	}

}
