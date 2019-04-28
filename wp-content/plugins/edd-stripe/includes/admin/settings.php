<?php

/**
* Register our settings section
*
* @return array
*/
function edds_settings_section( $sections ) {
	$sections['edd-stripe'] = __( 'Stripe', 'edds' );

	return $sections;
}
add_filter( 'edd_settings_sections_gateways', 'edds_settings_section' );

/**
 * Register the gateway settings
 *
 * @access      public
 * @since       1.0
 * @return      array
 */

function edds_add_settings( $settings ) {

	// Build the Stripe Connect OAuth URL
	$stripe_connect_url = add_query_arg( array(
		'live_mode' => (int) ! edd_is_test_mode(),
		'state' => str_pad( wp_rand( wp_rand(), PHP_INT_MAX ), 100, wp_rand(), STR_PAD_BOTH ),
		'customer_site_url' => admin_url( 'edit.php?post_type=download&page=edd-settings&tab=gateways&section=edd-stripe' ),
	), 'https://easydigitaldownloads.com/?edd_gateway_connect_init=stripe_connect' );

	$test_mode = edd_is_test_mode();

	$test_key = edd_get_option( 'test_publishable_key' );
	$live_key = edd_get_option( 'live_publishable_key' );

	$live_text = _x( 'live', 'current value for test mode', 'edds' );
	$test_text = _x( 'test', 'current value for test mode', 'edds' );

	$mode = $live_text;
	if( $test_mode ) {
		$mode = $test_text;
	}

	$stripe_connect_account_id = edd_get_option( 'stripe_connect_account_id' );

	if( empty( $stripe_connect_account_id ) || ( ( empty( $test_key ) && $test_mode ) || ( empty( $live_key ) && ! $test_mode ) ) ) {
		$stripe_connect_desc = '<a href="'. esc_url( $stripe_connect_url ) .'" class="edd-stripe-connect"><span>' . __( 'Connect with Stripe', 'edds' ) . '</span></a>';
		$stripe_connect_desc .= '<p>' . sprintf( __( 'Have questions about connecting with Stripe? See the <a href="%s" target="_blank" rel="noopener noreferrer">documentation</a>.', 'edds' ), 'https://docs.easydigitaldownloads.com/article/2039-how-does-stripe-connect-affect-me' ) . '</p>';
	} else {
		$stripe_connect_desc = sprintf( __( 'Your Stripe account is connected in %s mode. If you need to reconnect in %s mode, <a href="%s">click here</a>.', 'edds' ), '<strong>' . $mode . '</strong>', $mode, esc_url( $stripe_connect_url ) );
	}

	$stripe_connect_desc .= '<p id="edds-api-keys-row-reveal">' . __( '<a href="#">Click here</a> to manage your API keys manually.', 'edds' ) . '</p>';
	$stripe_connect_desc .= '<p id="edds-api-keys-row-hide" class="edd-hidden">' . __( '<a href="#">Click here</a> to hide your API keys.', 'edds' ) . '</p>';

	$stripe_settings = array(
		array(
			'id'   => 'stripe_settings',
			'name'  => '<strong>' . __( 'Stripe Settings', 'edds' ) . '</strong>',
			'desc'  => __( 'Configure the Stripe settings', 'edds' ),
			'type'  => 'header'
		),
		array(
			'id' => 'stripe_connect_button',
			'name' => __( 'Connection Status', 'edds' ),
			'desc' => $stripe_connect_desc,
			'type' => 'descriptive_text',
			'class' => 'edd-stripe-connect-row',
		),
		array(
			'id'   => 'test_secret_key',
			'name'  => __( 'Test Secret Key', 'edds' ),
			'desc'  => __( 'Enter your test secret key, found in your Stripe Account Settings', 'edds' ),
			'type'  => 'text',
			'size'  => 'regular',
			'class' => 'edd-hidden edds-api-key-row',
		),
		array(
			'id'   => 'test_publishable_key',
			'name'  => __( 'Test Publishable Key', 'edds' ),
			'desc'  => __( 'Enter your test publishable key, found in your Stripe Account Settings', 'edds' ),
			'type'  => 'text',
			'size'  => 'regular',
			'class' => 'edd-hidden edds-api-key-row',
		),
		array(
			'id'   => 'live_secret_key',
			'name'  => __( 'Live Secret Key', 'edds' ),
			'desc'  => __( 'Enter your live secret key, found in your Stripe Account Settings', 'edds' ),
			'type'  => 'text',
			'size'  => 'regular',
			'class' => 'edd-hidden edds-api-key-row',
		),
		array(
			'id'   => 'live_publishable_key',
			'name'  => __( 'Live Publishable Key', 'edds' ),
			'desc'  => __( 'Enter your live publishable key, found in your Stripe Account Settings', 'edds' ),
			'type'  => 'text',
			'size'  => 'regular',
			'class' => 'edd-hidden edds-api-key-row',
		),
		array(
			'id'    => 'stripe_webhook_description',
			'type'  => 'descriptive_text',
			'name'  => __( 'Webhooks', 'edds' ),
			'desc'  =>
				'<p>' . sprintf( __( 'In order for Stripe to function completely, you must configure your Stripe webhooks. Visit your <a href="%s" target="_blank">account dashboard</a> to configure them. Please add a webhook endpoint for the URL below.', 'edds' ), 'https://dashboard.stripe.com/account/webhooks' ) . '</p>' .
				'<p><strong>' . sprintf( __( 'Webhook URL: %s', 'edds' ), home_url( 'index.php?edd-listener=stripe' ) ) . '</strong></p>' .
				'<p>' . sprintf( __( 'See our <a href="%s">documentation</a> for more information.', 'edds' ), 'http://docs.easydigitaldownloads.com/article/405-setup-documentation-for-stripe-payment-gateway' ) . '</p>'
		),
		array(
			'id'    => 'stripe_billing_fields',
			'name'  => __( 'Billing Address Display', 'edds' ),
			'desc'  => __( 'Select how you would like to display the billing address fields on the checkout form. <p><strong>Notes</strong>:</p><p>If taxes are enabled, this option cannot be changed from "Full address".</p><p>This setting does <em>not</em> apply to Stripe Checkout options below.</p><p>If set to "No address fields", you <strong>must</strong> disable "zip code verification" in your Stripe account.</p>', 'edds' ),
			'type'  => 'select',
			'options' => array(
				'full'        => __( 'Full address', 'edds' ),
				'zip_country' => __( 'Zip / Postal Code and Country only', 'edds' ),
				'none'        => __( 'No address fields', 'edds' )
			),
			'std'   => 'full'
		),
 		array(
			'id'   => 'stripe_use_existing_cards',
			'name'  => __( 'Show previously used cards?', 'edds' ),
			'desc'  => __( 'When enabled, provides logged in customers with a list of previously used payment methods, for faster checkout.', 'edds' ),
			'type'  => 'checkbox'
		),
 		array(
 			'id'   => 'stripe_statement_descriptor',
 			'name' => __( 'Statement Descriptor', 'edds' ),
 			'desc' => __( 'Choose how charges will appear on customer\'s credit card statements. <em>Max 22 characters</em>', 'edds' ),
 			'type' => 'text',
 		),
		array(
			'id'   => 'stripe_preapprove_only',
			'name'  => __( 'Preapprove Only?', 'edds' ),
			'desc'  => __( 'Check this if you would like to preapprove payments but not charge until a later date.', 'edds' ),
			'type'  => 'checkbox',
			'tooltip_title' => __( 'What does checking preapprove do?', 'edds' ),
			'tooltip_desc'  => __( 'If you choose this option, Stripe will not charge the customer right away after checkout, and the payment status will be set to preapproved in Easy Digital Downloads. You (as the admin) can then manually change the status to Complete by going to Payment History and changing the status of the payment to Complete. Once you change it to Complete, the customer will be charged. Note that most typical stores will not need this option.', 'edds' ),
		),
		array(
			'id'    => 'stripe_checkout_settings',
			'name'  => __( 'Stripe Checkout Options', 'edds' ),
			'type'  => 'header'
		),
		array(
			'id'    => 'stripe_checkout',
			'name'  => __( 'Enable Stripe Checkout', 'edds' ),
			'desc'  => __( 'Check this if you would like to enable the <a target="_blank" href="https://stripe.com/checkout">Stripe Checkout</a> modal window on the main checkout screen.', 'edds' ),
			'type'  => 'checkbox'
		),
		array(
			'id'    => 'stripe_checkout_button_text',
			'name'  => __( 'Complete Purchase Text', 'edds' ),
			'desc'  => __( 'Enter the text shown on the checkout\'s submit button. This is the button that opens the Stripe Checkout modal window.', 'edds' ),
			'type'  => 'text',
			'std'   => __( 'Next', 'edds' )
		),
		array(
			'id'    => 'stripe_checkout_image',
			'name'  => __( 'Checkout Logo', 'edds' ),
			'desc'  => __( 'Upload an image to be shown on the Stripe Checkout modal window. Recommended minimum size is 128x128px. Leave blank to disable the image.', 'edds' ),
			'type'  => 'upload'
		),
		array(
			'id'    => 'stripe_checkout_billing',
			'name'  => __( 'Enable Billing Address', 'edds' ),
			'desc'  => __( 'Check this box to instruct Stripe to collect a billing address in the Checkout modal window.', 'edds' ),
			'type'  => 'checkbox',
			'std'   => 0,
		),
		array(
			'id'    => 'stripe_checkout_zip_code',
			'name'  => __( 'Enable Zip / Postal Code', 'edds' ),
			'desc'  => __( 'Check this box to instruct Stripe to collect a zip / postal code in the Checkout modal window.', 'edds' ),
			'type'  => 'checkbox',
			'std'   => 0,
		),
		array(
			'id'    => 'stripe_checkout_remember',
			'name'  => __( 'Enable Remember Me', 'edds' ),
			'desc'  => __( 'Check this box to enable the Remember Me option in the Stripe Checkout modal window.', 'edds' ),
			'type'  => 'checkbox',
			'std'   => 0,
		),
	);

	if ( version_compare( EDD_VERSION, 2.5, '>=' ) ) {
		$stripe_settings = array( 'edd-stripe' => $stripe_settings );

		// Set up the new setting field for the Test Mode toggle notice
		$notice = array(
			'stripe_connect_test_mode_toggle_notice' => array(
				'id' => 'stripe_connect_test_mode_toggle_notice',
				'desc' => '<p>' . __( 'You just toggled the test mode option. Save your changes using the Save Changes button below, then connect your Stripe account using the "Connect with Stripe" button when the page reloads.', 'edds' ) . '</p>',
				'type' => 'stripe_connect_notice',
				'field_class' => 'edd-hidden',
			)
		);

		// Insert the new setting after the Test Mode checkbox
		$position = array_search( 'test_mode', array_keys( $settings['main'] ), true );
		$settings = array_merge(
			array_slice( $settings['main'], $position, 1, true ),
			$notice,
			$settings
		);
	}

	return array_merge( $settings, $stripe_settings );
}
add_filter( 'edd_settings_gateways', 'edds_add_settings' );

