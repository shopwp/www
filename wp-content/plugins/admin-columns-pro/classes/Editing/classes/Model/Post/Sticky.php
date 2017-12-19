<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Editing_Model_Post_Sticky extends ACP_Editing_Model {

	public function get_view_settings() {
		return array(
			'type'    => 'togglable',
			'options' => array( 'no', 'yes' ),
		);
	}

	public function get_edit_value( $id ) {
		$value = parent::get_edit_value( $id );

		return $value ? 'yes' : 'no';
	}

	public function save( $id, $value ) {
		if ( 'yes' == $value ) {
			stick_post( $id );
		} else {
			unstick_post( $id );
		}
	}

}
