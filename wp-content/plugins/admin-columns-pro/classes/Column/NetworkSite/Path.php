<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Column_NetworkSite_Path extends ACP_Column_NetworkSite_Property {

	public function __construct() {
		$this->set_type( 'column-msite_path' );
		$this->set_label( __( 'Path', 'codepress-admin-columns' ) );
	}

	public function get_site_property() {
		return 'path';
	}

}
