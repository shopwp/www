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
	$stripe_settings = array(
		array(
			'id' => 'stripe_connect_button',
			'name' => __( 'Connection Status', 'edds' ),
			'desc' => edds_stripe_connect_setting_field(),
			'type' => 'descriptive_text',
			'class' => 'edd-stripe-connect-row',
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
			'id'   => 'test_secret_key',
			'name'  => __( 'Test Secret Key', 'edds' ),
			'desc'  => __( 'Enter your test secret key, found in your Stripe Account Settings', 'edds' ),
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
			'id'   => 'live_secret_key',
			'name'  => __( 'Live Secret Key', 'edds' ),
			'desc'  => __( 'Enter your live secret key, found in your Stripe Account Settings', 'edds' ),
			'type'  => 'text',
			'size'  => 'regular',
			'class' => 'edd-hidden edds-api-key-row',
		),
		array(
			'id'    => 'stripe_webhook_description',
			'type'  => 'descriptive_text',
			'name'  => __( 'Webhooks', 'edds' ),
			'desc'  =>
			'<p>' . sprintf(
				/* translators: %1$s Opening anchor tag, do not translate. %2$s Closing anchor tag, do not translate. */
				__( 'In order for Stripe to function completely, you must configure your Stripe webhooks. Visit your %1$saccount dashboard%2$s to configure them. Please add a webhook endpoint for the URL below.', 'edds' ),
				'<a href="https://dashboard.stripe.com/account/webhooks" target="_blank" rel="noopener noreferrer">',
				'</a>'
			) . '</p>' .
			'<p><strong>' . sprintf(
				/* translators: %s Webhook URL. Do not translate. */
				__( 'Webhook URL: %s', 'edds' ),
				home_url( 'index.php?edd-listener=stripe' )
			) . '</strong></p>' .
			'<p>' . sprintf(
				/* translators: %1$s Opening anchor tag, do not translate. %2$s Closing anchor tag, do not translate. */
				__( 'See our %1$sdocumentation%2$s for more information.', 'edds' ),
				'<a href="http://docs.easydigitaldownloads.com/article/405-setup-documentation-for-stripe-payment-gateway" target="_blank" rel="noopener noreferrer">',
				'</a>'
			) . '</p>'
		),
		array(
			'id'    => 'stripe_billing_fields',
			'name'  => __( 'Billing Address Display', 'edds' ),
			'desc'  => __( 'Select how you would like to display the billing address fields on the checkout form. <p><strong>Notes</strong>:</p><p>If taxes are enabled, this option cannot be changed from "Full address".</p><p>If set to "No address fields", you <strong>must</strong> disable "zip code verification" in your Stripe account.</p>', 'edds' ),
			'type'  => 'select',
			'options' => array(
				'full'        => __( 'Full address', 'edds' ),
				'zip_country' => __( 'Zip / Postal Code and Country only', 'edds' ),
				'none'        => __( 'No address fields', 'edds' )
			),
			'std'   => 'full'
		),
 		array(
 			'id'   => 'stripe_statement_descriptor',
 			'name' => __( 'Statement Descriptor', 'edds' ),
 			'desc' => __( 'Choose how charges will appear on customer\'s credit card statements. <em>Max 22 characters</em>', 'edds' ),
 			'type' => 'text',
 		),
 		array(
			'id'   => 'stripe_use_existing_cards',
			'name' => __( 'Show Previously Used Cards', 'edds' ),
			'desc' => __( 'Provides logged in customers with a list of previous used payment methods for faster checkout.', 'edds' ),
			'type' => 'checkbox'
		),
		array(
			'id'   => 'stripe_preapprove_only',
			'name'  => __( 'Preapproved Payments', 'edds' ),
			'desc'  => __( 'Authorize payments for processing and collection at a future date.', 'edds' ),
			'type'  => 'checkbox',
			'tooltip_title' => __( 'What does checking preapprove do?', 'edds' ),
			'tooltip_desc'  => __( 'If you choose this option, Stripe will not charge the customer right away after checkout, and the payment status will be set to preapproved in Easy Digital Downloads. You (as the admin) can then manually change the status to Complete by going to Payment History and changing the status of the payment to Complete. Once you change it to Complete, the customer will be charged. Note that most typical stores will not need this option.', 'edds' ),
		),
		array(
			'id'    => 'stripe_allow_prepaid',
			'name'  => __( 'Prepaid Cards', 'edds' ),
			'desc'  => __( 'Allow prepaid cards as valid payment method.', 'edds' ),
			'type'  => 'checkbox',
		),
		array(
			'id'   => 'stripe_split_payment_fields',
			'name'  => __( 'Split Credit Card Form', 'edds' ),
			'desc'  => __( 'Use separate card number, expiration, and CVC fields in payment forms.', 'edds' ),
			'type'  => 'checkbox',
		),
		array(
			'id' => 'stripe_restrict_assets',
			'name' => ( __( 'Restrict Stripe Assets', 'edds' ) ),
			'desc' => ( __( 'Only load Stripe.com hosted assets on pages that specifically utilize Stripe functionality.', 'edds' ) ),
			'type' => 'checkbox',
			'tooltip_title' => __( 'Loading Javascript from Stripe', 'edds' ),
			'tooltip_desc' => __( 'Stripe advises that their Javascript library be loaded on every page to take advantage of their advanced fraud detection rules. If you are not concerned with this, enable this setting to only load the Javascript when necessary. Read more about Stripe\'s recommended setup here: https://stripe.com/docs/web/setup.', 'edds' ),
		)
	);

	if ( edd_get_option( 'stripe_checkout' ) ) {
		$stripe_settings[] = array(
			'id'    => 'stripe_checkout',
			'name'  => '<strong>' . __( 'Stripe Checkout', 'edds' ) . '</strong>',
			'type'  => 'stripe_checkout_notice',
			'desc'  => wp_kses(
				sprintf(
					/* translators: %1$s Opening anchor tag, do not translate. %2$s Closing anchor tag, do not translate. */
					esc_html__( 'To ensure your website is compliant with the new %1$sStrong Customer Authentication%2$s (SCA) regulations, the legacy Stripe Checkout modal is no longer supported. Payments are still securely accepted through through Stripe on the standard Easy Digital Downloads checkout page. "Buy Now" buttons will also automatically redirect to the standard checkout page.', 'edds' ),
					'<a href="https://stripe.com/en-ca/guides/strong-customer-authentication" target="_blank" rel="noopener noreferrer">',
					'</a>'
				),
				array(
					'a' => array(
						'href'   => true,
						'rel'    => true,
						'target' => true,
					)
				)
			),
		);
	}

	if ( version_compare( EDD_VERSION, 2.5, '>=' ) ) {
		$stripe_settings = array( 'edd-stripe' => $stripe_settings );

		// Set up the new setting field for the Test Mode toggle notice
		$notice = array(
			'stripe_connect_test_mode_toggle_notice' => array(
				'id' => 'stripe_connect_test_mode_toggle_notice',
				'desc' => '<p>' . __( 'You have disabled the "Test Mode" option. Once you have saved your changes, please verify your Stripe connection, especially if you have not previously connected in with "Test Mode" disabled.', 'edds' ) . '</p>',
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
 * Callback for the stripe_checkout_notice field type.
 *
 * @since 2.7.0
 *
 * @param array $args The setting field arguments
 */
function edd_stripe_checkout_notice_callback( $args ) {
	$value = isset( $args['desc'] ) ? $args['desc'] : '';

	$html = '<div class="notice notice-warning inline' . edd_sanitize_html_class( $args['field_class'] ) . '" id="edd_settings[' . edd_sanitize_key( $args['id'] ) . ']">' . wpautop( $value ) . '</div>';

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
		$message = '<p>' . sprintf(
			/* translators: %1$s Opening anchor tag, do not translate. %2$s Closing anchor tag, do not translate. */
			__( 'There was an error getting your Stripe credentials. Please %1$stry again%2$s. If you continue to have this problem, please contact support.', 'edds' ),
			'<a href="' . esc_url( admin_url( 'edit.php?post_type=download&page=edd-settings&tab=gateways&section=edd-stripe' ) ) . '" target="_blank" rel="noopener noreferrer">',
			'</a>'
		) . '</p>';
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