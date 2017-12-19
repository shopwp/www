<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Column_Post_Excerpt extends AC_Column_Post_Excerpt
	implements ACP_Column_SortingInterface, ACP_Column_EditingInterface, ACP_Column_FilteringInterface {

	public function sorting() {
		return new ACP_Sorting_Model_Value( $this );
	}

	public function filtering() {
		return new ACP_Filtering_Model_Post_Excerpt( $this );
	}

	public function editing() {
		return new ACP_Editing_Model_Post_Excerpt( $this );
	}

}
