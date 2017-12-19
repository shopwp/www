<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Editing_Strategy_User extends ACP_Editing_Strategy {

	public function get_rows() {
		global $wp_list_table;

		return $this->get_editable_rows( $wp_list_table->items );
	}

	/**
	 * @param WP_User|int $user
	 *
	 * @return bool|int
	 */
	public function user_has_write_permission( $user ) {
		if ( ! is_a( $user, 'WP_User' ) ) {
			$user = get_userdata( $user );
		}

		if ( ! $user ) {
			return false;
		}

		if ( ! current_user_can( 'edit_user', $user->ID ) ) {
			return false;
		}

		return $user->ID;
	}

	/**
	 * @since 4.0
	 */
	public function update( $id, $args ) {
		$args['ID'] = $id;

		return wp_update_user( $args );
	}

}
