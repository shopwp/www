<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Sorting_Model_User_Roles extends ACP_Sorting_Model_Meta {

	public function get_sorting_vars() {
		global $wp_roles;

		$values = array();

		add_action( 'pre_user_query', array( $this, 'pre_user_query_callback' ) );

		$results = $this->strategy->get_results( parent::get_sorting_vars() );

		// cache translated roles
		$translated = array();

		foreach ( $results as $row ) {
			$roles = maybe_unserialize( $row->meta_value );

			if ( ! is_array( $roles ) ) {
				continue;
			}

			$role = false;

			foreach ( $roles as $maybe_role => $active ) {
				if ( $active && isset( $wp_roles->roles[ $maybe_role ] ) ) {
					if ( ! isset( $translated[ $maybe_role ] ) ) {
						$translated[ $maybe_role ] = translate_user_role( $wp_roles->roles[ $maybe_role ]['name'] );
					}

					$role = $translated[ $maybe_role ];

					break; // single role per user
				}
			}

			$values[ $row->ID ] = $role;
		}

		return array(
			'ids' => $this->sort( $values ),
		);
	}

	public function pre_user_query_callback( WP_User_Query $query ) {
		global $wpdb;

		$query->query_fields .= ", $wpdb->usermeta.meta_value";
		$query->query_vars['fields'] = array();

		remove_action( 'pre_user_query', array( $this, __FUNCTION__ ) );
	}

}
