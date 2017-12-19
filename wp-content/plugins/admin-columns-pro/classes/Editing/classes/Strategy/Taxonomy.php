<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Editing_Strategy_Taxonomy extends ACP_Editing_Strategy {

	public function get_rows() {
		global $wp_list_table;

		/* @var WP_Terms_List_Table $wp_list_table */

		ob_start();
		$wp_list_table->display_rows_or_placeholder();

		$html = ob_get_clean();

		// In version 4.7.3 it it's impossible to get the terms from the current table.
		// All the logic is in display_rows_or_placeholder().
		// By fetching the rows HTML we can parse out the needed term ID's with DOMDocument

		$doc = new DOMDocument();

		$doc->loadHTML( $html );
		$xpath = new DOMXPath( $doc );

		$query = "//input[@type='checkbox']";

		$term_ids = array();

		$node_list = $xpath->query( $query );

		if ( $node_list->length > 0 ) {
			foreach ( $node_list as $dom_element ) {

				/* @var DOMElement $dom_element */
				$term_ids[] = $dom_element->getAttribute( "value" );
			}
		}

		return $this->get_editable_rows( $term_ids );
	}

	/**
	 * @param WP_Term|int $term
	 *
	 * @return bool|int
	 */
	public function user_has_write_permission( $term ) {
		if ( ! current_user_can( 'manage_categories' ) ) {
			return false;
		}

		if ( ! is_a( $term, 'WP_Term' ) ) {
			$term = get_term_by( 'id', $term, $this->column->get_taxonomy() );
		}

		if ( ! $term || is_wp_error( $term ) ) {
			return false;
		}

		return $term->term_id;
	}

	/**
	 * @since 4.0
	 */
	public function update( $id, $args ) {
		return wp_update_term( $id, $this->column->get_taxonomy(), $args );
	}

}
