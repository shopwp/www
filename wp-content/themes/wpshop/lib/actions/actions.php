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




function wps_user_login_redirect( $url, $request, $user ) {

    if ( $user && is_object( $user ) && is_a( $user, 'WP_User' ) ) {
        if ( $user->has_cap( 'administrator' ) ) {
            $url = admin_url();
        } else {
            $url = home_url( '/account/' );
        }
    }
    return $url;
}
 
add_filter( 'login_redirect', 'wps_user_login_redirect', 10, 3 );



/*

Changing the default Wordpress login logo

*/
function wps_custom_login_logo()
{
   echo '<style type="text/css">
    .login h1 a {
      background-image: url("/wp-content/uploads/2019/06/logo-mark-v2.svg") !important;
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

function my_login_logo_url() {
    return home_url();
}
add_filter( 'login_headerurl', 'my_login_logo_url' );

function my_login_logo_url_title() {
    return 'WP Shopify';
}
add_filter( 'login_headertext', 'my_login_logo_url_title' );

/*

wps_login_redirect

*/
// function wps_login_redirect()
// {

//    if (!is_user_logged_in() && is_page('account')) {
//       wp_redirect('/login');
//       exit();
//    }
  
// }

// add_action('template_redirect', 'wps_login_redirect');

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



?>
