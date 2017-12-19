<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 4.0
 */
class ACP_Column_Post_Sticky extends AC_Column_Post_Sticky
	implements ACP_Column_EditingInterface, ACP_Column_FilteringInterface, ACP_Column_SortingInterface {

	public function sorting() {
		return new ACP_Sorting_Model_Post_Sticky( $this );
	}

	public function editing() {
		return new ACP_Editing_Model_Post_Sticky( $this );
	}

	public function filtering() {
		return new ACP_Filtering_Model_Post_Sticky( $this );
	}

}
