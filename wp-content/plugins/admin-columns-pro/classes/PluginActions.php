<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 4.0
 */
class ACP_PluginActions {

	/**
	 * @var string Plugin basename
	 */
	private $plugin;

	/**
	 * @var string Plugin version
	 */
	private $required_version;

	public function __construct( $plugin ) {
		$this->plugin = $plugin;
	}

	/**
	 * Plugin File
	 *
	 * @return string
	 */
	private function get_file() {
		return trailingslashit( WP_PLUGIN_DIR ) . $this->plugin;
	}

	/**
	 * @param string $version
	 */
	public function set_required_version( $version ) {
		$this->required_version = $version;
	}

	/**
	 * Deactivate Plugin
	 *
	 * @return bool
	 */
	public function deactivate() {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';

		if ( is_plugin_active( $this->plugin ) ) {
			deactivate_plugins( $this->plugin );

			return true;
		}

		return false;
	}

	public function notice() {
		add_action( 'after_plugin_row_' . $this->plugin, array( $this, 'display_notice' ) );
	}

	public function display_notice() {
		if ( ! class_exists( 'AC_Notice_Plugin' ) ) {
			return;
		}

		$message = sprintf( __( 'This add-on is no longer compatible with the current version of %s.', 'codepress-admin-columns' ), __( 'Admin Columns Pro', 'codepress-admin-columns' ) ) . ' ' . sprintf( __( 'Add-on should be at least version %s.', 'codepress-admin-columns' ), $this->required_version );

		$notice = new AC_Notice_Plugin( $this->plugin );
		$notice->set_message( $message )
		       ->set_type( 'warning' )
		       ->display_notice();
	}

	/**
	 * Lesser then
	 *
	 * @param $version
	 *
	 * @return bool
	 */
	public function is_compatible() {
		if ( file_exists( $this->get_file() ) && function_exists( 'AC' ) ) {
			$current_version = AC()->get_plugin_version( $this->get_file() );
			if ( $current_version && version_compare( $current_version, $this->required_version, '<' ) ) {
				return false;
			}
		}

		return true;
	}

}
