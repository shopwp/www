<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Processes the Add Site button
 *
 * @since       2.4
 * @return      void
*/
function edd_sl_process_add_site() {
	if ( ! wp_verify_nonce( $_POST['edd_add_site_nonce'], 'edd_add_site_nonce' ) ) {
		return;
	}

	if ( ! empty( $_POST['license_id'] ) && empty( $_POST['license'] ) ) {
		// In 3.5, we switched from checking for license_id to just license. Fallback check for backwards compatibility
		$_POST['license'] = $_POST['license_id'];
	}

	$license_id  = absint( $_POST['license'] );
	$license     = new EDD_SL_License( $license_id );
	if ( $license_id !== $license->ID ) {
		return;
	}

	if ( ( is_admin() && ! current_user_can( 'edit_shop_payments'  ) ) || ( ! is_admin() && $license->user_id != get_current_user_id() ) ) {
		return;
	}

	$site_url = sanitize_text_field( $_POST['site_url'] );

	if ( $license->is_at_limit() && ! current_user_can( 'edit_shop_payments' ) ) {
		// The license is at its activation limit so stop and show an error
		wp_safe_redirect( add_query_arg( 'edd_sl_error', 'at_limit' ) ); exit;
	}

	if ( $license->add_site( $site_url ) ) {

		$license->status = 'active';

		if ( is_admin() ) {
			$redirect = admin_url( 'edit.php?post_type=download&page=edd-licenses&view=overview&license=' . $license->ID );
		} else {
			$redirect = remove_query_arg( array( 'edd_action', 'site_url', 'edd_sl_error', '_wpnonce' ) );
		}

	} else {
		$redirect = add_query_arg( 'edd_sl_error', 'error_adding_site' );
	}

	wp_safe_redirect( $redirect ); exit;
}
add_action( 'edd_insert_site', 'edd_sl_process_add_site' );


/**
 * Processes the Deactivate Site button
 *
 * @since       2.4
 * @return      void
*/
function edd_sl_process_deactivate_site() {
	if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'edd_deactivate_site_nonce' ) ) {
		return;
	}

	$license_id = absint( $_GET['license'] );
	$license    =  new EDD_SL_License( $license_id );

	if ( $license_id !== $license->ID ) {
		return;
	}

	if ( ( is_admin() && ! current_user_can( 'edit_shop_payments' ) ) || ( ! is_admin() && $license->user_id != get_current_user_id() ) ) {
		return;
	}

	$site_url = urldecode( $_GET['site_url'] );
	$license->remove_site( $site_url );

	wp_safe_redirect( remove_query_arg( array( 'edd_action', 'site_url', 'edd_sl_error', '_wpnonce' ) ) ); exit;
}
add_action( 'edd_deactivate_site', 'edd_sl_process_deactivate_site' );
