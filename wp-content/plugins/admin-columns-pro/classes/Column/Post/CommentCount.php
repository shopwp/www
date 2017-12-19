<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 4.0
 */
class ACP_Column_Post_CommentCount extends AC_Column_Post_CommentCount
	implements ACP_Column_FilteringInterface, ACP_Column_SortingInterface {

	public function sorting() {
		$model = new ACP_Sorting_Model( $this );
		$model->set_orderby( 'comment_count' );

		return $model;
	}

	public function filtering() {
		return new ACP_Filtering_Model_Post_CommentCount( $this );
	}

}
