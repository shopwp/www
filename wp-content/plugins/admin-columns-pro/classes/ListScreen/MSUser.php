<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_ListScreen_MSUser extends AC_ListScreen_User {

	public function __construct() {
		parent::__construct();

		$this->set_label( __( 'Network Users' ) );
		$this->set_singular_label( __( 'Network User' ) );
		$this->set_key( 'wp-ms_users' );
		$this->set_screen_base( 'users-network' );
		$this->set_screen_id( 'users-network' );
		$this->set_group( 'network' );
		$this->set_network_only( true );

		/* @see WP_MS_Users_List_Table */
		$this->set_list_table_class( 'WP_MS_Users_List_Table' );
	}

	/**
	 * @since 2.0
	 * @return string Link
	 */
	public function get_screen_link() {
		return network_admin_url( 'users.php' );
	}

	/**
	 * @since 4.0
	 * @return string HTML
	 */
	public function get_single_row( $user_id ) {
		ob_start();
		parent::get_single_row( $user_id );

		return ob_get_clean();
	}

}
