<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Filtering_Model_Post_BeforeMoreTag extends ACP_Filtering_Model {

	public function filter_by_before_moretag( $where ) {
		global $wpdb;

		if ( $value = $this->get_filter_value() ) {
			$sql = '';
			if ( 'cpac_empty' === $value ) {
				$sql = " NOT LIKE '%<!--more-->%'";
			}
			else if ( 'cpac_nonempty' === $value ) {
				$sql = " LIKE '%<!--more-->%'";
			}
			$where .= " AND {$wpdb->posts}.post_content" . $sql;
		}

		return $where;
	}

	public function get_filtering_vars( $vars ) {
		add_filter( 'posts_where', array( $this, 'filter_by_before_moretag' ) );

		return $vars;
	}

	public function get_filtering_data() {
		return array( 'empty_option' => true );
	}

}
