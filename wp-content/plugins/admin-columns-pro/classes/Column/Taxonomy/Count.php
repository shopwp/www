<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 4.0
 */
class ACP_Column_Taxonomy_Count extends AC_Column {

	public function __construct() {
		$this->set_original( true );
		$this->set_type( 'posts' );
	}

	public function register_settings() {
		$width = $this->get_setting( 'width' );

		$width->set_default( 74 );
		$width->set_default( 'px', 'width_unit' );
	}

}
