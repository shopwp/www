<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 4.0
 */
abstract class ACP_Column_Meta extends AC_Column_Meta
	implements ACP_Column_SortingInterface, ACP_Column_EditingInterface, ACP_Column_FilteringInterface {

	/**
	 * @return ACP_Sorting_Model_Meta
	 */
	public function sorting() {
		return new ACP_Sorting_Model_Meta( $this );
	}

	/**
	 * @return ACP_Editing_Model_Meta
	 */
	public function editing() {
		return new ACP_Editing_Model_Meta( $this );
	}

	/**
	 * @return ACP_Filtering_Model_Meta
	 */
	public function filtering() {
		return new ACP_Filtering_Model_Meta( $this );
	}

}
