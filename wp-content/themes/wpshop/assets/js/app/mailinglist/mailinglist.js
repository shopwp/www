import { getMailchimpListById } from '../ws/ws.js';
import to from 'await-to-js';
import { initMailinglistTracking } from '../analytics/analytics.js';

async function addToMailchimpList($form) {
  var email = $form.find('.mailinglist-email').val();
  var type = $form.data('type');

  const response = await fetch(
    wpshopifyMarketing.api.restUrl + wpshopifyMarketing.api.namespace + '/mailinglist/add',
    {
      body: JSON.stringify({
        email: email,
        type: type,
      }),
      method: 'post',
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
        'X-WP-Nonce': wpshopifyMarketing.api.nonce,
      },
    }
  );

  return await response.json();
}

function showError($form, data) {
  $form.find('.form-message.form-success').empty();
  $form.find('.form-error').addClass('is-visible');
  $form
    .find('.form-message.form-error')
    .empty()
    .append('<label class="error">' + data.message + '</label>');
  $form.find('.spinner').removeClass('is-visible');
  $form.find('input, button[type="submit"]').removeClass('is-disabled').prop('disabled', false);
  $form.removeClass('is-submitting');
  $form.find('.mailinglist-email').focus().select();
}

function showSuccess($form, data) {
  var type = $form.data('type');

  if (type === 'Getting Started') {
    var message =
      '<label class="success"><i class="fa fa-check-circle" aria-hidden="true"></i> ' +
      data.message +
      '</label>';
  } else {
    var message =
      '<label class="success"><i class="fa fa-check-circle" aria-hidden="true"></i> Success! Thanks for signing up</label>';
  }
  $form.find('.form-message.form-error').empty();
  $form.removeClass('is-submitting');
  $form.find('.spinner').removeClass('is-visible');
  $form.find('input, button[type="submit"]').removeClass('is-disabled');
  $form.find('input, button[type="submit"]').prop('disabled', false);
  $form.find('input, button[type="submit"]').removeAttr('aria-invalid');
  $form.find('input[type="text"]').val('');
  $form.find('.form-success').addClass('is-visible');
  $form.find('.form-success').empty().append(message);
  $form.addClass('is-submitted');

  downloadFreeVersion();

  initMailinglistTracking();
}

function downloadFreeVersion() {
  var anchor = document.createElement('a');

  var link =
    'https://downloads.wordpress.org/plugin/wpshopify.' +
    wpshopifyMarketing.misc.latestVersion +
    '.zip';
  console.log('link', link);

  anchor.href = link;
  anchor.download = 'WP Shopify';
  anchor.click();
}
/*

On Mailing List Form submission

*/
function validateMailingListForm($form) {
  $form.validate({
    submitHandler: async function (form, e) {
      e.preventDefault();
      $form.find('.form-message').empty();

      $form.addClass('is-submitting');
      $form.find('input, button[type="submit"]').addClass('is-disabled').prop('disabled', true);
      $form.find('.spinner').addClass('is-visible');

      const [err, resp] = await to(addToMailchimpList($form));

      if (resp.code === 'rest_cookie_invalid_nonce') {
        return showError($form, resp);
      }

      if (resp.error) {
        return showError($form, resp);
      }

      showSuccess($form, resp);
    },
    highlight: function (element, errorClass, validClass) {
      jQuery(element).parent().removeClass('form-valid');
      jQuery('.form-error').addClass('is-visible');
      jQuery('.form-success').removeClass('is-visible');
    },
    unhighlight: function (element, errorClass, validClass) {
      jQuery(element).find('.form-error').removeClass('is-visible');
    },
    success: function (label) {
      $form.find('.form-error').removeClass('is-visible');

      jQuery(label).parent().addClass('form-valid');
    },
    errorPlacement: function (error, element) {
      error.appendTo(jQuery(element).closest('.mailinglist-form').find('.form-error'));
    },
  });
}

function initMailinglist($form) {
  validateMailingListForm($form);
}

export { initMailinglist };
