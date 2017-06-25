<?php

namespace Roots\Sage\Extras;

use Roots\Sage\Setup;

/**
 * Add <body> classes
 */
function body_class($classes) {
  // Add page slug if it doesn't exist
  if (is_single() || is_page() && !is_front_page()) {
    if (!in_array(basename(get_permalink()), $classes)) {
      $classes[] = basename(get_permalink());
    }
  }

  // Add class if sidebar is active
  if (Setup\display_sidebar()) {
    $classes[] = 'sidebar-primary';
  }

  return $classes;
}
add_filter('body_class', __NAMESPACE__ . '\\body_class');

/**
 * Clean up the_excerpt()
 */
function excerpt_more() {
  return ' &hellip; <a href="' . get_permalink() . '">' . __('Continued', 'sage') . '</a>';
}
add_filter('excerpt_more', __NAMESPACE__ . '\\excerpt_more');






/*

Get recent payment receipt data

*/
function wps_get_recent_receipt_data() {

  global $edd_receipt_args;

  $session = edd_get_purchase_session();

  if ( isset( $_GET['payment_key'] ) ) {
    $payment_key = urldecode( $_GET['payment_key'] );

  } else if ( $session ) {
    $payment_key = $session['purchase_key'];

  }

  $payment_id    = edd_get_purchase_id_by_key( $payment_key );
  $payment       = get_post( $payment_id );
  $meta          = edd_get_payment_meta( $payment->ID );
  $cart          = edd_get_payment_meta_cart_details( $payment->ID, true );
  $user          = edd_get_payment_meta_user_info( $payment->ID );
  $email         = edd_get_payment_user_email( $payment->ID );
  $status        = edd_get_payment_status( $payment, true );

  if (isset($meta['key']) && $meta['key']) {

    $finalArray = [
      'transaction' => $payment,
      'payment' => $meta,
      'cart' => $cart,
      'user' => $user
    ];

  } else {
    $finalArray = [];

  }

  return $finalArray;

}
