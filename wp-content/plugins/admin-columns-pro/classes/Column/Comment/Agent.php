<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 2.0
 */
class ACP_Column_Comment_Agent extends AC_Column_Comment_Agent
	implements ACP_Column_FilteringInterface, ACP_Column_SortingInterface {

	public function sorting() {
		$model = new ACP_Sorting_Model( $this );
		$model->set_orderby( 'comment_agent' );

		return $model;
	}

	public function filtering() {
		return new ACP_Filtering_Model_Comment_Agent( $this );
	}

}
