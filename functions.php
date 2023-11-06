<?php 

define('EDD_SLUG', 'extensions');

function shopwp_assets() {
	
	if (is_page('account')) {
		wp_enqueue_script('shopwp-account', get_stylesheet_directory_uri() . '/dist/account.js', ['wp-element'], '1', true);
		add_settings_script('shopwp-account', true);
	//   wp_enqueue_style('app', get_stylesheet_directory_uri() . '/dist/app.css');
	  return;
	} else {
		wp_enqueue_style('app', get_stylesheet_directory_uri() . '/dist/app.css');
		wp_enqueue_script('app', get_stylesheet_directory_uri() . '/dist/app.js', ['jquery', 'wp-element'], '', true);

		add_settings_script('app', true);
	}

	if (is_page_to_dequeue()) {
      dequeue_superfluous_assets();
   }

   if (is_page('checkout')) {
	wp_enqueue_script('checkout', get_stylesheet_directory_uri() . '/dist/checkout.js', ['jquery'], '', true);
   }
	
}

function customize_shopwp_settings($settings) {
	$settings['misc']['pluginsDistURL'] = '/wp-content/plugins/shopwp-pro/dist/';

	return $settings;
}

function remove_default_favicon() {
	exit;
}

function stringify_settings($settings)
{
	return "const swp = " . wp_json_encode($settings);
}

function add_settings_script($script_dep)
{
	$string_settings = stringify_settings(client_settings());

	wp_add_inline_script($script_dep, $string_settings, 'before');
}

function client_settings() {

	$rest_url = \get_rest_url();

	if (\is_ssl()) {
		$rest_url = str_replace("http://", "https://", $rest_url);
	}

	if ( is_user_logged_in() && !is_front_page() && !is_page('features') && function_exists('edd_software_licensing') ) {

		$current_user 		= wp_get_current_user();
		$sl               	= edd_software_licensing();
		$license_keys     	= format_license_keys($sl->get_license_keys_of_user($current_user->ID), $sl);

		$user = [
			'first' 		=> $current_user->user_firstname,
			'last' 			=> $current_user->user_lastname,
			'email' 		=> $current_user->data->user_email,
			'licenseKeys' 	=> false
		];

		if (is_page('support')) {
			$user['licenseKeys'] = $license_keys;
		}		

	} else {
		$user = false;
	}

	return [
		'api' => [
			'namespace' => 'swp/v8',
			'restUrl' => $rest_url,
			'nonce' => \wp_create_nonce('wp_rest'),
		],
		'misc' => [
			'siteUrl' 		=> \site_url(),
			'themeUrl' 		=> \get_stylesheet_directory_uri(),
			'isAdmin' 		=> \current_user_can('administrator'),
			'isLoggedIn' 	=> is_user_logged_in()
		],
		'user' => $user
	];
}

function handle_add_contact($request) {

	$sendData 		= $request->get_params();
	$email 			= $sendData['email'];

	if (empty($email)) {
		return \wp_send_json_error('You can\'t join without an email!');
	}

	$create_new_contact_resp = update_contact_by_email([
		'email' => $email,
		'properties'   => [
			'firstname' 			=> '',
			'lastname' 				=> '',
			'shopwp_license' 		=> '',
			'website' 				=> '',
			'email' 				=> $email,
			'lifecyclestage'		=> 'lead'
		]
	]);  	

	return hs_add_to_list('11', [$email]);

}


/*

Gets the user's ShopWP subscription tier (solo / agency) and status.

Matches up with the EDD_Subscription->get_status_label() method.

Possible status values:
- Active 
- Canceled
- Expired
- Pending 
- Failing
- Trialling
- Completed
- Lifetime (has no subscription expiration)

*/
function get_sub_tier_and_status($license) {

	$subscription_tier 					= '';
	$subscription_status 				= '';
	$subscription_maybe_future_churn 	= false;

	if (!empty($license)) {

		$sl = edd_software_licensing();
		$license = $sl->get_license($license, true);

		// This could be empty if the user entered the key incorrectly, or if we haven't synced our db from production
		if (empty($license)) {
			return [
				'tier' 					=> $subscription_tier,
				'status' 				=> $subscription_status,
				'maybe_future_churn' 	=> $subscription_maybe_future_churn
			];
		}

		$subscriptions = swp_get_subscriptions($license->user_id);

		if (empty($subscriptions) && $license->status === 'expired') {
			return [
				'tier' 					=> find_tier_from_price_id($license->price_id),
				'status' 				=> 'Canceled',
				'maybe_future_churn' 	=> $subscription_maybe_future_churn
			];
		}

		if (empty($subscriptions)) {
			
			if ($license->is_lifetime) {
				return [
					'tier' 					=> $subscription_tier,
					'status' 				=> 'Lifetime',
					'maybe_future_churn' 	=> $subscription_maybe_future_churn
				];
			}

		} else {

			$shopwp_pro_subscriptions = array_values(array_filter($subscriptions, function($subscription) {
				return $subscription->product_id === '35';
			}));

			// First subscription is always the latest
			if (!empty($shopwp_pro_subscriptions)) {

				$shopwp_pro_subscription = $shopwp_pro_subscriptions[0];

				$subscription_tier = find_tier_from_price_id($shopwp_pro_subscription->price_id);
				$subscription_status = ucfirst($shopwp_pro_subscription->status);

				/*
				
				If the user has an active subscription, but their license key isn't being used.
				
				*/
				if ($license->status === 'inactive' && !str_contains($subscription_status, 'Cancel')) {
					$subscription_maybe_future_churn = true;
				} else {
					$subscription_maybe_future_churn = false;
				}
			}		
		}

	}

	return [
		'tier' 					=> $subscription_tier,
		'status' 				=> $subscription_status,
		'maybe_future_churn' 	=> $subscription_maybe_future_churn
	];	
}

