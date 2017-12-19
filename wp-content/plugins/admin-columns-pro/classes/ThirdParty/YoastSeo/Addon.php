<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class ACP_ThirdParty_YoastSeo_Addon {

	public function __construct() {
		add_action( 'ac/column_types', array( $this, 'set_columns' ) );
		add_action( 'ac/column_groups', array( $this, 'set_groups' ) );
	}

	/**
	 * @param AC_ListScreen $list_screen
	 */
	public function set_columns( $list_screen ) {
		if ( $this->is_active() ) {
			$list_screen->register_column_types_from_dir( plugin_dir_path( __FILE__ ) . 'Column', ACP::CLASS_PREFIX );
		}
	}

	/**
	 * @param AC_Groups $groups
	 */
	public function set_groups( $groups ) {
		$groups->register_group( 'yoast-seo', __( 'Yoast SEO', 'wordpress-seo' ), 25 );
	}

	/**
	 * @return bool
	 */
	private function is_active() {
		return defined( 'WPSEO_VERSION' );
	}

}
