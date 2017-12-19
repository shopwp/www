<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @property AC_Column_Meta $column
 */
class ACP_Sorting_Model_Meta extends ACP_Sorting_Model {

	public function __construct( AC_Column_Meta $column ) {
		parent::__construct( $column );
	}

	/**
	 * Get args for a WP_Meta_Query to sort on a single key
	 *
	 * @param string $key  Meta key
	 * @param string $type See WP_Meta_Query for available options
	 *
	 * @see   WP_Meta_Query
	 * @since 4.0
	 * @return array Arguments to sort with using a WP_Meta_Query
	 */
	public function get_sorting_vars() {
		$key = $this->column->get_meta_key();

		$id = uniqid();

		$vars = array(
			'meta_query' => array(
				$id => array(
					'key'     => $key,
					'type'    => $this->get_data_type(),
					'value'   => '',
					'compare' => '!=',
				),
			),
			'orderby' => $id
		);

		if ( acp_sorting()->show_all_results() ) {

			$vars['meta_query'] = array(
				'relation' => 'OR',

				// $id indicates which $key should be used for sorting. wp_query will use the $key for sorting, and applies both
				// the EXISTS and NOT EXISTS compares. Without $id it will not work when sorting is used
				// in conjunction with filtering.
				$id => array(
					'key'     => $key,
					'type'    => $this->get_data_type(),
					'compare' => 'EXISTS',
				),
				array(
					'key'     => $key,
					'compare' => 'NOT EXISTS',
				),
			);
		}

		return $vars;
	}

}
