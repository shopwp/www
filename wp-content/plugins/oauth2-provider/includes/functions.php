<?php
/**
 * WordPress OAuth Main Functions File
 *
 * @version 3.2.0 (IMPORTANT)
 *
 * Modifying this file will cause the plugin to crash. This could also result in the the entire WordPress install
 * to become unstable. This file is considered sensitive and thus we have provided simple protection against file
 * manipulation.
 */

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

// Hook into core filters
require_once dirname( __FILE__ ) . '/filters.php';

// Hook into core actions
require_once( dirname( __FILE__ ) . '/actions.php' );

add_action( 'init', 'wo_types' );
function wo_types() {
	$labels = array(
		'name'               => _x( 'Client', 'post type general name', 'wp-oauth' ),
		'singular_name'      => _x( 'Client', 'post type singular name', 'wp-oauth' ),
		'menu_name'          => _x( 'Clients', 'admin menu', 'wp-oauth' ),
		'name_admin_bar'     => _x( 'Client', 'add new on admin bar', 'wp-oauth' ),
		'add_new'            => _x( 'Add New', 'Client', 'wp-oauth' ),
		'add_new_item'       => __( 'Add New BoClientok', 'wp-oauth' ),
		'new_item'           => __( 'New Client', 'wp-oauth' ),
		'edit_item'          => __( 'Edit Client', 'wp-oauth' ),
		'view_item'          => __( 'View Client', 'wp-oauth' ),
		'all_items'          => __( 'All Clients', 'wp-oauth' ),
		'search_items'       => __( 'Search Clients', 'wp-oauth' ),
		'parent_item_colon'  => __( 'Parent Clients:', 'wp-oauth' ),
		'not_found'          => __( 'No clients found.', 'wp-oauth' ),
		'not_found_in_trash' => __( 'No clients found in Trash.', 'wp-oauth' )
	);

	$args = array(
		'labels'              => $labels,
		'description'         => __( 'Description.', 'wp-oauth' ),
		'public'              => true,
		'publicly_queryable'  => false,
		'show_ui'             => true,
		'show_in_menu'        => false,
		'query_var'           => true,
		'rewrite'             => array( 'slug' => 'wo_client' ),
		'capability_type'     => 'post',
		'has_archive'         => true,
		'hierarchical'        => false,
		'menu_position'       => null,
		'supports'            => array( 'title' ),
		'exclude_from_search' => true
	);

	register_post_type( 'wo_client', $args );
}

/**
 * [wo_create_client description]
 *
 * @param  [type] $user [description]
 *
 * @return [type]       [description]
 *
 * @todo Add role and permissions check
 */
function wo_insert_client( $client_data = null ) {

	// @todo Look into changing capabilities to create_clients after proper mapping has been done
	if ( ! current_user_can( 'manage_options' ) || is_null( $client_data ) || has_a_client() ) {
		exit( 'Not Allowed' );

		return false;
	}

	do_action( 'wo_before_create_client', array( $client_data ) );

	// Generate the keys
	$client_id     = wo_gen_key();
	$client_secret = wo_gen_key();

	$grant_types = isset( $client_data['grant_types'] ) ? $client_data['grant_types'] : array();

	$client = array(
		'post_title'     => wp_strip_all_tags( $client_data['name'] ),
		'post_status'    => 'publish',
		'post_author'    => get_current_user_id(),
		'post_type'      => 'wo_client',
		'comment_status' => 'closed',
		'meta_input'     => array(
			'client_id'     => $client_id,
			'client_secret' => $client_secret,
			'grant_types'   => $grant_types,
			'redirect_uri'  => $client_data['redirect_uri'],
			'user_id'       => $client_data['user_id'],
			'scope'         => $client_data['scope']
		)

	);

	// Insert the post into the database
	$client_insert = wp_insert_post( $client );
	if ( is_wp_error( $client_insert ) ) {
		exit( $client_insert->get_error_message() );
	}

	return $client_insert;
}

