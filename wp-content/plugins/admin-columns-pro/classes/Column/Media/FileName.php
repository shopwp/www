<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 4.0
 */
class ACP_Column_Media_FileName extends AC_Column_Media_FileName
	implements ACP_Column_SortingInterface {

	public function sorting() {
		return new ACP_Sorting_Model_Media_FileName( $this );
	}

}
