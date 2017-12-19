<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Editing_Model_Post_Order extends ACP_Editing_Model {

	public function get_edit_value( $id ) {
		return get_post_field( 'menu_order', $id );
	}

	public function save( $id, $value ) {
		$this->strategy->update( $id, array( 'menu_order' => $value ) );

		return $value;
	}

}
