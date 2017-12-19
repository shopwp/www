<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Column displaying whether an item is open for comments, i.e. whether users can
 * comment on this item.
 *
 * @since 2.0
 */
class ACP_Column_Post_CommentStatus extends AC_Column_Post_CommentStatus
	implements ACP_Column_EditingInterface, ACP_Column_FilteringInterface, ACP_Column_SortingInterface {

	public function sorting() {
		return new ACP_Sorting_Model( $this );
	}

	public function editing() {
		return new ACP_Editing_Model_Post_CommentStatus( $this );
	}

	public function filtering() {
		return new ACP_Filtering_Model_Post_CommentStatus( $this );
	}

}
