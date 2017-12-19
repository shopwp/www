<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Column_Media_Dimensions extends AC_Column_Media_Dimensions
	implements ACP_Column_SortingInterface {

	public function sorting() {
		return new ACP_Sorting_Model_Media_Dimensions( $this );
	}

}
