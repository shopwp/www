<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Editing_Model_Post_Taxonomy extends ACP_Editing_Model {

	/**
	 * @param int $id
	 *
	 * @return array
	 */
	public function get_edit_value( $id ) {
		$values = array();

		$terms = get_the_terms( $id, $this->column->get_taxonomy() );
		if ( $terms && ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				$values[ $term->term_id ] = htmlspecialchars_decode( $term->name );
			}
		}

		return $values;
	}

	private function get_taxonomy_object() {
		return get_taxonomy( $this->column->get_taxonomy() );
	}

	public function get_view_settings() {
		$taxonomy = $this->get_taxonomy_object();

		if ( ! $taxonomy ) {
			return false;
		}

		$data = array(
			'type'          => 'select2_dropdown',
			'multiple'      => true,
			'ajax_populate' => true,
		);

		if ( 'on' === $this->column->get_option( 'enable_term_creation' ) ) {
			$data = array(
				'type'     => 'select2_tags',
				'multiple' => false,
				'options'  => $this->get_term_options(),
			);
		}

		if ( 'post_format' == $taxonomy->name ) {
			$data = array(
				'type'     => 'select2_dropdown',
				'multiple' => false,
			);
		}

		return $data;
	}

	public function get_term_options() {
		$args = array(
			'taxonomy'   => $this->column->get_taxonomy(),
			'hide_empty' => false,
		);

		return acp_editing_helper()->get_terms_list( $args );
	}

	public function get_ajax_options( $request ) {
		$args = array(
			'taxonomy'   => $this->column->get_taxonomy(),
			'hide_empty' => false,
		);

		if ( $request['paged'] ) {
			$args['offset'] = ( $request['paged'] - 1 ) * 40;
			$args['number'] = 40;
		}
		
		if ( isset( $request['search'] ) ) {
			$args['search'] = $request['search'];
		}

		$terms = acp_editing_helper()->get_terms_list( $args );

		return $terms;
	}

	/**
	 * @param int    $id
	 * @param string $value
	 */
	public function save( $id, $value ) {
		$this->set_terms( $id, $value, $this->column->get_taxonomy() );
	}

	/**
	 * Register editing settings
	 */
	public function register_settings() {
		$this->column->add_setting( new ACP_Editing_Settings_Taxonomy( $this->column ) );
	}

	/**
	 * @param $post     WP_Post|int
	 * @param $term_ids int[]|int Term ID's
	 * @param $taxonomy string Taxonomy name
	 */
	protected function set_terms( $post, $term_ids, $taxonomy ) {
		$post = get_post( $post );

		if ( ! $post || ! taxonomy_exists( $taxonomy ) ) {
			return;
		}

		// Filter list of terms
		if ( empty( $term_ids ) ) {
			$term_ids = array();
		}

		$term_ids = array_unique( (array) $term_ids );

		// maybe create terms?
		$created_term_ids = array();

		foreach ( (array) $term_ids as $index => $term_id ) {
			if ( is_numeric( $term_id ) ) {
				continue;
			}

			if ( $term = get_term_by( 'name', $term_id, $taxonomy ) ) {
				$term_ids[ $index ] = $term->term_id;
			} else {
				$created_term = wp_insert_term( $term_id, $taxonomy );
				$created_term_ids[] = $created_term['term_id'];
			}
		}

		// merge
		$term_ids = array_merge( $created_term_ids, $term_ids );

		//to make sure the terms IDs is integers:
		$term_ids = array_map( 'intval', (array) $term_ids );
		$term_ids = array_unique( $term_ids );

		if ( $taxonomy == 'category' && is_object_in_taxonomy( $post->post_type, 'category' ) ) {
			wp_set_post_categories( $post->ID, $term_ids );
		} else if ( $taxonomy == 'post_tag' && is_object_in_taxonomy( $post->post_type, 'post_tag' ) ) {
			wp_set_post_tags( $post->ID, $term_ids );
		} else {
			wp_set_object_terms( $post->ID, $term_ids, $taxonomy );
		}
	}

}
