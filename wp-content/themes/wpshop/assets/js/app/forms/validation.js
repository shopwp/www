import debounce from 'lodash/debounce';


/*

updateLoginHeight

*/
function updateLoginHeight($element, $) {

  if($element.attr('name') === 'edd_email') {
    var height = $('#edd_checkout_user_info').height();
    $('.wps-checkout-login-container').height(height);
  }

}


/*

Toggle Half Width Error Classes

*/
function toggleHalfWidthErrorClasses($element) {

  if( getInputWidth($element) === 48 ) {

    if( $element.parent().css('float') == 'right' ) {

      $element.parent().next().addClass('is-invalid-wrap');

    } else if( $element.parent().css('float') == 'left' ) {

      $element.parent().next().next().addClass('is-invalid-wrap');

    }

  }

}


/*

onInputAddError

*/
function onInputAddError(element) {

  toggleHalfWidthErrorClasses( jQuery(element) );

  removeValidIcon(jQuery(element));
  removeValidClass(jQuery(element));

  // updateLoginHeight($(element), $);
  // validate.showErrors();

}


/*

enableValidCreditCardExpDates

*/
function enableValidCreditCardExpDates($element) {

  if( $element.attr('name') === 'cardExpYear' || $element.attr('name') === 'cardExpYear') {
    jQuery('#card_exp_year').addClass('valid');
    jQuery('#card_exp_month').addClass('valid');
  }

}


/*

disableValidCreditCardExpDates

*/
function disableValidCreditCardExpDates($element) {

  // if( $element.attr('name') === 'cardExpYear' || $element.attr('name') === 'cardExpYear') {
  //   $('#card_exp_year').addClass('invalid');
  //   $('#card_exp_month').addClass('valid');
  // }

}


/*

Get Input Width

*/
function getInputWidth( $element ) {
  var elementWidth = $element.outerWidth();
  var parentFormWidth = $element.closest('form').outerWidth();
  var result = (elementWidth * 100) / parentFormWidth;

  return Math.round(result);

}


/*

Is Read Only

*/
function isReadOnly(element) {

  var attr = jQuery(element).attr('readonly');

  if (typeof attr !== typeof undefined && attr !== false) {

    return true;

  } else {

    return false;

  }

}



/*

Insert valid form icon

*/
function insertValidIcon($element) {
  $element.parent().append('<span class="is-valid"></span>');
}


/*

Insert valid form icon

*/
function removeValidIcon($element) {
  $element.parent().find('.is-valid').remove();
}


/*

Remove valid class

*/
function removeValidClass($element) {
  $element.removeClass('valid');
}


/*

Insert valid form icon

*/
function addValidInputClass($element) {
  $element.addClass('valid');
}


/*

Insert valid form icon

*/
function hideInputError($element) {
  $element.parent().find('label.error').hide();
}


/*

onInputRemoveError

*/
var onInputRemoveError = debounce(function(element) {

  if( !isReadOnly(element) ) {

    if(jQuery(element).attr('name') === 'edd_email') {

      if(!jQuery(element).parent().find('label.error').is(':visible') && !jQuery(element).hasClass('error')) {

        removeValidIcon(jQuery(element));
        hideInputError(jQuery(element));
        insertValidIcon(jQuery(element));
        addValidInputClass(jQuery(element));

      } else {
        // console.log('NOT valid');

      }

    } else {

      if(!jQuery(element).parent().find('label.error').is(':visible') && !jQuery(element).hasClass('error')) {

        enableValidCreditCardExpDates( jQuery(element) );
        removeValidIcon(jQuery(element));
        hideInputError(jQuery(element));
        insertValidIcon(jQuery(element));
        addValidInputClass(jQuery(element));

      } else {
        // console.log('NOT valid');
        // $(element)[0].setCustomValidity('Invalid');
      }

    }

  }

}, 200);


/*

ifAriaValuesPass

*/
function ifAriaValuesPass() {

  var $requiredFields = jQuery("#edd_purchase_form input.required, #edd_purchase_form select.required");
  var valid = true;

  jQuery.each($requiredFields, function() {

    if ( jQuery(this).attr('aria-invalid') === 'true') {
      valid = false;
      return;

    } else {

    }

  });

  return valid;

}


/*

Find Inputs Without Values

*/
function findInputsWithoutValues($inputs) {
  return $inputs.filter(function() {
    return !this.value;
  });
}


/*

hasRemainingRequiredFields

*/
function hasRemainingRequiredFields($) {

  var $requiredFields = $("#edd_purchase_form input.required, #edd_purchase_form select.required");
  var $remainingRequiredFields = findInputsWithoutValues($requiredFields);

  return $remainingRequiredFields.length ? true : false;

}


/*

getRemainingRequiredFields

*/
function getRemainingRequiredFields($) {

  var $requiredFields = $("#edd_purchase_form input.required, #edd_purchase_form select.required");
  var $remainingRequiredFields = findInputsWithoutValues($requiredFields);

  return $remainingRequiredFields;

}


