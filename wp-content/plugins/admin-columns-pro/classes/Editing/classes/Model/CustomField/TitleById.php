<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Editing_Model_CustomField_TitleById extends ACP_Editing_Model_CustomField {

	public function get_edit_value( $id ) {
		$raw = $this->column->get_raw_value( $id );

		/**
		 * @var ACP_Settings_Column_CustomFieldType $field_type
		 */
		$field_type = $this->column->get_setting( 'field_type' );

		// Post ID's
		$ids = $field_type->format( $raw, $id )->all();

		$values = false;
		foreach ( $ids as $id ) {
			$values[ $id ] = ac_helper()->post->get_title( $id );
		}

		return $values;
	}

	public function get_view_settings() {
		return array(
			'type'          => 'select2_dropdown',
			'ajax_populate' => true,
		);
	}

	public function get_ajax_options( $request ) {
		return acp_editing_helper()->get_posts_list( array(
			's'     => $request['search'],
			'paged' => $request['paged'],
		) );
	}

}
