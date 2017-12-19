<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 4.0
 */
class ACP_Filtering_Addon {

	/**
	 * @var ACP_Filtering_Helper
	 */
	private $helper;

	/**
	 * @var AC_TableScreen
	 */
	private $table_screen;

	public function __construct() {
		AC()->autoloader()->register_prefix( 'ACP_Filtering', $this->get_dir() . 'classes' );

		$this->helper = new ACP_Filtering_Helper();

		$this->table_screen = new ACP_Filtering_TableScreen();

		add_action( 'ac/column/settings', array( $this, 'settings' ) );
		add_action( 'ac/settings/scripts', array( $this, 'settings_scripts' ) );
	}

	/**
	 * @since 4.0
	 */
	public function get_url() {
		return plugin_dir_url( __FILE__ );
	}

	/**
	 * @return string
	 */
	public function get_dir() {
		return plugin_dir_path( __FILE__ );
	}

	/**
	 * @since 4.0
	 */
    public function get_version() {
		return ACP()->get_version();
	}

	public function helper() {
		return $this->helper;
	}

	public function table_screen() {
        return $this->table_screen;
	}

	/**
	 * @param AC_Column $column
	 *
	 * @return ACP_Filtering_Model|false
	 */
	public function get_filtering_model( $column ) {
		if ( ! $column instanceof ACP_Column_FilteringInterface ) {
			return false;
		}

		$model = $column->filtering();

		switch ( $column->get_list_screen()->get_meta_type() ) {
			case 'post' :
				$model->set_strategy( new ACP_Filtering_Strategy_Post( $model ) );

				break;
			case 'user' :
				$model->set_strategy( new ACP_Filtering_Strategy_User( $model ) );

				break;
			case 'comment' :
				$model->set_strategy( new ACP_Filtering_Strategy_Comment( $model ) );

				break;
			default :
				return false;
		}

		return $model;
	}

	public function settings_scripts() {
		wp_enqueue_style( 'acp-filtering-settings', $this->get_url() . '/assets/css/settings' . AC()->minified() . '.css', array(), $this->get_version() );
		wp_enqueue_script( 'acp-filtering-settings', $this->get_url() . '/assets/js/settings' . AC()->minified() . '.js', array( 'jquery' ), $this->get_version() );
	}

	/**
	 * Register field settings for filtering
	 *
	 * @param AC_Column $column
	 */
	public function settings( $column ) {
		if ( $model = $this->get_filtering_model( $column ) ) {
			$model->register_settings();
		}
	}

}
