<?php

/**
 * Load our javascript
 *
 * @access      public
 * @since       1.0
 * @param bool  $override Allows registering stripe.js on pages other than is_checkout()
 * @return      void
 */
function edd_stripe_js( $override = false ) {
	if ( function_exists( 'edd_is_checkout' ) ) {

		$publishable_key = NULL;

		if ( edd_is_test_mode() ) {
			$publishable_key = edd_get_option( 'test_publishable_key', '' );
		} else {
			$publishable_key = edd_get_option( 'live_publishable_key', '' );
		}

		wp_enqueue_script( 'stripe-js', 'https://js.stripe.com/v2/', array( 'jquery' ), null );

		if ( edd_is_checkout() || $override ) {

			// Use minified libraries if SCRIPT_DEBUG is turned off
			$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

			wp_enqueue_script( 'stripe-checkout', 'https://checkout.stripe.com/checkout.js', array( 'jquery' ) );
			wp_enqueue_script( 'edd-stripe-js', EDDSTRIPE_PLUGIN_URL . 'assets/js/edd-stripe' . $suffix . '.js', array( 'jquery', 'stripe-js' ), EDD_STRIPE_VERSION, true );

			$stripe_vars = apply_filters( 'edd_stripe_js_vars', array(
				'publishable_key'  => trim( $publishable_key ),
				'is_ajaxed'        => edd_is_ajax_enabled() ? 'true' : 'false',
				'currency'         => edd_get_currency(),
				'locale'           => edds_get_stripe_checkout_locale(),
				'is_zero_decimal'  => edds_is_zero_decimal_currency() ? 'true' : 'false',
				'checkout'         => edd_get_option( 'stripe_checkout' ) ? 'true' : 'false',
				'store_name'       => get_bloginfo( 'name' ),
				'alipay'           => edd_get_option( 'stripe_alipay' ) ? 'true' : 'false',
				'submit_text'      => edd_get_option( 'stripe_checkout_button_text', __( 'Next', 'edds' ) ),
				'image'            => edd_get_option( 'stripe_checkout_image' ),
				'zipcode'          => edd_get_option( 'stripe_checkout_zip_code', false ) ? 'true' : 'false',
				'billing_address'  => edd_get_option( 'stripe_checkout_billing', false ) ? 'true' : 'false',
				'remember_me'      => edd_get_option( 'stripe_checkout_remember', false ) ? 'true' : 'false',
				'no_key_error'     => __( 'Stripe publishable key missing. Please enter your publishable key in Settings.', 'edds' )
			) );

			wp_localize_script( 'edd-stripe-js', 'edd_stripe_vars', $stripe_vars );

		}
	}
}
add_action( 'wp_enqueue_scripts', 'edd_stripe_js', 100 );

function edd_stripe_css( $override = false ) {
	if ( edd_is_checkout() || $override ) {
		// Use minified libraries if SCRIPT_DEBUG is turned off
		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

		wp_register_style( 'edd-stripe', EDDSTRIPE_PLUGIN_URL . 'assets/css/style' . $suffix . '.css', array( 'edd-styles' ), EDD_STRIPE_VERSION );
		wp_enqueue_style( 'edd-stripe' );
	}
}
add_action( 'wp_enqueue_scripts', 'edd_stripe_css', PHP_INT_MAX );

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