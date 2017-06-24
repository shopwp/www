<?php use Roots\Sage\Titles; ?>


<?php

if(is_page('purchase-confirmation')) {

  if ( is_user_logged_in() ) {

    global $current_user;

    $firstName = $current_user->user_firstname;
    $lastName = $current_user->user_lastname;

    echo '<div class="msg msg-success animated fadeInDown">Thank you for your purchase, ' . $firstName . '! Please check your email for login credentials.</div>';

    // echo 'Hey, ' . $firstName . '. Lets get you checked out.';

  } else {
    // echo '<div class="">Do you have an existing account?</div>';
  }

}

?>

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
