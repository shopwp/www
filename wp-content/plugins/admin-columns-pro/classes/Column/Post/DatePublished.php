<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 2.4
 */
class ACP_Column_Post_DatePublished extends AC_Column_Post_DatePublished
	implements ACP_Column_SortingInterface {

	public function sorting() {
		$model = new ACP_Sorting_Model( $this );
		$model->set_orderby( 'date' );

		return $model;
	}

}
