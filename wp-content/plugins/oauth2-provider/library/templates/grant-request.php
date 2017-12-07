<?php
/**
 * Grant Request Template
 *
 * This template is used when asking if the user would like to grant access to the client.
 *
 * @author Justin Greer <justin@justin-greer.com
 * @copyright Justin Greer Interactive, LLC
 *
 * @package WP-Nightly
 */

if ( isset( $_POST['request-grant'] ) ) {
	if ( wp_verify_nonce( $_POST['nonce'], 'grant-request' ) ) {
		$user_response = $_POST['user-response'];
		if ( $user_response == 'allow' ) {
			update_user_meta( get_current_user_id(), 'wo_grant_' . $_REQUEST['client_id'], 'allow' );
		} elseif ( $user_response == 'deny' ) {
			update_user_meta( get_current_user_id(), 'wo_grant_' . $_REQUEST['client_id'], 'deny' );
		}
	}

	// Safely redirect the user back through the request but without prompt
	wp_safe_redirect( site_url( add_query_arg( array( 'prompt' => '' ) ) ) );
}

$client = get_client_by_client_id( $_REQUEST['client_id'] );
?>
<style>
    body {
        background: #ecf0f1;
    }

    .main-wrapper {
        width: 100%;
        max-width: 320px;
        background: #FFFFFF;
        margin: 0 auto;
        padding: 1em;
        box-sizing: content-box;
        position: relative;

        height: 300px;
        position: relative;
        top: 50%;
        transform: translateY(-50%);
    }

    input[type="submit"] {
        border: none;
        outline: none;
        width: 100%;
        display: block;
        text-align: center;
        text-transform: uppercase;
        padding: 1em;
        font-size: 14px;
        cursor: pointer;
    }

    .allow-btn {
        background: #2980b9;
        color: #FFFFFF;
    }

    .request-description {
        margin-bottom: 1.5em;
        display: block;
    }

    .request-notice {
        color: #cccccc;
        position: absolute;
        bottom: 10px;
    }
</style>

<div class="main-wrapper">
    <h2>Allow Access?</h2>
    <p class="request-description"><strong><?php echo $client['post_title']; ?></strong> would like to access and/or
        update your account.</p>
    <form action="" method="post">
		<?php wp_nonce_field( 'grant-request', 'nonce' ); ?>
        <input type="hidden" name="request-grant" value="1"/>
        <input type="hidden" name="user-response" value="allow"/>
        <input class="allow-btn" type="submit" value="Allow"/>
    </form>

    <form action="" method="post">
		<?php wp_nonce_field( 'grant-request', 'nonce' ); ?>
        <input type="hidden" name="request-grant" value="1"/>
        <input type="hidden" name="user-response" value="deny"/>
        <input class="deny-btn" type="submit" value="Deny"/>
    </form>

    <p class="request-notice">You should only grant access to applications you trust with your account information.</p>
</div>

 
 
