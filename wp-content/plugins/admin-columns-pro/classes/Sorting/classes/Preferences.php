<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Manage user preferences for sorting
 *
 */
class ACP_Sorting_Preferences {

	const KEY_PREFIX = 'ac_sortedby_';

	/**
	 * @var string meta key where the preferences of this user are stored
	 *
	 * @since 4.0
	 */
	protected $key;

	public function set_key( $key ) {
		$this->key = self::KEY_PREFIX . $key;

		return $this;
	}

	/**
	 * @return string|false
	 */
	public function get() {
		return ac_helper()->user->get_meta_site( $this->key, true );
	}

	public function update( $orderby, $order = null ) {
		$preference = array(
			'orderby' => $orderby,
			'order'   => $order === 'desc' ? 'desc' : 'asc',
		);

		ac_helper()->user->update_meta_site( $this->key, $preference, $this->get() );

		return $this;
	}

	public function delete() {
		return ac_helper()->user->delete_meta_site( $this->key );
	}

}
