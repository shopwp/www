<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class ACP_Model {

	/**
	 * @var AC_Column
	 */
	protected $column;

	/**
	 * Strategy
	 *
	 * Handles model functionality for content types like posts, users, comments and taxonomies
	 *
	 * @var ACP_Sorting_Strategy|ACP_Filtering_Strategy|ACP_Editing_Strategy|ACP_Strategy
	 */
	protected $strategy;

	/**
	 * @var string
	 */
	private $data_type = 'string';

	/**
	 * @return bool
	 */
	abstract public function is_active();

	public function __construct( AC_Column $column ) {
		$this->column = $column;
	}

	/**
	 * @return AC_Column
	 */
	public function get_column() {
		return $this->column;
	}

	/**
	 * @return ACP_Filtering_Strategy|ACP_Editing_Strategy|ACP_Sorting_Strategy
	 */
	public function get_strategy() {
		return $this->strategy;
	}

	/**
	 * @param ACP_Strategy $strategy
	 *
	 * @return $this
	 */
	public function set_strategy( ACP_Strategy $strategy ) {
		$this->strategy = $strategy;

		return $this;
	}

	/**
	 * @param string $data_type
	 *
	 * @return $this
	 */
	public function set_data_type( $data_type ) {
		$data_type = strtolower( $data_type );

		if ( in_array( $data_type, array( 'string', 'numeric', 'date' ) ) ) {
			$this->data_type = $data_type;
		}

		return $this;
	}

	/**
	 * @return string
	 */
	public function get_data_type() {
		return $this->data_type;
	}

	/**
	 * @deprecated 4.0.3
	 * @param $disabled
	 */
	public function set_disabled( $disabled ) {
		_deprecated_function( __METHOD__, '4.0.3' );
	}

}
