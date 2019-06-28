<?php

/*

wps_error_classes

*/
function wps_error_classes()
{
   $classes = array('msg', 'msg-error', 'animated', 'fadeInDown');

   return $classes;
}

add_filter('edd_error_class', 'wps_error_classes');

/*

wps_success_classes

*/
function wps_success_classes()
{
   $classes = array('edd_errors', 'edd-alert', 'edd-alert-success', 'animated', 'zoomIn');

   return $classes;
}

add_filter('edd_success_class', 'wps_success_classes');

/*

wps_info_classes

*/
function wps_info_classes()
{
   $classes = array('edd_errors', 'edd-alert', 'edd-alert-info', 'animated', 'zoomIn');

   return $classes;
}

add_filter('edd_info_class', 'wps_info_classes');

/*

wps_edd_register_email_template

*/
function wps_edd_register_email_template($templates)
{
   $templates['custom'] = 'WPS Custom';
   return $templates;
}

add_filter('edd_email_templates', 'wps_edd_register_email_template', 9999);


/*

wps_edd_register_email_template

*/
function wps_empty_cart_text()
{
   return '<div class="msg msg-not-fixed msg-notice">Your cart is empty. <a href="/purchase">Check out our plans</a></div>';
}

add_filter('edd_empty_cart_message', 'wps_empty_cart_text');

/*

wps_custom_login_lostpassword_url

*/
function wps_custom_login_lostpassword_url()
{
   return '/login';
}

add_filter('lostpassword_url', 'wps_custom_login_lostpassword_url');

/*

Redirect non-admins to the homepage after logging into the site.

*/
function wps_on_login_redirect($redirect_to, $request, $user)
{
   if (isset($user->roles)) {
      if (is_array($user->roles) && in_array('administrator', $user->roles)) {
         return admin_url();
      } else {
         return '/account';
      }
   }

   return '/account';
}

add_filter('login_redirect', 'wps_on_login_redirect', 10, 3);


?>
