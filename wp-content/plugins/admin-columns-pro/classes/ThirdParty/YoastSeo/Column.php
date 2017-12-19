<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class ACP_ThirdParty_YoastSeo_Column extends AC_Column {

	public function __construct() {
		$this->set_original( true );
		$this->set_group( 'yoast-seo' );
	}

}
