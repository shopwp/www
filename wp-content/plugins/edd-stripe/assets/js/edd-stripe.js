var edd_recurring_vars;
jQuery(document).ready(function($) {

	/**
	 * EDD Checkout
	 */
	var EDDS_Checkout = {

		init : function() {
			var body = $('body');
			this.existing_cards(body);
			this.toggle_address_update(body);
		},

		existing_cards : function(body) {
			body.on('click', '.edd-stripe-existing-card', function() {
				if ( edd_scripts.is_checkout ) {
					$('.edd-stripe-card-radio-item').removeClass('selected');
					var card_id = $(this).val(),
						new_card_wrapper = $('.edd-stripe-new-card'),
						inputs = $('#edd_cc_address p input, #edd_cc_address p select').not('#edd-stripe-update-billing-address'),
						billing_address_wrapper = $('.edd-stripe-update-billing-address-wrapper');


					if ( 'new' === card_id ) {
						$(this).parent().addClass('selected');
						new_card_wrapper.slideDown();
						inputs.prop('readonly', false);
						billing_address_wrapper.hide();
					} else {
						$(this).parent().parent().addClass('selected');
						new_card_wrapper.slideUp();
						billing_address_wrapper.show();
						var billing_info = $('#' + card_id + '-billing-details');
						$('#card_address').val(billing_info.data('address_line1'));
						$('#card_address_2').val(billing_info.data('address_line2'));
						$('#card_city').val(billing_info.data('address_city'));
						$('#card_zip').val(billing_info.data('address_zip'));
						$('#billing_country').val(billing_info.data('address_country')).trigger('change');
						setTimeout(function(){
						  $('#card_state').val(billing_info.data('address_state')).trigger('change').prop('readonly', true);
						}, 1500);

						// Only set the fields to read-only if we have a checkbox for updating the fields.
						if ( $('#edd-stripe-update-billing-address').length ) {
							inputs.prop('readonly', true);
						}
					}
				}
			});
		},

		toggle_address_update : function(body) {
			body.on('click', '#edd-stripe-update-billing-address', function() {
				var checked = $(this).is(':checked');
				var target  = $('#edd_cc_address p input, #edd_cc_address p select').not($(this));
				if ( checked ) {
					target.prop('readonly', false);
				} else {
					target.prop('readonly', true);
				}
			});
		}

	};
	EDDS_Checkout.init();

	/**
	 * Manage Cards
	 */
	var EDDS_Manage_Cards = {

		init : function() {
			this.add_new();
			this.cancel_add_new();
			this.update();
			this.cancel_update();
			this.submit_update();
			this.change_country();
			this.set_default();
			this.delete();
		},

		add_new : function() {
			$('.edd-stripe-add-new').click(function(e) {
				var card_form = $('.edd-stripe-add-new-card');

				$('#edd-stripe-manage-cards .edd-alert').remove();
				$('#edd-stripe-manage-cards .edd_errors').remove();

				if ( ! card_form.is(':visible')) {
					$('#edd-stripe-manage-cards .edd-stripe-add-new-card').slideDown();
					$('#edd-stripe-add-new-cancel').show();
				} else {
					var button       = $(this);
					var button_width = button.width();
					button.removeClass('.edd-stripe-add-new');
					button.find('.button-text').hide();
					button.find('.edd-loading').show();
					button.css('width', button_width);

					var card_fields_wrapper = $('.edd-stripe-add-new-card');

					if (card_fields_wrapper.find('.billing-country').val() == 'US') {
						state = card_fields_wrapper.find('#card_state_us').val();
					} else if (card_fields_wrapper.find('.billing-country').val() == 'CA') {
						state = card_fields_wrapper.find('#card_state_ca').val();
					} else {
						state = card_fields_wrapper.find('#card_state_other').val();
					}

					if (typeof card_fields_wrapper.find('#card_state_us').val() != 'undefined') {

						if (card_fields_wrapper.find('.billing-country').val() == 'US') {
							state = card_fields_wrapper.find('#card_state_us').val();
						} else if (card_fields_wrapper.find('.billing-country').val() == 'CA') {
							state = card_fields_wrapper.find('#card_state_ca').val();
						} else {
							state = card_fields_wrapper.find('#card_state_other').val();
						}

					} else {
						state = card_fields_wrapper.find('.card_state').val();
					}

					var token_args             = {};
					token_args.number          = card_fields_wrapper.find('.card-number').val();
					token_args.name            = card_fields_wrapper.find('.card-name').val();
					token_args.cvc             = card_fields_wrapper.find('.card-cvc').val();
					token_args.exp_month       = card_fields_wrapper.find('.card-expiry-month').val();
					token_args.exp_year        = card_fields_wrapper.find('.card-expiry-year').val();
					token_args.address_line1   = card_fields_wrapper.find('.card-address').val();
					token_args.address_line2   = card_fields_wrapper.find('.card-address-2').val();
					token_args.address_city    = card_fields_wrapper.find('.card-city').val();
					token_args.address_state   = state;
					token_args.address_zip     = card_fields_wrapper.find('.card-zip').val();
					token_args.address_country = card_fields_wrapper.find('.billing_country').val();
					edd_stripe_process_card(token_args, edd_stripe_add_card_handler);

				}

				return false;
			});
		},

		cancel_add_new : function() {
			$('#edd-stripe-add-new-cancel').click(function(e) {
				$('#edd-stripe-manage-cards .edd-stripe-add-new-card').slideUp();
				$(this).hide();
				$('#edd-stripe-add-new').show();

				return false;
			});
		},

		update : function() {
			$('.edd-stripe-update-card').click(function(e) {
				$('.card-update-form').slideUp();
				$('.card-actions').show();
				$(this).parent().parent().parent().find('.card-update-form').slideDown();
				$(this).parent().parent().hide();
				return false;
			});
		},

		cancel_update : function() {
			$('.edd-stripe-cancel-update').click(function(e) {
				$(this).parent().parent().slideUp();
				$(this).parent().parent().prev('.card-actions').show();
				return false;
			});
		},

		submit_update : function() {
			$('.edd-stripe-submit-update').click(function(e) {
				var button = $(this);
				button.removeClass('.edd-stripe-submit-update');
				var button_width = button.width();
				button.find('.button-text').hide();
				button.find('.edd-loading').show();
				button.css('width', button_width);
				$('.edd-stripe-add-new-card .edd-alert').remove();
				var card_id = button.parent().find('input[name="card_id"]').val();
				var user_id = $('#stripe-update-card-user_id').val();
				var fields  = button.parent().parent().find('.card-update-field');
				var nonce   = button.parent().find('input[name="card_update_nonce"]').val();

				var card_data = {};
				fields.each( function( field ) {
					card_data[$(this).data('key')] = $(this).val();
				});

				var data = {
					edd_action: 'update_stripe_card',
					card_id: card_id,
					user_id: user_id,
					card_data: card_data,
					nonce: nonce
				};

				 $.ajax({
					type: "POST",
					data: data,
					dataType: "json",
					url: edd_scripts.ajaxurl,
					xhrFields: {
						withCredentials: true
					},
					success: function (response) {
						if ( response.success === true ) {
							button.parent().replaceWith('<p class="edd-alert edd-alert-success">' + response.message + '</p>');
							setTimeout(function(){
								location.reload();
							}, 1000);
						} else {
							button.addClass('.edd-stripe-submit-update');
							button.find('.button-text').show();
							button.find('.edd-loading').hide();
							button.parent().parent().append('<p class="edd-alert edd-alert-error">' + response.message + '</p>');
							return false;
						}
					}
				}).fail(function (response) {
					if ( window.console && window.console.log ) {
						console.log( response );
					}
				}).done(function (response) {

				});

				return false;
			});
		},

		change_country : function() {
			$('.address_country').change(function() {
				var $this = $(this);

				// If the country field has changed, we need to update the state/province field
				var postData = {
					action: 'edd_get_shop_states',
					country: $this.val(),
					field_name: 'card_state'
				};

				$.ajax({
					type: "POST",
					data: postData,
					url: edd_scripts.ajaxurl,
					xhrFields: {
						withCredentials: true
					},
					success: function (response) {

						if( 'nostates' == $.trim(response) ) {
							var text_field = '<input type="text" name="card_state" class="card_state card-state edd-input required" value=""/>';
							$this.parent().find('.card_state').replaceWith( text_field );
						} else {
							$this.parent().find('.card_state').replaceWith( response );
						}

					}
				}).fail(function (data) {
					if ( window.console && window.console.log ) {
						console.log( data );
					}
				}).done(function (data) {
				});

				return false;
			});
		},

		set_default : function() {
			$('.edd-stripe-default-card').click(function(e) {
				var message_wrapper = $(this).parent().parent().next('.card-update-form');
				var card_id = message_wrapper.find('input[name="card_id"]').val();
				var nonce   = message_wrapper.find('input[name="card_update_nonce"]').val();
				var user_id = $('#stripe-update-card-user_id').val();
				message_wrapper.html('<span class="edd-loading-ajax edd-loading"></span>');
				message_wrapper.show();

				var data = {
					edd_action: 'set_default_card',
					card_id: card_id,
					user_id: user_id,
					nonce: nonce
				};

				 $.ajax({
					type: "POST",
					data: data,
					dataType: "json",
					url: edd_scripts.ajaxurl,
					xhrFields: {
						withCredentials: true
					},
					success: function (response) {
						if ( response.success === true ) {
							message_wrapper.html('<p class="edd-alert edd-alert-success">' + response.message + '</p>');
						} else {
							message_wrapper.html('<p class="edd-alert edd-alert-error">' + response.message + '</p>');
						}

						setTimeout(function(){
							location.reload();
						}, 1000);
					}
				}).fail(function (response) {
					if ( window.console && window.console.log ) {
						console.log( response );
					}
				}).done(function (response) {

				});

				return false;
			});
		},

		delete : function() {
			$('.edd-stripe-delete-card').click(function(e) {
				var message_wrapper = $(this).parent().parent().next('.card-update-form');
				var card_id = message_wrapper.find('input[name="card_id"]').val();
				var nonce   = message_wrapper.find('input[name="card_update_nonce"]').val();
				var user_id = $('#stripe-update-card-user_id').val();
				message_wrapper.html('<span class="edd-loading-ajax edd-loading"></span>');
				message_wrapper.show();

				var data = {
					edd_action: 'delete_stripe_card',
					card_id: card_id,
					user_id: user_id,
					nonce: nonce
				};

				 $.ajax({
					type: "POST",
					data: data,
					dataType: "json",
					url: edd_scripts.ajaxurl,
					xhrFields: {
						withCredentials: true
					},
					success: function (response) {
						if ( response.success === true ) {
							message_wrapper.html('<p class="edd-alert edd-alert-success">' + response.message + '</p>');
						} else {
							message_wrapper.html('<p class="edd-alert edd-alert-error">' + response.message + '</p>');
						}

						setTimeout(function(){
							location.reload();
						}, 1000);
					}
				}).fail(function (response) {
					if ( window.console && window.console.log ) {
						console.log( response );
					}
				}).done(function (response) {

				});

				return false;
			});
		}

	};
	EDDS_Manage_Cards.init();

	Stripe.setPublishableKey( edd_stripe_vars.publishable_key );

	// non ajaxed
	$('body').on('click', '#edd-purchase-button, #edd_profile_editor_form input[type="submit"], #edd-recurring-form input[type="submit"]', function(event) {

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

				// Since the error fields aren't shown for Stripe we need to include them
				if ( $('#edd-stripe-payment-errors').length == 0 ) {
					var checkout_error_anchor = '#edd_purchase_submit'; // Default here in case someone isn't on EDD 2.8 yet
					if( typeof edd_global_vars.checkout_error_anchor != 'undefined' ) {
						checkout_error_anchor = edd_global_vars.checkout_error_anchor;
					}

					// Insert our error wrapper on the anchor.
					$(checkout_error_anchor).append('<div id="edd-stripe-payment-errors"></div>');
				}

				var error_target = $('#edd-stripe-payment-errors');
				$(error_target).html('').hide();

				var purchase_button = $('#edd-purchase-button');

				var errors = [];

				var terms_checked    = true;
				if ( $('#edd_agree_to_terms').length != 0 ) {
					terms_checked = $('#edd_agree_to_terms').is(':checked');
					if ( false === terms_checked ) {
						errors.push( edd_stripe_vars.checkout_agree_to_terms );
					}
				}

				var html5_validation = $('#edd_purchase_form')[0].checkValidity();
				if ( false === html5_validation ) {
					errors.push(  edd_stripe_vars.checkout_required_fields_error );
				}

				if ( errors.length > 0 ) {
					$('.edd-loading-ajax').hide();
					$(purchase_button).val(edd_stripe_vars.submit_text).prop('disabled', false);

					for (var i = 0, len = errors.length; i < len; i++) {
						$(error_target).append('<div class="edd_errors"><p class="edd_error">' + errors[i] + '</p></div>');
					}

					$(error_target).show();
					return;
				}

				if ( checkout_modal_shown ) {
					return;
				}

				checkout_modal_shown = true;

				var amount = $('.edd_cart_total .edd_cart_amount').data('total');

				if ( typeof edd_recurring_vars !== 'undefined' ) {
					if( edd_recurring_vars.has_trial && edd_recurring_vars.total_plain ) {
						amount = edd_recurring_vars.total_plain;
					}
				}

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
					billingAddress:  ( 'true' === edd_stripe_vars.billing_address ),
					email: $('#edd-email').val(),
					closed: function() {
						checkout_modal_shown = false;
						$('.edd-loading-ajax').hide();

						if ( $('input[name="edd_stripe_token"]').length == 0 ) {
							$(purchase_button).val(edd_stripe_vars.submit_text).prop('disabled', false);
						}
					}
				});

			} else {
				var state;

				// disable the submit button to prevent repeated clicks
				$('#edd_purchase_form #edd-purchase-button, #edd_profile_editor_form #edd_profile_editor_submit, #edd-recurring-form #edd-recurring-update-submit').attr('disabled', 'disabled');

				// createToken returns immediately - the supplied callback submits the form if there are no errors
				var is_existing = $("input[name='edd_stripe_existing_card']:checked").val();

				if ( typeof is_existing !== 'undefined' && 'new' !== is_existing ) {
					edd_stripe_response_handler( true, false );
					return true;
				}

				if( $('.billing-country').val() ==  'US' ) {
					state = $('#card_state_us').val();
				} else if( $('.billing-country').val() ==  'CA' ) {
					state = $('#card_state_ca').val();
				} else {
					state = $('#card_state_other').val();
				}

				if( typeof $('#card_state_us').val() != 'undefined' ) {

					if( $('.billing-country').val() ==  'US' ) {
						state = $('#card_state_us').val();
					} else if( $('.billing-country').val() ==  'CA' ) {
						state = $('#card_state_ca').val();
					} else {
						state = $('#card_state_other').val();
					}

				} else {
					state = $('.card_state').val();
				}

				var token_args = {};
				token_args.number    = $('.card-number').val();
				token_args.name      = $('.card-name').val();
				token_args.cvc       = $('.card-cvc').val();
				token_args.exp_month = $('.card-expiry-month').val();
				token_args.exp_year  = $('.card-expiry-year').val();
				token_args.address_line1   = $('.card-address').val();
				token_args.address_line2   = $('.card-address-2').val();
				token_args.address_city    = $('.card-city').val();
				token_args.address_state   = state;
				token_args.address_zip     = $('.card-zip').val();
				token_args.address_country = $('#billing_country').val();
				edd_stripe_process_card( token_args, edd_stripe_response_handler );
			}

		}

	});

});

