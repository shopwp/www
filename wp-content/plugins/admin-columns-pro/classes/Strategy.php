<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class ACP_Strategy {

	/**
	 * @var AC_Column|AC_Column_Meta
	 */
	protected $column;

	/**
	 * @var ACP_Sorting_Model|ACP_Filtering_Model|ACP_Editing_Model|ACP_Model
	 */
	protected $model;

	/**
	 * @param ACP_Model $model
	 */
	public function __construct( ACP_Model $model ) {
		$this->model = $model;
		$this->column = $model->get_column();
	}

	/**
	 * @return AC_Column
	 */
	public function get_column() {
		return $this->column;
	}

	/**
	 * @return ACP_Model
	 */
	public function get_model() {
		return $this->model;
	}

}
