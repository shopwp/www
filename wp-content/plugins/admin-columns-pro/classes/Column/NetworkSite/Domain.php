<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Column_NetworkSite_Domain extends ACP_Column_NetworkSite_Property {

	public function __construct() {
		$this->set_type( 'column-msite_domain' );
		$this->set_label( __( 'Domain', 'codepress-admin-columns' ) );
	}

	public function get_site_property() {
		return 'domain';
	}

}
