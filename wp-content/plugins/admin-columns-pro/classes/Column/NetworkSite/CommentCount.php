<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Column_NetworkSite_CommentCount extends AC_Column {

	public function __construct() {
		$this->set_type( 'column-msite_commentcount' );
		$this->set_label( __( 'Comment Count', 'codepress-admin-columns' ) );
	}

	public function get_value( $blog_id ) {
		return $this->get_formatted_value( $blog_id );
	}

	public function register_settings() {
		$this->add_setting( new ACP_Settings_Column_NetworkSite_CommentCount( $this ) );
	}

}
