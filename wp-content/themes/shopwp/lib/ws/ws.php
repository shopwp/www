<?php



function mailinglist_get_list_id() {

  // Test list 53f4059701
  // Live list 5c6bd183d4

  echo '53f4059701';
  die();

}

add_action('wp_ajax_mailinglist_get_list_id', 'mailinglist_get_list_id');
add_action('wp_ajax_nopriv_mailinglist_get_list_id', 'mailinglist_get_list_id');


function mailinglist_signup($request) {

   $email = $request->get_param('email');
   $type = $request->get_param('type');

   if (empty($email)) {
      return new \WP_REST_Response([
         'message' => 'Error: Please enter an email address',
         'error' => true
      ], 500);
   }

   $subscriber_hash = md5(strtolower($email));
   $api_url = SHOPWP_MAILCHIMP_LIST_BASE_URI . SHOPWP_MAILCHIMP_LIST_ENDPOINT . $subscriber_hash;

   $headers = [
      'Authorization' => 'Basic ' . base64_encode('arobbins:' . SHOPWP_MAILCHIMP_API_KEY)
   ];

  $request_args = [
     'headers' => $headers,
     'body' => json_encode([
         'email_address' => $email,
         "status_if_new" => "subscribed",
         "status" => "subscribed"
     ]),
     'method' => 'PUT'
  ];

  $response = \wp_remote_request($api_url, $request_args);

  $resp_list_add = [];
  $resp_list_add['code'] = \wp_remote_retrieve_response_code($response);
  $resp_list_add['body'] = json_decode(\wp_remote_retrieve_body($response));
  $resp_list_add['message'] = \wp_remote_retrieve_response_message($response);

   if (!empty($resp_list_add['body']) && !empty($resp_list_add['body']->errors)) {
      $resp_list_add['message'] = $resp_list_add['body']->detail . ' ' . $resp_list_add['body']->errors[0]->message . ' ' . $resp_list_add['body']->errors[0]->field;

   } else if (!empty($resp_list_add['body']) && !empty($resp_list_add['body']->detail)) {
      $resp_list_add['message'] .= ' ' . $resp_list_add['body']->detail;
   }

   if ($type === 'Getting Started') {
      $request_args_tags = [
         'headers' => $headers,
         'body' => json_encode([
               'tags' => [
                  [
                     'name' => 'Getting Started',
                     'status' => 'active'
                  ]
               ]
         ]),
         'method' => 'POST'
      ];    

      $response_tags = \wp_remote_request($api_url . '/tags', $request_args_tags);

      $resp_tags = [];
      $resp_tags['code'] = \wp_remote_retrieve_response_code($response_tags);
      $resp_tags['body'] = json_decode(\wp_remote_retrieve_body($response_tags));
      $resp_tags['message'] = \wp_remote_retrieve_response_message($response_tags);
   }
  
  if ($resp_list_add['code'] !== 200) {
   return new \WP_REST_Response([
      'message' => $resp_list_add['message'],
      'error' => true
   ], $resp_list_add['code']);   
  }

  return new \WP_REST_Response([
     'message' => 'Thanks! Your download should start shortly.',
     'error' => false
  ], 200);

}


function register_route_mailinglist_signup() {

         if (!defined('SHOPWP_API_NAMESPACE')) {
            return;
         }

        return register_rest_route(
            SHOPWP_API_NAMESPACE,
            '/mailinglist/add',
            [
                [
                    'methods' => \WP_REST_Server::CREATABLE,
                    'callback' => 'mailinglist_signup',
                    'permission_callback' => '__return_true',
                ],
            ]
        );
}

add_action('rest_api_init', 'register_route_mailinglist_signup');


function get_faqs_for_api() {
   
   $faqs = [];

   $args = [
      'posts_per_page' => -1,
      'post_type' => 'faqs'
   ];

   $posts = get_posts($args);

   foreach ($posts as $post) {
      $faqs[] = [
         'question' => get_field('faq_question', $post->ID),
         'answer' => get_field('faq_answer', $post->ID),
         'tag' => get_the_category($post->ID)
      ];
   };

   return $faqs;

}

function register_route_faq() {

        return register_rest_route(
            'wpshopify',
            '/marketing/v1/faq',
            [
                [
                    'methods' => \WP_REST_Server::CREATABLE,
                    'callback' => 'get_faqs_for_api',
                    'permission_callback' => '__return_true',
                ],
            ]
        );
}

add_action('rest_api_init', 'register_route_faq');