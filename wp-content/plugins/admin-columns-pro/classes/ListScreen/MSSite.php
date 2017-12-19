<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_ListScreen_MSSite extends AC_ListScreen {

	public function __construct() {
		$this->set_label( __( 'Network Sites' ) );
		$this->set_singular_label( __( 'Network Site' ) );
		$this->set_key( 'wp-ms_sites' );
		$this->set_screen_id( 'sites-network' );
		$this->set_screen_base( 'sites-network' );
		$this->set_meta_type( 'site' );
		$this->set_group( 'network' );
		$this->set_network_only( true );

		/* @see WP_MS_Sites_List_Table */
		$this->set_list_table_class( 'WP_MS_Sites_List_Table' );
	}

	/**
	 * @since 4.0
	 * @return WP_Site Site object
	 */
	protected function get_object_by_id( $site_id ) {
		return get_site( $site_id );
	}

	public function set_manage_value_callback() {
		add_action( "manage_sites_custom_column", array( $this, 'manage_value' ), 100, 2 );
	}

	/**
	 * @return string
	 */
	protected function get_admin_url() {
		return network_admin_url( 'sites.php' );
	}

	/**
	 * @since 2.4.7
	 */
	public function manage_value( $column_name, $blog_id ) {
		echo $this->get_display_value_by_column_name( $column_name, $blog_id, null );
	}

	public function get_single_row( $site_id ) {
		return false;
	}

	/**
	 * Register custom columns
	 */
	protected function register_column_types() {
		$this->register_column_types_from_dir( ACP()->get_plugin_dir() . 'classes/Column/NetworkSite', ACP::CLASS_PREFIX );
	}

}
