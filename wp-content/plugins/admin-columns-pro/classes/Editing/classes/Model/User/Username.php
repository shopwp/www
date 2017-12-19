<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Editing_Model_User_Username extends ACP_Editing_Model {

	public function get_edit_value( $id ) {
		return ac_helper()->user->get_user_field( 'user_login', $id );
	}

	public function get_view_settings() {
		return array(
			'type'         => 'text',
			'js'           => array(
				'selector' => 'strong > a',
			),
			'display_ajax' => false,
		);
	}

	/**
	 * @param int $id
	 * @param string $value
	 *
	 * @return bool|WP_Error
	 */
	public function save( $id, $value ) {
		global $wpdb;

		$value = sanitize_user( $value, true );

		if ( $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(1) FROM {$wpdb->users} WHERE user_login = %s AND ID != %d", $value, $id ) ) ) {
			return new WP_Error( 'cacie_error_username_exists', __( 'The username already exists.', 'codepress-admin-columns' ) );
		}

		$wpdb->update(
			$wpdb->users,
			array( 'user_login' => $value ),
			array( 'ID' => $id ),
			array( '%s' ),
			array( '%d' )
		);

		clean_user_cache( $id );

		return true;
	}

}
