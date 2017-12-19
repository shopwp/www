<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @property AC_Column_CustomField $column
 */
class ACP_Filtering_Model_CustomField extends ACP_Filtering_Model_Meta {

	public function __construct( AC_Column_CustomField $column ) {
		parent::__construct( $column );
	}

}
