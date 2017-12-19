<?php

/*

Template Name: Account

*/

?>

<!-- <?php while (have_posts()) : the_post(); ?>
  <?php get_template_part('templates/page', 'header'); ?>
  <?php get_template_part('templates/content', 'page'); ?>
<?php endwhile; ?> -->

<!-- <section class="component">
  <a href="#" class="btn btn-lg">Learn more</a>
</section> -->

<section class="l-fill l-row l-contain">

  <aside class="sidebar sidebar-account">

    <h4 class="component-account-heading">
      <i class="fas fa-user-circle" aria-hidden="true"></i> Account
    </h4>

    <div class="sidebar-inner">

      <!-- <ul>
        <li class="account-cat" data-account-cat="downloads">Downloads</li>
        <li class="account-cat" data-account-cat="license">License</li>
        <li class="account-cat" data-account-cat="billing">Billing</li>
        <li class="account-cat" data-account-cat="orders">Order History</li>
        <li class="account-cat" data-account-cat="profile">Profile</li>
      </ul> -->
      <?php

      $customer = new EDD_Customer(get_current_user_id(), true );



      //
      // echo "<pre>";
      // print_r($customer);
      // echo "</pre>";

      $hash = md5( strtolower( trim($customer->email) ) );
      ?>

      <img src="https://www.gravatar.com/avatar/<?php echo $hash; ?>?s=200" alt="" class="user-img">

      <div class="user-info">
        <h5><?php echo $customer->name; ?></h5>
        <h5><?php echo $customer->email; ?></h5>
      </div>

      <?php // echo do_shortcode('[edd_login]'); ?>

    </div>

  </aside>

  <div class="content l-col l-fill">
    <?php

      get_template_part('components/account/license/license');
      get_template_part('components/account/downloads/downloads');
      get_template_part('components/account/orders/orders');
      get_template_part('components/account/license/upgrade');
      get_template_part('components/account/profile/profile');
    ?>
  </div>

</section>
