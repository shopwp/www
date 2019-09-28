<section class="component-account-sub">

  <h4 class="component-heading accordion-heading">
    <i class="fas fa-plus-square" aria-hidden="true"></i> Change Password
  </h4>

  <?php

  // $customer = new EDD_Customer(get_current_user_id(), true);

  ?>

  <form id="form-account-profile-password" action="password" method="post" class="form accordion-content">

    <div class="form-input">
      <label for="wps_customer_password_current">Current Password</label>
      <input type="password" name="wps_customer_password_current" value="">
    </div>

    <div class="form-input">
      <label for="wps_customer_password_new">New Password</label>
      <input type="password" name="wps_customer_password_new" id="form-input-password" value="">
    </div>

    <div class="form-input">
      <label for="wps_customer_password_new_confirm">Confirm New Password</label>
      <input type="password" name="wps_customer_password_new_confirm" id="form-input-password-confirm" value="">
    </div>

    <div class="btn-wrap">
      <input type="submit" name="wps_customer_password_submit" class="btn btn-primary" value="Save Password">
      <div class="spinner spinner-sm"></div>
    </div>

    <input type="hidden" name="wps_customer_id" value="<?php echo get_current_user_id(); ?>">

    <?php wp_nonce_field( 'account-profile-password', 'account-profile-password' ); ?>

  </form>

</section>
