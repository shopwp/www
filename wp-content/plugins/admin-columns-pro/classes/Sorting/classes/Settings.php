<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Sorting_Settings extends AC_Settings_Column
	implements AC_Settings_HeaderInterface {

	private $sort;

	protected function define_options() {
		return array( 'sort' => 'off' );
	}

	public function create_header_view() {
		$sort = $this->get_sort();

		$view = new AC_View( array(
			'title'    => __( 'Enable Sorting', 'codepress-admin-columns' ),
			'dashicon' => 'dashicons-sort',
			'state'    => $sort,
		) );

		$view->set_template( 'settings/header-icon' );

		return $view;
	}

	public function create_view() {
		$sort = $this->create_element( 'radio', 'sort' )
		             ->set_options( array(
			             'on'  => __( 'Yes' ),
			             'off' => __( 'No' ),
		             ) );

		$view = new AC_View();
		$view->set( 'label', __( 'Sorting', 'codepress-admin-columns' ) )
		     ->set( 'tooltip', __( 'This will make the column support sorting.', 'codepress-admin-columns' ) )
		     ->set( 'setting', $sort );

		return $view;
	}

	/**
	 * @return string
	 */
	public function get_sort() {
		return $this->sort;
	}

	/**
	 * @param string $sort
	 *
	 * @return $this
	 */
	public function set_sort( $sort ) {
		$this->sort = $sort;

		return $this;
	}

	/**
	 * @return bool
	 */
	public function is_active() {
		return 'on' === $this->get_sort();
	}

}
