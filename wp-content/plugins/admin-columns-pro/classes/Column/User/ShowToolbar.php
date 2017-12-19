<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 4.0
 */
class ACP_Column_User_ShowToolbar extends AC_Column_User_ShowToolbar
	implements ACP_Column_FilteringInterface, ACP_Column_SortingInterface, ACP_Column_EditingInterface {

	public function sorting() {
		return new ACP_Sorting_Model( $this );
	}

	public function editing() {
		return new ACP_Editing_Model_User_ShowToolbar( $this );
	}

	public function filtering() {
		return new ACP_Filtering_Model_User_ShowToolbar( $this );
	}

}
