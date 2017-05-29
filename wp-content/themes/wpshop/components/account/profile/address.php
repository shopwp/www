<section class="">
  <h3>Profile</h3>

  <?php

  $customer = new EDD_Customer(get_current_user_id(), true);

  ?>

  <form id="form-account-profile-address" action="address" method="post" class="form">

    <label for="wps_customer_name">Name</label>
    <input type="text" name="wps_customer_name" value="<?php echo $customer->name; ?>">

    <label for="wps_customer_email">Email</label>
    <input type="email" name="wps_customer_email" value="<?php echo $customer->email; ?>">

    <div class="btn-wrap">
      <input type="submit" name="wps_customer_address_submit" class="btn btn-primary">Save profile</input>
    </div>

    <div class="spinner"></div>

    <?php // wp_nonce_field('account-profile-address-nonce', 'account-profile-address'); ?>
    <?php wp_nonce_field( 'account-profile-address', 'account-profile-address' ); ?>
  </form>

</section>
