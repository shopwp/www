<?php

/**
 * WPOauth_Admin Class
 * Add admin functionkaity to the backend of WordPress
 */
class WPOAuth_Admin {

	/**
	 * WO Options Name
	 *
	 * @var string
	 */
	protected $option_name = 'wo_options';

	/**
	 * [_init description]
	 *
	 * @return [type] [description]
	 */
	public static function init() {
		add_action( 'admin_init', array( new self, 'admin_init' ) );
		add_action( 'admin_menu', array( new self, 'add_page' ), 1 );
	}

	/**
	 * [admin_init description]
	 *
	 * @return [type] [description]
	 */
	public function admin_init() {
		register_setting( 'wo_options', $this->option_name, array( $this, 'validate' ) );

		require_once( dirname( __FILE__ ) . '/admin/page-server-status.php' );
	}

	/**
	 * [add_page description]
	 */
	public function add_page() {
		add_menu_page( 'OAuth Server', 'OAuth Server', 'manage_options', 'wo_settings', array(
			$this,
			'options_do_page'
		), 'dashicons-groups' );

		add_submenu_page( 'wo_settings', 'OAuth Server', 'OAuth Server', 'manage_options', 'wo_settings', array(
			$this,
			'options_do_page'
		) );

		add_submenu_page( 'wo_settings', 'Server Status', 'Server Status', 'manage_options', 'wo_server_status',
			'wo_server_status_page' );
	}

	/**
	 * loads the plugin styles and scripts into scope
	 *
	 * @return [type] [description]
	 */
	public function admin_head() {
		wp_enqueue_style( 'wo_admin' );
		wp_enqueue_script( 'wo_admin' );
		wp_enqueue_script( 'jquery-ui-tabs' );
	}

