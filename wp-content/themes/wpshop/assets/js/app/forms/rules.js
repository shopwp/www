import {
  onInputAddError,
  onInputRemoveError
} from './validation';

/*

rulesCheckout

*/
function rulesCheckout() {
  return {
    rules: {
      cardExpYear: {
        CCExp: {
          month: '#card_exp_month'
        }
      },
      edd_card_name: {
        required: true
      },
      card_address: {
        required: true
      },
      card_city: {
        required: true
      },
      card_zip: {
        required: true
      },
      billing_country: {
        required: true
      },
      card_state: {
        required: true
      },
      edd_credit_card: {
        creditcard: true,
        required: true
      },
      edd_email: {
        remote: {
          url: "/wp/wp-admin/admin-ajax.php",
          type: "post",
          data: {
            action: 'wps_check_existing_username',
            email: function() {
              return $("#edd-email").val();
            }
          }
        },
        email: true,
        required: true
      },
      edd_cvc: {
        required: true,
        pattern: /^[0-9]{3,4}$/
      },
      edd_first: {
        required: true
      }
    },
    messages: {
      cardExpYear: "Please choose a valid date",
      CCExp: {
        month: 'Valid month required'
      },
      edd_email: {
        email: "Please enter a valid email address",
        required: "Email is required",
        remote: 'That email is already taken. Do you want to <a href="/login?redirect_to=checkout" class="wps-welcome-link">login</a> instead?'
      },
      edd_credit_card: {
        creditcard: "Please enter a valid credit card",
        required: "Credit card is required"
      },
      edd_first: {
        required: "First name is required"
      },
      edd_cvc: {
        required: "CVC is required",
        pattern: "Must be a number between 3-4 digits long"
      },
      edd_card_name: {
        required: "Name is required"
      },
      card_address: {
        required: "Billing Address is required"
      },
      card_city: {
        required: "Billing City is required"
      },
      card_zip: {
        required: "Billing Zip is required"
      },
      billing_country: {
        required: "Country is required"
      },
      card_state: {
        required: "State / province is required"
      }
    },
    highlight: onInputAddError,
    unhighlight: onInputRemoveError

  }
}

export {
  rulesCheckout
}
