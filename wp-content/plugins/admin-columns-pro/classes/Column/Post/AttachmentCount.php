<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 4.0
 */
class ACP_Column_Post_AttachmentCount extends AC_Column_Post_AttachmentCount
	implements ACP_Column_SortingInterface {

	public function sorting() {
		$model = new ACP_Sorting_Model( $this );
		$model->set_data_type( 'numeric' );

		return $model;
	}

}
