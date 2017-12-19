<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Editing_Model_Meta extends ACP_Editing_Model {

	/**
	 * @var AC_Column_Meta
	 */
	protected $column;

	public function __construct( AC_Column_Meta $column ) {
		parent::__construct( $column );
	}

	public function get_view_settings() {
		return array(
			'type'        => 'text',
			'placeholder' => $this->column->get_label(),
		);
	}

	public function save( $id, $value ) {
		update_metadata( $this->column->get_meta_type(), $id, $this->column->get_meta_key(), $value );
	}

}
