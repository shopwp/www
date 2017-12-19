<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @version 1.0
 * @since   1.0
 */
class ACP_License_Manager {

	/**
	 * Option key to store licence data
	 *
	 * @since 1.1
	 */
	const OPTION_KEY = 'cpupdate_cac-pro';

	/**
	 * Licence Key
	 *
	 * @since 1.1
	 */
	private $licence_key;

	/**
	 * API object
	 *
	 * @since 1.1
	 * @var ACP_License_API $api
	 */
	private $api;

	/**
	 * @since 1.0
	 *
	 * @param array $args [api_url, option_key, file, name, version]
	 */
	public function __construct() {

		// reflect API settings within the update request
		add_filter( 'http_request_args', array( $this, 'use_api_http_request_args_for_plugin_update' ), 10, 2 );

		// Hook into WP update process
		add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'update_check' ) );

		// Seen when the user clicks "view details" on the plugin listing page
		add_action( 'install_plugins_pre_plugin-information', array( $this, 'plugin_changelog' ) );

		// Activate licence on plugin install
		register_activation_hook( ACP_FILE, array( $this, 'auto_activate_licence' ) );

		// Add UI
		add_filter( 'ac/settings/groups', array( $this, 'settings_group' ) );
		add_action( 'ac/settings/group/addons', array( $this, 'display' ) );

		// Multisite
		add_filter( 'acp/network_settings/groups', array( $this, 'settings_group' ), 10, 2 );
		add_action( 'acp/network_settings/group/addons', array( $this, 'display' ) );

		// licence Requests
		add_action( 'admin_init', array( $this, 'handle_request' ) );

		// Hook into the plugin install process, inject addon download url
		add_action( 'plugins_api', array( $this, 'inject_addon_install_resource' ), 10, 3 );

		// Do check before installing add-on
		add_filter( 'ac/addons/install_request/maybe_error', array( $this, 'maybe_install_error' ), 10, 2 );

		// Add notifications to the plugin screen
		add_action( 'after_plugin_row_' . ACP()->get_basename(), array( $this, 'display_plugin_row_notices' ), 11 );

		// Add notice for license expiry
		add_action( 'all_admin_notices', array( $this, 'display_license_expiry_notices' ) );

		// Check for notice hide request
		add_action( 'wp_ajax_cpac_hide_license_expiry_notice', array( $this, 'ajax_hide_license_expiry_notice' ) );

		// Adds notice to update message that a licence is needed
		add_action( 'in_plugin_update_message-' . ACP()->get_basename(), array( $this, 'need_license_message' ), 10, 2 );

		// add scripts, after settings page is set.
		add_action( 'admin_enqueue_scripts', array( $this, 'register_admin_scripts' ), 20 );
		add_action( 'admin_menu', array( $this, 'scripts' ), 20 );
		add_action( 'network_admin_menu', array( $this, 'network_scripts' ), 20 );

		// check for a secure connection
		add_action( 'wp_ajax_cpac_check_connection', array( $this, 'ajax_check_connection' ) );

		// check license has been renewed
		add_action( 'wp_ajax_cpac_check_license_renewed', array( $this, 'ajax_check_license_renewed' ) );

		// check subscription renewal status once every week
		add_action( 'shutdown', array( $this, 'do_weekly_renewal_check' ) );

		add_action( 'init', array( $this, 'register_no_ssl_endpoint_rewrite_rule' ) );
	}

	/**
	 * Register endpoint to access API over plain http
	 *
	 * @since 3.1.2
	 */
	public function register_no_ssl_endpoint_rewrite_rule() {
		add_rewrite_rule( '^' . $this->get_no_ssl_endpoint() . '/?$', 'index.php', 'top' );
	}

	/**
	 * Endpoint to access API over plain http
	 *
	 * @since 3.1.2
	 * @return string
	 */
	private function get_no_ssl_endpoint() {
		return 'cac-api-nossl';
	}

	/**
	 * Setup an API object
	 *
	 * @since 1.1
	 */
	private function set_api() {
		$api = new ACP_License_API();

		$url = apply_filters( 'ac/api/url', ac_get_site_url() );

		$use_secure = apply_filters( 'ac/api/secure', true );

		// change the scheme to access the API via http
		if ( ! $use_secure ) {
			$url = set_url_scheme( $url, 'http' ) . '/' . $this->get_no_ssl_endpoint();
		}

		$api->set_url( $url )->set_request_arg( 'sslverify', $this->is_ssl_enabled() );

		$this->api = $api;
	}

	/**
	 * @return ACP_License_API
	 */
	public function api() {
		if ( null === $this->api ) {
			$this->set_api();
		}

		return $this->api;
	}

	/**
	 * Tries to match API settings with the update request
	 *
	 * @since 3.1.2
	 *
	 * @param array  $r
	 * @param string $url
	 *
	 * @return array
	 */
	public function use_api_http_request_args_for_plugin_update( $r, $url ) {
		// only applies to api URL domain
		if ( 0 === strpos( $url, $this->api()->get_url() ) ) {
			$api_args = $this->api()->get_request_args();

			if ( isset( $api_args['sslverify'] ) ) {
				$r['sslverify'] = $api_args['sslverify'];
			}
		}

		return $r;
	}

	/**
	 * @since 1.1
	 * @return object self
	 */
	public function set_licence_key( $licence_key ) {
		$this->licence_key = $licence_key;

		return $this;
	}

	/**
	 * Update expiration date & renewal discount
	 *
	 * @since 3.4.3
	 */
	public function update_license_details() {
		$response = $this->api()->get_license_details( $this->get_licence_key() );

		if ( isset( $response->expiry_date ) ) {
			$this->store_license_expiry_date( $response->expiry_date );
		}
		if ( isset( $response->renewal_discount ) ) {
			$this->store_license_renewal_discount( $response->renewal_discount );
		}
	}

	/**
	 * @since 1.0
	 *
	 * @param string $licence_key Licence Key
	 *
	 * @return object Response
	 */
	public function activate_licence( $licence_key ) {
		$response = $this->api()->activate_licence( $licence_key );

		$this->delete_licence_key();
		$this->delete_licence_status();

		if ( isset( $response->activated ) ) {
			$this->store_licence_key( $licence_key );
			$this->store_licence_status( 'active' );

			if ( isset( $response->expiry_date ) ) {
				$this->store_license_expiry_date( $response->expiry_date );
			}
			if ( isset( $response->renewal_discount ) ) {
				$this->store_license_renewal_discount( $response->renewal_discount );
			}

			$this->purge_plugin_transients();
		}

		return $response;
	}

	public function purge_plugin_transients() {
		delete_site_transient( 'update_plugins' );
		delete_site_transient( 'admin-columns-pro_acppluginupdate' );

		// Integrations
 		foreach ( AC()->addons()->get_addons() as $addon ) {
			delete_site_transient( $addon->get_slug() . '_acppluginupdate' );
		}
	}

	/**
	 * @since 1.0
	 */
	public function deactivate_licence() {
		$response = $this->api()->deactivate_licence( $this->get_licence_key() );

		$this->delete_licence_key();
		$this->delete_licence_status();
		$this->delete_license_expiry_date();

		return $response;
	}

	/**
	 * HTML changelog
	 *
	 * @since 1.0
	 * @return void
	 */
	public function plugin_changelog() {
		$basename = false;

		$plugin = filter_input( INPUT_GET, 'plugin' );

		// Pro
		if ( $plugin === dirname( ACP()->get_basename() ) ) {
			$basename = $plugin;
		}

		// Addons
		if ( AC()->addons()->get_addon( $plugin ) ) {
			$basename = $plugin;
		}

		if ( $basename ) {
			$changelog = $this->api()->get_plugin_changelog( $basename );

			if ( is_wp_error( $changelog ) ) {
				$changelog = $changelog->get_error_message();
			}

			echo $changelog;
			exit;
		}
	}

	/**
	 * @see   ACP_License_API::get_plugin_install_data()
	 * @since 1.1
	 * @return mixed
	 */
	public function get_plugin_install_data( $plugin_name, $clear_cache = false ) {

		if ( $clear_cache ) {
			delete_site_transient( self::OPTION_KEY . '_plugininstall' );
		}

		$plugin_install = get_site_transient( self::OPTION_KEY . '_plugininstall' );

		// no cache, get data
		if ( ! $plugin_install ) {
			$plugin_install = $this->api()->get_plugin_install_data( $this->get_licence_key(), $plugin_name );

			// flatten wp_error object for transient storage
			if ( is_wp_error( $plugin_install ) ) {
				$plugin_install = $this->flatten_wp_error( $plugin_install );
			}
		}

		/*
			We need to set the transient even when there's an error,
			otherwise we'll end up making API requests over and over again
			and slowing things down big time.
		*/
		set_site_transient( self::OPTION_KEY . '_plugininstall', $plugin_install, 60 * 15 ); // 15 min.

		// Maybe create wp_error object
		$plugin_install = $this->maybe_unflatten_wp_error( $plugin_install );

		return $plugin_install;
	}

	/**
	 * @see   ACP_License_API::get_plugin_update_data()
	 * @since 1.1
	 * @return
	 */
	public function get_plugin_update_data( $plugin_name, $version ) {
		$plugin_update = get_site_transient( $plugin_name . '_acppluginupdate' );

		// no cache, get data
		if ( ! $plugin_update ) {
			$plugin_update = $this->api()->get_plugin_update_data( $this->get_licence_key(), $plugin_name, $version );

			// flatten wp_error object for transient storage
			if ( is_wp_error( $plugin_update ) ) {
				$plugin_update = $this->flatten_wp_error( $plugin_update );
			}
		}

		/*
			We need to set the transient even when there's an error,
			otherwise we'll end up making API requests over and over again
			and slowing things down big time.
		*/
		set_site_transient( $plugin_name . '_acppluginupdate', $plugin_update, HOUR_IN_SECONDS );

		$plugin_update = $this->maybe_unflatten_wp_error( $plugin_update );

		return $plugin_update;
	}

	/**
	 * @see   ACP_License_API::get_plugin_details()
	 * @since 1.1
	 * @return
	 */
	public function get_plugin_details() {

		$plugin_details = get_site_transient( self::OPTION_KEY . '_plugindetails' );

		// no cache, get data
		if ( ! $plugin_details ) {
			$plugin_details = $this->api()->get_plugin_details( ACP()->get_basename() );

			// flatten wp_error object for transient storage
			if ( is_wp_error( $plugin_details ) ) {
				$plugin_details = $this->flatten_wp_error( $plugin_details );
			}
		}

		/*
			We need to set the transient even when there's an error,
			otherwise we'll end up making API requests over and over again
			and slowing things down big time.
		*/
		set_site_transient( self::OPTION_KEY . '_plugindetails', $plugin_details, DAY_IN_SECONDS );

		$plugin_details = $this->maybe_unflatten_wp_error( $plugin_details );

		return $plugin_details;
	}

	/**
	 * Check for Updates at the defined API endpoint and modify the update array.
	 *
	 * @uses api_request()
	 *
	 * @param object $transient Update array build by Wordpress.
	 *
	 * @return stdClass Modified update array with custom plugin data.
	 */
	public function update_check( $transient ) {

		// Addons
		if ( $addons = $this->get_addons_update_data() ) {
			foreach ( $addons as $addon ) {
				$plugin_data = $this->get_plugin_update_data( dirname( $addon['plugin'] ), $addon['version'] );
				if ( ! is_wp_error( $plugin_data ) && ! empty( $plugin_data->new_version ) && version_compare( $plugin_data->new_version, $addon['version'] ) > 0 ) {
					$transient->response[ $addon['plugin'] ] = $plugin_data;
				}
			}
		}

		// Main plugin
		$plugin_data = $this->get_plugin_update_data( dirname( ACP()->get_basename() ), $this->get_version() );
		if ( ! is_wp_error( $plugin_data ) && ! empty( $plugin_data->new_version ) && version_compare( $plugin_data->new_version, $this->get_version() ) > 0 ) {
			$transient->response[ ACP()->get_basename() ] = $plugin_data;
		}

		return $transient;
	}

	/**
	 * @since 1.0
	 * @return void
	 */
	public function auto_activate_licence() {
		if ( ! $this->is_license_active() && ( $licence = $this->get_licence_key() ) ) {
			$this->activate_licence( $licence );
		}
	}

	/**
	 * Get the plugin's header info from the installed plugins list.
	 *
	 * @since 1.1
	 */
	public function get_plugin_info( $field ) {
		if ( ! is_admin() ) {
			return false;
		}

		$plugins = get_plugins();

		if ( ! isset( $plugins[ ACP()->get_basename() ][ $field ] ) ) {
			return false;
		}

		return $plugins[ ACP()->get_basename() ][ $field ];
	}

	public function get_basename() {
		return ACP()->get_basename();
	}

	public function get_version() {
		return $this->get_plugin_info( 'Version' );
	}

	public function get_name() {
		return $this->get_plugin_info( 'Name' );
	}

	/**
	 * Check if the license for this plugin is managed per site or network
	 *
	 * @since 3.6
	 * @return boolean
	 */
	protected function is_network_managed_license() {
		return is_multisite() && is_plugin_active_for_network( ACP()->get_basename() );
	}

	protected function update_option( $option, $value, $autoload = false ) {
		return $this->is_network_managed_license()
			? update_site_option( $option, $value )
			: update_option( $option, $value, $autoload );
	}

	protected function get_option( $option, $default = false ) {
		return $this->is_network_managed_license()
			? get_site_option( $option, $default )
			: get_option( $option, $default );
	}

	protected function delete_option( $option ) {
		return $this->is_network_managed_license()
			? delete_site_option( $option )
			: delete_option( $option );
	}

	public function get_masked_licence_key() {
		return str_repeat( '*', 28 ) . substr( $this->get_licence_key(), -4 );
	}

	public function get_licence_key() {
		if ( null === $this->licence_key ) {
			$this->set_licence_key( trim( $this->get_option( self::OPTION_KEY ) ) );
		}

		return $this->licence_key;
	}

	public function get_licence_status() {
		return $this->get_option( self::OPTION_KEY . '_sts' );
	}

	public function is_license_active() {
		$status = $this->get_licence_status();

		return true === $status || '1' === $status || 'active' === $status;
	}

	public function store_licence_key( $licence_key ) {
		$this->update_option( self::OPTION_KEY, $licence_key );
	}

	public function delete_licence_key() {
		$this->delete_option( self::OPTION_KEY );
	}

	public function store_licence_status( $status ) {
		$this->update_option( self::OPTION_KEY . '_sts', $status ); // status is 'true' or 'expired'
	}

	public function delete_licence_status() {
		$this->delete_option( self::OPTION_KEY . '_sts' );
	}

	public function is_ssl_enabled() {
		return '1' === $this->get_option( self::OPTION_KEY . '_ssl' );
	}

	public function enable_ssl() {
		$this->update_option( self::OPTION_KEY . '_ssl', '1' );
		$this->purge_plugin_transients(); // for updater
	}

	public function disable_ssl() {
		$this->delete_option( self::OPTION_KEY . '_ssl' );
		$this->purge_plugin_transients(); // for updater
	}

	public function get_license_expiry_date() {
		$expiry_date = $this->get_option( self::OPTION_KEY . '_expiry_date' );

		if ( ! is_int( $expiry_date ) ) {
			$expiry_date = strtotime( $expiry_date );
		}

		return $expiry_date;
	}

	public function store_license_expiry_date( $renewal_date ) {
		$this->update_option( self::OPTION_KEY . '_expiry_date', $renewal_date );
	}

	public function delete_license_expiry_date() {
		$this->delete_option( self::OPTION_KEY . '_expiry_date' );
	}

	public function get_license_renewal_discount() {
		return $this->get_option( self::OPTION_KEY . '_renewal_discount' );
	}

	public function store_license_renewal_discount( $renewal_discount ) {
		$this->update_option( self::OPTION_KEY . '_renewal_discount', $renewal_discount );
	}

	public function delete_license_renewal_discount() {
		$this->delete_option( self::OPTION_KEY . '_renewal_discount' );
	}

	public function get_days_to_expiry() {
		$days = false;

		if ( $this->is_license_active() && ( $expiry_date = $this->get_license_expiry_date() ) ) {
			$days = floor( ( $expiry_date - time() ) / DAY_IN_SECONDS );
		}

		return $days;
	}

	public function is_license_expired() {
		$days = $this->get_days_to_expiry();

		return false !== $days && $days <= 0;
	}

	/**
	 * Flatten WP_Error object for storage in transient
	 *
	 * @param object $wp_error WP_Error object
	 *
	 * @return $error Error Object
	 */
	public function flatten_wp_error( $wp_error ) {
		$error = false;

		if ( is_wp_error( $wp_error ) ) {
			$error = (object) array(
				'error'   => 1,
				'time'    => time(),
				'code'    => $wp_error->get_error_code(),
				'message' => $wp_error->get_error_message(),
			);
		}

		return $error;
	}

	/**
	 * Maybe unflatten error
	 *
	 * @param mixed $maybe_error stdClass
	 *
	 * @return $wp_error WP_Error Object
	 */
	public function maybe_unflatten_wp_error( $maybe_error ) {
		if ( isset( $maybe_error->error ) && isset( $maybe_error->message ) ) {
			$maybe_error = new WP_Error( $maybe_error->code, $maybe_error->message );
		}

		return $maybe_error;
	}

	/**
	 * @since 3.4.3
	 */
	public function ajax_check_license_renewed() {
		// update renewal date
		$this->update_license_details();

		// check is license is renewed
		$phases = $this->get_hide_license_notice_thresholds();
		$is_renewed = ( $this->get_days_to_expiry() <= $phases[ count( $phases ) - 1 ] ) ? false : true;

		// create message based on status
		$message = __( 'Your license was successfully renewed!', 'codepress-admin-columns' );
		$type = 'success';

		if ( ! $is_renewed ) {
			$message = $this->get_renewal_message() . ' <strong>' . __( 'Your license has not been renewed yet.', 'codepress-admin-columns' ) . '</strong>';
			$type = 'error';
		}

		$notice = new AC_Notice_Plugin( ACP()->get_basename() );
		$notice->set_message( $message )
		       ->set_type( $type )
		       ->display_notice();

		exit;
	}

	/**
	 * @since 3.4.3
	 */
	public function do_weekly_renewal_check() {
		if ( get_transient( '_cpac_renewal_check' ) ) {
			return;
		}

		$this->update_license_details();

		set_transient( '_cpac_renewal_check', 1, WEEK_IN_SECONDS ); // 7 day interval
	}

	/**
	 * @since 3.1.2
	 */
	public function ajax_check_connection() {
		echo $this->api()->test_request( ACP()->get_basename() ) ? '1' : '0';
		exit;
	}

	/**
	 * @since 3.1.2
	 */
	public function scripts() {
		if ( AC()->admin()->is_current_page( 'settings' ) ) {
			add_action( "admin_print_scripts-" . AC()->admin()->get_hook_suffix(), array( $this, 'admin_scripts' ) );
		}
	}

	public function network_scripts() {
		if ( ac_is_pro_active() ) {
			add_action( "admin_print_scripts-" . ACP()->network_admin()->get_hook_suffix(), array( $this, 'admin_scripts' ) );
		}
	}

	public function register_admin_scripts() {
		wp_register_style( 'acp-license-manager', ACP()->get_plugin_url() . "assets/css/license-manager" . AC()->minified() . ".css", array(), ACP()->get_version() );
		wp_register_script( 'acp-license-manager', ACP()->get_plugin_url() . "assets/js/license-manager" . AC()->minified() . ".js", array( 'jquery' ), ACP()->get_version() );
	}

	/**
	 * @since 3.1.2
	 */
	public function admin_scripts() {
		wp_enqueue_script( 'ac-connection', ACP()->get_plugin_url() . "assets/js/check-connection.js", array( 'jquery' ), $this->get_version() );

		wp_enqueue_script( 'acp-license-manager' );
		wp_enqueue_style( 'acp-license-manager' );
	}

	/**
	 * @since 1.0
	 */
	public function maybe_install_error( $error, $plugin_name ) {
		if ( ! $this->is_license_active() ) {
			$error = sprintf( __( "Licence not active. Enter your licence key on <a href='%s'>the settings page</a>.", 'codepress-admin-columns' ), $this->get_license_page_url() );
		}

		$install_data = $this->get_plugin_install_data( $plugin_name, $clear_cache = true ); // get remote add-on info

		if ( is_wp_error( $install_data ) ) {
			$error = $install_data->get_error_message();
		}

		return $error;
	}

	/**
	 * Get addons data for the update process
	 *
	 * @since 1.0.0
	 */
	public function get_addons_update_data() {
		$addons_update_data = array();

		foreach ( AC()->addons()->get_addons() as $addon ) {
			$basename = $addon->get_basename();

			if ( ! $basename ) {
				continue;
			}

			$addons_update_data[] = array(
				'plugin'  => $basename,
				'version' => $addon->get_version(),
			);
		}

		return $addons_update_data;
	}

	/**
	 * Add addons to install process, not the update process.
	 *
	 * @since 1.0
	 */
	public function inject_addon_install_resource( $result, $action, $args ) {
		if ( 'plugin_information' != $action || empty( $args->slug ) ) {
			return $result;
		}

		if ( ! AC()->addons()->get_addon( $args->slug ) ) {
			return $result;
		}

		$install_data = $this->get_plugin_install_data( $args->slug, true );

		if ( ! $install_data ) {
			return $result;
		}

		return $install_data;
	}

	/**
	 * Handle requests for license activation and deactivation
	 *
	 * @since 1.0
	 */
	public function handle_request() {
		$nonce = filter_input( INPUT_POST, '_acnonce' );

		if ( ! $nonce ) {
			return;
		}

		// Activation
		if ( wp_verify_nonce( $nonce, 'ac-addon-activate' ) ) {

			$licence_key = sanitize_text_field( filter_input( INPUT_POST, 'license' ) );

			if ( empty( $licence_key ) ) {
				AC()->notice( __( 'Empty licence.', 'codepress-admin-columns' ), 'error' );

				return;
			}

			$response = $this->activate_licence( $licence_key );

			if ( is_wp_error( $response ) ) {
				AC()->notice( $response->get_error_message(), 'error' );
			} elseif ( isset( $response->activated ) ) {
				AC()->notice( $response->message, 'updated' );
			} else {
				AC()->notice( __( 'Wrong response from API.', 'codepress-admin-columns' ), 'error' );
			}
		}

		// Deactivation
		if ( wp_verify_nonce( $nonce, 'ac-addon-deactivate' ) ) {

			$response = $this->deactivate_licence();

			if ( is_wp_error( $response ) ) {
				AC()->notice( __( 'Wrong response from API.', 'codepress-admin-columns' ) . ' ' . $response->get_error_message(), 'error' );
			} elseif ( isset( $response->deactivated ) ) {
				AC()->notice( $response->message, 'updated' );
			} else {
				AC()->notice( __( 'Wrong response from API.', 'codepress-admin-columns' ), 'error' );
			}
		}

		// Toggle SSL
		if ( wp_verify_nonce( $nonce, 'ac-addon-toggle-ssl' ) ) {

			// disable ssl
			if ( '0' == filter_input( INPUT_POST, 'ssl' ) ) {
				$this->disable_ssl();
			} else {
				$this->enable_ssl();
			}
		}
	}

	/**
	 * Add settings group to Admin Columns settings page
	 *
	 * @since 1.0
	 *
	 * @param array $groups Add group to ACP settings screen
	 *
	 * @return array Settings group for ACP
	 */
	public function settings_group( $groups ) {
		if ( isset( $groups['addons'] ) ) {
			return $groups;
		}

		$groups['addons'] = array(
			'title'       => __( 'Updates', 'codepress-admin-columns' ),
			'description' => __( 'Enter your licence code to receive automatic updates.', 'codepress-admin-columns' ),
		);

		return $groups;
	}

	/**
	 * Get the URL to manage your license based on network or site managed license
	 *
	 * @return string
	 */
	public function get_license_page_url() {
		$url = AC()->admin()->get_link( 'settings' );

		if ( $this->is_network_managed_license() ) {
			$url = ACP()->get_network_settings_url();
		}

		return $url;
	}

	/**
	 * Display licence field
	 *
	 * @since 1.0
	 * @return void
	 */
	public function display() {

		// When the plugin is network activated, the license is managed globally
		if ( $this->is_network_managed_license() && ! is_network_admin() ) {
			?>
            <p>
				<?php
				$page = __( 'network settings page', 'codepress-admin-columns' );

				if ( current_user_can( 'manage_network_options' ) ) {
					$page = ac_helper()->html->link( network_admin_url( 'settings.php?page=codepress-admin-columns' ), $page );
				}

				printf( __( 'The license can be managed on the %s.', 'codepress-admin-columns' ), $page );
				?>
            </p>
			<?php
		} else {

			/**
			 * Hook is used for hiding the license form from the settings page
			 *
			 * @param bool false Show license input fields
			 */
			$show_license = apply_filters( 'acp/display_licence', true );

			if ( ! $show_license ) {
				return;
			}

			$licence = filter_input( INPUT_POST, 'license' );

			if ( ! $licence ) {
				$licence = $this->get_licence_key();
			}

			?>

            <form id="licence_activation" action="" method="post">

				<?php if ( $this->is_license_active() ) : ?>

					<?php wp_nonce_field( 'ac-addon-deactivate', '_acnonce' ); ?>

                    <p>
                        <span class="dashicons dashicons-yes"></span>
						<?php _e( 'Automatic updates are enabled.', 'codepress-admin-columns' ); ?>
                        <input type="submit" class="button" value="<?php _e( 'Deactivate licence', 'codepress-admin-columns' ); ?>">
                    </p>

				<?php else : ?>

					<?php wp_nonce_field( 'ac-addon-activate', '_acnonce' ); ?>

                    <input type="password" value="<?php echo esc_attr( $licence ); ?>" name="license" size="30" placeholder="<?php echo esc_attr( __( 'Enter your licence code', 'codepress-admin-columns' ) ); ?>">
                    <input type="submit" class="button" value="<?php _e( 'Update licence', 'codepress-admin-columns' ); ?>">
                    <p class="description">
						<?php printf( __( 'You can find your license key on your %s.', 'codepress-admin-columns' ), '<a href="' . ac_get_site_utm_url( 'my-account', 'license-activation' ) . '" target="_blank">' . __( 'account page', 'codepress-admin-columns' ) . '</a>' ); ?>
                    </p>

				<?php endif; ?>

            </form>

            <form id="toggle-ssl" action="" method="post" class="notice notice-warning hidden">

				<?php wp_nonce_field( 'ac-addon-toggle-ssl', '_acnonce' ); ?>

                <p style="padding: 20px;">
					<?php printf( __( 'Could not connect to %s — You will not receive update notifications or be able to activate your license until this is fixed. This issue is often caused by an improperly configured SSL server (https). We recommend fixing the SSL configuration on your server, but if you need a quick fix you can:', 'codepress-admin-columns' ), ac_get_site_url() ); ?>
                    <br/><br/>

					<?php
					$ssl_value = 1;
					$ssl_label = __( 'Enable SSL', 'codepress-admin-columns' );

					if ( $this->is_ssl_enabled() ) {
						$ssl_value = 0;
						$ssl_label = __( 'Disable SSL', 'codepress-admin-columns' );
					}
					?>

                    <input type="hidden" name="ssl" value="<?php echo esc_attr( $ssl_value ); ?>">
                    <input type="submit" class="button" value="<?php echo esc_attr( $ssl_label ); ?>">

                </p>
            </form>
			<?php
		}
	}

	/**
	 * Get renewal message
	 *
	 * @since 3.4.3
	 */
	private function get_renewal_message() {

		$message = false;
		$days_to_expiry = $this->get_days_to_expiry();

		// renewal date has been set?
		if ( $days_to_expiry !== false ) {
			if ( $days_to_expiry > 0 ) {

				if ( $days_to_expiry < 28 ) { // for plugin page
					$days = sprintf( _n( '1 day', '%s days', $days_to_expiry, 'codepress-admin-columns' ), $days_to_expiry );
					if ( $discount = $this->get_license_renewal_discount() ) {
						$message = sprintf(
							__( "Your Admin Columns Pro license will expire in %s. %s now and get a %d%% discount!", 'codepress-admin-columns' ),
							'<strong>' . $days . '</strong>',
							ac_helper()->html->link( ac_get_site_utm_url( 'my-account', 'renewal' ), __( 'Renew your license', 'codepress-admin-columns' ) ),
							$discount
						);
					} else {
						$message = sprintf(
							__( "Your Admin Columns Pro license will expire in %s. %s now and get a discount!", 'codepress-admin-columns' ),
							'<strong>' . $days . '</strong>',
							ac_helper()->html->link( ac_get_site_utm_url( 'my-account', 'renewal' ), __( 'Renew your license', 'codepress-admin-columns' ) )
						);
					}
				}
			} else {
				$message = sprintf(
					__( 'Your Admin Columns Pro license has expired on %s! Renew your license now by going to your %s.', 'codepress-admin-columns' ),
					date_i18n( get_option( 'date_format' ), $this->get_license_expiry_date() ),
					'<a href="' . ac_get_site_utm_url( 'my-account', 'renewal' ) . '">' . __( 'My Account page', 'codepress-admin-columns' ) . '</a>'
				);
			}
		}

		return $message;
	}

	/**
	 * @return string
	 */
	private function get_check_license_link() {
		ob_start();

		$this->check_license_link();

		return ob_get_clean();
	}

	/**
	 * Get the HTML for checking a license
	 *
	 * @since 3.4.3
	 */
	private function check_license_link() {
		wp_enqueue_script( 'acp-license-manager' );
		wp_enqueue_style( 'acp-license-manager' );
		?>

        <a href="#" class="cpac-check-license"><?php _e( 'Check my license', 'codepress-admin-columns' ); ?>.</a>

		<?php
	}

	/**
	 * Shows a message below the plugin on the plugins page
	 *
	 * @since 1.0.3
	 */
	public function display_plugin_row_notices() {
		$message = false;

		if ( $this->is_license_active() ) {
			if ( $message = $this->get_renewal_message() ) {
				$message .= $this->get_check_license_link();
			}
		} else {
			$plugin_details = $this->get_plugin_details();

			if ( isset( $plugin_details->version ) && version_compare( $this->get_version(), $plugin_details->version, '>=' ) ) {
				$message = $this->get_need_license_message();
			}
		}

		if ( $message ) {
			$notice = new AC_Notice_Plugin( ACP()->get_basename() );
			$notice->set_message( $message )
			       ->display_notice();
		}
	}

	/**
	 * Whether the license expiry notice should be displayed, regardless of the license timeout
	 *
	 * @since 3.4.3
	 */
	public function is_license_expiry_notice_hideable() {
		return ! AC()->admin()->is_current_page( 'settings' );
	}

	/**
	 * Display notice for license expiry
	 *
	 * @since 3.4.3
	 */
	public function display_license_expiry_notices() {
		global $pagenow, $current_screen;

		// Only visible on plugin screen, table screen or AC settings screen
		if ( 'plugins.php' !== $pagenow && ! AC()->admin()->is_admin_screen() && ! AC()->get_list_screen_by_wpscreen( $current_screen ) ) {
			return;
		}

		if ( ! AC()->user_can_manage_admin_columns() ) {
			return;
		}

		/**
		 * @since 4.0
		 */
		$hide_notice = apply_filters( 'acp/hide_renewal_notice', false );

		/**
		 * Filter the visibility of the Admin Columns renewal notice
		 *
		 * @since 3.4.3
		 *
		 * @param bool $hide Whether to hide the renewal notice. Defaults to false.
		 */
		if ( $hide_notice || AC()->suppress_site_wide_notices() ) {
			return;
		}

		$hide_license_timeout = get_user_meta( get_current_user_id(), 'cpac_hide_license_notice_timeout', true );
		$hide_license_phase = get_user_meta( get_current_user_id(), 'cpac_hide_license_notice_phase', true );

		if ( $this->is_license_expiry_notice_hideable() ) {
			// Notice was blocked the final time
			if ( $hide_license_phase == 'completed' ) {
				return;
			}

			// Notice was blocked, and timeout hasn't been reached yet
			if ( time() < $hide_license_timeout ) {
				return;
			}
		}

		// First license expiry threshold passed
		$phases = $this->get_hide_license_notice_thresholds();

		if ( $this->get_days_to_expiry() > $phases[ count( $phases ) - 1 ] ) {
			return;
		}

		// Show a renewal message if the license needs renewal
		if ( $message = $this->get_renewal_message() ) {
			wp_enqueue_style( 'ac-sitewide-notices' );
			wp_enqueue_script( 'acp-license-manager' );
			?>

            <div class="ac-message error warning">
				<?php if ( $this->is_license_expiry_notice_hideable() ) : ?>
                    <a href="#" class="hide-notice" data-hide-notice="license-check"></a>
				<?php endif; ?>
                <p>
					<?php echo $message; ?>
					<?php $this->check_license_link(); ?>
                </p>
                <div class="clear"></div>
            </div>

			<?php
		}
	}

	public function get_hide_license_notice_thresholds() {
		return array( 0, 7, 21 );
	}

	/**
	 * Handle an AJAX request for hiding license expiry notices
	 *
	 * @since 3.4.3
	 */
	public function ajax_hide_license_expiry_notice() {
		$hide_license_phase = get_user_meta( get_current_user_id(), 'cpac_hide_license_notice_phase', true );

		if ( $hide_license_phase != 'completed' ) {
			$expiry_date = $this->get_license_expiry_date();
			$phases = $this->get_hide_license_notice_thresholds();
			$days = $this->get_days_to_expiry();
			$phase = 0;

			foreach ( $phases as $phase => $threshold ) {
				if ( $days <= $threshold ) {
					break;
				}
			}

			$new_phase = $phase - 1;

			if ( $new_phase == -1 ) {
				update_user_meta( get_current_user_id(), 'cpac_hide_license_notice_timeout', 0 );
				update_user_meta( get_current_user_id(), 'cpac_hide_license_notice_phase', 'completed' );
			} else {
				// Expiry date minus x days
				update_user_meta( get_current_user_id(), 'cpac_hide_license_notice_timeout', $expiry_date - $phases[ $new_phase ] * DAY_IN_SECONDS );
				update_user_meta( get_current_user_id(), 'cpac_hide_license_notice_phase', $new_phase );
			}
		}

		wp_send_json_success();
	}

	/**
	 * Message to add to update message when you have not activated your license
	 *
	 * @return string
	 */
	public function get_need_license_message() {
		$message = sprintf(
			__( "To enable updates, please enter your license key on the <a href='%s'>Settings</a> page. If you don't have a licence key, please see <a href='%s' target='_blank'>details & pricing</a>.", 'codepress_admin_columns' ),
			AC()->admin()->get_link( 'settings' ),
			ac_get_site_utm_url( 'pricing-purchase', 'plugins' )
		);

		$sanitized_message = wp_kses( $message, array(
			'a' => array(
				'href'   => array(),
				'target' => array(),
				'class'  => array(),
			),
		) );

		return $sanitized_message;
	}

	/**
	 * Message to add to update message when you have not activated your license
	 *
	 * @param  array  $plugin_data
	 * @param  object $r
	 *
	 */
	public function need_license_message( $plugin_data, $r ) {
		if ( empty( $r->package ) ) {
			echo '<br>' . $this->get_need_license_message();
		}
	}

}
