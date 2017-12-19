<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @property AC_Column_CustomField $column
 */
class ACP_Sorting_Model_CustomField_Checkmark extends ACP_Sorting_Model_CustomField {

	public function __construct( AC_Column_CustomField $column ) {
		parent::__construct( $column );

		$this->set_data_type( 'numeric' );
	}

}
