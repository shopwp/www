<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Settings_Column_CustomField extends AC_Settings_Column_CustomField {

	public function get_dependent_settings() {
		return array( new ACP_Settings_Column_CustomFieldType( $this->column ) );
	}

}
