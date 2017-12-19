<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 4.0
 */
class ACP_Column_Comment_Comment extends AC_Column_Comment_Comment
	implements ACP_Column_EditingInterface, ACP_Column_SortingInterface {

	public function editing() {
		return new ACP_Editing_Model_Comment_Comment( $this );
	}

	public function sorting() {
		$model = new ACP_Sorting_Model( $this );
		$model->set_orderby( 'comment_content' );

		return $model;
	}
}
