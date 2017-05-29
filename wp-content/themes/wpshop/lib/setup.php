<?php

namespace Roots\Sage\Setup;

use Roots\Sage\Assets;

/*

Theme setup

*/
function setup() {
  // Enable features from Soil when plugin is activated
  // https://roots.io/plugins/soil/
  add_theme_support('soil-clean-up');
  add_theme_support('soil-nav-walker');
  add_theme_support('soil-nice-search');
  add_theme_support('soil-jquery-cdn');
  add_theme_support('soil-relative-urls');

  // Make theme available for translation
  // Community translations can be found at https://github.com/roots/sage-translations
  load_theme_textdomain('sage', get_template_directory() . '/lang');

  // Enable plugins to manage the document title
  // http://codex.wordpress.org/Function_Reference/add_theme_support#Title_Tag
  add_theme_support('title-tag');

  // Register wp_nav_menu() menus
  // http://codex.wordpress.org/Function_Reference/register_nav_menus
  register_nav_menus([
    'primary_navigation' => __('Primary Navigation', 'sage'),
    'footer_navigation' => __('Footer Navigation', 'sage'),
    'checkout_navigation' => __('Checkout Navigation', 'sage')
  ]);

  // Enable post thumbnails
  // http://codex.wordpress.org/Post_Thumbnails
  // http://codex.wordpress.org/Function_Reference/set_post_thumbnail_size
  // http://codex.wordpress.org/Function_Reference/add_image_size
  add_theme_support('post-thumbnails');

  // Enable post formats
  // http://codex.wordpress.org/Post_Formats
  add_theme_support('post-formats', ['aside', 'gallery', 'link', 'image', 'quote', 'video', 'audio']);

  // Enable HTML5 markup support
  // http://codex.wordpress.org/Function_Reference/add_theme_support#HTML5
  add_theme_support('html5', ['caption', 'comment-form', 'comment-list', 'gallery', 'search-form']);

  // Use main stylesheet for visual editor
  // To add custom styles edit /assets/styles/layouts/_tinymce.scss
  add_editor_style(Assets\asset_path('styles/main.css'));
}

add_action('after_setup_theme', __NAMESPACE__ . '\\setup');


/*

Register sidebars

*/
function widgets_init() {
  register_sidebar([
    'name'          => __('Primary', 'sage'),
    'id'            => 'sidebar-primary',
    'before_widget' => '<section class="widget %1$s %2$s">',
    'after_widget'  => '</section>',
    'before_title'  => '<h3>',
    'after_title'   => '</h3>'
  ]);

  register_sidebar([
    'name'          => __('Footer', 'sage'),
    'id'            => 'sidebar-footer',
    'before_widget' => '<section class="widget %1$s %2$s">',
    'after_widget'  => '</section>',
    'before_title'  => '<h3>',
    'after_title'   => '</h3>'
  ]);
}

add_action('widgets_init', __NAMESPACE__ . '\\widgets_init');


/*

Determine which pages should NOT display the sidebar

*/
function display_sidebar() {
  static $display;

  isset($display) || $display = !in_array(true, [
    // The sidebar will NOT be displayed if ANY of the following return true.
    // @link https://codex.wordpress.org/Conditional_Tags
    is_404(),
    is_front_page(),
    is_page_template('template-custom.php'),
    is_page_template('template-landing.php')
  ]);

  return apply_filters('sage/display_sidebar', $display);
}


/*

Theme assets

*/
function assets() {

  wp_enqueue_style('Animate CSS', 'https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.5.2/animate.min.css', false, null);
  wp_enqueue_style('Font Awesome CSS', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css', false, null);

  wp_enqueue_style('WPS Fonts', 'https://fonts.googleapis.com/css?family=Open+Sans:400,700|Catamaran:400,700', false, null);


  // TODO: Enque within plugin?
  wp_enqueue_script('modernizr-js', Assets\asset_path('js/vendor/modernizr.min.js'), [], null, true);


  if (is_single() && comments_open() && get_option('thread_comments')) {
    wp_enqueue_script('comment-reply');
  }


  wp_enqueue_style('WP Shopify CSS', Assets\asset_path('prod/css/app.min.css'), false, null);



  // Scroll magic
  wp_enqueue_script('Scroll magic', 'http://cdnjs.cloudflare.com/ajax/libs/ScrollMagic/2.0.5/ScrollMagic.min.js', ['jquery'], null, true);

  // WPS Vendor Commons
  wp_enqueue_script('WPS Vendor Commons', Assets\asset_path('prod/js/vendor.min.js'), [], null, true);



  // jQuery Validate Additional Methods
  wp_enqueue_script('jQuery Validate', 'https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.16.0/jquery.validate.min.js', [], null, true);

  // jQuery Validate Additional Methods
  wp_enqueue_script('jQuery Validate Additional Methods', 'https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.16.0/additional-methods.min.js', ['jQuery Validate'], null, true);


  wp_enqueue_script('WPS App', Assets\asset_path('prod/js/app.min.js'), [], null, true);






  // // WPS Plugins
  wp_enqueue_script('WPS Plugins', Assets\asset_path('prod/js/plugins.min.js'), [], null, true);
  //
  //
  // // Checkout
  // if(is_page('Checkout')) {
  //   wp_enqueue_script('WPS Checkout', Assets\asset_path('prod/js/checkout.min.js'), [], null, true);
  // }
  //
  // // Account
  // if(is_page('Account')) {
  //   wp_enqueue_script('WPS Account', Assets\asset_path('prod/js/account.min.js'), [], null, true);
  // }
  //
  // // Auth
  // if(is_page('Auth')) {
  //   wp_enqueue_script('WPS Auth', Assets\asset_path('prod/js/auth.min.js'), [], null, true);
  // }
  //
  // Docs
  if(is_page('Docs') || get_post_type( get_the_ID() ) === 'docs') {
    wp_enqueue_style('Prism CSS', Assets\asset_path('css/vendor/prism.min.css'), false, null);
    // wp_enqueue_script('WPS Auth', Assets\asset_path('prod/js/docs.min.js'), [], null, true);
    wp_enqueue_script('Prism JS', Assets\asset_path('js/vendor/prism.min.js'), [], null, true);
  }
  //
  // wp_enqueue_script('WPS Mailinglist', Assets\asset_path('prod/js/mailinglist.min.js'), [], null, true);
  // wp_enqueue_script('WPS Auth', Assets\asset_path('prod/js/forms.min.js'), [], null, true);
  // wp_enqueue_script('WPS App', Assets\asset_path('prod/js/app.min.js'), [], null, true);

}

add_action('wp_enqueue_scripts', __NAMESPACE__ . '\\assets', 100);


/*

Creating options page

*/
if(function_exists('acf_add_options_page')) {

  acf_add_options_page(array(
    'page_title'  => 'Theme Settings',
    'menu_title'  => 'Theme Settings',
    'menu_slug'   => 'theme-settings',
    'capability'  => 'edit_posts',
    'icon_url'    => 'dashicons-hammer',
    'redirect'    => false
  ));

}
