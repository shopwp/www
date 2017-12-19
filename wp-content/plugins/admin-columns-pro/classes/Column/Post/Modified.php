<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Column_Post_Modified extends AC_Column_Post_Modified
	implements ACP_Column_SortingInterface {

	public function sorting() {
		$model = new ACP_Sorting_Model( $this );
		$model->set_orderby( 'modified' );

		return $model;
	}

}
