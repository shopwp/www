/**
 * EDD Admin Recurring JS
 *
 * @description: JS for EDD's Recurring Add-on applied in admin download (single download post) screen
 *
 */
var EDD_Recurring_Vars;

jQuery( document ).ready( function ( $ ) {

	var EDD_Recurring = {
		init: function () {

			//Recurring select field conditionals
			this.recurring_select();
			this.custom_price_toggle();
			this.free_trial_toggle();
			this.variable_price_free_trial_toggle();
			this.validate_times();
			this.edit_expiration();
			this.edit_product_id();
			this.edit_profile_id();
			this.edit_txn_id();
			this.new();
			this.delete();
			//Ensure when new rows are added recurring fields respect recurring select option
			$( '.edd_add_repeatable' ).on( 'click', this.recurring_select() );

			// Toggle display of Billing Cycle details
			$( '.edd-item-toggle-next-hidden-row' ).on( 'click', function(e) {
				e.preventDefault();
				$(this).parents('tr').siblings('.edd-item-hidden-row').slideToggle();
			});

		},

		/**
		 * Recurring Select
		 * @description: Ensures that the "period", "times", and "signup fees" fields are disabled/enabled according to the "Recurring" selection yes/no option
		 */
		recurring_select: function () {
			$( 'body' ).on( 'change', '.edd-recurring-enabled select, select#edd_recurring, select#edd_custom_recurring', function () {
				var $this  = $( this ),
					fields = $this.parents( '#edd_regular_price_field' ).find( 'select,input[type="number"]' ),
					val    = $( 'option:selected', this ).val();

				if( ! $this.is(':visible') ) {
					return;
				}


				// Is this a variable select? Check parent
				if ( $this.parents( '.edd_variable_prices_wrapper' ).length > 0 ) {

					fields = $this.parents('.edd_repeatable_row').find( '.times input, .edd-recurring-period select, .edd-recurring-free-trial input, .edd-recurring-free-trial select, .signup_fee input' );

				} else if( 'edd_custom_recurring' == $(this).attr('id') ) {

					fields = $('.edd_recurring_custom_wrap').find( '.times input, #edd_custom_period, .signup_fee input' );

					if( $('#edd_recurring_free_trial').is(':checked') ) {
						$('.signup_fee input, #edd_signup_fee').val(0).attr('disabled', true );
					}
				}

				// Enable/disable fields based on user selection
				if ( val == 'no' ) {
					fields.attr( 'disabled', true );
				} else {
					fields.attr( 'disabled', false );
				}

				$this.attr( 'disabled', false );

			} );

			// Kick it off
			$( '.edd-recurring-enabled select, select#edd_recurring, select#edd_custom_recurring' ).change();

			$( 'input[name$="[times]"], input[name$=times]' ).change( function () {
				$( this ).next( '.times' ).text( $( this ).val() == 1 ? EDD_Recurring_Vars.singular : EDD_Recurring_Vars.plural );
			} );
		},

		/**
		 * Custom Price toggle
		 * @description: Hides / shows recurring options for a custom price
		 */
		custom_price_toggle: function () {
			$('body').on('click', '#edd_cp_custom_pricing', function() {
				$('.edd_recurring_custom_wrap').toggle();
			});
		},

		/**
		 * Free trial toggle
		 * @description: Hides / shows recurring options for a free trial
		 */
		free_trial_toggle: function () {
			$('body').on('click', '#edd_recurring_free_trial', function() {
				if( $(this).is(':checked') ) {
					$('#edd_recurring_free_trial_options,#edd-sl-free-trial-length-notice').show();
					$('.signup_fee input, #edd_signup_fee').val(0).attr('disabled', true );
				} else {
					$('.signup_fee input, #edd_signup_fee').attr('disabled', false );
					$('#edd-sl-free-trial-length-notice,#edd_recurring_free_trial_options').hide();
				}
			});

			$('body').on( 'change', '#edd_variable_pricing', function() {
				var checked   = $(this).is(':checked');
				var single    = $( '#edd_recurring_free_trial_options_wrap' );
				if ( checked ) {
					single.hide();
				} else {
					single.show();
				}
			});
		},

		variable_price_free_trial_toggle: function () {
			$( 'body' ).on( 'load change', '.trial-quantity', function () {
				var $this  = $( this ),
					fields = $this.parents().siblings( '.signup_fee' ).find( ':input' ),
					val    = $this.val();

				// Enable/disable fields based on user selection
				if ( val > 0 ) {
					fields.attr( 'disabled', true );
				} else {
					fields.attr( 'disabled', false );
				}

				$this.attr( 'disabled', false );
			});
		},

		/**
		 * Validate Times
		 * @description: Used for client side validation of times set for various recurring gateways
		 */
		validate_times: function () {

			var recurring_times = $( '.times' ).find( 'input[type="number"]' );

			//Validate times on times input blur (client side then server side)
			recurring_times.on( 'change', function () {

				var time_val = $( this ).val();
				var is_variable = $( 'input#edd_variable_pricing' ).prop( 'checked' );
				var recurring_option = $( this ).parents( '#edd_regular_price_field' ).find( '[id^=edd_recurring]' ).val();
				if ( is_variable ) {
					recurring_option = $( this ).parents( '.edd_variable_prices_wrapper' ).find( '[id^=edd_recurring]' ).val();
				}

				//Verify this is a recurring download first
				//Sanity check: only validate if recurring is set to Yes
				if ( recurring_option == 'no' ) {
					return false;
				}

				//Check if PayPal Standard is set & Validate times are over 1 - https://github.com/easydigitaldownloads/edd-recurring/issues/58
				if ( typeof EDD_Recurring_Vars.enabled_gateways.paypal !== 'undefined' && (time_val == 1 || time_val >= 53) ) {

					//Alert user of issue
					alert( EDD_Recurring_Vars.invalid_time.paypal );
					//Refocus on the faulty input
					$( this ).focus();

				}

			} );

		},

        /**
         * Edit Subscription Text Input
         *
         * @since
         *
         * @description: Handles actions when a user clicks the edit or cancel buttons in sub details
         *
         * @param link object The edit/cancelled element the user clicked
         * @param input the editable field
         */
        edit_subscription_input: function (link, input) {

            //User clicks edit
            if (link.text() === EDD_Recurring_Vars.action_edit) {
                //Preserve current value
                link.data('current-value', input.val());
                //Update text to 'cancel'
                link.text(EDD_Recurring_Vars.action_cancel);
            } else {
                //User clicked cancel, return previous value
                input.val(link.data('current-value'));
                //Update link text back to 'edit'
                link.text(EDD_Recurring_Vars.action_edit);
            }

        },

		edit_expiration: function() {

			$('.edd-edit-sub-expiration').on('click', function(e) {
				e.preventDefault();

				var link = $(this);
				var exp_input = $('input.edd-sub-expiration');
				EDD_Recurring.edit_subscription_input(link, exp_input);

				$('.edd-sub-expiration').toggle();
				$('#edd-sub-expiration-update-notice').slideToggle();
			});

		},

		edit_profile_id: function() {

			$('.edd-edit-sub-profile-id').on('click', function(e) {
				e.preventDefault();

				var link = $(this);
				var profile_input = $('input.edd-sub-profile-id');
				EDD_Recurring.edit_subscription_input(link, profile_input);

				$('.edd-sub-profile-id').toggle();
				$('#edd-sub-profile-id-update-notice').slideToggle();
			});

		},

		edit_product_id: function() {

			$('.edd-sub-product-id').on('change', function(e) {
				e.preventDefault();

				$('#edd-sub-product-update-notice').slideDown();
			});

		},

		edit_txn_id: function() {

			$('.edd-edit-sub-transaction-id').on('click', function(e) {
				e.preventDefault();

				var link = $(this);
				var txn_input = $('input.edd-sub-transaction-id');
				EDD_Recurring.edit_subscription_input(link, txn_input);

				$('.edd-sub-transaction-id').toggle();
			});

		},

		new: function() {

			$('.edd-recurring-new-customer,.edd-recurring-select-customer').on('click', function(e) {

				e.preventDefault();
				if($(this).hasClass('edd-recurring-new-customer')) {
					$('.edd-recurring-customer-wrap-new').show();
					$('.edd-recurring-customer-wrap-existing').hide();
				} else {
					$('.edd-recurring-customer-wrap-existing').show();
					$('.edd-recurring-customer-wrap-new').hide();
				}
				$('.edd-recurring-customer-wrap:visible').find('select,input').focus();

			});

			$('.edd-recurring-select-payment').on('change', function(e) {
				$('.edd-recurring-payment-id').toggle().val( '' );
				$('.edd-recurring-gateway-wrap').toggle();
			});

			$('#edd-recurring-new-subscription-wrap').on('change', 'select#products', function() {

				var $this = $(this), download_id = $this.val();

				if( parseInt( download_id ) > 0 ) {

					var postData = {
						action : 'edd_check_for_download_price_variations',
						download_id: download_id
					};

					$.ajax({
						type: "POST",
						data: postData,
						url: ajaxurl,
						success: function (prices) {

							$this.parent().find( '.edd-recurring-price-option-wrap' ).html( prices );

						}

					}).fail(function (data) {
						if ( window.console && window.console.log ) {
							console.log( data );
						}
					});

				}
			});

		},

		delete: function() {

			$('.edd-delete-subscription').on('click', function(e) {

				if( confirm( EDD_Recurring_Vars.delete_subscription ) ) {
					return true;
				}

				return false;
			});

		}

	};

	EDD_Recurring.init();

} );