/**
 * Update a client
 *
 * @param null $client
 *
 * @return false|int|void
 */
function wo_update_client( $client = null ) {
	if ( is_null( $client ) ) {
		return;
	}

	$client_data = array(
		'ID'         => $client['edit_client'],
		'post_title' => $client['name']
	);
	wp_update_post( $client_data, true );

	$grant_types = isset( $client['grant_types'] ) ? $client['grant_types'] : array();
	update_post_meta( $client['edit_client'], 'client_id', $client['client_id'] );
	update_post_meta( $client['edit_client'], 'client_secret', $client['client_secret'] );
	update_post_meta( $client['edit_client'], 'grant_types', $grant_types );
	update_post_meta( $client['edit_client'], 'redirect_uri', $client['redirect_uri'] );
	update_post_meta( $client['edit_client'], 'user_id', $client['user_id'] );
	update_post_meta( $client['edit_client'], 'scope', $client['scope'] );
}

/**
 * Get a client by client ID
 *
 * @param $client_id
 */
function get_client_by_client_id( $client_id ) {
	$query   = new \WP_Query();
	$clients = $query->query( array(
		'post_type'   => 'wo_client',
		'post_status' => 'any',
		'meta_query'  => array(
			array(
				'key'   => 'client_id',
				'value' => $client_id,
			)
		),
	) );

	if ( $clients ) {
		$client                = $clients[0];
		$client->client_secret = get_post_meta( $client->ID, 'client_secret', true );
		$client->redirect_uri  = get_post_meta( $client->ID, 'redirect_uri', true );
		$client->grant_types   = get_post_meta( $client->ID, 'grant_types', true );
		$client->user_id       = get_post_meta( $client->ID, 'user_id', true );
		$client->scope         = get_post_meta( $client->ID, 'scope', true );
		$client->meta          = get_post_meta( $client->ID );

		return (array) $client;
	}
}

/**
 * Retrieve a client from the database
 *
 * @param null $id
 *
 * @return array|null|object|void
 */
function wo_get_client( $id = null ) {
	if ( is_null( $id ) ) {
		return;
	}

	global $wpdb;
	$prep = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}posts WHERE ID = %s", array( $id ) );

	$client = $wpdb->get_row( $prep );
	if ( ! $client ) {
		return false;
	}

	$client->grant_types = maybe_unserialize( get_post_meta( $client->ID, 'grant_types', true ) );
	$client->user_id     = get_post_meta( $client->ID, 'user_id', true );

	return $client;
}

/**
 * Generates a 40 Character key is generated by default but should be adjustable in the admin
 * @return [type] [description]
 *
 * @todo Allow more characters to be added to the character list to provide complex keys
 */
function wo_gen_key( $length = 40 ) {

	// Gather the settings
	$user_defined_length = wo_setting( 'token_length' );

	/**
	 * Temp Fix for https://github.com/justingreerbbi/wp-oauth-server/issues/3
	 * @todo Remove this check on next standard release
	 */
	if ( $user_defined_length > 255 ) {
		$user_defined_length = 255;
	}

	// If user setting is larger than 0, then define it
	if ( $user_defined_length > 0 ) {
		$length = $user_defined_length;
	}

	$characters   = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$randomString = '';

	for ( $i = 0; $i < $length; $i ++ ) {
		$randomString .= $characters[ rand( 0, strlen( $characters ) - 1 ) ];
	}

	return $randomString;
}

/**
 * Blowfish Encryptions
 *
 * @param  [type]  $input  [description]
 * @param  integer $rounds [description]
 *
 * @return [type]          [description]
 *
 * REQUIRES ATLEAST 5.3.x
 */
function wo_crypt( $input, $rounds = 7 ) {
	$salt       = "";
	$salt_chars = array_merge( range( 'A', 'Z' ), range( 'a', 'z' ), range( 0, 9 ) );
	for ( $i = 0; $i < 22; $i ++ ) {
		$salt .= $salt_chars[ array_rand( $salt_chars ) ];
	}

	return crypt( $input, sprintf( '$2a$%02d$', $rounds ) . $salt );
}