function handle_create_ticket($request) {

	$sendData 			= $request->get_params();
	$files 				= $request->get_file_params();
	$first_name 		= isset($sendData['firstName']) ? $sendData['firstName'] : '';
	$last_name 			= isset($sendData['lastName']) ? $sendData['lastName'] : '';
	$email 				= isset($sendData['email']) ? $sendData['email'] : '';
	$website 			= isset($sendData['website']) ? $sendData['website'] : '';
	$license 			= isset($sendData['license']) ? $sendData['license'] : '';
	$notes 				= isset($sendData['notes']) ? $sendData['notes'] : '';
	$systemInfo 		= empty($sendData['systemInfo']) ? '' : $sendData['systemInfo'];
	$topic 				= isset($sendData['topic']) ? $sendData['topic'] : '';
	$subject 			= ucfirst($first_name) . ' - ' . ucfirst($topic);
	$lifecyclestage 	= 'lead';

	/*
	
	If user passes a license key, we'll use that to find their active ShopWP Pro subscription.
	
	$subscription_tier 					= false;
	$subscription_status 				= false;
	$subscription_maybe_future_churn 	= false;

	*/
	$stuff = get_sub_tier_and_status($license);


	/*
	
	Checking the status of the passed in license key.
	
	*/
	if (!empty($license)) {
		if ($stuff['status'] === 'Active') {
			$lifecyclestage = 'customer';

		} else {
			$lifecyclestage = '119671112';
		}		
	}


	/*

	2. Either create or update contact inside Hubspot

	*/
	$properties = [
		'firstname' 							=> $first_name,
		'lastname' 								=> $last_name,
		'shopwp_license' 						=> $license,
		'website' 								=> $website,
		'email' 								=> $email,
		'lifecyclestage'						=> $lifecyclestage,
		'subscription_maybe_future_churn'		=> $stuff['maybe_future_churn']
	];	

	if (!empty($license)) {
		$properties['subscription_status'] 		= $stuff['status'];
		$properties['subscription_tier'] 		= $stuff['tier'];
	}

	$contact_id = update_contact_by_email([
		'email' 		=> $email,
		'properties'   	=> $properties
	]);

	if (empty($contact_id)) {
		return \wp_send_json_error(__('Unable to find contact to assign a ticket to. Please email us instead: hello@wpshop.io'));
	}

	/*

	5. Create the ticket

	*/
	if (!empty($contact_id)) {

		$ticket_result = hs_create_ticket([
			'subject' 				=> $subject,
			'shopwp_license_key' 	=> $license,
			'topic' 				=> $topic,
			'content' 				=> $notes,
			'shopwp_system_info' 	=> $systemInfo,
			'shopwp_website' 		=> $website,
			'contact_id'			=> $contact_id
		]);

		if (\is_wp_error($ticket_result)) {
			return \wp_send_json_error($ticket_result);
		}

		$ticket_result_body = \json_decode(\wp_remote_retrieve_body($ticket_result));

		/*

		6. Upload any files to the Hubspot media library and attach them to the ticket

		*/
		if (!empty($files) && !empty($ticket_result_body->id)) {

			$file_ids = hs_upload_attachments($files, $subject);

			if (is_wp_error($file_ids)) {
				return \wp_send_json_error($file_ids->get_error_message());
			}

			/*
			
			7. Assign the files to the ticket
			
			*/
			$attachment_ids = implode(';', $file_ids);

			$create_note_resp = hs_create_note($ticket_result_body->id, $attachment_ids);

			if (is_wp_error($create_note_resp)) {
				return \wp_send_json_error($create_note_resp->get_error_message());
			}

			$resp_note = \json_decode(\wp_remote_retrieve_body($create_note_resp));

			if (isset($resp_note->status) && $resp_note->status === 'error') {
				return \wp_send_json_error($resp_note->category . ' - ' . $resp_note->message);
			}
		}	

	} else {
		return \wp_send_json_error('Error creating ticket, contact id is empty.');
	}

	return \wp_send_json_success('Thanks! Your ticket has been submitted. We\'ll be in touch with you shortly.');
	
}

function custom_post_type_faqs() {

  $labels = [
    'name'                => _x('FAQs', 'Post Type General Name', 'wpshop'),
    'singular_name'       => _x('FAQ', 'Post Type Singular Name', 'wpshop'),
    'menu_name'           => __('FAQs', 'wpshop'),
    'parent_item_colon'   => __('Parent FAQ:', 'wpshop'),
    'new_item'            => __('Add New FAQ', 'wpshop'),
    'edit_item'           => __('Edit FAQ', 'wpshop'),
    'not_found'           => __('No FAQs found', 'wpshop'),
    'not_found_in_trash'  => __('No FAQs found in trash', 'wpshop')
  ];

  $args = [
    'label'               => __('post_type_faqs', 'wpshop'),
    'description'         => __('Custom Post Type for FAQs', 'wpshop'),
    'labels'              => $labels,
    'supports'            => ['title', 'category', 'editor'],
    'taxonomies'          => ['faq-category'],
    'hierarchical'        => false,
    'public'              => true,
    'show_ui'             => true,
	'show_in_rest'		  => true,
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
    'rewrite'             => [ 'with_front' => false ]
  ];

  register_post_type('faqs', $args);


  // Add new taxonomy, make it hierarchical (like categories)
	$labels = [
		'name'              => _x( 'FAQ Categories', 'taxonomy general name', 'textdomain' ),
		'singular_name'     => _x( 'FAQ Category', 'taxonomy singular name', 'textdomain' ),
		'search_items'      => __( 'Search FAQ Categories', 'textdomain' ),
		'all_items'         => __( 'All FAQ Categories', 'textdomain' ),
		'parent_item'       => __( 'Parent FAQ Category', 'textdomain' ),
		'parent_item_colon' => __( 'Parent FAQ Category:', 'textdomain' ),
		'edit_item'         => __( 'Edit FAQ Category', 'textdomain' ),
		'update_item'       => __( 'Update FAQ Category', 'textdomain' ),
		'add_new_item'      => __( 'Add New FAQ Category', 'textdomain' ),
		'new_item_name'     => __( 'New FAQ Category Name', 'textdomain' ),
		'menu_name'         => __( 'Categories', 'textdomain' ),
	];

	$args = [
		'hierarchical'      => true,
		'labels'            => $labels,
		'show_ui'           => true,
		'show_admin_column' => true,
		'query_var'         => true,
		'rewrite'           => ['slug' => 'faq-category', 'with_front' => false],
	];

	register_taxonomy( 'faq-category', ['faqs'], $args );

}




function customize_cart_product_name($product_name) {
   return str_replace("WP Shopify", "ShopWP Pro", $product_name);
}

function shopwp_edd_checkout_final_total() { ?>
	<p id="edd_final_total_wrap">
		<span class="edd_cart_amount">You will be charged <?php edd_cart_total(); ?>/year. Cancel anytime.</span>
	</p>
<?php }


function shopwp_edd_checkout_final_total_after() { ?>
	<p class="l-row purchase-after">
		<svg class="absolute h-6 w-6" width="24" height="25" viewBox="0 0 24 25" fill="none" xmlns="http://www.w3.org/2000/svg">
			<path d="M0 12.2174C0 5.58999 5.37258 0.217407 12 0.217407V0.217407C18.6274 0.217407 24 5.58999 24 12.2174V12.2174C24 18.8448 18.6274 24.2174 12 24.2174V24.2174C5.37258 24.2174 0 18.8448 0 12.2174V12.2174Z" fill="#bcf0bb"></path>
			<path d="M15.7707 8.69502L10.246 14.1386L8.22932 12.1309C8.12429 12.0481 7.95624 12.0481 7.87221 12.1309L7.26302 12.7311C7.17899 12.8139 7.17899 12.9795 7.26302 13.083L10.0779 15.8358C10.1829 15.9393 10.33 15.9393 10.435 15.8358L16.737 9.62643C16.821 9.54364 16.821 9.37805 16.737 9.27456L16.1278 8.69502C16.0438 8.59153 15.8757 8.59153 15.7707 8.69502Z" fill="#000"></path>
		</svg>
		<span>30-day <a href="/refunds-and-payment-terms" target="_blank">money back guarantee</a></span>
	</p>
<?php }


