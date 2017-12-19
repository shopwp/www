<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 4.0
 */
class ACP_Column_Media_Width extends AC_Column_Media_Width
	implements ACP_Column_SortingInterface {

	public function sorting() {
		return new ACP_Sorting_Model_Media_Width( $this );
	}

}
