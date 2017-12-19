<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sorting Addon class
 *
 * @since 1.0
 */
class ACP_Sorting_Addon {

	const OPTIONS_KEY = 'ac_sorting';

	/**
	 * @since 1.0
	 */
	public function __construct() {
		AC()->autoloader()->register_prefix( 'ACP_Sorting', $this->get_dir() . 'classes' );

		// Column

		add_action( 'ac/column/settings', array( $this, 'register_column_settings' ) );

		// Settings screen

		add_action( 'ac/settings/general', array( $this, 'add_settings' ) );
		add_filter( 'ac/settings/groups', array( $this, 'settings_group' ), 15 );
		add_action( 'ac/settings/group/sorting', array( $this, 'settings_display' ) );

		add_action( 'admin_init', array( $this, 'handle_settings_request' ) );

		// Table screen

		add_action( 'ac/table_scripts', array( $this, 'table_scripts' ) );
		add_action( 'wp_ajax_acp_reset_sorting', array( $this, 'ajax_reset_sorting' ) );

		// After filtering

		add_action( 'ac/table/list_screen', array( $this, 'init_sorting_preference' ), 11 );
		add_action( 'ac/table/list_screen', array( $this, 'handle_sorting' ), 11 );
		add_action( 'ac/table/list_screen', array( $this, 'save_sorting_preference' ), 12 );
	}

	/**
	 * Returns the version of this addon
	 *
	 * @since 4.0
	 * @return string Version
	 */
	public function get_version() {
		return ACP()->get_version();
	}

	/**
	 * @return string
	 */
	public function get_dir() {
		return plugin_dir_path( __FILE__ );
	}

	/**
	 * Returns the url of this addon
	 *
	 * @since 4.0
	 * @return string
	 */
	public function get_url() {
		return plugin_dir_url( __FILE__ );
	}

	/**
	 * Get an instance of preferences for the current user
	 *
	 * @return ACP_Sorting_Preferences
	 */
	public function preferences() {
		return new ACP_Sorting_Preferences();
	}

	/**
	 * @since 4.0
	 *
	 * @param AC_ListScreen $list_screen
	 */
	public function handle_sorting( AC_ListScreen $list_screen ) {

		/**
		 * @see WP_List_Table::get_column_info
		 * */
		add_filter( 'manage_' . $list_screen->get_screen_id() . '_sortable_columns', array( $this, 'add_sortable_headings' ) );

		// Handle sorting request
		foreach ( $list_screen->get_columns() as $column ) {
			$model = $this->get_sorting_model( $column );

			if ( ! $model ) {
				continue;
			}

			if ( ! $model->is_active() ) {
				continue;
			}

			if ( $this->get_orderby() !== $this->get_sorting_label( $column ) ) {
				continue;
			}

			$list_screen = $column->get_list_screen();

			switch ( true ) {

				case $list_screen instanceof AC_ListScreenPost :
					add_action( 'pre_get_posts', array( $model->get_strategy(), 'handle_sorting_request' ) );

					break;
				case $list_screen instanceof AC_ListScreen_User :
					add_action( 'pre_get_users', array( $model->get_strategy(), 'handle_sorting_request' ) );

					break;
				case $list_screen instanceof AC_ListScreen_Comment :
					add_action( 'pre_get_comments', array( $model->get_strategy(), 'handle_sorting_request' ) );

					break;
			}
		}
	}

	/**
	 * Get request var from $_GET
	 *
	 * Don't use filter_input(): $_GET is managed by Admin Columns and filter_input() uses the request headers
	 *
	 * @return false|string
	 */
	private function get_request_var( $key ) {
		return isset( $_GET[ $key ] ) ? $_GET[ $key ] : false;
	}

	private function get_orderby() {
		return $this->get_request_var( 'orderby' );
	}

	private function get_order() {
		return $this->get_request_var( 'order' );
	}

	/**
	 * Sanitizes label so it matches the label sorting url.
	 *
	 * @since 1.0
	 *
	 * @param string $label
	 *
	 * @return string Sanitized string
	 */
	public function get_sorting_label( AC_Column $column ) {

		// Make display label compatible with sorting label in the URL
		if ( $column instanceof ACP_Column_SortingInterface ) {
			return $column->get_name();
		}

		if ( $orderby = $this->is_native_sortable( $column ) ) {
			return $orderby;
		}

		return false;
	}

	/**
	 * Is this column native sortable
	 *
	 * @param AC_Column $column
	 *
	 * @return false|string Orderby parameter that will be used in the query string
	 */
	public function is_native_sortable( AC_Column $column ) {
		$native_sortables = $this->get_native_sortables( $column->get_list_screen() );

		if ( ! isset( $native_sortables[ $column->get_type() ] ) ) {
			return false;
		}

		return $native_sortables[ $column->get_type() ];
	}

