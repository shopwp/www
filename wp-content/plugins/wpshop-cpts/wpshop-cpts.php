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
    'name'                => _x('Docs', 'Post Type General Name', 'text_domain'),
    'singular_name'       => _x('Doc', 'Post Type Singular Name', 'text_domain'),
    'menu_name'           => __('Docs', 'text_domain'),
    'parent_item_colon'   => __('Parent Item:', 'text_domain'),
    'new_item'            => __('Add New Doc', 'text_domain'),
    'edit_item'           => __('Edit Doc', 'text_domain'),
    'not_found'           => __('No Docs found', 'text_domain'),
    'not_found_in_trash'  => __('No Docs found in trash', 'text_domain')
  );

  $args = array(
    'label'               => __('Docs', 'text_domain'),
    'description'         => __('Custom Post Type for Docs', 'text_domain'),
    'labels'              => $labels,
    'supports'            => array('title', 'editor', 'comments'),
    'taxonomies'          => array('category', 'types'),
    'hierarchical'        => false,
    'public'              => false,
    'show_ui'             => true,
    'show_in_menu'        => true,
    'menu_position'       => 100,
    'menu_icon'           => 'dashicons-groups',
    'show_in_admin_bar'   => true,
    'can_export'          => true,
    'has_archive'         => false,
    'exclude_from_search' => true,
    'publicly_queryable'  => true,
    'capability_type'     => 'page',
    'rewrite'             => array( 'slug' => '/docs')
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
    'rewrite' => array( 'slug' => 'type' ),
  ));
}

add_action( 'init', 'wpshop_custom_taxonomy_docs', 0 );

?>
