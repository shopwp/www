<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class ACP_Filtering_Strategy extends ACP_Strategy {

	/**
	 * @param string $field
	 *
	 * @return array|false
	 */
	abstract public function get_values_by_db_field( $field );

}
