import {
  setCheckoutStep,
  getCheckoutStep
} from '../ws/localstorage';

/*

Set Step

*/
function getStepOnInit($) {

  if(getCheckoutStep() === null) {
    setCheckoutStep(1);
    return getCheckoutStep();

  } else {
    return getCheckoutStep();

  }

}


/*

Set Step

*/
function setActiveStep($) {

  var step = getCheckoutStep();
  var selector = '.component-steps .step:nth-child(' + step + ')';

  $(selector).removeClass('is-completed is-inactive').addClass('is-active');

}


/*

Set Completed Steps

*/
function findCompletedSteps($) {

  var currentStep = getCheckoutStep();
  var totalSteps = $('.component-steps .step').length;

  if(currentStep > 1) {
    var completedSteps = totalSteps - currentStep;

    setCompletedSteps(completedSteps);

  }

}


/*

Sets the completed steps

*/
function setCompletedSteps(completedSteps) {

  var selector = '.component-steps .step:nth-child(-n+' + completedSteps + ')';
  $(selector).removeClass('is-inactive is-active').addClass('is-completed');

}


/*

Set Inactive Steps

*/
function setInactiveSteps($) {}


/*

Init Account

*/
function initCheckoutSteps($) {

  // if($('.wrap').hasClass('.is-registered-and-purchasing')) {
  //   setCheckoutStep(2);
  // }

  getStepOnInit($);
  findCompletedSteps($);
  setActiveStep($);

}

export { initCheckoutSteps }
