<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Column_NetworkSite_PostCount extends AC_Column {

	public function __construct() {
		$this->set_type( 'column-msite_postcount' );
		$this->set_label( __( 'Post Count', 'codepress-admin-columns' ) );
	}

	public function get_raw_value( $blog_id ) {
		return $blog_id;
	}

	public function register_settings() {
		$this->add_setting( new ACP_Settings_Column_NetworkSite_PostCount( $this ) );
	}

}
