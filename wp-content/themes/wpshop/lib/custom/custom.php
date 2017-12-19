<?php

function get_taxonomy_hierarchy( $taxonomy, $parent = 0 ) {

  // only 1 taxonomy
  $taxonomy = is_array( $taxonomy ) ? array_shift( $taxonomy ) : $taxonomy;

  // get all direct decendents of the $parent
  $terms = get_terms( $taxonomy, array( 'parent' => $parent ) );

  // prepare a new array.  these are the children of $parent
  // we'll ultimately copy all the $terms into this new array, but only after they
  // find their own children
  $children = array();

  // go through all the direct decendents of $parent, and gather their children
  foreach ( $terms as $term ){
    // recurse to get the direct decendents of "this" term
    $term->children = get_taxonomy_hierarchy( $taxonomy, $term->term_id );

    // add the term to our new array
    $children[ $term->term_id ] = $term;
  }

  // send the results back to the caller
  return $children;

}


/*

Checking if user is logged in and checking out

*/
function formatHashSlug($slug) {

  $newSlug = (str_replace(' ', '-', strtolower($slug)));
  $newSlug = preg_replace('/[,\'?]/', '', $newSlug);

  return $newSlug;

}


/*

Checking if user is logged in and checking out

*/
function isRegisteredAndPurchasing() {

  if( is_page('Checkout') && is_user_logged_in() ) {
    return true;

  } else {
    return false;

  }

}


/*

Remove bundled WordPress jQuery
TODO: Do plugins need this?

*/
function jquery_cdn() {
  if (!is_admin()) {
    wp_deregister_script('jquery');
    // wp_register_script('jquery', false);
  }
}
// add_action('init', 'jquery_cdn');


/*

wps_forgot_password_shortcode

*/
function wps_forgot_password_shortcode($atts) {

  ob_start();
  get_template_part('components/account/profile/forgot-pass');
  return ob_get_clean();

}

add_shortcode('wps_forgot_password', 'wps_forgot_password_shortcode');


/*

wps_reset_password_shortcode

*/
function wps_reset_password_shortcode($atts) {

  ob_start();
  get_template_part('components/account/profile/reset-pass');
  return ob_get_clean();

}

add_shortcode('wps_reset_password', 'wps_reset_password_shortcode');


/*

Adds a shortcode to output the changelog

*/
function edd_changelog_callback($atts) {

	// Available attributes
	$shortcode_atts = array(
		'download_id' => 0,
	);

	$shortcode_atts = shortcode_atts( $shortcode_atts, $atts );
error_log('---- $shortcode_atts -----');
error_log(print_r($shortcode_atts, true));
error_log('---- /$shortcode_atts -----');
  // Extract each att to a variable
	extract( $shortcode_atts );

  // Return the changelog data
	return get_post_meta( $download_id, '_edd_sl_changelog', true );

}

add_shortcode( 'edd_changelog', 'edd_changelog_callback' );


/*

Nonce verifying helper

*/
function wps_verify_nonce($action) {

  $nonce = $_POST['data'][$action];

  if (!wp_verify_nonce($nonce, $action)) {
    die('Security check');

  } else {
    return true;

  }

}


/*

Get customer email

*/
function wps_get_customer_email() {

  $customer = new EDD_Customer(get_current_user_id(), true);

  return $customer->email;

}


/*

Update customer email
Note: User must be logged in

*/
function wps_update_customer_email($newEmail) {

  global $wpdb;

  $customer = new EDD_Customer(get_current_user_id(), true);

  if (!username_exists($newEmail)) {

    $customer->update(array(
    	'email' => $newEmail
    ));

    $okokok = wp_update_user( array(
      'ID' => get_current_user_id(),
      'user_email' => $newEmail,
      'user_nicename' => $newEmail
    ));

    $wpdb->update($wpdb->users, array('user_login' => $newEmail), array('ID' => get_current_user_id()));
    return true;

  } else {
    return false;

  }

}


/*

Get customer current name

*/
function wps_get_customer_name() {

  $customer = new EDD_Customer(get_current_user_id(), true);
  return $customer->name;

}


/*

Update customer name

*/
function wps_update_customer_name($newName) {

  $customer = new EDD_Customer(get_current_user_id(), true);

  return $customer->update(array(
  	'name' => $newName
  ));

}


/*

Update customer name

*/
function wps_check_current_pass_valid($passCurrent, $userID) {

  $user = get_user_by('id', $userID);

  if ( $user && wp_check_password($passCurrent, $user->data->user_pass, $user->ID) ) {

    return true;

  } else {

    return false;

  }

}


/*

Reset user's pass
TODO: Return error codes

*/
function wps_do_password_reset($data) {

  $email = $data['login'];
  $key = $data['key'];
  $newPass = $data['wps_account_new_password'];
  $newPassConfirm = $data['wps_account_new_password_confirm'];
  $user = get_user_by('email', $email);

  if(!isset($data['wps_account_new_password_confirm']) || !$data['wps_account_new_password_confirm']) {

    // Empty pass
    return false;

  } else {

    if($newPass !== $newPassConfirm) {

      return false;

    } else {

      if(isset($user) && $user) {

        if(password_verify($key, $user->data->user_activation_key)) {

          reset_password($user->data, $newPassConfirm);

          wp_signon(array(
            'user_login'    => $email,
            'user_password' => $newPassConfirm
          ), false);

          return true;

        } else {

          // Bad key
          return false;

        }

      } else {

        // Bad user
        return false;

      }

    }

  }

}


?>
