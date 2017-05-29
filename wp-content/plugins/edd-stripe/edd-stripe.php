<?php
/*
Plugin Name: Easy Digital Downloads - Stripe Payment Gateway
Plugin URL: https://easydigitaldownloads.com/downloads/stripe-gateway/
Description: Adds a payment gateway for Stripe.com
Version: 2.5.14
Author: Easy Digital Downloads
Author URI: https://easydigitaldownloads.com
Text Domain: edds
Domain Path: languages
*/

if( version_compare( PHP_VERSION, '5.3.3', '<' ) ) {

	add_action( 'admin_notices', 'edds_below_php_version_notice' );
	function edds_below_php_version_notice() {
		echo '<div class="error"><p>' . __( 'Your version of PHP is below the minimum version of PHP required by Easy Digital Downloads - Stripe Payment Gateway. Please contact your host and request that your version be upgraded to 5.3.3 or later.', 'edds' ) . '</p></div>';
	}
	return;
}

if ( ! defined( 'EDDS_PLUGIN_DIR' ) ) {
	define( 'EDDS_PLUGIN_DIR', dirname( __FILE__ ) );
}

if ( ! defined( 'EDDSTRIPE_PLUGIN_URL' ) ) {
	define( 'EDDSTRIPE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

define( 'EDD_STRIPE_VERSION', '2.5.14' );

if( class_exists( 'EDD_License' ) && is_admin() ) {
	$edd_stripe_license = new EDD_License( __FILE__, 'Stripe Payment Gateway', EDD_STRIPE_VERSION, 'Easy Digital Downloads', 'stripe_license_key' );
}

/**
 * Plugin activation
 *
 * @access      public
 * @since       2.5.7
 * @return      void
 */
function edds_plugin_activation() {

	global $edd_options;

	/*
	 * Migrate settings from old 3rd party gateway
	 *
	 * See https://github.com/easydigitaldownloads/edd-stripe/issues/153
	 *
	 */

	$changed = false;
	$options = get_option( 'edd_settings', array() );

	// Set checkout button text
	if( ! empty( $options['stripe_checkout_button_label'] ) && empty( $options['stripe_checkout_button_text'] ) ) {

		$options['stripe_checkout_button_text'] = $options['stripe_checkout_button_label'];

		$changed = true;

	}

	// Set checkout logo
	if( ! empty( $options['stripe_checkout_popup_image'] ) && empty( $options['stripe_checkout_image'] ) ) {

		$options['stripe_checkout_image'] = $options['stripe_checkout_popup_image'];

		$changed = true;

	}

	// Set billing address requirement
	if( ! empty( $options['require_billing_address'] ) && empty( $options['stripe_checkout_billing'] ) ) {

		$options['stripe_checkout_billing'] = 1;

		$changed = true;

	}


	if( $changed ) {

		$options['stripe_checkout'] = 1;
		$options['gateways']['stripe'] = 1;

		if( isset( $options['gateway']['stripe_checkout'] ) ) {
			unset( $options['gateway']['stripe_checkout'] );
		}

		$merged_options = array_merge( $edd_options, $options );
		$edd_options    = $merged_options;
		update_option( 'edd_settings', $merged_options );

	}

	if( is_plugin_active( 'edd-stripe-gateway/edd-stripe-gateway.php' ) ) {
		deactivate_plugins( 'edd-stripe-gateway/edd-stripe-gateway.php' );
	}

}
register_activation_hook( __FILE__, 'edds_plugin_activation' );

/**
 * Database Upgrade actions
 *
 * @access      public
 * @since       2.5.8
 * @return      void
 */
function edds_plugin_database_upgrades() {

	$did_upgrade = false;
	$version     = get_option( 'edds_stripe_version' );

	if( ! $version || version_compare( $version, EDD_STRIPE_VERSION, '<' ) ) {

		$did_upgrade = true;

		switch( EDD_STRIPE_VERSION ) {

			case '2.5.8' :
				edd_update_option( 'stripe_checkout_remember', true );
				break;
		}

	}

	if( $did_upgrade ) {
		update_option( 'edds_stripe_version', EDD_STRIPE_VERSION );
	}

}
add_action( 'admin_init', 'edds_plugin_database_upgrades' );


/**
 * Internationalization
 *
 * @access      public
 * @since       1.6.6
 * @return      void
 */
function edds_textdomain() {

	// Set filter for language directory
	$lang_dir = EDDS_PLUGIN_DIR . '/languages/';

	// Traditional WordPress plugin locale filter
	$locale = apply_filters( 'plugin_locale', get_locale(), 'edds' );
	$mofile = sprintf( '%1$s-%2$s.mo', 'edds', $locale );

	// Setup paths to current locale file
	$mofile_local   = $lang_dir . $mofile;
	$mofile_global  = WP_LANG_DIR . '/edd-stripe/' . $mofile;

	// Look in global /wp-content/languages/edd-stripe/ folder
	if( file_exists( $mofile_global ) ) {
		load_textdomain( 'edds', $mofile_global );

	// Look in local /wp-content/plugins/edd-stripe/languages/ folder
	} elseif( file_exists( $mofile_local ) ) {
		load_textdomain( 'edds', $mofile_local );

	} else {
		// Load the default language files
		load_plugin_textdomain( 'edds', false, $lang_dir );
	}
}
add_action( 'init', 'edds_textdomain', 99999 );

/**
 * Register our payment gateway
 *
 * @access      public
 * @since       1.0
 * @return      array
 */
function edds_register_gateway( $gateways ) {
	// Format: ID => Name
	$gateways['stripe'] = array(
		'admin_label'    => 'Stripe',
		'checkout_label' => __( 'Credit Card', 'edds' ),
		'supports'       => array(
			'buy_now'
		)
	);
	return $gateways;
}
add_filter( 'edd_payment_gateways', 'edds_register_gateway' );


/**
 * Add an errors div
 *
 * @access      public
 * @since       1.0
 * @return      void
 */
function edds_add_stripe_errors() {
	echo '<div id="edd-stripe-payment-errors"></div>';
}
add_action( 'edd_after_cc_fields', 'edds_add_stripe_errors', 999 );

/**
 * Stripe uses it's own credit card form because the card details are tokenized.
 *
 * We don't want the name attributes to be present on the fields in order to prevent them from getting posted to the server
 *
 * @access      public
 * @since       1.7.5
 * @return      void
 */
function edds_credit_card_form( $echo = true ) {

	global $edd_options;

	if( edd_get_option( 'stripe_checkout', false ) ) {
		return;
	}

	ob_start(); ?>

	<?php if ( ! wp_script_is ( 'edd-stripe-js' ) ) : ?>
		<?php edd_stripe_js( true ); ?>
	<?php endif; ?>

	<?php do_action( 'edd_before_cc_fields' ); ?>

	<fieldset id="edd_cc_fields" class="edd-do-validate">
		<legend><?php _e( 'Credit Card Info', 'edds' ); ?></legend>
		<?php if( is_ssl() ) : ?>
			<div id="edd_secure_site_wrapper">
				<span class="padlock">
					<svg class="edd-icon edd-icon-lock" xmlns="http://www.w3.org/2000/svg" width="18" height="28" viewBox="0 0 18 28" aria-hidden="true">
						<path d="M5 12h8V9c0-2.203-1.797-4-4-4S5 6.797 5 9v3zm13 1.5v9c0 .828-.672 1.5-1.5 1.5h-15C.672 24 0 23.328 0 22.5v-9c0-.828.672-1.5 1.5-1.5H2V9c0-3.844 3.156-7 7-7s7 3.156 7 7v3h.5c.828 0 1.5.672 1.5 1.5z"/>
					</svg>
				</span>
				<span><?php _e( 'This is a secure SSL encrypted payment.', 'edds' ); ?></span>
			</div>
		<?php endif; ?>
		<p id="edd-card-number-wrap">
			<label for="card_number" class="edd-label">
				<?php _e( 'Card Number', 'edds' ); ?>
				<span class="edd-required-indicator">*</span>
				<span class="card-type"></span>
			</label>
			<span class="edd-description"><?php _e( 'The (typically) 16 digits on the front of your credit card.', 'edds' ); ?></span>
			<input type="tel" pattern="[0-9]{13,16}" autocomplete="off" id="card_number" class="card-number edd-input required" placeholder="<?php _e( 'Card number', 'edds' ); ?>" />
		</p>
		<p id="edd-card-cvc-wrap">
			<label for="card_cvc" class="edd-label">
				<?php _e( 'CVC', 'edds' ); ?>
				<span class="edd-required-indicator">*</span>
			</label>
			<span class="edd-description"><?php _e( 'The 3 digit (back) or 4 digit (front) value on your card.', 'edds' ); ?></span>
			<input type="tel" pattern="[0-9]{3,4}" size="4" autocomplete="off" id="card_cvc" class="card-cvc edd-input required" placeholder="<?php _e( 'Security code', 'edds' ); ?>" />
		</p>
		<p id="edd-card-name-wrap">
			<label for="card_name" class="edd-label">
				<?php _e( 'Name on the Card', 'edds' ); ?>
				<span class="edd-required-indicator">*</span>
			</label>
			<span class="edd-description"><?php _e( 'The name printed on the front of your credit card.', 'edds' ); ?></span>
			<input type="text" autocomplete="off" id="card_name" class="card-name edd-input required" placeholder="<?php _e( 'Card name', 'edds' ); ?>" />
		</p>
		<?php do_action( 'edd_before_cc_expiration' ); ?>
		<p class="card-expiration">
			<label for="card_exp_month" class="edd-label">
				<?php _e( 'Expiration (MM/YY)', 'edds' ); ?>
				<span class="edd-required-indicator">*</span>
			</label>
			<span class="edd-description"><?php _e( 'The date your credit card expires, typically on the front of the card.', 'edds' ); ?></span>
			<select id="card_exp_month" class="card-expiry-month edd-select edd-select-small required">
				<?php for( $i = 1; $i <= 12; $i++ ) { echo '<option value="' . $i . '">' . sprintf ('%02d', $i ) . '</option>'; } ?>
			</select>
			<span class="exp-divider"> / </span>
			<select id="card_exp_year" class="card-expiry-year edd-select edd-select-small required">
				<?php for( $i = date('Y'); $i <= date('Y') + 30; $i++ ) { echo '<option value="' . $i . '">' . substr( $i, 2 ) . '</option>'; } ?>
			</select>
		</p>
		<?php do_action( 'edd_after_cc_expiration' ); ?>

	</fieldset>
	<?php

	do_action( 'edd_after_cc_fields' );

	$form = ob_get_clean();

	if ( false !== $echo ) {
		echo $form;
	}

	return $form;
}
add_action( 'edd_stripe_cc_form', 'edds_credit_card_form' );

/**
 * Zip / Postal Code field for when full billing address is disabled
 *
 * @access      public
 * @since       2.5
 * @return      void
 */
function edd_stripe_zip_and_country() {

	$logged_in = is_user_logged_in();
	$customer  = EDD()->session->get( 'customer' );
	$customer  = wp_parse_args( $customer, array( 'address' => array(
		'line1'   => '',
		'line2'   => '',
		'city'    => '',
		'zip'     => '',
		'state'   => '',
		'country' => ''
	) ) );

	$customer['address'] = array_map( 'sanitize_text_field', $customer['address'] );

	if( $logged_in ) {

		$user_address = get_user_meta( get_current_user_id(), '_edd_user_address', true );

		foreach( $customer['address'] as $key => $field ) {

			if ( empty( $field ) && ! empty( $user_address[ $key ] ) ) {
				$customer['address'][ $key ] = $user_address[ $key ];
			} else {
				$customer['address'][ $key ] = '';
			}

		}

	}
?>
	<fieldset id="edd_cc_address" class="cc-address">
		<legend><?php _e( 'Billing Details', 'edds' ); ?></legend>
		<p id="edd-card-country-wrap">
			<label for="billing_country" class="edd-label">
				<?php _e( 'Billing Country', 'edds' ); ?>
				<?php if( edd_field_is_required( 'billing_country' ) ) { ?>
					<span class="edd-required-indicator">*</span>
				<?php } ?>
			</label>
			<span class="edd-description"><?php _e( 'The country for your billing address.', 'edds' ); ?></span>
			<select name="billing_country" id="billing_country" class="billing_country edd-select<?php if( edd_field_is_required( 'billing_country' ) ) { echo ' required'; } ?>"<?php if( edd_field_is_required( 'billing_country' ) ) {  echo ' required '; } ?>>
				<?php

				$selected_country = edd_get_shop_country();

				if( ! empty( $customer['address']['country'] ) && '*' !== $customer['address']['country'] ) {
					$selected_country = $customer['address']['country'];
				}

				$countries = edd_get_country_list();
				foreach( $countries as $country_code => $country ) {
				  echo '<option value="' . esc_attr( $country_code ) . '"' . selected( $country_code, $selected_country, false ) . '>' . $country . '</option>';
				}
				?>
			</select>
		</p>
		<p id="edd-card-zip-wrap">
			<label for="card_zip" class="edd-label">
				<?php _e( 'Billing Zip / Postal Code', 'edds' ); ?>
				<?php if( edd_field_is_required( 'card_zip' ) ) { ?>
					<span class="edd-required-indicator">*</span>
				<?php } ?>
			</label>
			<span class="edd-description"><?php _e( 'The zip or postal code for your billing address.', 'edds' ); ?></span>
			<input type="text" size="4" name="card_zip" class="card-zip edd-input<?php if( edd_field_is_required( 'card_zip' ) ) { echo ' required'; } ?>" placeholder="<?php _e( 'Zip / Postal Code', 'edds' ); ?>" value="<?php echo $customer['address']['zip']; ?>"<?php if( edd_field_is_required( 'card_zip' ) ) {  echo ' required '; } ?>/>
		</p>
	</fieldset>
<?php
}

/**
 * Determine how the billing address fields should be displayed
 *
 * @access      public
 * @since       2.5
 * @return      void
 */
function edd_stripe_setup_billing_address_fields() {

	if( ! function_exists( 'edd_use_taxes' ) ) {
		return;
	}

	if( edd_use_taxes() || edd_get_option( 'stripe_checkout' ) || 'stripe' !== edd_get_chosen_gateway() || ! edd_get_cart_total() > 0 ) {
		return;
	}

	$display = edd_get_option( 'stripe_billing_fields', 'full' );

	switch( $display ) {

		case 'full' :

			// Make address fields required
			add_filter( 'edd_require_billing_address', '__return_true' );

			break;

		case 'zip_country' :

			remove_action( 'edd_after_cc_fields', 'edd_default_cc_address_fields', 10 );
			add_action( 'edd_after_cc_fields', 'edd_stripe_zip_and_country', 9 );

			// Make Zip required
			add_filter( 'edd_purchase_form_required_fields', 'edd_stripe_require_zip_and_country' );

			break;

		case 'none' :

			remove_action( 'edd_after_cc_fields', 'edd_default_cc_address_fields', 10 );

			break;

	}

}
add_action( 'init', 'edd_stripe_setup_billing_address_fields', 9 );

/**
 * Force zip code and country to be required when billing address display is zip only
 *
 * @access      public
 * @since       2.5
 * @return      array $fields The required fields
 */
function edd_stripe_require_zip_and_country( $fields ) {

	$fields['card_zip'] = array(
		'error_id' => 'invalid_zip_code',
		'error_message' => __( 'Please enter your zip / postal code', 'edds' )
	);

	$fields['billing_country'] = array(
		'error_id' => 'invalid_country',
		'error_message' => __( 'Please select your billing country', 'edds' )
	);

	return $fields;
}


/********************************************************
* If EDD Recurring is active and it is earlier than 2.4,
* we must load an old version of the gateway
*********************************************************/
if( defined( 'EDD_RECURRING_VERSION' ) && version_compare( preg_replace( '/[^0-9.].*/', '', EDD_RECURRING_VERSION ), '2.4', '<' ) ) {

	require_once EDDS_PLUGIN_DIR . '/edd-stripe-old.php';

} else {

	require_once EDDS_PLUGIN_DIR . '/vendor/autoload.php';

	require_once EDDS_PLUGIN_DIR . '/edd-stripe-new.php';

}
