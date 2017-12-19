<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Editing_Model_User_Role extends ACP_Editing_Model {

	public function get_edit_value( $id ) {
		$roles = ac_helper()->user->get_user_field( 'roles', $id );

		if ( ! $roles || ! is_array( $roles ) ) {
			return false;
		}

		return implode( ', ', $roles );
	}

	public function get_view_settings() {
		$options = array();
		if ( $_roles = get_editable_roles() ) {
			foreach ( $_roles as $k => $role ) {
				$options[ $k ] = translate_user_role( $role['name'] );
			}
		}

		return array(
			'type'     => 'select2_dropdown',
			'multiple' => true,
			'options'  => $options
		);
	}

	public function save( $id, $value ) {
		if ( current_user_can( 'edit_users' ) ) {
			// prevent the removal of your own admin role
			if ( current_user_can( 'administrator' ) && get_current_user_id() == $id ) {
				$value[] = 'administrator';
			}
			if ( ! empty( $value ) ) {
				$user = get_user_by( 'id', $id );
				$user->set_role( array_pop( $value ) );
				foreach ( $value as $key ) {
					$user->add_role( $key );
				}
			}
			else {
				return new WP_Error( 'empty', 'Can not be empty.', 'codepress-admin-columns' );
			}
		}

		return true;
	}

}
