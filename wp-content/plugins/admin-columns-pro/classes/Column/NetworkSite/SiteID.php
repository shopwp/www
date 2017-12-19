<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Column_NetworkSite_SiteID extends ACP_Column_NetworkSite_Property {

	public function __construct() {
		$this->set_type( 'column-msite_id' );
		$this->set_label( __( 'Site ID', 'codepress-admin-columns' ) );
	}

	public function get_site_property() {
		return 'site_id';
	}

}
