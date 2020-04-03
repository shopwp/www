<?php

namespace Roots\Sage\Setup;

use Roots\Sage\Assets;

/*

Theme setup

*/
function setup()
{
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
      'primary_sub' => __('Primary Sub', 'sage'),
      'checkout_navigation' => __('Checkout Navigation', 'sage'),
      'mobile_navigation' => __('Mobile Navigation', 'sage'),
      'footer_1' => __('Footer 1', 'sage'),
      'footer_2' => __('Footer 2', 'sage'),
      'footer_3' => __('Footer 3', 'sage'),
      'footer_4' => __('Footer 4', 'sage'),
      'footer_5' => __('Footer 5', 'sage')
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
   // add_editor_style(Assets\asset_path('styles/main.css'));
}

add_action('after_setup_theme', __NAMESPACE__ . '\\setup');

/*

Register sidebars

*/
function widgets_init()
{
   register_sidebar([
      'name' => __('Primary', 'sage'),
      'id' => 'sidebar-primary',
      'before_widget' => '<section class="widget %1$s %2$s">',
      'after_widget' => '</section>',
      'before_title' => '<h3>',
      'after_title' => '</h3>'
   ]);

   register_sidebar([
      'name' => __('Footer', 'sage'),
      'id' => 'sidebar-footer',
      'before_widget' => '<section class="widget %1$s %2$s">',
      'after_widget' => '</section>',
      'before_title' => '<h3>',
      'after_title' => '</h3>'
   ]);
}

add_action('widgets_init', __NAMESPACE__ . '\\widgets_init');

/*

Determine which pages should NOT display the sidebar

*/
function display_sidebar()
{
   static $display;

   isset($display) ||
      ($display = !in_array(true, [
         // The sidebar will NOT be displayed if ANY of the following return true.
         // @link https://codex.wordpress.org/Conditional_Tags
         is_404(),
         is_front_page(),
         is_page_template('template-custom.php'),
         is_page_template('template-landing.php')
      ]));

   return apply_filters('sage/display_sidebar', $display);
}

/*

Theme assets

*/
function assets()
{
   
   wp_enqueue_style('WPS Fonts', '//fonts.googleapis.com/css?family=Open+Sans:400,700|Noto+Sans|Bitter:400,700|IBM+Plex+Sans:400,700|Catamaran:400,700|Suez+One&display=swap', false, null);

   wp_enqueue_style('WP Shopify CSS', Assets\asset_path('prod/app.min.css'), false, filemtime(plugin_dir_path( __DIR__ ) . 'assets/prod/app.min.css'));


   if (is_page('purchase-confirmation')) {
      wp_enqueue_script('confetti-js', Assets\asset_path('../node_modules/confetti-js/dist/index.min.js'), [], null, false);
   }

   if (!is_page('checkout')) {
      
      wp_enqueue_style('Animate CSS', '//cdnjs.cloudflare.com/ajax/libs/animate.css/3.5.2/animate.min.css', false, null);
      wp_enqueue_script('Anime JS', Assets\asset_path('js/vendor/anime.min.js'), [], null, false);
      wp_enqueue_script('modernizr-js', Assets\asset_path('js/vendor/modernizr.min.js'), [], null, false);

      wp_enqueue_script('masonry-js', 'https://unpkg.com/masonry-layout@4/dist/masonry.pkgd.min.js', [], null, false);
      

      if (is_single() && comments_open() && get_option('thread_comments')) {
         wp_enqueue_script('comment-reply');
      }


      wp_enqueue_script('fitvids', '//cdnjs.cloudflare.com/ajax/libs/fitvids/1.2.0/jquery.fitvids.min.js', ['jquery'], null, false);

      wp_enqueue_script('WPS Vendor Commons', Assets\asset_path('prod/js/vendor.min.js'), [], null, true);

      wp_enqueue_script('jQuery Validate', '//cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.16.0/jquery.validate.min.js', [], null, true);
      wp_enqueue_script('jQuery Validate Additional Methods', '//cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.16.0/additional-methods.min.js', ['jQuery Validate'], null, true);

      wp_enqueue_script('WP Shopify JS', Assets\asset_path('prod/app.min.js'), [], filemtime(plugin_dir_path( __DIR__ ) . 'assets/prod/app.min.js'), true);

   } else {

      // Removing on checkout page ...
      wp_dequeue_style( 'wp-block-library' );
   }
   
}

add_action('wp_enqueue_scripts', __NAMESPACE__ . '\\assets', 100);



/*

Creating options page

*/
if (function_exists('acf_add_options_page')) {
   acf_add_options_page(array(
      'page_title' => 'Theme Settings',
      'menu_title' => 'Theme Settings',
      'menu_slug' => 'theme-settings',
      'capability' => 'edit_posts',
      'icon_url' => 'dashicons-hammer',
      'redirect' => false
   ));
}


function my_deregister_scripts() {

   if ( !is_admin() && is_page('checkout') ) {
      wp_dequeue_script( 'wp-embed' );
   }
   
}

add_action( 'wp_footer', __NAMESPACE__ . '\\my_deregister_scripts' );


remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('wp_print_styles', 'print_emoji_styles');
add_filter('show_admin_bar', '__return_false');