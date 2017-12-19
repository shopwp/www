<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Filtering_Model_Delegated extends ACP_Filtering_Model {

	/**
	 * @var string Dropdown HTML attribute ID
	 */
	private $dropdown_attr_id;

	public function get_filtering_vars( $vars ) {
		return $vars;
	}

	public function get_filtering_data() {
		return array();
	}

	public function register_settings() {
		$this->column->add_setting( new ACP_Filtering_Settings_Delegated( $this->column ) );
	}

	public function set_dropdown_attr_id( $id ) {
		$this->dropdown_attr_id = $id;
	}

	public function get_dropdown_attr_id() {
		return $this->dropdown_attr_id;
	}

}
