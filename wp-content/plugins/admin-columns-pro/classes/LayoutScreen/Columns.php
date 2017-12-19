<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_LayoutScreen_Columns {

	const PREFERENCE_KEY = 'cpac_layout_columns';

	public function __construct() {

	    // Init
		add_action( 'ac/settings/list_screen', array( $this, 'set_layout_on_settings_screen' ) );

		// Requests
		add_action( 'ac/settings/handle_request', array( $this, 'handle_request' ) );
		add_action( 'ac/restore_all_columns', array( $this, 'restore_all' ) );

		// HTML
		add_action( 'ac/settings/sidebox', array( $this, 'settings' ) );
		add_action( 'ac/settings/after_title', array( $this, 'menu' ) );
		add_action( 'ac/settings/after_menu', array( $this, 'layout_help' ) );

		add_action( 'admin_init', array( $this, 'select2_conflict_fix' ), 1 );
		add_filter( 'ac/settings/list_screen_message_label', array( $this, 'add_layout_to_label' ), 10, 2 );
		add_filter( 'ac/read_only_message', array( $this, 'read_only_message' ), 10, 2 );
		add_action( 'ac/settings/scripts', array( $this, 'admin_scripts' ) );

		// Ajax
		add_action( 'wp_ajax_acp_layout_get_users', array( $this, 'ajax_get_users' ) );
		add_action( 'wp_ajax_acp_update_layout', array( $this, 'ajax_update_layout' ) );
	}

	/**
	 * @param string        $message
	 * @param AC_ListScreen $list_screen
	 *
	 * @return string
	 */
	public function read_only_message( $message, $list_screen ) {
		if ( $list_screen->is_read_only() ) {
			$message .= '<br/>' . sprintf( __( 'You can make an editable copy of this set by clicking %s on the right.', 'codepress-admin-columns' ), '"<strong>' . $this->get_add_button_test() . '</strong>"' );
		}

		return $message;
	}

	/**
	 * @param AC_ListScreen $list_screen
	 */
	public function set_layout_on_settings_screen( $list_screen ) {

		// Preference
		$layout_id = $this->get_layout_preference( $list_screen->get_key() );

		// User selected. Do not use filter_input, because an empty layout can also be valid.
		if ( isset( $_GET['layout_id'] ) ) {
			$layout_id = $_GET['layout_id'];
		}

		$layouts = ACP()->layouts( $list_screen );

		// First one
		if ( ! $layouts->exists( $layout_id ) ) {
			$layout_id = $layouts->get_first_layout_id();
		}

		$this->set_layout_preference( $list_screen->get_key(), $layout_id );

		$list_screen->set_layout_id( $layout_id );
	}

	/**
	 * @param AC_Admin_Page_Columns $screen
	 */
	public function handle_request( $screen ) {

		switch ( filter_input( INPUT_POST, 'acp_action' ) ) {

			case 'create_layout' :
				if ( ! $this->verify_nonce( 'create-layout' ) ) {
					return;
				}

				$list_screen = AC()->get_list_screen( filter_input( INPUT_POST, 'list_screen' ) );

				if ( ! $list_screen ) {
					return;
				}

				$list_screen->set_layout_id( filter_input( INPUT_POST, 'layout' ) );

				$layouts = ACP()->layouts( $list_screen );

				// Create default layout
				// This saves the old column setting to a default layout when first
				if ( ! $layouts->get_layouts() ) {
					$layouts->create( array( 'name' => __( 'Original', 'codepress-admin-columns' ) ), true );
				}

				// New layout
				$layout = $layouts->create( array(
					'name'  => filter_input( INPUT_POST, 'layout_name' ),
					'roles' => filter_input( INPUT_POST, 'layout_roles', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY ),
					'users' => filter_input( INPUT_POST, 'layout_users', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY ),
				) );

				if ( ! $layout ) {
					$screen->notice( __( 'Column set not created.', 'codepress-admin-columns' ), 'error' );

					return;
				}

				// Get original columns
				if ( $original_settings = $list_screen->get_settings() ) {
					$list_screen->set_layout_id( $layout->get_id() )->store( $original_settings );
				}

				$this->set_layout_preference( $list_screen->get_key(), $layout->get_id() );

				$list_screen->set_layout_id( $layout->get_id() );
				$list_screen->set_read_only( false );

				$screen->notice( sprintf( __( 'Set %s succesfully created.', 'codepress-admin-columns' ), "<strong>\"" . esc_html( $layout->get_name() ) . "\"</strong>" ), 'updated' );
				break;

			case 'delete_layout' :
				if ( ! $this->verify_nonce( 'delete-layout' ) ) {
					return;
				}

				$list_screen = AC()->get_list_screen( filter_input( INPUT_POST, 'list_screen' ) );

				if ( ! $list_screen ) {
					return;
				}

				$layouts = ACP()->layouts( $list_screen );

				$layout = $layouts->get_layout_by_id( filter_input( INPUT_POST, 'layout_id' ) );

				if ( ! $layout ) {
					AC()->admin()->get_page( 'columns' )->notice( __( "Screen does not exist.", 'codepress-admin-columns' ), 'error' );

					return;
				}

				$layouts->delete( $layout->get_id() );
				$this->delete_layout_preference( $list_screen->get_key() );

				// Re populate
				$layouts->reset();

				$list_screen->set_layout_id( $layouts->get_first_layout_id() );

				$screen->notice( sprintf( __( 'Column set %s succesfully deleted.', 'codepress-admin-columns' ), "<strong>\"" . esc_html( $layout->get_name() ) . "\"</strong>" ), 'updated' );
				break;
		}
	}

	/**
	 * @param string $list_screen
	 * @param string $layout
	 */
	public function set_layout_preference( $list_screen_key, $layout ) {
		if ( is_string( $layout ) ) {
			ac_helper()->user->update_meta_site( self::PREFERENCE_KEY . $list_screen_key, $layout );
		}
	}

	/**
	 * @param string $list_screen
	 *
	 * @return string Layout ID
	 */
	public function get_layout_preference( $list_screen_key ) {
		return ac_helper()->user->get_meta_site( self::PREFERENCE_KEY . $list_screen_key, true );
	}

	/**
	 * @param string $list_screen
	 *
	 * @return string Layout
	 */
	public function delete_layout_preference( $list_screen_key ) {
		return ac_helper()->user->delete_meta_site( self::PREFERENCE_KEY . $list_screen_key );
	}

	/**
	 * @param string $action
	 *
	 * @return bool
	 */
	private function verify_nonce( $action ) {
		return wp_verify_nonce( filter_input( INPUT_POST, '_ac_nonce' ), $action );
	}

	private function nonce_field( $action ) {
		wp_nonce_field( $action, '_ac_nonce', false );
	}

	/**
	 * Delete all stored layouts
	 */
	public function restore_all() {
		global $wpdb;

		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s", $wpdb->esc_like( ACP_Layouts::LAYOUT_KEY ) . '%' ) );
	}

	/**
	 * Admin Scripts
	 */
	public function admin_scripts() {
		wp_deregister_script( 'select2' ); // try to remove any other version of select2

		wp_enqueue_style( 'acp-layouts', $this->get_assets_url() . "css/layouts" . AC()->minified() . ".css", array(), ACP()->get_version() );
		wp_enqueue_style( 'acp-layouts-select2', $this->get_assets_url() . "css/select2.min.css", array(), '4.0.2' );

		wp_register_script( 'acp-layouts-select2', $this->get_assets_url() . "js/select2" . AC()->minified() . ".js", array( 'jquery' ), ACP()->get_version() );
		wp_enqueue_script( 'acp-layouts', $this->get_assets_url() . "js/layouts" . AC()->minified() . ".js", array( 'acp-layouts-select2' ), ACP()->get_version() );

		wp_localize_script( 'acp-layouts', 'acp_layouts', array(
			'roles'  => __( 'Select roles', 'codepress-admin-columns' ),
			'users'  => __( 'Select users', 'codepress-admin-columns' ),
			'_nonce' => wp_create_nonce( 'acp-layout' ),
		) );
	}

	private function get_add_button_test() {
		return __( '+ Add set', 'codepress-admin-columns' );
	}

	/**
	 * @param AC_ListScreen $list_screen
	 */
	public function settings( AC_ListScreen $list_screen ) { ?>
        <div class="sidebox layouts" data-type="<?php echo $list_screen->get_key(); ?>">

            <div class="header">
                <h3>
                    <span class="header-content"><?php _e( 'Column Sets', 'codepress-admin-columns' ); ?></span>
                    <a class="button add-new">
                        <span class="add"><?php echo esc_html( $this->get_add_button_test() ); ?></span>
                        <span class="close"><?php echo esc_html( __( 'Cancel', 'codepress-admin-columns' ) ); ?></span>
                    </a>
                </h3>
            </div>
            <div class="item new">
                <form method="post" action="<?php echo esc_attr( add_query_arg( array( 'list_screen' => $list_screen->get_key() ), AC()->admin()->get_link( 'columns' ) ) ); // without layout id  ?>">

					<?php $this->nonce_field( 'create-layout' ); ?>

                    <input type="hidden" name="acp_action" value="create_layout">
                    <input type="hidden" name="list_screen" value="<?php echo esc_attr( $list_screen->get_key() ); ?>">
                    <input type="hidden" name="layout" value="<?php echo esc_attr( $list_screen->get_layout_id() ); ?>">

                    <div class="body">
                        <div class="row info">
                            <p><?php printf( __( "Create new sets to switch between different column views on the %s screen.", 'codepress-admin-columns' ), $list_screen->get_label() ); ?></p>
                        </div>

						<?php $this->input_rows( $list_screen->get_key() ); ?>

                        <div class="row actions">

							<?php $this->instructions(); ?>

                            <input class="save button-primary" type="submit" value="<?php _e( 'Add', 'codepress-admin-columns' ); ?>">
                        </div>
                    </div>

                </form>
            </div>

			<?php if ( $layouts = ACP()->layouts( $list_screen )->get_layouts() ) : ?>
				<?php foreach ( $layouts as $i => $layout ) : ?>
					<?php $onclick = AC()->use_delete_confirmation() ? ' onclick="return confirm(\'' . esc_attr( addslashes( sprintf( __( "Warning! The %s columns data will be deleted. This cannot be undone. 'OK' to delete, 'Cancel' to stop", 'codepress-admin-columns' ), "'" . $layout->get_name() . "'" ) ) ) . '\');"' : ''; ?>
					<?php $is_current = $list_screen->get_layout_id() == $layout->get_id(); ?>
                    <div class="item layout<?php echo $is_current ? ' current' : ''; ?><?php echo $i === ( count( $layouts ) - 1 ) ? ' last' : ''; ?><?php echo $layout->is_read_only() ? ' read_only' : ''; ?>" data-screen="<?php echo esc_attr( $layout->get_id() ); ?>">
                        <div class="head">
                            <div class="left">
                                <div class="title-div">
                                    <span class="title"><?php echo esc_html( $layout->get_name() ); ?></span>
                                    <span class="description"><?php echo esc_html( $layout->get_title_description() ); ?></span>
                                </div>
                                <div class="actions">
                                    <form method="post" class="delete">

										<?php $this->nonce_field( 'delete-layout' ); ?>

                                        <input type="hidden" name="acp_action" value="delete_layout">
                                        <input type="hidden" name="layout_id" value="<?php echo esc_attr( $layout->get_id() ); ?>">
                                        <input type="hidden" name="list_screen" value="<?php echo esc_attr( $list_screen->get_key() ); ?>">
                                        <input type="submit" class="delete" value="<?php echo esc_attr( __( 'Delete', 'codepress-admin-columns' ) ); ?>"<?php echo $onclick; ?>/>
                                    </form>

									<?php if ( ! $is_current ) : ?>
                                        <span class="pipe">|</span>
                                        <a class="select" href="<?php echo $this->get_edit_link( $list_screen, $layout->get_id() ); ?>">
											<?php _e( 'Select', 'codepress-admin-columns' ); ?>
                                        </a>
									<?php endif; ?>
                                </div>
                            </div>
                            <div class="right">
                                <span class="toggle"></span>
                            </div>
                        </div>

                        <div class="body">

                            <div class="save-message">
								<?php _e( 'Saved', 'codepress-admin-columns' ); ?>
                            </div>

							<?php if ( $layout->is_read_only() ) : ?>
                                <div class="error-notice">
									<?php _e( 'This set is loaded via PHP and can therefore not be edited', 'codepress-admin-columns' ); ?>
                                </div>
							<?php endif; ?>

                            <form method="post">
                                <input type="hidden" name="layout_id" value="<?php echo esc_attr( $layout->get_id() ); ?>">

								<?php $this->input_rows( $list_screen->get_key() . '-' . $layout->get_id(), $layout, $layout->is_read_only() ); ?>

                            </form>
                            <div class="row actions">

								<?php $this->instructions(); ?>

								<?php if ( ! $layout->is_read_only() ) : ?>
                                    <input class="save button-primary" type="submit" value="<?php _e( 'Update', 'codepress-admin-columns' ); ?>">
								<?php endif; ?>
                                <span class="spinner"></span>
                            </div>

                        </div>

                    </div>
				<?php endforeach; ?>
			<?php endif; ?>
        </div>
		<?php
	}

	private function get_assets_url() {
		return ACP()->get_plugin_url() . 'assets/';
	}

	public function layout_help() {
		?>
        <div id="layout-help" class="hidden">
            <h3><?php _e( 'Sets', 'codepress-admin-columns' ); ?></h3>

            <p>
				<?php _e( "Sets allow users to switch between different column views.", 'codepress-admin-columns' ); ?>
            </p>
            <p>
				<?php _e( "Available sets are selectable from the overview screen. Users can have their own column view preference.", 'codepress-admin-columns' ); ?>
            <p>
            <p>
                <img src="<?php echo esc_url( $this->get_assets_url() ); ?>images/layout-selector.png"/>
            </p>
            <p>
                <a href="<?php echo esc_url( ac_get_site_utm_url( 'documentation/how-to/make-multiple-column-sets', 'column-sets' ) ); ?>" target="_blank"><?php _e( 'Online documentation', 'codepress-admin-columns' ); ?></a>
            </p>
        </div>
		<?php
	}

	// Try to prevent older version 3.x of select2 from loading and causing conflicts with 4.x
	public function select2_conflict_fix() {
		if ( AC()->admin()->is_current_page( 'columns' ) ) {
			wp_enqueue_script( 'disable-older-version-select2', $this->get_assets_url() . "js/select2_conflict_fix.js", array(), ACP()->get_version() );
		}
	}

	private function ajax_validate_request() {
		check_ajax_referer( 'acp-layout' );

		if ( ! AC()->user_can_manage_admin_columns() ) {
			wp_die();
		}
	}

	public function ajax_get_users() {
		$this->ajax_validate_request();

		$query_args = array(
			'orderby'        => 'display_name',
			'number'         => 100,
			'search'         => '*' . filter_input( INPUT_POST, 'search' ) . '*',
			'search_columns' => array( 'ID', 'user_login', 'user_nicename', 'user_email', 'user_url' ),
		);

		$options = array();

		$users_query = new WP_User_Query( $query_args );
		if ( $users = $users_query->get_results() ) {
			$names = array();

			foreach ( $users as $user ) {
				$name = ac_helper()->user->get_display_name( $user );

				if ( in_array( $name, $names ) ) {
					$name .= ' (' . $user->user_email . ')';
				}

				// Select2 format
				$options[] = array(
					'id'   => $user->ID,
					'text' => $name,
				);

				// for duplicates
				$names[] = $name;
			}
		}

		wp_send_json_success( $options );
	}

	public function ajax_update_layout() {
		$this->ajax_validate_request();

		if ( ! $list_screen = AC()->get_list_screen( filter_input( INPUT_POST, 'list_screen' ) ) ) {
			wp_die();
		}

		if ( ! $formdata = filter_input( INPUT_POST, 'data' ) ) {
			wp_die();
		}

		parse_str( $formdata, $data );

		if ( ! isset( $data['layout_id'] ) ) {
			wp_die();
		}

		$layout = ACP()->layouts( $list_screen )->update( $data['layout_id'], array(
			'name'  => isset( $data['layout_name'] ) ? $data['layout_name'] : '',
			'roles' => isset( $data['layout_roles'] ) ? $data['layout_roles'] : '',
			'users' => isset( $data['layout_users'] ) ? $data['layout_users'] : '',
		) );

		if ( ! $layout ) {
			wp_die();
		}

		if ( is_wp_error( $layout ) ) {
			wp_send_json_error( $layout->get_error_code() );
		}

		wp_send_json_success( array(
				'title_description' => $layout->get_title_description(),
			)
		);
	}

	/**
	 * @param string        $label
	 * @param AC_ListScreen $list_screen
	 *
	 * @return string
	 */
	public function add_layout_to_label( $label, $list_screen ) {
		if ( $name = ACP()->layouts( $list_screen )->get_layout_name( $list_screen->get_layout_id() ) ) {
			$label = $name;
		}

		return $label;
	}

	/**
	 * @param AC_ListScreen $list_screen
	 * @param               $layout
	 *
	 * @return string
	 */
	private function get_edit_link( $list_screen, $layout ) {
		return esc_url( add_query_arg( array( 'layout_id' => $layout ), $list_screen->get_edit_link() ) );
	}

	/**
	 * @param ACP_Layout[] $layouts
	 */
	private function get_display_layout_list( AC_ListScreen $list_screen ) {
		ob_start();
		$count = 0;
		foreach ( ACP()->layouts( $list_screen )->get_layouts() as $layout ) : ?>
            <li<?php echo $layout->is_read_only() ? ' class="read-only"' : ''; ?> data-screen="<?php echo esc_attr( $layout->get_id() ); ?>">
				<?php echo ( $count++ ) != 0 ? ' | ' : ''; ?>
                <a class="<?php echo $layout->get_id() === $list_screen->get_layout_id() ? 'current' : ''; ?>" href="<?php echo $this->get_edit_link( $list_screen, $layout->get_id() ); ?>"><?php echo esc_html( $layout->get_name() ); ?></a>
            </li>
		<?php endforeach;

		return ob_get_clean();
	}

	/**
	 * @param AC_ListScreen $list_screen
	 */
	public function menu( AC_ListScreen $list_screen ) {
		$list = $this->get_display_layout_list( $list_screen );

		if ( ! $list ) {
			return;
		}
		?>
        <div class="layout-selector">
            <ul class="subsubsub">
                <li class="first"><?php _e( 'Column Sets', 'codepress-admin-columns' ); ?>:</li>
				<?php echo $list; ?>
            </ul>
        </div>
		<?php
	}

	/**
	 * @param                  $attr_id
	 * @param ACP_Layout|false $layout
	 * @param bool             $is_disabled
	 */
	public function input_rows( $attr_id, $layout = false, $is_disabled = false ) {
		?>
        <div class="row name">
            <label for="layout-name-<?php echo $attr_id; ?>">
				<?php _e( 'Name', 'codepress-admin-columns' ); ?>
            </label>
            <div class="input">
                <div class="ac-error-message">
                    <p>
						<?php _e( 'Please enter a name.', 'codepress-admin-columns' ); ?>
                    <p>
                </div>
                <input class="name" id="layout-name-<?php echo $attr_id; ?>" name="layout_name" value="<?php echo $layout ? esc_attr( $layout->get_name() ) : ''; ?>" data-value="<?php echo $layout ? esc_attr( $layout->get_name() ) : ''; ?>" placeholder="<?php _e( 'Enter name', 'codepress-admin-coliumns' ); ?>" <?php echo $is_disabled ? ' disabled="disabled"' : ''; ?>/>
            </div>
        </div>
        <div class="row info">
            <em><?php _e( 'Make this set available only for specific users or roles (optional)', 'codepress-admin-columns' ); ?></em>
        </div>
        <div class="row roles">
            <label for="layout-roles-<?php echo $attr_id; ?>">
				<?php _e( 'Roles', 'codepress-admin-columns' ); ?>
                <span>(<?php _e( 'optional', 'codepress-admin-columns' ); ?>)</span>
            </label>
            <div class="input">
				<?php $this->display_select_roles( $attr_id, $layout ? $layout->get_roles() : false, $is_disabled ); ?>
            </div>
        </div>
        <div class="row users">
            <label for="layout-users-<?php echo $attr_id; ?>">
				<?php _e( 'Users' ); ?>
                <span>(<?php _e( 'optional', 'codepress-admin-columns' ); ?>)</span>
            </label>
            <div class="input">
				<?php $this->display_select_users( $attr_id, $layout ? $layout->get_users() : false, $is_disabled ); ?>
            </div>
        </div>
		<?php
	}

	/**
	 * @param       $attr_id
	 * @param array $current_roles
	 * @param bool  $is_disabled
	 */
	private function display_select_roles( $attr_id, $current_roles = array(), $is_disabled = false ) {
		$grouped_roles = $this->get_grouped_role_names();
		?>
        <select class="roles" name="layout_roles[]" multiple="multiple" id="layout-roles-<?php echo $attr_id; ?>" style="width: 100%;"<?php echo $is_disabled ? ' disabled="disabled"' : ''; ?>>
			<?php foreach ( $grouped_roles as $group => $roles ) : ?>
                <optgroup label="<?php echo esc_attr( $group ); ?>">
					<?php foreach ( $roles as $name => $label ) : ?>
                        <option value="<?php echo esc_attr( $name ); ?>"<?php echo in_array( $name, (array) $current_roles ) ? ' selected="selected"' : ''; ?>><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
                </optgroup>
			<?php endforeach; ?>
        </select>
		<?php
	}

	private function display_select_users( $attr_id, $current_users = array(), $is_disabled = false ) {
		?>
        <select class="users" name="layout_users[]" multiple="multiple" id="layout-users-<?php echo $attr_id; ?>" style="width: 100%;"<?php echo $is_disabled ? ' disabled="disabled"' : ''; ?>>
			<?php if ( $current_users ) : ?>
				<?php foreach ( $current_users as $user_id ) : $user = get_userdata( $user_id ); ?>
                    <option value="<?php echo $user->ID; ?>" selected="selected"><?php echo esc_html( ac_helper()->user->get_display_name( $user ) ); ?></option>
				<?php endforeach; ?>
			<?php endif; ?>
        </select>
		<?php
	}

	private function instructions() {
		?>
        <a class="instructions ac-pointer" rel="layout-help" data-pos="left" data-width="305" data-noclick="1">
			<?php _e( 'Instructions', 'codepress-admin-columns' ); ?>
        </a>
		<?php
	}

	/**
	 * @return array
	 */
	private function get_grouped_role_names() {
		if ( ! function_exists( 'get_editable_roles' ) ) {
			return array();
		}

		$roles = array();

		foreach ( get_editable_roles() as $name => $role ) {
			$group = 'other';

			// Core roles
			if ( in_array( $name, array( 'super_admin', 'administrator', 'editor', 'author', 'contributor', 'subscriber' ) ) ) {
				$group = __( 'Default', 'codepress-admin-columns' );
			}

			/**
			 * @since 4.0
			 *
			 * @param string $group Role group
			 * @param string $name  Role name
			 */
			$group = apply_filters( 'ac/editing/role_group', $group, $name );

			$roles[ $group ][ $name ] = $role['name'];
		}

		return $roles;
	}

}
