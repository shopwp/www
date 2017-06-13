import {
  disable,
  enable,
  hideLoader,
  showLoader,
  disableForm,
  enableForm,
  hasValue,
  getUrlParams
} from '../utils/utils';

import {
  initCheckoutSteps
} from './steps';

import {
  getForgotPassForm
} from '../ws/ws';

import {
  rulesCheckout
} from '../forms/rules';

import {
  onInputRemoveError,
  onInputAddError,
  ifAriaValuesPass,
  checkFormValiditity,
  getInvalidRequiredFields
} from '../forms/validation';


/*

On account cat click

*/
function onAskExisting($) {

  var $loginForm = $("#edd_checkout_login_register"),
      $registerForm = $("#edd_checkout_user_info"),
      $submitContainer = $("#edd_purchase_submit");

  $('#edd-user-login-submit .button').val('Login and checkout');
  // $registerForm.addClass('animated bounceInTop');

  $("#card_number").attr('type', 'text');

  // var heigtheight = $('#edd_checkout_user_info').height();

  // TODO: Put somewhere that makes sense
  // $registerForm.addClass('animated zoomIn');
  // $('.wps-checkout-login-container').height(heigtheight);

  $('.component-ask-existing').on('click', '.button', async function(e) {

    e.preventDefault();

    var $element = $(this);
    var $parent = $element.parent();

    $loginForm.removeClass('animated bounceInTop');
    $registerForm.removeClass('animated bounceInTop');
    $submitContainer.removeClass('is-visible');

    $parent.children().removeClass('is-active is-disabled');
    $element.addClass('is-active');
    $parent.find('.button:not(.is-active)').addClass('is-disabled');

    if($element.data('checkout-path') === 'login') {
      $loginForm.addClass('animated bounceInTop');
      $submitContainer.removeClass('is-visible');

    } else {
      $registerForm.addClass('animated bounceInTop');
      $submitContainer.addClass('is-visible');

    }

  });
}


/*

Save Checkout State

*/
function saveCheckoutState($) {

  var $ccFields = $('#edd_cc_fields'),
      $ccAddress = $('#edd_cc_address').prop('outerHTML'),
      $ccSubmit = $('#edd_purchase_submit').prop('outerHTML');

  $ccFields.find("#card_number").val('');
  $ccFields = $ccFields.prop('outerHTML');

  localStorage.setItem('wps-checkout-form', $ccFields + $ccAddress + $ccSubmit);

}


/*

Insert Saved Checkout Form

*/
function insertSavedCheckoutForm($) {
  $('.wps-checkout-login-container').after($(localStorage.getItem('wps-checkout-form')));
}


/*

Removing Billing Inputs

*/
function removeBillingInputs($) {

  $('#edd_cc_fields').remove();
  $('#edd_cc_address').remove();
  $('#edd_purchase_submit').remove();

}


/*

On Login Link

*/
function onCheckoutLogin($) {

  var $loginForm = $("#edd_checkout_login_register"),
      $registerForm = $("#edd_checkout_user_info");

  $('#edd_checkout_user_info').on('click', '.wps-welcome-link', function(e) {

    e.preventDefault();

    $registerForm.hide();
    $loginForm.show();

    $('#edd-user-login-submit input').prop('disabled', false);
    removeBillingInputs();

  });

}


/*

Reset Login Form UI

*/
function resetLoginForm($) {
  $('#edd_purchase_form')[0].reset();
  $('#edd_purchase_form').find('input').removeClass('valid');
  $('#edd_purchase_form').find('.is-valid').remove();
  $('#edd_purchase_form').find('.edd_errors').remove();
}


/*

On Login Link

*/
function onCheckoutRegister($) {

  var $loginForm = $("#edd_checkout_login_register"),
      $registerForm = $("#edd_checkout_user_info");

  $('#edd_login_fields').on('click', '.wps-welcome-link', function(e) {

    e.preventDefault();

    $registerForm.show();
    $loginForm.hide();

    insertSavedCheckoutForm($);
    // setScrollScene();
    resetLoginForm($);

  });

}


/*

Setting the Scroll Magic sticky nav

*/
function setScrollScene($) {

  var controller = new ScrollMagic.Controller();

  // Scene1 Handler
  var scene1 = new ScrollMagic.Scene({
    duration: 0,
    triggerElement: '#edd_cc_fields',
    triggerHook: 0
  })
  .on('start', function () {

  })
  .on('enter', function () {
    $("#edd_checkout_cart_form_header").addClass('is-stuck');
  })
  .on('leave', function () {
    $("#edd_checkout_cart_form_header").removeClass('is-stuck');
  });

  // Add Scenes to ScrollMagic Controller
  controller.addScene([
    scene1
  ]);

  detectDestroy(controller, scene1, $);

}


/*

Scroll Magic Scene Destroy Handler

*/
function detectDestroy(controller, scene, $) {

  $('.wps-welcome-link').on('click', function(e) {
    e.preventDefault();

    var $activeContainer = $(this).closest('.animated');

    if($activeContainer.attr('id') === 'edd_checkout_login_register') {

    } else {
      controller.destroy();
      scene.destroy();
    }

  });

}


