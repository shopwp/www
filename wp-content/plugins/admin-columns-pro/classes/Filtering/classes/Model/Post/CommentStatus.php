<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Filtering_Model_Post_CommentStatus extends ACP_Filtering_Model {

	public function filter_by_comment_status( $where ) {
		global $wpdb;

		if ( $value = $this->get_filter_value() ) {
			$where .= $wpdb->prepare( " AND {$wpdb->posts}.comment_status = %s", $value );
		}

		return $where;
	}

	public function get_filtering_vars( $vars ) {
		add_filter( 'posts_where', array( $this, 'filter_by_comment_status' ) );

		return $vars;
	}

	public function get_filtering_data( ) {
		$data = array();
		$data['options'] = array(
			'open'   => __( 'Open' ),
			'closed' => __( 'Closed' ),
		);

		return $data;
	}

}
