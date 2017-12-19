<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_ListScreen_Taxonomy extends AC_ListScreen {

	/**
	 * @var string Taxonomy name
	 */
	private $taxonomy;

	/**
	 * Constructor
	 *
	 * @since 1.2.0
	 */
	public function __construct( $taxonomy ) {

		$this->set_meta_type( 'term' );
		$this->set_screen_base( 'edit-tags' );
		$this->set_screen_id( 'edit-' . $taxonomy );
		$this->set_key( 'wp-taxonomy_' . $taxonomy );
		$this->set_taxonomy( $taxonomy );
		$this->set_group( 'taxonomy' );

		/* @see WP_Terms_List_Table */
		$this->set_list_table_class( 'WP_Terms_List_Table' );
	}

	protected function set_taxonomy( $taxonomy ) {
		$this->taxonomy = (string) $taxonomy;
	}

	public function get_taxonomy() {
		return $this->taxonomy;
	}

	public function set_manage_value_callback() {
		/* @see WP_Terms_List_Table::column_default */
		add_action( "manage_" . $this->get_taxonomy() . "_custom_column", array( $this, 'manage_value' ), 10, 3 );
	}

	/**
	 * @since 4.0
	 */
	public function get_object_by_id( $term_id ) {
		return get_term_by( 'id', $term_id, $this->get_taxonomy() );
	}

	/**
	 * @return string|false
	 */
	public function get_label() {
		return $this->get_taxonomy_label_var( 'name' );
	}

	/**
	 * @return false|string
	 */
	public function get_singular_label() {
		return $this->get_taxonomy_label_var( 'singular_name' );
	}

	/**
	 * @since 3.7.3
	 */
	public function is_current_screen( $wp_screen ) {
		return parent::is_current_screen( $wp_screen ) && $this->get_taxonomy() === filter_input( INPUT_GET, 'taxonomy' );
	}

	/**
	 * Get screen link
	 *
	 * @since 1.2.0
	 *
	 * @return string Link
	 */
	public function get_screen_link() {
		$post_type = null;

		if ( $object_type = $this->get_taxonomy_var( 'object_type' ) ) {
			if ( post_type_exists( $object_type[0] ) ) {
				$post_type = $object_type[0];
			}
		}

		return add_query_arg( array( 'taxonomy' => $this->get_taxonomy(), 'post_type' => $post_type ), parent::get_screen_link() );
	}

	/**
	 * Manage value
	 *
	 * @since 1.2.0
	 *
	 * @param string $column_name
	 * @param int $post_id
	 */
	public function manage_value( $value, $column_name, $term_id ) {
		return $this->get_display_value_by_column_name( $column_name, $term_id, $value );
	}

	/**
	 * @param $var
	 *
	 * @return string|false
	 */
	private function get_taxonomy_label_var( $var ) {
		$taxonomy = get_taxonomy( $this->get_taxonomy() );

		return $taxonomy && isset( $taxonomy->labels->{$var} ) ? $taxonomy->labels->{$var} : false;
	}

	private function get_taxonomy_var( $var ) {
		$taxonomy = get_taxonomy( $this->get_taxonomy() );

		return $taxonomy && isset( $taxonomy->{$var} ) ? $taxonomy->{$var} : false;
	}

	protected function register_column_types() {
		$this->register_column_type( new ACP_Column_CustomField() );
		$this->register_column_type( new ACP_Column_UsedByMenu() );

		$this->register_column_types_from_dir( ACP()->get_plugin_dir() . 'classes/Column/Taxonomy', ACP::CLASS_PREFIX );
	}

}
