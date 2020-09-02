<?php

/**
 * Load our javascript
 *
 * The Stripe JS is by default, loaded on every page as suggested by Stripe. This can be overridden by using the Restrict Stripe Assets
 * setting within the admin, and the Stripe Javascript resources will only be loaded when necessary.
 * @link https://stripe.com/docs/web/setup
 *
 * The custom Javascript for EDD is loaded if the page is checkout. If checkout, the function is called directly with
 * `true` set for the `$force_load_scripts` argument.
 *
 * @access      public
 * @since       1.0
 *
 * @param bool $force_load_scripts Allows registering our Javascript files on pages other than is_checkout().
 *                                 This argument allows the `edd_stripe_js` function to be called directly, outside of
 *                                 the context of checkout, such as the card management or update subscription payment method
 *                                 UIs. Sending in 'true' will ensure that the Javascript resources are enqueued when you need them.
 *
 *
 * @return      void
 */
function edd_stripe_js( $force_load_scripts = false ) {
	if ( false === edd_is_gateway_active( 'stripe' ) ) {
		return;
	}

	if ( function_exists( 'edd_is_checkout' ) ) {

		$publishable_key = NULL;

		if ( edd_is_test_mode() ) {
			$publishable_key = edd_get_option( 'test_publishable_key', '' );
		} else {
			$publishable_key = edd_get_option( 'live_publishable_key', '' );
		}

		wp_register_script( 'stripe-js', 'https://js.stripe.com/v3/', array( 'jquery' ), null );
		wp_register_script( 'stripe-checkout', 'https://checkout.stripe.com/checkout.js', array( 'jquery' ), null );
		wp_register_script( 'edd-stripe-js', EDDSTRIPE_PLUGIN_URL . 'assets/js/build/app.min.js', array( 'jquery', 'stripe-js', 'edd-ajax' ), EDD_STRIPE_VERSION, true );

		$is_checkout     = edd_is_checkout();
		$restrict_assets = edd_get_option( 'stripe_restrict_assets', false );

		if ( $is_checkout || $force_load_scripts || false === $restrict_assets ) {
			wp_enqueue_script( 'stripe-js' );
		}

		if ( $is_checkout || $force_load_scripts ) {
			wp_enqueue_script( 'edd-stripe-js' );
			wp_enqueue_script( 'jQuery.payment' );

			$stripe_vars = apply_filters( 'edd_stripe_js_vars', array(
				'publishable_key'                => trim( $publishable_key ),
				'is_ajaxed'                      => edd_is_ajax_enabled() ? 'true' : 'false',
				'currency'                       => edd_get_currency(),
				'locale'                         => edds_get_stripe_checkout_locale(),
				'is_zero_decimal'                => edds_is_zero_decimal_currency() ? 'true' : 'false',
				'checkout'                       => edd_get_option( 'stripe_checkout' ) ? 'true' : 'false',
				'store_name'                     => get_bloginfo( 'name' ),
				'alipay'                         => edd_get_option( 'stripe_alipay' ) ? 'true' : 'false',
				'submit_text'                    => edd_get_option( 'stripe_checkout_button_text', __( 'Next', 'edds' ) ),
				'image'                          => edd_get_option( 'stripe_checkout_image' ),
				'zipcode'                        => edd_get_option( 'stripe_checkout_zip_code', false ) ? 'true' : 'false',
				'billing_address'                => edd_get_option( 'stripe_checkout_billing', false ) ? 'true' : 'false',
				'remember_me'                    => edd_get_option( 'stripe_checkout_remember', false ) ? 'true' : 'false',
				'no_key_error'                   => __( 'Stripe publishable key missing. Please enter your publishable key in Settings.', 'edds' ),
				'checkout_required_fields_error' => __( 'Please fill out all required fields to continue your purchase.', 'edds' ),
				'checkout_agree_to_terms'        => __( 'Please agree to the terms to complete your purchase.', 'edds' ),
				'generic_error'                  => __( 'Unable to complete your request. Please try again.', 'edds' ),
				'successPageUri'                 => edd_get_success_page_uri(),
				'failurePageUri'                 => edd_get_failed_transaction_uri(),
				'elementsOptions'                => edds_get_stripe_elements_options(),
			) );

			wp_localize_script( 'edd-stripe-js', 'edd_stripe_vars', $stripe_vars );

		}
	}
}
add_action( 'wp_enqueue_scripts', 'edd_stripe_js', 100 );

