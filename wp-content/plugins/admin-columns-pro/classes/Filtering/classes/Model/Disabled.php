<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Filtering_Model_Disabled extends ACP_Filtering_Model {

	public function is_active() {
		return false;
	}

	public function get_filtering_vars( $vars ) {
		return $vars;
	}

	public function get_filtering_data() {
		return array();
	}

	public function register_settings() {
	}

}
