var edd_global_vars;

if( ! edd_stripe_vars.publishable_key ) {
	alert( edd_stripe_vars.no_key_error );
}

if( 'true' == edd_stripe_vars.checkout ) {

	var checkout_modal_shown = false;

	var handler = StripeCheckout.configure({
		key: edd_stripe_vars.publishable_key,
		image: edd_stripe_vars.image,
		locale: edd_stripe_vars.locale,
		token: function(token) {
			// You can access the token ID with `token.id`.
			// Get the token ID to your server-side code for use.
			var form$ = jQuery("#edd_purchase_form, #edd_profile_editor_form, #edd-recurring-form");

			// insert the token into the form so it gets submitted to the server
			form$.append("<input type='hidden' name='edd_stripe_token' value='" + token.id + "' />");

			// and submit
			var submit_button = form$.find('input[type="submit"][name!="edd_login_submit"]');
			submit_button.click();
		}
	});

	jQuery(window).on('popstate', function() {
		handler.close();
	});

}

jQuery(document).ready(function($) {

	Stripe.setPublishableKey( edd_stripe_vars.publishable_key );

	// non ajaxed
	$('body').on('click', '#edd_purchase_form input[type="submit"], #edd_profile_editor_form input[type="submit"], #edd-recurring-form input[type="submit"]', function(event) {

		if ( $(this).attr('name') == 'edd_login_submit' ) {
			return;
		}

		if( ( $('input[name="edd-gateway"]').val() == 'stripe' && $('.edd_cart_total .edd_cart_amount').data('total') > 0 ) || $('input[name="edd-recurring-update-gateway"]').val() == 'stripe' ) {

			$(this).after('<span class="edd-loading-ajax edd-loading"></span>');

			if ( ! $('input[name="edd_stripe_token"]' ).length && 'true' == edd_stripe_vars.checkout ) {
				event.stopPropagation();
			}

			event.preventDefault();

			if( 'true' == edd_stripe_vars.checkout ) {

				if ( checkout_modal_shown ) {
					return;
				}

				checkout_modal_shown = true;

				var amount = $('.edd_cart_total .edd_cart_amount').data('total');
				if( 'true' != edd_stripe_vars.is_zero_decimal ) {
					amount *= 100;
					amount = Math.round( amount );
				}

				// Open Checkout with further options:
				handler.open({
					name: edd_stripe_vars.store_name,
					amount: amount,
					currency: edd_stripe_vars.currency,
					zipCode: ( 'true' === edd_stripe_vars.zipcode ),
					allowRememberMe: ( 'true' === edd_stripe_vars.remember_me ),
					alipay: ( 'true' === edd_stripe_vars.alipay ),
					billingAddress:  ( 'true' === edd_stripe_vars.billing_address ),
					email: $('#edd-email').val(),
					closed: function() {
						checkout_modal_shown = false;
						jQuery('.edd-loading-ajax').hide();
						jQuery('#edd-purchase-button').val(edd_stripe_vars.submit_text).prop('disabled', false);
					}
				});

			} else {

				edd_stripe_process_card();

			}

		}

	});

});


function edd_stripe_response_handler(status, response) {
	if (response.error) {
		// re-enable the submit button
		jQuery('#edd_purchase_form #edd-purchase-button, #edd_profile_editor_form #edd_profile_editor_submit, #edd-recurring-form #edd-recurring-update-submit').attr("disabled", false);

		var error = '<div class="edd_errors"><p class="edd_error">' + response.error.message + '</p></div>';

		// show the errors on the form
		jQuery('#edd-stripe-payment-errors').html(error);

		jQuery('.edd-loading-ajax').hide();
		if( edd_global_vars.complete_purchase )
			jQuery('#edd-purchase-button').val(edd_global_vars.complete_purchase);
		else
			jQuery('#edd-purchase-button').val('Purchase');

	} else {
		var form$ = jQuery("#edd_purchase_form, #edd_profile_editor_form, #edd-recurring-form");
		// token contains id, last4, and card type
		var token = response['id'];

		// insert the token into the form so it gets submitted to the server
		form$.append("<input type='hidden' name='edd_stripe_token' value='" + token + "' />");

		// and submit
		form$.get(0).submit();

	}
}


function edd_stripe_process_card() {
	var state;

	// disable the submit button to prevent repeated clicks
	jQuery('#edd_purchase_form #edd-purchase-button, #edd_profile_editor_form #edd_profile_editor_submit, #edd-recurring-form #edd-recurring-update-submit').attr('disabled', 'disabled');

	if( jQuery('.billing-country').val() ==  'US' ) {
		state = jQuery('#card_state_us').val();
	} else if( jQuery('.billing-country').val() ==  'CA' ) {
		state = jQuery('#card_state_ca').val();
	} else {
		state = jQuery('#card_state_other').val();
	}

	if( typeof jQuery('#card_state_us').val() != 'undefined' ) {

		if( jQuery('.billing-country').val() ==  'US' ) {
			state = jQuery('#card_state_us').val();
		} else if( jQuery('.billing-country').val() ==  'CA' ) {
			state = jQuery('#card_state_ca').val();
		} else {
			state = jQuery('#card_state_other').val();
		}

	} else {
		state = jQuery('.card_state').val();
	}

	// createToken returns immediately - the supplied callback submits the form if there are no errors
	Stripe.createToken({
		number: 	     jQuery('.card-number').val(),
		name: 		     jQuery('.card-name').val(),
		cvc: 		     jQuery('.card-cvc').val(),
		exp_month:       jQuery('.card-expiry-month').val(),
		exp_year: 	     jQuery('.card-expiry-year').val(),
		address_line1: 	 jQuery('.card-address').val(),
		address_line2: 	 jQuery('.card-address-2').val(),
		address_city: 	 jQuery('.card-city').val(),
		address_state: 	 state,
		address_zip: 	 jQuery('.card-zip').val(),
		address_country: jQuery('#billing_country').val()
	}, edd_stripe_response_handler);

	return false; // submit from callback
}
