<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Filtering_Settings_Date extends ACP_Filtering_Settings_Ranged {

	protected function get_options() {
		return array(
			''            => __( 'Daily' ),
			'monthly'     => __( 'Monthly' ),
			'yearly'      => __( 'Yearly' ),
			'future_past' => __( 'Future / Past', 'codepress-admin-columns' ),
		);
	}

}