	/**
	 * @param AC_ListScreen $list_screen
	 *
	 * @return array|false Sortable columns names
	 */
	private function get_default_sortable_columns( AC_ListScreen $list_screen ) {
		$column_info = $list_screen->get_list_table()->get_column_info();

		if ( empty( $column_info[2] ) ) {
			return false;
		}

		return array_keys( $column_info[2] );
	}

	/**
	 * @param AC_ListScreen $list_screen
	 */
	private function get_native_sortables( AC_ListScreen $list_screen ) {
		$native_sortables = $this->get_stored_default_sortable_columns( $list_screen->get_key() );

		if ( ! $native_sortables ) {
			$native_sortables = $this->get_default_sortable_columns( $list_screen );
		}

		return $native_sortables;
	}

	/**
	 * @since 1.0
	 *
	 * @param array $columns Column name or label
	 *
	 * @return array Column name or Sanitized Label
	 */
	public function add_sortable_headings( $sortable_columns ) {
		$list_screen = AC()->table_screen()->get_current_list_screen();

		if ( ! $list_screen ) {
			return $sortable_columns;
		}

		// Stores the default columns on the listings screen
		if ( ! AC()->is_doing_ajax() && AC()->user_can_manage_admin_columns() ) {
			$this->store_default_sortable_columns( $list_screen->get_key(), $sortable_columns );
		}

		if ( ! $list_screen->get_settings() ) {
			return $sortable_columns;
		}

		$columns = $list_screen->get_columns();

		if ( ! $columns ) {
			return $sortable_columns;
		}

		// Columns that are active and have enabled sort will be added to the sortable headings.
		foreach ( $columns as $column ) {
			if ( $model = $this->get_sorting_model( $column ) ) {

				// Custom column
				if ( $model->is_active() ) {
					$sortable_columns[ $column->get_name() ] = $this->get_sorting_label( $column );
				}
			} elseif ( isset( $sortable_columns[ $column->get_name() ] ) ) {

				// Native column
				$setting = $column->get_setting( 'sort' );
				if ( $setting instanceof ACP_Sorting_Settings && ! $setting->is_active() ) {
					unset( $sortable_columns[ $column->get_name() ] );
				}
			}
		}

		return $sortable_columns;
	}

	/**
	 * Hide or show empty results
	 *
	 * @since 4.0
	 * @return boolean
	 */
	public function show_all_results() {
		return AC()->admin()->get_general_option( 'show_all_results' );
	}

	/**
	 * @param AC_Admin_Page_Settings $settings
	 */
	public function add_settings( $settings ) {
		$settings->single_checkbox( array(
			'name'  => 'show_all_results',
			'label' => __( "Show all results when sorting.", 'codepress-admin-columns' ) . ' ' . $settings->get_default_text( 'off' ),
		) );
	}

	/**
	 * @param AC_Column $column
	 *
	 * @return ACP_Sorting_Model|false
	 */
	public function get_sorting_model( $column ) {
		if ( ! $column instanceof ACP_Column_SortingInterface ) {
			return false;
		}

		$model = $column->sorting();

		switch ( $column->get_list_screen()->get_meta_type() ) {
			case 'post' :
				$model->set_strategy( new ACP_Sorting_Strategy_Post( $model ) );

				break;
			case 'user' :
				$model->set_strategy( new ACP_Sorting_Strategy_User( $model ) );

				break;
			case 'comment' :
				$model->set_strategy( new ACP_Sorting_Strategy_Comment( $model ) );

				break;
			default :
				return false;
		}

		return $model;
	}

	/**
	 * @param string $list_screen_key
	 * @param string $column_names
	 */
	private function store_default_sortable_columns( $list_screen_key, $column_names ) {
		update_option( self::OPTIONS_KEY . '_' . $list_screen_key . "_default", $column_names );
	}

	/**
	 * Get default sortable headings
	 *
	 * @param $list_screen_key
	 *
	 * @return false|array [ column_name => order_by ] Sortable column names
	 */
	private function get_stored_default_sortable_columns( $list_screen_key ) {
		return get_option( self::OPTIONS_KEY . '_' . $list_screen_key . "_default" );
	}

	/**
	 * Register field settings for sorting
	 *
	 * @param AC_Column $column
	 */
	public function register_column_settings( $column ) {

		// Custom columns
		if ( $model = $this->get_sorting_model( $column ) ) {
			$model->register_settings();
		}

		// Native columns
		if ( $this->is_native_sortable( $column ) ) {
			$setting = new ACP_Sorting_Settings( $column );
			$setting->set_default( 'on' );

			$column->add_setting( $setting );
		}
	}

