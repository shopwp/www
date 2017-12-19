<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Column_Post_ID extends AC_Column_Post_ID
	implements ACP_Column_SortingInterface, ACP_Column_FilteringInterface {

	public function sorting() {
		$model = new ACP_Sorting_Model( $this );
		$model->set_orderby( 'ID' );

		return $model;
	}

	public function filtering() {
		return new ACP_Filtering_Model_Post_ID( $this );
	}

}
