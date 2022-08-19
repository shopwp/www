<?php

require 'vendor/autoload.php';

$sage_includes = [
   'lib/assets.php', // Scripts and stylesheets
   'lib/extras.php', // Custom functions
   'lib/setup.php', // Theme setup
   'lib/titles.php', // Page titles
   'lib/wrapper.php', // Theme wrapper class
   'lib/customizer.php', // Theme customizer
   'lib/custom/custom.php', // Custom
   'lib/filters/filters.php', // Filter
   'lib/actions/actions.php', // Actions
   'lib/ws/ws.php' // WS
];

foreach ($sage_includes as $file) {
   if (!($filepath = locate_template($file))) {
      trigger_error(sprintf(__('Error locating %s for inclusion', 'sage'), $file), E_USER_ERROR);
   }

   require_once $filepath;
}
unset($file, $filepath);

function wps_edd_payment_receipt_before($product_data)
{

   $payment = new EDD_Payment($product_data->ID);

   $purchases = false;
   foreach ($payment->downloads as $download) {
         $fs = edd_get_download($download['id']);

         $files = edd_get_download_files( $fs->ID);
         $wpshopify_files = $files[key($files)];
         $latest_version = edd_software_licensing()->get_download_version($fs->ID);

         $purchases[] = [
            'name' => $wpshopify_files['name'],
            'file' => $wpshopify_files['file'],
            'version' => $latest_version
         ];

   }

   echo '<div class="receipt-account-wrapper" style="margin-top: 15px;">';

   foreach ($purchases as $purchase) {
      echo '<a href="' . $purchase['file'] . '" class="btn btn-primary" download target="_blank"><svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="download" class="svg-inline--fa fa-download fa-w-16" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="currentColor" d="M216 0h80c13.3 0 24 10.7 24 24v168h87.7c17.8 0 26.7 21.5 14.1 34.1L269.7 378.3c-7.5 7.5-19.8 7.5-27.3 0L90.1 226.1c-12.6-12.6-3.7-34.1 14.1-34.1H192V24c0-13.3 10.7-24 24-24zm296 376v112c0 13.3-10.7 24-24 24H24c-13.3 0-24-10.7-24-24V376c0-13.3 10.7-24 24-24h146.7l49 49c20.1 20.1 52.5 20.1 72.6 0l49-49H488c13.3 0 24 10.7 24 24zm-124 88c0-11-9-20-20-20s-20 9-20 20 9 20 20 20 20-9 20-20zm64 0c0-11-9-20-20-20s-20 9-20 20 9 20 20 20 20-9 20-20z"></path></svg> Download ' . $purchase['name'] . ' v' . $purchase['version'] . '</a>';
   }

   echo '</div>';
}

add_action('edd_payment_receipt_before', 'wps_edd_payment_receipt_before');


add_action('edd_after_price_option', function () {
   echo '<small style="display:block;text-align:center;margin-top:-10px;">/per year</small>';
});

// Replaces the excerpt "Read More" text by a link
function new_excerpt_more($more)
{
   global $post;
   return '... <div class="moretag-wrapper"><a class="moretag btn-s" href="' . get_permalink($post->ID) . '">Read more</a></div>';
}
// add_filter('excerpt_more', 'new_excerpt_more');

function my_child_theme_edd_auto_register_email_subject($subject)
{
   return '🗝 ShopWP Pro Account';
}
add_filter('edd_auto_register_email_subject', 'my_child_theme_edd_auto_register_email_subject');

function wpshop_custom_excerpt_length($length)
{
   return 20;
}
add_filter('excerpt_length', 'wpshop_custom_excerpt_length', 999);


function is_admin_user($user) {

   if (is_array($user->roles) && in_array('administrator', $user->roles)) {
      return true;
   }
   
   return false;

}

function is_affiliate_only($user) {

   if (empty($user) || !isset($user->ID)) {
      return false;
   }

   $affiliate_id = affwp_get_affiliate_id($user->ID);
   $customer = new EDD_Customer($user->ID, true);

   if ($customer->email === NULL && $affiliate_id) {
      return true;
   }

   return false;

}


