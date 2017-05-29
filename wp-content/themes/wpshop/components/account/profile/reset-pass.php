<div id="password-reset-form" class="widecolumn">

  <h4 class="heading-center"><?php _e( 'Choose a New Password', 'personalize-login' ); ?></h4>

  <form name="form-reset-pass" id="form-reset-pass" action="" method="post" autocomplete="off" class="form form-account">

    <input type="hidden" id="user_login" name="login" value="<?php echo esc_attr( $_REQUEST['login'] ); ?>" autocomplete="off" />
    <input type="hidden" name="key" value="<?php echo esc_attr( $_REQUEST['key'] ); ?>" />


    <div class="form-input">
      <label for="wps_account_new_password"><?php _e( 'New password', 'personalize-login' ) ?></label>
      <input type="password" name="wps_account_new_password" id="wps_account_new_password" class="input" size="" value="" autocomplete="off" />
    </div>

    <div class="form-input">
      <label for="wps_account_new_password_confirm"><?php _e( 'Confirm new password', 'personalize-login' ) ?></label>
      <input type="password" name="wps_account_new_password_confirm" id="wps_account_new_password_confirm" class="input" size="" value="" autocomplete="off" />
    </div>

    <small class="description"><?php echo wp_get_password_hint(); ?></small>

    <div class="btn-wrap btn-wrap-center">
      <input type="submit" name="submit" id="resetpass-button" class="btn" value="<?php _e( 'Reset Password', 'personalize-login' ); ?>" />
      <div class="spinner spinner-sm"></div>
    </div>

    <?php wp_nonce_field( 'account-reset-pass', 'account-reset-pass' ); ?>

  </form>
</div>
