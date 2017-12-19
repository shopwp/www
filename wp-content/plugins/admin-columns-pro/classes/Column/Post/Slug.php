<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Column_Post_Slug extends AC_Column_Post_Slug
	implements ACP_Column_SortingInterface, ACP_Column_EditingInterface {

	public function sorting() {
		$model = new ACP_Sorting_Model_Post_Field( $this );
		$model->set_field( 'post_name' );

		return $model;
	}

	public function editing() {
		return new ACP_Editing_Model_Post_Slug( $this );
	}

}
