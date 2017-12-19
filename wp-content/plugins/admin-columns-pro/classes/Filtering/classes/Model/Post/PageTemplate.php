<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @property AC_Column_Post_PageTemplate $column
 */
class ACP_Filtering_Model_Post_PageTemplate extends ACP_Filtering_Model_Meta {

	public function __construct( AC_Column_Post_PageTemplate $column ) {
		parent::__construct( $column );
	}

	public function get_filtering_vars( $vars ) {
		$vars['meta_query'][] = array(
			'key'   => $this->column->get_meta_key(),
			'value' => $this->get_filter_value(),
		);

		return $vars;
	}

	public function get_filtering_data() {
		$data = array();

		if ( $values = $this->get_meta_values() ) {
			$page_templates = $this->column->get_page_templates();

			foreach ( $values as $page_template ) {
				$label = array_search( $page_template, $page_templates );

				$data['options'][ $page_template ] = $label ? $label : $page_template;
			}
		}

		return $data;
	}
}
