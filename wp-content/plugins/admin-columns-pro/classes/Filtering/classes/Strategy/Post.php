<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class ACP_Filtering_Strategy_Post extends ACP_Filtering_Strategy {

	/**
	 * Handle filter request
	 *
	 * @since 3.5
	 *
	 * @param WP_User_Query $user_query
	 */
	public function handle_filter_requests( $wp_query ) {
		if ( ! $wp_query->is_main_query() || ! is_admin() ) {
			return;
		}

		$wp_query->query_vars = $this->model->get_filtering_vars( $wp_query->query_vars );
	}

	/**
	 * Get values by post field
	 *
	 * @param string $field
	 *
	 * @return array
	 */
	public function get_values_by_db_field( $field ) {
		global $wpdb;

		$post_field = '`' . sanitize_key( $field ) . '`';

		$sql = "
			SELECT DISTINCT {$field}
			FROM {$wpdb->posts}
			WHERE post_type = %s
			AND {$post_field} <> ''
			ORDER BY 1
		";

		$values = $wpdb->get_col( $wpdb->prepare( $sql, $this->column->get_post_type() ) );

		if ( empty( $values ) ) {
			return array();
		}

		return $values;
	}

	/**
	 * @param $vars
	 * @param $value
	 * @param $taxonomy
	 *
	 * @return mixed
	 */
	public function get_filterable_request_vars_taxonomy( $vars, $value, $taxonomy ) {

		switch ( $value ) {

			case 'cpac_empty' :
				$tax_query = array(
					'terms'    => false,
					'operator' => 'NOT EXISTS',
				);

				break;
			case 'cpac_nonempty' :
				$tax_query = array(
					'terms'    => false,
					'operator' => 'EXISTS',
				);

				break;
			default :
				$tax_query = array(
					'terms' => $value,
					'field' => 'slug',
				);
		}

		$vars['tax_query'][] = array_merge( array( 'taxonomy' => $taxonomy ), $tax_query );

		return $vars;
	}

}
