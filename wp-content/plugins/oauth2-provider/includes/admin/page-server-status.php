<?php
/**
 * Server Status
 *
 */
global $license_error;
$license_error = null;
function wo_server_status_page() {
	if ( isset( $_REQUEST["activate_license"] ) ) {
		$wo_license_key = $_REQUEST['wo_license_key'];
		$api_params     = array(
			'edd_action' => 'activate_license',
			'license'    => $wo_license_key,
			'item_name'  => urlencode( 'WP OAuth Server' ),
			'url'        => home_url()
		);

		// Fix https://github.com/justingreerbbi/wp-oauth-server/issues/1
		$api_args = array(
			'sslverify' => false
		);

		// Send the license request
		$response = wp_remote_get( add_query_arg( $api_params, 'https://wp-oauth.com' ), $api_args );

		//print_r( wp_remote_retrieve_body( $response ) ); exit;
		// Response
		if ( ! is_wp_error( $response ) ) {
			$license_data = json_decode( wp_remote_retrieve_body( $response ) );

			// Check for errors in the JSON.
			if ( $license_data === null || json_last_error() != JSON_ERROR_NONE ) {
				$json_errors = array(
					JSON_ERROR_NONE           => __( 'No error', 'wp-oauth' ),
					JSON_ERROR_DEPTH          => __( 'Maximum stack depth exceeded', 'wp-oauth' ),
					JSON_ERROR_STATE_MISMATCH => __( 'State mismatch (invalid or malformed JSON)', 'wp-oauth' ),
					JSON_ERROR_CTRL_CHAR      => __( 'Control character error, possibly incorrectly encoded', 'wp-oauth' ),
					JSON_ERROR_SYNTAX         => __( 'Syntax error', 'wp-oauth' ),
					JSON_ERROR_UTF8           => __( 'Malformed UTF-8 characters, possibly incorrectly encoded', 'wp-oauth' )
				);

				global $license_error;
				$last_error    = json_last_error();
				$license_error = __( 'JSON ERROR: ', 'wp-oauth' ) . $json_errors[ $last_error ];
			}

			update_option( 'wo_license_key', $wo_license_key );
			update_option( 'wo_license_information', (array) $license_data );

		} else {

			global $license_error;
			$license_error = $response->get_error_message();
		}
	}

	wp_enqueue_style( 'wo_admin' );
	wp_enqueue_script( 'wo_admin' );
	wp_enqueue_script( 'jquery-ui-tabs' );
	?>
    <div class="wrap">
        <h2><?php _e( 'Server Status', 'wp-oauth' ); ?></h2>
        <div class="section group">
            <div class="col span_4_of_6">
				<?php wo_display_settings_tabs(); ?>
            </div>

            <div class="col span_2_of_6 sidebar">
                <div class="module pro-version">
                    <h3>What does the Pro version offer?</h3>
                    <div class="inner">
                        <p>
                            The pro version of this plugin is designed to give you more power over the OAuth 2.0
                            process.
                        </p>

                        <ul>
                            <li>Unlimited Clients</li>
                            <li>All Grant Types</li>
                            <li>OpenID Connect w/ Discovery</li>
                            <li>Mobile Device User Login Capability</li>
                            <li>Awesome Chat & Ticket Support</li>
                        </ul>

                        <h4>Discount Code: "PROME"</h4>
                        <a href="https://wp-oauth.com/downloads/wp-oauth-server/" class="button">
                            Purchase WP OAuth Server
                        </a>
                        <br/><br/>
                    </div>
                </div>

                <div class="module hire-us">
                    <h3>Hire a Developer</h3>
                    <div class="inner">
                        <p>
                            If you are looking for a developer for your project, why not hire the professionals that
                            built this plugin!
                        </p>
                        <p>
                            <strong>Get a Free Quote</strong>
                        </p>
			            <?php
			            $current_user = wp_get_current_user();
			            ?>
                        <form action="https://wp-oauth.com/professional-services-request/">
                            <input type="hidden" name="yourname" placeholder="Enter Your Name" value="<?php echo $current_user->user_firstname; ?>" required/>
                            <input type="hidden" name="email" value="<?php echo $current_user->user_email; ?>"/>
                            <input type="hidden" name="website" value="<?php echo site_url(); ?>"/>

                            <input type="submit" class="button button-primary" value="Request more information"/>
                            <br/><br/>
                            <small>
                                Your information is private and is not shared with anyone other than our development
                                team.
                            </small>
                        </form>
                    </div>
                </div>
            </div>

        </div>

    </div>
	<?php
}