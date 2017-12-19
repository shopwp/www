<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 4.0
 */
class ACP_Column_Comment_Author extends AC_Column_Comment_Author
	implements ACP_Column_FilteringInterface {

	public function filtering() {
		return new ACP_Filtering_Model_Comment_Author( $this );
	}

}
