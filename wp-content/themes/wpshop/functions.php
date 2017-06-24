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
  'lib/assets.php',               // Scripts and stylesheets
  'lib/extras.php',               // Custom functions
  'lib/setup.php',                // Theme setup
  'lib/titles.php',               // Page titles
  'lib/wrapper.php',              // Theme wrapper class
  'lib/customizer.php',           // Theme customizer
  'lib/custom/custom.php',        // Custom
  'lib/filters/filters.php',      // Filter
  'lib/actions/actions.php',      // Actions
  'lib/ws/ws.php'                 // WS
];

foreach ($sage_includes as $file) {
  if (!$filepath = locate_template($file)) {
    trigger_error(sprintf(__('Error locating %s for inclusion', 'sage'), $file), E_USER_ERROR);
  }

  require_once $filepath;
}
unset($file, $filepath);


/*

Hack for Webpack dev server
TODO: Remove before pushing live

*/
if(DISABLE_CANONICAL == 'Y') {
  remove_filter('template_redirect', 'redirect_canonical');
}


/*

my_lost_password_page

*/
function my_lost_password_page( $lostpassword_url, $redirect ) {
  return home_url('/forgot-password');
}

add_filter( 'lostpassword_url', 'my_lost_password_page', 10, 2 );


/*

wps_reset_pass_redirect

*/
function wps_reset_pass_redirect() {

  if (!is_user_logged_in() && is_page('reset-password')) {

    global $_GET;

    if(!isset($_GET['login']) || !$_GET['login'] || !isset($_GET['key']) || !$_GET['key']) {
      wp_safe_redirect('/forgot-password');
      exit;
    }

  }

}

add_action('template_redirect', 'wps_reset_pass_redirect');



function wps_identifier_for_post($post) {
  return $post->ID;
}




// define the edd_send_back_to_checkout callback
// function filter_edd_send_back_to_checkout( $redirect, $args ) {
//
//   error_log('------ $args ------');
//   error_log(print_r($args, true));
//
//   error_log('------ $redirect ------');
//   error_log(print_r($redirect, true));
//
//     // make filter magic happen here...
//     return $redirect;
// };
//
// // add the filter
// add_filter( 'edd_send_back_to_checkout', 'filter_edd_send_back_to_checkout', 999);












//
// print_r($ppplugin_public);


// remove_action('wps_products_pagination', array('WPS_Public', 'wps_products_pagination'));






// function WPShopify() {
//   echo '11H8787HHHH';
// }
//
// WPShopify();


// woocommerce_after_single_product();

// remove_action('wps_products_pagination', array(Pub::wps_products_paginationn(), 'wps_products_paginationn'));


// add_action('wps_products_paginationn', array($Frontend, 'testtetteeeerrr'), 999);

// echo "<pre>";
// print_r($Frontend);
// echo "</pre>";

// $Frontend->config->plugin_name = '1111';


function testtetteeeerrr() {
  echo 'NOPE';
}

//
// $Frontend = 'hello';
//
// print_r($Frontend);

// function WPS() {
//   error_log('OYOYOYOY');
// }
//
// WPS();


// remove_action( 'wps_products_pagination', array(WPS\Boot()->Frontend, 'wps_products_pagination' ) );

// add_action( 'wps_products_pagination', 'testtetteeeerrr');


// add_action( 'wps_before_products_item', 'testtetteeeerrr' );




function your_function($product_data) {

  echo '<small class="purchase-options-note">(Select an option above)</small>';

}

add_action( 'edd_purchase_link_top', 'your_function' );


function your_function_2($product_data) {

  echo '<div class="receipt-account-wrapper"><a href="/account" class="btn btn-secondary">Go to account</a></div>';

}

add_action( 'edd_payment_receipt_before', 'your_function_2' );
