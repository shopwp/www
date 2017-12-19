<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @property ACP_Column_NetworkSite_Option $column
 */
class ACP_Editing_Model_Site_Option extends ACP_Editing_Model {

	public function __construct( ACP_Column_NetworkSite_Option $column ) {
		parent::__construct( $column );
	}

	public function save( $id, $value ) {
		switch_to_blog( $id );

		update_option( $this->column->get_option_name(), $value );

		restore_current_blog();
	}

}
