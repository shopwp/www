<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 4.0
 */
class ACP_Column_Comment_AuthorIP extends AC_Column_Comment_AuthorIP
	implements ACP_Column_FilteringInterface, ACP_Column_SortingInterface {

	public function sorting() {
		$model = new ACP_Sorting_Model( $this );
		$model->set_orderby( 'comment_author_IP' );

		return $model;
	}

	public function filtering() {
		return new ACP_Filtering_Model_Comment_AuthorIP( $this );
	}

}