/**
 * Check if there is more than one client in the system
 * @return boolean [description]
 *
 * @todo Optimize query
 */
function has_a_client() {
	global $wpdb;
	$count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}posts WHERE post_type = 'wo_client'" );

	if ( intval( $count ) >= 1 ) {
		return true;
	}
}

/**
 * Get the client IP multiple ways since REMOTE_ADDR is not always the best way to do so
 * @return [type] [description]
 */
function client_ip() {
	$ipaddress = '';
	if ( getenv( 'HTTP_CLIENT_IP' ) ) {
		$ipaddress = getenv( 'HTTP_CLIENT_IP' );
	} else if ( getenv( 'HTTP_X_FORWARDED_FOR' ) ) {
		$ipaddress = getenv( 'HTTP_X_FORWARDED_FOR' );
	} else if ( getenv( 'HTTP_X_FORWARDED' ) ) {
		$ipaddress = getenv( 'HTTP_X_FORWARDED' );
	} else if ( getenv( 'HTTP_FORWARDED_FOR' ) ) {
		$ipaddress = getenv( 'HTTP_FORWARDED_FOR' );
	} else if ( getenv( 'HTTP_FORWARDED' ) ) {
		$ipaddress = getenv( 'HTTP_FORWARDED' );
	} else if ( getenv( 'REMOTE_ADDR' ) ) {
		$ipaddress = getenv( 'REMOTE_ADDR' );
	} else {
		$ipaddress = 'UNKNOWN';
	}

	return $ipaddress;
}

/**
 * Check if server is running windows
 * @return boolean [description]
 */
function wo_os_is_win() {
	if ( strtoupper( substr( PHP_OS, 0, 3 ) ) === "WIN" ) {
		return true;
	}

	return false;
}

/**
 * Return the private key for signing
 * @since 3.0.5
 * @return [type] [description]
 */
function get_private_server_key() {
	$keys = apply_filters( 'wo_server_keys', array(
		'public'  => WOABSPATH . '/library/keys/public_key.pem',
		'private' => WOABSPATH . '/library/keys/private_key.pem',
	) );

	return file_get_contents( $keys['private'] );
}

/**
 * Returns the public key
 * @return [type] [description]
 * @since 3.1.0
 */
function get_public_server_key() {
	$keys = apply_filters( 'wo_server_keys', array(
		'public'  => WOABSPATH . '/library/keys/public_key.pem',
		'private' => WOABSPATH . '/library/keys/private_key.pem',
	) );

	return file_get_contents( $keys['public'] );
}

/**
 * Returns the set ALGO that is to be used for the server to encode
 *
 * @todo Possibly set this to be adjusted somewhere. The id_token calls for it to be set by each
 * client as a pref but we need to keep this simple.
 *
 * @since 3.1.93
 * @return String Type of algorithm used for encoding and decoding.
 */
function wo_get_algorithm() {
	return 'RS256';
}

/**
 * Check to see if there is certificates that have been generated
 *
 * @return boolean [description]
 */
function wo_has_certificates() {
	$keys = apply_filters( 'wo_server_keys', array(
		'public'  => WOABSPATH . '/library/keys/public_key.pem',
		'private' => WOABSPATH . '/library/keys/private_key.pem',
	) );

	if ( is_array( $keys ) ) {
		foreach ( $keys as $key ) {
			if ( ! file_exists( $key ) ) {
				return false;
			}
		}

		return true;
	} else {

		return false;
	}
}

/**
 * Retrieves WP OAuth Server settings
 *
 * @param  [type] $key [description]
 *
 * @return [type]      [description]
 */