	/**
	 * [options_do_page description]
	 *
	 * @return [type] [description]
	 */
	public function options_do_page() {
		$options = get_option( $this->option_name );
		$this->admin_head();
		$scopes = apply_filters( 'WO_Scopes', null );
		error_reporting( 0 );
		add_thickbox();
		?>
		<div class="wrap">
			<h2>WP OAuth Server
				<small>(Free Edition)</small>
				<small> | <?php echo _WO()->version; ?></small>
			</h2>
			<p>
				<strong>This version has limited functionality but is completely free to use.</strong><br/>
				If this is a professional WordPress install, we recommend using
				<a href="https://wp-oauth.com/downloads/wp-oauth-server/"
				   title="Get WP OAuth Server - Pro" target="_blank">the premium version</a>.
			</p>

			<div class="section group">
				<div class="span_10_of_12 col">
					<form method="post" action="options.php">
						<?php settings_fields( 'wo_options' ); ?>
						<div id="wo_tabs">
							<ul>
								<li><a href="#general-settings">General Settings</a></li>
								<li><a href="#advanced-configuration">Advanced Configuration</a></li>
								<li><a href="#clients">Clients</a></li>
							</ul>

							<!-- GENERAL SETTINGS -->
							<div id="general-settings">
								<table class="form-table">
									<tr valign="top">
										<th scope="row">API Enabled:</th>
										<td>
											<input type="checkbox" name="<?php echo $this->option_name ?>[enabled]"
											       value="1" <?php echo $options["enabled"] == "1" ? "checked='checked'"
												: ""; ?> />
											<p class="description">If the API is not enabled, it will present requests
												with an
												"Unavailable" message.</p>
										</td>
									</tr>
								</table>
							</div>

							<!-- ADVANCED CONFIGURATION -->
							<div id="advanced-configuration">
								<h2>Advanced Configuration</h2>

								<h3>Grant Types
									<hr>
								</h3>
								<p>Looking for more grant types? Check out the <a href="https://wp-oauth.com/downloads/wp-oauth-server/" title="upgrade to the premium version" target="_blank"><strong>premium version</strong></a> of WP OAuth Server.</p>
								<table class="form-table">

									<tr valign="top">
										<th scope="row">Authorization Code:</th>
										<td>
											<input type="checkbox"
											       name="<?php echo $this->option_name ?>[auth_code_enabled]"
											       value="1" <?php echo $options["auth_code_enabled"] == "1"
												? "checked='checked'" : ""; ?> />
											<p class="description">HTTP redirects and WP login form when
												authenticating.</p>
										</td>
									</tr>

								</table>

								<h3>Misc Settings
									<hr>
								</h3>
								<table class="form-table">
									<tr valign="top">
										<th scope="row">Key Length</th>
										<td>
											<input type="number"
											       name="<?php echo $this->option_name ?>[client_id_length]"
											       min="10" value="<?php echo $options["client_id_length"]; ?>"/>
											<p class="description">Length of Client ID and Client Secrets when
												generated.</p>
										</td>
									</tr>
									<tr valign="top">
										<th scope="row">Require Exact Redirect URI:</th>
										<td>
											<input type="checkbox"
											       name="<?php echo $this->option_name ?>[require_exact_redirect_uri]"
											       value="1" <?php echo $options["require_exact_redirect_uri"] == "1"
												? "checked='checked'" : ""; ?> />
											<p class="description">Enable if exact redirect URI is required when
												authenticating.</p>
										</td>
									</tr>

									<tr valign="top">
										<th scope="row">Enforce State Parameter:</th>
										<td>
											<input type="checkbox"
											       name="<?php echo $this->option_name ?>[enforce_state]"
											       value="1" <?php echo $options["enforce_state"] == "1"
												? "checked='checked'" : ""; ?>/>
											<p class="description">Enable if the "state" parameter is required when
												authenticating. </p>
										</td>
									</tr>
								</table>
							</div>

							<!-- CLIENTS -->
							<div id="clients">
								<h2>
									Clients
									<a href="<?php echo site_url(); ?>?wpoauthincludes=create&_wpnonce=<?php echo wp_create_nonce( 'wpo-create-client' ); ?>&TB_iframe=true&width=600&height=420"
									   class="add-new-h2 thickbox" title="Add New Client">Add New Client</a>
								</h2>

								<?php
								$wp_list_table = new WO_Table();
								$wp_list_table->prepare_items();
								$wp_list_table->display();
								?>
							</div>

						</div>

						<p class="submit">
							<input type="submit" class="button-primary" value="<?php _e( 'Save Changes' ) ?>"/>
						</p>
					</form>
				</div>

				<!-- SIDEBAR -->
				<div class="span_2_of_12 col sidebar">
					<div class="module">
						<h3>Looks like you are using the free version.</h3>
						<div class="inner">
							<p>Is this a professional website? If so, upgrade to
								<a href="https://wp-oauth.com/downloads/wp-oauth-server/"
								   title="Get WP OAuth Server - Pro" target="_blank">the premium version</a> now.
							</p>
							<a href="https://wp-oauth.com/downloads/wp-oauth-server/" title="Get WP OAuth Server - Pro"
							   target="_blank">
								<img class="fullwidth"
								     src="<?php echo plugins_url( 'assets/images/pro/1.jpg', WPOAUTH_FILE ); ?>"/>
							</a>
						</div>
					</div>

					<div class="module">
						<h3>Services</h3>
						<div class="inner">
							<p>Not sure what to do or if you installed it correctly
								<a href="https://wp-oauth.com/services/" title="Professional Services" target="_blank">
									Let Us Help</a>.
							</p>
							<a href="https://wp-oauth.com/services/" title="Professional Services"
							   target="_blank">
								<img class="fullwidth"
								     src="<?php echo plugins_url( 'assets/images/pro/2.jpg', WPOAUTH_FILE ); ?>"/>
							</a>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * WO options validation
	 *
	 * @param  [type] $input [description]
	 *
	 * @return [type]        [description]
	 */
	public function validate( $input ) {

		// Check box values
		$input["enabled"]                = isset( $input["enabled"] ) ? $input["enabled"] : 0;
		$input["auth_code_enabled"]      = isset( $input["auth_code_enabled"] ) ? $input["auth_code_enabled"] : 0;
		$input["client_creds_enabled"]   = isset( $input["client_creds_enabled"] ) ? $input["client_creds_enabled"] : 0;
		$input["user_creds_enabled"]     = isset( $input["user_creds_enabled"] ) ? $input["user_creds_enabled"] : 0;
		$input["refresh_tokens_enabled"] = isset( $input["refresh_tokens_enabled"] ) ? $input["refresh_tokens_enabled"]
			: 0;
		$input["implicit_enabled"]       = isset( $input["implicit_enabled"] ) ? $input["implicit_enabled"] : 0;

		$input["require_exact_redirect_uri"] = isset( $input["require_exact_redirect_uri"] )
			? $input["require_exact_redirect_uri"] : 0;
		$input["enforce_state"]              = isset( $input["enforce_state"] ) ? $input["enforce_state"] : 0;
		$input["use_openid_connect"]         = isset( $input["use_openid_connect"] ) ? $input["use_openid_connect"] : 0;

		if ( ! isset( $input['id_token_lifetime'] ) ) {
			$input['id_token_lifetime'] = 3600;
		}

		if ( ! isset( $input['access_token_lifetime'] ) ) {
			$input['access_token_lifetime'] = 3600;
		}

		if ( ! isset( $input['refresh_token_lifetime'] ) ) {
			$input['refresh_token_lifetime'] = 86400;
		}

		return $input;
	}
}

WPOAuth_Admin::init();