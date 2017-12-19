<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Sorting_Model_Media_Height extends ACP_Sorting_Model_Media_Dimensions {

	protected function get_aspect( $values ) {
		return $this->get_height( $values );
	}

}
