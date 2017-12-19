<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

interface ACP_Column_FilteringInterface  {

	/**
	 * Return the filtering model for this column
	 *
	 * @return ACP_Filtering_ModelInterface|ACP_Filtering_Model
	 */
	public function filtering();

}
