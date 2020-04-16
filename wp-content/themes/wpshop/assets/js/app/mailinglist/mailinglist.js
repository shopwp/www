import { getMailchimpListById } from '../ws/ws.js'

import { initMailinglistTracking } from '../analytics/analytics.js'

/*

On Mailing List Form submission

*/
function validateMailingListForm($form) {
  $form.validate({
    submitHandler: function (form, e) {
      console.log('form ::::::: ', form)

      e.preventDefault()
      $form.find('.form-message').empty()
      console.log('form', form)

      $form.addClass('is-submitting')
      $form.find('input, button[type="submit"]').addClass('is-disabled').prop('disabled', true)
      $form.find('.spinner').addClass('is-visible')

      getMailchimpListById($form)
        .done(function (data) {
          console.log('...... data ', data)

          $form.find('input, button[type="submit"]').prop('disabled', false)

          $form.find('.mailinglist-email').focus().select()

          if (data.code !== 200) {
            var type = $form.data('type')
            var message = data.message.detail

            if (message.includes('is already a list member')) {
              if (type === 'Getting Started') {
                message =
                  '<label class="success">It looks like you already requested the download link. <a href="https://wordpress.org/plugins/wpshopify/" target="_blank">Here it is again.</a></label>'

                $form.find('.form-message.form-error').empty()
                $form.removeClass('is-submitting')
                $form.find('.spinner').removeClass('is-visible')
                $form.find('input, button[type="submit"]').removeClass('is-disabled')
                $form.find('.form-success').addClass('is-visible')
                $form.find('.form-success').empty().append(message)
                $form.addClass('is-submitted')
                return
              } else {
                message = 'That email is already signed up, thanks!'
              }
            }

            // }
            $form.find('.form-message.form-success').empty()
            $form.find('.form-error').addClass('is-visible')
            $form
              .find('.form-message.form-error')
              .empty()
              .append(
                '<label class="error"><i class="fa fa-times-circle" aria-hidden="true"></i> ' +
                  message +
                  '</label>'
              )
            $form.find('.spinner').removeClass('is-visible')
            $form.find('input, button[type="submit"]').removeClass('is-disabled')
            $form.removeClass('is-submitting')
          } else {
            var type = $form.data('type')

            if (type === 'Getting Started') {
              var message =
                '<label class="success"><i class="fa fa-check-circle" aria-hidden="true"></i> Success! Please check your email to download</label>'
            } else {
              var message =
                '<label class="success"><i class="fa fa-check-circle" aria-hidden="true"></i> Success! Thanks for signing up</label>'
            }
            $form.find('.form-message.form-error').empty()
            $form.removeClass('is-submitting')
            $form.find('.spinner').removeClass('is-visible')
            $form.find('input, button[type="submit"]').removeClass('is-disabled')
            $form.find('.form-success').addClass('is-visible')
            $form.find('.form-success').empty().append(message)
            $form.addClass('is-submitted')

            initMailinglistTracking()
          }
        })
        .fail(function (jqXHR, textStatus) {
          $form.find('.form-message.form-success').empty()
          $form.find('.form-error').addClass('is-visible')
          $form
            .find('.form-message.form-error')
            .empty()
            .append('<label class="error">Error! ' + textStatus + '</label>')

          $form.find('.spinner').removeClass('is-visible')
          $form.find('input, button[type="submit"]').removeClass('is-disabled')
          $form.removeClass('is-submitting')

          $form.find('.mailinglist-email').prop('disabled', false)
        })
    },

    rules: {
      email: {
        required: true,
        email: true,
      },
    },

    errorClass: 'error',
    validClass: 'succes',

    highlight: function (element, errorClass, validClass) {
      console.log('highlight :: element', element)

      jQuery(element).parent().removeClass('form-valid')
      jQuery('.form-error').addClass('is-visible')
      jQuery('.form-success').removeClass('is-visible')
    },
    unhighlight: function (element, errorClass, validClass) {
      console.log('unhighlight :: element', element)
      jQuery(element).find('.form-error').removeClass('is-visible')
    },
    success: function (label) {
      console.log('success')

      $form.find('.form-error').removeClass('is-visible')

      jQuery(label).parent().addClass('form-valid')
    },
    errorPlacement: function (error, element) {
      error.appendTo(jQuery(element).closest('.mailinglist-form').find('.form-error'))
    },
  })
}

function initMailinglist($form) {
  validateMailingListForm($form)
}

export { initMailinglist }
