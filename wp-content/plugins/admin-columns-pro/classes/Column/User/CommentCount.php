<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 2.0
 */
class ACP_Column_User_CommentCount extends AC_Column_User_CommentCount
	implements ACP_Column_SortingInterface {

	public function sorting() {
		return new ACP_Sorting_Model_User_CommentCount( $this );
	}

	public function editing() {
		return new ACP_Editing_Model_User_CommentCount( $this );
	}

}
