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



function your_function($product_data)
{
   // echo '<small class="purchase-options-note">(Your license key will auto-renew each year)</small>';
}

add_action('edd_purchase_link_top', 'your_function');

function your_function_2($product_data)
{
   echo '<div class="receipt-account-wrapper"><a href="/account" class="btn btn-secondary">Go to account</a></div>';
}

add_action('edd_payment_receipt_before', 'your_function_2');

add_action('login_init', function () {
   if (!isset($_GET['action'])) {
      wp_redirect('/login');
   }
});

add_action('edd_after_price_option', function () {
   echo '<small style="display:block;text-align:center;margin-top:-10px;">/per year</small>';
});


// Replaces the excerpt "Read More" text by a link
function new_excerpt_more($more)
{
   global $post;
   return '... <a class="moretag" href="' . get_permalink($post->ID) . '">Read more</a>';
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