function wo_setting( $key = null ) {

	$default_settings = _WO()->defualt_settings;
	$settings         = get_option( 'wo_options' );
	$settings         = array_merge( $default_settings, array_filter( $settings, function ( $value ) {
		return $value !== '';
	} ) );

	// No key is provided, let return the entire options table
	if ( is_null( $key ) ) {
		return $settings;
	}

	if ( ! isset( $settings[ $key ] ) ) {
		return;
	}

	return $settings[ $key ];
}

function wp_verifiy_authenticity_of_plugin_core() {
	if ( wo_is_dev() ) {
		return;
	}
	if ( WOCHECKSUM != strtoupper( md5_file( __FILE__ ) ) ) {
		function wo_incompatibility_with_wp_version() {
			?>
            <div class="notice notice-error">
                <p><strong>You are at risk!</strong> WP OAuth Server is not genuine. Please contact info@wp-oauth.com
                    immediately.</p>
            </div>
			<?php
		}

		add_action( 'admin_notices', 'wo_incompatibility_with_wp_version' );
	}
}

add_action( 'init', 'wp_verifiy_authenticity_of_plugin_core' );

/**
 * Returns if the core is valid
 * @return [type] [description]
 */
function wo_is_core_valid() {
	if ( WOCHECKSUM != strtoupper( md5_file( __FILE__ ) ) ) {
		return false;
	}

	return true;
}

/**
 * Retrieve the license status
 * @return String Valid|Invalid
 */
function license_status() {
	$options = get_option( 'wo_options' );
	$status  = isset( $options['license_status'] ) ? $options['license_status'] : '';
	switch ( $status ) {
		case 'invalid':
			echo 'Invalid. Activate your license now.';
			break;
		case 'valid':
			echo 'Valid';
			break;
	}
}

/**
 * Retrieves the license information
 * @return Array License Information
 */
function wo_license_information() {
	return get_option( "wo_license_information" );
}

/**
 * Retrieves the license key
 * @return [type] [description]
 */
function wo_license_key() {
	return get_option( "wo_license_key" );
}

/**
 * Determine is environment is development
 * @return [type] [description]
 *
 * @todo Need to make this more extendable by using __return_false
 */
function wo_is_dev() {
	return _WO()->env == 'development' ? true : false;
}

/**
 * Check if the server is using a secure connection or not.
 * @return bool
 */
function wo_is_protocol_secure() {
	$isSecure = false;
	if ( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] == 'on' ) {
		$isSecure = true;
	} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' || ! empty( $_SERVER['HTTP_X_FORWARDED_SSL'] ) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on' ) {
		$isSecure = true;
	}

	return $isSecure;
}

/**
 * Setup the admin tabs as needed
 *
 * @param $page
 * @param $tabs
 * @param $location
 * @param $default
 * @param null $current
 */
function wo_admin_setting_tabs( $page, $tabs, $location, $default, $current = null ) {
	if ( is_null( $current ) ) {
		if ( isset( $_GET['tab'] ) ) {
			$current = $_GET['tab'];
		} else {
			$current = $default;
		}
	}
	$content = '';
	$content .= '<h2 class="nav-tab-wrapper">';
	foreach ( $tabs as $tab => $tabname ) {
		if ( $current == $tab ) {
			$class = ' nav-tab-active';
		} else {
			$class = '';
		}
		$content .= '<a class="nav-tab' . $class . '" href="?page=' .
		            $page . '&tab=' . $tab . '">' . $tabname . '</a>';
	}
	$content .= '</h2>';
	echo $content;
	if ( ! $current ) {
		$current = key( $tabs );
	}
	require_once( $location . $current . '.php' );

	return;
}

function wo_display_settings_tabs() {
	$tabs         = apply_filters( 'wo_server_status_tabs', array(
		'general' => 'General Information',
		//'support' => 'Support',
		//'license' => 'License(s)',
		//'misc'    => 'Misc'
	) );
	$settings_tab = 'wo_server_status';
	echo wo_admin_setting_tabs( $settings_tab, $tabs, dirname( __FILE__ ) . '/admin/tabs/', 'general', null );
}

// Public Functions.
require_once( dirname( __FILE__ ) . '/public.php' );