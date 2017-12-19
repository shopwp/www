<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 2.0
 */
class ACP_Column_Comment_ReplyTo extends AC_Column_Comment_ReplyTo
	implements ACP_Column_FilteringInterface, ACP_Column_SortingInterface {

	public function sorting() {
		$model = new ACP_Sorting_Model( $this );
		$model->set_orderby( 'comment_parent' );

		return $model;
	}

	public function filtering() {
		return new ACP_Filtering_Model_Comment_ReplyTo( $this );
	}

}
