<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Filtering_Model_Post_Date extends ACP_Filtering_Model {

	public function __construct( $column ) {
		parent::__construct( $column );

		$this->set_data_type( 'date' );
	}

	public function register_settings() {
		$this->column->add_setting( new ACP_Filtering_Settings_Date( $this->column ) );
	}

	public function get_filtering_vars( $vars ) {

		switch ( $this->get_filter_format() ) {

			case 'monthly' :
				$timestamp = strtotime( $this->get_filter_value() . '01' );

				$vars['date_query'][] = array(
					'year'  => date( 'Y', $timestamp ),
					'month' => date( 'm', $timestamp ),
				);
				break;
			case 'yearly' :
				$vars['date_query'][] = array(
					'year' => $this->get_filter_value(),
				);
				break;
			case 'future_past' :
				$date = date( 'Y-m-d' );

				if ( 'future' == $this->get_filter_value() ) {
					$vars['date_query'][] = array(
						'inclusive' => true,
						'after'     => $date,
					);
				} else {
					$vars['date_query'][] = array(
						'inclusive' => true,
						'before'    => $date,
					);
				}
				break;
			case 'range' :
				$value = $this->get_filter_value();

				if ( $value['min'] || $value['max'] ) {
					$vars['date_query'][] = array(
						'inclusive' => true,
						'before'    => $value['max'],
						'after'     => $value['min'],
					);
				}
				break;
			case 'daily' :
			default :
				$timestamp = strtotime( $this->get_filter_value() );

				$vars['date_query'][] = array(
					'year'  => date( 'Y', $timestamp ),
					'month' => date( 'm', $timestamp ),
					'day'   => date( 'd', $timestamp ),
				);

				break;
		}

		return $vars;
	}

	/**
	 * @return array
	 */
	public function get_filtering_data() {
		$format = $this->get_filter_format();

		if ( ! $format ) {
			$format = 'daily';
		}

		if ( 'monthly' === $format ) {
			add_filter( 'disable_months_dropdown', '__return_true' );
		}

		$options = $this->get_date_options_relative( $format );

		if ( ! $options ) {
			$options = $this->get_date_options( $this->get_dates(), $format, 'Y-m-d H:i:s' );
		}

		return array(
			'options' => $options,
		);
	}

	/**
	 * @param string $field
	 *
	 * @return array
	 */
	private function get_dates( $field = 'post_date' ) {
		global $wpdb;

		$field = sanitize_key( $field );

		$query = "
			SELECT $field AS `date`
			FROM $wpdb->posts
			WHERE post_type = %s
			ORDER BY `date`
		";

		$sql = $wpdb->prepare( $query, $this->column->get_post_type() );

		return $wpdb->get_col( $sql );
	}

	private function get_filter_format() {
		return $this->column->get_setting( 'filter' )->get_value( 'filter_format' );
	}

}
