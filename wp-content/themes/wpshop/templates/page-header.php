<?php use Roots\Sage\Titles; ?>

<?php if( !get_field('page_settings_hide_title', get_the_ID()) ) { ?>

  <div class="page-header">

    <?php if(is_page('Checkout')) { ?>
      <h1 class="has-icon">
        <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/prod/imgs/icon-lock.svg" alt="Secure" class="icon icon-lock">
        <?= Titles\title(); ?>
      </h1>

    <?php } else if(is_page('purchase-confirmation')) { ?>

      <h1 class="has-icon">
        <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/prod/imgs/icon-check.svg" alt="Payment Success" class="icon icon-small icon-thumbs-up">
        <?= Titles\title(); ?>
      </h1>

    <?php } else { ?>
      <h1> <?= Titles\title(); ?> </h1>

    <?php } ?>

  </div>

<?php } ?>
