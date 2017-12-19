<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Filtering_Model_Post_Sticky extends ACP_Filtering_Model {

	public function filter_by_sticky( $where ) {
		global $wpdb;

		$stickies = get_option( 'sticky_posts' );

		if ( ! $stickies && '1' === $this->get_filter_value() ) {
			return "{$where} AND {$wpdb->posts}.ID = 0"; // Show no results
		}

		if( ! $stickies ){
			return $where;
		}
		
		$sql_val = '1' === $this->get_filter_value() ? " IN ('" . implode( "','", $stickies ) . "')" : " NOT IN ('" . implode( "','", $stickies ) . "')";

		return "{$where} AND {$wpdb->posts}.ID" . $sql_val;
	}

	public function get_filtering_vars( $vars ) {
		add_filter( 'posts_where', array( $this, 'filter_by_sticky' ) );

		return $vars;
	}

	public function get_filtering_data() {
		$options = array(
			0 => __( 'Not sticky', 'codepress-admin-columns' ),
			1 => __( 'Sticky', 'codepress-admin-columns' ),
		);

		return array(
			'options' => $options,
		);
	}

}
