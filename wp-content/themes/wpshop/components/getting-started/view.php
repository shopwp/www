<form class="mailinglist-form getting-started-mailinglist-form" id="getting-started-wrapper" class="form form-lg l-row-center" action="" method="post" data-nonce="<?php echo wp_create_nonce('mailinglist'); ?>">

   <div class="mailinglist-group-copy l-col l-col-center">
      <p class="mailinglist-copy">We'll email you a download link for WP Shopify.</p>
   </div>

   <div class="form-control l-row">
      <label for="email" class="form-label-modal is-hidden">Email Address</label>
      <input name="email" type="text" class="mailinglist-email form-input" placeholder="Email address" />

      <?php wp_nonce_field('mailinglist_signup'); ?>

      <div class="btn-group l-row-center">
         <button class="btn btn-secondary form-btn" type="submit" title="Sign up" value="Sign up">Email download link <i class="fal fa-long-arrow-right"></i></button>
      </div>

      <div class="spinner"></div>
   </div>

   <aside class="form-messages">
      <div class="form-message form-error"></div>
      <div class="form-message form-success"></div>
   </aside>

</form>