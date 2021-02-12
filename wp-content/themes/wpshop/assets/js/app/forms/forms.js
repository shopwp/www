function animateLabel($) {
  $('.mailinglist-form .form-label').on('click', function (e) {
    $(this).next().focus();
  });

  $('.mailinglist-form .form-input').on('focusin', function () {
    $(this).closest('.form-control').addClass('is-focused');
  });

  $('.mailinglist-form .form-input').on('focusout', function () {
    if (!$(this).val()) {
      $(this).closest('.form-control').removeClass('is-focused');
    }
  });
}

function initAccordions($) {
  $('.accordion-heading')
    .off()
    .on('click', function (e) {
      $(this).next().slideToggle('fast');
      $(this).toggleClass('is-open');

      if ($(this).hasClass('is-open')) {
        $(this)
          .find('[data-icon]')
          .removeClass('fas fa-plus-square')
          .addClass('fas fa-minus-square');
      } else {
        $(this)
          .find('[data-icon]')
          .removeClass('fas fa-minus-square')
          .addClass('fas fa-plus-square');
      }
    });
}

function initForms($) {
  animateLabel($);
  initAccordions($);
}

export { initForms, initAccordions };