	/**
	 * Callback for the settings page to add settings for sorting
	 *
	 */
	public function settings_group( $groups ) {
		if ( isset( $groups['sorting'] ) ) {
			return $groups;
		}

		$groups['sorting'] = array(
			'title'       => __( 'Sorting Preferences', 'codepress-admin-columns' ),
			'description' => __( 'This will reset the sorting preference for all users.', 'codepress-admin-columns' ),
		);

		return $groups;
	}

	/**
	 * Callback for the settings page to display settings for sorting
	 *
	 */
	public function settings_display() {
		?>
		<form action="" method="post">
			<?php wp_nonce_field( 'reset-sorting-preference', '_acnonce' ); ?>
			<input type="submit" class="button" value="<?php _e( 'Reset sorting preferences', 'codepress-admin-columns' ); ?>">
		</form>
		<?php
	}

	/**
	 * Reset all sorting preferences for all users
	 *
	 */
	public function handle_settings_request() {
		if ( ! wp_verify_nonce( filter_input( INPUT_POST, '_acnonce' ), 'reset-sorting-preference' ) || ! AC()->user_can_manage_admin_columns() ) {
			return;
		}

		global $wpdb;
		$wpdb->query( "DELETE FROM {$wpdb->usermeta} WHERE meta_key LIKE 'ac_sortedby_%';" );

		AC()->notice( __( 'All sorting preferences have been reset.', 'codepress-admin-columns' ) );

	}

	/**
	 * @param AC_ListScreen $list_screen
	 *
	 * @return array|false
	 */
	private function get_sorting_preference( $list_screen ) {
		$preference = $this->preferences()->set_key( $list_screen->get_storage_key() )->get();

		if ( empty( $preference['orderby'] ) || ! $list_screen->get_column_by_name( $preference['orderby'] ) ) {
			return false;
		}

		return $preference;
	}

	/**
	 * When you revisit a page, set the orderby variable so WordPress prints the columns headers properly
	 *
	 * @param AC_ListScreen $list_screen
	 *
	 * @since 4.0
	 */
	public function init_sorting_preference( $list_screen ) {

		// Do not apply any preferences when no columns are stored
		if ( ! $list_screen->get_settings() ) {
			return;
		}

		$preference = $this->get_sorting_preference( $list_screen );

		// Only load when a preference is set for this screen and no orderby is set
		if ( empty( $preference['orderby'] ) || empty( $preference['order'] ) || filter_input( INPUT_GET, 'orderby' ) ) {
			return;
		}

		// Set $_GET and $_REQUEST (used by WP_User_Query)
		$_REQUEST['orderby'] = $_GET['orderby'] = $preference['orderby'];
		$_REQUEST['order'] = $_GET['order'] = $preference['order'];
	}

	/**
	 * When the orderby (and order) are set, save the preference
	 *
	 * @param AC_ListScreen $list_screen
	 *
	 * @since 4.0
	 */
	public function save_sorting_preference( $list_screen ) {
		if ( $orderby = $this->get_orderby() ) {
			$this->preferences()->set_key( $list_screen->get_storage_key() )->update( $orderby, $this->get_order() );
		}
	}

	/**
	 * @since 1.0
	 *
	 * @param $list_screen AC_ListScreen
	 */
	public function table_scripts( $list_screen ) {
		wp_enqueue_script( 'acp-sorting', $this->get_url() . 'assets/js/table' . AC()->minified() . '.js', array( 'jquery' ), $this->get_version() );

		$preference = $this->get_sorting_preference( $list_screen );

		wp_localize_script( 'acp-sorting', 'ACP_Sorting', array(
			'reset_button' => array(
				'label'   => __( 'Reset sorting', 'codepress-admin-columns' ),
				'orderby' => isset( $preference['orderby'] ) ? $preference['orderby'] : false,
			),
		) );

		wp_enqueue_style( 'acp-sorting', $this->get_url() . 'assets/css/table' . AC()->minified() . '.css', array(), $this->get_version() );
	}

	/**
	 * Ajax reset sorting
	 */
	public function ajax_reset_sorting() {
		check_ajax_referer( 'ac-ajax' );

		$list_screen = AC()->get_list_screen( filter_input( INPUT_POST, 'list_screen' ) );

		if ( ! $list_screen ) {
			wp_die();
		}

		$list_screen->set_layout_id( filter_input( INPUT_POST, 'layout' ) );

		$deleted = $this->preferences()->set_key( $list_screen->get_storage_key() )->delete();

		wp_send_json_success( $deleted );
	}

}
