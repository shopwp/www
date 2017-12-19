<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 4.0
 */
class ACP_Column_Media_Parent extends AC_Column_Media_Parent
	implements ACP_Column_FilteringInterface {

	public function filtering() {
		return new ACP_Filtering_Model_Post_Parent( $this );
	}

}
