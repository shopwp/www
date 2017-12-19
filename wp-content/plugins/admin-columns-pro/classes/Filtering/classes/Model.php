<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @property ACP_Filtering_Strategy_Post|ACP_Filtering_Strategy_Comment|ACP_Filtering_Strategy_User $strategy
 */
abstract class ACP_Filtering_Model extends ACP_Model {

	/**
	 * @var bool
	 */
	private $ranged;

	/**
	 * Get the query vars to filter on
	 *
	 * @param array $vars
	 *
	 * @return array
	 */
	abstract public function get_filtering_vars( $vars );

	/**
	 * Return the data required to generate the filtering gui on a list screen
	 *
	 * @return array
	 */
	abstract public function get_filtering_data();

	public function is_active() {
		$setting = $this->column->get_setting( 'filter' );

		if ( ! $setting instanceof ACP_Filtering_Settings ) {
			return false;
		}

		return $setting->is_active();
	}

	/**
	 * Register column settings
	 */
	public function register_settings() {
		$this->column->add_setting( new ACP_Filtering_Settings( $this->column ) );
	}

	/**
	 * @return string|array
	 */
	public function get_filter_value() {
		return $this->validate_filter_value( acp_filtering()->table_screen()->get_requested_filter_value( $this->column ) );
	}

	/**
	 * @param string|array $value
	 *
	 * @return array|string|false
	 */
	private function validate_filter_value( $value ) {
		if ( $this->is_ranged() ) {

			// Value can only be an array with min and max keys
			if ( ! is_array( $value ) ) {
				$value = array(
					'min' => false,
					'max' => false,
				);
			}

			if ( ! isset( $value['min'] ) ) {
				$value['min'] = false;
			}

			if ( ! isset( $value['max'] ) ) {
				$value['max'] = false;
			}
		} else {

			// Value can only be scalar
			if ( ! is_scalar( $value ) ) {
				$value = false;
			}
		}

		return $value;
	}

	/**
	 * @param bool $is_ranged
	 */
	public function set_ranged( $is_ranged ) {
		$this->ranged = (bool) $is_ranged;
	}

	/**
	 * @return bool
	 */
	public function is_ranged() {
		if ( null === $this->ranged ) {
			$setting = $this->column->get_setting( 'filter' );
			$this->set_ranged( $setting instanceof ACP_Filtering_Settings_Ranged && $setting->is_ranged() );
		}

		return $this->ranged;
	}

	/**
	 * Validate a value: can it be used to filter results?
	 *
	 * @param string|integer $value
	 * @param string         $filters Options: all, serialize, length and empty. Use a | to use a selection of filters e.g. length|empty
	 *
	 * @return bool
	 */
	protected function validate_value( $value, $filters = 'all' ) {
		$available = array( 'serialize', 'length', 'empty' );

		switch ( $filters ) {
			case 'all':
				$applied = $available;

				break;
			default:
				$applied = array_intersect( $available, explode( '|', $filters ) );

				if ( empty( $applied ) ) {
					$applied = $available;
				}
		}

		foreach ( $applied as $filter ) {
			switch ( $filter ) {
				case 'serialize':
					if ( is_serialized( $value ) ) {
						return false;
					}

					break;
				case 'length':
					if ( strlen( $value ) > 1024 ) {
						return false;
					}

					break;
				case 'empty':
					if ( empty( $value ) && 0 !== $value ) {
						return false;
					}

					break;
			}
		}

		return true;
	}

	/**
	 * Return options for a date filter based on an array of dates
	 *
	 * @param array       $dates
	 * @param string      $display How to display the date
	 * @param string      $format Format of the date
	 * @param string|null $key
	 *
	 * @return array
	 */
	protected function get_date_options( array $dates, $display, $format = 'Y-m-d', $key = null ) {
		$options = array();

		switch ( $display ) {
			case 'yearly':
				$display = 'Y';
				$key = 'Y';

				break;
			case 'monthly':
				$display = 'F Y';
				$key = 'Ym';

				break;
			case 'daily':
				$display = 'j F Y';
				$key = 'Ymd';

				break;
		}

		if ( ! $key ) {
			$key = $format;
		}

		foreach ( $dates as $date ) {
			$timestamp = ac_helper()->date->get_timestamp_from_format( $date, $format );

			if ( ! $timestamp ) {
				continue;
			}

			$option = date( $key, $timestamp );

			if ( ! isset( $options[ $key ] ) ) {
				$options[ $option ] = date_i18n( $display, $timestamp );
			}
		}

		ksort( $options, SORT_NUMERIC );

		return $options;
	}

	/**
	 * @param string $format
	 *
	 * @return array|false
	 */
	protected function get_date_options_relative( $format ) {
		$options = array();

		switch ( $format ) {
			case 'future_past':
				$options = array(
					'future' => __( 'Future dates', 'codepress-admin-columns' ),
					'past'   => __( 'Past dates', 'codepress-admin-columns' ),
				);

				break;
		}

		if ( empty( $options ) ) {
			return false;
		}

		return $options;
	}

	/**
	 * @param string $label
	 *
	 * @return array
	 */
	protected function get_empty_labels( $label = '' ) {
		if ( ! $label ) {
			$label = strtolower( $this->column->get_label() );
		}

		return array(
			sprintf( __( "Without %s", 'codepress-admin-columns' ), $label ),
			sprintf( __( "Has %s", 'codepress-admin-columns' ), $label ),
		);
	}

}
