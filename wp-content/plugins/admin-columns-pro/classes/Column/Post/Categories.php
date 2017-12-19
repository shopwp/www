<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 4.0
 */
class ACP_Column_Post_Categories extends AC_Column_Post_Categories
	implements ACP_Column_SortingInterface, ACP_Column_EditingInterface, ACP_Column_FilteringInterface {

	public function sorting() {
		return new ACP_Sorting_Model_Post_Taxonomy( $this );
	}

	public function editing() {
		return new ACP_Editing_Model_Post_Taxonomy( $this );
	}

	public function filtering() {
		$model = new ACP_Filtering_Model_Delegated( $this );
		$model->set_dropdown_attr_id( 'cat' );

		return $model;
	}

}
