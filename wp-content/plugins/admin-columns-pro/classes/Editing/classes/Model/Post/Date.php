<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Editing_Model_Post_Date extends ACP_Editing_Model {

	public function get_edit_value( $id ) {
		$post = get_post( $id );

		if ( ! $post ) {
			return false;
		}

		return date( 'Ymd', strtotime( $post->post_date ) );
	}

	public function get_view_settings() {
		return array(
			'type' => 'date',
		);
	}

	public function save( $id, $value ) {
		$post = get_post( $id );

		// preserve the original time
		$time = strtotime( "1970-01-01 " . date( 'H:i:s', strtotime( $post->post_date ) ) );
		$date = date( 'Y-m-d H:i:s', strtotime( $value ) + $time );

		$this->strategy->update( $id, array(
			'post_date'     => $date,
			'post_date_gmt' => get_gmt_from_date( $date ),
		) );
	}

}