/*

disableFormSubmit

*/
function disableFormSubmit(validate, $form, $) {

  if(validate.lastActive !== undefined) {
    if( !submitDefaultEnabled(validate.lastActive, $) ) {
      var $submitButton = $form.find('input[type="submit"]');
      $submitButton.prop('disabled', true);
    }
  }

}


/*

enableFormSubmit

*/
function enableFormSubmit($form) {
  var $submitButton = $form.find('input[type="submit"]');

  $submitButton.prop('disabled', false);

}


/*

showInvalidFormNotice

*/
function showInvalidFormNotice($form) {

  if($form.next().length > 0) {
    $form.next('.form-note').show();

  } else {
    $form.after('<p class="form-note">Please fill out remaining required fields</p>');
  }

}


/*

hideFormNote

*/
function hideFormNote($form) {
  $form.next('.form-note').hide();
}


/*

removeInvalidFormNotes

*/
function removeInvalidFormNotes($form) {
  $form.next('.form-note').nextAll('.form-note-invalid-field').remove();
  removeGeneratedErrors();
}



/*

Remove generated (page reload) form errors

*/
function removeGeneratedErrors() {
  jQuery('.edd_errors').remove();
}



/*

Input Not Submit Disabled

*/
function inputNotSubmitDisabled() {

  return [
    'edd_user_login',
    'edd_user_pass'
  ];

}


/*

formContainsErrors

*/
function formContainsErrors(validate) {
  return validate.errorList.length;
}


/*

containsLoginFormInputs

*/
function submitDefaultEnabled(input, $) {
  return inputNotSubmitDisabled().includes( $(input).attr('name') );
}


/*

checkFields
Runs everytime jQuery validate fires (keyup, blur, change) debounced by 300ms

*/
function checkFields(validate, $form, $) {

  if( formContainsErrors(validate) ) {

    // disableFormSubmit(validate, $form, $);
    showInvalidFormNotice($form);
    toggleInvalidFormNotes($form, $);

  } else {

    if( hasRemainingRequiredFields($) ) {
      // disableFormSubmit(validate, $form, $);
      toggleInvalidFormNotes($form, $);

      //
      // Hacky way to submit form with dynamically created elements
      // TODO: FIX
      //
      if( getRemainingRequiredFields($).length <= 6 ) {
        enableFormSubmit($form);
      }


    } else {

      if( ifAriaValuesPass() ) {

        enableFormSubmit($form);
        hideFormNote($form);
        removeInvalidFormNotes($form);

      } else {
        toggleInvalidFormNotes($form, $);

      }

    }

  }

}


/*

checkFormValiditity

*/
function checkFormValiditity(validate, $form, $) {

  var $formInputs = $form.find('input, select');

  $formInputs.on('input change keyup', debounce(function() {
    checkFields(validate, $form, $);
  }, 300, {
    'leading': true,
    'trailing': true
  }));

}


/*

getInvalidRequiredFields

*/
function getInvalidRequiredFields($form) {
  return $form.find('input.required[aria-invalid="true"], select.required[aria-invalid="true"]');
}


/*

Filter Invalid Notes

*/
function filterInvalidNotes($currentlyInvalids, listOfErrorNames) {

  $currentlyInvalids.filter(function(index) {

    if( listOfErrorNames.includes( jQuery(this).data('name') ) ) {
      return listOfErrorNames.includes( jQuery(this).data('name') );

    } else {
      jQuery(this).remove();

    }

  });

}


/*

toggleInvalidFormNotes

*/
function toggleInvalidFormNotes($form, $) {

  var $newInvalids = getInvalidRequiredFields($form),
      $currentInvalids = $('#edd_checkout_form_wrap .form-note-invalid-field'),
      listOfErrorNames = [];

  $.each($newInvalids, function() {
    listOfErrorNames.push(this.name);
  });

  $.each($newInvalids, function() {

    if( !$('#edd_checkout_form_wrap .form-note-invalid-field[data-name="' + $(this).attr('name') + '"]').length ) {

      if($(this).attr('placeholder') === undefined) {

        if($(this).attr('id') === 'card_exp_month' || $(this).attr('id') === 'card_exp_year') {
          $('#edd_purchase_form + .form-note').after('<p class="form-note-invalid-field" data-name="' + $(this).attr('name') + '">Credit card expiration date</p>');

        } else {
          $('#edd_purchase_form + .form-note').after('<p class="form-note-invalid-field" data-name="' + $(this).attr('name') + '">' + $(this).attr('name') + '</p>');
        }

      } else {
        $('#edd_purchase_form + .form-note').after('<p class="form-note-invalid-field" data-name="' + $(this).attr('name') + '">' + $(this).attr('placeholder') + '</p>');

      }

    } else {

    }

  });

  filterInvalidNotes($currentInvalids, listOfErrorNames);

}


export {
  onInputRemoveError,
  onInputAddError,
  ifAriaValuesPass,
  checkFormValiditity,
  getInvalidRequiredFields,
  toggleInvalidFormNotes,
  insertValidIcon,
  addValidInputClass,
  removeValidIcon,
  hideInputError,
  removeValidClass
}
