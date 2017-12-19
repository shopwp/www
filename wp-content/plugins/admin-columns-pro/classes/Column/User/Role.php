<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 4.0
 */
class ACP_Column_User_Role extends AC_Column_User_Role
	implements ACP_Column_EditingInterface, ACP_Column_FilteringInterface, ACP_Column_SortingInterface {

	public function sorting() {
		return new ACP_Sorting_Model_User_Roles( $this );
	}

	public function editing() {
		return new ACP_Editing_Model_User_Role( $this );
	}

	public function filtering() {
		return new ACP_Filtering_Model_User_Role( $this );
	}

}
