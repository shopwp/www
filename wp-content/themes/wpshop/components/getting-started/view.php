<form class="mailinglist-form getting-started-mailinglist-form" id="getting-started-wrapper" class="form form-lg l-row-center" action="" method="post" data-type="Getting Started" data-nonce="<?php echo wp_create_nonce('mailinglist'); ?>">

   <div class="mailinglist-group-copy l-col l-col-center">
      <p class="mailinglist-copy">We'll email you a download link for WP Shopify.</p>
   </div>

   <div class="form-control l-row">
      <label for="email" class="form-label-modal is-hidden">Email Address</label>
      <input name="email" type="text" class="mailinglist-email form-input" placeholder="Email address" />

      <?php wp_nonce_field('mailinglist_signup'); ?>

      <div class="btn-group l-row-center">
         <button class="btn btn-secondary form-btn" type="submit" title="Sign up" value="Sign up">Email download link <svg class="svg-inline--fa fa-long-arrow-right fa-w-14" aria-hidden="true" focusable="false" data-prefix="fal" data-icon="long-arrow-right" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" data-fa-i2svg=""><path fill="currentColor" d="M311.03 131.515l-7.071 7.07c-4.686 4.686-4.686 12.284 0 16.971L387.887 239H12c-6.627 0-12 5.373-12 12v10c0 6.627 5.373 12 12 12h375.887l-83.928 83.444c-4.686 4.686-4.686 12.284 0 16.971l7.071 7.07c4.686 4.686 12.284 4.686 16.97 0l116.485-116c4.686-4.686 4.686-12.284 0-16.971L328 131.515c-4.686-4.687-12.284-4.687-16.97 0z"></path></svg></button>
      </div>

      <div class="spinner"></div>
   </div>

   <aside class="form-messages">
      <div class="form-message form-error"></div>
      <div class="form-message form-success"></div>
   </aside>

</form>