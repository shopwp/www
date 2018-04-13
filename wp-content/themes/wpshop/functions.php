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
// if (DISABLE_CANONICAL == 'Y') {
//   remove_filter('template_redirect', 'redirect_canonical');
// }


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


// function testtetteeeerrr() {
//   echo 'NOPE';
// }

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




function wpa_show_permalinks( $post_link, $post ) {

  if ( is_object($post) && $post->post_type == 'docs') {

    $terms = wp_get_object_terms( $post->ID, 'types' );

    if ($terms) {
      return str_replace( '%types%' , $terms[0]->slug , $post_link );
    }

  }

  return $post_link;

}

add_filter( 'post_type_link', 'wpa_show_permalinks', 1, 2 );




function your_function($product_data) {

  echo '<small class="purchase-options-note">(You need to manually renew your license key each year. We will <b>not</b> auto-renew your account by charging your credit card).</small>';

}

add_action( 'edd_purchase_link_top', 'your_function' );


function your_function_2($product_data) {

  echo '<div class="receipt-account-wrapper"><a href="/account" class="btn btn-secondary">Go to account</a></div>';

}

add_action( 'edd_payment_receipt_before', 'your_function_2' );




add_action('login_init', function(){
  if( !isset( $_GET['action'] ) ) {
    wp_redirect( '/login' );
  }
});


add_action('edd_after_price_option', function(){
  echo '<small style="display:block;text-align:center;margin-top:-10px;">per year</small>';
});




//
//
//
//
// /*
//  * Replace Taxonomy slug with Post Type slug in url
//  * Version: 1.1
//  */
// function taxonomy_slug_rewrite($wp_rewrite) {
//
//   $rules = array();
//
//   // get all custom taxonomies
//   $taxonomies = get_taxonomies(array('_builtin' => false), 'objects');
//   // get all custom post types
//   $post_types = get_post_types(array('public' => true, '_builtin' => false), 'objects');
//
//   foreach ($post_types as $post_type) {
//
//       foreach ($taxonomies as $taxonomy) {
//
//           // go through all post types which this taxonomy is assigned to
//           foreach ($taxonomy->object_type as $object_type) {
//
//               // check if taxonomy is registered for this custom type
//               if ($object_type == $post_type->rewrite['slug']) {
//
//                   // get category objects
//                   $terms = get_categories(array('type' => $object_type, 'taxonomy' => $taxonomy->name, 'hide_empty' => 0));
//                   // error_log('---- $terms -----');
//                   // error_log(print_r($terms, true));
//                   // error_log('---- /$terms -----');
//
//
//                   // make rules
//                   foreach ($terms as $term) {
//
//
//
//                     // error_log('---- $object_type -----');
//                     // error_log(print_r($object_type, true));
//                     // error_log('---- /$object_type -----');
//                     //
//                     // error_log('---- $term->slug -----');
//                     // error_log(print_r($term->slug, true));
//                     // error_log('---- /$term->slug -----');
//
//
//                     if (isset($term->category_parent) && $term->category_parent) {
//
//
//                       $parentID = $term->category_parent;
//
//                       $filtered = array_filter($terms, function($key) use ($parentID, $terms) {
//
//                         return $terms[$key]->term_id === $parentID;
//
//                       }, ARRAY_FILTER_USE_KEY);
//
//
//                       $filtered = array_values($filtered);
//
//
//                       if (!empty($filtered)) {
//
//                         $rules[$object_type . '/' . $filtered[0]->slug . '/' . $term->slug . '/?$'] = 'index.php?' . $term->taxonomy . '=' . $term->slug;
//
//                         error_log('---- $rules -----');
//                         error_log(print_r($rules, true));
//                         error_log('---- /$rules -----');
//
//                       }
//
//
//                     } else {
//                       $rules[$object_type . '/' . $term->slug . '/?$'] = 'index.php?' . $term->taxonomy . '=' . $term->slug;
//
//                     }
//
//
//                   }
//               }
//           }
//       }
//   }
//
//   // merge with global rules
//   $wp_rewrite->rules = $rules + $wp_rewrite->rules;
//
// }
//
// add_filter('generate_rewrite_rules', 'taxonomy_slug_rewrite');
//
//
//
//
//
//