function enable_strict_transport_security_hsts_header() {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
}

function remove_slashes_from_changelog($changelog) {
	return stripslashes($changelog);
}

function dequeue_superfluous_assets() {

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

   wp_dequeue_script( 'app' );
   wp_dequeue_script( 'shopwp-runtime' );
   wp_dequeue_script( 'shopwp-vendors-public' );
   wp_dequeue_script( 'shopwp-public' );

   wp_deregister_script( 'app' );
   wp_deregister_script( 'shopwp-runtime' );
   wp_deregister_script( 'shopwp-vendors-public' );
   wp_deregister_script( 'shopwp-public' );
}

function is_page_to_dequeue() {
   return is_page('checkout') || is_page('purchase-confirmation') || is_page('account');
}


function remove_duplicates_from_cart($cart) {
	$unique = [];

	foreach ($cart as $value)
	{
		$unique[$value['id']] = $value;
	}

	$unique = array_values($unique);

	return $unique;
}


function shopwp_edd_checkout_form_shortcode( $atts, $content = null ) {
	
	$payment_mode 	= edd_get_chosen_gateway();
	$form_action  	= edd_get_checkout_uri( 'payment-mode=' . $payment_mode );
	$cart_stuff 	= edd_get_cart_contents();

	ob_start();
		echo '<div id="edd_checkout_wrap"><div class="shopwp-checkout-inner">';
		if ( edd_get_cart_contents() || edd_cart_has_fees() ) : edd_checkout_cart(); ?>
			<div id="edd_checkout_form_wrap" class="edd_clearfix">


				<a href="/" class="logo">

					<svg width="140" xmlns="http://www.w3.org/2000/svg" xml:space="preserve" id="Layer_1" x="0" y="0" style="enable-background:new 0 0 733 191" version="1.1" viewBox="0 0 733 191"><style>.st0{fill:#fff}</style><circle cx="91.6" cy="95.4" r="88.7" class="st0"/><path d="M78 54.8c2.7-6.8 8.6-11.5 15.4-11.5S106 48 108.8 54.8h7.2c-3.1-11.3-12-19.4-22.5-19.4S74.1 43.6 71 54.8h7zM138.4 65.9H46.6c-1.9 21.4-3.6 42.8-4.9 64.2.3 2.7 2.1 7.4 9.8 8H133.3c7.7-.6 9.5-5.2 9.8-8-1.1-21.5-2.8-42.8-4.7-64.2zm-18.4 38c-2.1 6.6-5.9 12.1-11.2 15.8-4.8 3.5-10.6 5.3-16.4 5.3h-.2c-5.7-.1-11.4-1.9-16-5.3-5.2-3.8-9-9.2-11.2-15.8l-.6-1.9h12l.3.7c2.8 6.9 8.9 11.3 15.9 11.3h.3c6.9-.1 12.9-4.4 15.6-11.3l.3-.7h12l-.8 1.9z"/><path d="M251.1 133.6c-9.9 0-21-3.3-30.4-10.7l8.6-13.2c7.7 5.6 15.7 8.5 22.4 8.5 5.8 0 8.5-2.1 8.5-5.3v-.3c0-4.4-6.9-5.8-14.7-8.2-9.9-2.9-21.2-7.5-21.2-21.3v-.3c0-14.4 11.6-22.5 25.9-22.5 9 0 18.8 3 26.5 8.2l-7.7 14c-7-4.1-14-6.6-19.2-6.6-4.9 0-7.4 2.1-7.4 4.9v.2c0 4 6.7 5.8 14.4 8.5 9.9 3.3 21.4 8.1 21.4 21v.3c0 15.7-11.7 22.8-27.1 22.8zM337.5 132.3V92.8c0-9.5-4.5-14.4-12.2-14.4s-12.6 4.9-12.6 14.4v39.5h-20.1V35.8h20.1v35.7c4.6-6 10.6-11.4 20.8-11.4 15.2 0 24.1 10.1 24.1 26.3v45.9h-20.1zM409.8 133.9c-22 0-38.2-16.3-38.2-36.6V97c0-20.4 16.4-36.9 38.5-36.9 22 0 38.2 16.3 38.2 36.6v.3c0 20.4-16.4 36.9-38.5 36.9zM428.5 97c0-10.4-7.5-19.6-18.6-19.6-11.5 0-18.4 8.9-18.4 19.3v.3c0 10.4 7.5 19.6 18.6 19.6 11.5 0 18.4-8.9 18.4-19.3V97zM505.1 133.6c-10.7 0-17.3-4.9-22.1-10.6v30.4h-20.1v-92H483v10.2c4.9-6.6 11.6-11.5 22.1-11.5 16.5 0 32.3 13 32.3 36.6v.3c-.1 23.7-15.5 36.6-32.3 36.6zm12.1-36.9c0-11.8-7.9-19.6-17.3-19.6s-17.2 7.8-17.2 19.6v.3c0 11.8 7.8 19.6 17.2 19.6 9.4 0 17.3-7.7 17.3-19.6v-.3zM623.5 132.9H618l-20.1-57.8-20.2 57.8h-5.6l-24.7-68h7.3l20.4 59.3 20.4-59.5h5.2l20.4 59.5 20.4-59.3h7l-25 68zM695.3 133.9c-13.5 0-22.2-7.7-27.8-16.1v35.7H661V64.9h6.5V80c5.8-8.9 14.4-16.7 27.8-16.7 16.3 0 32.8 13.1 32.8 35v.3c0 22-16.6 35.3-32.8 35.3zm25.8-35.3c0-17.7-12.3-29.1-26.5-29.1-14 0-27.5 11.8-27.5 29v.3c0 17.3 13.5 29 27.5 29 14.7 0 26.5-10.7 26.5-28.8v-.4z" class="st0"/></svg> 
				</a>


				<?php do_action( 'edd_before_purchase_form' ); ?>

				<form id="edd_purchase_form" class="edd_form" action="<?php echo esc_url( $form_action ); ?>" method="POST">

					<div class="login-stuff">
					<?php if (is_user_logged_in()) {
						$current_user = wp_get_current_user();
						echo '<p class="checkout-logged-in-as">👋 Hey, ' . $current_user->user_firstname . ' ' . $current_user->user_lastname . '. <a href="' . wp_logout_url('/checkout') . '">Logout?</a></p>';
					} else {
						echo '<p class="checkout-logged-in-as">Already have an account? <a href="/login/?redirect=checkout">Log in</a></p>';
					} ?>
					</div>

					<?php
					
					// Discount code field
					do_action( 'edd_checkout_form_top' );	

					?>					

					<?php if ( edd_is_ajax_disabled() && ! empty( $_REQUEST['payment-mode'] ) ) {
						do_action( 'edd_purchase_form' );
					} elseif ( edd_show_gateways() ) {
						do_action( 'edd_payment_mode_select'  );
					} else {
						do_action( 'edd_purchase_form' );
					}

					/**
					 * Hooks in at the bottom of the checkout form
					 *
					 * @since 1.0
					 */
					do_action( 'edd_checkout_form_bottom' );
					
					?>
				</form>
				
				<div class="l-row l-row-center checkout-logos">
					<svg xmlns="http://www.w3.org/2000/svg" width="100" viewBox="0 0 500 142.8" style="enable-background:new 0 0 500 142.8" xml:space="preserve"><path d="M107.4 27.1c-.1-.7-.7-1.1-1.2-1.1s-10.4-.2-10.4-.2-8.3-8-9.1-8.9c-.8-.8-2.4-.6-3-.4 0 0-1.6.5-4.2 1.3-.4-1.4-1.1-3.1-2-4.9-2.9-5.6-7.3-8.6-12.5-8.6-.4 0-.7 0-1.1.1-.2-.2-.3-.4-.5-.5C61.1 1.5 58.2.3 54.7.4 48 .6 41.3 5.5 35.8 14.1c-3.8 6.1-6.7 13.7-7.6 19.6-7.7 2.4-13.1 4.1-13.3 4.1-3.9 1.2-4 1.3-4.5 5C10.2 45.6 0 124.5 0 124.5l85.6 14.8 37.1-9.2c-.1 0-15.2-102.3-15.3-103zm-32.2-7.9c-2 .6-4.2 1.3-6.6 2.1 0-3.4-.5-8.2-2-12.2 5.1.8 7.6 6.6 8.6 10.1zm-11.1 3.4c-4.5 1.4-9.4 2.9-14.3 4.4 1.4-5.3 4-10.5 7.2-14 1.2-1.3 2.9-2.7 4.8-3.5 2 3.9 2.4 9.4 2.3 13.1zM54.9 4.9c1.6 0 2.9.3 4 1.1-1.8.9-3.6 2.3-5.2 4.1-4.3 4.6-7.6 11.7-8.9 18.6-4.1 1.3-8.1 2.5-11.7 3.6C35.5 21.4 44.6 5.2 54.9 4.9z" style="fill:#95bf47"/><path d="M106.2 26c-.5 0-10.4-.2-10.4-.2s-8.3-8-9.1-8.9c-.3-.3-.7-.5-1.1-.5v122.9l37.1-9.2s-15.1-102.3-15.2-103c-.2-.7-.8-1.1-1.3-1.1z" style="fill:#5e8e3e"/><path d="m65 45.1-4.3 16.1s-4.8-2.2-10.5-1.8c-8.4.5-8.4 5.8-8.4 7.1.5 7.2 19.4 8.8 20.5 25.7.8 13.3-7 22.4-18.4 23.1-13.6.7-21.1-7.3-21.1-7.3l2.9-12.3s7.6 5.7 13.6 5.3c3.9-.2 5.4-3.5 5.2-5.7-.6-9.4-16-8.8-17-24.3-.8-13 7.7-26.1 26.5-27.3 7.3-.5 11 1.4 11 1.4z" style="fill:#fff"/><path d="M172.9 79.4c-4.3-2.3-6.5-4.3-6.5-7 0-3.4 3.1-5.6 7.9-5.6 5.6 0 10.6 2.3 10.6 2.3l3.9-12s-3.6-2.8-14.2-2.8c-14.8 0-25.1 8.5-25.1 20.4 0 6.8 4.8 11.9 11.2 15.6 5.2 2.9 7 5 7 8.1 0 3.2-2.6 5.8-7.4 5.8-7.1 0-13.9-3.7-13.9-3.7l-4.2 12s6.2 4.2 16.7 4.2c15.2 0 26.2-7.5 26.2-21-.1-7.3-5.6-12.5-12.2-16.3zM233.5 54.1c-7.5 0-13.4 3.6-17.9 9l-.2-.1 6.5-34H205l-16.5 86.6h16.9L211 86c2.2-11.2 8-18.1 13.4-18.1 3.8 0 5.3 2.6 5.3 6.3 0 2.3-.2 5.2-.7 7.5l-6.4 33.9h16.9l6.6-35c.7-3.7 1.2-8.1 1.2-11.1.1-9.6-4.9-15.4-13.8-15.4zM285.7 54.1c-20.4 0-33.9 18.4-33.9 38.9 0 13.1 8.1 23.7 23.3 23.7 20 0 33.5-17.9 33.5-38.9.1-12.1-7-23.7-22.9-23.7zm-8.3 49.7c-5.8 0-8.2-4.9-8.2-11.1 0-9.7 5-25.5 14.2-25.5 6 0 8 5.2 8 10.2 0 10.4-5.1 26.4-14 26.4zM352 54.1c-11.4 0-17.9 10.1-17.9 10.1h-.2l1-9.1h-15c-.7 6.1-2.1 15.5-3.4 22.5l-11.8 62h16.9l4.7-25.1h.4s3.5 2.2 9.9 2.2c19.9 0 32.9-20.4 32.9-41 0-11.4-5.1-21.6-17.5-21.6zM335.8 104c-4.4 0-7-2.5-7-2.5l2.8-15.8c2-10.6 7.5-17.6 13.4-17.6 5.2 0 6.8 4.8 6.8 9.3 0 11-6.5 26.6-16 26.6zM393.7 29.8c-5.4 0-9.7 4.3-9.7 9.8 0 5 3.2 8.5 8 8.5h.2c5.3 0 9.8-3.6 9.9-9.8 0-4.9-3.3-8.5-8.4-8.5zM370 115.5h16.9l11.5-60h-17zM441.5 55.4h-11.8l.6-2.8c1-5.8 4.4-10.9 10.1-10.9 3 0 5.4.9 5.4.9l3.3-13.3s-2.9-1.5-9.2-1.5c-6 0-12 1.7-16.6 5.6-5.8 4.9-8.5 12-9.8 19.2l-.5 2.8h-7.9l-2.5 12.8h7.9l-9 47.4h16.9l9-47.4h11.7l2.4-12.8zM482.3 55.5S471.7 82.2 467 96.8h-.2c-.3-4.7-4.2-41.3-4.2-41.3h-17.8l10.2 55.1c.2 1.2.1 2-.4 2.8-2 3.8-5.3 7.5-9.2 10.2-3.2 2.3-6.8 3.8-9.6 4.8l4.7 14.4c3.4-.7 10.6-3.6 16.6-9.2 7.7-7.2 14.9-18.4 22.2-33.6L500 55.5h-17.7z"/></svg>

					<svg xmlns="http://www.w3.org/2000/svg" width="120" viewBox="0 0 376.3 117"><path fill="black" clip-rule="evenodd" d="M116.2 51.1c-1.9 5.8-3.9 11.6-5.8 17.4-.6 1.8-1.2 3.7-1.8 5.5-.2.5-.2 1.2-.9 1.3-.9.1-.8-.8-1-1.3-2.8-8.4-5.5-16.9-8.2-25.3-.4-1.2-.8-2.4-1.3-3.5-1-2.3-2.5-3.8-5.2-3.9-.6 0-1.5.2-1.4-.9.1-1.2 1-.7 1.6-.7h14.7c.5 0 1.3-.4 1.4.7.1 1.2-.8.9-1.4 1-3.5.5-4.2 1.5-3.2 4.9 1.8 5.6 3.6 11.3 5.6 17.3 1.5-4.4 2.9-8.5 4.2-12.5 1.1-3.4 2.3-6.8 3.4-10.1.2-.6.2-1.3 1.1-1.3 1-.1 1.1.7 1.3 1.3 2.2 6.8 4.5 13.5 6.7 20.3.2.6.2 1.2.9 1.8 1.5-4.3 2.9-8.5 4.3-12.8.6-1.7 1.3-3.4 1.6-5.2.4-2.3-.2-3.2-2.6-3.6-.8-.2-2.3.5-2.2-1 .1-1.5 1.5-.7 2.3-.7 3.7-.1 7.4-.1 11.1 0 .6 0 1.5-.4 1.5.8 0 1-.8.9-1.3.9-3.7 0-5.3 2.4-6.3 5.5l-9 26.7c-.1.2-.1.5-.2.7-.2.5-.1 1.2-.9 1.3-.9.1-.9-.8-1-1.3l-6.9-21c-.2-.6-.4-1.2-.6-1.7-.2-.6-.4-.6-.5-.6zM322.8 69.4v-4.3c0-.5-.2-1.1.6-1.1.7-.1 1 .2 1.1.9.3 1.4.7 2.7 1.6 3.8 2.1 2.9 5 4 8.5 3.5 1.5-.2 2.7-1.1 3.2-2.6.5-1.6-.1-2.8-1.2-3.9-1.4-1.3-3.1-2.1-4.8-2.9-1.8-.9-3.7-1.6-5.3-2.7-3-2.1-4.3-5.4-3.4-8.3.9-2.8 3.9-5 7.3-5.2 2.2-.1 4.3.1 6.1 1.4 1 .7 1.4.4 1.7-.6.2-.6.5-1 1.2-.9.9.1.7.8.7 1.3v6.4c0 .6.4 1.5-.7 1.7-1.2.2-1.1-.7-1.3-1.4-.8-3-2.5-5-5.7-5.4-1.1-.1-2.1 0-3.1.5-2.3 1.1-2.7 3.5-.8 5.2 1.2 1.1 2.6 1.6 4 2.4 1.7.9 3.5 1.7 5.2 2.6 3.5 1.9 5.2 4.9 4.7 8-.6 3.7-3.6 6.5-7.5 7.1-2.9.5-5.7 0-8.1-1.7-1.2-.8-1.7-.6-2.1.7-.2.6-.3 1.5-1.3 1.2-.9-.2-.5-1-.5-1.6-.2-1.4-.1-2.7-.1-4.1zM347.3 69.5V65c0-.5-.1-1 .7-1 .7 0 .8.3 1 .9.3 1 .6 2.1 1.1 3 1.7 3.2 5.2 5 8.8 4.4 1.6-.2 2.9-1 3.4-2.6.6-1.7-.1-3.1-1.4-4.2-1.9-1.6-4.2-2.4-6.4-3.5-1.4-.7-2.8-1.3-4.1-2.3-2.4-2.1-3.8-4.6-2.8-7.8.9-3.3 3.5-4.8 6.7-5.2 2.3-.4 4.7 0 6.7 1.3.9.6 1.5.6 1.8-.5.2-.7.6-1.1 1.3-.9.7.2.5.8.5 1.2v7c0 .6 0 1.1-.8 1.2-.7 0-.9-.3-1.1-1-.8-3.1-2.3-5.4-5.8-5.9-1.2-.2-2.3 0-3.3.5-2.2 1.1-2.5 3.4-.8 5.1.8.7 1.7 1.2 2.7 1.7 2.2 1.1 4.4 2.1 6.5 3.3 3.6 1.9 5.3 5 4.7 8.2-.6 3.6-3.7 6.4-7.6 7-2.8.4-5.5 0-7.9-1.7-1.1-.8-1.7-.7-2.1.6-.2.6-.3 1.5-1.3 1.3-1.1-.2-.5-1.1-.6-1.7 0-1.4.1-2.6.1-3.9zM220.8 48.1c-6.2-1.4-12.5-.5-18.7-.7-.5 0-1.4-.3-1.4.7 0 1.1.8.7 1.4.8 1.8.2 3.4.8 3.5 2.9.5 5.9.5 11.8 0 17.6-.1 1.5-1.1 2.4-2.6 2.7-2.8.6-5.2-.4-7.3-2-2.8-2.1-4.8-4.9-7.4-7.5 1-.3 1.7-.5 2.3-.7 4.1-1.6 6.2-5.4 4.8-9.1-1.2-3.3-3.9-4.7-7.1-5.1-5.4-.8-10.9-.2-16.3-.3-.6 0-.9.2-.9.8 0 .8.6.6 1.1.7 3.3.6 3.8.9 4 4.3.3 5 .3 10 0 15-.2 3.1-.8 3.6-3.8 4.1-.5.1-1.2-.2-1.2.8s.8.8 1.3.8h13c.5 0 1.4.4 1.5-.6.1-1.2-.8-.8-1.4-.9-2.7-.4-3.3-1-3.6-3.7-.2-1.4-.2-2.9-.2-4.3 0-.5-.3-1.2.5-1.5.7-.2 1.1.3 1.5.8 1.7 2.1 3.6 4.1 5 6.4 2 3.2 4.6 4.2 8.4 4 7.2-.4 14.5.3 21.8-.4 6.1-.6 10.7-4.2 12-9.2 1.8-7.8-2.3-14.6-10.2-16.4zm-39.3 9v-1.9c0-1.2.1-2.5 0-3.7-.1-1.4.4-1.8 1.7-1.6.6.1 1.1 0 1.7 0 3.2.1 5.1 2 5.1 5.2 0 3-1.8 5.1-4.8 5.4-3.7.3-3.7.3-3.7-3.4zM223 68c-1.7 2.8-6.8 4.3-10 3.3-1.1-.3-1.4-1.1-1.5-2.1-.4-3.2 0-6.5-.2-9.2v-8.7c0-1.1.3-1.7 1.5-1.5.4.1.9 0 1.3 0 3.9-.1 7.7.4 9.5 4.5 2.1 4.6 2.1 9.3-.6 13.7zM317.2 66.6c-1.1-.2-.9 1-1.1 1.6-.5 1.7-1.6 2.8-3.4 3-2.3.4-4.6.3-6.9.1-1.3-.1-2.1-.9-2.5-2.2-.6-2.4-.3-4.7-.3-7.1 0-.3 0-.6.4-.7 2.5-1 5.8.8 6.2 3.4.1.5-.2 1.2.8 1.2s.8-.8.8-1.4v-9c0-.5.4-1.3-.7-1.4-1.1-.1-.8.7-.9 1.3-.2 1.2-.3 2.5-1.8 3-1.4.4-2.7.3-4.1.4-.8 0-.7-.5-.7-1V51c0-.8.3-1.1 1.1-1.1 1.6.1 3.1.1 4.7 0 3 0 5.5.6 6 4.2.1.4.3.8.9.8.8-.1.5-.7.5-1.1-.1-1.6-.3-3.2-.3-4.9 0-1.1-.4-1.5-1.5-1.5h-20.5c-.5 0-1.4-.3-1.4.7-.1 1 .8.8 1.3.9 2 .1 3.5.9 3.6 3.1.3 5.7.3 11.4 0 17.1-.1 2.4-1.9 3.5-4.4 3.1-1.4-.2-2.6-.8-3.8-1.6-3.1-2.1-5.3-5.2-8.1-8 1.6-.5 2.9-.9 4.1-1.6 4.7-3 4.6-9.2-.2-12-1.3-.8-2.7-1.2-4.2-1.4-5.1-.5-10.3-.1-15.4-.2-.6 0-1.4-.3-1.4.7 0 1.1.8.7 1.4.8 2.8.5 3.6 1.2 3.6 4 .1 5.1.1 10.3 0 15.4 0 2.7-.9 3.5-3.6 3.9-.6.1-1.6-.3-1.4.9.1 1 .9.6 1.5.6h12.8c.6 0 1.5.4 1.5-.8 0-1-.9-.7-1.4-.8-2.7-.4-3.4-1.1-3.7-3.9-.1-1.4-.1-2.8-.1-4.1 0-.5-.3-1.2.3-1.5.8-.3 1.2.3 1.7.8 2.4 3 4.9 6.1 7.3 9.1.5.7 1 1.1 2 1.1h30.3c.9 0 1.4-.1 1.4-1.2 0-1.4.3-2.9.3-4.3-.3-.4.3-1.4-.7-1.6zm-42.3-6.2c-.6-.2-.5-.6-.5-1v-4.3c0-.4-.1-.9 0-1.3.5-1.3-1.4-3.6 1.1-3.9 4.5-.5 6.7.9 7.2 4.1.6 4.7-3.3 7.9-7.8 6.4zM261.4 46.4c-1.4-4.1-4.7-5.8-8.6-6.6-3.5-.7-7-.2-9.9-.3h-8.8c-.6 0-1.3-.3-1.3.8 0 1 .8.8 1.3.9.2 0 .4 0 .6.1 2.8.3 3.9 1.4 4.1 4.2.6 7.1.5 14.1.1 21.2-.3 4.6-.7 4.9-5.2 5.7-.6.1-.9.2-.9.9s.4.8 1 .8h16.3c.6 0 1-.2 1-.8 0-.7-.4-.8-1-.8h-.6c-3-.3-3.9-1.1-4.5-4.1-.4-2.4-.1-4.8-.3-7.1-.1-1.5.3-2.1 1.9-2 3.2.1 6.4.1 9.4-1.2 4.7-2.3 7-7 5.4-11.7zm-11.1 9.7c-5.5.8-5.3.8-5.3-4.7v-2.1c0-1.9-.1-3.8-.2-5.6 0-1.1.2-1.4 1.3-1.3 1.2.1 2.3 0 3.5 0 3.6.1 5.9 2.5 6.1 6.3.2 4.3-1.6 6.9-5.4 7.4zM162.4 48.8c-6.3-3.6-12.6-3.4-18.6.7-7.8 5.4-8.1 15.9-.6 21.8 3.1 2.4 6.6 3.6 10.7 3.6 2.1 0 4.4-.3 6.5-1.3 5.1-2.3 8.5-6 9.1-11.7.5-5.8-2.2-10.2-7.1-13.1zm.1 17.7c-1.6 3.9-4.8 6-8.9 6-4 0-7.1-2.1-8.7-6-1.6-3.9-1.6-7.9.1-11.9 1.6-3.7 4.4-5.7 8.5-5.7 4.1-.1 7 1.8 8.8 5.5.9 2 1.3 4 1.3 6.2 0 2-.3 4-1.1 5.9z"></path><g fill="black" clip-rule="evenodd"><path d="M64.8 40.9c-.8-1.6-1.4-3.2-1.2-5 .4-3.4 2.5-5.3 5.9-5.9-16-15.1-42.7-10.2-52.4 6.8h1.5c3.1-.1 6.1-.2 9.2-.4 1.1-.1 2 .2 2 1.4.1 1.2-.9 1.5-1.9 1.6-.6 0-1.3.1-1.9.1-1.1 0-1.5.2-1.1 1.5C29 53.1 33 65.2 37.1 77.3c.7-.5.7-1.1.8-1.6 2.2-6.5 4.3-13 6.6-19.5.4-1.1.3-2-.1-3.1-1.4-3.6-2.8-7.1-3.9-10.7-.6-2.1-1.6-3.2-3.9-3-1 .1-2.2-.2-2.1-1.5.1-1.5 1.2-1.5 2.4-1.4 4.5.4 9 .4 13.5.3 1.4-.1 2.7-.2 4.1-.3 1-.1 1.6.4 1.6 1.4.1.9-.5 1.4-1.4 1.5-.7.1-1.5.2-2.2.2-1.4 0-1.7.4-1.2 1.8 2.6 7.6 5.1 15.1 7.7 22.7 1.5 4.3 2.9 8.7 4.4 13 2.2-6.3 4.2-12.7 5.9-19 1.1-4.2.3-8.2-1.7-12-.9-1.8-1.9-3.5-2.8-5.2z"></path><path d="M87.1 53.8c-.8-21.5-20.2-39.4-42-38.7-23.4.8-40.8 19.6-40 43.3.7 21.6 20.2 39.6 42 38.8 23.5-.8 40.8-19.7 40-43.4zM46 95.4C24.4 95.3 6.9 77.7 7 56.1 7 34.5 24.6 17 46.1 17c21.6 0 39.2 17.6 39.2 39.2-.1 21.6-17.7 39.2-39.3 39.2z"></path></g><path fill="black" clip-rule="evenodd" d="M46.7 59.3c2.7 7.2 5.1 14 7.6 20.8 1 2.8 2 5.5 3 8.3.3.8.2 1.2-.7 1.4-6.4 1.9-12.8 2.1-19.3.5-1.1-.3-1.3-.6-.9-1.6C40 79 43.3 69.3 46.7 59.3zM30.6 87.6C13.8 80 6.2 58.6 14 42c5.6 15.2 11.1 30.3 16.6 45.6zM77.7 40.6c9 18.4.1 38.2-13.3 45.4-.5-.4-.1-.8.1-1.2 3.6-10.3 7.1-20.7 10.7-31 1.3-3.8 2.1-7.7 2.1-11.8-.1-.5-.6-1.1.4-1.4z"></path><path fill="black" clip-rule="evenodd" d="M77.7 40.6c-.6.4-.1 1.1-.4 1.5-.3-.7-.4-1.5-.2-2.3.4.1.5.4.6.8z"></path></svg>
				</div>
				
				<div class="checkout-footer">
					<p><b>Please note:</b> Our <a href="/refunds-and-payment-terms" target="_blank">30-day money back guarantee</a> applies to all purchases regardless of tier or type. Discount prices will only apply to the first term. All ShopWP plans will renew automatically each year at regular price using the payment method you provide today. If you need to update the payment method or cancel your plan you can do so by logging into your ShopWP account and clicking "Subscriptions". By purchasing you agree to the ShopWP <a href="/terms-of-service/" target="_blank">Terms of Service</a>.</p>
					<p>Copyright &copy; <?php echo date("Y"); ?> ShopWP</p>
				</div>
				<?php 
				
				do_action( 'edd_after_purchase_form' ); 
				
				// do_action( 'edd_checkout_form_top' );
				
				?>
			</div><!--end #edd_checkout_form_wrap-->
		<?php
		else:
			/**
			 * Fires off when there is nothing in the cart
			 *
			 * @since 1.0
			 */
			do_action( 'edd_cart_empty' );
		endif;
		echo '</div></div><!--end #edd_checkout_wrap-->';

	return ob_get_clean();
}


function redirect_empty_cart() {
	$cart 			= function_exists( 'edd_get_cart_contents' ) ? edd_get_cart_contents() : false;
	$is_checkout 	= function_exists( 'edd_is_checkout' ) ? edd_is_checkout() : false;

	if (!$is_checkout) {
		return;
	}

	if (empty($cart)) {
		wp_redirect(site_url('purchase'), 301 ); 
		exit;
	}
}


function block_wp_admin() {
	if ( is_admin() && ! current_user_can( 'administrator' ) && ! ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
		wp_safe_redirect( home_url() );
		exit;
	}
}


function redirect_logged_in_users_to_account() {

	if ( !is_user_logged_in() && ( strpos($_SERVER['REQUEST_URI'], '/account') !== false )) {
        wp_redirect(site_url('login'));
        exit;
	} 

    if ( is_user_logged_in() && ( strpos($_SERVER['REQUEST_URI'], '/login') !== false )) {
        wp_redirect(site_url('account'));
        exit;
	} 

	// if ( !is_user_logged_in() && ( strpos($_SERVER['REQUEST_URI'], '/purchase-confirmation') !== false )) {
    //     wp_redirect(site_url('login'));
    //     exit;
	// } 

	if (isset($_SERVER['HTTP_REFERER'])) {
		// If the user just logged in, but was told to redirect to the checkout page from the login form ...
		if (strpos($_SERVER['HTTP_REFERER'], '?redirect=checkout') !== false && strpos($_SERVER['REQUEST_URI'], '/account') !== false) {
			wp_redirect(site_url('checkout'));
			exit;
		}

		if (strpos($_SERVER['HTTP_REFERER'], '?redirect=support') !== false && strpos($_SERVER['REQUEST_URI'], '/account') !== false) {
			wp_redirect(site_url('support'));
			exit;
		}
	}
	
}


function after_shopwp_purchase($order_id, $payment, $customer) {

	$user_id 						= $customer->user_id;
	$email 							= $payment->user_info['email'];
	$wp_user 						= get_user_by('email', $email);
	$EDD_Recurring_Subscriber 		= new EDD_Recurring_Subscriber($customer->user_id, true);
	$subs 							= $EDD_Recurring_Subscriber->get_subscriptions(35);

	/*
	
	The first sub is always the latest sub. There should always been at least one since this runs after purchasing a sub.
	
	*/
	if (empty($subs)) {
		error_log('ShopWP Error: A subscription was empty after purchasing for some reason. Skipped updating HS info for user: ' . $email);
		return;
	}

	$subscription 					= $subs[0];
	$EDD_Subscription 				= new EDD_Subscription( absint($subscription->id) );
	$converted_sub 					= object_to_array($EDD_Subscription);

	$converted_sub['card_info'] 												= [];
	$converted_sub['card_info']['billing_details']								= [];
	$converted_sub['card_info']['billing_details']['address']					= [];
	$converted_sub['card_info']['billing_details']['address']['state'] 			= $payment->user_info['address']['state'];
	$converted_sub['card_info']['billing_details']['address']['city'] 			= $payment->user_info['address']['city'];
	$converted_sub['card_info']['billing_details']['address']['line1'] 			= $payment->user_info['address']['line1'];
	$converted_sub['card_info']['billing_details']['address']['postal_code'] 	= $payment->user_info['address']['zip'];
	$converted_sub['card_info']['billing_details']['address']['country'] 		= $payment->user_info['address']['country'];

	\delete_transient('shopwp_cust_info_' . $wp_user->ID);

	update_contact_by_email([
      'email'        => $email,
      'properties'   => get_data_for_hs_contact_update($wp_user, $converted_sub, $customer, [
			'email'                 => $email,
			'subscription_status'   => 'Active',
			'lifecyclestage'        => 'customer',
			'using_plugin'          => 'Yes',
		])
   ]);  

}

function object_to_array($data) {

	$result = [];

	foreach ($data as $key => $value)
	{
		$result[$key] = (is_array($value) || is_object($value)) ? object_to_array($value) : $value;
	}

	return $result;
}

function handle_subscription_change($request) {
	\load_template( dirname( __FILE__ ) . '/lib/updates/on-subscriptions-change-webhook.php');
}

function handle_sync_edd_to_hs($request) {

	$wp_users = get_users([
		'number' => 100,
		'fields' => 'ID',
		// 'offset' => 2700,
		'count_total' => false
	]);

	return false;

	$new_custs = array_values(array_filter(array_map(function($wp_user_id) {

		$wp_user 						= new WP_User($wp_user_id);

		if (empty($wp_user)) {
			error_log('WordPress User record is empty, skipping for WP User ID: ' . $wp_user_id);
			return false;
		}

		$cust 							= edd_get_customer_by('user_id', $wp_user_id);

		if (empty($cust)) {
			error_log('EDD Customer record is using a different WP user, trying to fetch by name instead ...');
			$cust = edd_get_customer_by('name', $wp_user->display_name);

			if (empty($cust)) {
				error_log('EDD Customer record still empty. Setting as Lead in Hubspot and returning early for: ' . $wp_user->user_email);
				
				update_contact_by_email([
					'email' => $wp_user->user_email,
					'properties' => [
						'lifecyclestage' 	=> 'lead',
						'firstname'			=> isset($wp_user->first_name) ? $wp_user->first_name : '',
						'lastname'			=> isset($wp_user->last_name) ? $wp_user->last_name : ''
					]
				]);

				error_log('✅ Contact updated as lead.');

				return false;
			}
		}

		$EDD_Recurring_Subscriber = new EDD_Recurring_Subscriber($cust->id);

		$existing_subs 	= $EDD_Recurring_Subscriber->get_subscriptions(35);
		$addresses 		= edd_get_customer_addresses( [
			'customer_id' => $cust->id,
		]);

		if (empty($existing_subs)) {

			$expiration = date('Y-m-d', strtotime('+1 year', strtotime($cust->date_created)) );

			$subscription = [
				'created' => $cust->date_created,
				'expiration' => $expiration,
				'product_id' => 35,
				'price_id' => 1,
				'period' => 'year',
				'recurring_amount' => $cust->purchase_value,
				'bill_times' => 1,
				'status' => 'cancel',
			];

		} else {
			$subscription = object_to_array($existing_subs[0]);
		}

		if (!empty($addresses)) {

			$subscription['card_info'] = [
				'billing_details' => [
					'address' => [
						'state' 		=> $addresses[0]->region,
						'city' 			=> $addresses[0]->city,
						'line1' 		=> $addresses[0]->address,
						'postal_code' 	=> $addresses[0]->postal_code,
						'country' 		=> $addresses[0]->country
					]
				]
			];
		}

		return [
			'customer' 			=> $cust,
			'wp_user'			=> $wp_user,
			'subscription' 		=> $subscription
		];

	}, $wp_users)));

	foreach ($new_custs as $customer) {

		$props = get_data_for_hs_contact_update($customer['wp_user'], $customer['subscription'], $customer['customer'], [
			'subscription_status'   => empty($customer['subscription']) ? '' : ucfirst($customer['subscription']['status']),
			'lifecyclestage'        => empty($customer['subscription']) ? 'lead' : (str_contains($customer['subscription']['status'], 'cancel') ? '119671112' : 'customer'),
			'using_plugin'          => empty($customer['subscription']) ? 'No' : (str_contains($customer['subscription']['status'], 'cancel') ? 'No' : 'Yes'),
			'email'                 => $customer['wp_user']->data->user_email,
		]);

		update_contact_by_email([
			'email' => $customer['wp_user']->data->user_email,
			'properties' => $props
		]);
	}
}

function customize_edd_after_payment_actions_delay() {
	return 5;
}

function get_faqs_for_api() {
   
   $faqs = [];

   $args = [
      'posts_per_page' => -1,
      'post_type' => 'faqs'
   ];

   $posts = get_posts($args);

   foreach ($posts as $post) {
      $faqs[] = [
         'id' => $post->ID,
         'question' => get_field('faq_question', $post->ID),
         'answer' => get_field('faq_answer', $post->ID),
         'tag' => get_the_category($post->ID)
      ];
   };

   return $faqs;

}

function swp_add_endpoints() {

	register_rest_route('wpshopify', '/marketing/v1/faq', [
			[
				'methods' => \WP_REST_Server::CREATABLE,
				'callback' => 'get_faqs_for_api',
				'permission_callback' => '__return_true',
			],
        ]
	);

	register_rest_route('swp/v8', '/contact/add',
		[
			[
				'methods' 				=> 'POST',
				'callback' 				=> 'handle_add_contact',
				'permission_callback' 	=> "__return_true",
			]
		]
	);

	register_rest_route('swp/v8', '/ticket/create',
		[
			[
				'methods' 				=> 'POST',
				'callback' 				=> 'handle_create_ticket',
				'permission_callback' 	=> "__return_true",
			]
		]
	);

	register_rest_route('swp/v8', '/contacts/sync',
		[
			[
				'methods' 				=> 'POST',
				'callback' 				=> 'handle_sync_edd_to_hs',
				'permission_callback' 	=> "__return_true",
			]
		]
	);

	// register_rest_route('swp/v8', '/subscription/change',
	// 	[
	// 		[
	// 			'methods' 				=> 'POST',
	// 			'callback' 				=> 'handle_subscription_change',
	// 			'permission_callback' 	=> "__return_true",
	// 		]
	// 	]
	// );
}

function extensions_page_title($title) {

    if ( is_post_type_archive('download') ) {
		error_log('--- returning our title ---');
        return 'ShopWP | Add extensions like Elementor to your store';
    }
error_log('--- NOT returning our title ---');
    return $title;

} 

function shopwp_get_recent_receipt_data() {

	global $edd_receipt_args;

	$session = edd_get_purchase_session();

	if ( isset( $_GET['payment_key'] ) ) {
		$payment_key = urldecode( $_GET['payment_key'] );

	} else if ( $session ) {
		$payment_key = $session['purchase_key'];
	}

	if (empty($payment_key)) {
		return [];
	}

	$payment_id    = edd_get_purchase_id_by_key( $payment_key );
	$payment       = get_post( $payment_id );
	$meta          = edd_get_payment_meta( $payment->ID );
	$cart          = edd_get_payment_meta_cart_details( $payment->ID, true );
	$user          = edd_get_payment_meta_user_info( $payment->ID );
	$email         = edd_get_payment_user_email( $payment->ID );
	$status        = edd_get_payment_status( $payment, true );

	if (isset($meta['key']) && $meta['key']) {

	$finalArray = [
		'transaction' => $payment,
		'payment' => $meta,
		'cart' => $cart,
		'user' => $user
	];

	} else {
		$finalArray = [];
	}

	return $finalArray;

}


/*

Hooks

*/
remove_action('edd_purchase_form_before_submit', 'edd_checkout_final_total', 999);

add_action('rest_api_init', 'swp_add_endpoints');
add_action('template_redirect', 'redirect_empty_cart');
add_action('admin_init', 'block_wp_admin');
add_action('init', 'custom_post_type_faqs');
add_action('init', 'redirect_logged_in_users_to_account');
add_action('edd_after_payment_actions', 'after_shopwp_purchase', 10, 3);
add_action('send_headers', 'enable_strict_transport_security_hsts_header');
add_action('edd_purchase_form_after_submit', 'shopwp_edd_checkout_final_total_after', 999);
add_action('edd_purchase_form_before_submit', 'shopwp_edd_checkout_final_total', 999);
add_action('do_faviconico', 'remove_default_favicon');
add_action('wp_enqueue_scripts', 'shopwp_assets');

add_filter('edd_after_payment_actions_delay', 'customize_edd_after_payment_actions_delay');
add_filter('shopwp_settings', 'customize_shopwp_settings');
add_filter('show_admin_bar', '__return_false');
add_filter('edd_cart_contents', 'remove_duplicates_from_cart');
add_filter('edd_sl_download_changelog', 'remove_slashes_from_changelog');
add_filter('edd_get_cart_item_name', 'customize_cart_product_name');
add_filter('pre_get_document_title', 'extensions_page_title', 9999);

add_shortcode( 'shopwp_checkout', 'shopwp_edd_checkout_form_shortcode');

add_theme_support( 'post-thumbnails' );