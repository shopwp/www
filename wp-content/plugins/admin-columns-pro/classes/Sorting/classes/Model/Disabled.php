<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Sorting_Model_Disabled extends ACP_Sorting_Model {

	public function is_active() {
		return false;
	}

	public function register_settings() {
	}

}
