<?php

require 'vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\BadResponseException;

/**
 * Sage includes
 *
 * The $sage_includes array determines the code library included in your theme.
 * Add or remove files to the array as needed. Supports child theme overrides.
 *
 * Please note that missing files will produce a fatal error.
 *
 * @link https://github.com/roots/sage/pull/1042
 */
$sage_includes = [
   'lib/assets.php', // Scripts and stylesheets
   'lib/extras.php', // Custom functions
   'lib/setup.php', // Theme setup
   'lib/titles.php', // Page titles
   'lib/wrapper.php', // Theme wrapper class
   'lib/customizer.php', // Theme customizer
   'lib/custom/custom.php', // Custom
   'lib/filters/filters.php', // Filter
   'lib/actions/actions.php', // Actions
   'lib/ws/ws.php' // WS
];

foreach ($sage_includes as $file) {
   if (!($filepath = locate_template($file))) {
      trigger_error(sprintf(__('Error locating %s for inclusion', 'sage'), $file), E_USER_ERROR);
   }

   require_once $filepath;
}
unset($file, $filepath);

/*

my_lost_password_page

*/
function my_lost_password_page($lostpassword_url, $redirect)
{
   return home_url('/forgot-password');
}

add_filter('lostpassword_url', 'my_lost_password_page', 10, 2);

/*

wps_reset_pass_redirect

*/
function wps_reset_pass_redirect()
{
   if (!is_user_logged_in() && is_page('reset-password')) {
      global $_GET;

      if (!isset($_GET['login']) || !$_GET['login'] || !isset($_GET['key']) || !$_GET['key']) {
         wp_safe_redirect('/forgot-password');
         exit();
      }
   }
}

add_action('template_redirect', 'wps_reset_pass_redirect');


// function wpse12535_redirect_sample() {

//     if (!is_user_logged_in() && is_page('affiliates')) {
//       wp_safe_redirect('/forgot-password');
//       exit();
//     }

// }

// add_action( 'template_redirect', 'affiliates' );



function your_function($product_data)
{
   // echo '<small class="purchase-options-note">(Your license key will auto-renew each year)</small>';
}

add_action('edd_purchase_link_top', 'your_function');

function your_function_2($product_data)
{
   echo '<div class="receipt-account-wrapper"><a href="/account" class="btn btn-primary">Go to account</a></div>';
}

add_action('edd_payment_receipt_before', 'your_function_2');

// add_action('login_init', function () {
//    if (!isset($_GET['action'])) {
//       wp_redirect('/login');
//    }
// });

add_action('edd_after_price_option', function () {
   echo '<small style="display:block;text-align:center;margin-top:-10px;">/per year</small>';
});


// Replaces the excerpt "Read More" text by a link
function new_excerpt_more($more)
{
   global $post;
   return '... <a class="moretag" href="' . get_permalink($post->ID) . '">Read more →</a>';
}
add_filter('excerpt_more', 'new_excerpt_more');

function my_child_theme_edd_auto_register_email_subject($subject)
{
   // enter your new subject below
   $subject = '🗝 WP Shopify Pro Account';

   return $subject;
}
add_filter('edd_auto_register_email_subject', 'my_child_theme_edd_auto_register_email_subject');

function wpshop_custom_excerpt_length($length)
{
   return 20;
}
add_filter('excerpt_length', 'wpshop_custom_excerpt_length', 999);




function is_admin_user($user) {

   if (is_array($user->roles) && in_array('administrator', $user->roles)) {
      return true;
   }
   
   return false;

}

function is_affiliate_only($user) {

   $affiliate_id = affwp_get_affiliate_id( $user->ID );
   $customer = new EDD_Customer($user->ID, true );

   if ($customer->email === NULL && $affiliate_id) {
      return true;
   }

   return false;

}


function is_affiliate() {

   $user = wp_get_current_user();
   $affiliate_id = affwp_get_affiliate_id( $user->ID );

   if ($affiliate_id) {
      return true;
   }

   return false;

}

 
function wps_on_login_redirect($redirect_to, $user_id) {

   $user = get_userdata($user_id);

   // return $redirect_to;

   if (isset($user->roles)) {

      // Only admins end here
      if (is_admin_user($user)) {
         return admin_url();

      }

      // Only affiliates end here
      if (is_affiliate_only($user)) {
         return '/affiliates';

      }

      // Normal customers and customer affiliates end here
      return '/account';
      
   }

   // Fallback
   return '/account';

}

add_filter('edd_login_redirect', 'wps_on_login_redirect', 10, 2);





function wps_template_redirect() {

   $user_d = wp_get_current_user();
   $user = get_userdata($user_d->ID);

   if (!is_user_logged_in() && is_page('account')) {
      wp_redirect('/login');
      exit();
   }

   if (!is_user_logged_in() && is_page('affiliates')) {
      wp_redirect('/affiliate-login');
      exit();
   }   

   if (is_user_logged_in() && is_page('become-an-affiliate') && is_affiliate()) {
      wp_redirect('/affiliates');
      exit();
   }   

   if (is_user_logged_in() && is_page('affiliate-login') && is_affiliate()) {
      wp_redirect('/affiliates');
      exit();
   }   

   if (is_user_logged_in() && is_page('affiliate-login') && !is_affiliate()) {
      wp_redirect('/become-an-affiliate');
      exit();
   }   

   // Only affiliates end here
   if (is_affiliate_only($user) && is_page('account')) {
      wp_redirect('/affiliates');
      exit();
   }
}

add_action( 'template_redirect', 'wps_template_redirect' );