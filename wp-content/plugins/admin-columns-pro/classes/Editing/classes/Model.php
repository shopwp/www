<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 4.0
 */
class ACP_Editing_Model extends ACP_Model {

	/**
	 * @return bool True when editing is enabled by user
	 */
	public function is_active() {
		$setting = $this->column->get_setting( 'edit' );

		if ( ! $setting instanceof ACP_Editing_Settings ) {
			return false;
		}

		return $setting->is_active();
	}

	/**
	 * Get editing settings
	 *
	 * @return array {
	 * @type string     $type          Type of form field. Accepts: attachment, checkboxlist, checklist, color, float, email, media, number, password, select, select2_dropdown, select2_tags, text, textarea, togglable or url. Default is 'text.
	 * @type string     $placeholder   Add a placeholder text. Only applies to type: text, url, number, password, email.
	 * @type array      $options       Options for select form element. Only applies to type: togglable, select, select2_dropdown and select2_tags.
	 * @type string     $js            If a selector is provided, editable will be delegated to the specified targets. Example: [ 'js' => [ 'selector' => 'a.my-class' ] ];
	 * @type bool       $ajax_populate Populates the available select2 dropdown values through ajax. Only applies to the type: 'select2_dropdown'. Ajax callback used is 'get_editable_ajax_options()'.
	 * @type string|int $range_step    Determines the number intervals for the 'number' type field. Default is 'any'.
	 * @type string     $store_values  If a field can hold multiple values we store the key unless $store_values is set to (bool) true. Default is (bool) false.
	 * }
	 */
	public function get_view_settings() {
		return array(
			'type' => 'text',
		);
	}

	/**
	 * DB value used for storing the edited data
	 *
	 * @param int $id
	 *
	 * @return array|stdClass|string
	 */
	public function get_edit_value( $id ) {
		return $this->column->get_raw_value( $id );
	}

	/**
	 * Get editing options when using an ajax callback
	 *
	 * @param array $request
	 *
	 * @return array
	 */
	public function get_ajax_options( $request ) {
		return array();
	}

	/**
	 * @since 4.0
	 *
	 * @param int          $id
	 * @param string|array $value
	 *
	 * @return bool|WP_Error
	 */
	public function save( $id, $value ) {
		return false;
	}

	/**
	 * Register column field settings
	 */
	public function register_settings() {
		$this->column->add_setting( new ACP_Editing_Settings( $this->column ) );
	}

}
