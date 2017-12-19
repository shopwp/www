<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

interface ACP_Column_EditingInterface  {

	/**
	 * Return the editing model for this column
	 *
	 * @return ACP_Editing_Model
	 */
	public function editing();

}
