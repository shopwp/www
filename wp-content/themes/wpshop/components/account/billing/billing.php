<section class="component component-account-billing">
  <h4 class="component-account-heading">Payment Methods</h4>


  <?php

  $stripe_trans_id = '';
  $stripe_cus_id = '';

  $customer = new EDD_Customer(get_current_user_id(), true);
  $payments = $customer->get_payments();
  $user_info = get_userdata(get_current_user_id());

  $meta = get_user_meta($user_info->ID);
  $stripe_cus_id = $meta['_edd_stripe_customer_id_test'][0];

  foreach ($payments as $key => $payment) {
    $stripe_trans_id = edd_get_payment_transaction_id($payment->ID);
  }

  ?>

  <?php // echo do_shortcode('[edd_subscriptions]'); ?>
  <?php // echo do_shortcode('[purchase_history]'); ?>

</section>
