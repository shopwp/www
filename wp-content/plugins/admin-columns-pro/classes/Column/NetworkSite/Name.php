<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Column_NetworkSite_Name extends ACP_Column_NetworkSite_Option {

	public function __construct() {
		$this->set_type( 'column-msite_name' );
		$this->set_label( __( 'Name', 'codepress-admin-columns' ) );
	}

	public function get_option_name() {
		return 'blogname';
	}

}
