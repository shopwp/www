<section class="component component-mailinglist form-wrapper l-col l-row-center edge--bottom edge--top--reverse" id="mailinglist">

<div class="component-inner">

<div class="l-contain-narrow">
  <div class="mailinglist-group-copy l-col l-col-center">
    <h2 class="mailinglist-heading">Stay up to date!</h2>
    <p class="mailinglist-copy">Enter your email for the latest plugin developments, beta invites, and discounts.</p>
  </div>

  <form class="mailinglist-form" id="mailinglist-form" data-type="Normal" class="form form-lg l-row-center" action="" method="post" data-nonce="<?php echo wp_create_nonce('mailinglist'); ?>">

    <div class="form-control l-row">
      <label for="email" class="form-label">Email Address</label>
      <input name="email" id="" type="text" class="mailinglist-email form-input" />

      <?php wp_nonce_field('mailinglist_signup'); ?>

      <div class="btn-group l-row-center">
        <button class="btn btn-secondary form-btn" type="submit" title="Sign up" value="Sign up" />Sign me up</button>
      </div>

      <div class="spinner"></div>
    </div>

    <aside class="form-messages">
      <div class="form-message form-error"></div>
      <div class="form-message form-success"></div>
    </aside>

  </form>
</div>

</div>

</section>
