<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 4.0
 */
class ACP_Column_Media_Title extends AC_Column_Media_Title
	implements ACP_Column_EditingInterface {

	public function editing() {
		return new ACP_Editing_Model_Media_Title( $this );
	}

}
