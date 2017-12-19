<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class ACP_Column_NetworkSite_Option extends AC_Column
	implements ACP_Column_EditingInterface {

	/**
	 * @return string Site option name
	 */
	abstract public function get_option_name();

	public function get_value( $blog_id ) {
		return $this->get_site_option( $blog_id );
	}

	public function get_site_option( $blog_id ) {
		return ac_helper()->network->get_site_option( $blog_id, $this->get_option_name() );
	}

	public function get_raw_value( $blog_id ) {
		return $this->get_site_option( $blog_id );
	}

	public function editing() {
		return new ACP_Editing_Model_Site_Option( $this );
	}

}
