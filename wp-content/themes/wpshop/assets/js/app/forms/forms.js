import { onInputAddError, addValidInputClass, insertValidIcon, removeValidIcon, hideInputError, removeValidClass } from '../forms/validation'

import { reduceFormData, enableForm, disableForm, insertMessage, clearFormFields } from '../utils/utils'

import { forgotPassword, resetPassword } from '../ws/ws'

/*

Animate Labels

*/
function animateLabel($) {
   $('#mailinglist-form .form-label').on('click', function(e) {
      $(this)
         .next()
         .focus()
   })

   $('#mailinglist-form .form-input').on('focusin', function() {
      $(this)
         .closest('.form-control')
         .addClass('is-focused')
   })

   $('#mailinglist-form .form-input').on('focusout', function() {
      if (!$(this).val()) {
         $(this)
            .closest('.form-control')
            .removeClass('is-focused')
      }
   })
}

/*

addPlaceholders

*/
function addPlaceholders($) {
   var $inputUsername = $('#edd_user_login')
   var $inputPassword = $('#edd_user_pass')

   if ($inputUsername.length) {
      $inputUsername.attr(
         'placeholder',
         $inputUsername
            .prev()
            .text()
            .trim()
      )
   }

   if ($inputPassword.length) {
      $inputPassword.attr(
         'placeholder',
         $inputPassword
            .prev()
            .text()
            .trim()
      )
   }
}

/*

addPlaceholders

*/
function addValidElsToReadOnlyInputs($) {
   $('form input:read-only:not([type="submit"])').after('<span class="is-valid"></span>')
}

/*

Setting certain inputs as valid by default

*/
function setInputsToDefaultValid($) {
   $('.logged-in.edd-checkout #edd_purchase_form #edd-last')
      .addClass('valid')
      .attr('aria-invalid', false)
      .after('<span class="is-valid"></span>')

   // $('.logged-in.edd-checkout #edd-email').off().unbind();
}

/*

Init Accordions

*/
function initAccordions($) {
   $('.accordion-heading')
      .off()
      .on('click', function(e) {
         $(this)
            .next()
            .slideToggle('fast')
         $(this).toggleClass('is-open')

         if ($(this).hasClass('is-open')) {
            $(this)
               .find('[data-icon]')
               .removeClass('fas fa-plus-square')
               .addClass('fas fa-minus-square')
         } else {
            $(this)
               .find('[data-icon]')
               .removeClass('fas fa-minus-square')
               .addClass('fas fa-plus-square')
         }
      })
}

/*

Set Correct Input Values
TODO: Set on server side if EDD allows us to

*/
function setCorrectInputValues($) {
   $('#edd_login_submit[value="Log In"]').val('Login')
}

/*

Step 1 - Reset Password Process

*/
function initForgotPasswordForm($) {
   $('#form-forgot-pass')
      .submit(function(e) {
         e.preventDefault()
      })
      .validate({
         rules: {
            wps_account_forgot_password: {
               required: true,
               email: true
            }
         },
         messages: {
            wps_account_forgot_password: {
               required: 'Email required',
               email: 'Please enter a valid email'
            }
         },
         highlight: onInputAddError,
         unhighlight: function(element) {
            removeValidIcon($(element))
            hideInputError($(element))
            addValidInputClass($(element))
            insertValidIcon($(element))
         },
         submitHandler: async function(form) {
            var $form = $(form)

            disableForm($form)

            var formData = reduceFormData($form)
            var email = await forgotPassword(formData)

            enableForm($form)

            if (email) {
               insertMessage('An email as been sent! Please click the link to finish the password reset process.', 'success')
            } else {
               insertMessage("That email doesn't exist, please try again.", 'error')
            }

            clearFormFields($form)
         }
      })
}

/*

Step 2 - Reset Password Process

*/
function initResetPasswordForm($) {
   $('#form-reset-pass')
      .submit(function(e) {
         e.preventDefault()
      })
      .validate({
         rules: {
            wps_account_new_password: {
               required: true,
               minlength: 12
            },
            wps_account_new_password_confirm: {
               required: true,
               equalTo: '#wps_account_new_password',
               minlength: 12
            }
         },
         messages: {
            wps_account_new_password: {
               required: 'New password required',
               minlength: 'Passwords must be at least 12 characters long'
            },
            wps_account_new_password_confirm: {
               required: 'Confirm new password',
               equalTo: 'Passwords must match',
               minlength: 'Passwords must be at least 12 characters long'
            }
         },
         highlight: onInputAddError,
         unhighlight: function(element) {
            removeValidIcon($(element))
            hideInputError($(element))
            addValidInputClass($(element))
            insertValidIcon($(element))
         },
         submitHandler: async function(form) {
            var $form = $(form)

            disableForm($form)

            var formData = reduceFormData($form)
            var passwordReset = await resetPassword(formData)

            if (passwordReset) {
               // insertMessage('Success! Your password has been reset.', 'success');
               window.location.href = '/account?password-reset=true'
            } else {
               enableForm($form)

               insertMessage("Sorry we couldn't reset your password. Please try again or email support for help.", 'error')
            }

            clearFormFields($form)
         }
      })
}

/*

initMessages
TODO: Pull into more general file as messages can span
more than just the account page

*/
function initMessages($) {
   if ($.urlParam('password-reset')) {
      insertMessage('Successfully updated password', 'success')
   }

   if ($.urlParam('email-change')) {
      insertMessage('Successfully updated email. Please log back in', 'success')
   }

   if ($.urlParam('email-name-change')) {
      insertMessage('Successfully updated email and name. Please log back in', 'success')
   }
}

function initPurchaseForm() {
   // jQuery('.edd_price_options li:first-of-type').addClass('is-highlighted is-valid')

   jQuery('.edd_price_options li').on('click', function() {
      jQuery('.edd_price_option_35').prop('checked', false)
      jQuery('.edd_price_options li').removeClass('is-highlighted is-valid')

      jQuery(this).addClass('is-highlighted is-valid')
      jQuery(this)
         .find('.edd_price_option_35')
         .prop('checked', true)
   })
}

function initForms($) {
   animateLabel($)
   addPlaceholders($)
   // addValidElsToReadOnlyInputs($);
   // setInputsToDefaultValid($);
   initAccordions($)
   initForgotPasswordForm($)
   initResetPasswordForm($)
   initMessages($)
   initPurchaseForm()
}

export { initForms, initAccordions }
