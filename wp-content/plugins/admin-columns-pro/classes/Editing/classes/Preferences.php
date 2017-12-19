<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Manage user preferences for editing
 *
 */
class ACP_Editing_Preferences {

	/**
	 * @var string meta key where the preferences of this user are stored
	 *
	 * @since 4.0
	 */
	protected $key;

	public function set_key( $key ) {
		$this->key = 'cacie_editability_state' . $key;

		return $this;
	}

	public function get() {
		$is_active = '1' === ac_helper()->user->get_meta_site( $this->key, true );

		/**
		 * Filters the default state of editability of cells on overview pages
		 *
		 * @since 4.0
		 *
		 * @param bool $is_active Whether the default state is active (true) or inactive (false)
		 * @param string $key Listscreen key
		 */
		$is_active = apply_filters( 'acp/editing/preference/is_active', $is_active, $this->key );

		return $is_active;
	}

	public function update( $is_enabled = false ) {
		ac_helper()->user->update_meta_site( $this->key, $is_enabled ? '1' : '0' );
	}

	public function delete() {
		return ac_helper()->user->delete_meta_site( $this->key );
	}

}
