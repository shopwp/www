<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Column_NetworkSite_Options extends ACP_Column_NetworkSite_Option {

	public function __construct() {
		$this->set_type( 'column-msite_options' );
		$this->set_label( __( 'Options', 'codepress-admin-columns' ) );
	}

	public function get_value( $id ) {
		$value = parent::get_value( $id );

		if ( ! $value ) {
			return $this->get_empty_char();
		}

		return $this->get_formatted_value( $value );
	}

	// Common

	public function get_option_name() {
		return $this->get_setting( 'field' )->get_value();
	}

	// Settings

	public function register_settings() {
		$this->add_setting( new ACP_Settings_Column_NetworkSite_Options( $this ) );
		$this->add_setting( new AC_Settings_Column_BeforeAfter( $this ) );
	}

}
