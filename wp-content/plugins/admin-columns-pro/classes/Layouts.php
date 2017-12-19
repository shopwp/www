<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Layouts
 * @since 3.8
 */
final class ACP_Layouts {

	const LAYOUT_KEY = 'cpac_layouts';

	const USER_PREFERENCE_KEY = 'cpac_layout_table';

	/**
	 * @var ACP_Layout[]
	 */
	private $layouts;

	/**
	 * @var AC_ListScreen
	 */
	private $list_screen;

	public function __construct( AC_ListScreen $list_screen ) {
		$this->list_screen = $list_screen;
	}

	/**
	 * @return AC_ListScreen
	 */
	public function get_list_screen() {
		return $this->list_screen;
	}

	/**
	 * @return ACP_Layout|false
	 */
	public function get_current_layout() {
		return $this->get_layout_by_id( $this->list_screen->get_layout_id() );
	}

	/**
	 * @return string Layout name
	 */
	public function get_layout_name( $layout_id ) {
		$layout = $this->get_layout_by_id( $layout_id );

		if ( ! $layout ) {
			return false;
		}

		return $layout->get_name();
	}

	/**
	 * @param string $layout_id
	 *
	 * @return false|ACP_Layout
	 */
	public function get_layout_by_id( $layout_id ) {
		foreach ( $this->get_layouts() as $layout ) {
			if ( $layout->get_id() === $layout_id ) {
				return $layout;
			}
		}

		return false;
	}

	/**
	 * @return ACP_Layout[] Layouts
	 */
	public function get_layouts_for_current_user() {
		$layouts = array();

		foreach ( $this->get_layouts() as $k => $layout ) {
			if ( $layout->is_current_user_eligible() ) {
				$layouts[ $k ] = $layout;
			}
		}

		return $layouts;
	}

	/**
	 * @param string $layout_id
	 *
	 * @return string
	 */
	private function get_storage_key( $layout_id = '' ) {
		return self::LAYOUT_KEY . $this->list_screen->get_key() . $layout_id;
	}

	/**
	 * @return ACP_Layout|WP_Error Layout
	 */
	public function update( $layout_id, $args ) {

		if ( empty( $args['name'] ) ) {
			return new WP_Error( 'empty-name' );
		}

		$layout = new ACP_Layout( $args );

		if ( ! $layout->get_name() ) {
			return new WP_Error( 'empty-name' );
		}

		update_option( $this->get_storage_key( $layout_id ), (object) array(
			'id'    => $layout_id,
			'name'  => $layout->get_name(),
			'roles' => $layout->get_roles(),
			'users' => $layout->get_users(),
		) );

		// Re populate layouts
		$this->reset();

		return $this->get_layout_by_id( $layout_id );
	}

	/**
	 * @return bool True, if layout is successfully deleted. False on failure.
	 */
	public function delete( $layout_id ) {
		return delete_option( $this->get_storage_key( $layout_id ) );
	}

	/**
	 * @param string $id
	 *
	 * @return bool
	 */
	public function exists( $layout_id ) {
		return $this->get_layout_by_id( $layout_id ) ? true : false;
	}

	/**
	 * @return ACP_Layout|false
	 */
	public function get_first_layout() {
		$layouts = $this->get_layouts();

		if ( ! $layouts ) {
			return false;
		}

		return reset( $layouts );
	}

	/**
	 * @return bool|string
	 */
	public function get_first_layout_id() {
		$layout = $this->get_first_layout();

		if ( ! $layout ) {
			return false;
		}

		return $layout->get_id();
	}

	/**
	 * @return ACP_Layout|false
	 */
	public function get_first_layout_for_current_user() {
		$layouts = $this->get_layouts_for_current_user();

		if ( ! $layouts ) {
			return false;
		}

		return reset( $layouts );
	}

	/**
	 * @param array $args
	 * @param bool  $is_default
	 *
	 * @return ACP_Layout
	 */
	public function create( $args, $is_default = false ) {
		// The default layout has an empty ID, that way it stays compatible when layouts is disabled.
		$layout_id = $is_default ? '' : uniqid();

		return $this->update( $layout_id, $args );
	}

	/**
	 * @param ACP_Layout $layout
	 */
	public function register_layout( ACP_Layout $layout ) {
		$this->layouts[ $layout->get_id() ] = $layout;
	}

	/**
	 * @param string $layout_id
	 */
	private function deregister_layout( $layout_id ) {
		if ( array_key_exists( $layout_id, $this->layouts ) ) {
			unset( $this->layouts[ $layout_id ] );
		}
	}

	/**
	 * Reset
	 */
	public function reset() {
		$this->layouts = null;
	}

	/**
	 * @since 4.0
	 */
	public function set_layouts() {
		global $wpdb;

		$this->layouts = array();

		$storage_key = $this->get_storage_key();

		// Load from DB
		if ( $results = $wpdb->get_results( $wpdb->prepare( "SELECT {$wpdb->options}.option_name, {$wpdb->options}.option_value FROM {$wpdb->options} WHERE option_name LIKE %s ORDER BY option_id DESC", $storage_key . '%' ) ) ) {
			foreach ( $results as $result ) {

				// Removes incorrect layouts.
				// For example when a list screen is called "Car" and one called "Carrot", then
				// both layouts from each model are in the DB results.
				if ( strlen( $result->option_name ) !== strlen( $storage_key ) + 13 && $result->option_name != $storage_key ) {
					continue;
				}

				$layout = new ACP_Layout( maybe_unserialize( $result->option_value ) );

				$this->register_layout( $layout );
			}
		}

		// Load from API
		if ( $layouts_settings = AC()->api()->get_layouts_settings( $this->get_list_screen() ) ) {
			foreach ( $layouts_settings as $settings ) {
				$layout = new ACP_Layout( $settings );

				$this->deregister_layout( $layout->get_id() );
				$this->register_layout( $layout );
			}
		}
	}

	/**
	 * @param string $list_screen_key
	 *
	 * @return array|ACP_Layout[]
	 */
	public function get_layouts() {
		if ( null === $this->layouts ) {
			$this->set_layouts();
		}

		return $this->layouts;
	}

	/**
	 * @return false|ACP_Layout
	 */
	public function get_user_preference() {
		$layout_id = ac_helper()->user->get_meta_site( self::USER_PREFERENCE_KEY . $this->list_screen->get_key(), true );

		$layouts = $this->get_layouts_for_current_user();

		if ( ! isset( $layouts[ $layout_id ] ) ) {
			return false;
		}

		return $layouts[ $layout_id ];
	}

	/**
	 * @param ACP_Layout $layout
	 */
	public function set_user_preference( $layout ) {
		ac_helper()->user->update_meta_site( self::USER_PREFERENCE_KEY . $this->list_screen->get_key(), $layout->get_id() );
	}

	/**
	 * Delete all layouts for current listscreen
	 */
	public function delete_all() {
		global $wpdb;

		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s", $wpdb->esc_like( $this->get_storage_key() ) . '%' ) );
	}

}
