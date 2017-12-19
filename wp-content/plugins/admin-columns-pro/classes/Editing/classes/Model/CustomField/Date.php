<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Editing_Model_CustomField_Date extends ACP_Editing_Model_CustomField {

	public function get_edit_value( $id ) {
		$timestamp = ac_helper()->date->strtotime( parent::get_edit_value( $id ) );

		if ( ! $timestamp ) {
			return false;
		}

		return date( 'Ymd', $timestamp );
	}

	public function get_view_settings() {
		return array(
			'type' => 'date',
		);
	}

}
