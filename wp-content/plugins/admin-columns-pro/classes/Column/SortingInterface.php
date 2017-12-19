<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

interface ACP_Column_SortingInterface  {

	/**
	 * Return the sortable model for this column
	 *
	 * @return ACP_Sorting_Model
	 */
	public function sorting();

}
