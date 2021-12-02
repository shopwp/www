<?php





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
    return 'ShopWP';
}
add_filter( 'login_headertext', 'my_login_logo_url_title' );


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
