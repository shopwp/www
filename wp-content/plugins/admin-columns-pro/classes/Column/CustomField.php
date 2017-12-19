<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 4.0
 */
class ACP_Column_CustomField extends AC_Column_CustomField
	implements ACP_Column_SortingInterface, ACP_Column_EditingInterface, ACP_Column_FilteringInterface {

	/**
	 * @return ACP_Sorting_Model_Meta
	 */
	public function sorting() {
		return $this->get_field_object( 'ACP_Sorting_Model_CustomField' );
	}

	/**
	 * @return ACP_Editing_Model_Meta
	 */
	public function editing() {
		$class = $this->get_field_class_name( 'ACP_Editing_Model_CustomField' );

		if ( ! $class ) {
			$class = 'ACP_Editing_Model_Disabled';
		}

		return new $class( $this );
	}

	/**
	 * @return ACP_Filtering_Model_Meta
	 */
	public function filtering() {
		return $this->get_field_object( 'ACP_Filtering_Model_CustomField' );
	}

	/**
	 * Settings
	 */
	public function register_settings() {
		$this->add_setting( new ACP_Settings_Column_CustomField( $this ) );
		$this->add_setting( new AC_Settings_Column_BeforeAfter( $this ) );
	}

	/**
	 * Get the correct class for this meta field
	 *
	 * @param string $class
	 *
	 * @return ACP_Sorting_Model_Meta|ACP_Editing_Model_Meta|ACP_Filtering_Model_Meta
	 */
	private function get_field_object( $class ) {
		if ( $field_class = $this->get_field_class_name( $class ) ) {
			$class = $field_class;
		}

		return new $class( $this );
	}

	/**
	 * @param string $class
	 *
	 * @return bool|string
	 */
	private function get_field_class_name( $class ) {
		$field_class = $class;

		if ( $this->get_field_type() ) {
			$field_class = $class . '_' . AC_Autoloader::string_to_classname( $this->get_field_type() );
		}

		if ( ! class_exists( $field_class ) ) {
			return false;
		}

		return $field_class;
	}

}
