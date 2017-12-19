<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 2.0
 */
class ACP_Column_Post_LastModifiedAuthor extends AC_Column_Post_LastModifiedAuthor
	implements ACP_Column_FilteringInterface, ACP_Column_SortingInterface {

	public function sorting() {
		return new ACP_Sorting_Model_Post_LastModifiedAuthor( $this );
	}

	public function filtering() {
		return new ACP_Filtering_Model_Post_LastModifiedAuthor( $this );
	}

}
