<?php 

/**
 * Plugin Name: Custom WP Shopify Plugin Loader
 * Description: Custom WP Shopify Plugin Loader
 * Author:      WP Shopify
 * License:     GNU General Public License v3 or later
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

require_once( wp_normalize_path(ABSPATH).'wp-load.php');


// Basic security, prevents file from being loaded directly.
defined( 'ABSPATH' ) or die( 'Cheatin&#8217; uh?' );


function uri_has($needle) {
   return strpos($_SERVER['REQUEST_URI'], $needle) !== false;
}

add_filter( 'option_active_plugins', function($plugins) {

   if (is_admin()) {
      return $plugins;
   }


	if (!empty($_POST) && isset($_POST['edd_action']) && !empty($_POST['edd_action'])) {
      $is_edd = true;

    } else {

	if (!empty($_GET) && isset($_GET['edd_action']) && !empty($_GET['edd_action'])) {
	   $is_edd = true;
	} else {
	   $is_edd = false;
	}

}


	if (uri_has('customers/v1') || uri_has('wpshopify/v1') || uri_has('wpshopifyAPI/v1')) {
      return $plugins;
	}

   if ($_SERVER['REQUEST_URI'] === '/' && !$is_edd) {

      $plugins_to_deactivate = [
         'custom-post-type-permalinks/custom-post-type-permalinks.php',
         'affiliate-wp/affiliate-wp.php',
         'code-syntax-block/index.php',
         'easy-digital-downloads/easy-digital-downloads.php',
         'edd-auto-register/edd-auto-register.php',
         'edd-conditional-emails/edd-conditional-emails.php',
         'edd-invoices/edd-invoices.php',
         'edd-paypal-pro-express/edd-paypal-pro-express.php',
         'edd-recurring/edd-recurring.php',
         'edd-software-licensing/edd-software-licenses.php',
         'edd-stripe/edd-stripe.php',
         'edd-variable-pricing-descriptions/edd-variable-pricing-descriptions.php',
         'post-types-order/post-types-order.php',
         'preserved-html-editor-markup-plus/preserved_markup_plus.php',
         'redirection/redirection.php',
         'simple-page-ordering/simple-page-ordering.php',
         'social-warfare-pro/social-warfare-pro.php',
         'social-warfare/social-warfare.php',
         'svg-support/svg-support.php',
         'taxonomy-terms-order/taxonomy-terms-order.php',
         'wpshop-cpts/wpshop-cpts.php'
      ];

      foreach ($plugins as $key => $plugin) {

         if (in_array($plugin, $plugins_to_deactivate)) {
            unset($plugins[$key]);
         }
      }

   } else if (uri_has('/purchase')) {

      $plugins_to_deactivate = [
         'code-syntax-block/index.php',
         'affiliate-wp/affiliate-wp.php',
         'custom-post-type-permalinks/custom-post-type-permalinks.php',
         'edd-auto-register/edd-auto-register.php',
         'edd-conditional-emails/edd-conditional-emails.php',
         'edd-invoices/edd-invoices.php',
         'edd-paypal-pro-express/edd-paypal-pro-express.php',
         'edd-recurring/edd-recurring.php',
         'post-types-order/post-types-order.php',
         'preserved-html-editor-markup-plus/preserved_markup_plus.php',
         'redirection/redirection.php',
         'simple-page-ordering/simple-page-ordering.php',
         'social-warfare-pro/social-warfare-pro.php',
         'social-warfare/social-warfare.php',
         'svg-support/svg-support.php',
         'taxonomy-terms-order/taxonomy-terms-order.php',
         'wpshop-cpts/wpshop-cpts.php',
         'wp-shopify-pro/wp-shopify.php',
         'jwt-auth/jwt-auth.php',
         'jwt-whitelist/jwt-whitelist.php',
         'customers-account-addons/customers-account-addons.php'
      ];

      foreach ($plugins as $key => $plugin) {

         if (in_array($plugin, $plugins_to_deactivate)) {
            unset($plugins[$key]);
         }
      }

   } else if (uri_has('/checkout')) {

      $plugins_to_deactivate = [
         'advanced-custom-fields-pro/acf.php',
         'affiliate-wp/affiliate-wp.php',
         'custom-post-type-permalinks/custom-post-type-permalinks.php',
         'code-syntax-block/index.php',
         'post-types-order/post-types-order.php',
         'preserved-html-editor-markup-plus/preserved_markup_plus.php',
         'redirection/redirection.php',
         'simple-page-ordering/simple-page-ordering.php',
         'social-warfare-pro/social-warfare-pro.php',
         'social-warfare/social-warfare.php',
         'svg-support/svg-support.php',
         'taxonomy-terms-order/taxonomy-terms-order.php',
         'wpshop-cpts/wpshop-cpts.php',
         'wordpress-seo/wp-seo.php',
         'wp-shopify-pro/wp-shopify.php',
         'jwt-auth/jwt-auth.php',
         'jwt-whitelist/jwt-whitelist.php',
         'customers-account-addons/customers-account-addons.php'
      ];

      foreach ($plugins as $key => $plugin) {

         if (in_array($plugin, $plugins_to_deactivate)) {
            unset($plugins[$key]);
         }
      }

   } else if (uri_has('/login')) {

      $plugins_to_deactivate = [
         'custom-post-type-permalinks/custom-post-type-permalinks.php',
         'code-syntax-block/index.php',
         'post-types-order/post-types-order.php',
         'preserved-html-editor-markup-plus/preserved_markup_plus.php',
         'redirection/redirection.php',
         'simple-page-ordering/simple-page-ordering.php',
         'social-warfare-pro/social-warfare-pro.php',
         'social-warfare/social-warfare.php',
         'svg-support/svg-support.php',
         'taxonomy-terms-order/taxonomy-terms-order.php',
         'wpshop-cpts/wpshop-cpts.php',
         'wp-shopify-pro/wp-shopify.php'
      ];

      foreach ($plugins as $key => $plugin) {

         if (in_array($plugin, $plugins_to_deactivate)) {
            unset($plugins[$key]);
         }
      }      

   } else if ( uri_has('/faq')) {

      $plugins_to_deactivate = [
         'custom-post-type-permalinks/custom-post-type-permalinks.php',
         'affiliate-wp/affiliate-wp.php',
         'code-syntax-block/index.php',
         'easy-digital-downloads/easy-digital-downloads.php',
         'edd-auto-register/edd-auto-register.php',
         'edd-conditional-emails/edd-conditional-emails.php',
         'edd-invoices/edd-invoices.php',
         'edd-paypal-pro-express/edd-paypal-pro-express.php',
         'edd-recurring/edd-recurring.php',
         'edd-software-licensing/edd-software-licenses.php',
         'edd-stripe/edd-stripe.php',
         'edd-variable-pricing-descriptions/edd-variable-pricing-descriptions.php',
         'preserved-html-editor-markup-plus/preserved_markup_plus.php',
         'redirection/redirection.php',
         'social-warfare-pro/social-warfare-pro.php',
         'social-warfare/social-warfare.php',
         'svg-support/svg-support.php'
      ];

      foreach ($plugins as $key => $plugin) {

         if (in_array($plugin, $plugins_to_deactivate)) {
            unset($plugins[$key]);
         }
      }

   } else if (uri_has('/how')) {

      $plugins_to_deactivate = [
         'custom-post-type-permalinks/custom-post-type-permalinks.php',
         'affiliate-wp/affiliate-wp.php',
         'code-syntax-block/index.php',
         'easy-digital-downloads/easy-digital-downloads.php',
         'edd-auto-register/edd-auto-register.php',
         'edd-conditional-emails/edd-conditional-emails.php',
         'edd-invoices/edd-invoices.php',
         'edd-paypal-pro-express/edd-paypal-pro-express.php',
         'edd-recurring/edd-recurring.php',
         'edd-software-licensing/edd-software-licenses.php',
         'edd-stripe/edd-stripe.php',
         'edd-variable-pricing-descriptions/edd-variable-pricing-descriptions.php',
         'post-types-order/post-types-order.php',
         'preserved-html-editor-markup-plus/preserved_markup_plus.php',
         'redirection/redirection.php',
         'simple-page-ordering/simple-page-ordering.php',
         'social-warfare-pro/social-warfare-pro.php',
         'social-warfare/social-warfare.php',
         'svg-support/svg-support.php',
         'taxonomy-terms-order/taxonomy-terms-order.php',
      ];

      foreach ($plugins as $key => $plugin) {

         if (in_array($plugin, $plugins_to_deactivate)) {
            unset($plugins[$key]);
         }
      }

   } 

   return $plugins;

});