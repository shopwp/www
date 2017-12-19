<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Filtering_Model_Post_Content extends ACP_Filtering_Model {

	public function filter_by_description( $where ) {
		global $wpdb;

		 $where = $where . ' ' . $wpdb->prepare( "AND {$wpdb->posts}.post_content = %s", $this->get_filter_value() );

		 return $where;
	}

	public function get_filtering_vars( $vars ) {
		add_filter( 'posts_where', array( $this, 'filter_by_description' ) );

		return $vars;
	}

	public function get_filtering_data() {
		$options = array();

		foreach ( $this->strategy->get_values_by_db_field( 'post_content' ) as $value ) {
			$options[ $value ] = strip_tags( $value );
		}

		return array(
			'options' => $options,
		);
	}

}
