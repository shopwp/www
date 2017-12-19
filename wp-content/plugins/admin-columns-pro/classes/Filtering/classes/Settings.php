<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Filtering_Settings extends AC_Settings_Column
	implements AC_Settings_HeaderInterface {

	/**
	 * @var string 'On' or 'Off'
	 */
	private $filter;

	/**
	 * @var string Top Label
	 */
	private $filter_label;

	protected function set_name() {
		$this->name = 'filter';
	}

	protected function define_options() {
		return array(
			'filter' => 'off', // default Off
			'filter_label',
		);
	}

	public function create_header_view() {
		$filter = $this->get_filter();

		$view = new AC_View( array(
			'title'    => __( 'Enable Filtering', 'codepress-admin-columns' ),
			'dashicon' => 'dashicons-filter',
			'state'    => $filter,
		) );

		$view->set_template( 'settings/header-icon' );

		return $view;
	}

	public function create_view() {

		$filter = $this->create_element( 'radio', 'filter' )
		               ->set_options( array(
			               'on'  => __( 'Yes' ),
			               'off' => __( 'No' ),
		               ) );

		// Main settings
		$view = new AC_View();
		$view->set( 'label', __( 'Filtering', 'codepress-admin-columns' ) )
		     ->set( 'tooltip', __( 'This will make the column support filtering.', 'codepress-admin-columns' ) )
		     ->set( 'setting', $filter );

		$filter_label = $this->create_element( 'text', 'filter_label' )
		                     ->set_attribute( 'placeholder', sprintf( __( "All %s", 'codepress-admin-columns' ), $this->column->get_setting( 'label' )->get_value() ) );

		// Sub settings
		$label_view = new AC_View();
		$label_view->set( 'label', __( 'Top label', 'codepress-admin-columns' ) )
		           ->set( 'tooltip', __( "Set the name of the label in the filter menu", 'codepress-admin-columns' ) )
		           ->set( 'setting', $filter_label )
		           ->set( 'for', $filter_label->get_id() );

		$view->set( 'sections', array( $label_view ) );

		return $view;
	}

	/**
	 * @return string
	 */
	public function get_filter() {
		return $this->filter;
	}

	/**
	 * @param string $filter
	 *
	 * @return $this
	 */
	public function set_filter( $filter ) {
		$this->filter = $filter;

		return $this;
	}

	/**
	 * @return bool True when filter is selected
	 */
	public function is_active() {
		return 'on' === $this->filter;
	}

	/**
	 * @return string
	 */
	public function get_filter_label() {
		return strip_tags( $this->filter_label );
	}

	/**
	 * @param string $filter_label
	 *
	 * @return $this
	 */
	public function set_filter_label( $filter_label ) {
		$this->filter_label = $filter_label;

		return $this;
	}

}
