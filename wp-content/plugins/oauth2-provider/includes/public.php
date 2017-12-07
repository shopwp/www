<?php
/**
 * Public Functions for WP OAuth Server
 *
 * @author Justin Greer  <jusin@justin-greer.com>
 * @package WP OAuth Server
 */

/**
 *
 * @deprecated in favor of wo_public_get_access_token
 */
function wo_get_access_token( $access_token, $return_type = ARRAY_A ) {

	$data = wo_public_get_access_token( $access_token, $return_type );

	return $data;
}

/**
 * Retrieve information about an access token
 *
 * @param $access_token
 * @param string $return_type
 *
 * @return array|bool|null|object|void
 */
function wo_public_get_access_token( $access_token, $return_type = ARRAY_A ) {
	if ( is_null( $access_token ) ) {
		return false;
	}

	global $wpdb;
	$prepare_query = $wpdb->prepare( "
		SELECT *
		FROM {$wpdb->prefix}oauth_access_tokens
		WHERE access_token = %s
		LIMIT 1
		",
		array( $access_token ) );

	$access_token = $wpdb->get_row( $prepare_query, $return_type );
	if ( $access_token ) {
		$expires = strtotime( $access_token['expires'] );
		if ( current_time( 'timestamp' ) > $expires ) {
			return false;
		}
	}

	return $access_token;
}

/**
 * Insert a new OAuth 2 client
 *
 * @param null $client_data
 *
 * @return bool|int|WP_Error
 */
function wo_public_insert_client( $client_data = null ) {

	do_action( 'wo_before_create_client', array( $client_data ) );

	$client_id     = wo_gen_key();
	$client_secret = wo_gen_key();

	$grant_types = isset( $client_data['grant_types'] ) ? $client_data['grant_types'] : array();
	$user_id     = isset( $client_data['user_id'] ) ? intval( $client_data['user_id'] ) : 0;

	$client = array(
		'post_title'     => wp_strip_all_tags( $client_data['name'] ),
		'post_status'    => 'publish',
		'post_author'    => 1,
		'post_type'      => 'wo_client',
		'comment_status' => 'closed',
		'meta_input'     => array(
			'client_id'     => $client_id,
			'client_secret' => $client_secret,
			'grant_types'   => $grant_types,
			'redirect_uri'  => sanitize_text_field( $client_data['redirect_uri'] ),
			'user_id'       => $user_id,
			'scope'         => sanitize_text_field( $client_data['scope'] )
		)

	);

	// Insert the post into the database
	$client_insert = wp_insert_post( $client );
	if ( is_wp_error( $client_insert ) ) {
		return $client_insert->get_error_message();
	}

	return $client_insert;
}
