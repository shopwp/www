<?php

/*

Plugin Name: WP Shopify | Custom Post Types
Version: 1.0
Author: Andrew Robbins - https://blog.simpleblend.net
Description: Custom Post Types for WP Shopify

*/

/*

CPT: Docs

*/
function wpshop_custom_post_type_docs() {

  $labels = array(
    'name'                => _x('Docs', 'Post Type General Name', 'wpshop'),
    'singular_name'       => _x('Doc', 'Post Type Singular Name', 'wpshop'),
    'menu_name'           => __('Docs', 'wpshop'),
    'parent_item_colon'   => __('Parent Item:', 'wpshop'),
    'new_item'            => __('Add New Doc', 'wpshop'),
    'edit_item'           => __('Edit Doc', 'wpshop'),
    'not_found'           => __('No Docs found', 'wpshop'),
    'not_found_in_trash'  => __('No Docs found in trash', 'wpshop')
  );

  $args = array(
    'label'               => __('Docs', 'wpshop'),
    'description'         => __('Custom Post Type for Docs', 'wpshop'),
    'labels'              => $labels,
    'supports'            => array('title', 'editor', 'comments'),
    'taxonomies'          => array('category', 'types'),
    'hierarchical'        => true,
    'public'              => true,
    'show_ui'             => true,
    'show_in_menu'        => true,
    'menu_position'       => 100,
    'menu_icon'           => 'dashicons-groups',
    'show_in_admin_bar'   => true,
    'can_export'          => true,
    'has_archive'         => true,
    'exclude_from_search' => true,
    'publicly_queryable'  => true,
    'query_var'           => true,
    'capability_type'     => 'post'
  );

  register_post_type('docs', $args);

}

add_action('init', 'wpshop_custom_post_type_docs', 0);


/*

Docs Taxonomy

*/
function wpshop_custom_taxonomy_docs() {

  $labels = array(
    'name' => _x( 'Types', 'taxonomy general name' ),
    'singular_name' => _x( 'Type', 'taxonomy singular name' ),
    'search_items' =>  __( 'Search Types' ),
    'all_items' => __( 'All Types' ),
    'parent_item' => __( 'Parent Type' ),
    'parent_item_colon' => __( 'Parent Type:' ),
    'edit_item' => __( 'Edit Type' ),
    'update_item' => __( 'Update Type' ),
    'add_new_item' => __( 'Add New Type' ),
    'new_item_name' => __( 'New Type Name' ),
    'menu_name' => __( 'Types' ),
  );

  register_taxonomy('types', array('docs'), array(
    'hierarchical' => true,
    'labels' => $labels,
    'show_ui' => true,
    'show_admin_column' => true,
    'query_var' => true,
    'rewrite' => array(
      'slug' => 'docs',
      'hierarchical' => true
    )
  ));
}

add_action( 'init', 'wpshop_custom_taxonomy_docs', 0 );



/*

CPT: FAQs

*/
function custom_post_type_faqs() {

  $labels = array(
    'name'                => _x('FAQs', 'Post Type General Name', 'wpshop'),
    'singular_name'       => _x('FAQ', 'Post Type Singular Name', 'wpshop'),
    'menu_name'           => __('FAQs', 'wpshop'),
    'parent_item_colon'   => __('Parent FAQ:', 'wpshop'),
    'new_item'            => __('Add New FAQ', 'wpshop'),
    'edit_item'           => __('Edit FAQ', 'wpshop'),
    'not_found'           => __('No FAQs found', 'wpshop'),
    'not_found_in_trash'  => __('No FAQs found in trash', 'wpshop')
  );

  $args = array(
    'label'               => __('post_type_faqs', 'wpshop'),
    'description'         => __('Custom Post Type for FAQs', 'wpshop'),
    'labels'              => $labels,
    'supports'            => array('title', 'category'),
    'taxonomies'          => array('faq-category'),
    'hierarchical'        => false,
    'public'              => true,
    'show_ui'             => true,
    'show_in_menu'        => true,
    'menu_position'       => 100,
    'menu_icon'           => 'dashicons-format-quote',
    'show_in_admin_bar'   => true,
    'show_in_nav_menus'   => true,
    'can_export'          => true,
    'has_archive'         => true,
    'exclude_from_search' => false,
    'publicly_queryable'  => true,
    'capability_type'     => 'page',
  );

  register_post_type('faqs', $args);


  // Add new taxonomy, make it hierarchical (like categories)
	$labels = array(
		'name'              => _x( 'FAQ Categories', 'taxonomy general name', 'textdomain' ),
		'singular_name'     => _x( 'FAQ Categorie', 'taxonomy singular name', 'textdomain' ),
		'search_items'      => __( 'Search FAQ Categories', 'textdomain' ),
		'all_items'         => __( 'All FAQ Categories', 'textdomain' ),
		'parent_item'       => __( 'Parent FAQ Category', 'textdomain' ),
		'parent_item_colon' => __( 'Parent FAQ Category:', 'textdomain' ),
		'edit_item'         => __( 'Edit FAQ Category', 'textdomain' ),
		'update_item'       => __( 'Update FAQ Category', 'textdomain' ),
		'add_new_item'      => __( 'Add New FAQ Category', 'textdomain' ),
		'new_item_name'     => __( 'New FAQ Category Name', 'textdomain' ),
		'menu_name'         => __( 'Categories', 'textdomain' ),
	);

	$args = array(
		'hierarchical'      => true,
		'labels'            => $labels,
		'show_ui'           => true,
		'show_admin_column' => true,
		'query_var'         => true,
		'rewrite'           => array( 'slug' => 'faq-category' ),
	);

	register_taxonomy( 'faq-category', array( 'faqs' ), $args );

}


// Hookin, yo
add_action('init', 'custom_post_type_faqs', 0);


?>