function is_affiliate() {

   $user = wp_get_current_user();
   $affiliate_id = affwp_get_affiliate_id( $user->ID );

   if ($affiliate_id) {
      return true;
   }

   return false;

}




function add_script_attributes($tag, $handle) {

   if ($handle !== 'WPS Vendor Commons' && $handle !== 'WPS Fonts' && $handle !== 'modernizr-js' && $handle !== 'ShopWP JS') {
      return $tag;
   }

   return str_replace(' src', ' defer="defer" src', $tag );

}

add_filter('script_loader_tag', 'add_script_attributes', 10, 2);




function pw_edd_payment_icon($icons) {

   $icons['/wp-content/uploads/2019/11/icon-mastercard.png'] = 'Mastercard (custom)';
   $icons['/wp-content/uploads/2019/11/icon-visa.png'] = 'Visa (custom)';
   $icons['/wp-content/uploads/2019/11/icon-ae.png'] = 'American Express (custom)';
   $icons['/wp-content/uploads/2019/11/icon-discover.png'] = 'Discover (custom)';
   $icons['/wp-content/uploads/2019/11/icon-paypal.png'] = 'PayPal (custom)';

   return $icons;

}

add_filter('edd_accepted_payment_icons', 'pw_edd_payment_icon', 99, 1);


function purchase_with_paypal_button_text($one, $label) {

   $chosen_gateway = edd_get_chosen_gateway();

   if ($chosen_gateway === 'paypalexpress') {
      return 'Purchase with PayPal';
   }

   if ($chosen_gateway === 'stripe') {
      return 'Purchase now';
   }

   return $label;

}

add_filter('edd_get_checkout_button_purchase_label', 'purchase_with_paypal_button_text', 10, 2);


add_filter( 'edd_subscription_can_update', function() {
    return true;
});

add_action('edd_before_checkout_cart', function() {

   if (is_user_logged_in()) {
      $current_user = wp_get_current_user();
      echo '<p class="wps-checkout-logged-in-as">👋 Hey, ' . $current_user->user_firstname . ' ' . $current_user->user_lastname . '. <a href="' . wp_logout_url('/') . '">Logout?</a></p>';
   } else {
      echo '<p class="wps-checkout-logged-in-as">Already have an account? <a href="/login/?redirect=checkout">Log in</a></p>';
   }

});



function edd_auto_register_email_body_custom($default_email_body, $first_name, $username, $password) {

   $default_email_body = str_replace("wpshop.io/wp-login.php", "wpshop.io/login", $default_email_body);

   return $default_email_body;
}

add_filter('edd_auto_register_email_body', 'edd_auto_register_email_body_custom', 10, 4);



function final_total_note() {
?>
<p id="edd_final_total_wrap">
	<span><?php _e( 'You will be charged: ', 'easy-digital-downloads' ); ?></span>
	<span class="edd_cart_amount" data-subtotal="<?php echo edd_get_cart_subtotal(); ?>" data-total="<?php echo edd_get_cart_total(); ?>"><?php edd_cart_total(); ?></span>
</p>
<?php
}
remove_action('edd_purchase_form_before_submit', 'edd_checkout_final_total', 999);
add_action( 'edd_purchase_form_before_submit', 'final_total_note', 999 );



function has_managed_plan_in_cart() {

   $cart_contents = edd_get_cart_contents();

   if (empty($cart_contents)) {
      return false;
   }

   $contents = array_filter($cart_contents, function($cart_item) {

      if ($cart_item['id'] === 209061 || $cart_item['id'] === 229781 || $cart_item['id'] === 203186 ) {
         return true;
      }

      if ($cart_item['options']['price_id'] == 7) {
         return true;
      }

      return false;

   });

   if (empty($contents)) {
      return false;
   }

   return true;

}

function add_managed_plan_note() {

   if (!has_managed_plan_in_cart()) {
      include(locate_template('components/managed-plugin-setup/view.php'));
   }
   
}

