<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Column_User_Registered extends AC_Column_User_Registered
	implements ACP_Column_FilteringInterface, ACP_Column_SortingInterface {

	public function sorting() {
		$model = new ACP_Sorting_Model( $this );
		$model->set_orderby( 'registered' );

		return $model;
	}

	public function filtering() {
		return new ACP_Filtering_Model_User_Registered( $this );
	}

}
