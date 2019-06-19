<?php

/*

Template Name: Account

*/

?>

<?php

      $customer = new EDD_Customer(get_current_user_id(), true );

      //
      // echo "<pre>";
      // print_r($customer);
      // echo "</pre>";

      $hash = md5( strtolower( trim($customer->email) ) );
      ?>

<!-- <?php while (have_posts()) : the_post(); ?>
  <?php get_template_part('templates/page', 'header'); ?>
  <?php get_template_part('templates/content', 'page'); ?>
<?php endwhile; ?> -->

<!-- <section class="component">
  <a href="#" class="btn btn-lg">Learn more</a>
</section> -->

<section class="l-fill l-row l-contain">

  <div class="content l-col l-fill">

    <?php
      
      get_template_part('components/account/info/info');

      get_template_part('components/account/nav');
      
      get_template_part('components/account/license/license');
      get_template_part('components/account/subscriptions/subscriptions');
      get_template_part('components/account/downloads/downloads');
      get_template_part('components/account/orders/orders');
      get_template_part('components/account/license/upgrade');
      get_template_part('components/account/profile/profile');
    ?>
  </div>

</section>
