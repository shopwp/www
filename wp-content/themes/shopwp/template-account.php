<?php

/*

Template Name: Account

*/

?>

<?php

      $customer = new EDD_Customer(get_current_user_id(), true );
      $user = wp_get_current_user();
      //
      // echo "<pre>";
      // print_r($customer);
      // echo "</pre>";

      $hash = md5( strtolower( trim($customer->email) ) );
      ?>


<div class="page-header">

   <h1><?= get_the_title();?></h1>

</div>


<section class="l-fill l-row l-contain">


   <div class="l-row account-wrapper">

      <div class="account-nav-wrapper">

      <div class="user-info">
         <b><?= $user->data->display_name; ?></b>
         <p><?= $user->data->user_email; ?></p>
         <small><a href="<?= wp_logout_url('/login'); ?>">Logout</a></small>
      </div>
         
<?php 
      get_template_part('components/account/nav');
  ?>         
      </div>

      <div class="account-content-wrapper">
  <?php 
  
      get_template_part('components/account/license/license');
      get_template_part('components/account/subscriptions/subscriptions');
      get_template_part('components/account/downloads/downloads');
      get_template_part('components/account/orders/orders');
      get_template_part('components/account/license/upgrade');
      get_template_part('components/account/profile/profile');
  
  ?>       
      </div>
   </div>
  

</section>
