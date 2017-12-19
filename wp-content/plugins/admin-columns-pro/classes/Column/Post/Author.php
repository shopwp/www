<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 4.0
 */
class ACP_Column_Post_Author extends AC_Column_Post_Author
	implements ACP_Column_EditingInterface, ACP_Column_SortingInterface {

	public function sorting() {
		$model = new ACP_Sorting_Model( $this );
		$model->set_orderby( 'author' );

		return $model;
	}

	public function editing() {
		return new ACP_Editing_Model_Post_Author( $this );
	}

}
