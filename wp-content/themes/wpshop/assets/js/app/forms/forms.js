import { insertMessage } from '../utils/utils'

function animateLabel($) {
  $('.mailinglist-form .form-label').on('click', function (e) {
    $(this).next().focus()
  })

  $('.mailinglist-form .form-input').on('focusin', function () {
    $(this).closest('.form-control').addClass('is-focused')
  })

  $('.mailinglist-form .form-input').on('focusout', function () {
    if (!$(this).val()) {
      $(this).closest('.form-control').removeClass('is-focused')
    }
  })
}

function addPlaceholders($) {
  var $inputUsername = $('#edd_user_login')
  var $inputPassword = $('#edd_user_pass')

  if ($inputUsername.length) {
    $inputUsername.attr('placeholder', $inputUsername.prev().text().trim())
  }

  if ($inputPassword.length) {
    $inputPassword.attr('placeholder', $inputPassword.prev().text().trim())
  }
}

function initAccordions($) {
  $('.accordion-heading')
    .off()
    .on('click', function (e) {
      $(this).next().slideToggle('fast')
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
  jQuery('.edd_price_options li').on('click', function () {
    jQuery('.edd_price_option_35').prop('checked', false)
    jQuery('.edd_price_options li').removeClass('is-highlighted is-valid')

    jQuery('.edd_price_options li').addClass('is-not-highlighted')

    jQuery(this).removeClass('is-not-highlighted')

    jQuery(this).addClass('is-highlighted is-valid')
    jQuery(this).find('.edd_price_option_35').prop('checked', true)
  })
}

function initForms($) {
  animateLabel($)
  addPlaceholders($)
  initAccordions($)
  initMessages($)
  initPurchaseForm()
}

export { initForms, initAccordions }
