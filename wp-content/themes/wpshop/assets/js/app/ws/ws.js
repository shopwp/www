/*

Get docs data
Returns: Promise

*/
function getDoc(docId) {
  var options = {
    method: 'POST',
    url: '/wp-json/wpshop/v1/docs/get',
    dataType: 'HTML',
    data: {
      docId: docId
    }
  }

  return jQuery.ajax(options)
}

/*

Get docs data
Returns: Promise

*/
function getAccountCat(catSlug) {
  var options = {
    method: 'POST',
    url: '/wp/wp-admin/admin-ajax.php',
    dataType: 'HTML',
    data: {
      action: 'wps_get_account_cat',
      cat: catSlug
    }
  }

  return jQuery.ajax(options)
}

/*

Get docs data
Returns: Promise

*/
function getForgotPassForm() {
  var options = {
    method: 'GET',
    url: '/wp/wp-admin/admin-ajax.php',
    dataType: 'HTML',
    data: {
      action: 'wps_get_forgot_pass_form'
    }
  }

  return jQuery.ajax(options)
}

/*

MC: Get list by ID
Returns promise

*/
function getMailchimpListById($form) {
  var emailVal = $form.find('.mailinglist-email').val(),
    emailnonce = $form.find('#_wpnonce').val(),
    type = $form.data('type')

  return jQuery.ajax({
    type: 'POST',
    url: '/wp-admin/admin-ajax.php',
    dataType: 'json',
    data: {
      action: 'mailinglist_signup',
      email: emailVal,
      nonce: emailnonce,
      type: type
    }
  })
}

/*

Check for existing user by email

*/
function getUserByEmail(email) {
  var options = {
    type: 'POST',
    url: '/wp/wp-admin/admin-ajax.php',
    dataType: 'json',
    data: {
      action: 'wps_check_existing_username',
      email: email
    }
  }

  return jQuery.ajax(options)
}

/*

Account - Update profile

*/
function updateAccountProfile(data) {
  var options = {
    type: 'POST',
    url: '/wp/wp-admin/admin-ajax.php',
    dataType: 'json',
    data: {
      action: 'wps_account_update_profile',
      data: data
    }
  }

  return jQuery.ajax(options)
}

/*

Account - Update profile

*/
function updateAccountPassword(data) {
  var options = {
    type: 'POST',
    url: '/wp/wp-admin/admin-ajax.php',
    dataType: 'json',
    data: {
      action: 'wps_change_customer_password',
      data: data
    }
  }

  return jQuery.ajax(options)
}

/*

Account - Start Reset Password Process

*/
function forgotPassword(data) {
  var options = {
    type: 'POST',
    url: '/wp/wp-admin/admin-ajax.php',
    dataType: 'json',
    data: {
      action: 'wps_account_forgot_password',
      data: data
    }
  }

  return jQuery.ajax(options)
}

/*

Account - Fnish Reset Password Process

*/
function resetPassword(data) {
  var options = {
    type: 'POST',
    url: '/wp/wp-admin/admin-ajax.php',
    dataType: 'json',
    data: {
      action: 'wps_account_reset_password',
      data: data
    }
  }

  return jQuery.ajax(options)
}

export {
  getDoc,
  getAccountCat,
  getForgotPassForm,
  getMailchimpListById,
  getUserByEmail,
  updateAccountProfile,
  updateAccountPassword,
  forgotPassword,
  resetPassword
}
