<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Column_Media_Taxonomy extends AC_Column_Media_Taxonomy
	implements ACP_Column_SortingInterface, ACP_Column_EditingInterface {

	public function sorting() {
		return new ACP_Sorting_Model_Post_Taxonomy( $this );
	}

	public function editing() {
		return new ACP_Editing_Model_Post_Taxonomy( $this );
	}

}
