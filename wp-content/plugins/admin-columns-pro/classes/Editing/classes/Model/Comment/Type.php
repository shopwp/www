<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Editing_Model_Comment_Type extends ACP_Editing_Model {

	public function save( $id, $value ) {
		$this->strategy->update( $id, array( 'comment_type' => $value ) );
	}

}
