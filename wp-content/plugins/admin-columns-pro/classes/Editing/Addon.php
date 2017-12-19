<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main Inline Edit Addon plugin class
 *
 * @since 1.0
 */
class ACP_Editing_Addon {

	/**
	 * @var ACP_Editing_Helper
	 */
	private $helper;

	/**
	 * @var ACP_Editing_TableScreen
	 */
	private $table_screen;

	/**
	 * @since 4.0
	 */
	function __construct() {
		AC()->autoloader()->register_prefix( 'ACP_Editing', $this->get_dir() . 'classes' );

		// Settings screen

		add_action( 'ac/column/settings', array( $this, 'register_column_settings' ) );
		add_action( 'ac/settings/general', array( $this, 'register_general_settings' ) );
		add_action( 'ac/settings/scripts', array( $this, 'settings_scripts' ) );

		// Table screen
		$this->table_screen = new ACP_Editing_TableScreen();
	}

	public function table_screen() {
		return $this->table_screen;
	}

	/**
	 * @since 4.0
	 */
	public function get_version() {
		return ACP()->get_version();
	}

	/**
	 * @since 4.0
	 */
	public function get_dir() {
		return plugin_dir_path( __FILE__ );
	}

	/**
	 * @since 4.0
	 */
	public function get_url() {
		return plugin_dir_url( __FILE__ );
	}

	public function helper() {
		if ( null === $this->helper ) {
			$this->helper = new ACP_Editing_Helper();
		}

		return $this->helper;
	}

	/**
	 * @param AC_Column $column
	 *
	 * @return ACP_Editing_Model|false
	 */
	public function get_editing_model( $column ) {
		if ( ! $column instanceof ACP_Column_EditingInterface ) {
			return false;
		}

		$model = $column->editing();

		switch ( $column->get_list_screen()->get_meta_type() ) {
			case 'post' :
				$model->set_strategy( new ACP_Editing_Strategy_Post( $model ) );

				break;
			case 'user' :
				$model->set_strategy( new ACP_Editing_Strategy_User( $model ) );

				break;
			case 'comment' :
				$model->set_strategy( new ACP_Editing_Strategy_Comment( $model ) );

				break;
			case 'term' :
				$model->set_strategy( new ACP_Editing_Strategy_Taxonomy( $model ) );

				break;
			case 'site' :
				$model->set_strategy( new ACP_Editing_Strategy_Site( $model ) );

				break;
			default :
				return false;
		}

		return apply_filters( 'acp/editing/model', $model );
	}

	/**
	 * @since 3.1.2
	 *
	 * @param AC_Admin_Page_Settings $settings
	 */
	public function register_general_settings( $settings ) {
		$settings->single_checkbox( array(
			'name'         => 'custom_field_editable',
			'label'        => __( 'Enable inline editing for Custom Fields. Default is <code>off</code>', 'codepress-admin-columns' ),
			'instructions' => sprintf(
				'<p>%s</p><p>%s</p>',
				__( 'Inline edit will display all the raw values in an editable text field.', 'codepress-admin-columns' ),
				sprintf(
					__( "Please read <a href='%s'>our documentation</a> if you plan to use these fields.", 'codepress-admin-columns' ),
					ac_get_site_utm_url( 'documentation', 'general-settings' ) . 'faq/enable-inline-editing-custom-fields/'
				)
			),
		) );
	}

	/**
	 * @since 4.0
	 */
	public function settings_scripts() {
		wp_enqueue_style( 'acp-editing-settings', $this->get_url() . 'assets/css/settings' . AC()->minified() . '.css', array(), $this->get_version() );
	}

	/**
	 * Register setting for editing
	 *
	 * @param AC_Column|ACP_Column_EditingInterface $column
	 */
	public function register_column_settings( $column ) {
		if ( $model = $this->get_editing_model( $column ) ) {
			$model->register_settings();
		}
	}

}