/**
 * Force full billing address display when taxes are enabled
 *
 * @access      public
 * @since       2.5
 * @return      string
 */
function edd_stripe_sanitize_stripe_billing_fields_save( $value, $key ) {

	if( 'stripe_billing_fields' == $key && edd_use_taxes() ) {

		$value = 'full';

	}

	return $value;

}
add_filter( 'edd_settings_sanitize_select', 'edd_stripe_sanitize_stripe_billing_fields_save', 10, 2 );

/**
 * Filter the output of the statement descriptor option to add a max length to the text string
 *
 * @since 2.6
 * @param $html string The full html for the setting output
 * @param $args array  The original arguments passed in to output the html
 *
 * @return string
 */
function edd_stripe_max_length_statement_descriptor( $html, $args ) {
	if ( 'stripe_statement_descriptor' !== $args['id'] ) {
		return $html;
	}

	$html = str_replace( '<input type="text"', '<input type="text" maxlength="22"', $html );

	return $html;
}
add_filter( 'edd_after_setting_output', 'edd_stripe_max_length_statement_descriptor', 10, 2 );

/**
 * Callback for the stripe_connect_notice field type.
 *
 * @since 2.6.14
 *
 * @param array $args The setting field arguments
 */
function edd_stripe_connect_notice_callback( $args ) {

	$value = isset( $args['desc'] ) ? $args['desc'] : '';

	$class = edd_sanitize_html_class( $args['field_class'] );

	$html = '<div class="'.$class.'" id="edd_settings[' . edd_sanitize_key( $args['id'] ) . ']">' . $value . '</div>';

	echo $html;
}

