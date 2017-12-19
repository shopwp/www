<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Filtering_Settings_Delegated extends ACP_Filtering_Settings {

	public function define_options() {
		$options = parent::define_options();

		// Default is on
		$options['filter'] = 'on';

		return $options;
	}

	public function create_view() {
		$view = parent::create_view();

		// Remove Top Label
		$view->set( 'sections', null );

		return $view;
	}

}
