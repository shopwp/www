/*

Get Checkout Step

*/
function getCheckoutStep() {
  return localStorage.getItem('wps-checkout-step');
}


/*

Set Checkout Step

*/
function setCheckoutStep(step) {
  localStorage.setItem('wps-checkout-step', step);
}


export {
  setCheckoutStep,
  getCheckoutStep,
}
