<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Filtering_Model_Media_Comments extends ACP_Filtering_Model {

	public function filter_by_comments( $where ) {
		global $wpdb;

		if ( '0' == $this->get_filter_value() ) {
			$where .= "AND {$wpdb->posts}.comment_count = '0'";
		}
		elseif ( '1' == $this->get_filter_value() ) {
			$where .= "AND {$wpdb->posts}.comment_count <> '0'";
		}

		return $where;
	}

	public function get_filtering_vars( $vars ) {
		add_filter( 'posts_where', array( $this, 'filter_by_comments' ) );

		return $vars;
	}

	public function get_filtering_data( ) {
		$data = array();
		$data['options'] = array(
			'' => __( 'No comments', 'codepress-admin-columns' ),
			1  => __( 'Has comments', 'codepress-admin-columns' ),
		);

		return $data;
	}

}
