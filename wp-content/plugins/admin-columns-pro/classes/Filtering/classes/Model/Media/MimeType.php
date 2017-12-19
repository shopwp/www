<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Filtering_Model_Media_MimeType extends ACP_Filtering_Model {

	public function filter_by_mime_type( $where ) {
		global $wpdb;

		return $where . $wpdb->prepare( "AND {$wpdb->posts}.post_mime_type = %s", $this->get_filter_value() );
	}

	public function get_filtering_vars( $vars ) {
		add_filter( 'posts_where', array( $this, 'filter_by_mime_type' ) );

		return $vars;
	}

	public function get_filtering_data() {
		$data = array();
		$mime_types = array_flip( wp_get_mime_types() );
		foreach ( $this->strategy->get_values_by_db_field( 'post_mime_type' ) as $_value ) {
			$data['options'][ $_value ] = $mime_types[ $_value ];
		}

		return $data;
	}

}
