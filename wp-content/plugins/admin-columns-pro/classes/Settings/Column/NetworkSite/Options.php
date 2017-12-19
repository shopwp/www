<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Settings_Column_NetworkSite_Options extends AC_Settings_Column_Meta {

	public function __construct( ACP_Column_NetworkSite_Options $column ) {
		parent::__construct( $column );
	}

	public function create_view() {
		$view = parent::create_view();

		$view->set( 'label', __( 'Option', 'codepress-admin-columns' ) );

		return $view;
	}

	public function get_cache_group() {
		return 'acp_network_site_options';
	}

	public function get_meta_keys() {
		global $wpdb;

		$keys = array();

		foreach ( get_sites() as $site ) {
			$table = $wpdb->get_blog_prefix( $site->blog_id ) . 'options';

			$sql = "
					SELECT {$table}.option_name, {$table}.option_value 
					FROM {$table}
					WHERE option_name NOT LIKE %s
				";

			// Exclude transients
			$values = $wpdb->get_results( $wpdb->prepare( $sql, $wpdb->esc_like( '_transient' ) . '%' ) );

			// Exclude serialized data
			foreach ( $values as $value ) {
				if ( is_serialized( $value->option_value ) ) {
					continue;
				}

				$keys[ $value->option_name ] = $value->option_name;
			}
		}

		natcasesort( $keys );

		return $keys;
	}

}
