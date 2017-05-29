<section class="component-account-sub">

  <h4 class="component-heading accordion-heading"><i class="fa fa-plus-square-o" aria-hidden="true"></i> Change Email</h4>

  <?php

  $customer = new EDD_Customer(get_current_user_id(), true);
  $user_info = get_userdata(get_current_user_id());




  $download = edd_get_download('wp-shopify');

  // echo "<pre>";
  // print_r($download);
  // echo "</pre>";


  ?>

  <form id="form-account-profile-general" action="address" method="post" class="form form-account accordion-content">

    <div class="form-input">
      <label for="wps_customer_name">Name</label>
      <input type="text" name="wps_customer_name" value="<?php echo $customer->name; ?>">
    </div>

    <div class="form-input">
      <label for="wps_customer_email">Email</label>
      <small>You will be forced to log back in if you change your email</small>
      <input type="email" name="wps_customer_email" id="wps_customer_email" value="<?php echo $user_info->user_email; ?>">
    </div>

    <div class="btn-wrap btn-wrap-center">
      <input type="submit" name="wps_customer_address_submit" class="btn btn-primary" value="Save profile">
      <div class="spinner spinner-sm"></div>
    </div>

    <?php wp_nonce_field( 'account-profile-general', 'account-profile-general' ); ?>

  </form>

</section>
