<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @property AC_Column_Post_PageTemplate $column
 */
class ACP_Editing_Model_Post_PageTemplate extends ACP_Editing_Model {

	public function __construct( AC_Column_Post_PageTemplate $column ) {
		parent::__construct( $column );
	}

	public function get_view_settings() {
		return array(
			'type'    => 'select',
			'options' => array_merge( array( '' => __( 'Default Template' ) ), array_flip( (array) $this->column->get_page_templates() ) ),
		);
	}

	public function save( $id, $value ) {
		update_post_meta( $id, '_wp_page_template', $value );
	}

}
