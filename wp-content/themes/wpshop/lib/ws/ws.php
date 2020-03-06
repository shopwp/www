<?php

/*

Checking for valid nonce

*/
function wps_check_valid_nonce() {

  $meta = get_user_meta(2);
  echo json_encode($meta['description']);
  die();

}

add_action('wp_ajax_wps_check_valid_nonce', 'wps_check_valid_nonce');
add_action('wp_ajax_nopriv_wps_check_valid_nonce', 'wps_check_valid_nonce');



/*

Checking for valid nonce

*/
function wps_get_access_token() {

  echo json_encode( get_field('github_access_token', 'option') );
  die();

}

add_action('wp_ajax_wps_get_access_token', 'wps_get_access_token');
add_action('wp_ajax_nopriv_wps_get_access_token', 'wps_get_access_token');


/*

Saving auth data

*/
function wps_save_auth_data() {

  $data = $_POST['data'];
  update_user_meta(2, 'description', $data);

  echo json_encode($data);
  die();

}

add_action('wp_ajax_wps_save_auth_data', 'wps_save_auth_data');
add_action('wp_ajax_nopriv_wps_save_auth_data', 'wps_save_auth_data');



/*

Return Shopify Settings

*/
function wps_get_settings() {

  $settings = array(
    'wps_api_key' => WP_SHOPIFY_API_KEY,
    'wps_shared_secret' => WP_SHOPIFY_SHARED_SECRET,
    'wps_scopes' => WP_SHOPIFY_SCOPES,
    'wps_redirect' => WP_SHOPIFY_REDIRECT
  );

	return $settings;

}


add_action('rest_api_init', function () {
	register_rest_route('wp-shopify/v1', '/settings', array(
		'methods' => 'GET',
		'callback' => 'wps_get_settings'
	));
});


//
// Fetching Mailchimp List ID
//
function mailinglist_get_list_id() {

  // Test list 53f4059701
  // Live list 5c6bd183d4

  echo '53f4059701';
  die();

}

add_action('wp_ajax_mailinglist_get_list_id', 'mailinglist_get_list_id');
add_action('wp_ajax_nopriv_mailinglist_get_list_id', 'mailinglist_get_list_id');


//
// Fetching Mailchimp List
//
function mailinglist_signup() {

  $email = $_POST['email'];
  $nonce = $_POST['nonce'];
  $type = $_POST['type'];

  error_log('----- $email -----');
  error_log(print_r($email, true));
  error_log('----- /$email -----');

  error_log('----- $type -----');
  error_log(print_r($type, true));
  error_log('----- /$type -----');

  if (wp_verify_nonce($nonce, 'mailinglist_signup')) {

    $resp = [];

    $apiKey = get_field('theme_mailchimp_api_key', 'option');

    try {

      $client = new GuzzleHttp\Client(['base_uri' => 'https://us11.api.mailchimp.com/3.0/']);
      $subscriber_hash = md5(strtolower($email));

      $body = [
          'email_address' => $email,
          "status" => "subscribed"
      ];

      if ($type === 'Getting Started') {
         $body['tags'] = ['Getting Started'];
      }

      error_log('----- $body -----');
      error_log(print_r($body, true));
      error_log('----- /$body -----');

      $response = $client->request('POST', 'lists/5c6bd183d4/members', [
        'auth' => [
          'arobbins',
          $apiKey
        ],
        'json' => $body
      ]);

      $statusCode = $response->getStatusCode();

      $resp['code'] = $statusCode;
      $resp['message'] = json_decode($response->getBody());

      echo json_encode($resp);
      die();

    } catch (GuzzleHttp\Exception\ClientException $e) {

      $response = $e->getResponse();
      $statusCode = $response->getStatusCode();
      $message = $e->getMessage();

      $resp['code'] = $statusCode;
      $resp['message'] = json_decode($response->getBody());

      echo json_encode($resp);
      die();

    }

  } else {

    echo 'Invalid Nonce. Reload the browser and try again.';
    die();

  }

}

add_action('wp_ajax_mailinglist_signup', 'mailinglist_signup');
add_action('wp_ajax_nopriv_mailinglist_signup', 'mailinglist_signup');




function wpshopify_doc_updated($post_id) {

  if (get_post_type($post_id) !== 'docs') {
    return;
  };

  $transientExists = get_transient('wpshopify_' . $post_id);

  if ($transientExists) {
    delete_transient('wpshopify_' . $post_id);
    delete_transient('wpshopify_sidebar_docs');
  }

}

add_action('save_post', 'wpshopify_doc_updated');






function get_doc() {

  $docID = $_POST['docId'];
  $cache = get_transient('wpshopify_' . $docID);

  if ($cache) {

    return new WP_REST_Response($cache, 200);

  } else {

    $post = get_post($docID);

    ob_start();
    include(locate_template('templates/content-single-docs.php'));
    $content = ob_get_contents();
    ob_end_clean();

    $docData = array(
      'content' => $content,
      'slug'    => $post->post_name,
      'url'     => get_post_permalink($post->ID)
    );

    set_transient('wpshopify_' . $docID, $docData);

    return new \WP_REST_Response($docData, 200);

  }

}


/*

WP Shopify API

*/
function register_api_endpoints() {

  register_rest_route('wpshop/v1', '/docs/get', [
    'methods'     => 'POST',
    'callback'    => 'get_doc'
  ]);

}

add_action( 'rest_api_init', 'register_api_endpoints');






