/**
 * Listens for Stripe Connect completion requests and saves the Stripe API keys.
 *
 * @since 2.6.14
 */
function edds_process_gateway_connect_completion() {

	if( ! isset( $_GET['edd_gateway_connect_completion'] ) || 'stripe_connect' !== $_GET['edd_gateway_connect_completion'] || ! isset( $_GET['state'] ) ) {
		return;
	}

	if( ! current_user_can( 'manage_shop_settings' ) ) {
		return;
	}

	if( headers_sent() ) {
		return;
	}

	$edd_credentials_url = add_query_arg( array(
		'live_mode' => (int) ! edd_is_test_mode(),
		'state' => sanitize_text_field( $_GET['state'] ),
		'customer_site_url' => admin_url( 'edit.php?post_type=download' ),
	), 'https://easydigitaldownloads.com/?edd_gateway_connect_credentials=stripe_connect' );

	$response = wp_remote_get( esc_url_raw( $edd_credentials_url ) );

	if( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
		$message = '<p>' . sprintf( __( 'There was an error getting your Stripe credentials. Please <a href="%s">try again</a>. If you continue to have this problem, please contact support.', 'edds' ), esc_url( admin_url( 'edit.php?post_type=download&page=edd-settings&tab=gateways&section=edd-stripe' ) ) ) . '</p>';
		wp_die( $message );
	}

	$data = json_decode( $response['body'], true );
	$data = $data['data'];

	if( edd_is_test_mode() ) {
		edd_update_option( 'test_publishable_key', sanitize_text_field( $data['publishable_key'] ) );
		edd_update_option( 'test_secret_key', sanitize_text_field( $data['secret_key'] ) );
	} else {
		edd_update_option( 'live_publishable_key', sanitize_text_field( $data['publishable_key'] ) );
		edd_update_option( 'live_secret_key', sanitize_text_field( $data['secret_key'] ) );
	}

	edd_update_option( 'stripe_connect_account_id', sanitize_text_field( $data['stripe_user_id'] ) );
	wp_redirect( esc_url_raw( admin_url( 'edit.php?post_type=download&page=edd-settings&tab=gateways&section=edd-stripe' ) ) );
	exit;

}
add_action( 'admin_init', 'edds_process_gateway_connect_completion' );