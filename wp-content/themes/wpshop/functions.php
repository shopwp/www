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

function wps_edd_payment_receipt_before($product_data)
{
   echo '<div class="receipt-account-wrapper" style="margin-top: 15px;"><a href="/account" class="btn btn-primary">Go to account</a></div>';
}

add_action('edd_payment_receipt_before', 'wps_edd_payment_receipt_before');


add_action('edd_after_price_option', function () {
   echo '<small style="display:block;text-align:center;margin-top:-10px;">/per year</small>';
});

// Replaces the excerpt "Read More" text by a link
function new_excerpt_more($more)
{
   global $post;
   return '... <div class="moretag-wrapper"><a class="moretag btn-s" href="' . get_permalink($post->ID) . '">Read more</a></div>';
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

   if (empty($user) || !isset($user->ID)) {
      return false;
   }

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




function add_script_attributes($tag, $handle) {

   if ($handle !== 'WPS Vendor Commons' && $handle !== 'WPS Fonts' && $handle !== 'modernizr-js' && $handle !== 'WP Shopify JS') {
      return $tag;
   }

   return str_replace(' src', ' defer="defer" src', $tag );

}

add_filter('script_loader_tag', 'add_script_attributes', 10, 2);




function pw_edd_payment_icon($icons) {

   $icons['/wp-content/uploads/2019/11/icon-mastercard.png'] = 'Mastercard (custom)';
   $icons['/wp-content/uploads/2019/11/icon-visa.png'] = 'Visa (custom)';
   $icons['/wp-content/uploads/2019/11/icon-ae.png'] = 'American Express (custom)';
   $icons['/wp-content/uploads/2019/11/icon-discover.png'] = 'Discover (custom)';
   $icons['/wp-content/uploads/2019/11/icon-paypal.png'] = 'PayPal (custom)';

   return $icons;

}

add_filter('edd_accepted_payment_icons', 'pw_edd_payment_icon', 99, 1);


function purchase_with_paypal_button_text($one, $label) {

   $chosen_gateway = edd_get_chosen_gateway();
   

   if ($chosen_gateway === 'paypalexpress') {
      return '✨ Purchase with PayPal ✨';
   }

   return $label;

}

add_filter('edd_get_checkout_button_purchase_label', 'purchase_with_paypal_button_text', 10, 2);


add_filter( 'edd_subscription_can_update', function() {
    return true;
});

add_action('edd_before_checkout_cart', function() {

   if (is_user_logged_in()) {
      $current_user = wp_get_current_user();
      echo '<p class="wps-checkout-logged-in-as">👋 Hey, ' . $current_user->user_firstname . ' ' . $current_user->user_lastname . '. <a href="' . wp_logout_url('/') . '">Logout?</a></p>';
   } else {
      echo '<p class="wps-checkout-logged-in-as">Already have an account? <a href="/login?redirect=checkout">Log in</a></p>';
   }

});



function edd_auto_register_email_body_custom($default_email_body, $first_name, $username, $password) {

   $default_email_body = str_replace("wpshop.io/wp-login.php", "account.wpshop.io/login", $default_email_body);

   return $default_email_body;
}

add_filter('edd_auto_register_email_body', 'edd_auto_register_email_body_custom', 10, 4);



function react_rounter_rewrite_rules() {
    add_rewrite_rule('^account/(.+)?', 'index.php?pagename=account', 'top');
}

add_action('init', 'react_rounter_rewrite_rules');

add_filter( 'login_url', 'my_login_page', 10, 3 );

function my_login_page( $login_url, $redirect, $force_reauth ) {
   return home_url('/login');
}


// function redirect_non_admin_user() {
//    if (is_user_logged_in()) {
//       if (!defined('DOING_AJAX') && !current_user_can('administrator')) {
//          wp_redirect(site_url() . '/account');
//          exit;
//       }
//    }
// }

// add_action('admin_init', 'redirect_non_admin_user');