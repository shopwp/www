<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Column_Post_Order extends AC_Column_Post_Order
	implements ACP_Column_SortingInterface, ACP_Column_EditingInterface {

	public function sorting() {
		$model = new ACP_Sorting_Model( $this );
		$model->set_orderby( 'menu_order' );

		return $model;
	}

	public function editing() {
		return new ACP_Editing_Model_Post_Order( $this );
	}

}
