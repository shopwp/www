<section class="component component-marquee l-col">

  <div class="marquee-content l-row">

  <div class="marquee-left">
         
         <div class="logos-wrapper">
            
            <a class="logos" target="_blank" href="https://www.shopify.com/?ref=wps">
               <img class="logo wordpress-logo" src="<?php echo get_template_directory_uri() ?>/assets/imgs/icon-wp.svg" alt="WordPress Logo">
               <img class="logo shopify-logo" src="<?php echo get_template_directory_uri() ?>/assets/imgs/icon-shopify.svg" alt="Shopify Logo">
            </a>

            <?php include(locate_template('components/current-version/view.php')); ?>

         </div>

         <h1><?php the_sub_field('short_description'); ?></h1>
         
         <p class="marquee-short-blurb">A killer WordPress plugin that empowers creators, businesses, and developers to sell Shopify products on any WordPress site.</p>

         <div class="btn-group l-row l-row-left">
            <span class="btn btn-download-free getting-started-trigger" target="_blank">Start for free</span>
            <a href="https://demo.wpshop.io" class="btn btn-l btn-secondary" target="_blank">View the demo <i class="fal fa-external-link"></i></a>
         </div>

      </div>

      <div class="marquee-right">

      <img class="screen-one" src="<?php echo get_template_directory_uri() ?>/assets/imgs/marquee-right-<?= rand(1, 6) ?>.jpg" alt="Sup">

      <img class="screen-two" src="<?php echo get_template_directory_uri() ?>/assets/imgs/marquee-right-two.jpg" alt="Sup">
      <div class="stage"></div>
      </div>
  </div>

  

</div>


</section>

<!-- 
<section class="component component-snippet l-col l-col-center l-row-center">

  <div class="l-contain">
    <div class="snippet l-row">
      <div class="snippet-image">
        <img src="<?php echo get_template_directory_uri() ?>/assets/prod/imgs/illustration-brand.svg">
      </div>

      <div class="l-col l-row-center snippet-content">
        <h2 class="snippet-heading">Free your brand.</h2>
        <p class="snippet-copy">Keeping your brand consistent is important to build trust with your customers. Sync your data into WordPress without restrictive iFrames.</p>
      </div>
    </div>

  </div>

</section>

<section class="component component-snippet l-col l-col-center l-row-center">

  <div class="l-contain">

    <div class="snippet l-row">

      <div class="l-col l-row-center snippet-content">
        <h2 class="snippet-heading">Sync what you need,<br>display what you want.</h2>
        <p class="snippet-copy">You control the data and how it's shown to your users. We provide templates and over 100 + actions and filters.</p>

      </div>

      <div class="snippet-image">
        <img src="<?php echo get_template_directory_uri() ?>/assets/prod/imgs/illustration-sync.svg">
      </div>

    </div>

    <div class="snippet snippet-made-with-devs l-row">
      <div class="snippet-image">
        <img src="<?php echo get_template_directory_uri() ?>/assets/prod/imgs/illustration-code.svg">
      </div>

      <div class="l-col l-row-center snippet-content">
        <h2 class="snippet-heading">One plugin for two platforms.</h2>
        <p class="snippet-copy">Change something in Shopify and watch it automatically appear in WordPress. Webhooks are used to keep your data in sync.</p>

      </div>
    </div>

  </div>

</section> -->
