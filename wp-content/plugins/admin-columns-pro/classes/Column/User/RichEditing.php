<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Column_User_RichEditing extends AC_Column_User_RichEditing
	implements ACP_Column_EditingInterface, ACP_Column_FilteringInterface, ACP_Column_SortingInterface {

	public function editing() {
		return new ACP_Editing_Model_User_RichEditing( $this );
	}

	public function filtering() {
		return new ACP_Filtering_Model_User_RichEditing( $this );
	}

	public function sorting() {
		return new ACP_Sorting_Model( $this );
	}

}
