<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 4.0
 */
class ACP_Column_Post_Tags extends AC_Column_Post_Tags
	implements ACP_Column_FilteringInterface, ACP_Column_SortingInterface, ACP_Column_EditingInterface {

	public function sorting() {
		return new ACP_Sorting_Model_Post_Taxonomy( $this );
	}

	public function editing() {
		return new ACP_Editing_Model_Post_Taxonomy( $this );
	}

	public function filtering() {
		return new ACP_Filtering_Model_Post_Taxonomy( $this );
	}

}
