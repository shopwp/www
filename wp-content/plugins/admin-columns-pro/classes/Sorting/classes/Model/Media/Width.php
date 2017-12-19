<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Sorting_Model_Media_Width extends ACP_Sorting_Model_Media_Dimensions {

	protected function get_aspect( $values ) {
		return $this->get_width( $values );
	}

}
