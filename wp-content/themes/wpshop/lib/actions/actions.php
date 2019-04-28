<?php

/*

wps_login_check

*/
function wps_login_check()
{
   if (is_user_logged_in() && is_page('login')) {
      wp_redirect('/account');
      exit();
   }

   if (is_user_logged_in() && is_page('forgot-password')) {
      wp_redirect('/account');
      exit();
   }
}

add_action('wp', 'wps_login_check');

/*

wps_before_register_login

*/
function wps_before_register_login()
{
   echo '<div class="wps-checkout-login-container">';
}

// add_action('edd_purchase_form_before_register_login', 'wps_before_register_login', 999);

/*

wps_before_register_login

*/
function wps_after_checkout_cart()
{
   echo '<div class="wps-checkout-login-spacer"></div>';
}

// add_action('edd_after_checkout_cart', 'wps_after_checkout_cart', 999);

/*

wps_before_register_login

*/
function wps_after_user_info()
{
   echo '</div>';
}

// add_action('edd_purchase_form_after_user_info', 'wps_after_user_info', 999);

/*

wps_before_register_login

*/
function wps_register_text()
{
   if (is_user_logged_in()) {
      global $current_user;

      $firstName = $current_user->user_firstname;
      echo 'Checking out as, ' . $firstName . '.';
   } else {
      echo 'Account Info <p>(An email will be sent with your login credentials)</p>';
   }
}

// add_action('edd_checkout_personal_info_text', 'wps_register_text');

/*

wps_before_register_login

*/
function wps_login_text()
{
   echo '<legend>Login and checkout</legend><span class="wps-welcome"><a href="" class="wps-welcome-link">Need an account? </a></span>';
}

// add_action('edd_checkout_login_fields_before', 'wps_login_text');

/*

wps_before_register_login

*/
function wps_login_text_after()
{
   if (is_user_logged_in()) {
      global $current_user;

      $firstName = $current_user->user_firstname;
      $logoutUrl = wp_logout_url('/checkout');

      echo '<span class="wps-welcome">Not ' . $firstName . '? <a href="' . $logoutUrl . '">Logout</a></span>';
   } else {
      echo '<span class="wps-welcome"><a href="" class="wps-welcome-link">Have an existing account? </a></span>';
   }
}

// add_action('edd_purchase_form_before_email', 'wps_login_text_after');

/*

wps_before_register_login

*/
function wps_forgot_pass()
{
   echo '<a href="/reset-password">Forgot your password?</a>';
}

// add_action('edd_checkout_login_fields_after', 'wps_forgot_pass');

/*

wps_before_register_login

*/
function wps_after_purchase_form()
{
   echo '<p class="form-note">Please fill out all required fields</p>';
}

// add_action('edd_after_purchase_form', 'wps_after_purchase_form');

/*

wps_before_register_login

*/
function wps_insert_spinner_checkout_form()
{
   echo '<div class="spinner"></div>';
}

// add_action('edd_checkout_form_top', 'wps_insert_spinner_checkout_form');

/*

wps_before_register_login

*/
function wps_edd_on_complete_purchase($payment_id)
{
   // Basic payment meta
   $payment_meta = edd_get_payment_meta($payment_id);

   // Cart details
   $cart_items = edd_get_payment_meta_cart_details($payment_id);

   // do something with payment data here
}

// add_action( 'edd_complete_purchase', 'wps_edd_on_complete_purchase' );

/*

wps_login_before

*/
function wps_login_before()
{
   echo '<p>Have an existing account? Login here</p>';
}

// add_action('edd_checkout_login_fields_before', 'wps_login_before');

/*

wps_purchase_before

*/
function wps_purchase_before()
{
   // echo "<p>Have an existing account? Login here</p>";
   if (is_user_logged_in()) {
      global $current_user;

      $firstName = $current_user->user_firstname;
      $lastName = $current_user->user_lastname;

      echo '<div class="msg msg-notice animated fadeInDown">Hey, ' . $firstName . '. Lets get you checked out.</div>';

      // echo 'Hey, ' . $firstName . '. Lets get you checked out.';
   } else {
      echo 'Welcome, visitor!';
   }
}

// add_action('edd_checkout_form_top', 'wps_purchase_before');

/*

Changing the default Wordpress login logo

*/
function wps_custom_login_logo()
{
   echo '<style type="text/css">
    .login h1 a {
      background-image: url(' .
      get_stylesheet_directory_uri() .
      '/assets/prod/imgs/logo-mark.svg) !important;
      margin-bottom: 0;
      line-height: 3;
      height: 120px;
      width: 100%;
      background-size: contain;
    }

    .login #login_error,
    .login .message {
      margin-top: 2em;
    }

  </style>';
}

add_action('login_head', 'wps_custom_login_logo');

/*

wps_login_redirect

*/
function wps_login_redirect()
{
   if (!is_user_logged_in() && is_page('account')) {
      wp_redirect('/login');
      exit();
   }
}

add_action('template_redirect', 'wps_login_redirect');

function process_add_transfer()
{
   if (empty($_POST) || !wp_verify_nonce($_POST['security-code-here'], 'add_transfer')) {
      echo 'You targeted the right function, but sorry, your nonce did not verify.';
      die();
   } else {
      // wp_redirect($redirect_url_for_non_ajax_request);
   }
}

// add_action('wp_ajax_add_transfer', 'process_add_transfer');

function redirect_to_custom_lostpassword()
{
   if ($_SERVER['REQUEST_METHOD'] === 'GET') {
      // if ( is_user_logged_in() ) {
      //
      //   $this->redirect_logged_in_user();
      //   exit;
      //
      // }

      wp_redirect(home_url('reset-password'));
      exit();
   }
}

add_action('login_form_lostpassword', 'redirect_to_custom_lostpassword');

/*

Redirects to the custom password reset page, or the login page
if there are errors.

*/
function wps_redirect_to_custom_password_reset()
{
   global $wpdb;

   if ('GET' == $_SERVER['REQUEST_METHOD']) {
      $user = $wpdb->get_row($wpdb->prepare("SELECT ID, user_activation_key FROM $wpdb->users WHERE user_login = %s", $_REQUEST['login']));

      if (isset($user) && $user) {
         if (password_verify($_REQUEST['key'], $user->user_activation_key)) {
            $redirect_url = home_url('reset-password');
            $redirect_url = add_query_arg('login', esc_attr($_REQUEST['login']), $redirect_url);
            $redirect_url = add_query_arg('key', esc_attr($_REQUEST['key']), $redirect_url);

            wp_redirect($redirect_url);
            exit();
         } else {
            wp_redirect(home_url('login?login=badkey'));
            exit();
         }
      } else {
         wp_redirect(home_url('login?login=failed'));
         exit();
      }
   }
}

add_action('login_form_rp', 'wps_redirect_to_custom_password_reset');
add_action('login_form_resetpass', 'wps_redirect_to_custom_password_reset');

// remove_action( 'edd_purchase_history_header_after', 'edd_sl_add_key_column', 10, 2 );
// remove_action( 'edd_purchase_history_row_end', 'edd_sl_site_management_links', 10, 2 );

// add_action('edd_after_download_history', function() {
//   echo '<small>(Latest Version)</small>';
// }, 10, 2);

?>
