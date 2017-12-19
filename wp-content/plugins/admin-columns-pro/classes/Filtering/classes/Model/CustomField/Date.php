<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Filtering_Model_CustomField_Date extends ACP_Filtering_Model_CustomField {

	public function __construct( $column ) {
		parent::__construct( $column );

		$this->set_data_type( 'date' );
	}

	public function get_filtering_data() {
		$options = array();

		foreach ( $this->get_meta_values() as $value ) {
			$options[ $value ] = $this->column->get_formatted_value( $value );
		}

		krsort( $options );

		return array(
			'empty_option' => true,
			'order'        => false,
			'options'      => $options,
		);
	}

}