/*

Get Account Cat

*/
function wps_get_account_cat() {

  $customer = new EDD_Customer(get_current_user_id(), true);

  echo do_shortcode('[download_history]');
  die();

}

add_action('wp_ajax_wps_get_account_cat', 'wps_get_account_cat');
add_action('wp_ajax_nopriv_wps_get_account_cat', 'wps_get_account_cat');



/*

Get Account Cat

*/
function wps_get_forgot_pass_form() {

  echo get_template_part('components/account/profile/forgot-pass');
  die();

}

add_action('wp_ajax_wps_get_forgot_pass_form', 'wps_get_forgot_pass_form');
add_action('wp_ajax_nopriv_wps_get_forgot_pass_form', 'wps_get_forgot_pass_form');


/*

Check if username exists

*/
function wps_check_existing_username() {

  $userID = username_exists($_POST['email']);

  if($userID) {
    echo('false');
    die();

  } else {
    echo('true');
    die();

  }

}

add_action('wp_ajax_wps_check_existing_username', 'wps_check_existing_username');
add_action('wp_ajax_nopriv_wps_check_existing_username', 'wps_check_existing_username');




function wps_account_update_profile() {

  wps_verify_nonce('account-profile-general');

  $response = array(
    'name' => false,
    'email' => false
  );

  if(wps_get_customer_name() !== $_POST['data']['wps_customer_name']) {
    $responseName = wps_update_customer_name($_POST['data']['wps_customer_name']);
    $response['name'] = $responseName;
  }

  if(wps_get_customer_email() !== $_POST['data']['wps_customer_email']) {
    $responseEmail = wps_update_customer_email($_POST['data']['wps_customer_email']);
    $response['email'] = $responseEmail;
  }

  echo json_encode($response);
  die();

}

add_action('wp_ajax_wps_account_update_profile', 'wps_account_update_profile');
add_action('wp_ajax_nopriv_wps_account_update_profile', 'wps_account_update_profile');





/*

Update customer name

*/
function wps_change_customer_password() {

  $passCurrent = $_POST['data']['wps_customer_password_current'];
  $passNew = $_POST['data']['wps_customer_password_new_confirm'];
  $userID =  $_POST['data']['wps_customer_id'];
  $error = '';

  if (wps_check_current_pass_valid($passCurrent, $userID)) {

    $userId = wp_update_user( array('ID' => $userID, 'user_pass' => $passNew) );
    $result = $userId;

  } else {

    $result = false;
  }

  echo json_encode($result);
  die();

}

add_action('wp_ajax_wps_change_customer_password', 'wps_change_customer_password');
add_action('wp_ajax_nopriv_wps_change_customer_password', 'wps_change_customer_password');



/*

WPS Generate Password Reset

*/
function wps_generate_password_reset($email) {

  global $wpdb, $current_site;

  $result = array();

  $user = get_user_by( 'email', $email );

  if (isset($user) && $user) {

    $user_email = $email;

    // redefining user_login ensures we return the right case in the email
    $user_login = $user_email;

    do_action('retreive_password', $user_login);  // Misspelled and deprecated
    do_action('retrieve_password', $user_login);

    $key = wp_generate_password(40, false);
    $hash = password_hash($key, PASSWORD_DEFAULT);


    do_action('retrieve_password_key', $user_login, $hash);
    // Now insert the new md5 key into the db
    $wpdb->update($wpdb->users, array('user_activation_key' => $hash), array('user_login' => $user_login));

    $message = __('Someone requested that the password be reset for the following account:') . "\r\n\r\n";
    $message .= sprintf(__('Email: %s'), $user_login) . "\r\n\r\n";
    $message .= __('If this was a mistake, just ignore this email and nothing will happen.') . "\r\n\r\n";
    $message .= __('To reset your password, click the link below:') . "\r\n\r\n";
    $message .= network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user_login), 'login') . "\r\n";

    if ( is_multisite() )
        $blogname = $GLOBALS['current_site']->site_name;
    else
        // The blogname option is escaped with esc_html on the way into the database in sanitize_option
        // we want to reverse this for the plain text arena of emails.
        $blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

    $title = sprintf( __('[%s] Password Reset'), $blogname );

    $title = apply_filters('retrieve_password_title', $title);
    $message = apply_filters('retrieve_password_message', $message, $key);

    if ( $message && !wp_mail($user_email, $title, $message) )
        wp_die( __('The e-mail could not be sent.') . "<br />\n" . __('Possible reason: your host may have disabled the mail() function...') );

    $result['message'] = $message;
    return $result;

  } else {
    return false;

  }


}


/*

Reset password

*/
function wps_account_forgot_password() {

  wps_verify_nonce('account-forgot-pass');

  $passReset = wps_generate_password_reset($_POST['data']['wps_account_forgot_password']);

  if(isset($passReset) && $passReset) {
    echo json_encode($passReset);
    die();

  } else {
    echo json_encode(false);
    die();

  }

}

add_action('wp_ajax_nopriv_wps_account_forgot_password', 'wps_account_forgot_password');



/*

Reset password

*/
function wps_account_reset_password() {

  wps_verify_nonce('account-reset-pass');

  $passwordChanged = wps_do_password_reset($_POST['data']);

  echo json_encode($passwordChanged);
  die();

}

add_action('wp_ajax_wps_account_reset_password', 'wps_account_reset_password');
add_action('wp_ajax_nopriv_wps_account_reset_password', 'wps_account_reset_password');




?>
