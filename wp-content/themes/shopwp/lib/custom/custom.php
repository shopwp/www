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

wps_forgot_password_shortcode

*/
function wps_download_shortcode($atts) {

  ob_start();
  get_template_part('components/downloads/downloads-free-view');
  return ob_get_clean();

}

add_shortcode('wps_download', 'wps_download_shortcode');


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

  // Extract each att to a variable
	extract( $shortcode_atts );

  // Return the changelog data
	return get_post_meta( $download_id, '_edd_sl_changelog', true );

}

add_shortcode( 'edd_changelog', 'edd_changelog_callback' );

