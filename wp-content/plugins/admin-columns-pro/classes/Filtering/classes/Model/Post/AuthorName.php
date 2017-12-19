<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Filtering_Model_Post_AuthorName extends ACP_Filtering_Model {

	public function filter_by_author_name( $where ) {
		global $wpdb;

		return $where . $wpdb->prepare( "AND {$wpdb->posts}.post_author = %s", $this->get_filter_value() );
	}

	public function get_filtering_vars( $vars ) {
		add_filter( 'posts_where', array( $this, 'filter_by_author_name' ) );

		return $vars;
	}

	public function get_filtering_data( ) {
		$data = array();
		if ( $values = $this->strategy->get_values_by_db_field( 'post_author' ) ) {
			foreach ( $values as $value ) {
				$data['options'][ $value ] = ac_helper()->user->get_display_name( $value );
			}
		}

		return $data;
	}

}
