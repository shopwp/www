<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Column_UsedByMenu extends AC_Column_UsedByMenu
	implements ACP_Column_SortingInterface {

	public function sorting() {
		return new ACP_Sorting_Model( $this );
	}

}
