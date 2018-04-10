<?php
/**
 * Plugin Name: WP OAuth Server
 * Plugin URI: https://wp-oauth.com
 * Version: 3.4.5
 * Description: Full OAuth 2.0 Server for WordPress. User Authorization Management Systems For WordPress. This is the Free community version of this plugin. Download the full version <a href="https://wp-oauth.com">by clicking here</a>.
 * Author: Dash10 Digital
 * Author URI: https://dash10.digital
 * Text Domain: wp-oauth
 *
 * @author  Justin Greer <justin@justin-greer.com>
 * @package WP OAuth Server
 */

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

if ( ! defined( 'WPOAUTH_FILE' ) ) {
	define( 'WPOAUTH_FILE', __FILE__ );
}

if ( ! defined( 'WPOAUTH_VERSION' ) ) {
	define( 'WPOAUTH_VERSION', '3.4.5' );
}

// localize
add_action( 'plugins_loaded', 'wo_load_textdomain', 99 );
function wo_load_textdomain() {
	load_plugin_textdomain( 'wp-oauth', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}

/**
 * 5.4 Strict Mode Temp Patch
 *
 * Since PHP 5.4, WP will through notices due to the way WP calls statically
 */
function _wo_server_register_files() {
	wp_register_style( 'wo_admin', plugins_url( '/assets/css/admin.css', __FILE__ ) );
	wp_register_script( 'wo_admin', plugins_url( '/assets/js/admin.js', __FILE__ ), array( 'jquery-ui-tabs' ) );
}

add_action( 'admin_enqueue_scripts', '_wo_server_register_files' );

require_once( dirname( __FILE__ ) . '/includes/functions.php' );
require_once( dirname( __FILE__ ) . '/wp-oauth-main.php' );
//require_once( dirname( __FILE__ ) . '/examples.php' );

/**
 * Adds/registers query vars
 *
 * @return void
 */
function _wo_server_register_query_vars() {
	_wo_server_register_rewrites();

	global $wp;
	$wp->add_query_var( 'oauth' );
}

add_action( 'init', '_wo_server_register_query_vars' );

/**
 * Registers rewrites for OAuth2 Server
 *
 * - authorize
 * - token
 * - .well-known
 * - wpoauthincludes
 *
 * @return void
 */
function _wo_server_register_rewrites() {
	add_rewrite_rule( '^oauth/(.+)', 'index.php?oauth=$matches[1]', 'top' );
}

/**
 * [template_redirect_intercept description]
 *
 * @return [type] [description]
 */
function _wo_server_template_redirect_intercept( $template ) {
	global $wp_query;

	if ( $wp_query->get( 'oauth' ) || $wp_query->get( 'well-known' ) ) {
		require_once dirname( __FILE__ ) . '/library/class-wo-api.php';
		exit;
	}

	return $template;
}

add_filter( 'template_include', '_wo_server_template_redirect_intercept', 100 );

/**
 * OAuth2 Server Activation
 *
 * @param  [type] $network_wide [description]
 *
 * @return [type]               [description]
 */
function _wo_server_activation( $network_wide ) {
	if ( function_exists( 'is_multisite' ) && is_multisite() && $network_wide ) {
		$mu_blogs = wp_get_sites();
		foreach ( $mu_blogs as $mu_blog ) {
			switch_to_blog( $mu_blog['blog_id'] );
			_wo_server_register_rewrites();
			flush_rewrite_rules();
		}
		restore_current_blog();
	} else {
		_wo_server_register_rewrites();
		flush_rewrite_rules();
	}
}

register_activation_hook( __FILE__, '_wo_server_activation' );

/**
 * OAuth Server Deactivation
 *
 * @param  [type] $network_wide [description]
 *
 * @return [type]               [description]
 */
function _wo_server_deactivation( $network_wide ) {
	if ( function_exists( 'is_multisite' ) && is_multisite() && $network_wide ) {
		$mu_blogs = wp_get_sites();
		foreach ( $mu_blogs as $mu_blog ) {
			switch_to_blog( $mu_blog['blog_id'] );
			flush_rewrite_rules();
		}
		restore_current_blog();
	} else {
		flush_rewrite_rules();
	}
}

register_deactivation_hook( __FILE__, '_wo_server_deactivation' );

global $wp_version;
if ( $wp_version <= 4.3 ) {
	function wo_incompatibility_with_wp_version() {
		?>
        <div class="notice notice-error">
            <p><?php _e( 'WP OAuth Server requires that WordPress 4.4 or greater be used. Update to the latest WordPress version.', 'wp-oauth' ); ?>
                <a href="<?php echo admin_url( 'update-core.php' ); ?>"><?php _e( 'Update Now', 'wp-oauth' ); ?></a></p>
        </div>
		<?php
	}

	add_action( 'admin_notices', 'wo_incompatibility_with_wp_version' );
}

/**
 * @todo  Move setup and upgrade inside the function wo_plugin_activate()
 */
register_activation_hook( __FILE__, array( new WO_Server, 'setup' ) );
register_activation_hook( __FILE__, array( new WO_Server, 'upgrade' ) );
