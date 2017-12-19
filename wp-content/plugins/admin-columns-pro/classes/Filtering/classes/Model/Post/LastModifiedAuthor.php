<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Filtering_Model_Post_LastModifiedAuthor extends ACP_Filtering_Model_Meta {

	public function get_filtering_data() {
		$data = array();

		if ( $values = $this->get_meta_values() ) {
			foreach ( $values as $user_id ) {
				$data['options'][ $user_id ] = ac_helper()->user->get_display_name( $user_id );
			}
		}

		return $data;
	}

}
