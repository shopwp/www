<?php 

error_log('👾 On subscription change webhook');

$payload = @file_get_contents('php://input');
$event = null;

try {
    $event = \Stripe\Event::constructFrom(
        json_decode($payload, true)
    );
} catch(\UnexpectedValueException $e) {
    error_log('👾 On subscription change error');
    // Invalid payload
    http_response_code(400);
    exit();
}

// Handle the event
switch ($event->type) {
    case 'customer.subscription.updated':
        error_log('👾 customer.subscription.updated');
        
        $stripe_subscription = $event->data->object;
        break;

    case 'customer.subscription.deleted':
        error_log('👾 customer.subscription.deleted');
        
        $stripe_subscription = $event->data->object;
        break;
        
    default:
        error_log('👾 Received unknown event type ' . $event->type);
        $stripe_subscription = false;
        break;
}

http_response_code(200);

if (empty($stripe_subscription)) {
    exit;
}

// error_log('👾 Subscription');
// error_log(print_r($subscription, true));
// error_log('👾 /Subscription');


$edd_subscription               = new EDD_Subscription($stripe_subscription->id, true);
$email                          = $edd_subscription->customer->email;
$payment                        = edd_get_payment($edd_subscription->parent_payment_id);
$wp_user                        = get_user_by('email', $email);
$EDD_Subscription 				= new EDD_Subscription( absint($stripe_subscription->id) );
$customer                       = new EDD_Customer($wp_user->ID, true);
$converted_sub 					= json_decode(json_encode($EDD_Subscription), true);

$converted_sub['card_info'] 												= [];
$converted_sub['card_info']['billing_details']								= [];
$converted_sub['card_info']['billing_details']['address']					= [];
$converted_sub['card_info']['billing_details']['address']['state'] 			= $payment->user_info['address']['state'];
$converted_sub['card_info']['billing_details']['address']['city'] 			= $payment->user_info['address']['city'];
$converted_sub['card_info']['billing_details']['address']['line1'] 			= $payment->user_info['address']['line1'];
$converted_sub['card_info']['billing_details']['address']['postal_code'] 	= $payment->user_info['address']['zip'];
$converted_sub['card_info']['billing_details']['address']['country'] 		= $payment->user_info['address']['country'];

$properties = [
    'email'                 => $email,
    'subscription_status'   => $stripe_subscription->status,
];

if ($stripe_subscription->status === 'active') {
     $properties['lifecyclestage']  = 'customer';
     $properties['using_plugin']    = 'Yes';

} else if ($stripe_subscription->status === 'canceled') {
     $properties['lifecyclestage']  = '119671112';
     $properties['using_plugin']    = 'No';
}

update_contact_by_email([
    'email'        => $email,
    'properties'   => get_data_for_hs_contact_update($wp_user, $converted_sub, $customer, $properties)
]);

\delete_transient('shopwp_cust_info_' . $wp_user->ID);