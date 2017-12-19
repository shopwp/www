<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 4.0
 */
class ACP_Column_Post_Date extends AC_Column_Post_Date
	implements ACP_Column_FilteringInterface, ACP_Column_EditingInterface {

	public function editing() {
		return new ACP_Editing_Model_Post_Date( $this );
	}

	public function filtering() {
		return new ACP_Filtering_Model_Post_Date( $this );
	}

}
