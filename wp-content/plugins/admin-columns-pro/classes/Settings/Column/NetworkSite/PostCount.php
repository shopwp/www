<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Settings_Column_NetworkSite_PostCount extends AC_Settings_Column
	implements AC_Settings_FormatValueInterface {

	private $post_type;

	private $post_status;

	protected function define_options() {
		return array( 'post_type', 'post_status' );
	}

	public function create_view() {

		$options = $this->get_post_types();

		if ( ! $options ) {
			return false;
		}

		$setting = $this
			->create_element( 'select', 'post_type' )
			->set_options( $options );

		$view_post_type = new AC_View( array(
			'label'   => __( 'Post Type', 'codepress-admin-columns' ),
			'setting' => $setting,
			'for'     => $setting->get_id(),
		) );

		$setting = $this
			->create_element( 'select', 'post_status' )
			->set_attribute( 'data-refresh', 'column' )
			->set_options( $this->get_post_statuses() );

		if ( $excluded = $this->get_exludeded_post_statuses() ) {
			$setting->set_description( sprintf( __( 'Does not include %s', 'codepress-admin-columns' ), ac_helper()->string->enumeration_list( $excluded ) ) );
		}

		$view_post_status = new AC_View( array(
			'label'   => __( 'Post Status', 'codepress-admin-columns' ),
			'setting' => $setting,
			'for'     => $setting->get_id(),
		) );

		$view = new AC_View( array(
			'label'    => __( 'Display Options', 'codepress-admin-columns' ),
			'sections' => array( $view_post_type, $view_post_status ),
		) );

		return $view;
	}

	/**
	 * @param string $field
	 * @param int    $expire
	 *
	 * @return array|bool|mixed
	 */
	private function get_cached_distinct_db_values( $field, $expire = 15 ) {
		$values = wp_cache_get( $this->column->get_list_screen()->get_storage_key(), 'ac-network-postcount-' . $field );

		if ( ! $values ) {
			$values = $this->get_distinct_db_values( $field );

			wp_cache_add( $this->column->get_list_screen()->get_storage_key(), $values, 'ac-network-postcount-' . $field, $expire );
		}

		return $values;
	}

	/**
	 * @param string $db_field
	 *
	 * @return array
	 */
	private function get_distinct_db_values( $field ) {
		global $wpdb;

		$queries = array();
		foreach ( get_sites() as $site ) {

			/* @var WP_Site $site */
			$table = $wpdb->get_blog_prefix( $site->id ) . 'posts';

			$field = '`' . sanitize_key( $field ) . '`';

			$sql = "SELECT DISTINCT {$field} FROM {$table}";

			$queries[] = $sql;
		}

		return $wpdb->get_col( implode( " UNION ", $queries ) );
	}

	/**
	 * @return array
	 */
	private function get_post_types() {
		$post_types = $this->get_cached_distinct_db_values( 'post_type' );

		if ( ! $post_types ) {
			return array();
		}

		natcasesort( $post_types );

		$post_types = array_combine( $post_types, $post_types );

		return array( '' => __( 'All post types', 'codepress-admin-columns' ) ) + $post_types;
	}

	/**
	 * @return array
	 */
	private function get_post_statuses() {
		$post_statuses = $this->get_cached_distinct_db_values( 'post_status' );

		if ( ! $post_statuses ) {
			return array();
		}

		$post_statuses[] = 'trash';

		$post_statuses = array_unique( array_merge( $post_statuses, array_keys( get_post_statuses() ) ) );
		$post_statuses = array_combine( $post_statuses, $post_statuses );

		// Exclude 'auto-draft', 'inherit'
		$excluded = (array) get_post_stati( array( 'show_in_admin_status_list' => false ) );

		foreach ( $excluded as $k => $status ) {
			if ( isset( $post_statuses[ $status ] ) ) {
				unset( $post_statuses[ $status ] );
			}
		}

		natcasesort( $post_statuses );

		$options = array(
			           ''              => __( 'Any post status', 'codepress-admin-columns' ),
			           'without_trash' => __( 'Any post status without Trash', 'codepress-admin-columns' ),
		           ) + $post_statuses;

		return $options;
	}

	/**
	 * Excludes 'auto-draft' and 'inherit'
	 *
	 * Or use 'show_in_admin_all_list' to also exclude 'trash'
	 *
	 * @return array Post statuses
	 */
	private function get_exludeded_post_statuses() {

		if ( 'without_trash' === $this->get_post_status() ) {
			return (array) get_post_stati( array( 'show_in_admin_all_list' => false ) );
		}
		if ( ! $this->get_post_status() ) {
			return (array) get_post_stati( array( 'show_in_admin_status_list' => false ) );
		}

		return array();
	}

	/**
	 * @return string
	 */
	public function get_post_type() {
		return $this->post_type;
	}

	/**
	 * @param string $post_type
	 *
	 * @return bool
	 */
	public function set_post_type( $post_type ) {
		$this->post_type = $post_type;

		return true;
	}

	/**
	 * @return string
	 */
	public function get_post_status() {
		return $this->post_status;
	}

	/**
	 * @param string $post_status
	 *
	 * @return bool
	 */
	public function set_post_status( $post_status ) {
		$this->post_status = $post_status;

		return true;
	}

	public function format( $value, $original_value ) {
		global $wpdb;

		$blog_id = $original_value;
		$table = $wpdb->get_blog_prefix( $blog_id ) . 'posts';
		$post_status = $this->get_post_status();

		$sql = "SELECT count(*) FROM {$table}";

		$conditional = array();

		// Exclude internal post status, like 'auto-draft' and 'inherit' or 'trash'
		if ( $excluded = $this->get_exludeded_post_statuses() ) {
			$conditional[] = "{$table}.post_status NOT IN ( '" . implode( "','", $excluded ) . "' )";

			$post_status = '';
		}

		if ( $this->get_post_type() ) {
			$conditional[] = $wpdb->prepare( "{$table}.post_type = %s", $this->get_post_type() );
		}

		if ( $post_status ) {
			$conditional[] = $wpdb->prepare( "{$table}.post_status = %s", $post_status );
		}

		if ( $conditional ) {
			$sql .= " WHERE " . implode( " AND ", $conditional );
		}

		$value = $wpdb->get_var( $sql );

		if ( $this->get_post_type() ) {
			$url = add_query_arg( array( 'post_type' => $this->get_post_type() ), get_admin_url( $blog_id, 'edit.php' ) );

			if ( $post_status ) {
				$url = add_query_arg( array( 'post_status' => $post_status ), $url );
			}

			$value = ac_helper()->html->link( $url, $value );
		}

		return $value;
	}

}
