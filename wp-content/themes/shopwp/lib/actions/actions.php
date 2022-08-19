<?php

function wps_custom_login_logo()
{
   echo '<style type="text/css">
    .login h1 a {
      background-image: url("/wp-content/themes/shopwp/assets/imgs/logo-mark.svg") !important;
      margin-bottom: 0;
      line-height: 3;
      height: 75px;
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