var edd_global_vars, edd_scripts;

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
			var submit_button = form$.find('#edd-purchase-button');
			submit_button.click();
		}
	});

	jQuery(window).on('popstate', function() {
		handler.close();
	});

}

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
		if ( false !== response ) {
			// token contains id, last4, and card type
			var token = response['id'];

			// insert the token into the form so it gets submitted to the server
			form$.append("<input type='hidden' name='edd_stripe_token' value='" + token + "' />");
		}
		// and submit
		form$.get(0).submit();
	}
}

function edd_stripe_add_card_handler(status, response) {

	if (response.error) {

		var error = '<div class="edd_errors"><p class="edd_error">' + response.error.message + '</p></div>';
		// show the errors on the form
		jQuery('.edd-stripe-add-card-actions').append(error);

	} else {

		if ( false !== response ) {
			// token contains id, last4, and card type
			var token = response['id'];

			// insert the token into the form so it gets submitted to the server
			jQuery('#edd-stripe-manage-cards').append("<input type='hidden' id='edd-stripe-new-token' name='edd_stripe_token' value='" + token + "' />");
		}

		var button = jQuery('.edd-stripe-add-new');
		var stripe_token = jQuery('body').find('#edd-stripe-new-token').val();
		var data         = {
			edd_action: 'add_stripe_card',
			token     : stripe_token,
			user_id   : jQuery('#stripe-update-card-user_id').val(),
			nonce     : jQuery('input[name="edd-stripe-add-card-nonce"]').val()
		};

		jQuery.ajax({
			type     : "POST",
			data     : data,
			dataType : "json",
			url      : edd_scripts.ajaxurl,
			xhrFields: {
				withCredentials: true
			},
			success  : function (response) {

				if (response.success === true) {

					button.parent().replaceWith('<p class="edd-alert edd-alert-success">' + response.message + '</p>');
					setTimeout(function () {
						location.reload();
					}, 1000);

				} else {

					button.addClass('.edd-stripe-add-new');
					button.find('.button-text').show();
					button.find('.edd-loading').hide();
					button.css('width', 'auto');
					button.parent().parent().append('<p class="edd-alert edd-alert-error">' + response.message + '</p>');
					return false;

				}

			}

		}).fail(function (response) {
			if (window.console && window.console.log) {
				console.log(response);
			}
		}).done(function (response) {

		});

	}
}


function edd_stripe_process_card( token_args, callback ) {
	Stripe.createToken( token_args, callback );

	return false; // submit from callback
}
