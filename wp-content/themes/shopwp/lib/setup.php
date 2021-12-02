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


function dequeue_superfluous_assets() {

   error_log('--- dequeue_superfluous_assets ---');

   // Remove the REST API endpoint.
   remove_action( 'rest_api_init', 'wp_oembed_register_route' );

   // Turn off oEmbed auto discovery.
   add_filter( 'embed_oembed_discover', '__return_false' );

   // Don't filter oEmbed results.
   remove_filter( 'oembed_dataparse', 'wp_filter_oembed_result', 10 );

   // Remove oEmbed discovery links.
   remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );

   // Remove oEmbed-specific JavaScript from the front-end and back-end.
   remove_action( 'wp_head', 'wp_oembed_add_host_js' );
   add_filter( 'tiny_mce_plugins', 'disable_embeds_tiny_mce_plugin' );

   // Remove all embeds rewrite rules.
   add_filter( 'rewrite_rules_array', 'disable_embeds_rewrites' );

   // Remove filter of the oEmbed result before any HTTP requests are made.
   remove_filter( 'pre_oembed_result', 'wp_filter_pre_oembed_result', 10 );
   wp_deregister_script( 'wp-embed' );
   wp_dequeue_style( 'wp-block-library' );
   wp_dequeue_style( 'shopwp-styles-frontend-all' );

   wp_dequeue_script( 'shopwp-runtime' );
   wp_dequeue_script( 'shopwp-vendors-public' );
   wp_dequeue_script( 'shopwp-public' );

   wp_deregister_script( 'shopwp-runtime' );
   wp_deregister_script( 'shopwp-vendors-public' );
   wp_deregister_script( 'shopwp-public' );
}

function is_page_to_dequeue() {
   return is_page('checkout') || is_page('purchase-confirmation') || is_page('purchase') || is_page('login') || is_page('account');
}


function replace_rest_protocol() {
   if (is_ssl()) {
      return str_replace("http://", "https://", get_rest_url());
   }

   return get_rest_url();
}

/*

Theme assets

*/
function assets()
{

   if (is_page('account')) {
      wp_enqueue_script('ShopWP Account', Assets\asset_path('prod/account.min.js'), [], filemtime(plugin_dir_path( __DIR__ ) . 'assets/prod/account.min.js'), true);

   } else {

      wp_enqueue_style('WPS Fonts', '//fonts.googleapis.com/css?family=Inter&display=swap', false, null);

      wp_enqueue_style('ShopWP CSS', Assets\asset_path('prod/app.min.css'), false, filemtime(plugin_dir_path( __DIR__ ) . 'assets/prod/app.min.css'));

      if (is_page('purchase-confirmation')) {
         wp_enqueue_script('confetti-js', Assets\asset_path('js/vendor/confetti.min.js'), [], null, false);
      }

      wp_enqueue_script('masonry-js', Assets\asset_path('js/vendor/masonry.pkgd.min.js'), [], null, true);

      wp_enqueue_script('jquery-validate', '//cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.16.0/jquery.validate.min.js', [], null, true);

      wp_enqueue_script('ShopWP JavaScript', Assets\asset_path('prod/app.min.js'), ['jquery', 'jquery-validate', 'masonry-js'], filemtime(plugin_dir_path( __DIR__ ) . 'assets/prod/app.min.js'), true);

      if (!is_page('purchase-confirmation')) {
         wp_enqueue_script('popper-js', 'https://unpkg.com/@popperjs/core@2', [], null, false);
         wp_enqueue_script('tippy-js', 'https://unpkg.com/tippy.js@6', [], null, false);
      }
      
   }

   $settings_encoded_string = [
      'api' => [
         'namespace' => defined('SHOPWP_SHOPIFY_API_NAMESPACE') ? SHOPWP_SHOPIFY_API_NAMESPACE : false,
         'restUrl' => replace_rest_protocol(),
         'nonce' => wp_create_nonce( 'wp_rest' )
      ],
      'misc' => [
         'dequeued' => false,
         'userId' => get_current_user_id(),
         'themeUrl' => get_template_directory_uri(),
         'siteUrl' => get_site_url(),
         'latestVersion' => defined('WP_SHOPIFY_NEW_PLUGIN_VERSION') ? WP_SHOPIFY_NEW_PLUGIN_VERSION : false
      ]
   ];

   if (is_page_to_dequeue()) {
      dequeue_superfluous_assets();
      $settings_encoded_string['misc']['dequeued'] = true;
   }

   $js_string = "const wpshopifyMarketing = " . wp_json_encode($settings_encoded_string) . ";";

   wp_add_inline_script('ShopWP JavaScript', $js_string, 'before');
   wp_add_inline_script('ShopWP Account', $js_string, 'before');

}

add_action('wp_enqueue_scripts', __NAMESPACE__ . '\\assets', 100);


function assets_login() {

   $uri = $_SERVER['REQUEST_URI'];

   if (strpos($uri, '/wp-login.php?action=resetpass') !== false || strpos($uri, '/wp-login.php?action=rp') !== false) {
      wp_enqueue_style('WPS Fonts', '//fonts.googleapis.com/css?family=Open+Sans:400,700|Noto+Sans|Bitter:400,700|IBM+Plex+Sans:400,700|Catamaran:400,700|Suez+One&display=swap', false, null);

      wp_enqueue_style('ShopWP CSS', Assets\asset_path('prod/app.min.css'), false, filemtime(plugin_dir_path( __DIR__ ) . 'assets/prod/app.min.css'));
   }

}

add_action( 'login_enqueue_scripts', __NAMESPACE__ . '\\assets_login', 100);


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