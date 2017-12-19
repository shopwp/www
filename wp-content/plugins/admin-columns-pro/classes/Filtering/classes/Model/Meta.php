<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @property AC_Column_Meta $column
 */
class ACP_Filtering_Model_Meta extends ACP_Filtering_Model {

	/**
	 * @param AC_Column_Meta $column
	 */
	public function __construct( AC_Column_Meta $column ) {
		parent::__construct( $column );
	}

	/**
	 * Get meta values by meta key
	 *
	 * @param boolean $filter Remove unserialized, long and empty values
	 *
	 * @return array
	 */
	public function get_meta_values() {
		$query = new AC_Meta_Query( $this->column->get_meta_type() );
		$query->select( 'meta_value' )
		      ->distinct()
		      ->join()
		      ->where( 'meta_value', '!=', '' )
		      ->where( 'meta_key', $this->column->get_meta_key() )
		      ->order_by( 'meta_value' );

		if ( $this->column->get_post_type() ) {
			$query->where_post_type( $this->column->get_post_type() );
		}

		return $query->get();
	}

	/**
	 * @return array Filtered meta values
	 */
	public function get_meta_values_filtered() {
		$values = array();

		// SQL ignores whitespace when filtering
		$filtered = array_map( 'trim', $this->get_meta_values() );

		foreach ( $filtered as $value ) {
			if ( $this->validate_value( $value ) ) {
				$values[] = $value;
			}
		}

		return $values;
	}

	/**
	 * Get meta query empty_not_empty
	 *
	 * @since 4.0
	 *
	 * @param array $vars
	 *
	 * @return array Query vars
	 */
	protected function get_filtering_vars_empty_nonempty( $vars ) {
		if ( ! isset( $vars['meta_query'] ) ) {
			$vars['meta_query'] = array();
		}

		// Check if empty or nonempty is in string (also check for like operators)
		foreach ( $vars['meta_query'] as $id => $query ) {
			if ( isset( $query['value'] ) && in_array( $query['value'], array( 'cpac_empty', 'cpac_nonempty' ) ) ) {
				unset( $vars['meta_query'][ $id ] );
			}
		}

		switch ( $this->get_filter_value() ) {

			case 'cpac_empty' :
				$vars['meta_query'][] = array(
					'relation' => 'OR',
					array(
						'key'     => $this->column->get_meta_key(),
						'compare' => 'NOT EXISTS',
					),
					array(
						'key'   => $this->column->get_meta_key(),
						'value' => '',
					),
				);
				break;

			case 'cpac_nonempty' :
				$vars['meta_query'][] = array(
					array(
						'key'     => $this->column->get_meta_key(),
						'value'   => '',
						'compare' => '!=',
					),
				);
				break;
		}

		return $vars;
	}

	/**
	 * @since 4.0
	 *
	 * @param array $vars Query args
	 * @param array $args Options
	 *
	 * @return array
	 */
	protected function get_filtering_vars_ranged( $vars, $args = array() ) {
		$defaults = array(
			'min'  => false,
			'max'  => false,
			'key'  => $this->column->get_meta_key(),
			'type' => $this->get_data_type(),
		);

		$args = array_merge( $defaults, (array) $args );

		if ( $args['min'] ) {
			$vars['meta_query'][] = array(
				'key'     => $args['key'],
				'value'   => $args['min'],
				'compare' => '>=',
				'type'    => $args['type'],
			);
		}

		if ( $args['max'] ) {
			$vars['meta_query'][] = array(
				'key'     => $args['key'],
				'value'   => $args['max'],
				'compare' => '<=',
				'type'    => $args['type'],
			);
		}

		return $vars;
	}

	/**
	 * @param array $vars
	 *
	 * @return array
	 */
	public function get_filtering_vars( $vars ) {

		if ( $this->is_ranged() ) {
			return $this->get_filtering_vars_ranged( $vars, $this->get_filter_value() );
		}

		if ( $this->column->is_serialized() ) {

			// Serialized
			$vars = $this->get_filtering_vars_serialized( $vars, $this->get_filter_value() );

		} else {

			// Exact
			$vars['meta_query'][] = array(
				'key'   => $this->column->get_meta_key(),
				'value' => $this->get_filter_value(),
				'type'  => $this->get_data_type(),
			);
		}

		return $this->get_filtering_vars_empty_nonempty( $vars );
	}

	/**
	 * @return array
	 */
	public function get_filtering_data() {
		$options = array();

		foreach ( $this->get_meta_values() as $value ) {
			$options[ $value ] = $this->column->get_formatted_value( $value );
		}

		return array(
			'empty_option' => true,
			'options'      => $options,
		);
	}

	/**
	 * @return array
	 */
	protected function get_meta_values_unserialized() {
		$values = array();

		foreach ( $this->get_meta_values() as $value ) {
			if ( is_serialized( $value ) ) {
				$values = array_merge( $values, unserialize( $value ) );
			}
		}

		return array_filter( $values );
	}

	/**
	 * @param array  $vars
	 * @param string $filter_value
	 *
	 * @return array
	 */
	protected function get_filtering_vars_serialized( $vars, $value ) {
		if ( in_array( $value, array( 'cpac_empty', 'cpac_nonempty' ) ) ) {
			return $vars;
		}

		$vars['meta_query'][] = array(
			'key'     => $this->column->get_meta_key(),
			'value'   => serialize( $value ),
			'compare' => 'LIKE',
		);

		return $vars;
	}

	/**
	 * @deprecated 4.0.3
	 * @param array $vars
	 * @param array $args
	 * @return array
	 */
	public function get_filtering_vars_date( $vars, $args ) {
		_deprecated_function( __METHOD__, '4.0.3' );

		return $vars;
	}

}
