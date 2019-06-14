<section class="component component-mailinglist form-wrapper l-col l-row-center" id="mailinglist">
<div class="l-contain-narrow">
  <div class="mailinglist-group-copy l-col l-col-center">
    <h1 class="mailinglist-heading">Stay up to date!</h1>
    <p class="mailinglist-copy">Enter your email to know about the latest WP Shopify changes, beta invites, and discount codes!</p>
  </div>

  <form id="mailinglist-form" class="form form-lg l-row-center" action="" method="post" data-nonce="<?php echo wp_create_nonce('mailinglist'); ?>">

    <div class="form-control l-row">
      <label for="email" class="form-label">Email Address</label>
      <input name="email" id="mailinglist-email" type="text" class="form-input" />
      <?php wp_nonce_field('mailinglist_signup'); ?>

      <div class="btn-group l-row-center">
        <button class="btn btn-l btn-secondary form-btn" type="submit" title="Sign up" value="Sign up" />
        <i class="fa fa-envelope" aria-hidden="true"></i> Sign me up</button>
        <div class="btn-bg"></div>
      </div>

      <div class="spinner"></div>
    </div>

    <aside class="form-messages">
      <div class="form-message form-error"></div>
      <div class="form-message form-success"></div>
    </aside>

  </form>
</div>
</section>
