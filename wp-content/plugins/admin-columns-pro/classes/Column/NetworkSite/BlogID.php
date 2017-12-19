<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Column_NetworkSite_BlogID extends ACP_Column_NetworkSite_Property {

	public function __construct() {
		$this->set_type( 'column-blog_id' );
		$this->set_label( __( 'Blog ID', 'codepress-admin-columns' ) );
	}

	public function get_site_property() {
		return 'blog_id';
	}

}
