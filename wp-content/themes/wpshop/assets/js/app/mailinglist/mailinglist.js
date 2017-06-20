import {
  getMailchimpListById
} from '../ws/ws.js';


/*

On Mailing List Form submission

*/
function validateMailingListForm($) {

  $("#mailinglist-form").validate({

    submitHandler: function(form, e) {

      e.preventDefault();

      $(form).addClass('is-submitting');
      $(form).find('input, button[type="submit"]').addClass('is-disabled').prop("disabled", true);
      $(form).find('.spinner').addClass('is-visible');

      getMailchimpListById($)
        .done(function(data) {

          $(form).find('input, button[type="submit"]').prop("disabled", false);
          $('#mailinglist-email').focus().select();

          if(data.code !== 200) {
            $(form).find('.form-error').addClass('is-visible');
            $(form).find('#mailinglist-email-error').append('<i class="fa fa-times-circle" aria-hidden="true"></i> ' + data.message.detail);
            $(form).find('.spinner').removeClass('is-visible');
            $(form).find('input, button[type="submit"]').removeClass('is-disabled');
            $(form).removeClass('is-submitting');

          } else {

            $(form).removeClass('is-submitting');
            $(form).find('.spinner').removeClass('is-visible');
            $(form).find('input, button[type="submit"]').removeClass('is-disabled');
            $(form).find('.form-success').addClass('is-visible');
            $(form).find('.form-success').append('<i class="fa fa-check-circle" aria-hidden="true"></i> Success! Please check your email to finish signing up.');
            $(form).addClass('is-submitted');

          }

        })
        .fail(function(jqXHR, textStatus) {

          $(form).find('.form-error').addClass('is-visible');
          $(form).find('#mailinglist-email-error').append('Error! ' + textStatus);

          $(form).find('.spinner').removeClass('is-visible');
          $(form).find('input, button[type="submit"]').removeClass('is-disabled');
          $(form).removeClass('is-submitting');

          $('#mailinglist-email').prop("disabled", false);


        });

    },

    rules: {
      email: {
        required: true,
        email: true
      }
    },

    errorClass: 'error',
    validClass: 'succes',

    highlight: function (element, errorClass, validClass) {
      $('#mailinglist-email').parent().removeClass('form-valid');
      $('.form-error').addClass('is-visible');
      $('.form-success').removeClass('is-visible');

    },
    unhighlight: function (element, errorClass, validClass) {
      // $('.form-success').addClass('is-visible');
      $('.form-error').removeClass('is-visible');

    },
    success: function(label){
      $('#mailinglist-email').parent().addClass('form-valid');

    },
    errorPlacement: function(error, element) {
      error.appendTo($('.form-error'));
    }

  });

}

function initMailinglist($) {
  validateMailingListForm($);
}

export { initMailinglist }
