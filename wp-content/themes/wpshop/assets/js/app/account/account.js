import {
  onInputAddError,
  addValidInputClass,
  insertValidIcon,
  removeValidIcon,
  hideInputError,
  removeValidClass
} from '../forms/validation';

import {
  reduceFormData,
  enableForm,
  disableForm,
  insertMessage,
  clearFormFields
} from '../utils/utils';


import {
  getAccountCat,
  updateAccountProfile,
  updateAccountPassword
} from '../ws/ws';


/*

On account cat click

*/
function onAccountCatClick($) {

  window.paceOptions = {
    ajax: {
      trackMethods: ["GET", "POST"]
    }
  };

  $('.account-cat').on('click', function(e) {

    var $element = $(this);

    Pace.track(async function() {

      var stuff = await getAccountCat( $element.data('account-cat') );

      console.log("stuffstuffstuff: ", stuff);
      $('.content').html($(stuff));
      // console.log('account-cat: ', $(this).data('account-cat'));

    });

  });
}

//
// function onAccountDownloads() {
//
// }
//
// function onAccountLicenses() {
//
// }
//
// function onAccountOrders() {
//
// }
//
// function onAccountProfile() {
//
// }

//
// function onAccountCatLinks() {
//   onAccountBilling();
//   onAccountDownloads();
//   onAccountLicenses();
//   onAccountOrders();
//   onAccountProfile();
// }







function onProfileChange($) {

  $("#form-account-profile-general").submit(function(e) {
    e.preventDefault();

  }).validate({
    rules: {
      wps_customer_email: {
        email: true,
        remote: {
          url: "/wp/wp-admin/admin-ajax.php",
          type: "post",
          data: {
            action: 'wps_check_existing_username',
            email: function() {
              return $("#wps_customer_email").val();
            }
          }
        },
      }
    },
    messages: {
      wps_customer_email: {
        email: "Please enter a valid email",
        remote: "Sorry but it looks like another user already has that email. Please choose a different one."
      }
    },
    highlight: onInputAddError,
    unhighlight: function(element) {

      removeValidIcon($(element));
      hideInputError($(element));
      addValidInputClass($(element));
      insertValidIcon($(element));

    },
    submitHandler: async function(form) {

      var $form = $(form);

      disableForm($form);

      console.log('before', reduceFormData($form));
      var emailUpdated = await updateAccountProfile( reduceFormData($form) );
      console.log('afterrrr', emailUpdated);

      enableForm($form);

      if(emailUpdated.email && emailUpdated.name) {
        // insertMessage('Successfully updated email and name', 'success');
        window.location.href = '/login?email-name-change=true';

      } else if(emailUpdated.email && !emailUpdated.name) {
        // insertMessage('Successfully updated email', 'success');
        window.location.href = '/login?email-change=true';

      } else if(!emailUpdated.email && emailUpdated.name) {
        insertMessage('Successfully updated name', 'success');

        removeValidIcon($form.find('input'));
        removeValidClass($form.find('input'));

      } else {
        insertMessage('Sorry we couldn\'t update your profile. Please try again.', 'error');

      }


    }

  });

}


/*

On Password Change

*/
function onPasswordChange($) {

  $("#form-account-profile-password").submit(function(e) {

    e.preventDefault();

  }).validate({
    rules: {
      wps_customer_password_current: {
        required: true
      },
      wps_customer_password_new: {
        required: true
      },
      wps_customer_password_new_confirm: {
        required: true,
        equalTo: "#form-input-password"
      }
    },
    messages: {
      password: {
        required: "New passwords must match"
      }
    },
    highlight: onInputAddError,
    unhighlight: function(element) {

      removeValidIcon($(element));
      hideInputError($(element));
      addValidInputClass($(element));
      insertValidIcon($(element));

    },
    submitHandler: async function(form) {
      var $form = $(form);

      disableForm($form);

      console.log('before', reduceFormData($form));
      var passUpdated = await updateAccountPassword( reduceFormData($form) );
      console.log('after passUpdated', passUpdated);

      if (!passUpdated) {
        insertMessage('Error updating password, please try again', 'error');

      } else {

        insertMessage('Successfully updated password', 'success');

      }

      clearFormFields($form);
      enableForm($form);


    }

  });

}


function showUpgrades($) {
  $('.account-view-upgrades').on('click', function(e) {
    e.preventDefault();
    console.log('lieeee');

    $('#edd_sl_license_upgrades').toggleClass('is-hidden');

  });
}



/*

Init Account

*/
function initAccount($) {
  onAccountCatClick($);
  onProfileChange($);
  onPasswordChange($);
  showUpgrades($);

  var clipboard = new Clipboard('.btn-copy');

  var $inlineKeyNotice = jQuery('#license-key + .notice-inline');

  clipboard.on('success', function(e) {
    console.info('Action:', e.action);
    console.info('Text:', e.text);
    console.info('Trigger:', e.trigger);

    $inlineKeyNotice.addClass('is-notifying');

    setTimeout(function() {
      $inlineKeyNotice.removeClass('is-notifying');
      e.clearSelection();
    }, 2000);

    jQuery(e.trigger).focus().select();


  });

  clipboard.on('error', function(e) {
    console.error('Action:', e.action);
    console.error('Trigger:', e.trigger);
  });



}

export { initAccount }
