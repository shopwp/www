<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 4.0
 */
class ACP_Filtering_Helper {

	/**
	 * @param int[] $post_ids Post ID's
	 *
	 * @return array
	 */
	public function get_post_titles( $post_ids ) {
		$titles = array();

		if ( $post_ids ) {
			foreach ( $post_ids as $id ) {
				$post = get_post( $id );

				if ( ! $post ) {
					continue;
				}

				$title = $post->post_title;

				if ( ! $title ) {
					$title = '#' . $post->ID;
				}

				$titles[ $id ] = $title;
			}
		}

		foreach ( ac_helper()->array->get_duplicates( $titles ) as $id => $title ) {
			$titles[ $id ] .= ' (' . get_post_field( 'post_name', $id ) . ')';
		}

		return $titles;
	}

	/**
	 * @param array $term_ids
	 * @param string $taxonomy
	 *
	 * @return array
	 */
	public function get_term_names( $term_ids, $taxonomy ) {
		$terms = array();

		if ( $term_ids ) {
			foreach ( $term_ids as $term_id ) {
				$term = get_term_by( 'id', $term_id, $taxonomy );

				if ( ! $term ) {
					continue;
				}

				$label = $term->name;

				if ( ! $label ) {
					$label = '#' . $term->term_id;
				}

				$terms[ $term_id ] = $label;
			}
		}

		foreach ( ac_helper()->array->get_duplicates( $terms ) as $term_id => $label ) {
			$terms[ $term_id ] .= ' (' . get_term_field( 'slug', $term_id, $taxonomy ) . ')';
		}

		return $terms;
	}

}
