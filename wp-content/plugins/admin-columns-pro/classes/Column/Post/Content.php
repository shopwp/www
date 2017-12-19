<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 2.4
 */
class ACP_Column_Post_Content extends AC_Column_Post_Content
	implements ACP_Column_EditingInterface, ACP_Column_SortingInterface, ACP_Column_FilteringInterface {

	public function editing() {
		return new ACP_Editing_Model_Post_Content( $this );
	}

	public function filtering() {
		return new ACP_Filtering_Model_Post_Content( $this );
	}

	public function sorting() {
		$model = new ACP_Sorting_Model_Post_Field( $this );
		$model->set_field( 'post_content' );

		return $model;
	}

}
