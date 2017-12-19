<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Editing_Settings_Taxonomy extends ACP_Editing_Settings {

	private $enable_term_creation;

	protected function define_options() {
		$managed_options = parent::define_options();
		$managed_options['enable_term_creation'] = 'off';

		return $managed_options;
	}

	public function create_view() {
		$view = parent::create_view();

		// Force refresh
		$view->get( 'setting' )->set_attribute( 'data-refresh', 'column' );

		// Sub settings
		if ( $this->is_active() ) {

			if ( $taxonomy = get_taxonomy( $this->column->get_taxonomy() ) ) {

				$enable_term = $this
					->create_element( 'radio', 'enable_term_creation' )
					->set_options( array(
							'on'  => __( 'Yes' ),
							'off' => __( 'No' ),
						)
					);

				$new_term = new AC_View();
				$new_term->set( 'label', sprintf( __( 'Allow new %s?', 'codepress-admin-columns' ), strtolower( $taxonomy->labels->name ) ) )
				         ->set( 'tooltip', sprintf( __( 'Allow new %s to be created whilst editing', 'codepress-admin-columns' ), strtolower( $taxonomy->labels->name ) ) )
				         ->set( 'setting', $enable_term );

				$view->set( 'sections', array( $new_term ) );
			}
		}

		return $view;
	}

	/**
	 * @return string
	 */
	public function get_enable_term_creation() {
		return $this->enable_term_creation;
	}

	/**
	 * @param string $enable_term_creation
	 *
	 * @return $this
	 */
	public function set_enable_term_creation( $enable_term_creation ) {
		$this->enable_term_creation = $enable_term_creation;

		return $this;
	}

}
