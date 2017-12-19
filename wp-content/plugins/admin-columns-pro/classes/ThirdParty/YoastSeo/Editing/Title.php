<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_ThirdParty_YoastSeo_Editing_Title extends ACP_Editing_Model {

	public function get_edit_value( $id ) {
		return get_post_meta( $id, '_yoast_wpseo_title', true );
	}

	public function get_view_settings() {
		return array(
			'type'        => 'text',
			'placeholder' => __( 'Enter your SEO Title', 'codepress-admin-columns' ),
		);
	}

	public function save( $id, $value ) {
		update_post_meta( $id, '_yoast_wpseo_title', $value );

		return $value;
	}

}
