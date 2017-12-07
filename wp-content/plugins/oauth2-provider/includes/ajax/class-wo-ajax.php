<?php
/**
 * WordPress OAuth Server AJAX functionality
 * @var array
 */
$ajax_events = array(
	'remove_client'     => false,
	'regenerate_secret' => false
);

/** loop though all the ajax events and add then as needed */
foreach ( $ajax_events as $ajax_event => $nopriv ) {
	add_action( 'wp_ajax_wo_' . $ajax_event, 'wo_ajax_' . $ajax_event );
	if ( $nopriv ) {
		add_action( 'wp_ajax_nopriv_wo_' . $ajax_event, 'wo_ajax_' . $ajax_event );
	}
}

/**
 * Remove a client
 * @return [type] [description]
 *
 * @todo Add Ajax referral check here as well.
 */
function wo_ajax_remove_client() {

	// Check the current user caps
	if ( ! current_user_can( 'manage_options' ) ) {
		exit;
	}

	wp_delete_post( $_POST['data'], true );

	print "1";

	exit;
}

/**
 * [wo_ajax_regenerate_secret description]
 * @return [type] [description]
 */
function wo_ajax_regenerate_secret() {

	// Check current user caps
	if ( ! current_user_can( 'manage_options' ) ) {
		exit;
	}

	// Generate new key
	$new_secret = wo_gen_key();

	global $wpdb;
	$action = $wpdb->update( "{$wpdb->prefix}oauth_clients", array( 'client_secret' => $new_secret ), array( 'client_id' => $_POST['data'] ) );

	// if the action was good, return
	if ( $action ) {
		print json_encode( array(
			'error'      => false,
			'new_secret' => $new_secret
		) );
	} else {
		print json_encode( array(
			'error'             => true,
			'error_description' => 'Something went wrong while updating the clients secret'
		) );
	}

	exit;
}