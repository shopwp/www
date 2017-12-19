<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Column_Media_MimeType extends AC_Column_Media_MimeType
	implements ACP_Column_EditingInterface, ACP_Column_FilteringInterface, ACP_Column_SortingInterface {

	public function sorting() {
		return new ACP_Sorting_Model_Media_MimeType( $this );
	}

	public function editing() {
		return new ACP_Editing_Model_Media_MimeType( $this );
	}

	public function filtering() {
		return new ACP_Filtering_Model_Media_MimeType( $this );
	}

}
