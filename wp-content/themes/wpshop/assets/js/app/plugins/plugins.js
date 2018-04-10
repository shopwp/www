import {
  getUserByEmail
} from '../ws/ws';


function initPlugins($) {

  /*

  Animate

  */
  $.fn.extend({
    animateCss: function (animationName, callback) {
      var animationEnd = 'webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend';
      this.addClass('animated ' + animationName).one(animationEnd, callback);
    }
  });

  $.validator.setDefaults({
    ignore: ':hidden, [readonly=readonly]'
  });

  /*

  Get URL params

  */
  $.urlParam = function(name){
    var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);

    if(results !== null) {
      return (results[1] == "true") || 0;

    } else {
      return false;

    }

  }


  /*

  jQuery Validate: Check for expired credit card date

  */
  $.validator.addMethod('CCExp', function(value, element, params) {

    var $month = $(element).parent().find("#card_exp_month"),
        $year = $(element).parent().find("#card_exp_year"),
        minMonth = new Date().getMonth() + 1,
        minYear = new Date().getFullYear(),
        month = parseInt($month.val(), 10),
        year = parseInt($year.val(), 10);

    if( (!month || !year || year > minYear || (year === minYear && month >= minMonth)) ) {

      $month.attr("aria-invalid", false).addClass('valid');
      $year.attr("aria-invalid", false).addClass('valid');
      $('#card_exp_year-error, #card_exp_month-error').hide();

      return true;

    } else {

      $month.attr("aria-invalid", true).removeClass('valid');
      $year.attr("aria-invalid", true).removeClass('valid');

      return false;

    }

  }, 'Your Credit Card Expiration date is invalid.');


  /*

  jQuery Validate: Check for correct cvc

  */
  $.validator.addMethod('cardcvc', function(value, element, params) {

    return regexpr.test(value);

  }, 'Must be a number between 3-4 digits long');


  /*

  Polyfill endsWith() used to check for myshopify.com domain ending during auth

  */
  if (!String.prototype.endsWith) {

    String.prototype.endsWith = function(searchStr, Position) {

      if (!(Position < this.length)) {
        Position = this.length;

      } else {
        Position |= 0;
      }

      return this.substr(Position - searchStr.length, searchStr.length) === searchStr;

    };

  }


}

export {
  initPlugins
}
