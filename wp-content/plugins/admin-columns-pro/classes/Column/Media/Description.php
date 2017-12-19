<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Column_Media_Description extends AC_Column_Media_Description
	implements ACP_Column_EditingInterface, ACP_Column_FilteringInterface, ACP_Column_SortingInterface {

	public function sorting() {
		return new ACP_Sorting_Model( $this );
	}

	public function editing() {
		return new ACP_Editing_Model_Post_Content( $this );
	}

	public function filtering() {
		return new ACP_Filtering_Model_Post_Content( $this );
	}

}
