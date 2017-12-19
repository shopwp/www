<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 4.0
 */
class ACP_Editing_Helper {

	/**
	 * Format options for posts selection
	 *
	 * Results are formatted as an array of post types, the key being the post type name, the value
	 * being an array with two keys: label (the post type label) and options, an array of options (posts)
	 * for this post type, with the post IDs as keys and the post titles as values
	 *
	 * @since 1.0
	 * @uses  WP_Query
	 *
	 * @param array  $args   Additional query arguments for WP_Query
	 * @param string $format Formatting type
	 *
	 * @return array List of options, grouped by post type
	 */
	public function get_posts_list( $args = array() ) {

		$defaults = array(
			'posts_per_page' => 60,
			'post_type'      => 'any',
			'post_status'    => 'any',
			'orderby'        => 'title',
			'order'          => 'ASC',
			's'              => '',
			'paged'          => 1,
		);

		$args = wp_parse_args( $args, $defaults );

		if ( ! is_numeric( $args['paged'] ) ) {
			$args['paged'] = 1;
		}

		$posts = get_posts( $args );

		if ( ! $posts ) {
			return array();
		}

		$grouped_options = array();

		foreach ( $posts as $post ) {

			if ( ! isset( $grouped_options[ $post->post_type ] ) ) {
				$post_type_object = get_post_type_object( $post->post_type );

				$grouped_options[ $post->post_type ] = array(
					'label'   => $post_type_object ? $post_type_object->labels->name : $post->post_type,
					'options' => array(),
				);
			}

			$label = $post->post_title;

			// Add filename to attachments
			if ( 'attachment' === $post->post_type ) {
				$label = '#' . $post->ID . ' - ' . ac_helper()->image->get_file_name( $post->ID );
			}

			if ( ! $label ) {
				$label = $post->ID;
			}

			$label = ac_helper()->string->trim_characters( $label, 26, '...' );

			$grouped_options[ $post->post_type ]['options'][ $post->ID ] = $label;
		}

		foreach ( $grouped_options as $post_type => $options ) {

			// Add post ID to duplicates
			foreach ( $this->get_duplicates( $options['options'] ) as $id => $label ) {
				$grouped_options[ $post_type ]['options'][ $id ] .= ' - #' . $id;
			}

		}

		return $grouped_options;
	}

	/**
	 * Format list of options for users selection
	 *
	 * Results are formatted as an array of roles, the key being the role name, the value
	 * being an array with two keys: label (the role label) and options, an array of options (users)
	 * for this role, with the user IDs as keys and the user display names as values
	 *
	 * @since 1.0
	 * @uses  WP_User_Query
	 *
	 * @param array  $args User query args
	 * @param string $format
	 *
	 * @return array Grouped users by role
	 */
	public function get_users_list( $args = array() ) {

		if ( ! empty( $args['search'] ) ) {
			$args['search'] = '*' . $args['search'] . '*';
		}

		$args = wp_parse_args( $args, array(
			'orderby'        => 'display_name',
			'search_columns' => array( 'ID', 'user_login', 'user_nicename', 'user_email', 'user_url' ),
			'number'         => 50,
			'paged'          => 1,
			'fields'         => 'ID',
		) );

		if ( ! is_numeric( $args['paged'] ) ) {
			$args['paged'] = 1;
		}

		$users_query = new WP_User_Query( $args );

		$user_ids = $users_query->get_results();

		if ( ! $user_ids ) {
			return array();
		}

		$grouped_options = array();

		foreach ( $user_ids as $user_id ) {
			$user = get_userdata( $user_id );
			$name = ac_helper()->user->get_display_name( $user );

			// Add login name
			$name .= ' (' . $user->user_login . ')';

			// Group by role
			$role = array_shift( $user->roles );

			if ( ! isset( $grouped_options[ $role ] ) ) {

				$grouped_options[ $role ] = array(
					'label'   => $this->get_role_name( $role ),
					'options' => array(),
				);
			}

			$grouped_options[ $role ]['options'][ $user->ID ] = $name;
		}

		return $grouped_options;
	}

	/**
	 * @param string $role
	 *
	 * @return false|string
	 */
	private function get_role_name( $role ) {
		$roles = get_editable_roles();

		if ( ! isset( $roles[ $role ] ) ) {
			return false;
		}

		return translate_user_role( $roles[ $role ]['name'] );
	}

	/**
	 * Format list of options for term selection
	 *
	 * @since 4.0
	 *
	 * @param array $args get_term args
	 *
	 * @return array Formatted Taxonomies
	 */
	public function get_terms_list( $args = array() ) {

		$defaults = array(
			'taxonomy'   => 'category',
			'hide_empty' => false,
		);

		$args = wp_parse_args( $args, $defaults );

		$terms = get_terms( $args );

		if ( ! $terms ) {
			return array();
		}

		$options = array();

		foreach ( $terms as $term ) {

			// Remove corrupt post formats. There can be post format added to the
			// DB that are not officially registered. Those are skipped.
			if ( 'post_format' === $term->taxonomy && ! $this->is_term_post_format( $term ) ) {
				continue;
			}

			$label = htmlspecialchars_decode( $term->name );

			if ( ! $label ) {
				$label = $term->term_id;
			}

			$options[ $term->term_id ] = $label;
		}

		// Add term slug to duplicates
		foreach ( $this->get_duplicates( $options ) as $term_id => $label ) {
			$options[ $term_id ] .= ' (' . $this->get_term_field_by_id( $term_id, 'slug', $terms ) . ')';
		}

		natcasesort( $options );

		return $options;
	}

	/**
	 * @param WP_Term $term
	 *
	 * @return bool
	 */
	private function is_term_post_format( $term ) {
		return 0 === strpos( $term->slug, 'post-format-' ) && in_array( str_replace( 'post-format-', '', $term->slug ), get_post_format_slugs() );

	}

	/**
	 * Get duplicates from array
	 *
	 * @param array $options
	 *
	 * @return array
	 */
	private function get_duplicates( array $options ) {
		return ac_helper()->array->get_duplicates( $options );
	}

	/**
	 * @param int       $term_id
	 * @param WP_Term[] $terms
	 */
	private function get_term_field_by_id( $term_id, $field, array $terms ) {
		foreach ( $terms as $term ) {
			if ( $term_id === $term->term_id ) {
				return $term->{$field};
			}
		}

		return false;
	}

	/**
	 * Format list of options for comment selection
	 *
	 * @since 4.0
	 * @uses  WP_User_Query
	 *
	 * @param array $args Comment query args
	 *
	 * @return array Formatted Comments
	 */
	public function get_comments_list( $args = array() ) {

		$defaults = array(
			'number'  => 50,
			'fields'  => 'ID',
			'status'  => 'all',
			'orderby' => 'comment_date_gmt',
		);

		$args = wp_parse_args( $args, $defaults );

		if ( ! is_numeric( $args['paged'] ) ) {
			$args['paged'] = 1;
		}

		$args['offset'] = ( $args['paged'] - 1 ) * $args['number'];

		$comments = get_comments( $args );

		if ( ! $comments ) {
			return array();
		}

		$options = array();

		foreach ( $comments as $comment ) {
			$parts = array_filter( array(
				'#' . $comment->comment_post_ID,
				$comment->comment_author_email,
				$comment->comment_date,
			) );

			$options[ $comment->comment_ID ] = implode( ' - ', $parts );
		}

		return $options;
	}

}
