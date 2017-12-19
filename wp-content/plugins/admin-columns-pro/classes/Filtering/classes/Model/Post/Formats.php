<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Filtering_Model_Post_Formats extends ACP_Filtering_Model_Post_Taxonomy {

	public function get_filtering_data() {
		$options = $this->get_terms_list( $this->column->get_taxonomy() );
		$options['cpac_empty'] = get_post_format_string( 'standard' );

		return array(
			'options' => $options,
		);
	}

}