add_action('edd_purchase_form_after_cc_form', 'add_managed_plan_note');


function customize_product_name($product_name) {
   return str_replace("WP Shopify", "ShopWP Pro", $product_name);
}

add_filter('edd_get_cart_item_name', 'customize_product_name');























function sd_edd_checkout_field_myshopify_domain() {

   if (has_managed_plan_in_cart()) {
      $has_managed_plan = true;

   } else {
      $has_managed_plan = false;
   }

	?>
   
   <div id="myshopify-field">
      <label class="edd-label" for="sd-edd-checkout-field" style="margin-bottom: 0;">
         <?php _e( 'Your Shopify domain' ); ?> <?= $has_managed_plan ? '<span class="edd-required-indicator">*</span>' : ''; ?>
      </label>
   
      <span class="edd-description">
         <?php _e( 'This is your "myshopify.com" domain which looks like this: (yourstore.myshopify.com)' ); ?>
      </span>
   
      <input class="edd-input" type="text" name="sd_edd_checkout_field_myshopify_domain" id="sd-edd-checkout-field" placeholder="<?php _e( 'yourstore.myshopify.com' ); ?>" />
   </div>

	<?php

}

add_action( 'edd_purchase_form_user_info_fields' , 'sd_edd_checkout_field_myshopify_domain' );
 


function sd_edd_required_field($required_fields) {

   if (!has_managed_plan_in_cart()) {
      return $required_fields;
   }

   $required_fields['sd_edd_checkout_field_myshopify_domain'] = [
      'error_id'		   => 'invalid_custom_field',
      'error_message'	=> __( 'Please fill in the custom field' )      
   ];
 
   return $required_fields;

}

add_filter( 'edd_purchase_form_required_fields', 'sd_edd_required_field' );
 

 
function sd_validate_custom_edd_checkout_field($valid_data, $data) {

   if (!has_managed_plan_in_cart()) {
      return $required_fields;
   }
      
   if ( empty( $data['sd_edd_checkout_field_myshopify_domain'] ) ) {
      edd_set_error( 'invalid_custom_field', __( 'Please fill in the myshopify domain field' ) );
   }

}

add_action( 'edd_checkout_error_checks', 'sd_validate_custom_edd_checkout_field', 10, 2 );





add_action('edd_checkout_cart_item_title_after', function() {
   echo ' - <a style="font-size:14px;" href="/purchase/" target="_blank">Details</a>';
});





 
function sd_save_custom_fields( $payment_meta ){
 
	if ( isset( $_POST['sd_edd_checkout_field_myshopify_domain'] ) ){
		$payment_meta['sd_edd_checkout_field_myshopify_domain'] = sanitize_text_field( $_POST['sd_edd_checkout_field_myshopify_domain'] );
	}
 
	return $payment_meta;

}

add_filter( 'edd_payment_meta', 'sd_save_custom_fields');



function sd_add_custom_field_to_order_details( $payment_meta, $user_info ) {
	
   $custom_field = isset($payment_meta['sd_edd_checkout_field_myshopify_domain']) ? $payment_meta['sd_edd_checkout_field_myshopify_domain'] : 'Not set';
   
   ?>
	
   <div class="column-container">
		<div class="column">
			<strong><?php _e( 'Shopify domain' ); ?>:</strong>
			 <?php echo $custom_field; ?>
		</div>
	</div>

<?php

}

add_action( 'edd_payment_personal_details_list', 'sd_add_custom_field_to_order_details', 10, 2 );



function new_cpt_archive_title($title){

    if ( is_post_type_archive('download') ){
        $title = 'Extension plugins for ShopWP Pro';
        return $title;
    }

    return $title;
} 

add_filter( 'pre_get_document_title', 'new_cpt_archive_title', 9999 );




function tg_enable_strict_transport_security_hsts_header_wordpress() {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
}

add_action('send_headers', 'tg_enable_strict_transport_security_hsts_header_wordpress');









add_filter('edd_sl_download_changelog', function ($changelog) {
	return stripslashes($changelog);
});