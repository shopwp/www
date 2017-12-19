<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Editing_TableScreen {

	public function __construct() {
		add_action( 'ac/table_scripts', array( $this, 'scripts' ) );

		// Ajax calls
		add_action( 'wp_ajax_acp_editing_column_save', array( $this, 'ajax_column_save' ) );
		add_action( 'wp_ajax_acp_editing_state_save', array( $this, 'ajax_editability_state_save' ) );
		add_action( 'wp_ajax_acp_editing_get_options', array( $this, 'ajax_get_options' ) );
	}

	/**
	 * Register and enqueue scripts and styles
	 *
	 * @since 1.0
	 *
	 * @param AC_ListScreen $list_screen
	 */
	public function scripts( $list_screen ) {

		$columns = $list_screen->get_columns();
		if ( ! $columns ) {
			return;
		}

		$column_data = $this->get_column_data( $columns );
		if ( ! $column_data ) {
			return;
		}

		$column_items = $this->get_column_items( $columns );
		if ( ! $column_items ) {
			return;
		}

		$minified = AC()->minified();
		$plugin_url = ACP()->editing()->get_url();
		$version = ACP()->editing()->get_version();

		// Libraries
		wp_register_script( 'acp-editing-bootstrap', $plugin_url . 'library/bootstrap/bootstrap.min.js', array( 'jquery' ), $version );
		wp_register_script( 'acp-editing-select2', $plugin_url . 'library/select2/select2.min.js', array( 'jquery' ), $version );
		wp_register_style( 'acp-editing-select2-css', $plugin_url . 'library/select2/select2.css', array(), $version );
		wp_register_style( 'acp-editing-select2-bootstrap', $plugin_url . 'library/select2/select2-bootstrap.css', array(), $version );
		wp_register_script( 'acp-editing-bootstrap-editable', $plugin_url . "library/bootstrap-editable/js/bootstrap-editable{$minified}.js", array( 'jquery', 'acp-editing-bootstrap' ), $version );
		wp_register_style( 'acp-editing-bootstrap-editable', $plugin_url . 'library/bootstrap-editable/css/bootstrap-editable.css', array(), $version );

		// Main
		wp_register_script( 'acp-editing-table', $plugin_url . 'assets/js/table' . $minified . '.js', array( 'jquery', 'acp-editing-bootstrap-editable' ), $version );
		wp_register_style( 'acp-editing-table', $plugin_url . 'assets/css/table' . $minified . '.css', array(), $version );

		// Allow JS to access the column and item data for this list screen on the edit page
		wp_localize_script( 'acp-editing-table', 'ACP_Editing_Columns', $column_data );
		wp_localize_script( 'acp-editing-table', 'ACP_Editing_Items', $column_items );
		wp_localize_script( 'acp-editing-table', 'ACP_Editing', array(
			'inline_edit' => array(
				'active' => $this->preferences()->set_key( $list_screen->get_key() )->get(),
			),
			// Translations
			'i18n'        => array(
				'select_author' => __( 'Select author', 'codepress-admin-columns' ),
				'edit'          => __( 'Edit' ),
				'redo'          => __( 'Redo', 'codepress-admin-columns' ),
				'undo'          => __( 'Undo', 'codepress-admin-columns' ),
				'delete'        => __( 'Delete', 'codepress-admin-columns' ),
				'download'      => __( 'Download', 'codepress-admin-columns' ),
				'errors'        => array(
					'field_required' => __( 'This field is required.', 'codepress-admin-columns' ),
					'invalid_float'  => __( 'Please enter a valid float value.', 'codepress-admin-columns' ),
					'invalid_floats' => __( 'Please enter valid float values.', 'codepress-admin-columns' ),
				),
				'inline_edit'   => __( 'Inline Edit', 'codepress-admin-columns' ),
				'media'         => __( 'Media', 'codepress-admin-columns' ),
				'image'         => __( 'Image', 'codepress-admin-columns' ),
				'audio'         => __( 'Audio', 'codepress-admin-columns' ),
			),
		) );

		// jQuery
		wp_enqueue_script( 'jquery' );

		// Libraries CSS
		wp_enqueue_style( 'acp-editing-select2-css' );
		wp_enqueue_style( 'acp-editing-select2-bootstrap' );

		// Core
		wp_enqueue_script( 'acp-editing-select2' );
		wp_enqueue_script( 'acp-editing-table' );
		wp_enqueue_style( 'acp-editing-bootstrap-editable' );
		wp_enqueue_style( 'acp-editing-table' );

		// WP Media picker
		wp_enqueue_media();

		// WP Color picker
		wp_enqueue_script( 'wp-color-picker' );
		wp_enqueue_style( 'wp-color-picker' );

		// Translations
		$locale = substr( get_locale(), 0, 2 );

		// Select 2 translations
		if ( file_exists( $this->get_dir() . 'library/select2/select2_locale_' . $locale . '.js' ) ) {
			wp_register_script( 'select2-locale', $plugin_url . 'library/select2/select2_locale_' . $locale . '.js', array( 'jquery' ), $version );
			wp_enqueue_script( 'select2-locale' );
		}

		do_action( 'ac/table_scripts/editing', $list_screen );
	}

	/**
	 * Ajax callback for saving a column
	 *
	 * @since 1.0
	 */
	public function ajax_column_save() {
		check_ajax_referer( 'ac-ajax' );

		// Get ID of entry to edit
		$id = intval( filter_input( INPUT_POST, 'pk' ) );

		if ( ! $id ) {
			$this->ajax_error( __( 'Invalid item ID.', 'codepress-admin-columns' ) );
		}

		$list_screen = AC()->get_list_screen( filter_input( INPUT_POST, 'list_screen' ) );

		if ( ! $list_screen ) {
			$this->ajax_error( __( 'Invalid list screen.', 'codepress-admin-columns' ) );
		}

		$list_screen->set_layout_id( filter_input( INPUT_POST, 'layout' ) );

		/* @var $column AC_Column */
		$column = $list_screen->get_column_by_name( filter_input( INPUT_POST, 'column' ) );

		if ( ! $column ) {
			$this->ajax_error( __( 'Invalid column.', 'codepress-admin-columns' ) );
		}

		if ( ! $column instanceof ACP_Column_EditingInterface ) {
			$this->ajax_error( __( 'Column does not support editing.', 'codepress-admin-columns' ) );
		}

		$model = ACP()->editing()->get_editing_model( $column );

		if ( ! $model->get_strategy()->user_has_write_permission( $id ) ) {
			$this->ajax_error( __( 'User does not have write permissions', 'codepress-admin-columns' ) );
		}

		// Can contain strings and array's
		$value = isset( $_POST['value'] ) ? $_POST['value'] : '';

		/**
		 * Filter for changing the value before storing it to the DB
		 *
		 * @since 4.0
		 *
		 * @param mixed     $value Value send from inline edit ajax callback
		 * @param AC_Column $column
		 * @param int       $id    ID
		 */
		$value = apply_filters( 'acp/editing/save_value', $value, $column, $id );

		// Save
		$save_result = $model->save( $id, $value );

		/**
		 * Hook to allow saving of values by Third Party columns
		 *
		 * @since 4.0
		 *
		 * @param bool      $save_result
		 * @param int       $id Object ID
		 * @param mixed     $value
		 * @param AC_Column $column
		 */
		$save_result = apply_filters( 'acp/editing/save', $save_result, $id, $value, $column );
		$save_result = apply_filters( 'acp/editing/save/' . $column->get_type(), $save_result, $id, $value, $column );

		if ( is_wp_error( $save_result ) ) {
			$this->ajax_error( $save_result->get_error_message() );
		}

		/**
		 * Fires after a inline-edit successfully saved a value
		 *
		 * @since 4.0
		 *
		 * @param AC_Column $column Column instance
		 * @param int       $id     Item ID
		 * @param string    $value  User submitted input
		 */
		do_action( 'acp/editing/saved', $column, $id, $value );

		$display_value = $list_screen->get_display_value_by_column_name( $column->get_name(), $id );

		// Fallback
		if ( ! $display_value && is_string( $save_result ) ) {
			$display_value = $save_result;
		}

		$data = array(
			'rawvalue'  => $value,

			// Display HTML
			'cell_html' => $display_value,
			'row_html'  => $list_screen->get_single_row( $id ) // Mostly for Default columns
		);

		wp_send_json_success( $data );
	}

	/**
	 * Ajax callback for storing user preference of the default state of editability on an overview page
	 *
	 * @since 3.2.1
	 */
	public function ajax_editability_state_save() {
		check_ajax_referer( 'ac-ajax' );

		$preferences = $this->preferences();
		$preferences->set_key( filter_input( INPUT_POST, 'list_screen' ) )->update( filter_input( INPUT_POST, 'value' ) );
		exit;
	}

	/**
	 * AJAX callback for retrieving options for a column
	 * Results can be formatted in two ways: an array of options ([value] => [label]) or
	 * an array of option groups ([group key] => [group]) with [group] being an array with
	 * two keys: label (the label displayed for the group) and options (an array ([value] => [label])
	 * of options)
	 *
	 * @since 1.0
	 *
	 */
	public function ajax_get_options() {
		check_ajax_referer( 'ac-ajax' );

		$column = filter_input( INPUT_GET, 'column' );
		$list_screen = filter_input( INPUT_GET, 'list_screen' );

		if ( ! $column || ! $list_screen ) {
			wp_send_json_error( __( 'Invalid request.', 'codepress-admin-columns' ) );
		}

		$list_screen = AC()->get_list_screen( $list_screen );

		if ( ! $list_screen ) {
			$this->ajax_error( __( 'Invalid list screen.', 'codepress-admin-columns' ) );
		}

		$list_screen->set_layout_id( filter_input( INPUT_GET, 'layout' ) );

		$column = $list_screen->get_column_by_name( $column );

		if ( ! $column ) {
			wp_send_json_error( __( 'Invalid column.', 'codepress-admin-columns' ) );
		}

		if ( ! $column instanceof ACP_Column_EditingInterface ) {
			wp_send_json_error( __( 'Invalid column.', 'codepress-admin-columns' ) );
		}

		$request = array(
			'search'    => filter_input( INPUT_GET, 'searchterm' ),
			'paged'     => absint( filter_input( INPUT_GET, 'page' ) ),
			'object_id' => absint( filter_input( INPUT_GET, 'item_id' ) ),
		);

		$result = $column->editing()->get_ajax_options( $request );

		wp_send_json_success( array(
			'options' => $this->format_js( $result ),
			'more'    => true,
		) );
	}

	/**
	 * @param AC_Column[] $columns
	 */
	private function get_column_data( $columns ) {
		$column_data = array();

		foreach ( $columns as $column ) {

			$model = ACP()->editing()->get_editing_model( $column );

			if ( ! $model || ! $model->is_active() ) {
				continue;
			}

			$data = $model->get_view_settings();

			/**
			 * @since 4.0
			 *
			 * @param array     $data
			 * @param AC_Column $column
			 */
			$data = apply_filters( 'acp/editing/view_settings', $data, $column );
			$data = apply_filters( 'acp/editing/view_settings/' . $column->get_type(), $data, $column );

			if ( false === $data ) {
				continue;
			}

			if ( isset( $data['options'] ) ) {
				$data['options'] = $this->format_js( $data['options'] );
			}

			$column_data[ $column->get_name() ] = array(
				'type'     => $column->get_type(),
				'editable' => $data,
			);
		}

		return $column_data;
	}

	/**
	 * @param AC_Column[] $columns
	 * @param int[]       $rows
	 *
	 * @return array
	 */
	public function get_column_items( $columns, $rows = array() ) {
		$items = array();

		foreach ( $columns as $column ) {
			$editing = ACP()->editing()->get_editing_model( $column );

			if ( ! $editing || ! $editing->is_active() ) {
				continue;
			}

			if ( ! $rows ) {
				$rows = wp_cache_get( $column->get_list_screen()->get_storage_key(), 'editable-rows' );

				if ( ! $rows ) {
					$rows = $editing->get_strategy()->get_rows();
					wp_cache_add( $column->get_list_screen()->get_storage_key(), $editing->get_strategy()->get_rows(), 'editable-rows', 60 );
				}
			}

			$view_data = $editing->get_view_settings();

			// Uses keys as revisions
			$store_values = isset( $view_data['store_values'] ) && true === $view_data['store_values'];

			// Uses a single key as revision
			$store_single_value = isset( $view_data['store_single_value'] ) && true === $view_data['store_single_value'];

			// Editable column value for each row (object)
			foreach ( $rows as $id ) {
				$value = $editing->get_edit_value( $id );

				/**
				 * Filter the raw value, used for editability, for a column
				 *
				 * @since 4.0
				 *
				 * @param mixed     $value  Column value used for editability
				 * @param int       $id     Post ID to get the column editability for
				 * @param AC_Column $column Column object
				 */
				$value = apply_filters( 'acp/editing/value', $value, $id, $column );
				$value = apply_filters( 'acp/editing/value/' . $column->get_type(), $value, $id, $column );

				// Not editable
				if ( null === $value ) {
					continue;
				}

				if ( is_array( $value ) && empty( $value ) ) {
					$value = '';
				}

				if ( false === $value ) {
					$value = '';
				}

				// Revisions
				$revisions = $value;

				// Use keys as revision
				if ( is_array( $value ) && ! $store_values ) {
					$revisions = array_keys( $value );
				}

				// USe single key as revision
				if ( $store_single_value && $value ) {
					$revisions = key( $value );
				}

				$items[ $id ]['ID'] = $id;
				$items[ $id ]['columndata'][ $column->get_name() ] = array(

					// Revision needs to be an array in array. For example: ACA_Types_Editing_Repeatable_File.
					'revisions'        => array( $revisions ),
					'formattedvalue'   => $value,
					'current_revision' => 0,
				);
			}
		}

		return $items;
	}

	/**
	 * @since 4.0
	 */
	private function get_dir() {
		return ACP()->editing()->get_dir();
	}

	/**
	 * @param string $message
	 */
	private function ajax_error( $message ) {
		wp_die( $message, null, 400 );
	}

	/**
	 * Format options to be in JS
	 *
	 * @since 1.0
	 *
	 * @param array $options List of options, possibly with option groups
	 *
	 * @return array Formatted option list
	 */
	private function format_js( $list ) {
		$options = array();

		if ( $list ) {
			foreach ( $list as $index => $option ) {
				if ( is_array( $option ) && isset( $option['options'] ) ) {
					$option['options'] = $this->format_js( $option['options'] );
					$options[] = $option;
				} else {
					$options[] = array(
						'value' => $index,
						'label' => html_entity_decode( $option ),
					);
				}
			}
		}

		return $options;
	}

	/**
	 * Get an instance of preferences for the current user
	 *
	 * @return ACP_Editing_Preferences
	 */
	private function preferences() {
		return new ACP_Editing_Preferences();
	}

}
