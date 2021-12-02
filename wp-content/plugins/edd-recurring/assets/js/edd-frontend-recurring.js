var edd_scripts;
jQuery(document).ready(function($) {
	$('.edd_subscription_cancel').on('click',function(e) {
		if( confirm( edd_recurring_vars.confirm_cancel ) ) {
			return true;
		}
		return false;
	});

	// Force subscription terms to behave for Custom Prices
	$('.edd_download_purchase_form').each(function() {

		var form = $(this);

		if ( form.find( '.edd-cp-container' ).length && form.find( '.edd_price_options' ).length ) {

			var terms = form.find('.eddr-custom-terms-notice');
			var signup_fee = form.find('.eddr-custom-signup-fee-notice');
			terms.prev().append(terms);
			signup_fee.prev().append(signup_fee);
			terms.show();
			signup_fee.show();

		} else if ( form.find( '.edd-cp-container' ).length ) {

			form.find('.edd_cp_price').keyup(function() {
				form.find('.eddr-terms-notice,.eddr-signup-fee-notice').hide();
				form.find('.eddr-custom-terms-notice,.eddr-custom-signup-fee-notice').show();
			});

		}

	});

	if( edd_recurring_vars.has_trial ) {
		setTrialTotal();

		$( document.body ).on( 'edd_discount_applied', setTrialTotal );
		$( document.body ).on( 'edd_discount_removed', setTrialTotal );
		$( document.body ).on( 'edd_taxes_recalculated', setTrialTotal );
	}

	/**
	 * Sets the total order amount in the UI for a trial. (`0.00` in the store currency)
	 *
	 * @since 2.11
	 */
	function setTrialTotal( e, data ) {
		// This sets the amount due today.
		$('.edd_cart_amount').html( edd_recurring_vars.total );

		// This sets the recurring amount (after a trial).
		if ( 'undefined' !== typeof data && data.response && data.response.total ) {
			$( 'body' ).find( '.edd_recurring_total_after_trial' ).each( function () {
				$( this ).html( data.response.total );
			} );
		}
	}

	// Look to see if the customer has purchased a free trial after email is entered on checkout
	$('#edd_purchase_form').on( 'focusout', '#edd-email', function() {

		if( 'undefined' == edd_scripts ) {
			return;
		}

		// We don't need to make this AJAX call, if there isn't a trial in the cart.
		if ( ! edd_recurring_vars.has_trial ) {
			return;
		}

		var email = $(this).val();
		var product_ids = [];

		$('body').find('.edd_cart_item').each(function() {
			product_ids.push( $(this).data( 'download-id' ) );
		});

		 $.ajax({
			type: "POST",
			data: {
				action: 'edd_recurring_check_repeat_trial',
				email: email,
				downloads: product_ids
			},
			dataType: "json",
			url: edd_scripts.ajaxurl,
			xhrFields: {
				withCredentials: true
			},
			success: function (response) {

				if( response.message ) {
					$('<div class="edd_errors"><p class="edd_error">' + response.message + '</p></div>').insertBefore( '#edd_purchase_submit' );
				}

			}
		}).fail(function (response) {
			if ( window.console && window.console.log ) {
				console.log( response );
			}
		}).done(function (response) {

		});

	});

});
