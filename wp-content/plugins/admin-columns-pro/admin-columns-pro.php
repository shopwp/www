<?php
/*
Plugin Name: Admin Columns Pro
Version: 4.0.5
Description: Customize columns on the administration screens for post(types), users and other content. Filter and sort content, and edit posts directly from the posts overview. All via an intuitive, easy-to-use drag-and-drop interface.
Author: AdminColumns.com
Author URI: https://www.admincolumns.com
Plugin URI: https://www.admincolumns.com
Text Domain: codepress-admin-columns
Domain Path: /languages/
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'ACP_FILE', __FILE__ );

// Only run plugin in the admin interface
if ( ! is_admin() ) {
	return false;
}

/**
 * Loads Admin Columns and Admin Columns Pro
 *
 * @since 3.0.6
 */
final class ACP_Full {

	/**
	 * @since 4.0
	 */
	private static $_instance = null;

	/**
	 * @since 4.0
	 * @return $this
	 */
	public static function instance() {
		if ( null === self::$_instance ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	private function __construct() {

		// Can not be auto loaded this early
		require_once plugin_dir_path( __FILE__ ) . 'classes/PluginActions.php';

		// Only load Admin Columns if it hasn't been loaded already (in which case it is automatically deactivated by maybe_deactivate_admincolumns())
		if ( ! $this->maybe_deactivate_admincolumns() ) {

			require_once dirname( __FILE__ ) . '/codepress-admin-columns/codepress-admin-columns.php';
			require_once dirname( __FILE__ ) . '/acp.php';

			// Non compatible add-ons will be deactivated
			$this->deactivate_incompatible_addons();

			// Set capabilities
			register_activation_hook( __FILE__, array( AC(), 'set_capabilities' ) );
		}
	}

	/**
	 * Disable the Admin Columns base plugin if it is active
	 *
	 * @since 3.0
	 *
	 * @return bool Whether the base plugin was deactivated
	 */
	public function maybe_deactivate_admincolumns() {
		$deactivated = false;

		$actions = new ACP_PluginActions( 'codepress-admin-columns/codepress-admin-columns.php' );
		if ( $actions->deactivate() ) {
			$deactivated = true;
		}

		$actions = new ACP_PluginActions( 'cac-addon-pro/cac-addon-pro.php' );
		if ( $actions->deactivate() ) {
			$deactivated = true;
		}

		return $deactivated;
	}

	/**
	 * Non compatible add-ons will be deactivated
	 */
	public function deactivate_incompatible_addons() {
		$this->deactivate_incompatible_plugin( 'cac-addon-woocommerce/cac-addon-woocommerce.php', '2.0' );
		$this->deactivate_incompatible_plugin( 'cac-addon-acf/cac-addon-acf.php', '2.0' );
	}

	/**
	 * @param string $file
	 * @param string $required_version
	 */
	private function deactivate_incompatible_plugin( $file, $version ) {
		$plugin = new ACP_PluginActions( $file );

		$plugin->set_required_version( $version );

		if ( ! $plugin->is_compatible() ) {
			$plugin->deactivate();
			$plugin->notice();
		}
	}

}

ACP_Full::instance();