<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sorting model
 *
 * @since 1.0
 */
class ACP_Sorting_Model extends ACP_Model {

	/**
	 * When set, the native query will try to use this orderby
	 *
	 * @var string
	 */
	protected $orderby;

	public function is_active() {
		$setting = $this->column->get_setting( 'sort' );

		if ( ! $setting instanceof ACP_Sorting_Settings ) {
			return false;
		}

		return $setting->is_active();
	}

	/**
	 * @param string $orderby
	 *
	 * return $this
	 */
	public function set_orderby( $orderby ) {
		$this->orderby = $orderby;

		return $this;
	}

	/**
	 * Return the default or set order from the strategy.
	 *
	 * Falls back to ASC if an invalid order is found
	 *
	 * @return string ASC|DESC
	 */
	public function get_order() {
		$order = strtoupper( $this->strategy->get_order() );

		// Always return valid
		if ( 'ASC' !== $order && 'DESC' !== $order ) {
			$order = 'ASC';
		}

		return $order;
	}

	/**
	 * Get the sorting vars
	 *
	 * @since 4.0
	 * @return array
	 */
	public function get_sorting_vars() {
		$sorting_vars = array(
			'orderby' => $this->orderby,
		);

		// fallback to sorting by column value
		if ( empty( $this->orderby ) ) {
			$sorting_vars = array(
				'ids' => $this->sort_by_column_value( $this->strategy->get_results() ),
			);
		}

		return $sorting_vars;
	}

	/**
	 * Sorts an array ascending, maintains index association and returns keys
	 *
	 * @param array  $array
	 * @param string $data_type
	 *
	 * @return array Returns the array keys of the sorted array
	 */
	public function sort( array $array ) {
		$array = $this->prepare_values( $array );

		switch ( $this->get_data_type() ) {
			case 'numeric' :
				asort( $array, SORT_NUMERIC );

				break;
			default :

				natcasesort( $array );
		}

		$ids = array_keys( $array );

		if ( 'DESC' === $this->get_order() ) {
			$ids = array_reverse( $ids );
		}

		return $ids;
	}

	/**
	 * @param array  $ids
	 * @param string $data_type
	 *
	 * @return array
	 */
	public function sort_by_column_value( array $ids ) {
		$values = array();

		foreach ( $ids as $id ) {
			$values[ $id ] = $this->column->get_raw_value( $id );
		}

		return $this->sort( $values );
	}

	/**
	 * Prepare a value for sorting
	 *
	 * Removes html, shortcodes and reduces length to 20.
	 *
	 * @param string|array $value String or array with a string
	 *
	 * @since 4.0
	 * @return string|false Returns prepared value on success, false on failure.
	 */
	protected function prepare_value( $value ) {
		if ( is_array( $value ) ) {
			$value = array_shift( $value );
		}

		if ( ! $value || ! is_scalar( $value ) ) {
			return false;
		}

		if ( ! is_numeric( $value ) ) {

			// apply filters on small chunk, discard the rest
			$value = trim( strip_shortcodes( strip_tags( substr( $value, 0, 1000 ) ) ) );
		}

		if ( ! $value ) {
			return false;
		}

		return substr( $value, 0, 40 );
	}

	/**
	 * Prepare an array for sorting
	 *
	 * Parse all values in the array. Based on settings all empty values are unset.
	 *
	 * @param array $array
	 *
	 * @since 4.0
	 * @return array Optimized for sorting
	 */
	protected function prepare_values( $array ) {
		$show_all_results = acp_sorting()->show_all_results();

		foreach ( $array as $id => $value ) {
			$value = $this->prepare_value( $value );

			if ( $this->is_not_empty( $value ) || $show_all_results ) {
				$array[ $id ] = $value;
			} else {
				unset( $array[ $id ] );
			}
		}

		return $array;
	}

	/**
	 * Allow zero values as non empty. Allows them to be sorted.
	 *
	 * @param string|int|bool $value
	 *
	 * @return bool
	 */
	private function is_not_empty( $value ) {
		return $value || 0 === $value;
	}

	/**
	 * Register column settings
	 */
	public function register_settings() {
		$this->column->add_setting( new ACP_Sorting_Settings( $this->column ) );
	}

}
