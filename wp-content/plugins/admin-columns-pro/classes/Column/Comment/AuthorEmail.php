<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 4.0
 */
class ACP_Column_Comment_AuthorEmail extends AC_Column_Comment_AuthorEmail
	implements ACP_Column_EditingInterface, ACP_Column_FilteringInterface, ACP_Column_SortingInterface {

	public function sorting() {
		$model = new ACP_Sorting_Model( $this );
		$model->set_orderby( 'comment_author_email' );

		return $model;
	}

	public function editing() {
		return new ACP_Editing_Model_Comment_AuthorEmail( $this );
	}

	public function filtering() {
		return new ACP_Filtering_Model_Comment_AuthorEmail( $this );
	}

}
