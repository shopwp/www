<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Column_NetworkSite_Plugins extends AC_Column {

	public function __construct() {
		$this->set_type( 'column-msite_plugins' );
		$this->set_label( __( 'Plugins' ) );
	}

	public function get_option_name() {
		return 'active_plugins';
	}

	public function get_raw_value( $blog_id ) {
		$active_plugins = array();

		$plugins = get_plugins();

		if ( $site_plugins = ac_helper()->network->get_site_option( $blog_id, 'active_plugins' ) ) {
			$site_plugins = unserialize( $site_plugins );

			foreach ( $site_plugins as $basename ) {
				if ( isset( $plugins[ $basename ] ) ) {
					$active_plugins[ $basename ] = $plugins[ $basename ]['Name'];
				}
			}
		}

		return $active_plugins;
	}

	public function register_settings() {
		$this->add_setting( new ACP_Settings_Column_NetworkSite_PluginsInclude( $this ) );
		$this->add_setting( new ACP_Settings_Column_NetworkSite_Plugins( $this ) );
	}

}