//
//
//
//
//
//
//
//
// add_action( 'init', 'register_my_types' );
// function register_my_types() {
// 	register_post_type( 'recipes',
// 		array(
// 			'labels' => array(
// 				'name' => __( 'Recipes' ),
// 				'singular_name' => __( 'Recipee' )
// 			),
// 			'public' => true,
// 			'has_archive' => true,
// 		)
// 	);
// 	register_taxonomy( 'occasion', array( 'recipes' ), array(
// 			'hierarchical' => true,
// 			'label' => 'Occasions'
// 		)
// 	);
// }
//
//
//
//
//
//
//
//
// // Add our custom permastructures for custom taxonomy and post
//
// add_action( 'wp_loaded', 'add_clinic_permastructure' );
//
// function add_clinic_permastructure() {
//
// 	global $wp_rewrite;
//
//   // error_log('---- $wp_rewrite -----');
//   // error_log(print_r($wp_rewrite, true));
//   // error_log('---- /$wp_rewrite -----');
//
// 	add_permastruct( 'types', 'docs/%types%', false );
// 	add_permastruct( 'docs', 'docs/%types%/%docs%', false );
//   add_permastruct( 'templates', 'docs/templates/%types%/%docs%', false );
//
// }
//
// // Make sure that all links on the site, include the related texonomy terms
// add_filter( 'post_type_link', 'recipe_permalinks', 10, 2 );
//
//
//
//
//
//
//
//
//
//
//
// function recipe_permalinks( $permalink, $post ) {
// 	if ( $post->post_type !== 'docs' )
// 		return $permalink;
// 	$terms = get_the_terms( $post->ID, 'types' );
//
// 	if ( ! $terms )
// 		return str_replace( '%types%/', '', $permalink );
// 	$post_terms = array();
// 	foreach ( $terms as $term )
// 		$post_terms[] = $term->slug;
//
//
//
//
// 	return str_replace( '%types%', implode( ',', $post_terms ) , $permalink );
// }
// // Make sure that all term links include their parents in the permalinks
// add_filter( 'term_link', 'add_term_parents_to_permalinks', 10, 2 );
// function add_term_parents_to_permalinks( $permalink, $term ) {
// 	$term_parents = get_term_parents( $term );
// 	foreach ( $term_parents as $term_parent )
// 		$permlink = str_replace( $term->slug, $term_parent->slug . ',' . $term->slug, $permalink );
// 	return $permlink;
// }
// // Helper function to get all parents of a term
// function get_term_parents( $term, &$parents = array() ) {
// 	$parent = get_term( $term->parent, $term->taxonomy );
//
// 	if ( is_wp_error( $parent ) )
// 		return $parents;
//
// 	$parents[] = $parent;
// 	if ( $parent->parent )
// 		get_term_parents( $parent, $parents );
//     return $parents;
// }
//
//
//
//
//
//
//
//


























//
//
//
//
//
//
// add_filter('request', 'rudr_change_term_request', 1, 1 );
//
// function rudr_change_term_request($query){
//
// 	$tax_name = 'types'; // specify you taxonomy name here, it can be also 'category' or 'post_tag'
//
// 	// Request for child terms differs, we should make an additional check
// 	if( $query['attachment'] ) :
// 		$include_children = true;
// 		$name = $query['attachment'];
// 	else:
// 		$include_children = false;
// 		$name = $query['name'];
// 	endif;
//
//
// 	$term = get_term_by('slug', $name, $tax_name); // get the current term to make sure it exists
//
// 	if (isset($name) && $term && !is_wp_error($term)): // check it here
//
// 		if( $include_children ) {
// 			unset($query['attachment']);
// 			$parent = $term->parent;
// 			while( $parent ) {
// 				$parent_term = get_term( $parent, $tax_name);
// 				$name = $parent_term->slug . '/' . $name;
// 				$parent = $parent_term->parent;
// 			}
// 		} else {
// 			unset($query['name']);
// 		}
//
// 		switch( $tax_name ):
// 			case 'category':{
// 				$query['category_name'] = $name; // for categories
// 				break;
// 			}
// 			case 'post_tag':{
// 				$query['tag'] = $name; // for post tags
// 				break;
// 			}
// 			default:{
// 				$query[$tax_name] = $name; // for another taxonomies
// 				break;
// 			}
// 		endswitch;
//
// 	endif;
//
// 	return $query;
//
// }
//
//
// add_filter( 'term_link', 'rudr_term_permalink', 10, 3 );
//
// function rudr_term_permalink( $url, $term, $taxonomy ){
//
// 	$taxonomy_name = 'types'; // your taxonomy name here
// 	$taxonomy_slug = 'types'; // the taxonomy slug can be different with the taxonomy name (like 'post_tag' and 'tag' )
//
// 	// exit the function if taxonomy slug is not in URL
// 	if ( strpos($url, $taxonomy_slug) === FALSE || $taxonomy != $taxonomy_name ) return $url;
//
// 	$url = str_replace('/' . $taxonomy_slug, '', $url);
//
// 	return $url;
// }
