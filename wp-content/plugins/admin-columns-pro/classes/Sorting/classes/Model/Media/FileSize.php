<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Sorting_Model_Media_FileSize extends ACP_Sorting_Model {

	public function get_sorting_vars() {
		$ids = array();

		foreach ( $this->strategy->get_results() as $id ) {
			$value = false;

			if ( $file = get_attached_file( $id ) ) {
				$value = is_file( $file ) ? filesize( $file ) : false;
			}

			if ( $value || acp_sorting()->show_all_results() ) {
				$ids[ $id ] = $value;
			}
		}

		return array(
			'ids' => $this->sort( $ids ),
		);
	}
}
