<form id="form-forgot-pass" action="<?php echo wp_lostpassword_url(); ?>" method="post" name="lostpasswordform" class="form form-account">

  <div class="form-input">
    <label for="wps_account_forgot_password"><?php _e( 'Enter your email', 'personalize-login' ); ?></label>
    <input type="email" name="wps_account_forgot_password" id="wps_account_forgot_password" placeholder="Email" />
  </div>

  <p class="form-note">You'll recieve an email with a special reset link</p>

  <div class="btn-wrap btn-wrap-center">
    <input type="submit" name="submit" class="button" value="<?php _e( 'Reset Password', 'personalize-login' ); ?>"/>
    <div class="spinner spinner-sm"></div>
  </div>

  <?php wp_nonce_field( 'account-forgot-pass', 'account-forgot-pass' ); ?>

</form>
