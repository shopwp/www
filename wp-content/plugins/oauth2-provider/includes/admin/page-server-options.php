<?php
/**
 * Server Options Page
 */
function wo_server_options_page() {
	wp_enqueue_style( 'wo_admin' );
	wp_enqueue_script( 'wo_admin' );
	wp_enqueue_script( 'jquery-ui-tabs' );

	$scopes = apply_filters( 'wo_scopes', array() );
	add_thickbox();

	$options = wo_setting();
	?>
    <div class="wrap">
        <h2>WP OAuth Server
            <small>
                - CE
            </small>
            <p>
                This area you can control the gloabl settings for WP OAuth Server.
            </p>
        </h2>
        <div class="section group">
            <div class="col span_4_of_6">

                <form method="post" action="options.php">
					<?php settings_fields( 'wo-options-group' ); ?>

                    <div id="wo_tabs">
                        <ul>
                            <li><a href="#general-settings">General Settings</a></li>
                            <li><a href="#advanced-configuration">Advanced Configuration</a></li>
                        </ul>

                        <!-- GENERAL SETTINGS -->
                        <div id="general-settings">
                            <table class="form-table">
                                <tr valign="top">
                                    <th scope="row">OAuth Server Enabled:</th>
                                    <td>
                                        <input type="checkbox" name="wo_options[enabled]"
                                               value="1" <?php echo $options["enabled"] == "1" ? "checked='checked'" : ""; ?> />
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <!-- ADVANCED CONFIGURATION -->
                        <div id="advanced-configuration">

                            <h3>Grant Types
                                <small>(Global)</small>
                                <hr>
                            </h3>
                            <p>Control which Grant Types that the server will accept.</p>
                            <table class="form-table">

                                <tr valign="top">
                                    <th scope="row">Authorization Code:</th>
                                    <td>
                                        <input type="checkbox" name="wo_options[auth_code_enabled]"
                                               value="1" <?php echo $options["auth_code_enabled"] == "1" ? "checked='checked'" : ""; ?> />
                                        <p class="description">HTTP redirects and WP login form when authenticating.</p>
                                    </td>
                                </tr>

                                <tr valign="top">
                                    <th scope="row">Client Credentials:</th>
                                    <td>
                                        <input type="checkbox" name="wo_options[client_creds_enabled]"
                                               value="1" disabled/>
                                        <p class="description">General API access for client. You must a assign a user
                                            to a client. <strong> - Pro Only</strong></p>
                                    </td>
                                </tr>

                                <tr valign="top">
                                    <th scope="row">User Credentials:</th>
                                    <td>
                                        <input type="checkbox" name="wo_options[user_creds_enabled]"
                                               value="1" disabled/>
                                        <p class="description">Great for Mobile Applications and Desktop Software.
                                            <strong> - Pro Only</strong></p>
                                    </td>
                                </tr>

                                <tr valign="top">
                                    <th scope="row">Refresh Tokens:</th>
                                    <td>
                                        <input type="checkbox" name="wo_options[refresh_tokens_enabled]"
                                               value="1" disabled/>
                                        <p class="description">Enable "Refresh Token" Grant Type <strong> - Pro
                                                Only</strong></p>
                                    </td>
                                </tr>

                                <tr valign="top">
                                    <th scope="row">JWT Bearer:</th>
                                    <td>
                                        <input type="checkbox" name="wo_options[jwt_bearer_enabled]"
                                               value="1" disabled/>
                                        <p class="description">Enable "JWT Bearer" Grant Type <strong> - Pro
                                                Only</strong></p>
                                    </td>
                                </tr>

                                <tr valign="top">
                                    <th scope="row">Allow Implicit:</th>
                                    <td>
                                        <input type="checkbox" name="wo_options[implicit_enabled]"
                                               value="1" disabled/>
                                        <p class="description">Enable "Authorization Code (Implicit)" <strong> - Pro
                                                Only</strong></p>
                                    </td>
                                </tr>
                            </table>

                            <h3>Misc Settings
                                <small>(Global)</small>
                                <hr>
                            </h3>
                            <table class="form-table">
                                <tr valign="top">
                                    <th scope="row">Token Length</th>
                                    <td>
                                        <input type="number" name="wo_options[token_length]" min="10" max="100"
                                               value="<?php echo $options["token_length"]; ?>"
                                               placeholder="40"/>
                                        <p class="description">Length of tokens</p>
                                    </td>
                                </tr>

                                <tr valign="top">
                                    <th scope="row">Require Exact Redirect URI:</th>
                                    <td>
                                        <input type="checkbox" name="wo_options[require_exact_redirect_uri]"
                                               value="1" <?php echo $options["require_exact_redirect_uri"] == "1" ? "checked='checked'" : ""; ?> />
                                        <p class="description">Enable if exact redirect URI is required when
                                            authenticating.</p>
                                    </td>
                                </tr>

                                <tr valign="top">
                                    <th scope="row">Enforce State Parameter:</th>
                                    <td>
                                        <input type="checkbox" name="wo_options[enforce_state]"
                                               value="1" <?php echo @$options["enforce_state"] == "1" ? "checked='checked'" : ""; ?>/>
                                        <p class="description">Enable if the "state" parameter is required when
                                            authenticating. </p>
                                    </td>
                                </tr>
                            </table>

                            <!-- OpenID Connect -->
                            <h3>OpenID Connect 1.0a
                                <small>(Global)</small>
                                <hr>
                            </h3>
                            <p>
                                The OpenID Connect 1.0a works with other systems like Drupal and Moodle.
                            </p>
                            <table class="form-table">
                                <tr valign="top">
                                    <th scope="row">Enable OpenID Connect:</th>
                                    <td>
                                        <input type="checkbox" name="wo_options[use_openid_connect]"
                                               value="1" disabled/>
                                        <p class="description">Enable if your server should generate a id_token when
                                            OpenID request is made. <strong> - Pro Only</strong></p>
                                    </td>
                                </tr>

                                <tr valign="top">
                                    <th scope="row">ID Token Lifetime</th>
                                    <td>
                                        <input type="number" name="wo_options[id_token_lifetime]"
                                               value="<?php echo $options["id_token_lifetime"]; ?>" placeholder="3600"/>
                                        <p class="description">How long an id_token is valid (in seconds).</p>
                                    </td>
                                </tr>
                            </table>

                            <h3>Token Lifetimes
                                <small>(Global)</small>
                                <hr>
                            </h3>
                            <p>
                                By default Access Tokens are valid for 1 hour and Refresh Tokens are valid for 24 hours.
                            </p>

                            <table class="form-table">
                                <tr valign="top">
                                    <th scope="row">Access Token Lifetime</th>
                                    <td>
                                        <input type="number" name="wo_options[access_token_lifetime]"
                                               value="<?php echo $options["access_token_lifetime"]; ?>"
                                               placeholder="3600" disabled/>
                                        <p class="description">How long an access token is valid (seconds) - Leave blank
                                            for default (1 hour) <strong> - Pro Only</strong></p>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row">Refresh Token Lifetime</th>
                                    <td>
                                        <input type="number" name="wo_options[refresh_token_lifetime]"
                                               value="<?php echo $options["refresh_token_lifetime"]; ?>"
                                               placeholder="86400" disabled/>
                                        <p class="description">How long a refresh token is valid (seconds) - Leave blank
                                            for default (24 hours) <strong> - Pro Only</strong></p>
                                    </td>
                                </tr>
                            </table>

                        </div>
                        <!-- / END - Advance Configuration Content -->

                    </div>
                    <!-- END - #Tabs Content -->

                    <p class="submit">
                        <input type="submit" class="button-primary" value="<?php _e( 'Save Changes' ) ?>"/>
                    </p>
                </form>

            </div>
            <!-- END- col 4 of 6 -->

            <!-- SIDEBAR -->
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
                            <input type="hidden" name="yourname" placeholder="Enter Your Name"
                                   value="<?php echo $current_user->user_firstname; ?>" required/>
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
        <!-- END OF SECTION -->

    </div>
<?php }