<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class ACP_Column_NetworkSite_Property extends AC_Column {

	abstract public function get_site_property();

	public function get_value( $id ) {
		$site = get_site( $id );
		$property = $this->get_site_property();

		if ( ! isset( $site->{$property} ) ) {
			return false;
		}

		return $site->{$property};
	}

}
