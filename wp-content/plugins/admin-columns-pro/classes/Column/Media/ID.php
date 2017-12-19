<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Column_Media_ID extends AC_Column_Media_ID
	implements ACP_Column_SortingInterface {

	public function sorting() {
		$model = new ACP_Sorting_Model( $this );
		$model->set_orderby( 'ID' );

		return $model;
	}

}
