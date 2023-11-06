<?php 

get_header();

get_template_part('components/header');

?>

<main>

   <div class="l-contain">
      <div class="content l-contain-s l-text-center">
         <h1>Extensions for ShopWP Pro</h1>
         <p>Give your store super powers by using the below extensions for <a href="/purchase/">ShopWP Pro</a>.</p>
      </div>
   </div>

   <?php 

   get_template_part('components/extensions');

   ?>
</main>

<?php 

get_footer();

?>