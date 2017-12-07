<?php
if ( isset( $_POST['create_client'] ) && wp_verify_nonce( $_POST['nonce'], 'create_client' ) ) {
	$client = wo_insert_client( $_POST );
	wp_safe_redirect( admin_url( 'admin.php?page=wo_edit_client&id=' . $client ) );
}