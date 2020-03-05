import { getMailchimpListById } from '../ws/ws.js'

import { initMailinglistTracking } from '../analytics/analytics.js'

/*

On Mailing List Form submission

*/
function validateMailingListForm($form) {
  $form.validate({
    submitHandler: function(form, e) {
      console.log('form ::::::: ', form)

      e.preventDefault()

      console.log('form', form)

      jQuery(form).addClass('is-submitting')
      jQuery(form)
        .find('input, button[type="submit"]')
        .addClass('is-disabled')
        .prop('disabled', true)
      jQuery(form)
        .find('.spinner')
        .addClass('is-visible')

      getMailchimpListById($)
        .done(function(data) {
          console.log('...... data ', data)

          jQuery(form)
            .find('input, button[type="submit"]')
            .prop('disabled', false)

          jQuery(form)
            .find('.mailinglist-email')
            .focus()
            .select()

          if (data.code !== 200) {
            jQuery(form)
              .find('.form-error')
              .addClass('is-visible')
            jQuery(form)
              .find('.mailinglist-email-error')
              .append(
                '<i class="fa fa-times-circle" aria-hidden="true"></i> ' + data.message.detail
              )
            jQuery(form)
              .find('.spinner')
              .removeClass('is-visible')
            jQuery(form)
              .find('input, button[type="submit"]')
              .removeClass('is-disabled')
            jQuery(form).removeClass('is-submitting')
          } else {
            jQuery(form).removeClass('is-submitting')
            jQuery(form)
              .find('.spinner')
              .removeClass('is-visible')
            jQuery(form)
              .find('input, button[type="submit"]')
              .removeClass('is-disabled')
            jQuery(form)
              .find('.form-success')
              .addClass('is-visible')
            jQuery(form)
              .find('.form-success')
              .append(
                '<i class="fa fa-check-circle" aria-hidden="true"></i> Success! Please check your email to finish signing up.'
              )
            jQuery(form).addClass('is-submitted')

            initMailinglistTracking()

            jQuery(form)
              .find('.mailinglist-email')
              .val('')
              .blur()
          }
        })
        .fail(function(jqXHR, textStatus) {
          jQuery(form)
            .find('.form-error')
            .addClass('is-visible')
          jQuery(form)
            .find('.mailinglist-email-error')
            .append('Error! ' + textStatus)

          jQuery(form)
            .find('.spinner')
            .removeClass('is-visible')
          jQuery(form)
            .find('input, button[type="submit"]')
            .removeClass('is-disabled')
          jQuery(form).removeClass('is-submitting')

          jQuery(form)
            .find('.mailinglist-email')
            .prop('disabled', false)
        })
    },

    rules: {
      email: {
        required: true,
        email: true
      }
    },

    errorClass: 'error',
    validClass: 'succes',

    highlight: function(element, errorClass, validClass) {
      console.log('highlight :: element', element)

      jQuery(element)
        .parent()
        .removeClass('form-valid')
      jQuery('.form-error').addClass('is-visible')
      jQuery('.form-success').removeClass('is-visible')
    },
    unhighlight: function(element, errorClass, validClass) {
      console.log('unhighlight :: element', element)
      jQuery(element)
        .find('.form-error')
        .removeClass('is-visible')
    },
    success: function(label) {
      jQuery(label)
        .parent()
        .addClass('form-valid')
    },
    errorPlacement: function(error, element) {
      console.log('errorPlacement :: element', element)
      error.appendTo(
        jQuery(element)
          .closest('.mailinglist-form')
          .find('.form-error')
      )
    }
  })
}

function initMailinglist($form) {
  validateMailingListForm($form)
}

export { initMailinglist }
