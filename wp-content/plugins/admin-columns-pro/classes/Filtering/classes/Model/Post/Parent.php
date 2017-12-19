<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Filtering_Model_Post_Parent extends ACP_Filtering_Model {

	public function get_filtering_vars( $vars ) {
		$vars['post_parent'] = $this->get_filter_value();

		return $vars;
	}

	public function get_filtering_data() {
		$parents = $this->strategy->get_values_by_db_field( 'post_parent' );

		return array(
			'options' => acp_filtering()->helper()->get_post_titles( $parents ),
		);
	}

}
