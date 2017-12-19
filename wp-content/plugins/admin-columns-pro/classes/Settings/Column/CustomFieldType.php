<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Settings_Column_CustomFieldType extends AC_Settings_Column_CustomFieldType {

	public function get_dependent_settings() {
		$settings = parent::get_dependent_settings();

		switch ( $this->get_field_type() ) {

			case 'title_by_id' :
				$settings[] = new AC_Settings_Column_Post( $this->column );

				break;
			case 'user_by_id' :
				$settings[] = new AC_Settings_Column_User( $this->column );

				break;
		}

		return $settings;
	}

	public function format( $value, $original_value ) {

		switch ( $this->get_field_type() ) {

			case 'title_by_id' :
			case 'user_by_id' :
				$string = ac_helper()->array->implode_recursive( ',', $value );
				$ids = ac_helper()->string->string_to_array_integers( $string );

				$value = new AC_Collection( $ids );

				break;
			default :
				$value = parent::format( $value, $original_value );
		}

		return $value;
	}

	/**
	 * Get possible field types
	 *
	 * @return array
	 */
	protected function get_field_type_options() {
		$field_types = parent::get_field_type_options();

		$field_types['relational']['title_by_id'] = __( 'Post', 'codepress-admin-columns' );
		$field_types['relational']['user_by_id'] = __( 'User', 'codepress-admin-columns' );

		return $field_types;
	}

}