/*

Modify all default form attributes here

*/
function initFormState($) {
  $('.is-registered-and-purchasing #edd_checkout_user_info input').prop('readonly', true);
  $('#card_exp_month, #card_exp_year').attr('name', 'cardExpYear');
  $('#card_number').attr('name', 'edd_credit_card');
  $('#card_cvc').attr('name', 'edd_cvc');
  $('#card_name').attr('name', 'edd_card_name');

  // $('#edd-purchase-button').prop('disabled', true);

  // allCheckoutFieldsValid();

}


/*

Is Form Valid

*/
function isFormValid(valid) {

  if(valid) {
    $('#edd-purchase-button').prop('disabled', false);

  } else {
    $('#edd-purchase-button').prop('disabled', true);

  }

}


/*

Validate Checkout Form

*/
function validateCheckoutForm($) {

  var $form = $("#edd_purchase_form"),
      validate = $form.validate(rulesCheckout());

  checkFormValiditity(validate, $form, $);

}


/*

Toggle Checkout Form State During Submit

*/
function toggleCheckoutFormStateDuringSubmit($) {

  var $errorsContainer = $('#edd-stripe-payment-errors'),
      $checkoutSubmitButton = $("#edd-purchase-button");

  $checkoutSubmitButton.on('click', function() {

    $errorsContainer.empty();

    var $button = $(this);

    var loop = setInterval(function checkFormState() {
      var $errorContainer = $button.closest('form').find("#edd-stripe-payment-errors .edd_errors");


      var errortest = $button.closest('form').find("#edd-email-error").is(':visible');

      if ($errorContainer.length || errortest) {
        enableForm( $button.closest('form') );
        hideLoader( $button.closest('form') );;
        clearInterval(loop);

      } else {
        disableForm( $button.closest('form') );
        showLoader( $button.closest('form') );
      }

    }, 200);

  });
}


/*

On account cat click

*/
function onForgotPass($) {

  $('.ajax-forgot-pass').on('click', async function(e) {

    e.preventDefault();
    var $element = $(this);
    var $parentForm = $element.closest('form');
    var $forgotPassForm = $("#form-forgot-pass");

    $forgotPassForm.show();
    $parentForm.hide();

  });

}


/*

On account cat click

*/
function onForgotPassBack($) {

  $('#form-forgot-pass .wps-welcome-link').on('click', function(e) {

    e.preventDefault();

    var $element = $(this);
    var $parentForm = $element.closest('form');

    $parentForm.prev().show();
    $parentForm.hide();

  });

}




/*

duplicateCartForHeader

*/
function duplicateCartForHeader($) {

  var $wrapper = $("#edd_checkout_cart_form");

  if($wrapper.length) {
    $wrapper.after( $wrapper.clone().attr('id', 'edd_checkout_cart_form_header') );
    setScrollScene($);
  }

}


/*

addValidUponLogin

*/
function addValidUponLogin($) {

  if( $('body').hasClass('logged-in') ) {

    $('input:required, select:required').each(function() {

      if( $(this).val() ) {

        if( !$(this).hasClass('valid') ) {
          $(this).addClass('valid');
          $(this).parent().append('<span class="is-valid"></span>');
        }

      }

    });

  }

}






function checkForExistingErrors($) {
  if( $('.edd_errors').length ) {
    return true;

  } else {
    return false;

  }
}



function insertCheckoutErrors($) {
  if( checkForExistingErrors($) ) {

    var $errorClone = $('.edd_errors').clone();
    $('.main').prepend($errorClone);

  }
}

/*

On Country Change

*/
function onCountryChange($) {
  $('#billing_country').on('change', function() {
    removeValidFromStateProvince($);
  });
}


/*

Remove valid from state / province field
Used when country changes

*/
function removeValidFromStateProvince($) {
  $('#edd-card-state-wrap .is-valid').remove();
  $('#card_state').removeClass('valid');

}


function onStateChange($) {

  $('#card_state, .card-state').on('change', function() {

  });

}


/*

Simple scroll to error on checkout error

*/
function onCheckoutError($) {

  if(getUrlParams(location.search)['payment-mode'] === 'stripe') {
    $('html, body').animate({scrollTop:$(document).height()}, 1500);
  }

}


/*

Init Checkout

*/
function initCheckout($) {

  onAskExisting($);
  onCheckoutLogin($);
  onCheckoutRegister($);
  initCheckoutSteps($);

  initFormState($);
  validateCheckoutForm($);
  toggleCheckoutFormStateDuringSubmit($);
  saveCheckoutState($);
  onForgotPass($);
  duplicateCartForHeader($);
  // onForgotPassBack($);
  addValidUponLogin($);

  // checkForExistingErrors($);
  insertCheckoutErrors($);

  onCountryChange($);

  // onCheckoutError($);

}

export { initCheckout }
