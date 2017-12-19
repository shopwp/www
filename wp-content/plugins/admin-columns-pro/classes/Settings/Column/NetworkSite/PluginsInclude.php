<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Settings_Column_NetworkSite_PluginsInclude extends AC_Settings_Column
	implements AC_Settings_FormatValueInterface {

	/**
	 * @var bool
	 */
	private $include_network;

	protected function define_options() {
		return array( 'include_network' );
	}

	public function create_view() {

		$options = array(
			'include_network' => __( 'Include network plugins', 'codepress-admin-columns' ),
		);

		$view = new AC_View( array(
			'label'   => __( 'Plugins' ),
			'setting' => $this->create_element( 'checkbox' )->set_options( $options ),
		) );

		return $view;
	}

	/**
	 * @return string
	 */
	public function get_include_network() {
		return $this->include_network;
	}

	/**
	 * @param string $include_network
	 *
	 * @return bool
	 */
	public function set_include_network( $include_network ) {
		$this->include_network = $include_network;

		return true;
	}

	public function format( $value, $original_value ) {
		if ( $this->get_include_network() ) {
			foreach ( get_plugins() as $basename => $plugin ) {
				if ( is_plugin_active_for_network( $basename ) ) {
					$value[ $basename ] = $plugin['Name'];
				}
			}
		}

		return $value;
	}

}
