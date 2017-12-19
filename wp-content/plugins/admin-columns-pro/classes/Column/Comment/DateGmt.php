<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 2.0
 */
class ACP_Column_Comment_DateGmt extends AC_Column_Comment_DateGmt
	implements ACP_Column_FilteringInterface, ACP_Column_SortingInterface {

	public function sorting() {
		$model = new ACP_Sorting_Model( $this );
		$model->set_orderby( 'comment_date_gmt' );

		return $model;
	}

	public function filtering() {
		return new ACP_Filtering_Model_Comment_DateGmt( $this );
	}

}
