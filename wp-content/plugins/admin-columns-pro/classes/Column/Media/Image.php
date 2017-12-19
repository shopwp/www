<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 2.0
 */
class ACP_Column_Media_Image extends AC_Column {

	public function __construct() {
		$this->set_type( 'column-image' );
		$this->set_label( __( 'Image', 'codepress-admin-columns' ) );
	}

	public function register_settings() {
		$this->add_setting( new AC_Settings_Column_Image( $this ) );
	}

}