function edd_stripe_css( $force_load_scripts = false ) {
	if ( false === edd_is_gateway_active( 'stripe' ) ) {
		return;
	}

	if ( edd_is_checkout() || $force_load_scripts ) {
		$deps = array( 'edd-styles' );

		if ( ! wp_script_is( 'edd-styles', 'enqueued' ) ) {
			$deps = array();
		}

		wp_register_style( 'edd-stripe', EDDSTRIPE_PLUGIN_URL . 'assets/css/build/style.min.css', $deps, EDD_STRIPE_VERSION );
		wp_enqueue_style( 'edd-stripe' );
	}
}
add_action( 'wp_enqueue_scripts', 'edd_stripe_css', 100 );

/**
 * Load our admin javascript
 *
 * @access      public
 * @since       1.8
 * @return      void
 */
function edd_stripe_admin_js( $payment_id  = 0 ) {

	if( 'stripe' !== edd_get_payment_gateway( $payment_id ) ) {
		return;
	}
?>
	<script type="text/javascript">
		jQuery(document).ready(function($) {
			$('select[name=edd-payment-status]').change(function() {

				if( 'refunded' == $(this).val() ) {

					// Localize refund label
					var edd_stripe_refund_charge_label = "<?php echo esc_js( __( 'Refund Charge in Stripe', 'edds' ) ); ?>";

					$(this).parent().parent().append( '<input type="checkbox" id="edd_refund_in_stripe" name="edd_refund_in_stripe" value="1" style="margin-top: 0;" />' );
					$(this).parent().parent().append( '<label for="edd_refund_in_stripe">' + edd_stripe_refund_charge_label + '</label>' );

				} else {

					$('#edd_refund_in_stripe').remove();
					$('label[for="edd_refund_in_stripe"]').remove();

				}

			});
		});
	</script>
<?php

}
add_action( 'edd_view_order_details_before', 'edd_stripe_admin_js', 100 );

/**
 * Loads the javascript for the Stripe Connect functionality in the settings page.
 *
 * @param string $hook The current admin page.
 */
function edd_stripe_connect_admin_script( $hook ) {

	if( 'download_page_edd-settings' !== $hook ) {
		return;
	}

	wp_enqueue_style( 'edd-stripe-admin-styles', EDDSTRIPE_PLUGIN_URL . 'assets/css/build/admin-style.min.css', array(), EDD_STRIPE_VERSION );

	wp_enqueue_script( 'edd-stripe-admin-scripts', EDDSTRIPE_PLUGIN_URL . 'assets/js/build/admin.min.js', array( 'jquery' ), EDD_STRIPE_VERSION );

	$test_key = edd_get_option( 'test_publishable_key' );
	$live_key = edd_get_option( 'live_publishable_key' );

	wp_localize_script(
		'edd-stripe-admin-scripts',
		'edd_stripe_admin',
		array(
			'stripe_enabled' => array_key_exists( 'stripe', edd_get_enabled_payment_gateways() ),
			'test_mode' => (int) edd_is_test_mode(),
			'test_key_exists' => ! empty( $test_key ) ? 'true' : 'false',
			'live_key_exists' => ! empty( $live_key ) ? 'true' : 'false',
			'ajaxurl' => esc_url( admin_url( 'admin-ajax.php' ) ),
		)
	);
}
add_action( 'admin_enqueue_scripts', 'edd_stripe_connect_admin_script' );
