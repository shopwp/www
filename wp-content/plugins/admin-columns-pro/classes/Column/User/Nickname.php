<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 2.0
 */
class ACP_Column_User_Nickname extends AC_Column_User_Nickname
	implements ACP_Column_EditingInterface, ACP_Column_SortingInterface {

	public function sorting() {
		return new ACP_Sorting_Model( $this );
	}

	public function editing() {
		return new ACP_Editing_Model_Meta( $this );
	}

}
