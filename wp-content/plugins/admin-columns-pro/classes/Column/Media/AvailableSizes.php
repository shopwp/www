<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Column_Media_AvailableSizes extends AC_Column_Media_AvailableSizes
	implements ACP_Column_SortingInterface {

	public function sorting(){
		return new ACP_Sorting_Model_Media_AvailableSizes( $this );
	}

}
