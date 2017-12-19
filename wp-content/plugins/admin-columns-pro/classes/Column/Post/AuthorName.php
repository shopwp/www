<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 4.0
 */
class ACP_Column_Post_AuthorName extends AC_Column_Post_AuthorName
	implements ACP_Column_EditingInterface, ACP_Column_FilteringInterface, ACP_Column_SortingInterface {

	public function sorting() {
		return new ACP_Sorting_Model_Post_AuthorName( $this );
	}

	public function editing() {
		return new ACP_Editing_Model_Post_Author( $this );
	}

	public function filtering() {
		return new ACP_Filtering_Model_Post_AuthorName( $this );
	}

}
