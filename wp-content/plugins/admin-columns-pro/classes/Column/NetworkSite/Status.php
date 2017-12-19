<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Column_NetworkSite_Status extends AC_Column {

	public function __construct() {
		$this->set_type( 'column-msite_status' );
		$this->set_label( __( 'Status', 'codepress-admin-columns' ) );
	}

	public function get_value( $id ) {
		$values = array();

		$site = get_site( $id );

		foreach ( $this->get_statuses() as $status => $label ) {
			if ( ! empty( $site->{$status} ) ) {
				$values[] = $label;
			}
		}

		return ac_helper()->html->implode( $values );
	}

	private function get_statuses() {
		return array(
			'public'   => __( 'Public' ),
			'archived' => __( 'Archived' ),
			'spam'     => _x( 'Spam', 'site' ),
			'deleted'  => __( 'Deleted' ),
			'mature'   => __( 'Mature' ),
		);
	}

}
