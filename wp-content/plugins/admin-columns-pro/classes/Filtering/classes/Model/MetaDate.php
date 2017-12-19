<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Filtering_Model_MetaDate extends ACP_Filtering_Model_Meta {

	/**
	 * @var string
	 */
	private $date_format = 'Y-m-d';

	public function __construct( $column ) {
		parent::__construct( $column );

		$this->set_data_type( 'date' );
	}

	/**
	 * @param string $date_format
	 */
	protected function set_date_format( $date_format ) {
		$this->date_format = $date_format;
	}

	/**
	 * @return string
	 */
	protected function get_date_format() {
		return $this->date_format;
	}

	/**
	 * Adds Meta Query vars for dates
	 *
	 * @since 4.0
	 *
	 * @param array $vars Query args
	 *
	 * @return array
	 */
	public function get_filtering_vars( $vars ) {
		$value = $this->get_filter_value();

		// Empty or nonempty
		if ( in_array( $value, array( 'cpac_empty', 'cpac_nonempty' ) ) ) {
			return $this->get_filtering_vars_empty_nonempty( $vars );
		}

		$args = array();

		// Ranged
		if ( $this->is_ranged() ) {

			if ( $value['min'] ) {
				$args['min'] = date( $this->get_date_format(), strtotime( $value['min'] ) );
			}

			if ( $value['max'] ) {
				$args['max'] = date( $this->get_date_format(), strtotime( $value['max'] ) );
			}

			return $this->get_filtering_vars_ranged( $vars, $args );
		}

		// Date formats
		switch ( $this->get_filter_format() ) {

			case 'future_past' :
				if ( $date = $this->get_date_time_object() ) {
					$key = 'future' === $value ? 'min' : 'max';
					$args[ $key ] = $date->format( $this->get_date_format() );

					return $this->get_filtering_vars_ranged( $vars, $args );
				}

				break;
			case 'yearly' :
				if ( $date = $this->get_date_time_object( $value . '0101' ) ) {

					$args['min'] = $date->format( $this->get_date_format() );
					$args['max'] = $date->modify( '+1 year' )->modify( '-1 day' )->format( $this->get_date_format() );

					return $this->get_filtering_vars_ranged( $vars, $args );
				}

				break;
			case 'monthly' :
				if ( $date = $this->get_date_time_object( $value . '01' ) ) {

					$args['min'] = $date->format( $this->get_date_format() );
					$args['max'] = $date->modify( '+1 month' )->format( $this->get_date_format() );

					return $this->get_filtering_vars_ranged( $vars, $args );
				}

				break;
			case 'daily' :
				if ( $date = $this->get_date_time_object( $value ) ) {

					$args['min'] = $date->format( $this->get_date_format() );
					$args['max'] = $date->modify( '+1 day' )->format( $this->get_date_format() );

					return $this->get_filtering_vars_ranged( $vars, $args );
				}
		}

		return $this->get_filtering_vars_empty_nonempty( $vars );
	}

	public function get_filtering_data() {
		$format = $this->get_filter_format();

		$options = $this->get_date_options_relative( $format );

		if ( ! $options ) {
			$options = $this->get_date_options( $this->get_meta_values(), $format, $this->get_date_format() );
		}

		return array(
			'empty_option' => true,
			'order'        => false,
			'options'      => $options,
		);
	}

	private function get_filter_format() {
		$format = $this->column->get_setting( 'filter' )->get_value( 'filter_format' );

		if ( ! $format ) {
			$format = 'daily';
		}

		return $format;
	}

	/**
	 * @param string $date
	 *
	 * @return DateTime|false
	 */
	private function get_date_time_object( $date = null ) {
		try {
			return new DateTime( $date );
		} catch ( Exception $e ) {
			return false;
		}
	}

	public function register_settings() {
		$this->column->add_setting( new ACP_Filtering_Settings_Date( $this->column ) );
	}

}
